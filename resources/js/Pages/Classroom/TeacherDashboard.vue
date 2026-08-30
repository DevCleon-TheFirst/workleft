<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Classroom Hub" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">

            <!-- Welcome Banner -->
            <div class="bg-brand-800 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background: radial-gradient(ellipse at top right, #fff 0%, transparent 70%)"></div>
                <div class="relative z-10">
                    <p class="text-brand-200 text-sm font-medium mb-1">Teacher Dashboard</p>
                    <h1 class="text-3xl font-bold mb-2">Classroom Hub</h1>
                    <p class="text-brand-100 text-sm max-w-xl">Manage your assignments, materials, and students all in one place.</p>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                <Link :href="route('classroom.bank.index')"
                    class="group bg-gray-800 border border-gray-700 hover:border-brand-600/60 rounded-xl p-5 flex items-center gap-4 transition-all duration-200 hover:bg-gray-700/50">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-600/15 flex items-center justify-center text-brand-400 group-hover:bg-brand-600/25 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">Assignment Bank</p>
                        <p class="text-xs text-gray-500 mt-0.5">Create reusable questions</p>
                    </div>
                </Link>

                <Link :href="route('classroom.assignments.index')"
                    class="group bg-gray-800 border border-gray-700 hover:border-brand-600/60 rounded-xl p-5 flex items-center gap-4 transition-all duration-200 hover:bg-gray-700/50">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-600/15 flex items-center justify-center text-brand-400 group-hover:bg-brand-600/25 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 4.5a.75.75 0 0 1 .721.544l.813 2.846a3.75 3.75 0 0 0 2.576 2.576l2.846.813a.75.75 0 0 1 0 1.442l-2.846.813a3.75 3.75 0 0 0-2.576 2.576l-.813 2.846a.75.75 0 0 1-1.442 0l-.813-2.846a3.75 3.75 0 0 0-2.576-2.576l-2.846-.813a.75.75 0 0 1 0-1.442l2.846-.813A3.75 3.75 0 0 0 7.466 7.89l.813-2.846A.75.75 0 0 1 9 4.5Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">Release &amp; Grade</p>
                        <p class="text-xs text-gray-500 mt-0.5">Send assignments, grade work</p>
                    </div>
                </Link>

                <Link :href="route('classroom.materials.index')"
                    class="group bg-gray-800 border border-gray-700 hover:border-brand-600/60 rounded-xl p-5 flex items-center gap-4 transition-all duration-200 hover:bg-gray-700/50">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-600/15 flex items-center justify-center text-brand-400 group-hover:bg-brand-600/25 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">Materials Vault</p>
                        <p class="text-xs text-gray-500 mt-0.5">Videos, docs &amp; resources</p>
                    </div>
                </Link>

                <Link :href="route('classroom.students.index')"
                    class="group bg-gray-800 border border-gray-700 hover:border-brand-600/60 rounded-xl p-5 flex items-center gap-4 transition-all duration-200 hover:bg-gray-700/50">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-600/15 flex items-center justify-center text-brand-400 group-hover:bg-brand-600/25 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">Student Roster</p>
                        <p class="text-xs text-gray-500 mt-0.5">Add &amp; manage students</p>
                    </div>
                </Link>

            </div>

            <!-- Recent Submissions Placeholder -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Recent Submissions</h2>
                </div>
                <div class="p-10 text-center">
                    <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 0 1 3.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 0 1 3.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Release an assignment to start receiving submissions.</p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
