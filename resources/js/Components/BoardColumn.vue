<script setup lang="ts">
import ApplicationCard from '@/Components/ApplicationCard.vue';
import { Application, ApplicationStatus } from '@/types';
import { router } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';

const props = defineProps<{
    status: ApplicationStatus;
    label: string;
}>();

const applications = defineModel<Application[]>('applications', {
    required: true,
});

const persist = (application: Application, position: number) => {
    router.patch(
        route('applications.move', application.id),
        { status: props.status, position },
        { preserveScroll: true },
    );
};

const onAdd = (event: { newIndex?: number }) => {
    const index = event.newIndex ?? 0;
    const application = applications.value[index];
    if (application) {
        persist(application, index);
    }
};

const onUpdate = (event: { newIndex?: number }) => {
    const index = event.newIndex ?? 0;
    const application = applications.value[index];
    if (application) {
        persist(application, index);
    }
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
            @add="onAdd"
            @update="onUpdate"
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
