<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    meetings: Object,
    projects: Array,
    filters: Object,
});

const selectedProject = ref(props.filters.project_id || '');

watch(selectedProject, (value) => {
    router.get(route('meetings.index'), { project_id: value }, { preserveState: true, replace: true });
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
};
</script>

<template>
    <Head title="Meetings" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Meetings
                </h2>
                <div class="flex items-center gap-4">
                    <select
                        v-model="selectedProject"
                        class="rounded-md border-gray-700 bg-gray-900 text-sm text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                        <option value="">All Projects</option>
                        <option v-for="project in projects" :key="project.id" :value="project.id">
                            {{ project.title }}
                        </option>
                    </select>
                    <Link :href="route('meetings.create')">
                        <PrimaryButton>Schedule Meeting</PrimaryButton>
                    </Link>
                </div>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        
                        <div v-if="meetings.data.length === 0" class="text-center py-12 text-gray-500">
                            No meetings found.
                        </div>

                        <ul v-else class="divide-y divide-gray-700">
                            <li v-for="meeting in meetings.data" :key="meeting.id" class="py-4 flex justify-between items-center group">
                                <div>
                                    <Link :href="route('meetings.show', meeting.id)" class="text-gray-100 font-medium hover:text-brand-400 text-lg flex items-center gap-2">
                                        {{ meeting.title }}
                                        <span v-if="meeting.ai_summary" class="text-xs font-medium bg-brand-500/10 text-brand-400 px-2 py-0.5 rounded ring-1 ring-inset ring-brand-500/20">
                                            AI Summary
                                        </span>
                                    </Link>
                                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ formatDate(meeting.scheduled_at) }}
                                        </span>
                                        <span v-if="meeting.project">Project: {{ meeting.project.title }}</span>
                                    </div>
                                </div>
                                <Link :href="route('meetings.edit', meeting.id)" class="text-gray-400 hover:text-gray-300 opacity-0 group-hover:opacity-100 transition">
                                    Edit
                                </Link>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
