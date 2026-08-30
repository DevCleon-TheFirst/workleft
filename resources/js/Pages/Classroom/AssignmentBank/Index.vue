<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ templates: Array });

const isAdding = ref(false);
const form = useForm({
    title: '',
    description_markdown: '',
});

function addTemplate() {
    form.post(route('classroom.bank.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isAdding.value = false;
            form.reset();
        }
    });
}

function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        useForm({}).delete(route('classroom.bank.destroy', id), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Assignment Bank" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <Link :href="route('classroom.index')" class="hover:text-gray-300 transition-colors">Classroom Hub</Link>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">Assignment Bank</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-100">Assignment Bank</h1>
                    <p class="text-sm text-gray-500 mt-1">Create and manage reusable assignment templates.</p>
                </div>
                <button @click="isAdding = !isAdding"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                    {{ isAdding ? 'Cancel' : 'New Template' }}
                </button>
            </div>

            <!-- Add Form -->
            <div v-if="isAdding" class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Create Template</h3>
                <form @submit.prevent="addTemplate">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Title</label>
                            <input v-model="form.title" type="text" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="e.g. Build a RESTful API">
                            <p v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Description (Markdown Supported)</label>
                            <textarea v-model="form.description_markdown" required rows="6"
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600 font-mono"
                                placeholder="Write the assignment instructions here..."></textarea>
                            <p v-if="form.errors.description_markdown" class="text-red-400 text-xs mt-1">{{ form.errors.description_markdown }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                            Save Template
                        </button>
                    </div>
                </form>
            </div>

            <!-- Empty State -->
            <div v-if="templates.length === 0" class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-300">No templates yet</h3>
                <p class="text-sm text-gray-500 mt-1">Create your first assignment template to get started.</p>
            </div>

            <!-- List of Templates -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="template in templates" :key="template.id"
                    class="bg-gray-800 border border-gray-700 rounded-xl p-5 hover:border-gray-600 transition-colors group flex flex-col">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-semibold text-gray-100 text-lg">{{ template.title }}</h3>
                        <button @click="deleteTemplate(template.id)" class="opacity-0 group-hover:opacity-100 text-gray-500 hover:text-red-400 transition-all p-1">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-3 flex-1">{{ template.description_markdown }}</p>
                    <div class="pt-4 border-t border-gray-700 text-xs text-gray-500">
                        Added {{ new Date(template.created_at).toLocaleDateString() }}
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
