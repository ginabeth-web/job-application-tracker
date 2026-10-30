<script setup lang="ts">
import BoardColumn from '@/Components/BoardColumn.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BoardColumn as BoardColumnData } from '@/types';
import { Button } from '@/Components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { BellRing, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    columns: BoardColumnData[];
    dueCount: number;
}>();

// Local copy so dragging updates instantly; refreshed whenever the server sends new props.
const columns = ref<BoardColumnData[]>(props.columns);
watch(
    () => props.columns,
    (value) => {
        columns.value = value;
    },
);

const firstDueHref = computed(() => {
    const due = columns.value
        .flatMap((column) => column.applications)
        .find((application) => application.follow_up_due);

    return due ? `#application-${due.id}` : '#';
});
</script>

<template>
    <Head title="Board" />

    <AuthenticatedLayout>
        <template #header>Board</template>

        <template #actions>
            <Button as-child size="sm">
                <Link :href="route('applications.create')">
                    <Plus />
                    New application
                </Link>
            </Button>
        </template>

        <div class="min-w-0">
            <a
                v-if="dueCount > 0"
                :href="firstDueHref"
                class="mb-4 flex items-center gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-200 dark:hover:bg-amber-950"
            >
                <BellRing class="size-4 shrink-0" />
                {{ dueCount }}
                {{ dueCount === 1 ? 'follow-up' : 'follow-ups' }} due
            </a>
            <div class="flex snap-x gap-4 overflow-x-auto pb-4">
                <BoardColumn
                    v-for="column in columns"
                    :key="column.status"
                    v-model:applications="column.applications"
                    :status="column.status"
                    :label="column.label"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
