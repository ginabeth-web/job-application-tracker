<script setup lang="ts">
import ApplicationForm from '@/Components/ApplicationForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { StatusOption } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    statuses: StatusOption[];
}>();
</script>

<template>
    <Head title="New application" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                New application
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-8">
                    <ApplicationForm :statuses="statuses" class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
