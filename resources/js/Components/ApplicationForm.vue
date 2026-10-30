<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Application, ApplicationStatus, StatusOption } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    application?: Application;
    statuses: StatusOption[];
}>();

const form = useForm({
    company: props.application?.company ?? '',
    role: props.application?.role ?? '',
    url: props.application?.url ?? '',
    location: props.application?.location ?? '',
    salary: props.application?.salary ?? '',
    status: (props.application?.status ?? 'wishlist') as ApplicationStatus,
    applied_on: props.application?.applied_on ?? '',
    follow_up_on: props.application?.follow_up_on ?? '',
});

const onSuccess = () => {
    toast.success('Application saved');
};

const submit = () => {
    if (props.application) {
        form.put(route('applications.update', props.application.id), {
            onSuccess,
        });
    } else {
        form.post(route('applications.store'), { onSuccess });
    }
};
</script>

<template>
    <form @submit.prevent="submit">
        <Card>
            <CardContent class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="company">Company</Label>
                    <Input
                        id="company"
                        v-model="form.company"
                        type="text"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.company" />
                </div>

                <div class="space-y-2">
                    <Label for="role">Role</Label>
                    <Input id="role" v-model="form.role" type="text" required />
                    <InputError :message="form.errors.role" />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <Label for="url">Job posting URL</Label>
                    <Input id="url" v-model="form.url" type="url" />
                    <InputError :message="form.errors.url" />
                </div>

                <div class="space-y-2">
                    <Label for="location">Location</Label>
                    <Input id="location" v-model="form.location" type="text" />
                    <InputError :message="form.errors.location" />
                </div>

                <div class="space-y-2">
                    <Label for="salary">Salary</Label>
                    <Input id="salary" v-model="form.salary" type="text" />
                    <InputError :message="form.errors.salary" />
                </div>

                <div class="space-y-2 md:col-span-2">
                    <Label for="status">Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger id="status" class="w-full">
                            <SelectValue placeholder="Select a status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in statuses"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="space-y-2">
                    <Label for="applied_on">Applied on</Label>
                    <Input id="applied_on" v-model="form.applied_on" type="date" />
                    <InputError :message="form.errors.applied_on" />
                </div>

                <div class="space-y-2">
                    <Label for="follow_up_on">Follow up on</Label>
                    <Input
                        id="follow_up_on"
                        v-model="form.follow_up_on"
                        type="date"
                    />
                    <InputError :message="form.errors.follow_up_on" />
                </div>
            </CardContent>
            <CardFooter class="gap-3">
                <Button type="submit" :disabled="form.processing">Save</Button>
                <Button variant="outline" as-child>
                    <Link
                        :href="
                            application
                                ? route('applications.show', application.id)
                                : route('applications.index')
                        "
                    >
                        Cancel
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
