<script setup lang="ts">
import ApplicationForm from '@/Components/ApplicationForm.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Application, StatusOption } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    application: Application;
    statuses: StatusOption[];
}>();
</script>

<template>
    <Head title="Edit application" />

    <AuthenticatedLayout>
        <template #header>Edit application</template>

        <div class="mx-auto max-w-3xl">
            <ApplicationForm :application="application" :statuses="statuses" />
        </div>
    </AuthenticatedLayout>
</template>
