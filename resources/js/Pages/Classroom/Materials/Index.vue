<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ groupedMaterials: Object, students: Array });
const page = usePage();
const isTeacher = computed(() => page.props.auth.user.role === 'teacher');
const isAdding = ref(false);

const form = useForm({
    title: '',
    module: '',
    type: 'link',
    content_url: '',
    description: '',
    target: 'all',
    student_ids: [],
});

const typeIcons = {
    link: `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.342 8.214a.75.75 0 0 1 1.06 0 3.75 3.75 0 0 0 5.304 0l4.5-4.5a3.75 3.75 0 0 0-5.304-5.304l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a5.25 5.25 0 1 1 7.424 7.424l-4.5 4.5a5.25 5.25 0 0 1-7.424 0 .75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>`,
    pdf: `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 0 1 3.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 0 1 3.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875Zm6.905 9.97a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l1.72-1.72V18a.75.75 0 0 0 1.5 0v-4.19l1.72 1.72a.75.75 0 1 0 1.06-1.06l-3-3Z" clip-rule="evenodd"/><path d="M14.25 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963 5.23 5.23 0 0 0-3.434-1.279h-2.25Z"/></svg>`,
    video: `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4.5 4.5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h8.25a3 3 0 0 0 3-3v-9a3 3 0 0 0-3-3H4.5ZM19.94 18.75l-2.69-2.69V7.94l2.69-2.69c.944-.945 2.56-.276 2.56 1.06v11.38c0 1.336-1.616 2.005-2.56 1.06Z"/></svg>`,
};

const typeBadgeClass = {
    link: 'bg-blue-500/10 text-blue-400',
    pdf: 'bg-red-500/10 text-red-400',
    video: 'bg-purple-500/10 text-purple-400',
};

function addMaterial() {
    form.post(route('classroom.materials.store'), {
        preserveScroll: true,
        onSuccess: () => { isAdding.value = false; form.reset(); }
    });
}

function deleteMaterial(id) {
    if (confirm('Remove this material?')) {
        useForm({}).delete(route('classroom.materials.destroy', id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Materials Vault" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <Link :href="route('classroom.index')" class="hover:text-gray-300 transition-colors">Classroom Hub</Link>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">Materials Vault</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-100">Materials Vault</h1>
                    <p class="text-sm text-gray-500 mt-1">All learning resources, organised by module.</p>
                </div>
                <button v-if="isTeacher" @click="isAdding = !isAdding"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                    {{ isAdding ? 'Cancel' : 'Add Material' }}
                </button>
            </div>

            <!-- Add Form (Teacher Only) -->
            <div v-if="isAdding && isTeacher" class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">New Material</h3>
                <form @submit.prevent="addMaterial">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Title</label>
                            <input v-model="form.title" type="text" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="e.g. REST API Design Principles">
                            <p v-if="form.errors.title" class="text-red-400 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Module / Week</label>
                            <input v-model="form.module" type="text" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="e.g. Week 1, Authentication">
                            <p v-if="form.errors.module" class="text-red-400 text-xs mt-1">{{ form.errors.module }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Type</label>
                            <select v-model="form.type"
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="link">Link (Article / Docs)</option>
                                <option value="video">Video (YouTube / Loom)</option>
                                <option value="pdf">PDF / File URL</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">URL</label>
                            <input v-model="form.content_url" type="url" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="https://...">
                            <p v-if="form.errors.content_url" class="text-red-400 text-xs mt-1">{{ form.errors.content_url }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Description (optional)</label>
                            <textarea v-model="form.description" rows="2"
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="Brief note about this material..."></textarea>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Target Audience</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="radio" v-model="form.target" value="all" class="bg-gray-900 border-gray-700 text-brand-500 focus:ring-brand-500" />
                                All Students
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="radio" v-model="form.target" value="specific" class="bg-gray-900 border-gray-700 text-brand-500 focus:ring-brand-500" />
                                Specific Students
                            </label>
                        </div>
                    </div>

                    <div v-if="form.target === 'specific'" class="mt-4 p-4 bg-gray-900 border border-gray-700 rounded-lg max-h-48 overflow-y-auto">
                        <label class="block text-xs font-medium text-gray-400 mb-2">Select Students</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label v-for="student in students" :key="student.id" class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="checkbox" v-model="form.student_ids" :value="student.id" class="bg-gray-800 border-gray-600 rounded text-brand-500 focus:ring-brand-500" />
                                {{ student.name }}
                            </label>
                        </div>
                        <p v-if="form.errors.student_ids" class="text-red-400 text-xs mt-1">{{ form.errors.student_ids }}</p>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                            Save to Vault
                        </button>
                    </div>
                </form>
            </div>

            <!-- Empty state -->
            <div v-if="Object.keys(groupedMaterials).length === 0" class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/></svg>
                </div>
                <p class="text-sm text-gray-500">No materials yet. {{ isTeacher ? 'Add your first resource above.' : 'Your instructor has not added materials yet.' }}</p>
            </div>

            <!-- Grouped Modules -->
            <div v-for="(items, module) in groupedMaterials" :key="module" class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-5 bg-brand-600 rounded-full"></div>
                    <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">{{ module }}</h2>
                    <div class="flex-1 h-px bg-gray-800"></div>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div v-for="material in items" :key="material.id"
                        class="bg-gray-800 border border-gray-700 hover:border-gray-600 rounded-xl p-4 flex items-start gap-3 transition-colors group">
                        <div :class="typeBadgeClass[material.type]" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5" v-html="typeIcons[material.type]"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-200 truncate">{{ material.title }}</p>
                                <span v-if="isTeacher" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-brand-500/10 text-brand-400 border border-brand-500/20">
                                    {{ material.visibility === 'all' ? 'All Students' : material.students.length + ' Students' }}
                                </span>
                            </div>
                            <p v-if="material.description" class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ material.description }}</p>
                            <a :href="material.content_url" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 text-xs text-brand-400 hover:text-brand-300 mt-1.5 transition-colors">
                                Open resource
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M15.75 2.25H21a.75.75 0 0 1 .75.75v5.25a.75.75 0 0 1-1.5 0V4.81L8.03 17.03a.75.75 0 0 1-1.06-1.06L19.19 3.75h-3.44a.75.75 0 0 1 0-1.5Zm-10.5 4.5a1.5 1.5 0 0 0-1.5 1.5v10.5a1.5 1.5 0 0 0 1.5 1.5h10.5a1.5 1.5 0 0 0 1.5-1.5V10.5a.75.75 0 0 1 1.5 0v8.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V8.25a3 3 0 0 1 3-3H13.5a.75.75 0 0 1 0 1.5H5.25Z" clip-rule="evenodd"/></svg>
                            </a>
                        </div>
                        <button v-if="isTeacher" @click="deleteMaterial(material.id)"
                            class="opacity-0 group-hover:opacity-100 text-gray-600 hover:text-red-400 transition-all flex-shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
