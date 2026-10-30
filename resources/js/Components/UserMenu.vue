<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { SidebarMenuButton, useSidebar } from '@/Components/ui/sidebar';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, LogOut, User } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);
const { isMobile } = useSidebar();

const initials = computed(
    () =>
        user.value.name
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0].toUpperCase())
            .join('') || '?',
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <SidebarMenuButton
                size="lg"
                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
                <Avatar class="size-8 rounded-lg">
                    <AvatarFallback
                        class="rounded-lg bg-amber-400 font-semibold text-amber-950"
                    >
                        {{ initials }}
                    </AvatarFallback>
                </Avatar>
                <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-medium">{{ user.name }}</span>
                    <span class="truncate text-xs opacity-80">
                        {{ user.email }}
                    </span>
                </div>
                <ChevronsUpDown class="ml-auto size-4" />
            </SidebarMenuButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
            :side="isMobile ? 'bottom' : 'right'"
            align="end"
            :side-offset="4"
        >
            <DropdownMenuLabel class="p-0 font-normal">
                <div class="flex items-center gap-2 px-1 py-1.5 text-sm">
                    <Avatar class="size-8 rounded-lg">
                        <AvatarFallback
                            class="rounded-lg bg-amber-400 font-semibold text-amber-950"
                        >
                            {{ initials }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="grid flex-1 text-left leading-tight">
                        <span class="truncate font-medium">
                            {{ user.name }}
                        </span>
                        <span class="truncate text-xs text-muted-foreground">
                            {{ user.email }}
                        </span>
                    </div>
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <Link :href="route('profile.edit')" class="w-full">
                    <User />
                    Profile
                </Link>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full"
                >
                    <LogOut />
                    Log out
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
