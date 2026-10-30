<script setup lang="ts">
import AppHeader from '@/Components/AppHeader.vue';
import AppSidebar from '@/Components/AppSidebar.vue';
import { SidebarInset, SidebarProvider } from '@/Components/ui/sidebar';

const defaultOpen = !document.cookie.includes('sidebar_state=false');
</script>

<template>
    <SidebarProvider :default-open="defaultOpen">
        <AppSidebar />
        <SidebarInset class="min-w-0">
            <AppHeader>
                <template #title>
                    <slot name="header" />
                </template>
                <template #actions>
                    <slot name="actions" />
                </template>
            </AppHeader>
            <div class="flex-1 p-4 md:p-6">
                <slot />
            </div>
        </SidebarInset>
    </SidebarProvider>
</template>
