<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps({
    task: Object,
    projects: Array,
});

const projectList = ref([...props.projects]);

const form = useForm({
    project_id: props.task.project_id,
    title: props.task.title,
    status: props.task.status,
    start_date: props.task.start_date ? props.task.start_date.substring(0, 16) : '',
    due_date: props.task.due_date ? props.task.due_date.substring(0, 16) : '',
});

const submit = () => {
    form.put(route('tasks.update', props.task.id));
};

// Quick-add project modal
const showProjectModal = ref(false);
const projectForm = useForm({ title: '', description: '', status: 'planning' });

const createProject = () => {
    projectForm.post(route('projects.store', { return_back: 1 }), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['projects'], onSuccess: (page) => {
                projectList.value = page.props.projects;
                const newest = projectList.value[projectList.value.length - 1];
                if (newest) form.project_id = newest.id;
            }});
            showProjectModal.value = false;
            projectForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Edit Task" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center gap-4">
                <Link :href="route('tasks.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Edit Task: {{ task.title }}
                </h2>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <InputLabel for="title" value="Task Title" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <InputLabel for="project_id" value="Project" />
                                    <button
                                        type="button"
                                        @click="showProjectModal = true"
                                        class="flex items-center gap-1 text-xs text-brand-400 hover:text-brand-300 transition-colors"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        </svg>
                                        New Project
                                    </button>
                                </div>
                                <select
                                    id="project_id"
                                    v-model="form.project_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value="" disabled>Select a project</option>
                                    <option v-for="project in projectList" :key="project.id" :value="project.id">
                                        {{ project.title }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.project_id" />
                            </div>

                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="done">Done</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="start_date" value="Start Date & Time (Optional)" />
                                    <input
                                        id="start_date"
                                        type="datetime-local"
                                        v-model="form.start_date"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-3 py-2 text-sm"
                                    />
                                    <InputError class="mt-2" :message="form.errors.start_date" />
                                </div>

                                <div>
                                    <InputLabel for="due_date" value="Due Date & Time (Optional)" />
                                    <input
                                        id="due_date"
                                        type="datetime-local"
                                        v-model="form.due_date"
                                        class="mt-1 block w-full rounded-md border border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-3 py-2 text-sm"
                                    />
                                    <InputError class="mt-2" :message="form.errors.due_date" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('tasks.destroy', task.id)"
                                    method="delete"
                                    as="button"
                                    class="text-sm text-red-400 hover:text-red-300"
                                    preserve-scroll
                                >
                                    Delete Task
                                </Link>
                                <div class="flex gap-4">
                                    <Link :href="route('tasks.index')" class="text-sm text-gray-400 hover:text-gray-300 mt-2">
                                        Cancel
                                    </Link>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Update Task
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>

    <!-- Quick Add Project Modal -->
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="showProjectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showProjectModal = false">
                <div class="bg-gray-900 border border-gray-700 rounded-xl shadow-2xl p-6 max-w-md w-full">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-white">New Project</h3>
                        <button @click="showProjectModal = false" class="text-gray-500 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="createProject" class="space-y-4">
                        <div>
                            <InputLabel for="p_title" value="Project Name" />
                            <TextInput id="p_title" type="text" class="mt-1 block w-full" v-model="projectForm.title" required autofocus />
                            <InputError class="mt-1" :message="projectForm.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="p_desc" value="Description (optional)" />
                            <textarea
                                id="p_desc"
                                v-model="projectForm.description"
                                rows="2"
                                class="mt-1 block w-full rounded-md border border-gray-700 bg-gray-800 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 px-3 py-2 text-sm"
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showProjectModal = false" class="px-4 py-2 text-sm rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors">
                                Cancel
                            </button>
                            <PrimaryButton :disabled="projectForm.processing" :class="{ 'opacity-50': projectForm.processing }">
                                Create Project
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
