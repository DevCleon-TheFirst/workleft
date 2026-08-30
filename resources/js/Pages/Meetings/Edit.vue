<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    meeting: Object,
    projects: Array,
});

// Format datetime-local correctly
const formatForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};

const form = useForm({
    project_id: props.meeting.project_id,
    title: props.meeting.title,
    scheduled_at: formatForInput(props.meeting.scheduled_at),
    transcript: props.meeting.transcript || '',
});

const submit = () => {
    form.put(route('meetings.update', props.meeting.id));
};
</script>

<template>
    <Head title="Edit Meeting" />

    <AuthenticatedLayout>
                <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">

            <div class="flex items-center gap-4">
                <Link :href="route('meetings.show', meeting.id)" class="text-gray-400 hover:text-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-100">
                    Edit Meeting: {{ meeting.title }}
                </h2>
            </div>
        
            </div>
            <div>

        <div class="py-2">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-800 border border-gray-700 sm:rounded-md">
                    <div class="p-6 text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <InputLabel for="title" value="Meeting Title" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <div>
                                <InputLabel for="project_id" value="Project" />
                                <select
                                    id="project_id"
                                    v-model="form.project_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value="" disabled>Select a project</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">
                                        {{ project.title }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.project_id" />
                            </div>

                            <div>
                                <InputLabel for="scheduled_at" value="Scheduled Date & Time" />
                                <TextInput
                                    id="scheduled_at"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    v-model="form.scheduled_at"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.scheduled_at" />
                            </div>

                            <div>
                                <InputLabel for="transcript" value="Transcript / Notes (Optional)" />
                                <p class="text-xs text-gray-400 mb-2">Changing the transcript will trigger a new AI Summary.</p>
                                <textarea
                                    id="transcript"
                                    v-model="form.transcript"
                                    class="mt-1 block w-full rounded-md border-gray-700 bg-gray-900 text-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    rows="6"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.transcript" />
                            </div>

                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('meetings.destroy', meeting.id)"
                                    method="delete"
                                    as="button"
                                    class="text-sm text-red-400 hover:text-red-300"
                                    preserve-scroll
                                >
                                    Delete Meeting
                                </Link>
                                <div class="flex gap-4">
                                    <Link :href="route('meetings.show', meeting.id)" class="text-sm text-gray-400 hover:text-gray-300 mt-2">
                                        Cancel
                                    </Link>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Update Meeting
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>
