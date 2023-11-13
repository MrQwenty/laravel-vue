<script setup>
import BreezeButton from '@/Components/Button.vue';
import BreezeGuestLayout from '@/Layouts/Guest.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeInputError from '@/Components/InputError.vue';
import BreezeLabel from '@/Components/Label.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    })
};
</script>

<template>
    <BreezeGuestLayout>
        <Head :title="__('auth.confirmPassword')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ __('auth.secureArea') }}
        </div>

        <form @submit.prevent="submit">
            <div>
<!--                <BreezeLabel for="password" value="Password" />
                <BreezeInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" autofocus />
                <BreezeInputError class="mt-2" :message="form.errors.password" />-->
                <BreezeInput id="password" type="password" v-model="form.password" :error="form.errors.password" required autocomplete="current-password" autofocus label="Password"></BreezeInput>
            </div>

            <div class="flex justify-end mt-4">
                <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('strings.confirm') }}
                </BreezeButton>
            </div>
        </form>
    </BreezeGuestLayout>
</template>
