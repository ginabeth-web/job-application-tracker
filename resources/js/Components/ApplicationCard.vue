<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { statusStyles } from '@/lib/status';
import { cn } from '@/lib/utils';
import { Application } from '@/types';
import { Link } from '@inertiajs/vue3';
import { CalendarDays, MessageSquare } from 'lucide-vue-next';

defineProps<{
    application: Application;
}>();
</script>

<template>
    <!-- The Link is the single root element, so it is the direct draggable item. -->
    <Link
        :id="`application-${application.id}`"
        :href="route('applications.show', application.id)"
        class="block cursor-grab rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
    >
        <Card
            :class="
                cn(
                    'gap-2 py-3 transition-shadow hover:shadow-md',
                    statusStyles(application.status).stripe,
                )
            "
        >
            <CardContent class="space-y-2 px-3">
                <div>
                    <p class="font-semibold leading-tight">
                        {{ application.company }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                        {{ application.role }}
                    </p>
                </div>
                <div
                    v-if="
                        application.applied_on ||
                        application.notes_count ||
                        application.follow_up_due
                    "
                    class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground"
                >
                    <span
                        v-if="application.applied_on"
                        class="inline-flex items-center gap-1"
                    >
                        <CalendarDays class="size-3.5" />
                        {{ application.applied_on }}
                    </span>
                    <span
                        v-if="application.notes_count"
                        class="inline-flex items-center gap-1"
                        :title="`${application.notes_count} ${application.notes_count === 1 ? 'note' : 'notes'}`"
                    >
                        <MessageSquare class="size-3.5" />
                        {{ application.notes_count }}
                    </span>
                    <Badge
                        v-if="application.follow_up_due"
                        class="border-transparent bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                    >
                        Follow up
                    </Badge>
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
