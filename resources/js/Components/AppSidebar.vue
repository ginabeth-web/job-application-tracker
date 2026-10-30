<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import UserMenu from '@/Components/UserMenu.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from '@/Components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { Briefcase, LayoutDashboard } from 'lucide-vue-next';

const items = [
    {
        title: 'Board',
        href: route('board'),
        icon: LayoutDashboard,
        active: route().current('board'),
    },
    {
        title: 'Applications',
        href: route('applications.index'),
        icon: Briefcase,
        active: route().current('applications.*'),
    },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('board')">
                            <div
                                class="flex aspect-square size-8 items-center justify-center rounded-lg bg-white/20"
                            >
                                <ApplicationLogo
                                    class="size-5 fill-current text-white"
                                />
                            </div>
                            <span class="truncate text-base font-semibold">
                                Job Tracker
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in items" :key="item.title">
                        <SidebarMenuButton
                            as-child
                            :is-active="item.active"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <UserMenu />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
