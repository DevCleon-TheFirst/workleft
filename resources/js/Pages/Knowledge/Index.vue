<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    documents: Object,
    projects: Array,
    filters: Object,
});

const selectedProject = ref(props.filters.project_id || '');
const selectedType = ref(props.filters.type || '');

watch([selectedProject, selectedType], ([projVal, typeVal]) => {
    router.get(route('documents.index'), { project_id: projVal, type: typeVal }, { preserveState: true, replace: true });
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
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="Knowledge Base" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Knowledge Base
                </h2>
                <div class="flex items-center gap-4">
                    <select v-model="selectedType" class="rounded-md border-gray-700 bg-gray-900 text-sm text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Types</option>
                        <option value="readme">README</option>
                        <option value="srs">SRS (Requirements)</option>
                        <option value="api">API Docs</option>
                        <option value="general">General</option>
                    </select>
                    <select v-model="selectedProject" class="rounded-md border-gray-700 bg-gray-900 text-sm text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">All Projects</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.title }}
                        </option>
                    </select>
                    <Link :href="route('documents.create')">
                        <PrimaryButton>New Document</PrimaryButton>
                    </Link>
                </div>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div v-if="documents.data.length === 0" class="text-center py-12 text-gray-500">
                    No documents found.
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <Link 
                        v-for="doc in documents.data" 
                        :key="doc.id"
                        :href="route('documents.show', doc.id)"
                        class="group flex flex-col justify-between overflow-hidden rounded-md bg-gray-800 p-5 border border-gray-700 transition hover:bg-gray-750 hover:ring-white/10"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="p-2 bg-gray-900 rounded-lg text-brand-400 group-hover:text-brand-300 border border-gray-700">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconForType(doc.type)" />
                                    </svg>
                                </span>
                                <span class="uppercase text-xs font-medium tracking-wider text-gray-500">{{ doc.type }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-100 mb-2 line-clamp-1">{{ doc.project?.title || 'General' }}</h3>
                            <p class="text-sm text-gray-400 line-clamp-3 break-words">{{ doc.content_markdown.substring(0, 100) }}...</p>
                        </div>
                        
                        <div class="mt-6 border-t border-gray-700 pt-4 flex justify-between items-center">
                            <span class="text-xs text-gray-500">Updated</span>
                            <span class="text-xs text-gray-400">{{ formatDate(doc.updated_at) }}</span>
                        </div>
                    </Link>
                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
