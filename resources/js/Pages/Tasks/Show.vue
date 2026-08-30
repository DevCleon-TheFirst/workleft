<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    task: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'No due date';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head :title="task.title" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('tasks.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-3">
                        {{ task.title }}
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset capitalize"
                              :class="{
                                  'text-gray-400 bg-gray-400/10 ring-gray-400/20': task.status === 'todo',
                                  'text-brand-400 bg-brand-400/10 ring-brand-400/20': task.status === 'in_progress',
                                  'text-emerald-400 bg-emerald-400/10 ring-emerald-400/20': task.status === 'done',
                              }">
                            {{ task.status.replace('_', ' ') }}
                        </span>
                    </h2>
                </div>
                <Link :href="route('tasks.edit', task.id)" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    Edit Task
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
                                    <Link v-if="task.project" :href="route('projects.show', task.project_id)" class="text-brand-400 hover:underline">
                                        {{ task.project.title }}
                                    </Link>
                                    <span v-else class="text-gray-500">None</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-100 font-medium">{{ formatDate(task.due_date) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
