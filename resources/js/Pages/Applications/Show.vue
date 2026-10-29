<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NotesList from '@/Components/NotesList.vue';
import { Application, Note } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    application: Application;
    notes: Note[];
}>();

const destroy = () => {
    if (confirm('Delete this application?')) {
        router.delete(route('applications.destroy', props.application.id));
    }
};
</script>

<template>
    <Head :title="`${application.company} - ${application.role}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ application.company }} - {{ application.role }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <dl class="max-w-xl space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="text-gray-900">{{ application.status_label }}</dd>
                        </div>
                        <div v-if="application.url">
                            <dt class="text-sm font-medium text-gray-500">Job posting</dt>
                            <dd>
                                <a
                                    :href="application.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-indigo-600 hover:underline"
                                >
                                    {{ application.url }}
                                </a>
                            </dd>
                        </div>
                        <div v-if="application.location">
                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                            <dd class="text-gray-900">{{ application.location }}</dd>
                        </div>
                        <div v-if="application.salary">
                            <dt class="text-sm font-medium text-gray-500">Salary</dt>
                            <dd class="text-gray-900">{{ application.salary }}</dd>
                        </div>
                        <div v-if="application.applied_on">
                            <dt class="text-sm font-medium text-gray-500">Applied on</dt>
                            <dd class="text-gray-900">{{ application.applied_on }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex items-center gap-4">
                        <Link
                            :href="route('applications.edit', application.id)"
                            class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                        >
                            Edit
                        </Link>
                        <DangerButton @click="destroy">Delete</DangerButton>
                        <Link
                            :href="route('applications.index')"
                            class="text-sm text-gray-600 underline hover:text-gray-900"
                        >
                            Back to list
                        </Link>
                    </div>
                </div>

                <div class="mt-6 bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <NotesList :application-id="application.id" :notes="notes" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
