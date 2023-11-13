<script setup>
import BreezeButton from '@/Components/Button.vue';
import BreezeGuestLayout from '@/Layouts/Guest.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeInputError from '@/Components/InputError.vue';
import BreezeLabel from '@/Components/Label.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <BreezeGuestLayout>
        <Head :title="__('auth.register')" />

        <form @submit.prevent="submit">
            <div>
<!--                <BreezeLabel for="name" :value="__('strings.name')" />
                <BreezeInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                <BreezeInputError class="mt-2" :message="form.errors.name" />-->
                <BreezeInput id="name" type="text" v-model="form.name" required autofocus autocomplete="name" :error="form.errors.name" :label="__('strings.name')"></BreezeInput>
            </div>

            <div class="mt-4">
<!--                <BreezeLabel for="email" value="Email" />
                <BreezeInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                <BreezeInputError class="mt-2" :message="form.errors.email" />-->
                <BreezeInput id="Email" type="email" v-model="form.email" required autofocus autocomplete="username" :error="form.errors.name" label="E-mail"></BreezeInput>
            </div>

            <div class="mt-4">
<!--                <BreezeLabel for="password" value="Password" />
                <BreezeInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                <BreezeInputError class="mt-2" :message="form.errors.password" />-->
                <BreezeInput id="password" type="password" v-model="form.password" required autofocus autocomplete="new-password" :error="form.errors.password" label="Password"></BreezeInput>
            </div>

            <div class="mt-4">
<!--                <BreezeLabel for="password_confirmation" :value="__('auth.confirmPassword')" />
                <BreezeInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                <BreezeInputError class="mt-2" :message="form.errors.password_confirmation" />-->
                <BreezeInput id="password_confirmation" type="password" v-model="form.password_confirmation" required autofocus autocomplete="new-password" :error="form.errors.password_confirmation" :label="__('auth.confirmPassword')"></BreezeInput>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('auth.alreadyRegistered') }}
                </Link>

                <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('auth.register') }}
                </BreezeButton>
            </div>
        </form>
    </BreezeGuestLayout>
</template>
