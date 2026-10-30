<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Card, CardContent } from '@/Components/ui/card';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center gap-6 bg-background p-6"
    >
        <Link href="/" class="flex items-center gap-3">
            <ApplicationLogo class="size-10 fill-current text-primary" />
            <span class="text-xl font-semibold">Job Tracker</span>
        </Link>

        <Card class="w-full max-w-sm">
            <CardContent>
                <slot />
            </CardContent>
        </Card>
    </div>
</template>
