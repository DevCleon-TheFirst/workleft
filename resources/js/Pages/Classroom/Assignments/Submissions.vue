<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ 
    assignment: Object,
    submissions: Array 
});

const gradingForm = useForm({
    score: '',
    comments: '',
});

const activeGradingId = ref(null);

function startGrading(submission) {
    activeGradingId.value = submission.id;
    gradingForm.score = submission.score ?? '';
    gradingForm.comments = submission.comments ?? '';
}

function submitGrade() {
    gradingForm.patch(route('classroom.submissions.grade', activeGradingId.value), {
        preserveScroll: true,
        onSuccess: () => {
            activeGradingId.value = null;
            gradingForm.reset();
        }
    });
}

function deleteSubmission(submission) {
    if (!confirm(`Delete ${submission.student.name}'s submission? This cannot be undone.`)) return;
    useForm({}).delete(route('classroom.submissions.delete', submission.id), {
        preserveScroll: true,
    });
}

// Returns true if the assignment's deadline has passed (or no deadline set)
function isPastDeadline() {
    if (!props.assignment.due_date) return true; // no deadline = always deletable
    return new Date() > new Date(props.assignment.due_date);
}
</script>

<template>
    <Head :title="`Submissions - ${assignment.template.title}`" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">

            <!-- Header -->
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <Link :href="route('classroom.index')" class="hover:text-gray-300 transition-colors">Classroom Hub</Link>
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                    <Link :href="route('classroom.assignments.index')" class="hover:text-gray-300 transition-colors">Assignments</Link>
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-300">Submissions</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-100">Submissions: {{ assignment.template.title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Review student work and assign grades.</p>
            </div>

            <!-- Empty State -->
            <div v-if="submissions.length === 0" class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 0 1 3.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 0 1 3.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875Z" clip-rule="evenodd"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-300">No submissions yet</h3>
                <p class="text-sm text-gray-500 mt-1">Students have not submitted their work for this assignment.</p>
            </div>

            <!-- List of Submissions -->
            <div v-else class="space-y-4">
                <div v-for="submission in submissions" :key="submission.id"
                    class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-brand-600/20 flex items-center justify-center text-brand-400 font-bold text-lg">
                                {{ submission.student.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-100">{{ submission.student.name }}</h3>
                                <p class="text-xs text-gray-500">Submitted {{ new Date(submission.created_at).toLocaleString() }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span v-if="submission.status === 'graded'" class="px-3 py-1 rounded bg-green-500/10 text-green-400 text-sm font-bold border border-green-500/20">
                                Graded: {{ submission.score }}/100
                            </span>
                            <span v-else class="px-3 py-1 rounded bg-yellow-500/10 text-yellow-400 text-sm font-bold border border-yellow-500/20">
                                Pending Grade
                            </span>
                        </div>
                    </div>

                    <!-- Links -->
                    <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700/50 mb-4 flex flex-wrap gap-4">
                        <a v-if="submission.github_url" :href="submission.github_url" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2 text-sm text-gray-300 hover:text-white transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0 0 22 12.017C22 6.484 17.522 2 12 2Z" clip-rule="evenodd"/></svg>
                            View Repository
                        </a>
                        <a v-if="submission.live_url" :href="submission.live_url" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2 text-sm text-gray-300 hover:text-white transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.902 4.098a3.75 3.75 0 0 0-5.304 0l-4.5 4.5a3.75 3.75 0 0 0 1.035 6.037.75.75 0 0 1-.646 1.353 5.25 5.25 0 0 1-1.449-8.45l4.5-4.5a5.25 5.25 0 1 1 7.424 7.424l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a3.75 3.75 0 0 0 0-5.304Zm-7.342 8.214a.75.75 0 0 1 1.06 0 3.75 3.75 0 0 0 5.304 0l4.5-4.5a3.75 3.75 0 0 0-5.304-5.304l-1.757 1.757a.75.75 0 1 1-1.06-1.06l1.757-1.757a5.25 5.25 0 1 1 7.424 7.424l-4.5 4.5a5.25 5.25 0 0 1-7.424 0 .75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                            View Live Demo
                        </a>
                        <a v-if="submission.file_path" :href="route('classroom.submissions.download', submission.id)"
                            class="flex items-center gap-2 text-sm text-brand-400 hover:text-brand-300 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Download Attachment
                        </a>
                        <span v-if="!submission.github_url && !submission.live_url && !submission.file_path" class="text-sm text-gray-500 italic">No files or links provided.</span>
                    </div>

                    <!-- Grading Interface -->
                    <div v-if="activeGradingId === submission.id" class="border-t border-gray-700 pt-4 mt-4">
                        <form @submit.prevent="submitGrade">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Score (0-100)</label>
                                    <input v-model="gradingForm.score" type="number" min="0" max="100" required
                                        class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Feedback / Comments</label>
                                    <textarea v-model="gradingForm.comments" rows="2"
                                        class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                        placeholder="Great job on the API structure!"></textarea>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end gap-3">
                                <button type="button" @click="activeGradingId = null" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-gray-200 transition-colors">Cancel</button>
                                <button type="submit" :disabled="gradingForm.processing"
                                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                                    Save Grade
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div v-else class="flex justify-end gap-3 border-t border-gray-700 pt-4 mt-4">
                        <!-- Delete button: only shows after deadline -->
                        <button v-if="isPastDeadline()" @click="deleteSubmission(submission)"
                            class="px-5 py-2 bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 text-red-400 text-sm font-medium rounded-lg transition-colors">
                            Delete
                        </button>
                        <button @click="startGrading(submission)"
                            class="px-5 py-2 bg-gray-900 border border-gray-700 hover:border-gray-500 text-gray-200 text-sm font-medium rounded-lg transition-colors">
                            {{ submission.status === 'graded' ? 'Update Grade' : 'Grade Submission' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
