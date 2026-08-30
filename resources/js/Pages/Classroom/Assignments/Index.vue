<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import MarkdownIt from 'markdown-it';

const md = new MarkdownIt();

const props = defineProps({
    activeAssignments: Array,
    closedAssignments: Array,
    templates: Array, // Teacher only
    students: Array, // Teacher only
});

const page = usePage();
const isTeacher = computed(() => page.props.auth.user.role === 'teacher');

// Teacher actions
const isReleasing = ref(false);
const releaseForm = useForm({
    assignment_template_id: '',
    due_date: '',
    target: 'all',
    student_ids: [],
});

function releaseAssignment() {
    releaseForm.post(route('classroom.assignments.store'), {
        preserveScroll: true,
        onSuccess: () => { isReleasing.value = false; releaseForm.reset(); }
    });
}

function closeAssignment(id) {
    if (confirm('Close this assignment? Students will no longer be able to submit.')) {
        useForm({}).patch(route('classroom.assignments.close', id), { preserveScroll: true });
    }
}

// Student actions
const activeSubmissionModal = ref(null);
const submitForm = useForm({
    github_url: '',
    live_url: '',
    attachment: null,
});

function openSubmissionModal(assignment) {
    activeSubmissionModal.value = assignment;
    submitForm.github_url = assignment.my_submission?.github_url || '';
    submitForm.live_url = assignment.my_submission?.live_url || '';
}

function submitWork() {
    submitForm.post(route('classroom.assignments.submit', activeSubmissionModal.value.id), {
        preserveScroll: true,
        onSuccess: () => { activeSubmissionModal.value = null; submitForm.reset(); }
    });
}

function handleFileUpload(event) {
    submitForm.attachment = event.target.files[0];
}
</script>

<template>
    <Head title="Assignments" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8 relative">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                        <Link :href="route('classroom.index')" class="hover:text-gray-300 transition-colors">Classroom Hub</Link>
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-300">{{ isTeacher ? 'Release & Grade' : 'My Assignments' }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-100">{{ isTeacher ? 'Release & Grade' : 'My Assignments' }}</h1>
                </div>
                <button v-if="isTeacher" @click="isReleasing = !isReleasing"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3.478 2.404a.75.75 0 0 0-.926.941l2.432 7.905H13.5a.75.75 0 0 1 0 1.5H4.984l-2.432 7.905a.75.75 0 0 0 .926.94 60.519 60.519 0 0 0 18.445-8.986.75.75 0 0 0 0-1.218A60.517 60.517 0 0 0 3.478 2.404Z"/></svg>
                    {{ isReleasing ? 'Cancel' : 'Release Assignment' }}
                </button>
            </div>

            <!-- Release Form (Teacher Only) -->
            <div v-if="isReleasing && isTeacher" class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Release New Assignment</h3>
                
                <div v-if="templates.length === 0" class="text-sm text-gray-500 mb-4">
                    You have no assignment templates. <Link :href="route('classroom.bank.index')" class="text-brand-400 hover:underline">Create one in the Assignment Bank first.</Link>
                </div>
                
                <form v-else @submit.prevent="releaseAssignment">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Select Template</label>
                            <select v-model="releaseForm.assignment_template_id" required
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                <option value="" disabled>Choose a template...</option>
                                <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.title }}</option>
                            </select>
                            <p v-if="releaseForm.errors.assignment_template_id" class="text-red-400 text-xs mt-1">{{ releaseForm.errors.assignment_template_id }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Due Date (Optional)</label>
                            <input v-model="releaseForm.due_date" type="datetime-local"
                                class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 color-scheme-dark">
                            <p v-if="releaseForm.errors.due_date" class="text-red-400 text-xs mt-1">{{ releaseForm.errors.due_date }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Target Audience</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="radio" v-model="releaseForm.target" value="all" class="bg-gray-900 border-gray-700 text-brand-500 focus:ring-brand-500" />
                                All Students
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="radio" v-model="releaseForm.target" value="specific" class="bg-gray-900 border-gray-700 text-brand-500 focus:ring-brand-500" />
                                Specific Students
                            </label>
                        </div>
                    </div>

                    <div v-if="releaseForm.target === 'specific'" class="mt-4 p-4 bg-gray-900 border border-gray-700 rounded-lg max-h-48 overflow-y-auto">
                        <label class="block text-xs font-medium text-gray-400 mb-2">Select Students</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label v-for="student in students" :key="student.id" class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="checkbox" v-model="releaseForm.student_ids" :value="student.id" class="bg-gray-800 border-gray-600 rounded text-brand-500 focus:ring-brand-500" />
                                {{ student.name }}
                            </label>
                        </div>
                        <p v-if="releaseForm.errors.student_ids" class="text-red-400 text-xs mt-1">{{ releaseForm.errors.student_ids }}</p>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit" :disabled="releaseForm.processing"
                            class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                            Release to Students
                        </button>
                    </div>
                </form>
            </div>

            <!-- Active Assignments -->
            <div>
                <h2 class="text-lg font-bold text-gray-100 mb-4">Active Assignments</h2>
                
                <div v-if="activeAssignments.length === 0" class="bg-gray-800 border border-gray-700 rounded-xl p-12 text-center">
                    <p class="text-gray-500 text-sm">No active assignments right now.</p>
                </div>
                
                <div v-else class="space-y-4">
                    <div v-for="assignment in activeAssignments" :key="assignment.id"
                        class="bg-gray-800 border border-gray-700 rounded-xl p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-gray-100">{{ assignment.template.title }}</h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-green-500/10 text-green-400 border border-green-500/20">Active</span>
                                    <span v-if="isTeacher" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-brand-500/10 text-brand-400 border border-brand-500/20">
                                        {{ assignment.visibility === 'all' ? 'All Students' : assignment.students.length + ' Students' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Released: {{ new Date(assignment.created_at).toLocaleDateString() }}
                                    <span v-if="assignment.due_date" class="ml-2 text-red-400">Due: {{ new Date(assignment.due_date).toLocaleString() }}</span>
                                </p>
                            </div>
                            
                            <!-- Teacher Actions -->
                            <div v-if="isTeacher" class="flex items-center gap-2">
                                <Link :href="route('classroom.assignments.submissions', assignment.id)"
                                    class="px-4 py-1.5 rounded-lg bg-gray-900 border border-gray-700 text-gray-300 hover:text-white text-sm font-medium transition-colors">
                                    View Submissions ({{ assignment.submissions_count || 0 }})
                                </Link>
                                <button @click="closeAssignment(assignment.id)"
                                    class="px-4 py-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 text-sm font-medium transition-colors">
                                    Close
                                </button>
                            </div>
                            
                            <!-- Student Actions -->
                            <div v-else>
                                <span v-if="assignment.my_submission" class="px-3 py-1.5 rounded-lg bg-green-500/10 text-green-400 text-sm font-medium border border-green-500/20">
                                    Submitted
                                </span>
                                <button v-else @click="openSubmissionModal(assignment)"
                                    class="px-4 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium transition-colors">
                                    Submit Work
                                </button>
                            </div>
                        </div>
                        
                        <div class="prose prose-sm prose-invert max-w-none text-gray-400 bg-gray-900/50 p-4 rounded-lg border border-gray-700/50" v-html="md.render(assignment.template.description_markdown)"></div>
                        
                        <!-- Show student their score if graded -->
                        <div v-if="!isTeacher && assignment.my_submission?.status === 'graded'" class="mt-4 p-4 bg-gray-900 border border-gray-700 rounded-lg flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-300">Grade & Feedback</p>
                                <p class="text-sm text-gray-400 mt-1">{{ assignment.my_submission.comments || 'No feedback provided.' }}</p>
                            </div>
                            <div class="text-xl font-bold" :class="assignment.my_submission.score >= 50 ? 'text-green-400' : 'text-red-400'">
                                {{ assignment.my_submission.score }} / 100
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Closed Assignments -->
            <div v-if="closedAssignments.length > 0" class="mt-12">
                <h2 class="text-lg font-bold text-gray-100 mb-4 opacity-75">Closed Assignments</h2>
                <div class="space-y-4 opacity-75 hover:opacity-100 transition-opacity">
                    <div v-for="assignment in closedAssignments" :key="assignment.id"
                        class="bg-gray-800/50 border border-gray-700 rounded-xl p-5">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-medium text-gray-300">{{ assignment.template.title }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Closed on {{ new Date(assignment.updated_at).toLocaleDateString() }}</p>
                            </div>
                            <Link v-if="isTeacher" :href="route('classroom.assignments.submissions', assignment.id)"
                                class="text-sm text-brand-400 hover:text-brand-300">
                                View Submissions ({{ assignment.submissions_count || 0 }})
                            </Link>
                            <div v-else class="text-sm">
                                <span v-if="assignment.my_submission" class="text-green-400 font-medium flex items-center gap-2">
                                    Submitted
                                    <span v-if="assignment.my_submission.status === 'graded'" class="text-gray-400 text-xs">Score: {{ assignment.my_submission.score }}/100</span>
                                </span>
                                <span v-else class="text-red-400 font-medium">Missed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Modal (Student Only) -->
            <div v-if="activeSubmissionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="activeSubmissionModal = null">
                <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl p-6 max-w-md w-full">
                    <h3 class="text-lg font-bold text-gray-100 mb-4">Submit Assignment: {{ activeSubmissionModal.template.title }}</h3>
                    <form @submit.prevent="submitWork">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">GitHub Repository URL (Optional if uploading file)</label>
                                <input v-model="submitForm.github_url" type="url"
                                    class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                    placeholder="https://github.com/your-username/repo">
                                <p v-if="submitForm.errors.github_url" class="text-red-400 text-xs mt-1">{{ submitForm.errors.github_url }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Live URL (Optional)</label>
                                <input v-model="submitForm.live_url" type="url"
                                    class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 placeholder-gray-600"
                                    placeholder="https://my-app.vercel.app">
                                <p v-if="submitForm.errors.live_url" class="text-red-400 text-xs mt-1">{{ submitForm.errors.live_url }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Upload File (ZIP, PDF, etc. - Max 10MB)</label>
                                <input @change="handleFileUpload" type="file"
                                    class="w-full bg-gray-900 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20">
                                <p v-if="submitForm.errors.attachment" class="text-red-400 text-xs mt-1">{{ submitForm.errors.attachment }}</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="activeSubmissionModal = null" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-gray-200 transition-colors">Cancel</button>
                            <button type="submit" :disabled="submitForm.processing"
                                class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50">
                                Submit Work
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Make markdown dark theme friendly */
.color-scheme-dark {
    color-scheme: dark;
}
.prose-invert pre {
    background-color: #111827 !important;
    border: 1px solid #374151;
}
.prose-invert code {
    color: #93c5fd;
}
.prose-invert h1, .prose-invert h2, .prose-invert h3, .prose-invert h4, .prose-invert strong {
    color: #f3f4f6;
}
.prose-invert a {
    color: #60a5fa;
    text-decoration: none;
}
.prose-invert a:hover {
    text-decoration: underline;
}
</style>
