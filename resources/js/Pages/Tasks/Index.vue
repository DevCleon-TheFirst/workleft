<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    tasksByStatus: Object,
    projects: Array,
    filters: Object,
});

const selectedProject = ref(props.filters.project_id || '');

watch(selectedProject, (value) => {
    router.get(route('tasks.index'), { project_id: value }, { preserveState: true, replace: true });
});

const getColumns = () => {
    return [
        { id: 'todo', title: 'To Do', color: 'border-gray-500' },
        { id: 'in_progress', title: 'In Progress', color: 'border-brand-500' },
        { id: 'done', title: 'Done', color: 'border-emerald-500' },
    ];
};

const formatDate = (dateString) => {
    if (!dateString) return 'No due date';
    return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head title="Tasks Board" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Tasks Board
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
                    <Link :href="route('tasks.create')">
                        <PrimaryButton>Add Task</PrimaryButton>
                    </Link>
                </div>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    
                    <div 
                        v-for="col in getColumns()" 
                        :key="col.id" 
                        class="bg-gray-800/50 rounded-xl p-4 min-h-[500px] border-t-4 shadow-sm"
                        :class="col.color"
                    >
                        <h3 class="text-sm font-medium text-gray-300 uppercase tracking-wider mb-4">{{ col.title }} ({{ tasksByStatus[col.id]?.length || 0 }})</h3>
                        
                        <div class="space-y-4">
                            <div 
                                v-for="task in (tasksByStatus[col.id] || [])" 
                                :key="task.id"
                                class="bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-700 hover:ring-white/10 transition group"
                            >
                                <Link :href="route('tasks.show', task.id)" class="block">
                                    <h4 class="text-gray-100 font-medium mb-2 group-hover:text-brand-400">{{ task.title }}</h4>
                                    <p class="text-xs text-gray-500 mb-4">{{ task.project?.title || 'No Project' }}</p>
                                    
                                    <div class="flex items-center justify-between mt-4">
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            {{ formatDate(task.due_date) }}
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            
                            <div v-if="!(tasksByStatus[col.id]?.length)" class="text-center py-8 border-2 border-dashed border-gray-700 rounded-lg text-gray-500 text-sm">
                                No tasks
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
