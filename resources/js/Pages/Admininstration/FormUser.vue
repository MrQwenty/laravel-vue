<script setup>
import BreezeButton from '@/Components/Button.vue';
import BreezeGuestLayout from '@/Layouts/Guest.vue';
import BreezeInput from '@/Components/Input.vue';
import BreezeInputError from '@/Components/InputError.vue';
import BreezeLabel from '@/Components/Label.vue';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import {onMounted} from "vue";
const props = defineProps({
    user : Object ,
})
onMounted(() => {
    if(props.user){
        form.name = props.user.name
        form.company_id = props.user.company_id
        form.email = props.user.email
        props.user.is_admin ? form.is_admin = true : form.is_admin = false
    }
})
const form = useForm({
    name: '',
    company_id: null,
    email: '',
    is_admin: false,
});

const submit = () => {
    props.user ? form.put(route('admininstration.users.update',{
        id : props.user.id
    })) : form.post(route('admininstration.users.store'), {
        onFinish: () => form.reset('name', 'company_id','email'),
    });
};
</script>

<template>
    <BreezeGuestLayout>
        <Head :title="user ? __('user.editUser') : __('user.newUser')" />

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
                <BreezeInput id="company_id" type="text" v-model="form.company_id" required autofocus autocomplete="company_id" :error="form.errors.company_id" :label="__('user.company_id')"></BreezeInput>
            </div>

            <div class="mt-4">
                <BreezeInput id="Email" type="email" v-model="form.email" required autofocus autocomplete="username" :error="form.errors.email" label="E-mail"></BreezeInput>
            </div>

<!--            <div class="mt-4">
                &lt;!&ndash;                <BreezeLabel for="password" value="Password" />
                                <BreezeInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                                <BreezeInputError class="mt-2" :message="form.errors.password" />&ndash;&gt;
                <BreezeInput id="password" type="password" v-model="form.password" required autofocus autocomplete="new-password" :error="form.errors.password" label="Password"></BreezeInput>
            </div>-->

            <div class="mt-4 flex flex-row w-full gap-2">
                <!--                <BreezeLabel for="password_confirmation" :value="__('auth.confirmPassword')" />
                                <BreezeInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                                <BreezeInputError class="mt-2" :message="form.errors.password_confirmation" />-->
                <div class="flex w-full items-center pl-4 border border-gray-200 h-[40px]">
                    <input id="is_admin_1" type="radio" :value="true" name="bordered-radio" v-model="form.is_admin" class="w-4 h-4 text-primaryColor bg-gray-100 border-gray-300 focus:ring-0 focus:ring-white">
                    <label for="is_admin_1" class="w-full py-4 ml-2 text-sm text-gray-900 ">Admin</label>
                </div>
                <div class="flex w-full items-center pl-4 border border-gray-200 h-[40px]">
                    <input checked id="is_admin_2" type="radio" :value="false" name="bordered-radio" v-model="form.is_admin" class="w-4 h-4 text-primaryColor bg-gray-100 border-gray-300   focus:ring-0 focus:ring-white ">
                    <label for="is_admin_2" class="w-full py-4 ml-2 text-sm text-gray-900">Normal</label>
                </div>
            </div>

            <div class="flex items-center justify-end mt-4">

                <BreezeButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('user.addUser') }}
                </BreezeButton>
            </div>
        </form>
    </BreezeGuestLayout>
</template>
