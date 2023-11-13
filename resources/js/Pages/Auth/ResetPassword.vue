<script setup>
import BreezeButton from '@/Components/Button.vue';
import BreezeGuestLayout from '@/Layouts/Guest.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeInputError from '@/Components/InputError.vue';
import BreezeLabel from '@/Components/Label.vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import {onMounted, ref} from "vue";
onMounted(() => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('pending')){
        if(urlParams.get('pending')){
            isPending.value = true
        }else{
            isPending.value = false
        }
        if(urlParams.has('u')){
            form.email = atob(urlParams.get('u'));
        }
    }else{
        isPending.value = false
    }
})

const isPending = ref(false);

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <BreezeGuestLayout>
        <Head :title="isPending ? 'Imposta password ' : 'Reset Password'" />

        <form @submit.prevent="submit">
            <div v-if="isPending" class="mb-4 text-center">
                {{ __('auth.confirmNewPassord') }}
            </div>
            <div>
<!--                <BreezeLabel for="email" value="Email" />
                <BreezeInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
                <BreezeInputError class="mt-2" :message="form.errors.email" />-->
                <BreezeInput id="email" type="email" v-model="form.email" required autofocus autocomplete="username" label="E-mail" :error="form.errors.email"></BreezeInput>
            </div>

            <div class="mt-4">
<!--                <BreezeLabel for="password" value="Password" />
                <BreezeInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                <BreezeInputError class="mt-2" :message="form.errors.password" />-->
                <BreezeInput id="password" type="password" v-model="form.password" required autofocus autocomplete="new-password" label="Password" :error="form.errors.password"></BreezeInput>
            </div>

            <div class="mt-4">
<!--                <BreezeLabel for="password_confirmation" :value="__('auth.confirmPassword')" />
                <BreezeInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                <BreezeInputError class="mt-2" :message="form.errors.password_confirmation" />-->
                <BreezeInput id="password_confirmation" type="password" v-model="form.password_confirmation" required autofocus autocomplete="new-password" :label="__('auth.confirmPassword')" :error="form.errors.password_confirmation"></BreezeInput>
            </div>

            <div class="flex items-center justify-end mt-4">
                <BreezeButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ isPending ? __('auth.setNewPassword') : 'Reset Password' }}
                </BreezeButton>
            </div>
        </form>
    </BreezeGuestLayout>
</template>
