<script setup lang="ts">
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { statusStyles } from '@/lib/status';
import { cn } from '@/lib/utils';
import { Application } from '@/types';
import { Link } from '@inertiajs/vue3';
import { CalendarDays, MessageSquare } from 'lucide-vue-next';

// The root must stay a single plain element (no leading comment): it is the direct draggable item.
defineProps<{
    application: Application;
}>();
</script>

<template>
    <div
        :id="`application-${application.id}`"
        class="group relative scroll-mt-20 rounded-xl"
    >
        <Link
            :href="route('applications.show', application.id)"
            :aria-label="`Open ${application.company}`"
            class="absolute inset-0 z-10 cursor-grab rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        />
        <Card
            :class="
                cn(
                    'gap-2 py-3 transition-shadow group-hover:shadow-md',
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
                        title="Applied"
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
    </div>
</template>
