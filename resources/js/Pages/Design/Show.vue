<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MermaidDiagram from '@/Components/MermaidDiagram.vue';

const props = defineProps({ blueprint: Object });

const isProcessing = ref(false);
const error = ref(null);
const uiuxDesign = ref(props.blueprint.uiux_design);
const designStatus = ref(props.blueprint.design_status);
const copiedId = ref(null);

async function copyPrompt(prompt, id) {
    if (!prompt) return;
    try {
        await navigator.clipboard.writeText(prompt);
        copiedId.value = id;
        setTimeout(() => { copiedId.value = null; }, 2000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
}

let pollInterval = null;

// If the page loads while still processing from a previous session, resume polling
onMounted(() => {
    if (designStatus.value === 'processing') {
        startPolling();
    }
    if (uiuxDesign.value) {
        loadFonts(uiuxDesign.value.typography);
        applyDesignTokens(uiuxDesign.value);
    }
});

onUnmounted(() => stopPolling());

watch(uiuxDesign, (val) => {
    if (val) {
        loadFonts(val.typography);
        applyDesignTokens(val);
    }
});

function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
}

function startPolling() {
    isProcessing.value = true;
    stopPolling();
    pollInterval = setInterval(async () => {
        try {
            const res = await fetch(`/ai/design/${props.blueprint.id}/status`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() }
            });
            const data = await res.json();
            designStatus.value = data.design_status;

            if (data.design_status === 'completed') {
                uiuxDesign.value = data.uiux_design;
                isProcessing.value = false;
                error.value = null;
                stopPolling();
            } else if (data.design_status === 'failed') {
                error.value = 'Design generation failed. Please try again.';
                isProcessing.value = false;
                stopPolling();
            }
        } catch (e) {
            // Network blip — keep polling, don't stop
            console.warn('Poll error (will retry):', e);
        }
    }, 5000);
}

async function generateDesign(force = false) {
    if (isProcessing.value) return;
    if (uiuxDesign.value && !force) {
        if (!confirm('Regenerate the entire design? This will replace the current one.')) return;
    }
    error.value = null;
    isProcessing.value = true;
    uiuxDesign.value = null;
    designStatus.value = 'processing';

    try {
        const res = await fetch(`/ai/design/${props.blueprint.id}/generate`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        });
        const data = await res.json();
        if (data.queued) {
            startPolling();
        } else {
            error.value = data.error || 'Could not start generation.';
            isProcessing.value = false;
        }
    } catch (e) {
        error.value = 'Failed to contact the server. Please refresh and try again.';
        isProcessing.value = false;
    }
}

function csrfToken() {
    // Try meta tag first (added in app.blade.php)
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    // Fallback: read from XSRF-TOKEN cookie (set by Laravel automatically)
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function loadFonts(typography) {
    if (!typography) return;
    const fonts = new Set();
    ['display_font','heading_font','body_font','mono_font'].forEach(k => {
        if (typography[k]?.name) fonts.add(typography[k].name.replace(/ /g, '+'));
    });
    if (fonts.size > 0) {
        const link = document.createElement('link');
        link.href = `https://fonts.googleapis.com/css2?family=${[...fonts].join('&family=')}&display=swap`;
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
}

// Inject the AI-generated brand colors as CSS variables onto the page
function applyDesignTokens(design) {
    if (!design) return;
    const palette = design.color_system?.palette;
    const dark = design.color_system?.dark_mode;
    const root = document.documentElement;
    if (palette?.primary?.hex)   root.style.setProperty('--brand-primary',   palette.primary.hex);
    if (palette?.secondary?.hex) root.style.setProperty('--brand-secondary', palette.secondary.hex);
    if (palette?.accent?.hex)    root.style.setProperty('--brand-accent',    palette.accent.hex);
    if (dark?.background)        root.style.setProperty('--brand-bg',        dark.background);
    if (dark?.surface)           root.style.setProperty('--brand-surface',   dark.surface);
    if (dark?.border)            root.style.setProperty('--brand-border',    dark.border);
    if (dark?.text)              root.style.setProperty('--brand-text',      dark.text);
}

function getStyleForToken(token) {
    if (!uiuxDesign.value) return {};
    const t = uiuxDesign.value.typography;
    if (!t) return {};
    // Try exact token match first, then fall back to closest
    const scale = t.scale?.find(s => s.token === token)
        ?? t.scale?.find(s => s.token === 'h1')
        ?? t.scale?.[0];
    if (!scale) return {};
    // Pick font family: new schema uses body_font, old uses display_font/heading_font
    let fontFamily = t.body_font?.name || t.display_font?.name || 'system-ui';
    if (token.startsWith('h') && t.heading_font?.name) fontFamily = t.heading_font.name;
    return {
        fontFamily: `"${fontFamily}", sans-serif`,
        fontSize: `${scale.size}px`,
        fontWeight: scale.weight,
        lineHeight: scale.line_height ?? 1.2,
        letterSpacing: scale.letter_spacing ?? '0'
    };
}

// Helper: get motion token value, supports both old (duration_fast) and new (fast) keys
function motionVal(key) {
    const m = uiuxDesign.value?.design_tokens?.motion;
    if (!m) return '';
    return m[key] ?? m['duration_' + key] ?? m[Object.keys(m).find(k => k.includes(key))] ?? '';
}

// Helper: get spacing tokens as entries array
function spacingEntries() {
    const s = uiuxDesign.value?.design_tokens?.spacing;
    return s ? Object.entries(s) : [];
}

// Helper: get border radius tokens
function radiusEntries() {
    const r = uiuxDesign.value?.design_tokens?.border_radius;
    return r ? Object.entries(r) : [];
}

// Helper: get all motion entries
function motionEntries() {
    const m = uiuxDesign.value?.design_tokens?.motion;
    return m ? Object.entries(m) : [];
}
</script>

<template>
    <Head :title="`Design — ${blueprint.title}`" />
    <AuthenticatedLayout>
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gray-900/90 backdrop-blur-md border-b border-gray-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="route('ai.design')" class="p-2 -ml-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ blueprint.title }}</h1>
                    <p class="text-xs text-gray-400">UI/UX Design Specification</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Generating spinner -->
                <div v-if="isProcessing" class="flex items-center gap-2 px-4 py-2 bg-gray-700 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed">
                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Generating…
                </div>
                <!-- Regenerate button (shows when design exists) -->
                <button v-if="uiuxDesign && !isProcessing" @click="generateDesign(true)"
                    class="flex items-center gap-2 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 hover:text-white rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Regenerate Design
                </button>
                <!-- Generate button (shows when no design) -->
                <button v-if="!uiuxDesign && !isProcessing" @click="generateDesign()"
                    style="background: var(--brand-primary, #4f46e5);"
                    class="flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-medium transition-colors hover:opacity-90">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 14.93V17a1 1 0 0 1-2 0v-.07A8 8 0 0 1 4.07 9H5a1 1 0 0 1 0 2 6 6 0 0 0 6 6zm0-9.86A6 6 0 0 0 7 13a1 1 0 0 1-1 1H5.07A8 8 0 0 1 11 4.07V5a1 1 0 0 1 2 0v-.07A8 8 0 0 1 18.93 11H19a1 1 0 0 1 0 2 6 6 0 0 0-6-6z"/></svg>
                    Generate UI/UX Design
                </button>
                <!-- Design Ready badge -->
                <div v-if="uiuxDesign && !isProcessing" class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-medium border border-emerald-500/20">
                    Design Ready
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto bg-[#0F1117] text-gray-200">
            <!-- Error -->
            <div v-if="error" class="max-w-4xl mx-auto mt-8 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <span>{{ error }}</span>
            </div>

            <!-- Empty State -->
            <div v-if="!uiuxDesign && !isProcessing" class="max-w-3xl mx-auto mt-24 text-center px-4">
                <div class="w-20 h-20 mx-auto bg-gray-800/50 rounded-2xl flex items-center justify-center border border-gray-700/50 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-gray-500"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42"/></svg>
                </div>
                <h2 class="text-2xl font-semibold text-white mb-3">No Design Yet</h2>
                <p class="text-gray-400 text-lg mb-8 leading-relaxed">Trigger the Principal UX Architect to generate a complete design system, typography scale, color palette, and full screen inventory.</p>
                <button @click="generateDesign" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-900 hover:bg-gray-100 rounded-xl text-base font-medium transition-colors shadow-lg">
                    Generate UI/UX Design
                </button>
            </div>

            <!-- Processing State -->
            <div v-else-if="isProcessing && !uiuxDesign" class="max-w-2xl mx-auto mt-32 text-center">
                <div class="relative w-24 h-24 mx-auto mb-8">
                    <div class="absolute inset-0 border-t-2 border-indigo-500 rounded-full animate-spin"></div>
                    <div class="absolute inset-2 border-r-2 border-purple-500 rounded-full animate-spin" style="animation-direction:reverse;animation-duration:1.5s"></div>
                    <div class="absolute inset-4 border-b-2 border-emerald-500 rounded-full animate-spin" style="animation-duration:2s"></div>
                </div>
                <h3 class="text-xl font-medium text-white mb-2">Architecting Design System…</h3>
                <p class="text-gray-400 mb-1">The Principal UX Architect is working.</p>
                <p class="text-gray-500 text-sm">This takes 2–4 minutes. You can leave this page — we'll have it ready when you return.</p>
            </div>

            <!-- Design Spec -->
            <div v-else-if="uiuxDesign" class="pb-24">
                <!-- Hero -->
                <div class="border-b border-white/[0.08] py-16 px-6 sm:px-12 lg:px-24 bg-gradient-to-b from-gray-900 to-[#0F1117]">
                    <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-12 items-start">
                        <div class="w-32 h-32 md:w-48 md:h-48 rounded-3xl bg-gray-800/50 border border-white/10 flex items-center justify-center p-6 shrink-0 shadow-2xl"
                             v-html="uiuxDesign.brand?.logo?.svg_mark"
                             :style="`color:${uiuxDesign.color_system?.palette?.primary?.hex}`"></div>
                        <div>
                            <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-white mb-4" :style="getStyleForToken('h1')">{{ uiuxDesign.brand?.app_name }}</h1>
                            <p class="text-2xl text-gray-400 font-light mb-8">{{ uiuxDesign.brand?.tagline }}</p>
                            <div class="flex flex-wrap gap-3 mb-8">
                                <span v-for="trait in uiuxDesign.brand?.personality_traits" :key="trait" class="px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-sm font-medium text-gray-300">{{ trait }}</span>
                            </div>
                            <p class="text-lg text-gray-400 leading-relaxed max-w-3xl">{{ uiuxDesign.design_philosophy }}</p>
                        </div>
                    </div>
                </div>

                <div class="max-w-6xl mx-auto px-6 sm:px-12 lg:px-24 py-16 space-y-32">
                    <!-- Color System -->
                    <section>
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-8">01 / Color System</h2>
                        <p class="text-gray-400 mb-12 max-w-3xl leading-relaxed">{{ uiuxDesign.color_system?.rationale }}</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                            <div v-for="(color, name) in uiuxDesign.color_system?.palette" :key="name" class="group">
                                <div class="h-24 rounded-2xl border border-white/5 mb-3 shadow-sm transition-transform duration-300 group-hover:-translate-y-1" :style="`background-color:${color.hex}`"></div>
                                <h4 class="text-sm font-medium text-gray-200 capitalize">{{ name.replace(/_/g,' ') }}</h4>
                                <p class="text-xs text-gray-500 font-mono mt-1">{{ color.hex }}</p>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ color.usage }}</p>
                            </div>
                        </div>
                    </section>

                    <hr class="border-white/[0.08]">

                    <!-- Typography -->
                    <section>
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-8">02 / Typography</h2>
                        <p class="text-gray-400 mb-12 max-w-3xl leading-relaxed">{{ uiuxDesign.typography?.rationale }}</p>
                        <div class="bg-white/[0.02] border border-white/[0.08] rounded-2xl overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead><tr class="border-b border-white/[0.08] text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="p-6 w-1/4">Token</th>
                                    <th class="p-6 w-1/2">Specimen</th>
                                    <th class="p-6 w-1/4 hidden md:table-cell">Properties</th>
                                </tr></thead>
                                <tbody class="divide-y divide-white/[0.04]">
                                    <tr v-for="scale in uiuxDesign.typography?.scale" :key="scale.token" class="hover:bg-white/[0.02] transition-colors">
                                        <td class="p-6"><span class="px-2.5 py-1 rounded-md bg-white/5 text-gray-300 text-xs font-mono">{{ scale.token }}</span></td>
                                        <td class="p-6 overflow-hidden"><div :style="getStyleForToken(scale.token)" class="text-gray-200 truncate max-w-[500px]">The quick brown fox</div></td>
                                        <td class="p-6 hidden md:table-cell text-sm text-gray-500 font-mono leading-relaxed">{{ scale.size }}px / wt{{ scale.weight }} / lh{{ scale.line_height ?? '–' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <hr class="border-white/[0.08]">

                    <!-- Design Tokens -->
                    <section>
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-8">03 / Core Tokens</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
                            <div>
                                <h3 class="text-sm font-medium text-gray-300 mb-6">Spacing Scale</h3>
                                <div class="space-y-3">
                                    <div v-for="[key, val] in spacingEntries()" :key="key" class="flex items-center gap-4">
                                        <div class="w-12 text-xs text-gray-500 font-mono text-right">{{ key }}</div>
                                        <div class="h-6 bg-indigo-500/20 rounded-sm border border-indigo-500/40" :style="`width:${Math.min(Number(val), 300)}px`"></div>
                                        <div class="text-xs text-gray-600 font-mono">{{ val }}px</div>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-12">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-300 mb-6">Border Radius</h3>
                                    <div class="flex flex-wrap gap-4">
                                        <div v-for="[key, val] in radiusEntries()" :key="key"
                                             class="w-16 h-16 bg-white/5 border border-white/20 flex items-center justify-center text-xs text-gray-400 font-mono"
                                             :style="`border-radius:${Number(val) >= 9999 ? '9999px' : val+'px'}`">{{ key }}</div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-300 mb-6">Motion</h3>
                                    <div class="bg-white/[0.02] border border-white/[0.08] rounded-xl p-6 text-sm text-gray-400 font-mono space-y-2">
                                        <div v-for="[key, val] in motionEntries()" :key="key" class="flex justify-between">
                                            <span>{{ key }}</span><span class="text-gray-500">{{ val }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <hr class="border-white/[0.08]">

                    <!-- Navigation Flow -->
                    <section v-if="uiuxDesign.navigation_flow_mermaid">
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-8">04 / User Journey</h2>
                        <div class="bg-gray-800/30 rounded-2xl border border-gray-700/50 p-6 overflow-hidden">
                            <MermaidDiagram :code="uiuxDesign.navigation_flow_mermaid" />
                        </div>
                    </section>

                    <hr class="border-white/[0.08]">

                    <!-- Screen Inventory -->
                    <section>
                        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-[0.2em] mb-8">05 / Screen Inventory</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="screen in uiuxDesign.screens" :key="screen.id"
                                 class="bg-white/[0.02] border border-white/[0.08] hover:border-white/20 rounded-2xl p-6 transition-colors flex flex-col h-full">
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-200">{{ screen.name }}</h3>
                                    <span class="px-2 py-1 rounded-md bg-white/10 text-gray-300 text-[10px] font-bold uppercase tracking-wider">{{ screen.category }}</span>
                                </div>
                                <div class="flex gap-2 mb-4">
                                    <span class="text-xs font-mono text-indigo-400 bg-indigo-500/10 px-2 py-1 rounded">{{ screen.route }}</span>
                                    <span class="text-xs text-gray-500 px-2 py-1 border border-gray-700/50 rounded">{{ screen.platform }}</span>
                                </div>
                                <p class="text-sm text-gray-400 mb-6">{{ screen.description }}</p>
                                <div class="space-y-6 flex-1">
                                    <div>
                                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Hierarchy</h4>
                                        <ol class="text-sm text-gray-300 space-y-2 pl-5 list-decimal marker:text-gray-600">
                                            <li v-for="(h,i) in screen.visual_hierarchy" :key="i" class="pl-1">{{ h }}</li>
                                        </ol>
                                    </div>
                                    <div v-if="screen.microcopy?.length">
                                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Microcopy</h4>
                                        <div class="space-y-2">
                                            <div v-for="(copy,i) in screen.microcopy" :key="i" class="bg-white/5 rounded-lg p-3 border-l-2 border-gray-600">
                                                <p class="text-[10px] text-gray-500 uppercase mb-1">{{ copy.element }}</p>
                                                <p class="text-sm text-gray-200 font-medium italic">"{{ copy.copy }}"</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="screen.interaction_states">
                                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">States</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="(desc, state) in screen.interaction_states" :key="state" class="group relative">
                                                <span class="px-2 py-1 text-[10px] font-medium rounded bg-gray-800 text-gray-300 border border-gray-700 cursor-help capitalize">{{ state }}</span>
                                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-48 p-2 bg-gray-800 text-xs text-gray-200 rounded shadow-xl border border-gray-700 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-10">{{ desc }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 border-t border-white/[0.04]">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-xs text-gray-500">Navigates To:</p>
                                        <button v-if="screen.image_prompt" @click="copyPrompt(screen.image_prompt, screen.id)" 
                                                class="text-[10px] flex items-center gap-1 font-medium px-2 py-1 rounded bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 transition-colors">
                                            <svg v-if="copiedId === screen.id" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                            {{ copiedId === screen.id ? 'Copied!' : 'Copy AI Prompt' }}
                                        </button>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-for="dest in screen.navigates_to" :key="dest" class="text-[10px] px-1.5 py-0.5 bg-white/[0.03] border border-white/[0.06] text-gray-400 rounded">{{ dest }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
