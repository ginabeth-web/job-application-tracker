<script setup lang="ts">
import { Application } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{
    application: Application;
}>();
</script>

<template>
    <div
        :id="`application-${application.id}`"
        class="cursor-grab rounded-md border border-gray-200 bg-white p-3 shadow-xs"
    >
        <Link
            :href="route('applications.show', application.id)"
            class="block font-medium text-gray-900 hover:underline"
        >
            {{ application.company }}
        </Link>
        <p class="text-sm text-gray-600">{{ application.role }}</p>
        <span
            v-if="application.follow_up_due"
            class="mt-1 inline-block rounded-sm bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800"
        >
            Follow up
        </span>
        <p v-if="application.applied_on" class="mt-1 text-xs text-gray-500">
            Applied {{ application.applied_on }}
        </p>
        <p v-if="application.notes_count" class="mt-1 text-xs text-gray-500">
            {{ application.notes_count }}
            {{ application.notes_count === 1 ? 'note' : 'notes' }}
        </p>
    </div>
</template>
