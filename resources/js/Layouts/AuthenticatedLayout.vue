<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import QrcodeVue from 'qrcode.vue';
import { useScheduleNotifier, unlockAudio } from '@/composables/useScheduleNotifier.js';

const page = usePage();
const user = computed(() => page.props.auth.user);
const sidebarOpen = ref(true);
const audioUnlocked = ref(false);

const notifications = ref([]);
const unreadCount = ref(0);
const showNotifications = ref(false);

async function fetchNotifications() {
    try {
        const response = await fetch(route('notifications.index'));
        const data = await response.json();
        notifications.value = data.notifications;
        unreadCount.value = data.unreadCount;
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    }
}

async function markAsRead(id) {
    try {
        await fetch(route('notifications.read', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': page.props.csrf_token, 'Content-Type': 'application/json' }
        });
        fetchNotifications();
    } catch (e) {
        console.error('Failed to mark read', e);
    }
}

async function markAllAsRead() {
    try {
        await fetch(route('notifications.read-all'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': page.props.csrf_token, 'Content-Type': 'application/json' }
        });
        fetchNotifications();
        showNotifications.value = false;
    } catch (e) {
        console.error('Failed to mark all read', e);
    }
}

onMounted(() => {
    fetchNotifications();
    setInterval(fetchNotifications, 60000);
});

const { toasts, dismissToast } = useScheduleNotifier();

function handleUnlock() {
    unlockAudio();
    audioUnlocked.value = true;
}

const showSyncModal = ref(false);
const syncUrl = ref('');

async function fetchSyncUrl() {
    showSyncModal.value = true;
    syncUrl.value = ''; // Reset
    try {
        const response = await fetch(route('calendar.link'));
        const data = await response.json();
        syncUrl.value = data.url;
    } catch (e) {
        console.error('Failed to fetch sync URL', e);
    }
}


const userRole = computed(() => user.value.role);

const navGroups = computed(() => {
    const groups = [];

    if (userRole.value === 'teacher') {
        groups.push(
            {
                label: 'Workspace',
                items: [
                    { name: 'Dashboard', route: 'dashboard', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" /><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" /></svg>` },
                    { name: 'Projects', route: 'projects.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3V18a3 3 0 0 0 3 3h15ZM1.5 10.146V6a3 3 0 0 1 3-3h5.379a2.25 2.25 0 0 1 1.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 0 1 3 3v1.146A4.483 4.483 0 0 0 19.5 12h-15a4.483 4.483 0 0 0-3 1.146V10.146Z" /></svg>` },
                    { name: 'Tasks', route: 'tasks.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10.5 6h9.75a.75.75 0 0 1 0 1.5H10.5a.75.75 0 0 1 0-1.5Zm0 4.5h9.75a.75.75 0 0 1 0 1.5H10.5a.75.75 0 0 1 0-1.5Zm0 4.5h9.75a.75.75 0 0 1 0 1.5H10.5a.75.75 0 0 1 0-1.5Zm-4.5-9a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5Zm0 4.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5Zm0 4.5a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5Z" clip-rule="evenodd" /></svg>` },
                    { name: 'Clients', route: 'clients.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" /></svg>` },
                    { name: 'Meetings', route: 'meetings.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M4.5 4.5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h8.25a3 3 0 0 0 3-3v-9a3 3 0 0 0-3-3H4.5ZM19.94 18.75l-2.69-2.69V7.94l2.69-2.69c.944-.945 2.56-.276 2.56 1.06v11.38c0 1.336-1.616 2.005-2.56 1.06Z" /></svg>` },
                ]
            },
            {
                label: 'AI Tools',
                items: [
                    { name: 'Intelligence Hub', route: 'ai.intelligence', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M14.447 3.026a.75.75 0 0 1 .527.921l-4.5 16.5a.75.75 0 0 1-1.448-.394l4.5-16.5a.75.75 0 0 1 .921-.527ZM16.72 6.22a.75.75 0 0 1 1.06 0l5.25 5.25a.75.75 0 0 1 0 1.06l-5.25 5.25a.75.75 0 1 1-1.06-1.06L21.44 12l-4.72-4.72a.75.75 0 0 1 0-1.06Zm-9.44 0a.75.75 0 0 1 0 1.06L2.56 12l4.72 4.72a.75.75 0 0 1-1.06 1.06L.97 12.53a.75.75 0 0 1 0-1.06l5.25-5.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>` },
                    { name: 'Planner', route: 'ai.planner', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M16.5 7.5h-9v9h9v-9Z" /><path fill-rule="evenodd" d="M8.25 2.25A.75.75 0 0 1 9 3v.75h2.25V3a.75.75 0 0 1 1.5 0v.75H15V3a.75.75 0 0 1 1.5 0v.75h.75a3 3 0 0 1 3 3v.75H21A.75.75 0 0 1 21 9h-.75v2.25H21a.75.75 0 0 1 0 1.5h-.75V15H21a.75.75 0 0 1 0 1.5h-.75v.75a3 3 0 0 1-3 3h-.75V21a.75.75 0 0 1-1.5 0v-.75h-2.25V21a.75.75 0 0 1-1.5 0v-.75H9V21a.75.75 0 0 1-1.5 0v-.75h-.75a3 3 0 0 1-3-3v-.75H3A.75.75 0 0 1 3 15h.75v-2.25H3a.75.75 0 0 1 0-1.5h.75V9H3a.75.75 0 0 1 0-1.5h.75v-.75a3 3 0 0 1 3-3h.75V3a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>` },
                    { name: 'Design', route: 'ai.design', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M2.04 8.618A12 12 0 0 1 11.25 3h1.5a12 12 0 0 1 9.21 4.382l-.46.46a3 3 0 0 0 0 4.243l3.24 3.24A1.5 1.5 0 0 1 23.68 18c0 .828-.672 1.5-1.5 1.5H3C1.343 19.5 0 18.157 0 16.5c0-.828.672-1.5 1.5-1.5l3.24-3.24a3 3 0 0 0 0-4.243l-.46-.46a11.96 11.96 0 0 1-2.24-2.439Zm7.71 5.382a3 3 0 0 0 4.24 0l1.24-1.24a3 3 0 0 0-4.24-4.24l-1.24 1.24a3 3 0 0 0 0 4.24ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" /></svg>` },
                    { name: 'Architect', route: 'ai.architect', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.25 5.337c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.036 1.007-1.875 2.25-1.875S15 2.34 15 3.375c0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959 0 .332.278.598.61.578 1.91-.114 3.79-.342 5.632-.676a.75.75 0 0 1 .878.645 49.17 49.17 0 0 1 .376 5.452.657.657 0 0 1-.66.664c-.354 0-.675-.186-.958-.401a1.647 1.647 0 0 0-1.003-.349c-1.035 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401.31 0 .557.262.534.571a48.774 48.774 0 0 1-.595 4.845.75.75 0 0 1-.61.61c-1.82.317-3.673.533-5.555.642a.58.58 0 0 1-.611-.581c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.035-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959a.641.641 0 0 1-.658.643 49.118 49.118 0 0 1-4.708-.36.75.75 0 0 1-.645-.878c.293-1.614.504-3.257.629-4.924A.53.53 0 0 0 5.337 15c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.036 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.369 0 .713.128 1.003.349.283.215.604.401.959.401a.656.656 0 0 0 .659-.663 47.703 47.703 0 0 0-.31-4.82.75.75 0 0 1 .83-.832c1.343.155 2.703.254 4.077.294a.64.64 0 0 0 .657-.642Z" /></svg>` },
                    { name: 'Knowledge Base', route: 'documents.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" /></svg>` },
                ]
            }
        );
    }

    const classroomHubIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" /><path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" /></svg>`;
    const bookIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" /></svg>`;
    const clipIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd" /><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd" /></svg>`;

    const educationItems = userRole.value === 'teacher'
        ? [
            { name: 'Classroom Hub', route: 'classroom.index', icon: classroomHubIcon },
            { name: 'Assignment Bank', route: 'classroom.bank.index', icon: clipIcon },
            { name: 'Release & Grade', route: 'classroom.assignments.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M9 4.5a.75.75 0 0 1 .721.544l.813 2.846a3.75 3.75 0 0 0 2.576 2.576l2.846.813a.75.75 0 0 1 0 1.442l-2.846.813a3.75 3.75 0 0 0-2.576 2.576l-.813 2.846a.75.75 0 0 1-1.442 0l-.813-2.846a3.75 3.75 0 0 0-2.576-2.576l-2.846-.813a.75.75 0 0 1 0-1.442l2.846-.813A3.75 3.75 0 0 0 7.466 7.89l.813-2.846A.75.75 0 0 1 9 4.5ZM18 1.5a.75.75 0 0 1 .728.568l.258 1.036c.236.94.97 1.674 1.91 1.91l1.036.258a.75.75 0 0 1 0 1.456l-1.036.258c-.94.236-1.674.97-1.91 1.91l-.258 1.036a.75.75 0 0 1-1.456 0l-.258-1.036a2.625 2.625 0 0 0-1.91-1.91l-1.036-.258a.75.75 0 0 1 0-1.456l1.036-.258a2.625 2.625 0 0 0 1.91-1.91l.258-1.036A.75.75 0 0 1 18 1.5Z" clip-rule="evenodd" /></svg>` },
            { name: 'Attendance', route: 'classroom.attendance.index', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/></svg>` },
            { name: 'Materials Vault', route: 'classroom.materials.index', icon: bookIcon },
          ]
        : [
            { name: 'My Classroom', route: 'classroom.index', icon: classroomHubIcon },
            { name: 'Assignments', route: 'classroom.assignments.index', icon: clipIcon },
            { name: 'Materials', route: 'classroom.materials.index', icon: bookIcon },
          ];

    groups.push({
        label: 'Education',
        items: educationItems
    });

    groups.push({
        label: 'Settings',
        items: [
            { name: 'Profile', route: 'profile.edit', icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" /></svg>` },
        ]
    });

    return groups;
});

function isActive(routeName) {
    try {
        if (route().current(routeName)) return true;
        if (routeName.endsWith('.index')) {
            const prefix = routeName.replace('.index', '.*');
            return route().current(prefix);
        }
        return false;
    } catch { return false; }
}
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-900 text-gray-100 font-sans">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-16'" class="sidebar flex flex-col flex-shrink-0 transition-all duration-300 ease-in-out bg-gray-900 border-r border-gray-800">

            <!-- Logo -->
            <div class="flex items-center justify-center px-4 py-4 border-b border-gray-800 min-h-[80px]">
                <img src="/logo.png" alt="Cleon Innovations" class="object-contain transition-all duration-300" :class="sidebarOpen ? 'w-28 h-auto' : 'w-8 h-auto'" />
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
                <div v-for="group in navGroups" :key="group.label">
                    <Transition name="fade">
                        <p v-if="sidebarOpen" class="px-2 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ group.label }}</p>
                    </Transition>
                    <ul class="space-y-1">
                        <li v-for="item in group.items" :key="item.name">
                            <Link :href="route(item.route)"
                                class="flex items-center gap-3 rounded-md px-2 py-2 text-sm transition-colors duration-150"
                                :class="isActive(item.route)
                                    ? 'bg-gray-800 text-white font-medium'
                                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'">
                                <span class="flex-shrink-0" v-html="item.icon"></span>
                                <Transition name="fade">
                                    <span v-if="sidebarOpen" class="whitespace-nowrap">{{ item.name }}</span>
                                </Transition>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- User -->
            <div class="p-4 border-t border-gray-800">
                <div class="flex items-center gap-3 p-2 -mx-2 rounded-md cursor-pointer hover:bg-gray-800 transition-colors">
                    <div class="w-8 h-8 rounded bg-brand-600 flex-shrink-0 flex items-center justify-center text-xs font-bold text-white">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <Transition name="fade">
                        <div v-if="sidebarOpen" class="min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex flex-col flex-1 overflow-hidden bg-gray-900">
            <!-- Top bar -->
            <header class="flex-shrink-0 flex items-center justify-between px-6 h-14 bg-gray-900 border-b border-gray-800">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-1 text-gray-500 rounded hover:bg-gray-800 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Search -->
                    <div class="hidden md:flex items-center gap-2 rounded bg-gray-800 border border-gray-700 px-3 py-1.5 text-sm text-gray-400 min-w-[240px]">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 flex-shrink-0">
                            <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />
                        </svg>
                        <span>Search... <kbd class="ml-auto text-xs text-gray-500 font-mono">⌘K</kbd></span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="userRole === 'teacher'" @click="fetchSyncUrl" class="p-2 rounded-lg transition-colors hover:bg-white/10 text-gray-500 hover:text-white" title="Sync to Phone Calendar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M10.5 18.75a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" />
                            <path fill-rule="evenodd" d="M8.625.75A3.375 3.375 0 0 0 5.25 4.125v15.75a3.375 3.375 0 0 0 3.375 3.375h6.75a3.375 3.375 0 0 0 3.375-3.375V4.125A3.375 3.375 0 0 0 15.375.75h-6.75ZM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.71a.75.75 0 0 0 .5-.192l.478-.456A.75.75 0 0 1 11.21 2.25h1.58a.75.75 0 0 1 .521.102l.478.456a.75.75 0 0 0 .5.192h1.086c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 0 1 7.5 19.875V4.125Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Notification bell -->
                    <div class="relative">
                        <button @click="showNotifications = !showNotifications" class="relative p-2 rounded-lg transition-colors hover:bg-white/10" style="color:rgba(255,255,255,0.5);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Z" clip-rule="evenodd" />
                            </svg>
                            <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500 items-center justify-center text-[8px] font-bold text-white">{{ unreadCount }}</span>
                            </span>
                        </button>
                        
                        <!-- Dropdown -->
                        <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                                <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Mark all read</button>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                                    No new notifications
                                </div>
                                <div v-for="notif in notifications" :key="notif.id" 
                                     @click="notif.read_at ? null : markAsRead(notif.id)"
                                     :class="['px-4 py-3 border-b border-gray-100 dark:border-gray-750 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-750/50 cursor-pointer transition-colors', !notif.read_at ? 'bg-brand-50/50 dark:bg-brand-900/10' : '']">
                                    <div class="flex gap-3">
                                        <div class="text-xl shrink-0">{{ notif.icon }}</div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ notif.title }}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ notif.body }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Click away overlay -->
                        <div v-if="showNotifications" @click="showNotifications = false" class="fixed inset-0 z-40"></div>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="text-xs px-3 py-1.5 rounded-lg transition-colors hover:bg-white/10" style="color:rgba(255,255,255,0.5);">
                        Logout
                    </Link>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto relative">
                <slot />
            </main>
        </div>

        <!-- ── Sync Modal ─────────────────────────────────────────────── -->
        <Teleport v-if="userRole === 'teacher'" to="body">
            <Transition name="fade">
                <div v-if="showSyncModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showSyncModal = false">
                    <div class="bg-gray-900 border border-gray-800 rounded-xl shadow-2xl p-8 max-w-md w-full flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-brand-600/20 text-brand-500 rounded-full flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                <path d="M10.5 18.75a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" />
                                <path fill-rule="evenodd" d="M8.625.75A3.375 3.375 0 0 0 5.25 4.125v15.75a3.375 3.375 0 0 0 3.375 3.375h6.75a3.375 3.375 0 0 0 3.375-3.375V4.125A3.375 3.375 0 0 0 15.375.75h-6.75ZM7.5 4.125C7.5 3.504 8.004 3 8.625 3H9.71a.75.75 0 0 0 .5-.192l.478-.456A.75.75 0 0 1 11.21 2.25h1.58a.75.75 0 0 1 .521.102l.478.456a.75.75 0 0 0 .5.192h1.086c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-6.75A1.125 1.125 0 0 1 7.5 19.875V4.125Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Sync to Phone Calendar</h3>
                        <p class="text-gray-400 text-sm mb-6">Scan this code with your phone's camera to automatically sync tasks and alarms natively to your calendar.</p>
                        
                        <div class="bg-white p-4 rounded-xl shadow-inner min-h-[200px] flex items-center justify-center">
                            <span v-if="!syncUrl" class="text-gray-400 text-sm animate-pulse">Generating secure link...</span>
                            <qrcode-vue v-else :value="syncUrl" :size="200" level="M" />
                        </div>

                        <button @click="showSyncModal = false" class="mt-8 px-6 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ── Schedule Notification Toasts ──────────────────────────── -->
        <Teleport to="body">
            <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 w-80">
                <TransitionGroup name="toast">
                    <div
                        v-for="toast in toasts"
                        :key="toast.id"
                        :class="[
                            'flex items-start gap-3 rounded border px-4 py-3 text-sm shadow-lg cursor-pointer select-none',
                            toast.severity === 'danger'
                                ? 'bg-gray-950 border-brand-700 text-brand-300'
                                : 'bg-gray-950 border-gray-700 text-gray-200'
                        ]"
                        @click="dismissToast(toast.id)"
                    >
                        <!-- icon -->
                        <span class="flex-shrink-0 mt-0.5 text-base">
                            {{ toast.severity === 'danger' ? '⏰' : '🔔' }}
                        </span>
                        <span class="flex-1 leading-snug">{{ toast.message }}</span>
                        <button class="flex-shrink-0 text-gray-500 hover:text-gray-300 leading-none" @click.stop="dismissToast(toast.id)">✕</button>
                    </div>
                </TransitionGroup>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Toast slide-up animation */
.toast-enter-active {
    transition: all 0.25s ease;
}
.toast-leave-active {
    transition: all 0.2s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(16px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(24px);
}
.toast-move {
    transition: transform 0.2s ease;
}
</style>
