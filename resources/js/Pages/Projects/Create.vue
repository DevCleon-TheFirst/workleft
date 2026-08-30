<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    clients: Array,
});

const form = useForm({
    client_id: '',
    title: '',
    description: '',
    status: 'planning',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="New Project" />

    <AuthenticatedLayout>
        <div class="p-6 space-y-6 min-h-full">
            <div class="flex items-center justify-between pb-4 border-b border-gray-800">
                <div class="flex items-center gap-4">
                    <Link :href="route('projects.index')" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-100">
                        Create Project
                    </h2>
                </div>
            </div>

            <div>
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <InputLabel for="title" value="Project Title" />
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
                                <InputLabel for="client_id" value="Client (optional)" />
                                <select
                                    id="client_id"
                                    v-model="form.client_id"
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value="">— No client —</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.company_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.client_id" />
                            </div>

                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value="planning">Planning</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <div>
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <Link :href="route('projects.index')" class="text-sm text-gray-400 hover:text-gray-300">
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Project
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
