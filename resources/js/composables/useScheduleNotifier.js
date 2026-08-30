import { ref, onMounted, onUnmounted } from 'vue';

// ── Audio helpers ──────────────────────────────────────────────────────────────────
let _sharedCtx = null;

function getCtx() {
    if (!_sharedCtx || _sharedCtx.state === 'closed') {
        _sharedCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return _sharedCtx;
}

// Call this on ANY user interaction to unlock audio for the session
export function unlockAudio() {
    try {
        const ctx = getCtx();
        if (ctx.state === 'suspended') {
            ctx.resume().then(() => console.log('[Audio] Context unlocked, state:', ctx.state));
        }
        // Play silent sound synchronously to unlock on strict browsers (iOS/Safari)
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        gain.gain.setValueAtTime(0, ctx.currentTime);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.01);
    } catch (e) {
        console.error('[Audio] unlock error:', e);
    }
}

if (typeof document !== 'undefined') {
    document.addEventListener('click', unlockAudio, { once: true });
    document.addEventListener('keydown', unlockAudio, { once: true });
}

function beep({ frequency = 880, duration = 0.3, volume = 0.4, type = 'sine', startTimeOffset = 0 } = {}) {
    try {
        const ctx = getCtx();
        if (ctx.state === 'suspended') {
            ctx.resume().then(() => _scheduleBeep(ctx, frequency, duration, volume, type, startTimeOffset));
        } else {
            _scheduleBeep(ctx, frequency, duration, volume, type, startTimeOffset);
        }
    } catch (e) {
        console.error('[Audio] beep error:', e);
    }
}

function _scheduleBeep(ctx, frequency, duration, volume, type, startTimeOffset) {
    const startTime = ctx.currentTime + startTimeOffset;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = type;
    osc.frequency.setValueAtTime(frequency, startTime);
    gain.gain.setValueAtTime(volume, startTime);
    gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);
    osc.start(startTime);
    osc.stop(startTime + duration);
    console.log('[Audio] beep played, freq:', frequency, 'ctx state:', ctx.state);
}

// Three-tone alert: descending chime for "time's up"
function playEndChime() {
    beep({ frequency: 880, duration: 0.25, volume: 0.5, startTimeOffset: 0 });
    beep({ frequency: 660, duration: 0.25, volume: 0.45, startTimeOffset: 0.28 });
    beep({ frequency: 440, duration: 0.4, volume: 0.4, startTimeOffset: 0.56 });
}

// Two-tone rising ping for "coming up soon"
function playWarningPing() {
    beep({ frequency: 660, duration: 0.2, volume: 0.35, startTimeOffset: 0 });
    beep({ frequency: 880, duration: 0.2, volume: 0.35, startTimeOffset: 0.24 });
}

// ── Composable ────────────────────────────────────────────────────────────────
export function useScheduleNotifier() {
    const toasts = ref([]);

    function addToast(id, message, severity = 'warning', autoClose = 8000) {
        // dedupe
        if (toasts.value.find(t => t.id === id)) return;
        toasts.value.push({ id, message, severity, ts: Date.now() });
        if (autoClose) {
            setTimeout(() => dismissToast(id), autoClose);
        }
    }

    function dismissToast(id) {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }

    onMounted(async () => {
        // Request browser notification permission once
        if (Notification.permission === 'default') {
            await Notification.requestPermission();
        }

        // Listen for Echo Events (pushed by Laravel Queue Workers via Reverb)
        if (window.Echo) {
            console.log('[Notifier] Echo found, connecting to schedules channel...');
            window.Echo.channel('schedules')
                .listen('ScheduleStartingSoon', (e) => {
                    console.log('[Notifier] ScheduleStartingSoon received:', e);
                    playWarningPing();
                    const label = e.type === 'meeting' ? '📅 Meeting' : '✅ Task';
                    const key = `warn-${e.type}-${e.id}`;
                    addToast(key, `${label} starting soon: "${e.title}"`, 'warning', 10000);

                    if (Notification.permission === 'granted') {
                        new Notification(`${label} Starting Soon`, {
                            body: e.title,
                            icon: '/logo.png',
                        });
                    }
                })
                .listen('ScheduleTimeUp', (e) => {
                    console.log('[Notifier] ScheduleTimeUp received:', e);
                    playEndChime();
                    const key = `end-${e.type}-${e.id}`;
                    addToast(key, `⏰ Time's up: "${e.title}" time is over.`, 'danger', 12000);

                    if (Notification.permission === 'granted') {
                        new Notification("⏰ Time's up!", { body: e.title, icon: '/logo.png' });
                    }
                });
            console.log('[Notifier] Listening on schedules channel.');
        } else {
            console.warn('[Notifier] window.Echo not found! Reverb may not be configured.');
        }
    });

    onUnmounted(() => {
        if (window.Echo) {
            window.Echo.leave('schedules');
        }
    });

    return { toasts, dismissToast };
}
