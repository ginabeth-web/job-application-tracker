<script setup lang="ts">
import { Application } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{
    application: Application;
}>();
</script>

<template>
    <div
        class="cursor-grab rounded-md border border-gray-200 bg-white p-3 shadow-sm"
    >
        <Link
            :href="route('applications.show', application.id)"
            class="block font-medium text-gray-900 hover:underline"
        >
            {{ application.company }}
        </Link>
        <p class="text-sm text-gray-600">{{ application.role }}</p>
        <p v-if="application.applied_on" class="mt-1 text-xs text-gray-500">
            Applied {{ application.applied_on }}
        </p>
    </div>
</template>
