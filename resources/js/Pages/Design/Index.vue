<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    blueprints: Array
});

function formatDate(dateString) {
    if (!dateString) return '';
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    }).format(new Date(dateString));
}
</script>

<template>
    <Head title="UI/UX Design Studio" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-brand-500">
                        <path fill-rule="evenodd" d="M2.04 8.618A12 12 0 0 1 11.25 3h1.5a12 12 0 0 1 9.21 4.382l-.46.46a3 3 0 0 0 0 4.243l3.24 3.24A1.5 1.5 0 0 1 23.68 18c0 .828-.672 1.5-1.5 1.5H3C1.343 19.5 0 18.157 0 16.5c0-.828.672-1.5 1.5-1.5l3.24-3.24a3 3 0 0 0 0-4.243l-.46-.46a11.96 11.96 0 0 1-2.24-2.439Zm7.71 5.382a3 3 0 0 0 4.24 0l1.24-1.24a3 3 0 0 0-4.24-4.24l-1.24 1.24a3 3 0 0 0 0 4.24ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                    </svg>
                    UI/UX Design Studio
                </h1>
                <p class="text-gray-400 mt-1">Select an approved blueprint to generate or view its UI/UX design specification.</p>
            </div>

            <div v-if="!blueprints || blueprints.length === 0" class="bg-gray-800/50 rounded-xl border border-gray-700/50 p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-500 mx-auto mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-300">No approved blueprints</h3>
                <p class="text-gray-500 mt-1">Go to the AI Architect Planner, create an architecture, and approve it to start designing.</p>
                <Link :href="route('ai.planner')" class="mt-4 inline-block text-brand-400 hover:text-brand-300">
                    Go to Planner &rarr;
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="bp in blueprints" :key="bp.id" 
                      :href="route('ai.design.show', bp.id)"
                      class="group relative block bg-gray-900/40 hover:bg-gray-800/80 rounded-2xl p-6 transition-all duration-300 overflow-hidden border border-white/5 hover:border-white/10 hover:shadow-2xl hover:shadow-brand-500/10">
                    
                    <!-- Gradient accent -->
                    <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    
                    <!-- Content -->
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1 pr-4">
                                <h3 class="text-xl font-semibold text-white tracking-tight leading-tight group-hover:text-brand-300 transition-colors">{{ bp.title }}</h3>
                                <p class="text-sm text-gray-400 mt-2 line-clamp-2 leading-relaxed">
                                    {{ bp.deliverable?.executive_summary || bp.deliverable?.architecture_pattern || 'Architecture blueprint ready for design phase.' }}
                                </p>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="shrink-0 mt-1">
                                <div v-if="bp.uiux_design" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    Design Ready
                                </div>
                                <div v-else class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-500/10 text-gray-400 text-xs font-medium border border-gray-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                    Awaiting Design
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-auto pt-6 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-md bg-white/5 text-[10px] font-mono text-gray-400 uppercase tracking-wider border border-white/5 truncate max-w-[150px]" :title="bp.deliverable?.architecture_pattern">
                                    {{ bp.deliverable?.architecture_pattern || 'Architecture' }}
                                </span>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 mr-1.5 opacity-70">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{ formatDate(bp.updated_at) }}
                            </div>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
