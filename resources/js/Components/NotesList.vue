<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Note } from '@/types';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    applicationId: number;
    notes: Note[];
}>();

const form = useForm({ body: '' });

const addNote = () => {
    form.post(route('applications.notes.store', props.applicationId), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const removeNote = (note: Note) => {
    if (confirm('Delete this note?')) {
        router.delete(route('notes.destroy', note.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <section>
        <h3 class="text-lg font-medium text-gray-900">Notes</h3>

        <form class="mt-4" @submit.prevent="addNote">
            <textarea
                v-model="form.body"
                rows="3"
                placeholder="Add a note..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <InputError class="mt-2" :message="form.errors.body" />
            <PrimaryButton class="mt-2" :disabled="form.processing">
                Add note
            </PrimaryButton>
        </form>

        <p v-if="notes.length === 0" class="mt-6 text-sm text-gray-500">
            No notes yet.
        </p>
        <ul v-else class="mt-6 space-y-4">
            <li
                v-for="note in notes"
                :key="note.id"
                class="rounded-md border border-gray-200 p-3"
            >
                <p class="whitespace-pre-line text-gray-900">{{ note.body }}</p>
                <div class="mt-2 flex items-center justify-between">
                    <time
                        class="text-xs text-gray-500"
                        :datetime="note.created_at"
                    >
                        {{ note.created_human }}
                    </time>
                    <DangerButton @click="removeNote(note)">Delete</DangerButton>
                </div>
            </li>
        </ul>
    </section>
</template>
