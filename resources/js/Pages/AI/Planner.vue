<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MermaidDiagram from '@/Components/MermaidDiagram.vue';

// -----------------------------------------------------------------------------
// State Management
// -----------------------------------------------------------------------------
const uiState = ref('input'); // 'input' | 'running' | 'deliverables'
const projectDescription = ref('');
const isProcessing = ref(false);

const agentLog = ref([]);
const deliverable = ref(null);
const currentActiveAgent = ref(null);
const activeTab = ref(0);

// Persistence
const savedBlueprints = ref([]);
const currentBlueprintId = ref(null);

// For scrolling the log
const feedContainer = ref(null);

const tabs = [
    { name: 'Architecture', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'UML Class Diagram', icon: 'M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5' },
    { name: 'Database Schema', icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' },
    { name: 'API Contracts', icon: 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { name: 'Tech Stack', icon: 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01' },
    { name: 'DB Perf Report', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
    { name: 'Security Report', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    { name: 'Future Features', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
];

const totalAgents = 7;

// -----------------------------------------------------------------------------
// Methods
// -----------------------------------------------------------------------------

onMounted(() => {
    loadBlueprints();
});

const loadBlueprints = async () => {
    try {
        const response = await fetch(window.route('ai.blueprints.index'));
        if (response.ok) {
            savedBlueprints.value = await response.json();
        }
    } catch (e) {
        console.error("Failed to load blueprints", e);
    }
};

const loadSavedBlueprint = async (id) => {
    try {
        const response = await fetch(window.route('ai.blueprints.show', id));
        if (response.ok) {
            const bp = await response.json();
            currentBlueprintId.value = bp.id;
            projectDescription.value = bp.raw_description;
            agentLog.value = bp.agent_log;
            deliverable.value = bp.deliverable;
            uiState.value = 'deliverables';
            activeTab.value = 0;
        }
    } catch (e) {
        console.error("Failed to load blueprint details", e);
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (feedContainer.value) {
            feedContainer.value.scrollTop = feedContainer.value.scrollHeight;
        }
    });
};

const launchWarRoom = () => {
    if (!projectDescription.value.trim() || isProcessing.value) return;

    uiState.value = 'running';
    isProcessing.value = true;
    agentLog.value = [];
    deliverable.value = null;
    currentActiveAgent.value = 1;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Construct the URL with query params for the GET request, or use fetch if it was POST. 
    // EventSource only supports GET. Let's assume we change it to GET or we use fetch to POST and process streams.
    // Since the backend uses StreamedResponse, we can use the fetch API to read the stream for POST.
    
    fetch(window.route('ai.analyze'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'text/event-stream'
        },
        body: JSON.stringify({ description: projectDescription.value })
    })
    .then(async response => {
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            
            let lines = buffer.split('\n\n');
            buffer = lines.pop(); // keep incomplete part in buffer
            
            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    const dataStr = line.substring(6);
                    if (dataStr === '[DONE]') {
                        isProcessing.value = false;
                        currentActiveAgent.value = null;
                        uiState.value = 'deliverables';
                        break;
                    } else {
                        try {
                            const data = JSON.parse(dataStr);
                            agentLog.value.push(data);
                            currentActiveAgent.value = Math.min(data.agent + 1, totalAgents);
                            
                            if (data.agent === totalAgents && data.output) {
                                deliverable.value = JSON.parse(data.output);
                            }
                            scrollToBottom();
                        } catch (e) {
                            console.error("Failed to parse SSE data", e, dataStr);
                        }
                    }
                }
            }
        }
    })
    .catch(err => {
        console.error("Stream error", err);
        isProcessing.value = false;
        alert("An error occurred during analysis.");
        uiState.value = 'input';
    });
};

const saveBlueprint = async () => {
    try {
        const isUpdate = !!currentBlueprintId.value;
        const url = isUpdate 
            ? window.route('ai.blueprints.update', currentBlueprintId.value)
            : window.route('ai.blueprints.store');
            
        const method = isUpdate ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                raw_description: projectDescription.value,
                agent_log: agentLog.value,
                deliverable: deliverable.value
            })
        });

        if (response.ok) {
            alert('Blueprint saved successfully!');
            const savedData = await response.json();
            currentBlueprintId.value = savedData.id;
            loadBlueprints();
        } else {
            alert('Failed to save blueprint.');
        }
    } catch (e) {
        console.error(e);
        alert('Error saving blueprint.');
    }
};

const requestChanges = () => {
    uiState.value = 'input';
};

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
const getAgentClasses = (model) => {
    return model === 'drafter' 
        ? 'border-brand-500 bg-brand-900/20 text-brand-300' 
        : 'border-purple-500 bg-purple-900/20 text-purple-300';
};

const cleanMermaid = (str) => {
    if (!str) return '';
    return str.replace(/```mermaid\n?/g, '').replace(/```/g, '').trim();
};

const formatCategoryColor = (category) => {
    const text = (category || '').toLowerCase();
    if (text.includes('critical') || text.includes('high')) return 'text-red-400 bg-red-900/30 border-red-700';
    if (text.includes('medium') || text.includes('warning')) return 'text-amber-400 bg-amber-900/30 border-amber-700';
    return 'text-blue-400 bg-blue-900/30 border-blue-700';
};

</script>

<template>
    <Head title="Architect Studio" />

    <AuthenticatedLayout>
        <div class="p-6 space-y-6 min-h-[calc(100vh-65px)] flex flex-col">
            
            <!-- Header -->
            <div class="pb-4 border-b border-gray-800 flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-2">
                    <svg class="w-6 h-6 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    AI Architect Studio
                </h2>
                <div v-if="uiState === 'running'" class="flex items-center gap-2 text-sm text-gray-400">
                    <span class="animate-pulse w-2 h-2 rounded-full bg-emerald-500"></span>
                    War Room Active
                </div>
            </div>

            <!-- STATE 1: INPUT -->
            <div v-if="uiState === 'input'" class="flex-1 flex gap-8 w-full py-8">
                <!-- Sidebar: History -->
                <div class="w-80 flex-shrink-0 bg-gray-800 border border-gray-700 rounded-xl flex flex-col overflow-hidden hidden md:flex">
                    <div class="p-4 border-b border-gray-700 bg-gray-900/50">
                        <h3 class="font-bold text-gray-200">My Blueprints</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2 space-y-2">
                        <div v-if="savedBlueprints.length === 0" class="text-sm text-gray-500 p-4 text-center">
                            No saved blueprints yet.
                        </div>
                        <button v-for="bp in savedBlueprints" :key="bp.id" 
                            @click="loadSavedBlueprint(bp.id)"
                            class="w-full text-left p-3 rounded-lg hover:bg-gray-700/50 transition-colors border border-transparent hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <div class="font-semibold text-gray-200 truncate">{{ bp.title || 'Untitled' }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ new Date(bp.created_at).toLocaleDateString() }}</div>
                        </button>
                    </div>
                </div>

                <!-- Main Input -->
                <div class="flex-1">
                    <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-xl p-8 space-y-6">
                        <div class="text-center space-y-2 mb-8">
                            <h3 class="text-2xl font-bold text-gray-100">Describe Your Vision</h3>
                            <p class="text-gray-400">The Master Architect will generate a full architectural system design instantly.</p>
                        </div>
                        
                        <textarea
                            v-model="projectDescription"
                            class="w-full h-64 bg-gray-900 border-gray-700 rounded-lg text-gray-100 focus:ring-brand-500 focus:border-brand-500 p-4 font-mono text-sm shadow-inner resize-none"
                            placeholder="Describe your project here... e.g. A marketplace for freelance designers. Needs user auth, Stripe integration, video streaming profiles, and an admin dashboard."
                        ></textarea>

                        <div class="flex justify-end gap-3">
                            <SecondaryButton @click="() => { projectDescription = ''; currentBlueprintId = null; }" :disabled="isProcessing">
                                Clear
                            </SecondaryButton>
                            <PrimaryButton @click="launchWarRoom" :disabled="!projectDescription.trim() || isProcessing" class="px-8 py-3 text-lg">
                                🚀 Master Architect &rarr;
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATE 2: ARCHITECTING (LOADING) -->
            <div v-if="uiState === 'running'" class="flex-1 flex flex-col justify-center items-center max-w-4xl mx-auto w-full py-12">
                <div class="bg-gray-800/80 border border-brand-500/30 rounded-xl shadow-[0_0_50px_rgba(99,102,241,0.15)] p-12 flex flex-col items-center text-center space-y-6 w-full max-w-lg">
                    <div class="relative w-24 h-24 flex items-center justify-center">
                        <div class="absolute inset-0 border-4 border-gray-700 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-brand-500 rounded-full border-t-transparent animate-spin"></div>
                        <div class="text-4xl">🧠</div>
                    </div>
                    
                    <div>
                        <h3 class="text-2xl font-bold text-gray-100 mb-2">Master Architect is Designing...</h3>
                        <p class="text-gray-400 text-sm">
                            Analyzing requirements, structuring databases, performing security audits, and drafting UML diagrams. This usually takes 15-30 seconds.
                        </p>
                    </div>

                    <div v-if="agentLog.length > 0" class="w-full bg-gray-900 rounded p-3 text-xs text-brand-400 font-mono text-left animate-pulse border border-brand-900">
                        > {{ agentLog[agentLog.length - 1].excerpt }}
                    </div>
                </div>
            </div>

            <!-- STATE 3: DELIVERABLES -->
            <div v-if="uiState === 'deliverables' && deliverable" class="flex-1 flex flex-col overflow-hidden">
                
                <div v-if="deliverable.error" class="bg-red-900/30 border border-red-700 p-6 rounded-xl overflow-y-auto">
                    <h2 class="text-xl font-semibold text-red-400 mb-2">⚠️ Master Architect failed to produce valid JSON</h2>
                    <p class="text-red-300 mb-4" v-if="deliverable.json_error">
                        JSON Error: {{ deliverable.json_error }}
                    </p>
                    <p class="text-red-300 mb-4">Here is the raw output:</p>
                    <pre class="bg-gray-800 p-4 rounded text-sm text-gray-300 overflow-x-auto whitespace-pre-wrap">{{ deliverable.raw }}</pre>
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="requestChanges">Try Again</SecondaryButton>
                    </div>
                </div>

                <template v-else>
                    <div class="flex justify-between items-end mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-100">{{ deliverable.project_title || 'Blueprint' }}</h1>
                        <p class="text-gray-400 mt-1 max-w-3xl">{{ deliverable.executive_summary }}</p>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton @click="requestChanges">✏️ Request Changes</SecondaryButton>
                        <PrimaryButton @click="saveBlueprint">✅ Approve & Save</PrimaryButton>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-700 overflow-x-auto flex flex-nowrap">
                    <nav class="-mb-px flex space-x-1" aria-label="Tabs">
                        <button v-for="(tab, index) in tabs" :key="tab.name"
                            @click="activeTab = index"
                            class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors"
                            :class="[
                                activeTab === index
                                    ? 'border-brand-500 text-brand-400 bg-brand-500/10'
                                    : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-500 hover:bg-gray-800/50'
                            ]"
                        >
                            <svg class="w-4 h-4" :class="activeTab === index ? 'text-brand-500' : 'text-gray-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                            </svg>
                            {{ tab.name }}
                            <span v-if="tab.name === 'Security Report'" class="ml-1 flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                <div class="flex-1 overflow-y-auto bg-gray-900/50 rounded-b-xl border border-t-0 border-gray-700 p-6">
                    
                    <!-- Architecture -->
                    <div v-show="activeTab === 0" class="space-y-6">
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-2">Pattern: {{ deliverable.architecture_pattern }}</h3>
                            <MermaidDiagram :chart="cleanMermaid(deliverable.architecture_mermaid)" id="mermaid-arch" />
                        </div>
                    </div>

                    <!-- UML -->
                    <div v-show="activeTab === 1">
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                            <MermaidDiagram :chart="cleanMermaid(deliverable.uml_mermaid)" id="mermaid-uml" />
                        </div>
                    </div>

                    <!-- Schema -->
                    <div v-show="activeTab === 2">
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
                            <MermaidDiagram :chart="cleanMermaid(deliverable.erd_mermaid)" id="mermaid-erd" />
                        </div>
                    </div>

                    <!-- API Contracts -->
                    <div v-show="activeTab === 3" class="space-y-4">
                        <div v-for="api in deliverable.api_contracts" :key="api.endpoint" class="bg-gray-800 border border-gray-700 rounded-lg p-4 overflow-x-auto">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="px-2 py-1 rounded text-xs font-bold font-mono"
                                      :class="{'bg-emerald-900/50 text-emerald-400': api.method === 'GET', 'bg-blue-900/50 text-blue-400': api.method === 'POST', 'bg-amber-900/50 text-amber-400': api.method === 'PUT' || api.method === 'PATCH', 'bg-red-900/50 text-red-400': api.method === 'DELETE'}">
                                    {{ api.method }}
                                </span>
                                <span class="font-mono text-gray-200">{{ api.endpoint }}</span>
                            </div>
                            <p class="text-sm text-gray-400 mb-4">{{ api.description }}</p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                                <div class="bg-gray-900 rounded p-3 border border-gray-700">
                                    <div class="text-gray-500 uppercase font-semibold mb-1">Auth</div>
                                    <div class="text-gray-300">{{ api.auth_required ? 'Required' : 'Public' }} ({{ (api.roles || []).join(', ') }})</div>
                                </div>
                                <div class="bg-gray-900 rounded p-3 border border-gray-700">
                                    <div class="text-gray-500 uppercase font-semibold mb-1">Idempotency</div>
                                    <div class="text-gray-300 font-mono">{{ api.idempotency }}</div>
                                </div>
                                <div class="bg-gray-900 rounded p-3 border border-gray-700">
                                    <div class="text-gray-500 uppercase font-semibold mb-1">Rate Limit</div>
                                    <div class="text-gray-300">{{ api.rate_limit }}</div>
                                </div>
                                <div class="bg-gray-900 rounded p-3 border border-gray-700">
                                    <div class="text-gray-500 uppercase font-semibold mb-1">Req / Res</div>
                                    <details>
                                        <summary class="cursor-pointer text-brand-400 hover:text-brand-300">View payload</summary>
                                        <div class="mt-2 text-gray-400 font-mono">
                                            Req: {{ api.request_body }}<br>Res: {{ api.response }}
                                        </div>
                                    </details>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tech Stack -->
                    <div v-show="activeTab === 4" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4 col-span-full">
                            <h3 class="text-lg font-medium text-gray-200 border-b border-gray-700 pb-2">Rationale</h3>
                            <p class="text-gray-400 leading-relaxed">{{ deliverable.tech_stack?.rationale }}</p>
                        </div>
                        
                        <div v-for="(val, key) in deliverable.tech_stack" :key="key" 
                             v-show="key !== 'rationale'"
                             class="bg-gray-800 border border-gray-700 rounded-lg p-5 flex items-start gap-4">
                            <div class="p-3 rounded-lg bg-gray-900 border border-gray-700">
                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ key.replace('_', ' ') }}</h4>
                                <div class="text-gray-100 font-medium">{{ val }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- DB Perf Report -->
                    <div v-show="activeTab === 5" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- N+1 Risks -->
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
                                <h3 class="text-lg font-medium text-amber-400 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    N+1 Query Risks
                                </h3>
                                <ul class="space-y-4">
                                    <li v-for="risk in deliverable.db_performance_report?.n_plus_one_risks" :key="risk.relationship" class="text-sm border-l-2 border-amber-500 pl-3">
                                        <div class="text-gray-300 font-mono mb-1">{{ risk.table }} &rarr; {{ risk.relationship }}</div>
                                        <div class="text-gray-500">Fix: <span class="text-gray-400 font-mono">{{ risk.fix }}</span></div>
                                    </li>
                                </ul>
                            </div>
                            <!-- Missing Indexes -->
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-5">
                                <h3 class="text-lg font-medium text-amber-400 mb-4">Missing Indexes</h3>
                                <ul class="space-y-4">
                                    <li v-for="idx in deliverable.db_performance_report?.missing_indexes" :key="idx.column" class="text-sm border-l-2 border-amber-500 pl-3">
                                        <div class="text-gray-300 font-mono mb-1">{{ idx.table }}.{{ idx.column }} <span class="text-xs text-gray-500 bg-gray-900 px-1 rounded">{{ idx.type }}</span></div>
                                        <div class="text-gray-500">{{ idx.reason }}</div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Query Budgets -->
                        <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Endpoint</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Naive Queries</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Optimization Strategy</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="budget in deliverable.db_performance_report?.query_budgets" :key="budget.endpoint">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-300">{{ budget.endpoint }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-400 font-bold">{{ budget.naive_query_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-400 font-bold">&le; {{ budget.budget }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-400">{{ budget.fix }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Security Report -->
                    <div v-show="activeTab === 6" class="space-y-6">
                        <div v-for="(items, severity) in deliverable.security_report" :key="severity">
                            <div v-if="items && items.length > 0" class="mb-8">
                                <h3 class="text-lg font-bold uppercase tracking-wider mb-4 flex items-center gap-2"
                                    :class="{'text-red-500': severity==='critical', 'text-amber-500': severity==='high', 'text-blue-500': severity==='medium'}">
                                    <span class="w-3 h-3 rounded-full" :class="{'bg-red-500': severity==='critical', 'bg-amber-500': severity==='high', 'bg-blue-500': severity==='medium'}"></span>
                                    {{ severity }} Risks
                                </h3>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div v-for="risk in items" :key="risk.description" 
                                         class="rounded-lg border p-5 flex flex-col h-full bg-gray-800"
                                         :class="formatCategoryColor(severity)">
                                        <div class="text-xs font-bold uppercase tracking-widest opacity-70 mb-2">{{ risk.category }}</div>
                                        <div class="text-sm font-medium mb-3 text-gray-200">{{ risk.description }}</div>
                                        
                                        <div class="mt-auto space-y-3">
                                            <div class="bg-gray-900/50 rounded p-3 border border-current/20">
                                                <div class="text-xs font-bold uppercase opacity-60 mb-1">Impact</div>
                                                <div class="text-sm text-gray-300">{{ risk.impact }}</div>
                                            </div>
                                            <div class="bg-gray-900/50 rounded p-3 border border-current/20">
                                                <div class="text-xs font-bold uppercase opacity-60 mb-1 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                    Required Mitigation
                                                </div>
                                                <div class="text-sm text-gray-300 font-mono">{{ risk.mitigation }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Future Features -->
                    <div v-show="activeTab === 7" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="feature in deliverable.future_features" :key="feature.title" class="bg-gray-800 border border-brand-500/30 rounded-lg p-6 hover:border-brand-500 transition-colors">
                                <h3 class="text-lg font-bold text-brand-400 mb-2 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ feature.title }}
                                </h3>
                                <p class="text-gray-300 text-sm mb-4 leading-relaxed">{{ feature.description }}</p>
                                <div class="bg-brand-900/20 border border-brand-700/50 rounded p-3">
                                    <div class="text-xs font-bold uppercase tracking-widest text-brand-500/70 mb-1">Business Value</div>
                                    <div class="text-brand-300 text-sm font-medium">{{ feature.business_value }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                </template>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
