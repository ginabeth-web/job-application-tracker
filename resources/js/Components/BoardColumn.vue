<script setup lang="ts">
import ApplicationCard from '@/Components/ApplicationCard.vue';
import { statusStyles } from '@/lib/status';
import { cn } from '@/lib/utils';
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
    <section
        :class="
            cn(
                'w-72 shrink-0 snap-start rounded-xl p-3',
                statusStyles(status).column,
            )
        "
    >
        <h3 class="mb-3 flex items-center justify-between text-sm font-semibold">
            <span :class="cn('flex items-center gap-2', statusStyles(status).title)">
                <span :class="cn('size-2.5 rounded-full', statusStyles(status).dot)" />
                {{ label }}
            </span>
            <span
                :class="
                    cn(
                        'rounded-full px-2 py-0.5 text-xs font-semibold',
                        statusStyles(status).count,
                    )
                "
            >
                {{ applications.length }}
            </span>
        </h3>
        <div class="relative">
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
            <!-- Sibling of the drop zone (not a list item), so it can't be dragged or counted. -->
            <div
                v-if="applications.length === 0"
                :class="
                    cn(
                        'pointer-events-none absolute inset-0 flex items-center justify-center rounded-lg border-2 border-dashed text-sm',
                        statusStyles(status).empty,
                    )
                "
            >
                No applications
            </div>
        </div>
    </section>
</template>
