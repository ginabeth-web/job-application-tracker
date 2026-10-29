<script setup lang="ts">
import ApplicationCard from '@/Components/ApplicationCard.vue';
import { Application, ApplicationStatus } from '@/types';
import { router } from '@inertiajs/vue3';
import { VueDraggable, type DraggableEvent } from 'vue-draggable-plus';

const props = defineProps<{
    status: ApplicationStatus;
    label: string;
}>();

const applications = defineModel<Application[]>('applications', {
    required: true,
});

// The dragged card comes from the event: the model above updates asynchronously,
// so reading it here would still show the pre-drop order.
const onDrop = (event: DraggableEvent<Application>) => {
    router.patch(
        route('applications.move', event.data.id),
        { status: props.status, position: event.newIndex ?? 0 },
        {
            preserveScroll: true,
            onError: () => router.reload({ only: ['columns'] }),
        },
    );
};
</script>

<template>
    <section class="w-72 shrink-0 rounded-lg bg-gray-200 p-3">
        <h3 class="mb-3 flex items-center justify-between text-sm font-semibold text-gray-700">
            {{ label }}
            <span class="rounded-full bg-white px-2 text-xs">
                {{ applications.length }}
            </span>
        </h3>
        <VueDraggable
            v-model="applications"
            group="board"
            class="min-h-24 space-y-2"
            @add="onDrop"
            @update="onDrop"
        >
            <ApplicationCard
                v-for="application in applications"
                :key="application.id"
                :application="application"
            />
        </VueDraggable>
        <p v-if="applications.length === 0" class="text-sm text-gray-500">
            No applications
        </p>
    </section>
</template>
