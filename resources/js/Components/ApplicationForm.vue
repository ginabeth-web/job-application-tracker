<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Application, ApplicationStatus, StatusOption } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';

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

const submit = () => {
    if (props.application) {
        form.put(route('applications.update', props.application.id));
    } else {
        form.post(route('applications.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="company" value="Company" />
            <TextInput
                id="company"
                v-model="form.company"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.company" />
        </div>

        <div>
            <InputLabel for="role" value="Role" />
            <TextInput
                id="role"
                v-model="form.role"
                type="text"
                class="mt-1 block w-full"
                required
            />
            <InputError class="mt-2" :message="form.errors.role" />
        </div>

        <div>
            <InputLabel for="url" value="Job posting URL" />
            <TextInput
                id="url"
                v-model="form.url"
                type="url"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.url" />
        </div>

        <div>
            <InputLabel for="location" value="Location" />
            <TextInput
                id="location"
                v-model="form.location"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.location" />
        </div>

        <div>
            <InputLabel for="salary" value="Salary" />
            <TextInput
                id="salary"
                v-model="form.salary"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.salary" />
        </div>

        <div>
            <InputLabel for="status" value="Status" />
            <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option
                    v-for="option in statuses"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.status" />
        </div>

        <div>
            <InputLabel for="applied_on" value="Applied on" />
            <TextInput
                id="applied_on"
                v-model="form.applied_on"
                type="date"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.applied_on" />
        </div>

        <div>
            <InputLabel for="follow_up_on" value="Follow up on" />
            <TextInput
                id="follow_up_on"
                v-model="form.follow_up_on"
                type="date"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.follow_up_on" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
            <Link
                :href="
                    application
                        ? route('applications.show', application.id)
                        : route('applications.index')
                "
                class="text-sm text-gray-600 underline hover:text-gray-900"
            >
                Cancel
            </Link>
        </div>
    </form>
</template>
