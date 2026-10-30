<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { statusStyles } from '@/lib/status';
import { cn } from '@/lib/utils';
import { Application } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    applications: Application[];
}>();

const open = (application: Application) => {
    router.visit(route('applications.show', application.id));
};
</script>

<template>
    <Head title="Applications" />

    <AuthenticatedLayout>
        <template #header>Applications</template>
        <template #actions>
            <Button as-child>
                <Link :href="route('applications.create')">
                    <Plus />
                    New application
                </Link>
            </Button>
        </template>

        <Card v-if="applications.length === 0">
            <CardContent class="flex flex-col items-center gap-4 py-10 text-center">
                <p class="text-muted-foreground">
                    No applications yet. Add your first one.
                </p>
                <Button as-child>
                    <Link :href="route('applications.create')">
                        <Plus />
                        New application
                    </Link>
                </Button>
            </CardContent>
        </Card>

        <Card v-else class="py-2">
            <CardContent class="px-2">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Company</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Applied</TableHead>
                            <TableHead>Follow up</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="application in applications"
                            :key="application.id"
                            class="cursor-pointer"
                            @click="open(application)"
                        >
                            <TableCell class="font-medium">
                                <Link
                                    :href="route('applications.show', application.id)"
                                    class="hover:underline"
                                    @click.stop
                                >
                                    {{ application.company }}
                                </Link>
                            </TableCell>
                            <TableCell>{{ application.role }}</TableCell>
                            <TableCell>
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
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ application.applied_on ?? '-' }}
                            </TableCell>
                            <TableCell>
                                <span class="inline-flex items-center gap-2">
                                    <span class="text-muted-foreground">
                                        {{ application.follow_up_on ?? '-' }}
                                    </span>
                                    <Badge
                                        v-if="application.follow_up_due"
                                        class="border-transparent bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        Follow up
                                    </Badge>
                                </span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
