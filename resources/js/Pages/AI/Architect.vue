<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const requirements = ref('');
const isProcessing = ref(false);
const architecture = ref(null);
const error = ref(null);

const generateArchitecture = async () => {
    if (!requirements.value.trim() || isProcessing.value) return;

    isProcessing.value = true;
    error.value = null;
    architecture.value = null;

    try {
        const reqArray = requirements.value.split('\n').filter(r => r.trim().length > 0);
        const response = await fetch(route('ai.planner.architecture'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ requirements: reqArray })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            architecture.value = data.architecture || data;
        } else {
            error.value = data.message || 'Failed to generate architecture.';
        }
    } catch (e) {
        console.error(e);
        error.value = 'Network error while connecting to the AI.';
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Head title="Architect" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <h2 class="text-xl font-semibold leading-tight text-gray-100 flex items-center gap-2">
                <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                Architect AI
            </h2>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <div class="overflow-hidden bg-gray-800 shadow-sm border border-gray-700 sm:rounded-lg">
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Paste raw requirements to generate a complete system architecture:</label>
                        <textarea
                            v-model="requirements"
                            class="block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono text-sm"
                            rows="6"
                            placeholder="- Needs user auth&#10;- Stripe integration&#10;- Video streaming..."
                        ></textarea>
                        
                        <div class="mt-4 flex justify-between items-center">
                            <div class="text-red-400 text-sm" v-if="error">{{ error }}</div>
                            <div v-else></div>
                            <PrimaryButton @click="generateArchitecture" :disabled="isProcessing || !requirements.trim()">
                                {{ isProcessing ? 'Generating...' : 'Generate Architecture' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Results Area -->
                <div v-if="isProcessing" class="flex justify-center py-12">
                    <div class="flex flex-col items-center text-gray-400">
                        <svg class="animate-spin h-8 w-8 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Architecting system components...</p>
                    </div>
                </div>

                <div v-else-if="architecture" class="overflow-hidden bg-gray-900 shadow-sm border border-emerald-500/30 sm:rounded-lg">
                    <div class="p-6 border-b border-gray-800 bg-gray-900/50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-emerald-400">System Architecture Result</h3>
                        <button class="text-xs text-gray-400 hover:text-gray-100" @click="() => { navigator.clipboard.writeText(JSON.stringify(architecture, null, 2)) }">Copy JSON</button>
                    </div>
                    <div class="p-6 text-gray-300 font-sans">
                        <pre class="whitespace-pre-wrap text-sm text-emerald-300/80">{{ JSON.stringify(architecture, null, 2) }}</pre>
                    </div>
                </div>

            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
