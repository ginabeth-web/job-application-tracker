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
        <template #header>New application</template>

        <div class="mx-auto max-w-3xl">
            <ApplicationForm :statuses="statuses" />
        </div>
    </AuthenticatedLayout>
</template>
