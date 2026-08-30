<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    project: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'planning': return 'text-yellow-400 bg-yellow-400/10 ring-yellow-400/20';
        case 'in_progress': return 'text-indigo-400 bg-indigo-400/10 ring-indigo-400/20';
        case 'review': return 'text-purple-400 bg-purple-400/10 ring-purple-400/20';
        case 'done': return 'text-emerald-400 bg-emerald-400/10 ring-emerald-400/20';
        default: return 'text-gray-400 bg-gray-400/10 ring-gray-400/20';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head :title="project.title" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('projects.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-3">
                        {{ project.title }}
                        <span :class="['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset', getStatusColor(project.status)]">
                            {{ project.status.replace('_', ' ') }}
                        </span>
                    </h2>
                </div>
                <Link :href="route('projects.edit', project.id)" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    Edit Project
                </Link>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Overview -->
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <dl class="grid grid-cols-1 sm:grid-cols-4 gap-x-4 gap-y-8">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Client</dt>
                                <dd class="mt-1 text-sm text-gray-100">
                                    <Link v-if="project.client" :href="route('clients.show', project.client_id)" class="text-brand-400 hover:underline">
                                        {{ project.client.company_name }}
                                    </Link>
                                    <span v-else>Internal Project</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Progress</dt>
                                <dd class="mt-1 text-sm text-gray-100 font-medium">{{ project.progress }}%</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-400">Description</dt>
                                <dd class="mt-1 text-sm text-gray-100 whitespace-pre-wrap">{{ project.description || 'No description' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Split Content: Tasks & Meetings -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Tasks -->
                    <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-100">Tasks</h3>
                                <Link :href="route('tasks.create')" class="text-sm text-brand-400 hover:text-brand-300">+ Add Task</Link>
                            </div>
                            <div v-if="project.tasks.length === 0" class="text-gray-400 py-4 text-sm">No tasks added yet.</div>
                            <ul v-else class="divide-y divide-gray-700">
                                <li v-for="task in project.tasks" :key="task.id" class="py-3 flex justify-between items-center">
                                    <div>
                                        <Link :href="route('tasks.show', task.id)" class="text-sm text-gray-200 hover:text-brand-400">{{ task.title }}</Link>
                                        <p class="text-xs text-gray-500 mt-1">Due: {{ formatDate(task.due_date) }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400 bg-gray-700 px-2 py-1 rounded capitalize">{{ task.status.replace('_', ' ') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Meetings -->
                    <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-100">Meetings</h3>
                                <Link :href="route('meetings.create')" class="text-sm text-brand-400 hover:text-brand-300">+ Schedule</Link>
                            </div>
                            <div v-if="project.meetings.length === 0" class="text-gray-400 py-4 text-sm">No meetings scheduled.</div>
                            <ul v-else class="divide-y divide-gray-700">
                                <li v-for="meeting in project.meetings" :key="meeting.id" class="py-3 flex justify-between items-center">
                                    <div>
                                        <Link :href="route('meetings.show', meeting.id)" class="text-sm text-gray-200 hover:text-brand-400">{{ meeting.title }}</Link>
                                        <p class="text-xs text-gray-500 mt-1">{{ formatDate(meeting.scheduled_at) }}</p>
                                    </div>
                                    <span v-if="meeting.ai_summary" class="text-xs text-brand-400 bg-brand-500/10 px-2 py-1 rounded flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> AI Summary
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
