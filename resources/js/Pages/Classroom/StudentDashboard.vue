<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Clock-in state ──────────────────────────────────────────────────────────
const session    = ref(null);      // active session data from server
const clockState = ref('idle');    // idle | locating | success | error | done
const clockMsg   = ref('');
const distance   = ref(null);

async function pollSession() {
    try {
        const res  = await fetch('/classroom/active-session', { headers: { Accept: 'application/json' } });
        const data = await res.json();
        session.value = data.active ? data : null;
        if (data.active && data.already_done && clockState.value === 'idle') {
            clockState.value = 'done';
        }
    } catch { /* silent */ }
}

let pollTimer;
onMounted(async () => {
    await pollSession();
    pollTimer = setInterval(pollSession, 10000);
});
onUnmounted(() => clearInterval(pollTimer));

// Countdown display
const timeLeft = ref('');
function startCountdown(expiresAt) {
    clearInterval(countTimer);
    const tick = () => {
        const diff = new Date(expiresAt) - Date.now();
        if (diff <= 0) { timeLeft.value = 'Closed'; session.value = null; clearInterval(countTimer); return; }
        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        timeLeft.value = `${m}m ${s.toString().padStart(2,'0')}s`;
    };
    tick();
    countTimer = setInterval(tick, 1000);
}
let countTimer;
const watchSession = computed(() => session.value?.expires_at);
import { watch } from 'vue';
watch(watchSession, val => { if (val) startCountdown(val); }, { immediate: true });

// ── Clock in action ─────────────────────────────────────────────────────────
async function clockIn() {
    if (!session.value || clockState.value !== 'idle') return;
    clockState.value = 'locating';

    if (!navigator.geolocation) {
        clockState.value = 'error';
        clockMsg.value = 'Your browser does not support location services.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                const res  = await fetch(`/checkin/${session.value.token}`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body:    JSON.stringify({ 
                        latitude: pos.coords.latitude, 
                        longitude: pos.coords.longitude,
                        accuracy: pos.coords.accuracy 
                    }),
                });
                const data = await res.json();
                if (data.ok) {
                    clockState.value = 'success';
                    distance.value   = data.distance;
                    clockMsg.value   = data.message;
                    clearInterval(countTimer);
                } else {
                    clockState.value = 'error';
                    clockMsg.value   = data.error;
                    distance.value   = data.distance ?? null;
                }
            } catch { clockState.value = 'error'; clockMsg.value = 'Network error. Try again.'; }
        },
        (err) => {
            clockState.value = 'error';
            clockMsg.value = err.code === 1
                ? 'Location denied. Please allow location access and try again.'
                : 'Could not get your location. Make sure GPS is on.';
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}
</script>

<template>
    <Head title="My Classroom"/>
    <AuthenticatedLayout>
        <div class="p-6 md:p-10 max-w-5xl mx-auto space-y-8">

            <!-- Welcome Banner -->
            <div class="bg-brand-800 rounded-2xl p-8 text-white shadow-lg relative overflow-hidden">
                <div class="absolute inset-0 opacity-10" style="background: radial-gradient(ellipse at top right, #fff 0%, transparent 70%)"></div>
                <div class="relative z-10">
                    <p class="text-brand-200 text-sm font-medium mb-1">Student Dashboard</p>
                    <h1 class="text-3xl font-bold mb-2">Welcome back, {{ user?.name?.split(' ')[0] }}!</h1>
                    <p class="text-brand-100 text-sm max-w-xl">Check your active assignments and study materials below.</p>
                </div>
            </div>

            <!-- ── CLOCK IN PANEL (only shows when teacher opens a session) ── -->
            <Transition name="slide">
                <div v-if="session && clockState !== 'done'">

                    <!-- Success state -->
                    <div v-if="clockState === 'success'"
                         class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5">
                        <div class="w-14 h-14 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-emerald-400 font-bold text-lg">You're Checked In!</p>
                            <p class="text-gray-300 text-sm mt-0.5">{{ clockMsg }}</p>
                        </div>
                    </div>

                    <!-- Error state -->
                    <div v-else-if="clockState === 'error'"
                         class="bg-red-500/10 border border-red-500/30 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5">
                        <div class="w-14 h-14 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-red-400 font-semibold">Check-In Failed</p>
                            <p class="text-gray-400 text-sm mt-0.5">{{ clockMsg }}</p>
                            <p v-if="distance" class="text-xs text-gray-500 mt-1">You were {{ distance }}m from the classroom. Required: within {{ session.radius_meters }}m.</p>
                        </div>
                        <button @click="clockState = 'idle'" class="text-xs px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-300 transition-colors flex-shrink-0">Try Again</button>
                    </div>

                    <!-- Active / locating state -->
                    <div v-else class="bg-gray-800 border-2 border-brand-500/50 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-5"
                         style="box-shadow: 0 0 30px rgba(99,102,241,0.15);">
                        <!-- Pulse dot -->
                        <div class="relative flex-shrink-0">
                            <div class="w-14 h-14 rounded-full bg-brand-600/20 flex items-center justify-center">
                                <svg class="w-7 h-7 text-brand-400" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="absolute top-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-400 border-2 border-gray-800 animate-pulse"></span>
                        </div>

                        <!-- Text -->
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex items-center justify-center sm:justify-start gap-2 mb-0.5">
                                <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wide">Attendance Open</span>
                            </div>
                            <p class="text-white font-semibold text-lg">{{ session.session_label || 'Class is in session' }}</p>
                            <p class="text-gray-400 text-sm">Closes in <span class="font-mono font-semibold text-white">{{ timeLeft }}</span> · must be within {{ session.radius_meters }}m</p>
                        </div>

                        <!-- Big clock-in button -->
                        <button
                            @click="clockIn"
                            :disabled="clockState === 'locating'"
                            class="w-full sm:w-auto flex-shrink-0 flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-2xl text-base font-bold text-white transition-all duration-200 disabled:opacity-60"
                            style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"
                            onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 8px 30px rgba(99,102,241,0.4)'"
                            onmouseout="this.style.transform=''; this.style.boxShadow=''"
                        >
                            <svg v-if="clockState === 'locating'" class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd"/>
                            </svg>
                            {{ clockState === 'locating' ? 'Getting location…' : 'Clock In' }}
                        </button>
                    </div>

                </div>
            </Transition>

            <!-- Already checked in badge -->
            <div v-if="clockState === 'done' && session"
                 class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                <p class="text-sm text-emerald-400 font-medium">Attendance recorded for today's session.</p>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Link :href="route('classroom.assignments.index')"
                    class="group bg-gray-800 border border-gray-700 hover:border-brand-600/60 rounded-xl p-5 flex items-center gap-4 transition-all duration-200 hover:bg-gray-700/50">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-brand-600/15 flex items-center justify-center text-brand-400 group-hover:bg-brand-600/25 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/>
                            <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375Zm9.586 4.594a.75.75 0 0 0-1.172-.938l-2.476 3.096-.908-.907a.75.75 0 0 0-1.06 1.06l1.5 1.5a.75.75 0 0 0 1.116-.062l3-3.75Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">My Assignments</p>
                        <p class="text-xs text-gray-500 mt-0.5">View &amp; submit your work</p>
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
                        <p class="text-sm font-semibold text-gray-100">Study Materials</p>
                        <p class="text-xs text-gray-500 mt-0.5">Videos, docs &amp; resources</p>
                    </div>
                </Link>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.slide-enter-active { transition: all 0.3s ease; }
.slide-leave-active { transition: all 0.2s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
