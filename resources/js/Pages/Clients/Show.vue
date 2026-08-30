<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    client: Object,
});
</script>

<template>
    <Head :title="client.company_name" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('clients.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100">
                        {{ client.company_name }}
                    </h2>
                </div>
                <Link :href="route('clients.edit', client.id)" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    Edit Client
                </Link>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Client Details -->
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <h3 class="text-lg font-medium text-gray-100 mb-4">Client Information</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-400">Contact Email</dt>
                                <dd class="mt-1 text-sm text-gray-100">{{ client.contact_email || 'No email provided' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-400">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-100 whitespace-pre-wrap">{{ client.notes || 'No notes available' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Associated Projects -->
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-100">Projects</h3>
                            <Link :href="route('projects.create')" class="text-sm text-brand-400 hover:text-brand-300">
                                + New Project
                            </Link>
                        </div>
                        
                        <div v-if="client.projects.length === 0" class="text-gray-400 py-4 text-center">
                            No projects for this client yet.
                        </div>
                        
                        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <Link 
                                v-for="project in client.projects" 
                                :key="project.id"
                                :href="route('projects.show', project.id)"
                                class="block p-4 rounded-lg border border-gray-700 bg-gray-900/50 hover:bg-gray-700/50 transition"
                            >
                                <h4 class="text-gray-100 font-medium mb-1">{{ project.title }}</h4>
                                <div class="flex justify-between items-center text-sm text-gray-400">
                                    <span class="capitalize">{{ project.status.replace('_', ' ') }}</span>
                                    <span>{{ project.tasks_count }} tasks</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
