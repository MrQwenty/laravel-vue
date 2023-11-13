<template>
    <div>
        <div>
            <form @submit.prevent="onNewCategorySubmit">
                <div class="grid grid-cols-2 gap-x-3">
                    <div class="flex flex-grow flex-col">
                        <InputCustom :translate="true" v-model="form.name" required :label="__('strings.name')"
                                     id="name"
                                     name="name"
                                     :error="form.errors.name" type="text"></InputCustom>
                        <div>
                            <div class="mb-[6px]">{{__('category.selectParent')}}</div>
                        <n-cascader filterable v-model:value="form.parent" :placeholder="__('category.selectParent')" :options="cascaderOptions"
                        ></n-cascader>
                        </div>
                    </div>
                    <div class="flex flex-grow flex-col">
                        <InputCustom v-model="form.weight" @keypress="regexInput($event, /^[0-9,]+$/i)"
                                     :label="__('category.default_weight')"
                                     id="weight"
                                     name="weight"
                                     :error="form.errors.weight" type="text"></InputCustom>
                    </div>
                    <div class="flex justify-start gap-2 mt-3">
                        <n-button type="error"
                                  attr-type="button"
                                  @click="goToBack"
                                  :disabled="form.processing"> {{ __('strings.cancel') }}
                        </n-button>
                    </div>
                    <div class="flex justify-end gap-2 mt-3">
                        <n-button
                            attr-type="submit"
                            :disabled="form.processing">{{ __('strings.save') }}
                        </n-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>

import Multiselect from "vue-multiselect";
import InputCustom from "@/Components/Input.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {computed, getCurrentInstance, h, nextTick, onMounted, reactive, ref} from "vue";
import {NIcon, NTag} from "naive-ui";
import {Inertia} from "@inertiajs/inertia";

onMounted(() => {
    cascaderOptions.value =  buildCascader(props.categories)

    if (props.categoryEdit) {
        form.weight = props.categoryEdit.default_weight ? str_replace(props.categoryEdit.default_weight) : null
        form.name = props.categoryEdit.name
        form.parent = props.categoryEdit.parent_id
    }
})
const buildCascader = (categories) => {
    let cats = categories.map((category) => {
        if((props.categoryEdit && category.id == props.categoryEdit.id)){

            return category.children.length > 0 ?
                {
                    value : category.id,
                    label  : getTranslatedEntity(category.name),
                    children : buildCascader(category.children),
                    disabled : true

                }

                : {

                    value : category.id,
                    label  : getTranslatedEntity(category.name),
                    disabled : true
                }
        }else{

            return category.children.length > 0 ?
                {
                    value : category.id,
                    label  : getTranslatedEntity(category.name),
                    children : buildCascader(category.children),

                }

                : {

                    value : category.id,
                    label  : getTranslatedEntity(category.name),
                }
        }
    })
    return cats
}
const form = useForm({
    name: null,
    weight: null,
    parent : null,
})

const cascaderOptions = ref([]);
const categoriesCustom = ref([]);
const props = defineProps({
    categories: Array,
    categoryEdit : Object,
})
const dialog = useDialog();
const onNewCategorySubmit = () => {
    if (props.categoryEdit) {
        let langs = usePage().props.value.langs;
        let nullLang = false
        langs.forEach((lang)=>{
            if(nullLang == false){
                if(form.name[lang.value] == null || form.name[lang.value] == ''){
                    nullLang = true
                    dialog.warning({
                        title: $trans('strings.attention'),
                        content: $trans('strings.nullLangs'),
                        positiveText: $trans('strings.confirm'),
                        negativeText: $trans('strings.cancel'),
                        maskClosable: false,
                        onPositiveClick: () => {
                            form.transform((el) => {
                                return ({
                                        name: el.name,
                                        weight: el.weight ? el.weight : null,
                                        parent : el.parent ? el.parent : null,
                                    }
                                )
                            }).put(route('warehouse.categories.update', {
                                id: props.categoryEdit.id
                            }), {
                                onFinish: () => {
                                    if (!form.hasErrors) {
                                        //Inertia.get(route('warehouse.versions.index'))
                                    }
                                }
                            })
                        }
                    })
                }
            }
        })
        if(!nullLang){
            form.transform((el) => {
                return ({
                        name: el.name,
                        weight: el.weight ? el.weight : null,
                        parent : el.parent ? el.parent : null,
                    }
                )
            }).put(route('warehouse.categories.update', {
                id: props.categoryEdit.id
            }), {
                onFinish: () => {
                    if (!form.hasErrors) {
                        //Inertia.get(route('warehouse.versions.index'))
                    }
                }
            })
        }
    } else {
        let langs = usePage().props.value.langs;
        let nullLang = false
        langs.forEach((lang)=> {
            if(nullLang == false){
                if (!(form.name instanceof Object) ||form.name[lang.value] == null || form.name[lang.value] == '') {
                    nullLang = true
                    dialog.warning({
                        title: $trans('strings.attention'),
                        content: $trans('strings.nullLangs'),
                        positiveText: $trans('strings.confirm'),
                        negativeText: $trans('strings.cancel'),
                        maskClosable: false,
                        onPositiveClick: () => {
                            form.transform((el) => {
                                return ({
                                        name: el.name,
                                        weight: el.weight ? el.weight : null,
                                        parent : el.parent ? el.parent : null,
                                    }
                                )
                            }).post(route('warehouse.categories.store'), {
                                onFinish: () => {
                                    if (!form.hasErrors) {
                                        //Inertia.get(route('warehouse.versions.index'))
                                    }
                                }
                            })
                        }
                    })
                }
            }
        })
        if(!nullLang){
            form.transform((el) => {
                return ({
                        name: el.name,
                        weight: el.weight ? el.weight : null,
                        parent : el.parent ? el.parent : null,
                    }
                )
            }).post(route('warehouse.categories.store'), {
                onFinish: () => {
                    if (!form.hasErrors) {
                        //Inertia.get(route('warehouse.versions.index'))
                    }
                }
            })
        }
    }
}

const goToBack = function () {
    window.history.back()
}

const str_replace = (str) => {
    if (str) {
        str = str.toString();
        return str.replace('.', ',')
    }
}
const formatDate = (temp) => {
    let date = new Date(temp)
    let y = date.getFullYear();
    let m = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
    let d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
    return y + '-' + m + '-' + d
}
</script>


<style scoped>

</style>
