<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    projects: Array,
});

const form = useForm({
    project_id: '',
    type: 'readme',
    content_markdown: '',
});

const submit = () => {
    form.post(route('documents.store'));
};
</script>

<template>
    <Head title="New Document" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center gap-4">
                <Link :href="route('documents.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    New Document
                </h2>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="project_id" value="Project" />
                                    <select
                                        id="project_id"
                                        v-model="form.project_id"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    >
                                        <option value="" disabled>Select a project</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.title }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.project_id" />
                                </div>

                                <div>
                                    <InputLabel for="type" value="Document Type" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    >
                                        <option value="readme">README</option>
                                        <option value="srs">SRS (Requirements)</option>
                                        <option value="api">API Specs</option>
                                        <option value="general">General Notes</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type" />
                                </div>
                            </div>

                            <div>
                                <InputLabel for="content_markdown" value="Content (Markdown)" />
                                <div class="mt-1 relative border rounded-md border-gray-700 bg-gray-900">
                                    <div class="flex items-center gap-2 p-2 border-b border-gray-700 bg-gray-800 rounded-t-md">
                                        <span class="text-xs font-mono text-gray-400"># Markdown Supported</span>
                                    </div>
                                    <textarea
                                        id="content_markdown"
                                        v-model="form.content_markdown"
                                        required
                                        class="block w-full border-0 bg-transparent text-gray-300 focus:ring-0 font-mono text-sm"
                                        rows="20"
                                        placeholder="# Document Title&#10;&#10;Write your content here..."
                                    ></textarea>
                                </div>
                                <InputError class="mt-2" :message="form.errors.content_markdown" />
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <Link :href="route('documents.index')" class="text-sm text-gray-400 hover:text-gray-300">
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Save Document
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
