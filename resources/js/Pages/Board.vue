<script setup lang="ts">
import BoardColumn from '@/Components/BoardColumn.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { BoardColumn as BoardColumnData } from '@/types';
import { Button } from '@/Components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
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

        <div class="py-8">
            <div
                v-if="dueCount > 0"
                class="mx-auto mb-4 max-w-full px-4 sm:px-6 lg:px-8"
            >
                <a
                    :href="firstDueHref"
                    class="block rounded-md bg-amber-100 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-200"
                >
                    {{ dueCount }}
                    {{ dueCount === 1 ? 'follow-up' : 'follow-ups' }} due
                </a>
            </div>
            <div class="mx-auto flex max-w-full gap-4 overflow-x-auto px-4 sm:px-6 lg:px-8">
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
