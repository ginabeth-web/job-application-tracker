<script setup lang="ts">
import AppearanceToggle from '@/Components/AppearanceToggle.vue';
import { Separator } from '@/Components/ui/separator';
import { SidebarTrigger } from '@/Components/ui/sidebar';
</script>

<template>
    <header
        class="sticky top-0 z-10 flex h-14 shrink-0 items-center gap-2 border-b bg-background/80 px-4 backdrop-blur"
    >
        <SidebarTrigger class="-ml-1" />
        <Separator
            orientation="vertical"
            class="mr-2 data-[orientation=vertical]:h-4"
        />
        <div class="min-w-0 truncate text-base font-semibold">
            <slot name="title" />
        </div>
        <div class="flex-1" />
        <slot name="actions" />
        <AppearanceToggle />
    </header>
</template>
