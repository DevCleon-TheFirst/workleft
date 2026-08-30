<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    projects: Object,
    filters: Object,
    statusOptions: Array,
});

const selectedStatus = ref(props.filters.status || '');

watch(selectedStatus, (value) => {
    router.get(route('projects.index'), { status: value }, { preserveState: true, replace: true });
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
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <div class="p-6 space-y-6 min-h-full">
            <div class="flex items-center justify-between pb-4 border-b border-gray-800">
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Projects
                </h2>
                <div class="flex items-center gap-4">
                    <select
                        v-model="selectedStatus"
                        class="rounded-md border border-gray-700 bg-gray-800 text-sm text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                        <option value="">All Statuses</option>
                        <option v-for="status in statusOptions" :key="status" :value="status">
                            {{ status.replace('_', ' ').charAt(0).toUpperCase() + status.replace('_', ' ').slice(1) }}
                        </option>
                    </select>
                    <Link :href="route('projects.create')" class="bg-brand-600 hover:bg-brand-700 text-white flex items-center gap-2 text-sm px-4 py-2 rounded transition-colors">
                        New Project
                    </Link>
                </div>
            </div>

            <div>
                
                <div v-if="projects.data.length === 0" class="text-center py-12">
                    <p class="text-gray-400">No projects found.</p>
                </div>

                <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link 
                        v-for="project in projects.data" 
                        :key="project.id"
                        :href="route('projects.show', project.id)"
                        class="group flex flex-col justify-between overflow-hidden rounded-md bg-gray-800 p-5 border border-gray-700 transition hover:bg-gray-750"
                    >
                        <div>
                            <div class="flex items-center justify-between">
                                <span :class="['inline-flex items-center rounded px-2 py-0.5 text-xs font-mono font-medium ring-1 ring-inset', getStatusColor(project.status)]">
                                    {{ project.status.replace('_', ' ') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ project.tasks_count }} tasks</span>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-gray-200 group-hover:text-brand-400 transition-colors">{{ project.title }}</h3>
                            <p class="mt-1 text-sm text-gray-400">{{ project.client?.company_name || 'Internal' }}</p>
                        </div>
                        
                        <div class="mt-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Progress</span>
                                <span class="text-white">{{ project.progress }}%</span>
                            </div>
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-700">
                                <div 
                                    class="h-full bg-brand-500 transition-all duration-500" 
                                    :style="{ width: project.progress + '%' }"
                                ></div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
