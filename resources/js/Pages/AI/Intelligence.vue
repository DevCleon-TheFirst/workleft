<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const loadingCrypto   = ref(true);
const loadingNews     = ref(true);
const loadingWorkflow = ref(true);

const cryptoData   = ref([]);
const newsData     = ref([]);
const workflowData = ref(null);

const errorCrypto   = ref(false);
const errorNews     = ref(false);
const errorWorkflow = ref(false);

async function fetchCrypto() {
    loadingCrypto.value = true;
    errorCrypto.value   = false;
    try {
        const { data } = await axios.get(route('ai.intelligence.crypto'));
        cryptoData.value = data.crypto ?? [];
    } catch { errorCrypto.value = true; }
    finally { loadingCrypto.value = false; }
}

async function fetchNews() {
    loadingNews.value = true;
    errorNews.value   = false;
    try {
        const { data } = await axios.get(route('ai.intelligence.news'));
        newsData.value = data.news ?? [];
    } catch { errorNews.value = true; }
    finally { loadingNews.value = false; }
}

async function fetchWorkflow() {
    loadingWorkflow.value = true;
    errorWorkflow.value   = false;
    try {
        const { data } = await axios.get(route('ai.intelligence.workflow'));
        workflowData.value = data.workflow ?? null;
    } catch { errorWorkflow.value = true; }
    finally { loadingWorkflow.value = false; }
}

function fetchAll() {
    fetchCrypto();
    fetchNews();
    fetchWorkflow();
}

onMounted(fetchAll);

function signalClass(signal) {
    if (signal === 'BUY')  return { badge: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30', dot: 'bg-emerald-400' };
    if (signal === 'HOLD') return { badge: 'text-amber-400 bg-amber-500/10 border-amber-500/30',   dot: 'bg-amber-400'   };
    return                        { badge: 'text-sky-400 bg-sky-500/10 border-sky-500/30',           dot: 'bg-sky-400'     };
}
</script>

<template>
    <Head title="AI Intelligence Hub" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-9 h-9 rounded-xl bg-brand-600/20 border border-brand-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-100">AI Intelligence Hub</h1>
                    </div>
                    <p class="text-sm text-gray-500 pl-12">Real-time market analysis, tech news, and personalized workflow coaching.</p>
                </div>
                <button @click="fetchAll"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-200 rounded-lg transition-colors text-sm font-medium shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Refresh All
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left: Workflow + News -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Workflow Coach -->
                    <div class="bg-gray-800/60 border border-gray-700 rounded-2xl overflow-hidden">
                        <div class="h-0.5 bg-gradient-to-r from-brand-500 via-purple-500 to-pink-500"></div>
                        <div class="p-6">
                            <h2 class="text-base font-semibold text-gray-100 flex items-center gap-2.5 mb-5">
                                <svg class="w-5 h-5 text-brand-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                                </svg>
                                Personal Workflow Coach
                            </h2>

                            <!-- Loading -->
                            <div v-if="loadingWorkflow" class="animate-pulse space-y-3">
                                <div class="h-4 bg-gray-700 rounded w-3/4"></div>
                                <div class="h-4 bg-gray-700 rounded w-1/2"></div>
                                <div class="h-4 bg-gray-700 rounded w-2/3"></div>
                            </div>

                            <!-- Error -->
                            <div v-else-if="errorWorkflow" class="flex flex-col items-center gap-3 py-6 text-center">
                                <svg class="w-8 h-8 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Failed to load workflow analysis.</p>
                                <button @click="fetchWorkflow" class="text-xs text-brand-400 hover:text-brand-300 underline">Retry</button>
                            </div>

                            <!-- Data -->
                            <div v-else-if="workflowData" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-xs font-semibold text-brand-400 uppercase tracking-wider mb-3">Actionable Tips</p>
                                    <ul class="space-y-2.5">
                                        <li v-for="(tip, i) in workflowData.advice" :key="i" class="flex items-start gap-2.5">
                                            <div class="mt-1.5 shrink-0 w-1.5 h-1.5 rounded-full bg-brand-500"></div>
                                            <span class="text-sm text-gray-300 leading-relaxed">{{ tip }}</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs font-semibold text-purple-400 uppercase tracking-wider mb-3">Recommended to Learn</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="(skill, i) in workflowData.learning" :key="i"
                                                class="px-3 py-1 bg-purple-500/10 border border-purple-500/20 text-purple-300 rounded-lg text-xs font-medium">
                                                {{ skill }}
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="workflowData.stats?.rate !== undefined" class="p-4 bg-gray-900/60 rounded-xl border border-gray-700/50">
                                        <div class="flex justify-between items-center text-sm mb-2">
                                            <span class="text-gray-400 text-xs">Task Completion Rate</span>
                                            <span class="font-bold text-gray-200">{{ workflowData.stats.rate }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-700 rounded-full h-1.5">
                                            <div class="bg-gradient-to-r from-brand-500 to-purple-500 h-1.5 rounded-full transition-all duration-700"
                                                :style="`width: ${workflowData.stats.rate}%`"></div>
                                        </div>
                                        <div class="flex justify-between text-[11px] text-gray-600 mt-1.5">
                                            <span>{{ workflowData.stats.completed }} done</span>
                                            <span>{{ workflowData.stats.overdue }} overdue</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No data -->
                            <p v-else class="text-sm text-gray-500">No workflow data. Create some tasks to get personalized coaching.</p>
                        </div>
                    </div>

                    <!-- Tech News -->
                    <div class="bg-gray-800/60 border border-gray-700 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-base font-semibold text-gray-100 flex items-center gap-2.5">
                                <svg class="w-5 h-5 text-orange-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z"/>
                                </svg>
                                Trending Tech News
                            </h2>
                            <button @click="fetchNews" class="text-xs text-gray-500 hover:text-gray-300 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Refresh
                            </button>
                        </div>

                        <!-- Loading -->
                        <div v-if="loadingNews" class="animate-pulse space-y-5">
                            <div v-for="i in 3" :key="i" class="space-y-2">
                                <div class="h-4 bg-gray-700 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-700/60 rounded w-full"></div>
                                <div class="h-3 bg-gray-700/40 rounded w-5/6"></div>
                            </div>
                        </div>

                        <!-- Error -->
                        <div v-else-if="errorNews" class="flex flex-col items-center gap-3 py-6 text-center">
                            <svg class="w-8 h-8 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Could not fetch news.</p>
                            <button @click="fetchNews" class="text-xs text-brand-400 hover:text-brand-300 underline">Retry</button>
                        </div>

                        <!-- Data -->
                        <div v-else class="divide-y divide-gray-700/50">
                            <div v-if="newsData.length === 0" class="py-8 text-center">
                                <p class="text-sm text-gray-500">No stories surfaced right now. Try refreshing.</p>
                            </div>
                            <div v-for="(article, i) in newsData" :key="i" class="py-4 first:pt-0 last:pb-0">
                                <a :href="article.url" target="_blank" rel="noopener noreferrer" class="group block">
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="text-sm font-medium text-gray-200 group-hover:text-brand-400 transition-colors leading-snug">
                                            {{ article.title }}
                                        </h3>
                                        <svg class="w-3.5 h-3.5 text-gray-600 group-hover:text-brand-400 shrink-0 mt-0.5 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed border-l-2 border-brand-500/40 pl-3">
                                        <span class="text-gray-400 font-semibold">AI:</span> {{ article.insight }}
                                    </p>
                                    <div v-if="article.score" class="flex items-center gap-1 mt-1.5">
                                        <svg class="w-3 h-3 text-orange-400" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-[11px] text-gray-600">{{ article.score }} points on HN</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Crypto -->
                <div>
                    <div class="bg-gray-800/60 border border-gray-700 rounded-2xl overflow-hidden sticky top-6">
                        <div class="h-0.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-sky-500"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="text-base font-semibold text-gray-100 flex items-center gap-2.5">
                                    <svg class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                                    </svg>
                                    Crypto AI Signals
                                </h2>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-[11px] text-gray-500">Live</span>
                                </div>
                            </div>

                            <!-- Loading -->
                            <div v-if="loadingCrypto" class="animate-pulse space-y-3">
                                <div v-for="i in 5" :key="i" class="h-20 bg-gray-700/60 rounded-xl"></div>
                            </div>

                            <!-- Error -->
                            <div v-else-if="errorCrypto" class="flex flex-col items-center gap-3 py-8 text-center">
                                <svg class="w-8 h-8 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Crypto data unavailable.</p>
                                <button @click="fetchCrypto" class="text-xs text-brand-400 hover:text-brand-300 underline">Retry</button>
                            </div>

                            <!-- Data -->
                            <div v-else class="space-y-3">
                                <div v-if="cryptoData.length === 0" class="py-6 text-center text-sm text-gray-500">No data available.</div>

                                <div v-for="coin in cryptoData" :key="coin.id"
                                    class="p-4 bg-gray-900/60 rounded-xl border border-gray-700/60 hover:border-gray-600 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <h3 class="text-sm font-bold text-gray-200">{{ coin.name }}</h3>
                                                <span class="text-[10px] text-gray-500 font-mono uppercase">{{ coin.symbol }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-base font-mono text-gray-100">${{ Number(coin.price).toLocaleString() }}</span>
                                                <span :class="['text-[11px] font-bold px-1.5 py-0.5 rounded', coin.change_24h >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400']">
                                                    {{ coin.change_24h > 0 ? '+' : '' }}{{ coin.change_24h }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div :class="['flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border', signalClass(coin.signal).badge]">
                                            <div :class="['w-1.5 h-1.5 rounded-full', signalClass(coin.signal).dot]"></div>
                                            {{ coin.signal }}
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-snug">{{ coin.reason }}</p>
                                </div>

                                <p class="text-[11px] text-gray-700 text-center pt-2">
                                    AI signals based on 7-day trends. Not financial advice.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
