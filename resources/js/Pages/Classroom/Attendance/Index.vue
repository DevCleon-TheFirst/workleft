<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';

const props = defineProps({
    roster: Array, date: String, session: String,
    pastSessions: Array, activeSession: Object,
});

const selectedDate = ref(props.date);
const sessionLabel = ref(props.session ?? '');
const saving = ref(false); const saved = ref(false);
const rows = ref(props.roster.map(s => ({ ...s, status: s.status ?? 'present', note: s.note ?? '' })));
watch(() => props.roster, val => { rows.value = val.map(s => ({ ...s, status: s.status ?? 'present', note: s.note ?? '' })); });

const total   = computed(() => rows.value.length);
const present = computed(() => rows.value.filter(r => r.status === 'present').length);
const late    = computed(() => rows.value.filter(r => r.status === 'late').length);
const absent  = computed(() => rows.value.filter(r => r.status === 'absent').length);

function loadDate() {
    router.get(route('classroom.attendance.index'), { date: selectedDate.value, session: sessionLabel.value }, { preserveState: false });
}
function cycleStatus(row) {
    const order = ['present', 'late', 'absent'];
    row.status = order[(order.indexOf(row.status) + 1) % order.length];
}
function markAll(s) { rows.value.forEach(r => r.status = s); }

async function saveAll() {
    saving.value = true; saved.value = false;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    await fetch(route('classroom.attendance.store'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Inertia': 'true' },
        body: JSON.stringify({ date: props.date, session: sessionLabel.value || null, records: rows.value.map(r => ({ student_id: r.id, status: r.status, note: r.note })) }),
    });
    saving.value = false; saved.value = true;
    setTimeout(() => saved.value = false, 3000);
}

function badgeClass(s) {
    return { present: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30', late: 'bg-amber-500/15 text-amber-400 border-amber-500/30', absent: 'bg-red-500/15 text-red-400 border-red-500/30' }[s] ?? '';
}
function methodBadge(m) { return m === 'self_checkin' ? 'bg-brand-500/15 text-brand-400' : 'bg-gray-700 text-gray-500'; }

// ── Session management ──────────────────────────────────────────────────────
const showSessionModal = ref(false);
const sessionForm = ref({ latitude: null, longitude: null, radius_meters: 100, duration_mins: 30, session_label: '' });
const locating = ref(false); const opening = ref(false);
const currentSession = ref(props.activeSession);
const qrUrl = ref(currentSession.value?.checkin_url ?? '');
const sessionTimer = ref('');
const liveCheckins = ref(0);
const liveRoster = ref([]);

// QR code via google charts API (no npm install needed)
const qrSrc = computed(() => qrUrl.value
    ? `https://chart.googleapis.com/chart?cht=qr&chs=220x220&chl=${encodeURIComponent(qrUrl.value)}&choe=UTF-8`
    : '');

function getMyLocation() {
    locating.value = true;
    navigator.geolocation.getCurrentPosition(
        pos => { sessionForm.value.latitude = pos.coords.latitude; sessionForm.value.longitude = pos.coords.longitude; locating.value = false; },
        ()  => { alert('Could not get location. Please enable GPS.'); locating.value = false; },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

async function openSession() {
    opening.value = true;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const res = await fetch(route('classroom.attendance.sessions.open'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ ...sessionForm.value, date: props.date }),
    });
    const data = await res.json();
    opening.value = false;
    if (res.ok) {
        currentSession.value = data;
        qrUrl.value = data.checkin_url;
        showSessionModal.value = false;
        startPolling(data.id);
        startTimer(data.expires_at);
    } else { alert(data.message ?? 'Error opening session.'); }
}

async function closeSession() {
    if (!currentSession.value) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    await fetch(route('classroom.attendance.sessions.close', currentSession.value.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    currentSession.value = null; qrUrl.value = ''; sessionTimer.value = '';
    stopPolling(); stopTimer();
    router.reload({ only: ['roster'] });
}

// Countdown timer
let timerInterval;
function startTimer(expiresAt) {
    stopTimer();
    timerInterval = setInterval(() => {
        const diff = new Date(expiresAt) - Date.now();
        if (diff <= 0) { sessionTimer.value = 'Expired'; stopTimer(); currentSession.value = null; return; }
        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        sessionTimer.value = `${m}m ${s.toString().padStart(2,'0')}s`;
    }, 1000);
}
function stopTimer() { clearInterval(timerInterval); }

// Live poll
let pollInterval;
async function startPolling(sessionId) {
    stopPolling();
    const poll = async () => {
        const res = await fetch(route('classroom.attendance.sessions.status', sessionId));
        const data = await res.json();
        liveCheckins.value = data.checked_in;
        liveRoster.value = data.roster;
        if (!data.is_open) { stopPolling(); }
    };
    await poll();
    pollInterval = setInterval(poll, 5000);
}
function stopPolling() { clearInterval(pollInterval); }

// On mount, resume if active session
if (currentSession.value) {
    startTimer(currentSession.value.expires_at);
    startPolling(currentSession.value.id);
}
onUnmounted(() => { stopTimer(); stopPolling(); });

function copyLink() { navigator.clipboard.writeText(qrUrl.value).then(() => alert('Link copied!')); }
</script>

<template>
    <Head title="Attendance Register"/>
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Attendance Register</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Mark students or open a geofenced self-check-in session.</p>
                </div>
                <div class="flex gap-2">
                    <button v-if="!currentSession" @click="showSessionModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-500 text-white transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                        Open Check-In Session
                    </button>
                    <button @click="saveAll" :disabled="saving"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all disabled:opacity-50"
                        :class="saved ? 'bg-emerald-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'">
                        {{ saved ? 'Saved!' : saving ? 'Saving…' : 'Save Manually' }}
                    </button>
                </div>
            </div>

            <!-- Active Session Banner -->
            <div v-if="currentSession" class="bg-brand-900/30 border border-brand-600/40 rounded-2xl p-5 flex flex-col md:flex-row gap-5">
                <!-- QR -->
                <div class="flex-shrink-0 flex flex-col items-center gap-2">
                    <div class="bg-white p-2 rounded-xl shadow-lg">
                        <img :src="qrSrc" width="160" height="160" alt="Check-in QR" class="rounded"/>
                    </div>
                    <button @click="copyLink" class="text-xs text-brand-400 hover:underline">Copy Link</button>
                </div>
                <!-- Info -->
                <div class="flex-1 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <p class="text-sm font-semibold text-white">Session Live</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Closes in <span class="text-white font-mono font-semibold">{{ sessionTimer }}</span></p>
                            <p class="text-xs text-gray-500 mt-0.5">Geofence radius: {{ currentSession.radius_meters }}m</p>
                        </div>
                        <button @click="closeSession" class="text-xs px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/30 border border-red-500/30 transition-colors">Close Session</button>
                    </div>
                    <!-- Live roster -->
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Live check-ins: <span class="text-white font-semibold">{{ liveCheckins }}</span> / {{ total }}</p>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="s in liveRoster" :key="s.id"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium"
                                :class="s.status === 'present' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-700/60 text-gray-500'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="s.status === 'present' ? 'bg-emerald-400' : 'bg-gray-600'"></span>
                                {{ s.name }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 break-all">{{ currentSession.checkin_url }}</p>
                </div>
            </div>

            <!-- Date / Session picker -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Date</label>
                    <input type="date" v-model="selectedDate" class="w-full bg-gray-900 border border-gray-600 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"/>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Session <span class="text-gray-600">(optional)</span></label>
                    <input type="text" v-model="sessionLabel" placeholder="e.g. Week 3 – Morning" class="w-full bg-gray-900 border border-gray-600 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"/>
                </div>
                <div class="flex items-end">
                    <button @click="loadDate" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-medium transition-colors">Load</button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-emerald-400">{{ present }}</p>
                    <p class="text-xs text-emerald-500 mt-0.5">Present</p>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-amber-400">{{ late }}</p>
                    <p class="text-xs text-amber-500 mt-0.5">Late</p>
                </div>
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-4 text-center">
                    <p class="text-2xl font-bold text-red-400">{{ absent }}</p>
                    <p class="text-xs text-red-500 mt-0.5">Absent</p>
                </div>
            </div>

            <!-- Mark all -->
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-500 uppercase tracking-wide font-medium">Mark all:</span>
                <button @click="markAll('present')" class="px-3 py-1 rounded-lg text-xs font-medium bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 border border-emerald-500/30 transition-colors">Present</button>
                <button @click="markAll('late')"    class="px-3 py-1 rounded-lg text-xs font-medium bg-amber-500/15 text-amber-400 hover:bg-amber-500/25 border border-amber-500/30 transition-colors">Late</button>
                <button @click="markAll('absent')"  class="px-3 py-1 rounded-lg text-xs font-medium bg-red-500/15 text-red-400 hover:bg-red-500/25 border border-red-500/30 transition-colors">Absent</button>
            </div>

            <!-- Roster -->
            <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                <div v-if="rows.length === 0" class="py-16 text-center text-gray-500 text-sm">
                    No students enrolled yet.
                </div>
                <table v-else class="w-full text-sm">
                    <thead class="bg-gray-900/50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left w-8">#</th>
                            <th class="px-5 py-3 text-left">Student</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left hidden md:table-cell">Note</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Via</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-gray-700/30 transition-colors">
                            <td class="px-5 py-3.5 text-gray-600 text-xs font-mono">{{ idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-600/20 flex items-center justify-center text-brand-400 text-xs font-bold flex-shrink-0">{{ row.name.charAt(0).toUpperCase() }}</div>
                                    <div>
                                        <p class="text-gray-100 font-medium">{{ row.name }}</p>
                                        <p class="text-gray-500 text-xs">{{ row.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <button @click="cycleStatus(row)" :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all', badgeClass(row.status)]">
                                    {{ { present:'Present', late:'Late', absent:'Absent' }[row.status] }}
                                </button>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <input type="text" v-model="row.note" placeholder="Note…" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-2.5 py-1.5 text-xs text-gray-300 placeholder-gray-600 focus:outline-none focus:border-brand-500"/>
                            </td>
                            <td class="px-4 py-3.5 hidden lg:table-cell">
                                <span v-if="row.method" :class="['px-2 py-0.5 rounded text-xs font-medium', methodBadge(row.method)]">
                                    {{ row.method === 'self_checkin' ? 'Self' : 'Manual' }}
                                    <span v-if="row.distance_meters" class="ml-1 text-gray-500">{{ row.distance_meters }}m</span>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Past sessions -->
            <div v-if="pastSessions.length" class="space-y-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Past Sessions</p>
                <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-900/50 text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Date</th>
                                <th class="px-4 py-2.5 text-left">Session</th>
                                <th class="px-4 py-2.5 text-center">Present</th>
                                <th class="px-4 py-2.5 text-center">Late</th>
                                <th class="px-4 py-2.5 text-center">Absent</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr v-for="ps in pastSessions" :key="ps.date + ps.session_label" class="hover:bg-gray-700/20">
                                <td class="px-4 py-2.5 text-gray-300">{{ ps.date }}</td>
                                <td class="px-4 py-2.5 text-gray-400">{{ ps.session_label || '–' }}</td>
                                <td class="px-4 py-2.5 text-center text-emerald-400 font-semibold">{{ ps.present_count }}</td>
                                <td class="px-4 py-2.5 text-center text-amber-400 font-semibold">{{ ps.late_count }}</td>
                                <td class="px-4 py-2.5 text-center text-red-400 font-semibold">{{ ps.absent_count }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <button @click="selectedDate = ps.date; sessionLabel = ps.session_label ?? ''; loadDate()" class="text-brand-400 hover:underline">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Open Session Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showSessionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @click.self="showSessionModal = false">
                    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md space-y-5 shadow-2xl">
                        <h2 class="text-lg font-bold text-white">Open Geofenced Check-In</h2>
                        <p class="text-sm text-gray-400">Students scan a QR code on their phone and must be physically inside the geofence radius to check in.</p>

                        <!-- Location -->
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-400">Classroom Location</label>
                            <button @click="getMyLocation" :disabled="locating"
                                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-gray-700 bg-gray-800 hover:bg-gray-700 text-sm text-gray-200 transition-colors disabled:opacity-50">
                                <svg class="w-4 h-4 text-brand-400" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/></svg>
                                {{ locating ? 'Getting location…' : sessionForm.latitude ? `${sessionForm.latitude.toFixed(4)}, ${sessionForm.longitude.toFixed(4)}` : 'Use My Current Location' }}
                            </button>
                        </div>

                        <!-- Radius -->
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-400">Geofence Radius: <span class="text-white">{{ sessionForm.radius_meters }}m</span></label>
                            <input type="range" v-model.number="sessionForm.radius_meters" min="20" max="500" step="10" class="w-full accent-brand-500"/>
                            <div class="flex justify-between text-xs text-gray-600"><span>20m (strict)</span><span>500m (wide)</span></div>
                        </div>

                        <!-- Duration -->
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-400">Session Duration</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="d in [10,15,30,60]" :key="d" @click="sessionForm.duration_mins = d"
                                    class="py-2 rounded-lg text-xs font-medium border transition-colors"
                                    :class="sessionForm.duration_mins === d ? 'bg-brand-600 border-brand-500 text-white' : 'bg-gray-800 border-gray-700 text-gray-400 hover:border-gray-500'">
                                    {{ d }}m
                                </button>
                            </div>
                        </div>

                        <!-- Session label -->
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-gray-400">Session Label <span class="text-gray-600">(optional)</span></label>
                            <input type="text" v-model="sessionForm.session_label" placeholder="e.g. Week 3 – Morning"
                                class="w-full bg-gray-800 border border-gray-700 text-gray-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"/>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button @click="showSessionModal = false" class="flex-1 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm transition-colors">Cancel</button>
                            <button @click="openSession" :disabled="!sessionForm.latitude || opening"
                                class="flex-1 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-colors disabled:opacity-50">
                                {{ opening ? 'Opening…' : 'Open Session' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
