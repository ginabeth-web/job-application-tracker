<script setup lang="ts">
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { Textarea } from '@/Components/ui/textarea';
import { Note } from '@/types';
import { router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    applicationId: number;
    notes: Note[];
}>();

const form = useForm({ body: '' });

const addNote = () => {
    form.post(route('applications.notes.store', props.applicationId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Note added');
        },
    });
};

const removeNote = (note: Note) => {
    router.delete(route('notes.destroy', note.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Note deleted'),
    });
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Notes</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
            <form class="space-y-2" @submit.prevent="addNote">
                <Textarea
                    v-model="form.body"
                    rows="3"
                    aria-label="New note"
                    placeholder="Add a note..."
                />
                <InputError :message="form.errors.body" />
                <Button type="submit" :disabled="form.processing">Add note</Button>
            </form>

            <p v-if="notes.length === 0" class="text-sm text-muted-foreground">
                No notes yet.
            </p>
            <ul v-else class="space-y-3">
                <li
                    v-for="note in notes"
                    :key="note.id"
                    class="rounded-lg border bg-muted/40 p-3"
                >
                    <p class="whitespace-pre-line">{{ note.body }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <time
                            class="text-xs text-muted-foreground"
                            :datetime="note.created_at"
                        >
                            {{ note.created_human }}
                        </time>
                        <ConfirmDialog
                            title="Delete this note?"
                            description="This note will be permanently deleted."
                            @confirm="removeNote(note)"
                        >
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                aria-label="Delete note"
                            >
                                <Trash2 />
                            </Button>
                        </ConfirmDialog>
                    </div>
                </li>
            </ul>
        </CardContent>
    </Card>
</template>
