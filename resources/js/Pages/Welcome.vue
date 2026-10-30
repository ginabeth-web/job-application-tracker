<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Button } from '@/Components/ui/button';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion?: string;
    phpVersion?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center gap-8 bg-background px-6 text-center"
    >
        <Head title="Welcome" />

        <ApplicationLogo class="size-16 fill-current text-primary" />

        <div class="space-y-3">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                Job Tracker
            </h1>
            <p class="mx-auto max-w-md text-lg text-muted-foreground">
                Keep every application, note and follow-up in one colourful
                board, from wishlist to offer.
            </p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <Button v-if="$page.props.auth.user" as-child size="lg">
                <Link :href="route('board')">Go to board</Link>
            </Button>

            <template v-else>
                <Button v-if="canLogin" as-child size="lg">
                    <Link :href="route('login')">Log in</Link>
                </Button>
                <Button
                    v-if="canRegister"
                    as-child
                    size="lg"
                    variant="outline"
                >
                    <Link :href="route('register')">Register</Link>
                </Button>
            </template>
        </div>
    </div>
</template>
