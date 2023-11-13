<script setup>
import BreezeButton from '@/Components/Button.vue';
import BreezeCheckbox from '@/Components/Checkbox.vue';
import BreezeGuestLayout from '@/Layouts/Guest.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeInputError from '@/Components/InputError.vue';
import BreezeLabel from '@/Components/Label.vue';
import {Head, Link, useForm, usePage} from '@inertiajs/inertia-vue3';
import {onMounted, ref} from "vue";
onMounted(() => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('pending')){
        if(urlParams.get('pending') == false){
            confirmedUser.value = true;
        }
    }
})
defineProps({
    canResetPassword: Boolean,
    status: String,
});
const confirmedUser = ref(false);
const form = useForm({
    email: '',
    password: '',
    remember: false
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <BreezeGuestLayout>
        <Head title="Log in" />

        <div v-if="confirmedUser" class="mb-4 font-medium text-sm text-primaryColor-600 text-center">
            {{ __('auth.confirmedUserText') }}
        </div>
        <div v-else-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <BreezeInput
                    type="text"
                    id="email"
                    required
                    :error="form.errors.email"
                    label="E-mail"
                    v-model="form.email"
                    placeholder="E-mail"
                    name="email"/>
            </div>

            <div class="mt-0">
                <BreezeInput id="password" placeholder="Password" :error="form.errors.password" show-password-on="click" label="Password" type="password" v-model="form.password" required autocomplete="current-password" name="password"/>
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <BreezeCheckbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('auth.rememberMe') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
                    {{ __('auth.forgotPassword') }}
                </Link>

                <BreezeButton class="ml-4 !bg-primaryColor" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </BreezeButton>
            </div>
        </form>
    </BreezeGuestLayout>
</template>
