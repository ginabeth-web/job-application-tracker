<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

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

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox
                    id="remember"
                    v-model="form.remember"
                    name="remember"
                />
                <Label for="remember" class="font-normal">Remember me</Label>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Log in
            </Button>

            <div class="flex flex-col items-center gap-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm underline-offset-4 hover:underline"
                >
                    Forgot your password?
                </Link>
                <Link
                    :href="route('register')"
                    class="text-sm text-muted-foreground underline-offset-4 hover:underline"
                >
                    Need an account? Register
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
