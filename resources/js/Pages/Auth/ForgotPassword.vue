<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <p class="mb-4 text-sm text-muted-foreground">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </p>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400"
        >
            {{ status }}
        </div>

        <form class="grid gap-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Email Password Reset Link
            </Button>

            <div class="text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                >
                    Back to log in
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
