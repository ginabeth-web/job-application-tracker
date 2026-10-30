<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/Components/ui/button';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <p class="mb-4 text-sm text-muted-foreground">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
        </p>

        <div
            v-if="verificationLinkSent"
            class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="flex items-center justify-between gap-4">
                <Button type="submit" :disabled="form.processing">
                    Resend Verification Email
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                    >Log Out</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
