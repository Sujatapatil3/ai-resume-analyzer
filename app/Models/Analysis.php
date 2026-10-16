<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'user_id',
        'resume_text',
        'job_description',
        'match_percentage',
        'matched_skills',
        'missing_skills',
        'strengths',
        'weaknesses',
        'recommendations',
        'interview_questions',
    ];

    protected $casts = [
        'matched_skills' => 'array',
        'missing_skills' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'recommendations' => 'array',
        'interview_questions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}