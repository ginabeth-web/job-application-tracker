<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Application } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    applications: Application[];
}>();
</script>

<template>
    <Head title="Applications" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Applications
                </h2>
                <Link
                    :href="route('applications.create')"
                    class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                >
                    New application
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <p v-if="applications.length === 0" class="text-gray-600">
                        No applications yet. Add your first one.
                    </p>
                    <ul v-else class="divide-y divide-gray-200">
                        <li
                            v-for="application in applications"
                            :key="application.id"
                            class="flex items-center justify-between py-3"
                        >
                            <Link
                                :href="route('applications.show', application.id)"
                                class="font-medium text-gray-900 hover:underline"
                            >
                                {{ application.company }} - {{ application.role }}
                            </Link>
                            <span
                                class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700"
                            >
                                {{ application.status_label }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
