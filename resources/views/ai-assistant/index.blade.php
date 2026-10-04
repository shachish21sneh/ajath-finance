@extends('layouts.app')

@section('title', 'AI Business Assistant')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold"><i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>Fuzurra AI Business Assistant</h1>
            <p class="text-muted small mb-0">Ask natural language business questions in English or Hindi regarding sales, overdue debts, profits, stock, battery warranties, and solar projects.</p>
        </div>
        <div>
            <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                <i class="fa-solid fa-circle-dot me-1"></i> Model Active: Fuzurra ERP Intelligence
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chat Container -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 d-flex flex-column" style="height: 600px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white p-2 rounded-circle me-2">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Enterprise Analytics Copilot</div>
                            <small class="text-success"><i class="fa-solid fa-bolt me-1"></i>Connected to {{ $company->name }} Real-time Engine</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearChat()">
                        <i class="fa-solid fa-trash me-1"></i> Clear Chat
                    </button>
                </div>

                <!-- Message History -->
                <div class="card-body p-4 overflow-auto flex-grow-1" id="chatHistory" style="background-color: #f8fafc;">
                    <!-- Welcome AI message -->
                    <div class="d-flex mb-3">
                        <div class="bg-primary text-white p-2 rounded-circle me-2 align-self-start" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div class="bg-white p-3 rounded-3 shadow-sm border" style="max-width: 80%;">
                            <p class="mb-1 fw-bold text-dark">Hello! I am your Fuzurra ERP Business Copilot.</p>
                            <p class="small text-muted mb-0">I can analyze real-time sales numbers, overdue receivables, low inventory alerts, gross profit margins, battery warranties, and solar EPC project statuses. What would you like to investigate today?</p>
                        </div>
                    </div>
                </div>

                <!-- Chat Input Form -->
                <div class="card-footer bg-white p-3 border-top">
                    <form id="aiChatForm" class="input-group">
                        <input type="text" id="aiQueryInput" class="form-control form-control-lg border-primary" placeholder="Type a business question in English or Hindi (e.g. आज की बिक्री कितनी है?)..." autocomplete="off" required>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" id="sendBtn">
                            <i class="fa-solid fa-paper-plane me-1"></i> Ask AI
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Suggestions & Quick Queries -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Quick Inquiries (English & Hindi)</h6>
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('आज की बिक्री कितनी है?')">
                        <i class="fa-solid fa-chart-line text-primary me-2"></i> "आज की बिक्री कितनी है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('What is today\'s total sales revenue?')">
                        <i class="fa-solid fa-file-invoice-dollar text-success me-2"></i> "What is today's total sales revenue?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('किस customer का payment overdue है?')">
                        <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> "किस customer का payment overdue है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('Which customers have overdue payments?')">
                        <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> "Which customers have overdue payments?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('इस महीने का gross profit कितना है?')">
                        <i class="fa-solid fa-scale-balanced text-info me-2"></i> "इस महीने का gross profit कितना है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('कौन सा product low stock में है?')">
                        <i class="fa-solid fa-boxes-stacked text-danger me-2"></i> "कौन सा product low stock में है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('कौन सी battery warranty में expire होने वाली है?')">
                        <i class="fa-solid fa-car-battery text-danger me-2"></i> "कौन सी battery warranty में expire होने वाली है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('किस dealer की outstanding सबसे ज्यादा है?')">
                        <i class="fa-solid fa-users-viewfinder text-primary me-2"></i> "किस dealer की outstanding सबसे ज्यादा है?"
                    </button>
                    <button class="btn btn-outline-light text-dark text-start border p-2 small suggestion-btn" onclick="askQuestion('इस महीने कितने solar installations हुए?')">
                        <i class="fa-solid fa-solar-panel text-warning me-2"></i> "इस महीने कितने solar installations हुए?"
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-3 bg-light">
                <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="fa-solid fa-shield-halved me-1"></i>Audit & Financial Safety Rule</h6>
                <p class="small text-muted mb-0">Fuzurra AI provides read-only analytical intelligence and business intelligence reports. It adheres strictly to double-entry ledger security and never executes ledger modifications without authorized user authorization.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function askQuestion(q) {
        document.getElementById('aiQueryInput').value = q;
        document.getElementById('aiChatForm').dispatchEvent(new Event('submit'));
    }

    function clearChat() {
        const history = document.getElementById('chatHistory');
        history.innerHTML = `
            <div class="d-flex mb-3">
                <div class="bg-primary text-white p-2 rounded-circle me-2 align-self-start" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-white p-3 rounded-3 shadow-sm border" style="max-width: 80%;">
                    <p class="mb-0 small text-muted">Chat cleared. Ready for your next query.</p>
                </div>
            </div>
        `;
    }

    document.getElementById('aiChatForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const input = document.getElementById('aiQueryInput');
        const query = input.value.trim();
        if (!query) return;

        const history = document.getElementById('chatHistory');

        // Append User Bubble
        const userHtml = `
            <div class="d-flex justify-content-end mb-3">
                <div class="bg-primary text-white p-3 rounded-3 shadow-sm" style="max-width: 80%;">
                    <p class="mb-0">${escapeHtml(query)}</p>
                </div>
            </div>
        `;
        history.insertAdjacentHTML('beforeend', userHtml);
        input.value = '';
        history.scrollTop = history.scrollHeight;

        // Loading bubble
        const loadingId = 'loading-' + Date.now();
        const loadingHtml = `
            <div class="d-flex mb-3" id="${loadingId}">
                <div class="bg-primary text-white p-2 rounded-circle me-2 align-self-start" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div class="bg-white p-3 rounded-3 shadow-sm border text-muted small">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Analyzing transactions...
                </div>
            </div>
        `;
        history.insertAdjacentHTML('beforeend', loadingHtml);
        history.scrollTop = history.scrollHeight;

        try {
            const res = await fetch("{{ route('api.ai.query') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ query: query })
            });
            const data = await res.json();
            document.getElementById(loadingId).remove();

            const formattedReply = data.reply.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            const aiHtml = `
                <div class="d-flex mb-3">
                    <div class="bg-primary text-white p-2 rounded-circle me-2 align-self-start" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="bg-white p-3 rounded-3 shadow-sm border" style="max-width: 80%;">
                        <p class="mb-0 text-dark" style="line-height: 1.6;">${formattedReply}</p>
                    </div>
                </div>
            `;
            history.insertAdjacentHTML('beforeend', aiHtml);
            history.scrollTop = history.scrollHeight;
        } catch (err) {
            document.getElementById(loadingId).remove();
            alert('Failed to connect to AI Assistant. Please try again.');
        }
    });

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>
@endpush
