<script setup lang="ts">
import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { type Appearance, useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, setAppearance } = useAppearance();

const options: { value: Appearance; label: string; icon: typeof Sun }[] = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
];
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" aria-label="Toggle theme">
                <Sun class="dark:hidden" />
                <Moon class="hidden dark:block" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuRadioGroup
                :model-value="appearance"
                @update:model-value="setAppearance($event as Appearance)"
            >
                <DropdownMenuRadioItem
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    <component :is="option.icon" />
                    {{ option.label }}
                </DropdownMenuRadioItem>
            </DropdownMenuRadioGroup>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
