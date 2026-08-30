<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});

const today = computed(() => new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));

const props = defineProps({
    recentProjects: Array,
    todayTasks: Array,
    upcomingMeetings: Array,
    aiMessage: String,
});

const taskDoneState = ref(props.todayTasks.map(t => t.done));
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8 min-h-full">

            <!-- Welcome Card -->
            <div class="bg-brand-800 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-brand-100 font-medium mb-1">{{ today }}</p>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ greeting }}, {{ user?.name?.split(' ')[0] }}!</h1>
                    <p class="text-lg text-brand-50 max-w-2xl leading-relaxed">{{ aiMessage }}</p>
                    
                    <div class="mt-8 flex flex-wrap gap-4">
                        <button class="bg-white text-brand-800 hover:bg-gray-50 font-medium px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                            Create New Project
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Immediate Priorities: Tasks & Meetings -->
                <div class="space-y-8">
                    <!-- Tasks -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">What to do today</h2>
                        
                        <div v-if="todayTasks.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-6">
                            You're all caught up! Enjoy your day.
                        </div>
                        
                        <div class="space-y-3" v-else>
                            <label v-for="(task, i) in todayTasks" :key="i" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                <input type="checkbox" v-model="taskDoneState[i]" class="w-5 h-5 rounded border-gray-300 text-brand-600 focus:ring-brand-500 bg-gray-50 dark:bg-gray-700 dark:border-gray-600" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-base" :class="taskDoneState[i] ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200 font-medium'">{{ task.title }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Meetings -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Upcoming Meetings</h2>
                        
                        <div v-if="upcomingMeetings.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-6">
                            No meetings scheduled for today.
                        </div>

                        <div class="space-y-4" v-else>
                            <div v-for="meeting in upcomingMeetings" :key="meeting.title" class="flex items-center gap-5 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                <div class="flex-shrink-0 text-center w-16">
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ meeting.time.split(':')[0] }}<span class="text-sm font-normal">{{ meeting.time.includes('AM') ? 'AM' : 'PM' }}</span></p>
                                </div>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700"></div>
                                <div class="flex-1">
                                    <p class="text-base font-semibold text-gray-900 dark:text-gray-200">{{ meeting.title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ meeting.duration }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projects & Actions -->
                <div class="space-y-8">
                    <!-- Projects -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Active Projects</h2>
                            <a href="#" class="text-sm text-brand-600 hover:text-brand-500 font-medium">See all</a>
                        </div>
                        
                        <div v-if="recentProjects.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-6">
                            No active projects.
                        </div>

                        <div class="grid grid-cols-1 gap-4" v-else>
                            <div v-for="project in recentProjects" :key="project.name" class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-500 transition-colors cursor-pointer bg-white dark:bg-gray-800">
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ project.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">For {{ project.client }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Quick Links</h2>
                        <div class="space-y-3">
                            <Link :href="route('ai.intelligence')" class="flex items-center gap-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors text-gray-800 dark:text-gray-200">
                                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M14.447 3.026a.75.75 0 0 1 .527.921l-4.5 16.5a.75.75 0 0 1-1.448-.394l4.5-16.5a.75.75 0 0 1 .921-.527ZM16.72 6.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L21.44 12l-4.72-4.72a.75.75 0 0 1 0-1.06Zm-9.44 0a.75.75 0 0 1 0 1.06L2.56 12l4.72 4.72a.75.75 0 0 1-1.06 1.06L.97 12.53a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">AI Intelligence</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">View insights & news</p>
                                </div>
                            </Link>

                            <Link :href="route('ai.planner')" class="flex items-center gap-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors text-gray-800 dark:text-gray-200">
                                <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M10 1a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 1ZM5.05 6.464A.75.75 0 1 1 6.11 5.409l1.06 1.06a.75.75 0 0 1-1.06 1.06l-1.06-1.06ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10Zm11 0a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 16 10Zm-7 4a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 9 14Zm-.53-2.47a.75.75 0 0 0-1.061 0l-1.06 1.06a.75.75 0 1 0 1.06 1.06l1.061-1.06a.75.75 0 0 0 0-1.06Zm5.657-5.657a.75.75 0 0 0-1.061 0l-1.06 1.06a.75.75 0 1 0 1.06 1.06l1.06-1.06a.75.75 0 0 0 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">Plan with AI</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Start a new project plan</p>
                                </div>
                            </Link>

                            <Link :href="route('documents.create')" class="flex items-center gap-4 p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-750 transition-colors text-gray-800 dark:text-gray-200">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                        <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 0 0 3 3.5v13A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5V7.621a1.5 1.5 0 0 0-.44-1.06l-4.12-4.122A1.5 1.5 0 0 0 11.378 2H4.5Zm2.25 8.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">My Notes</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Jot down quick thoughts</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
