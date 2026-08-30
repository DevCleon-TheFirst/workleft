<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    sessionToken: String,
    sessionLabel: String,
    date:         String,
    isOpen:       Boolean,
    radiusMeters: Number,
    expiresAt:    String,
    alreadyDone:  Boolean,
});

// state: idle | locating | success | error | expired | done
const state   = ref(props.alreadyDone ? 'done' : (props.isOpen ? 'idle' : 'expired'));
const message = ref('');
const distance = ref(null);

const expiresDate = computed(() => props.expiresAt ? new Date(props.expiresAt) : null);
const timeLeft    = ref('');

// countdown
let timer;
function tick() {
    if (!expiresDate.value) return;
    const diff = expiresDate.value - Date.now();
    if (diff <= 0) { timeLeft.value = 'Expired'; state.value = 'expired'; clearInterval(timer); return; }
    const m = Math.floor(diff / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    timeLeft.value = `${m}m ${s.toString().padStart(2,'0')}s`;
}
onMounted(() => { tick(); timer = setInterval(tick, 1000); });

async function checkIn() {
    if (state.value !== 'idle') return;
    state.value = 'locating';
    message.value = '';

    if (!navigator.geolocation) {
        state.value = 'error';
        message.value = 'Your browser does not support location services.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                          ?? usePage().props.csrf_token;
                const res  = await fetch(`/checkin/${props.sessionToken}`, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body:    JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
                });
                const data = await res.json();
                if (data.ok) {
                    state.value    = 'success';
                    distance.value = data.distance;
                    message.value  = data.message;
                    clearInterval(timer);
                } else {
                    state.value   = 'error';
                    message.value = data.error;
                    distance.value = data.distance ?? null;
                }
            } catch {
                state.value   = 'error';
                message.value = 'Network error — please try again.';
            }
        },
        (err) => {
            state.value   = 'error';
            message.value = err.code === 1
                ? 'Location permission denied. Please allow location access and try again.'
                : 'Could not get your location. Make sure GPS is on.';
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
}
</script>

<template>
    <Head :title="sessionLabel ? `Check-in: ${sessionLabel}` : 'Class Check-In'" />

    <div class="min-h-screen bg-gray-900 flex flex-col items-center justify-center px-4 py-10">

        <!-- Card -->
        <div class="w-full max-w-sm rounded-2xl overflow-hidden border border-gray-700 bg-gray-800 shadow-2xl">

            <!-- Header bar -->
            <div class="px-6 py-5 border-b border-gray-700 text-center"
                 :class="state === 'success' ? 'bg-emerald-600/20' : state === 'expired' ? 'bg-red-600/10' : 'bg-brand-600/10'">
                <div class="flex items-center justify-center mb-3">
                    <!-- Book icon -->
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         :class="state === 'success' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-brand-500/20 text-brand-400'">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-lg font-bold text-white">{{ sessionLabel || 'Class Check-In' }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">{{ date }}</p>
            </div>

            <!-- Body -->
            <div class="px-6 py-8 flex flex-col items-center gap-6 text-center">

                <!-- Already done -->
                <template v-if="state === 'done'">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <p class="text-emerald-400 font-semibold text-lg">Already Checked In</p>
                    <p class="text-gray-400 text-sm">You've already checked in for this session.</p>
                </template>

                <!-- Expired -->
                <template v-else-if="state === 'expired'">
                    <div class="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-400" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <p class="text-red-400 font-semibold text-lg">Session Expired</p>
                    <p class="text-gray-400 text-sm">This check-in window is no longer active.</p>
                </template>

                <!-- Success -->
                <template v-else-if="state === 'success'">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-emerald-400 font-bold text-xl">You're Checked In!</p>
                        <p class="text-gray-300 text-sm mt-2">{{ message }}</p>
                    </div>
                    <div class="bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-xs text-gray-400 w-full">
                        <span class="text-gray-500">Distance from class:</span>
                        <span class="text-white font-semibold ml-1">{{ distance }}m</span>
                    </div>
                </template>

                <!-- Error -->
                <template v-else-if="state === 'error'">
                    <div class="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <p class="text-red-400 text-sm font-medium">{{ message }}</p>
                    <button @click="state = 'idle'" class="text-xs text-brand-400 underline">Try again</button>
                </template>

                <!-- Idle / Locating -->
                <template v-else>
                    <!-- Timer -->
                    <div class="bg-gray-900 border border-gray-700 rounded-xl px-5 py-3 text-center w-full">
                        <p class="text-xs text-gray-500 mb-0.5">Session closes in</p>
                        <p class="text-2xl font-mono font-bold text-white">{{ timeLeft }}</p>
                    </div>

                    <p class="text-gray-400 text-sm">
                        You must be within <span class="text-white font-semibold">{{ radiusMeters }}m</span> of the classroom to check in.
                    </p>

                    <!-- Check in button -->
                    <button
                        @click="checkIn"
                        :disabled="state === 'locating'"
                        class="w-full py-4 rounded-2xl text-base font-bold text-white transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-3"
                        style="background: linear-gradient(135deg, #6366f1, #8b5cf6);"
                    >
                        <svg v-if="state === 'locating'" class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                        </svg>
                        {{ state === 'locating' ? 'Getting your location…' : 'Check In Now' }}
                    </button>

                    <p class="text-xs text-gray-600">Your location is only used to verify attendance. It is not stored beyond this session.</p>
                </template>

            </div>
        </div>
    </div>
</template>
