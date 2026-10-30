<script setup lang="ts">
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import NotesList from '@/Components/NotesList.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { statusStyles } from '@/lib/status';
import { cn } from '@/lib/utils';
import { Application, Note } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ExternalLink, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    application: Application;
    notes: Note[];
}>();

const destroy = () => {
    router.delete(route('applications.destroy', props.application.id));
};
</script>

<template>
    <Head :title="`${application.company} - ${application.role}`" />

    <AuthenticatedLayout>
        <template #header>{{ application.company }}</template>
        <template #actions>
            <Button variant="outline" as-child>
                <Link :href="route('applications.edit', application.id)">
                    <Pencil />
                    Edit
                </Link>
            </Button>
            <ConfirmDialog
                title="Delete this application?"
                description="This will permanently delete the application and all of its notes."
                @confirm="destroy"
            >
                <Button variant="destructive">
                    <Trash2 />
                    Delete
                </Button>
            </ConfirmDialog>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{{ application.role }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm text-muted-foreground">Status</dt>
                            <dd class="mt-1">
                                <Badge
                                    :class="
                                        cn(
                                            'border-transparent',
                                            statusStyles(application.status).badge,
                                        )
                                    "
                                >
                                    {{ application.status_label }}
                                </Badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-muted-foreground">Role</dt>
                            <dd class="mt-1">{{ application.role }}</dd>
                        </div>
                        <div v-if="application.url" class="sm:col-span-2">
                            <dt class="text-sm text-muted-foreground">Job posting</dt>
                            <dd class="mt-1">
                                <a
                                    :href="application.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 break-all text-primary hover:underline"
                                >
                                    {{ application.url }}
                                    <ExternalLink class="size-3.5 shrink-0" />
                                </a>
                            </dd>
                        </div>
                        <div v-if="application.location">
                            <dt class="text-sm text-muted-foreground">Location</dt>
                            <dd class="mt-1">{{ application.location }}</dd>
                        </div>
                        <div v-if="application.salary">
                            <dt class="text-sm text-muted-foreground">Salary</dt>
                            <dd class="mt-1">{{ application.salary }}</dd>
                        </div>
                        <div v-if="application.applied_on">
                            <dt class="text-sm text-muted-foreground">Applied on</dt>
                            <dd class="mt-1">{{ application.applied_on }}</dd>
                        </div>
                        <div v-if="application.follow_up_on">
                            <dt class="text-sm text-muted-foreground">Follow up on</dt>
                            <dd class="mt-1 flex items-center gap-2">
                                {{ application.follow_up_on }}
                                <Badge
                                    v-if="application.follow_up_due"
                                    class="border-transparent bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    Follow up
                                </Badge>
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <NotesList :application-id="application.id" :notes="notes" />

            <Link
                :href="route('applications.index')"
                class="inline-block text-sm text-muted-foreground hover:text-foreground hover:underline"
            >
                Back to list
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
