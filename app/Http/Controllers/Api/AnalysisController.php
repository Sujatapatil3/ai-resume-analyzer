<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnalysisController extends Controller
{
    // Get all analyses of the logged-in user
    public function index(Request $request)
    {
        $analyses = Analysis::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'analyses' => $analyses,
        ]);
    }

    // Create a basic analysis record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'resume_text' => 'required|string',
            'job_description' => 'required|string',
        ]);

        $analysis = Analysis::create([
            'user_id' => $request->user()->id,
            'resume_text' => $validated['resume_text'],
            'job_description' => $validated['job_description'],
        ]);

        return response()->json([
            'message' => 'Analysis created successfully',
            'analysis' => $analysis,
        ], 201);
    }

    // Get one analysis
    public function show(Request $request, $id)
    {
        $analysis = Analysis::where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'analysis' => $analysis,
        ]);
    }

    // Delete one analysis
    public function destroy(Request $request, $id)
    {
        $analysis = Analysis::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $analysis->delete();

        return response()->json([
            'message' => 'Analysis deleted successfully',
        ]);
    }

    // Analyze resume against job description
    public function analyze(Request $request)
    {
        $validated = $request->validate([
            'resume_text' => 'required|string|max:20000',
            'job_description' => 'required|string|max:20000',
        ]);

        $apiKey = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL', 'gemini-3.7-flash');

        /*
        |--------------------------------------------------------------------------
        | No API key
        |--------------------------------------------------------------------------
        */

        if (!$apiKey) {
            $result = $this->mockAnalysis(
                $validated['resume_text'],
                $validated['job_description']
            );

            $analysis = $this->saveAnalysis(
                $request,
                $validated,
                $result
            );

            return response()->json([
                'message' => 'Gemini API key not configured. Mock fallback analysis used.',
                'source' => 'mock_fallback',
                'analysis' => $analysis,
            ], 201);
        }

        /*
        |--------------------------------------------------------------------------
        | Gemini prompt
        |--------------------------------------------------------------------------
        */

        $prompt = <<<PROMPT
You are an AI Resume and Job Description Analyzer.

Compare the candidate's resume with the job description.

Return ONLY JSON matching the requested schema.

Analyze these items:
- Match percentage from 0 to 100
- Matched skills
- Missing skills
- Strengths
- Weaknesses
- Improvement recommendations
- 5 relevant interview questions

Do not invent information.
Only use information supported by the resume and job description.

RESUME:
{$validated['resume_text']}

JOB DESCRIPTION:
{$validated['job_description']}
PROMPT;

        try {
            /*
            |--------------------------------------------------------------------------
            | Gemini API request
            |--------------------------------------------------------------------------
            */

            $response = Http::timeout(60)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt,
                                    ],
                                ],
                            ],
                        ],

                        'generationConfig' => [
                            'responseFormat' => [
                                'text' => [
                                    'mimeType' => 'APPLICATION_JSON',

                                    'schema' => [
                                        'type' => 'object',

                                        'properties' => [
                                            'match_percentage' => [
                                                'type' => 'number',
                                            ],

                                            'matched_skills' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],

                                            'missing_skills' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],

                                            'strengths' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],

                                            'weaknesses' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],

                                            'recommendations' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],

                                            'interview_questions' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],

                                        'required' => [
                                            'match_percentage',
                                            'matched_skills',
                                            'missing_skills',
                                            'strengths',
                                            'weaknesses',
                                            'recommendations',
                                            'interview_questions',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | Gemini temporarily unavailable
            |--------------------------------------------------------------------------
            */

            if ($response->status() === 503 || $response->status() === 429) {

                $result = $this->mockAnalysis(
                    $validated['resume_text'],
                    $validated['job_description']
                );

                $analysis = $this->saveAnalysis(
                    $request,
                    $validated,
                    $result
                );

                return response()->json([
                    'message' => 'Gemini temporarily unavailable. Mock fallback analysis used.',
                    'source' => 'mock_fallback',
                    'analysis' => $analysis,
                ], 201);
            }

            /*
            |--------------------------------------------------------------------------
            | Other Gemini errors
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gemini API request failed.',
                    'status' => $response->status(),
                ], 502);
            }

            /*
            |--------------------------------------------------------------------------
            | Read Gemini response
            |--------------------------------------------------------------------------
            */

            $responseText = $response->json(
                'candidates.0.content.parts.0.text'
            );

            if (!$responseText) {
                return response()->json([
                    'message' => 'Gemini returned an empty response.',
                ], 502);
            }

            /*
            |--------------------------------------------------------------------------
            | Convert JSON text to PHP array
            |--------------------------------------------------------------------------
            */

            $result = json_decode($responseText, true);

            if (!is_array($result)) {
                return response()->json([
                    'message' => 'Gemini returned invalid JSON.',
                ], 502);
            }

            /*
            |--------------------------------------------------------------------------
            | Save Gemini result to database
            |--------------------------------------------------------------------------
            */

            $analysis = $this->saveAnalysis(
                $request,
                $validated,
                $result
            );

            return response()->json([
                'message' => 'AI analysis completed successfully.',
                'source' => 'gemini',
                'analysis' => $analysis,
            ], 201);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            /*
            |--------------------------------------------------------------------------
            | Network/timeout fallback
            |--------------------------------------------------------------------------
            */

            $result = $this->mockAnalysis(
                $validated['resume_text'],
                $validated['job_description']
            );

            $analysis = $this->saveAnalysis(
                $request,
                $validated,
                $result
            );

            return response()->json([
                'message' => 'Gemini connection failed. Mock fallback analysis used.',
                'source' => 'mock_fallback',
                'analysis' => $analysis,
            ], 201);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save analysis result
    |--------------------------------------------------------------------------
    */

    private function saveAnalysis(
        Request $request,
        array $validated,
        array $result
    ): Analysis {
        return Analysis::create([
            'user_id' => $request->user()->id,

            'resume_text' => $validated['resume_text'],

            'job_description' => $validated['job_description'],

            'match_percentage' => $result['match_percentage'] ?? null,

            'matched_skills' => $result['matched_skills'] ?? [],

            'missing_skills' => $result['missing_skills'] ?? [],

            'strengths' => $result['strengths'] ?? [],

            'weaknesses' => $result['weaknesses'] ?? [],

            'recommendations' => $result['recommendations'] ?? [],

            'interview_questions' => $result['interview_questions'] ?? [],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Simple local fallback
    |--------------------------------------------------------------------------
    */

    private function mockAnalysis(
        string $resume,
        string $jobDescription
    ): array {
        $skills = [
            'PHP',
            'Laravel',
            'Node.js',
            'JavaScript',
            'HTML',
            'CSS',
            'Bootstrap',
            'REST APIs',
            'MySQL',
            'Git',
            'GitHub',
            'Java',
            'Spring Boot',
            'Python',
            'Postman',
        ];

        $resumeLower = strtolower($resume);
        $jobLower = strtolower($jobDescription);

        $matchedSkills = [];
        $missingSkills = [];
        $requiredSkills = [];

        foreach ($skills as $skill) {
            $skillLower = strtolower($skill);

            if (str_contains($jobLower, $skillLower)) {

                $requiredSkills[] = $skill;

                if (str_contains($resumeLower, $skillLower)) {
                    $matchedSkills[] = $skill;
                } else {
                    $missingSkills[] = $skill;
                }
            }
        }

        $matchPercentage = count($requiredSkills) > 0
            ? round(
                (count($matchedSkills) / count($requiredSkills)) * 100,
                2
            )
            : 0;

        return [
            'match_percentage' => $matchPercentage,

            'matched_skills' => $matchedSkills,

            'missing_skills' => $missingSkills,

            'strengths' => [
                !empty($matchedSkills)
                    ? 'Relevant skills found: ' . implode(', ', $matchedSkills)
                    : 'Limited direct skill matches were detected.',

                'The candidate has technical project experience that can be discussed during an interview.',
            ],

            'weaknesses' => [
                !empty($missingSkills)
                    ? 'Skills not currently mentioned in the resume: ' . implode(', ', $missingSkills)
                    : 'No major missing skills were detected by the fallback check.',
            ],

            'recommendations' => [
                !empty($missingSkills)
                    ? 'Consider learning or adding practical experience with: ' . implode(', ', $missingSkills)
                    : 'Continue strengthening the skills already listed in the resume.',

                'Add measurable project details to the resume.',

                'Prepare clear explanations of your technical projects for interviews.',
            ],

            'interview_questions' => [
                'Explain one of your main software projects.',
                'How have you used REST APIs in your projects?',
                'How do you work with MySQL in a web application?',
                'How do you debug an application when an error occurs?',
                'How do you use Git and GitHub during development?',
            ],
        ];
    }
}