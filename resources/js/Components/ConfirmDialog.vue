<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/Components/ui/alert-dialog';
import { buttonVariants } from '@/Components/ui/button';

// The default slot is the trigger element; confirming emits `confirm`, cancelling does nothing.
withDefaults(
    defineProps<{
        title: string;
        description: string;
        confirmLabel?: string;
    }>(),
    { confirmLabel: 'Delete' },
);

defineEmits<{
    confirm: [];
}>();
</script>

<template>
    <AlertDialog>
        <AlertDialogTrigger as-child>
            <slot />
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription>{{ description }}</AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction
                    :class="buttonVariants({ variant: 'destructive' })"
                    @click="$emit('confirm')"
                >
                    {{ confirmLabel }}
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
