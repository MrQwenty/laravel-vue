<template>
    <n-card class="!border-t-[3px] !border-t-primaryColor mb-5">
    <div>
        <div>
            <form @submit.prevent="onNewAttributeSubmit">
                <div class="grid grid-cols-2 gap-x-3 flex">
                    <div class="flex gap-x-3">
                        <InputCustom :translate="true" customClass="w-full"  v-model="form.name" required :label="__('strings.name')"
                                     id="name"
                                     name="name"
                                     :error="form.errors.name" type="text"/>
                    </div>

                    <div>
                        <InputCustom :label="__('strings.type')" filterable required v-model="form.type" :error="form.errors.type" :optionsSelect="optionsType" type="select"></InputCustom>
                    </div>
                </div>
                    <div class="mb-3">
                        <InputCustom :hint="__('attribute.categoryHint')"   :label="__('category.categories')" :multiple="true" v-model="form.categories" :optionsSelect="options" type="cascader"></InputCustom>
                    </div>
                <div class="grid grid-cols-2 gap-x-3 flex">
                    <div>
                        <div class="mb-[5px]">{{ __('attribute.is_required') }}</div>
                        <n-radio :checked="form.is_required == true" :value="'si'" @change="handleChange('true')">{{__('strings.yes')}}</n-radio>
                        <n-radio :checked="form.is_required == false" :value="'no'" @change="handleChange('false')">{{__('strings.no')}}</n-radio>
                    </div>
                    <div>
                        <div class="mb-[5px]">{{ __('attribute.visibleEcommerce') }}</div>
                        <n-switch class="!h-[34px]" :default-value="form.is_ecommerce_shown" :rail-style="railStyle" :round="false" v-model:value="form.is_ecommerce_shown" :options="optionsShown">
                            <template #checked-icon>
                                <font-awesome-icon icon="fa-duotone fa-eye"></font-awesome-icon>
                            </template>
                            <template #unchecked-icon>
                                <font-awesome-icon icon="fa-duotone fa-eye-slash"></font-awesome-icon>
                            </template>
                            <template #checked>
                                {{ __('strings.visible') }}
                            </template>
                            <template #unchecked>
                                {{__('strings.hidden')}}
                            </template>
                        </n-switch>
                    </div>
                </div>
                    <div class="flex flex-row w-full justify-between">
                        <div class="flex justify-start gap-2 mt-3">
                            <n-button type="error"
                                      attr-type="button"
                                      @click="goToBack"
                                      :disabled="form.processing"> {{ __('strings.cancel') }}
                            </n-button>
                        </div>
                        <div class="flex justify-end gap-2 mt-3">
                            <span v-if="edit && newLogo" class="text-center items-center flex text-amber-600">{{__('strings.newLogo')}}</span>
                            <n-button
                                attr-type="submit"
                                :disabled="form.processing">{{ __('strings.save') }}
                            </n-button>
                        </div>
                    </div>

            </form>
        </div>
    </div>
    </n-card>
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
    options.value = buildCascader(props.categories)
    props.types.forEach((type) => {
        optionsType.value.push({
            label : getTranslatedEntity(type.name),
            value : type.id
        })
    })
    if (props.attributes) {
        form.name = props.attributes.name;
        form.is_ecommerce_shown = props.attributes.is_ecommerce_shown ? true : false;
        form.is_required = props.attributes.is_required ? true : false;
        form.type = props.attributes.type_id;
        let cats = [];
        props.attributes.categories.forEach((cat)=>{
            cats.push(cat.id)
        })
        form.categories = cats;
    }
})

const buildCascader = (categories) => {
    let cats = categories.map((category) => {

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
    })
    return cats
}
const handleChange = (a) => {
    form.is_required = a == 'true' ? true : false
}
const railStyle = (a) => {
    if(a.checked){
        return {
            background : 'green'
        }
    }else{
        return{
            background:  'red'
        }
    }
}
const options = ref([]);
const optionsType = ref([]);
const form = useForm({
    name: null,
    type : null,
    is_ecommerce_shown : false,
    categories : null,
    is_required : false,

})
const props = defineProps({
    types : Array,
    attributes : Object,
    categories : Array,
})
const onNewAttributeSubmit = () => {
    if (props.attributes) {
        form.transform((el) => {
            return ({
                    name: el.name,
                    type : el.type,
                    categories : el.categories ? el.categories : null,
                    is_ecommerce_shown : el.is_ecommerce_shown,
                    is_required : el.is_required
                }
            )
        })
            .put(route('warehouse.attributes.update', {
                id: props.attributes.id
            }), {
                onFinish: () => {
                    if (!form.hasErrors) {
                        //Inertia.get(route('warehouse.versions.index'))
                    }
                }
            })
    } else {
        form.transform((el) => {
            return ({
                    name: el.name,
                    type : el.type,
                    categories : el.categories ? el.categories : null,
                    is_ecommerce_shown : el.is_ecommerce_shown,
                    is_required : el.is_required
                }
            )
        }).post(route('warehouse.attributes.store'), {
                onFinish: () => {
                    if(usePage().props.value.errors?.type){
                        form.setError('type',usePage().props.value.errors.type)
                    }
                    if (!form.hasErrors) {
                        //Inertia.get(route('warehouse.versions.index'))
                    }
                },
            },
        )
    }
}
const goToBack = function () {
    window.history.back()
}

</script>


<style scoped>

</style>
