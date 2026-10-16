const API_BASE = '/api';

function getToken() {
    return localStorage.getItem('auth_token');
}

function getUser() {
    try {
        return JSON.parse(localStorage.getItem('auth_user') || 'null');
    } catch {
        return null;
    }
}

function saveAuth(data) {
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('auth_user', JSON.stringify(data.user));
}

function clearAuth() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    localStorage.removeItem('latest_analysis');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showAlert(id, message, type = 'danger') {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = `<div class="alert alert-${type}" role="alert">${escapeHtml(message)}</div>`;
}

function showList(id, items, emptyText = 'None found.') {
    const el = document.getElementById(id);
    if (!el) return;

    const safeItems = Array.isArray(items) ? items : [];
    if (!safeItems.length) {
        el.innerHTML = `<p class="text-secondary mb-0">${escapeHtml(emptyText)}</p>`;
        return;
    }

    el.innerHTML = safeItems.map(item => `<div class="mb-2">• ${escapeHtml(item)}</div>`).join('');
}

function showSkills(id, skills) {
    const el = document.getElementById(id);
    if (!el) return;

    const safeSkills = Array.isArray(skills) ? skills : [];
    el.innerHTML = safeSkills.length
        ? safeSkills.map(skill => `<span class="skill-badge">${escapeHtml(skill)}</span>`).join('')
        : '<p class="text-secondary mb-0">None detected.</p>';
}

async function apiRequest(url, options = {}) {
    const headers = {
        'Accept': 'application/json',
        ...(options.headers || {})
    };

    if (options.body && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
    }

    const token = getToken();
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE}${url}`, {
        ...options,
        headers
    });

    let data = null;
    try {
        data = await response.json();
    } catch {
        data = { message: 'Server returned an invalid response.' };
    }

    if (response.status === 401) {
        clearAuth();
        window.location.href = '/login';
        return;
    }

    if (!response.ok) {
        let message = data?.message || 'Request failed.';
        if (data?.errors) {
            const validationMessages = Object.values(data.errors).flat();
            if (validationMessages.length) {
                message = validationMessages.join(' ');
            }
        }
        const error = new Error(message);
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

function requireAuth() {
    if (!getToken()) {
        window.location.href = '/login';
        return false;
    }
    return true;
}

async function logout() {
    const token = getToken();

    try {
        if (token) {
            await fetch(`${API_BASE}/logout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
        }
    } finally {
        clearAuth();
        window.location.href = '/login';
    }
}

function wireLogoutButtons() {
    document.querySelectorAll('.logout-btn').forEach(button => {
        button.addEventListener('click', logout);
    });
}

async function initLogin() {
    if (getToken()) {
        window.location.href = '/dashboard';
        return;
    }

    const form = document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const button = document.getElementById('submit-btn');
        button.disabled = true;
        button.textContent = 'Logging in...';

        try {
            const data = await apiRequest('/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value
                })
            });

            saveAuth(data);
            window.location.href = '/dashboard';
        } catch (error) {
            showAlert('form-alert', error.message);
        } finally {
            button.disabled = false;
            button.textContent = 'Login';
        }
    });
}

async function initRegister() {
    if (getToken()) {
        window.location.href = '/dashboard';
        return;
    }

    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;

        if (password !== confirmation) {
            showAlert('form-alert', 'Passwords do not match.');
            return;
        }

        const button = document.getElementById('submit-btn');
        button.disabled = true;
        button.textContent = 'Creating...';

        try {
            const data = await apiRequest('/register', {
                method: 'POST',
                body: JSON.stringify({
                    name: document.getElementById('name').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    password,
                    password_confirmation: confirmation
                })
            });

            saveAuth(data);
            window.location.href = '/dashboard';
        } catch (error) {
            showAlert('form-alert', error.message);
        } finally {
            button.disabled = false;
            button.textContent = 'Create account';
        }
    });
}

async function initDashboard() {
    if (!requireAuth()) return;

    const user = getUser();
    if (user) {
        document.getElementById('user-name').textContent = user.name || '-';
        document.getElementById('user-email').textContent = user.email || '-';
    }
}

async function initAnalyzer() {
    if (!requireAuth()) return;

    const form = document.getElementById('analyzer-form');
    if (!form) return;

    form.addEventListener('submit', async event => {
        event.preventDefault();

        const resumeText = document.getElementById('resume_text').value.trim();
        const jobDescription = document.getElementById('job_description').value.trim();

        if (!resumeText || !jobDescription) {
            showAlert('form-alert', 'Please provide both your resume and the job description.');
            return;
        }

        const button = document.getElementById('analyze-btn');
        button.disabled = true;
        button.textContent = 'Analyzing...';

        try {
            const data = await apiRequest('/analyze', {
                method: 'POST',
                body: JSON.stringify({
                    resume_text: resumeText,
                    job_description: jobDescription
                })
            });

            localStorage.setItem('latest_analysis', JSON.stringify({
                source: data.source || 'unknown',
                analysis: data.analysis
            }));

            window.location.href = '/result';
        } catch (error) {
            showAlert('form-alert', error.message);
        } finally {
            button.disabled = false;
            button.textContent = 'Analyze Resume';
        }
    });
}

function renderResult(analysis, source) {
    document.getElementById('result-content').classList.remove('d-none');
    document.getElementById('analysis-source').textContent =
        source === 'gemini' ? 'Generated by Gemini AI' : 'Generated by the clearly labelled local fallback';

    document.getElementById('match-percentage').textContent =
        `${Number(analysis.match_percentage ?? 0).toFixed(2)}%`;

    showSkills('matched-skills', analysis.matched_skills);
    showSkills('missing-skills', analysis.missing_skills);
    showList('strengths', analysis.strengths);
    showList('weaknesses', analysis.weaknesses);
    showList('recommendations', analysis.recommendations);
    showList('interview-questions', analysis.interview_questions);

    document.getElementById('resume-output').textContent = analysis.resume_text || '';
    document.getElementById('jd-output').textContent = analysis.job_description || '';
}

async function initResult() {
    if (!requireAuth()) return;

    const saved = localStorage.getItem('latest_analysis');
    if (!saved) {
        showAlert('result-alert', 'No analysis result is available. Start a new analysis.');
        return;
    }

    try {
        const payload = JSON.parse(saved);
        if (!payload.analysis) throw new Error('Invalid analysis data.');
        renderResult(payload.analysis, payload.source);
    } catch {
        showAlert('result-alert', 'Could not load the analysis result.');
    }
}

async function viewAnalysis(id) {
    try {
        const data = await apiRequest(`/analyses/${id}`);
        localStorage.setItem('latest_analysis', JSON.stringify({
            source: 'history',
            analysis: data.analysis
        }));
        window.location.href = '/result';
    } catch (error) {
        showAlert('history-alert', error.message);
    }
}

async function deleteAnalysis(id) {
    if (!confirm('Delete this analysis?')) return;

    try {
        await apiRequest(`/analyses/${id}`, { method: 'DELETE' });
        await loadHistory();
    } catch (error) {
        showAlert('history-alert', error.message);
    }
}

async function loadHistory() {
    const container = document.getElementById('history-list');
    if (!container) return;

    container.innerHTML = '<div class="text-center py-5 text-secondary">Loading history...</div>';

    try {
        const data = await apiRequest('/analyses');
        const analyses = data.analyses || [];

        if (!analyses.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <h2 class="h5 fw-bold">No analyses yet</h2>
                    <p class="text-secondary">Run your first resume analysis to see it here.</p>
                    <a href="/analyzer" class="btn btn-primary">Analyze Resume</a>
                </div>`;
            return;
        }

        container.innerHTML = analyses.map(analysis => `
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <span class="badge text-bg-primary">${Number(analysis.match_percentage ?? 0).toFixed(2)}%</span>
                                <span class="text-secondary small">${escapeHtml(new Date(analysis.created_at).toLocaleString())}</span>
                            </div>
                            <p class="mb-1"><strong>Job description:</strong></p>
                            <p class="text-secondary mb-0">${escapeHtml((analysis.job_description || '').slice(0, 240))}${(analysis.job_description || '').length > 240 ? '...' : ''}</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <button class="btn btn-outline-primary btn-sm me-1 view-analysis" data-id="${analysis.id}">View</button>
                            <button class="btn btn-outline-danger btn-sm delete-analysis" data-id="${analysis.id}">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        container.querySelectorAll('.view-analysis').forEach(button => {
            button.addEventListener('click', () => viewAnalysis(button.dataset.id));
        });

        container.querySelectorAll('.delete-analysis').forEach(button => {
            button.addEventListener('click', () => deleteAnalysis(button.dataset.id));
        });
    } catch (error) {
        showAlert('history-alert', error.message);
        container.innerHTML = '';
    }
}

async function initHistory() {
    if (!requireAuth()) return;
    await loadHistory();
}

async function init() {
    wireLogoutButtons();

    const page = document.body.dataset.page;

    switch (page) {
        case 'login':
            await initLogin();
            break;
        case 'register':
            await initRegister();
            break;
        case 'dashboard':
            await initDashboard();
            break;
        case 'analyzer':
            await initAnalyzer();
            break;
        case 'result':
            await initResult();
            break;
        case 'history':
            await initHistory();
            break;
    }
}

document.addEventListener('DOMContentLoaded', init);
