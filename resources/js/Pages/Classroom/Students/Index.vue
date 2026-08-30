<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({ students: Array });
const page = usePage();

const isAdding = ref(false);
const revealedPassword = ref(null);
const revealedName = ref(null);

// Capture flash after successful create
watch(() => page.props.flash?.plain_password, (newVal) => {
    if (newVal) {
        revealedPassword.value = newVal;
        revealedName.value = page.props.flash?.student_name;
    }
}, { immediate: true });

const form = useForm({ name: '', email: '' });

function addStudent() {
    form.post(route('classroom.students.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isAdding.value = false;
            form.reset();
        }
    });
}

function removeStudent(id) {
    if (confirm('Remove this student from the platform?')) {
        useForm({}).delete(route('classroom.students.destroy', id), { preserveScroll: true });
    }
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Student Roster" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <Link :href="route('classroom.index')" class="hover:text-gray-300 transition-colors">Classroom Hub</Link>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">Student Roster</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-100">Student Roster</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ students.length }} enrolled student{{ students.length !== 1 ? 's' : '' }}</p>
                </div>
                <button @click="isAdding = !isAdding"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/></svg>
                    {{ isAdding ? 'Cancel' : 'Add Student' }}
                </button>
            </div>

            <!-- Revealed Password Banner -->
            <div v-if="revealedPassword" class="mb-6 bg-brand-600/10 border border-brand-600/30 rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-brand-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd"/></svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-brand-300">Account created for {{ revealedName }}</p>
                        <p class="text-xs text-gray-400 mt-1">Share this one-time password with the student. It will not be shown again.</p>
                        <div class="mt-3 flex items-center gap-3">
                            <code class="bg-gray-900 text-brand-300 px-4 py-2 rounded-lg text-sm font-mono tracking-widest">{{ revealedPassword }}</code>
                            <button @click="revealedPassword = null" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Student Form -->
            <div v-if="isAdding" class="bg-gray-800 border border-gray-700 rounded-xl p-6 mb-6">
                <h3 class="text-base font-semibold text-gray-100 mb-4">New Student Account</h3>
                <form @submit.prevent="addStudent">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Full Name</label>
                            <input v-model="form.name" type="text" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="e.g. Chukwuemeka Nwosu">
                            <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Email Address</label>
                            <input v-model="form.email" type="email" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                placeholder="student@email.com">
                            <p v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3">A random password will be generated and shown to you once. The student can change it from their profile.</p>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" :disabled="form.processing"
                            class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- Empty state -->
            <div v-if="students.length === 0" class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
                <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-300">No students yet</h3>
                <p class="text-sm text-gray-500 mt-1">Add your first student to get started.</p>
            </div>

            <!-- Students Table -->
            <div v-else class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead>
                        <tr class="bg-gray-900/50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <tr v-for="student in students" :key="student.id" class="hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-md bg-brand-600/20 flex items-center justify-center text-brand-400 text-sm font-bold flex-shrink-0">
                                        {{ student.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-200">{{ student.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ student.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(student.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="removeStudent(student.id)"
                                    class="text-xs text-red-500 hover:text-red-400 font-medium transition-colors">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
