<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    document: Object,
});

const getIconForType = (type) => {
    switch (type) {
        case 'readme': return 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';
        case 'srs': return 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10';
        case 'api': return 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4';
        default: return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
};
</script>

<template>
    <Head title="View Document" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('documents.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-3">
                        <span class="uppercase text-xs font-medium tracking-wider text-brand-400 bg-brand-500/10 px-2 py-1 rounded ring-1 ring-inset ring-brand-500/20">
                            {{ document.type }}
                        </span>
                        {{ document.project?.title || 'General' }}
                    </h2>
                </div>
                <Link :href="route('documents.edit', document.id)" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    Edit Document
                </Link>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 border-b border-gray-700 text-sm text-gray-400 flex justify-between items-center bg-gray-900/50">
                        <span>Project: <Link v-if="document.project" :href="route('projects.show', document.project_id)" class="text-brand-400 hover:underline">{{ document.project.title }}</Link><span v-else>None</span></span>
                        <span>Last Updated: {{ formatDate(document.updated_at) }}</span>
                    </div>
                    <div class="p-8 text-gray-100">
                        
                        <!-- Simple Markdown Display (pre-wrap for now, can add a markdown parser library later) -->
                        <div class="prose prose-invert max-w-none text-gray-300 font-sans">
                            <pre class="whitespace-pre-wrap font-sans text-sm">{{ document.content_markdown }}</pre>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
