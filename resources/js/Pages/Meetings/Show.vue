<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    meeting: Object,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
};
</script>

<template>
    <Head :title="meeting.title" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('meetings.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-3">
                        {{ meeting.title }}
                    </h2>
                </div>
                <Link :href="route('meetings.edit', meeting.id)" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    Edit Meeting
                </Link>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Project</dt>
                                <dd class="mt-1 text-sm text-gray-100">
                                    <Link v-if="meeting.project" :href="route('projects.show', meeting.project_id)" class="text-brand-400 hover:underline">
                                        {{ meeting.project.title }}
                                    </Link>
                                    <span v-else class="text-gray-500">None</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Scheduled At</dt>
                                <dd class="mt-1 text-sm text-gray-100 font-medium">{{ formatDate(meeting.scheduled_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- AI Summary -->
                    <div class="overflow-hidden bg-gray-900 shadow-sm border border-brand-500/20 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-brand-400 flex items-center gap-2 mb-4">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                AI Summary
                            </h3>
                            <div v-if="meeting.ai_summary" class="prose prose-invert max-w-none text-sm text-gray-300">
                                <pre class="whitespace-pre-wrap font-sans text-sm">{{ meeting.ai_summary }}</pre>
                            </div>
                            <div v-else class="text-gray-500 text-sm">
                                No AI summary available yet. Add a transcript to generate one.
                            </div>
                        </div>
                    </div>

                    <!-- Transcript -->
                    <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-100 mb-4">Transcript / Notes</h3>
                            <div v-if="meeting.transcript" class="whitespace-pre-wrap text-sm text-gray-300 bg-gray-900 p-4 rounded-md overflow-y-auto max-h-[500px] border border-gray-700">
                                {{ meeting.transcript }}
                            </div>
                            <div v-else class="text-gray-500 text-sm">
                                No transcript or notes provided.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
