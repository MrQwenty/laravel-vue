<template>
    <div>
        <div>
            <form @submit.prevent="onNewUserSubmit">
                <n-tabs type="line" animated>
                    <n-tab-pane name="dati" :tab="__('strings.personalData')">
                        <div class="grid grid-cols-2 gap-x-3">
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.name" required :label="__('strings.name')" id="name"
                                         name="name"
                                         :error="form.errors.name" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.company_id" :label="__('user.company_id')" id="name"
                                         name="company_id"
                                         :error="form.errors.company_id" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.email" required :label="__('user.email')" id="name"
                                         name="email"
                                         :error="form.errors.email" type="text"></InputCustom>
                        </div>
                        </div>
                    </n-tab-pane>
                    <n-tab-pane name="roles" :tab="__('strings.roles&perms')">
                        <div class="flex flex-grow flex-row items-center">
                            <div class="w-full">
                                {{ __('user.role') }}
                            <n-select id="role" @update:value="changeCheck" v-model:value="form.is_admin"
                                      :options="selectOptions"
                                      :placeholder="__('strings.selectRole')" type="select"/></div>
                        </div>
                        <div class="border rounded p-3 py-4 pb-5 mt-5 relative" v-show="form.is_admin == 'false'">
                            <div class="text-center absolute top-[-2px] left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-[5px] text-gray-400 ">{{__('strings.customPermissions')}}<font-awesome-icon class="ml-3" icon="fa-duotone fa-key"/></div>
                        <div class="grid grid-cols-2 gap-x-3">
                            <div class="flex flex-grow flex-row items-center ">
                                <div class="w-full">
                                    {{ __('make.makes') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.makes_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center ">
                                <div class="w-full">
                                    {{ __('model.models') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.models_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('version.versions') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.versions_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('side.sides') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.sides_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('category.categories') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.categories_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('attribute.attributes') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.attributes_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('replacement.replacements') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.replacements_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                            <div class="flex flex-grow flex-row items-center mt-4">
                                <div class="w-full">
                                    {{ __('product.products') }}
                                    <n-select id="role" @update:value="changeCheck" :disabled="form.is_admin == 'true'" v-model:value="form.products_perm"
                                              :options="selectOptionsPermissions"
                                              :placeholder="__('strings.selectRole')" type="select"/></div>
                            </div>
                        </div>
                        </div>
                    </n-tab-pane>
                </n-tabs>
                <div class="flex justify-between flex-row">
                    <div class="flex gap-2 mt-3">
                        <n-button type="error"
                                  attr-type="button"
                                  @click="goToBack"
                                  :disabled="form.processing"> {{ __('strings.cancel') }}
                        </n-button>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <n-button
                            attr-type="submit"
                            :disabled="form.processing">{{ __('strings.save') }}
                        </n-button>
                    </div></div>
            </form>
        </div>
    </div>
</template>

<script setup>

import Multiselect from "vue-multiselect";
import InputCustom from "@/Components/Input.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useForm,usePage} from "@inertiajs/inertia-vue3";
import {computed, getCurrentInstance, h, nextTick, onMounted, reactive, ref} from "vue";
import {NIcon, NTag} from "naive-ui";
import {Inertia} from "@inertiajs/inertia";

onMounted(() => {
    if(props.user){
        console.log(props.user)
        form.name = props.user.name
        form.company_id = props.user.company_id
        form.email = props.user.email
        form.is_admin = props.user.is_admin ? 'true' : 'false'
        if(props.roles[0].name == 'admin'){
            form.makes_perm = 'rw';
            form.models_perm = 'rw';
            form.versions_perm = 'rw';
            form.sides_perm = 'rw';
            form.categories_perm = 'rw';
            form.replacements_perm = 'rw';
            form.products_perm = 'rw';
            form.attributes_perm = 'rw'
        }else{
            if(props.permissions.length > 0){
                props.permissions.forEach((perm) => {
                    if(perm.name == 'write_makes'){
                        form.makes_perm = 'rw';
                    }
                    if(perm.name == 'write_models'){
                        form.models_perm = 'rw';
                    }
                    if(perm.name == 'write_versions'){
                        form.versions_perm = 'rw';
                    }
                    if(perm.name == 'write_sides'){
                        form.sides_perm = 'rw';
                    }
                    if(perm.name == 'write_categories'){
                        form.categories_perm = 'rw';
                    }
                    if(perm.name == 'write_attributes'){
                        form.attributes_perm = 'rw';
                    }
                    if(perm.name == 'write_products'){
                        form.products_perm = 'rw';
                    }
                    if(perm.name == 'write_replacements'){
                        form.replacements_perm = 'rw';
                    }
                })
            }
        }
    }
})
const form = useForm({
    name: null,
    email: null,
    company_id : null,
    is_admin : 'false',
    makes_perm : 'ro',
    models_perm : 'ro',
    versions_perm : 'ro',
    sides_perm : 'ro',
    categories_perm : 'ro',
    products_perm : 'ro',
    replacements_perm : 'ro',
    attributes_perm : 'ro',

})

const changeCheck = (value) => {
    if(value == 'true'){
        form.makes_perm = 'rw';
        form.models_perm = 'rw';
        form.versions_perm = 'rw';
        form.sides_perm = 'rw';
        form.categories_perm = 'rw';
        form.replacements_perm = 'rw';
        form.products_perm = 'rw';
        form.attributes_perm = 'rw';
    }else if(value == 'false'){
        form.makes_perm = 'ro';
        form.models_perm = 'ro';
        form.versions_perm = 'ro';
        form.sides_perm = 'ro';
        form.categories_perm = 'ro';
        form.attributes_perm = 'ro';
        form.products_perm = 'ro';
        form.replacements_perm = 'ro';
    }
}
const selectOptions = [
    {
        label: $trans('user.is_admin'),
        value: 'true'
    },
    {
        label: $trans('user.baseUser'),
        value: 'false'
    }
]
const selectOptionsPermissions = [
    {
        label: $trans('strings.read_only'),
        value: 'ro'
    },
    {
        label: $trans('strings.read_write'),
        value: 'rw'
    },
]
const props = defineProps({
    user : Object,
    roles : Array,
    permissions : Array,
})


const onNewUserSubmit = () => {
    if(props.user){
        form.transform((el) => {
            return ({
                    name: el.name,
                    email: el.email ? el.email : null,
                    company_id : el.company_id ? el.company_id : null,
                    is_admin : el.is_admin == 'true' ? true : false,
                    makes_perm : el.makes_perm,
                    models_perm : el.models_perm,
                    versions_perm : el.versions_perm,
                    sides_perm : el.sides_perm,
                    categories_perm : el.categories_perm,
                    attributes_perm : el.attributes_perm,
                    products_perm : el.products_perm,
                    replacements_perm : el.replacements_perm,
                }
            )
        }).put(route('admininstration.users.update',{
            id : props.user.id
        }),{
            onFinish : () => {
                if (!form.hasErrors) {
                    //Inertia.get(route('warehouse.versions.index'))
                }
            }
        })
    }else{
        form.transform((el) => {
            return ({
                    name: el.name,
                    email: el.email ? el.email : null,
                    company_id : el.company_id ? el.company_id : null,
                    is_admin : el.is_admin == 'true' ? true : false,
                    makes_perm : el.makes_perm,
                    models_perm : el.models_perm,
                    versions_perm : el.versions_perm,
                    sides_perm : el.sides_perm,
                    categories_perm : el.categories_perm,
                    attributes_perm : el.attributes_perm,
                    products_perm : el.products_perm,
                    replacements_perm : el.replacements_perm,
                }
            )
        }).post(route('admininstration.users.store'), {
            onFinish: () => {
                if (!form.hasErrors) {
                    //Inertia.get(route('warehouse.versions.index'))
                }
            }
        })
    }
}

const goToBack = function() {
    window.history.back()
}

const str_replace = (str) => {
    if(str){
        str = str.toString();
        return str.replace('.',',')
    }

}
const formatDate = (temp) => {
    let date = new Date(temp)
    let y = date.getFullYear();
    let m = (date.getMonth() + 1) < 10 ? '0'+ (date.getMonth()+1) : date.getMonth()+1
    let d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
    return y + '-' + m + '-' + d
}
</script>


<style scoped>

</style>
