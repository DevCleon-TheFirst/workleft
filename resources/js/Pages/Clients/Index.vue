<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    clients: Object,
});
</script>

<template>
    <Head title="Clients" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Clients
                </h2>
                <Link :href="route('clients.create')">
                    <PrimaryButton>New Client</PrimaryButton>
                </Link>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-nowrap text-left text-sm text-gray-400">
                                <thead class="border-b border-gray-700 bg-gray-800 text-xs uppercase text-gray-300">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Company</th>
                                        <th scope="col" class="px-6 py-3">Contact Email</th>
                                        <th scope="col" class="px-6 py-3 text-center">Projects</th>
                                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="client in clients.data" :key="client.id" class="border-b border-gray-700 hover:bg-gray-750">
                                        <td class="px-6 py-4 font-medium text-gray-100">{{ client.company_name }}</td>
                                        <td class="px-6 py-4">{{ client.contact_email || '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-md bg-brand-500/10 px-2 py-1 text-xs font-medium text-brand-400 ring-1 ring-inset ring-brand-500/20">
                                                {{ client.projects_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <Link :href="route('clients.show', client.id)" class="text-brand-400 hover:text-brand-300 mr-3">View</Link>
                                            <Link :href="route('clients.edit', client.id)" class="text-gray-400 hover:text-gray-300">Edit</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="clients.data.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No clients found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
