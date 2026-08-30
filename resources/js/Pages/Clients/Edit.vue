<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    client: Object,
});

const form = useForm({
    company_name: props.client.company_name,
    contact_email: props.client.contact_email || '',
    notes: props.client.notes || '',
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <Head title="Edit Client" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center gap-4">
                <Link :href="route('clients.index')" class="text-gray-400 hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Edit Client: {{ client.company_name }}
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
                                <InputLabel for="company_name" value="Company Name" />
                                <TextInput
                                    id="company_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.company_name"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.company_name" />
                            </div>

                            <div>
                                <InputLabel for="contact_email" value="Contact Email" />
                                <TextInput
                                    id="contact_email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.contact_email"
                                />
                                <InputError class="mt-2" :message="form.errors.contact_email" />
                            </div>

                            <div>
                                <InputLabel for="notes" value="Notes" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>

                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('clients.destroy', client.id)"
                                    method="delete"
                                    as="button"
                                    class="text-sm text-red-400 hover:text-red-300"
                                    preserve-scroll
                                >
                                    Delete Client
                                </Link>
                                <div class="flex gap-4">
                                    <Link :href="route('clients.index')" class="text-sm text-gray-400 hover:text-gray-300 mt-2">
                                        Cancel
                                    </Link>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Update Client
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
</template>
