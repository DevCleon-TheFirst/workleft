<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    chart: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        default: () => `mermaid-${Math.random().toString(36).slice(2, 9)}`,
    },
});

const container = ref(null);
const error = ref(null);
const mermaidReady = ref(false);

// Load Mermaid from CDN once globally
function loadMermaid() {
    if (window._mermaidLoaded) {
        mermaidReady.value = true;
        return;
    }
    if (window._mermaidLoading) {
        window._mermaidLoadCallbacks = window._mermaidLoadCallbacks || [];
        window._mermaidLoadCallbacks.push(() => { mermaidReady.value = true; });
        return;
    }
    window._mermaidLoading = true;
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js';
    script.onload = () => {
        window.mermaid.initialize({
            startOnLoad: false,
            theme: 'dark',
            themeVariables: {
                background: '#111827',
                primaryColor: '#6366f1',
                primaryTextColor: '#f3f4f6',
                primaryBorderColor: '#4f46e5',
                lineColor: '#6b7280',
                secondaryColor: '#1f2937',
                tertiaryColor: '#1f2937',
                edgeLabelBackground: '#1f2937',
                fontFamily: 'Inter, sans-serif',
            },
        });
        window._mermaidLoaded = true;
        mermaidReady.value = true;
        (window._mermaidLoadCallbacks || []).forEach(cb => cb());
    };
    document.head.appendChild(script);
}

async function renderChart() {
    if (!container.value || !props.chart || !window.mermaid) return;
    error.value = null;
    try {
        container.value.innerHTML = '';
        const { svg } = await window.mermaid.render(props.id, props.chart);
        container.value.innerHTML = svg;
    } catch (e) {
        error.value = `Diagram render error: ${e.message}`;
        console.error('Mermaid error:', e, '\nChart:\n', props.chart);
    }
}

onMounted(() => {
    loadMermaid();
});

watch(mermaidReady, (ready) => {
    if (ready) nextTick(renderChart);
});

watch(() => props.chart, () => {
    if (mermaidReady.value) nextTick(renderChart);
});
</script>

<template>
    <div class="mermaid-wrapper">
        <div v-if="error" class="text-red-400 text-sm p-3 bg-red-900/20 rounded border border-red-700/40">
            <p class="font-semibold mb-1">⚠️ Diagram could not be rendered</p>
            <p class="text-xs font-mono">{{ error }}</p>
            <details class="mt-2">
                <summary class="cursor-pointer text-xs text-gray-400">View raw Mermaid source</summary>
                <pre class="mt-2 text-xs text-gray-400 whitespace-pre-wrap">{{ chart }}</pre>
            </details>
        </div>
        <div v-else-if="!mermaidReady" class="flex items-center gap-2 text-gray-500 text-sm py-4">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Loading diagram renderer…
        </div>
        <div ref="container" class="mermaid-container overflow-x-auto" />
    </div>
</template>

<style scoped>
.mermaid-container :deep(svg) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}
</style>
