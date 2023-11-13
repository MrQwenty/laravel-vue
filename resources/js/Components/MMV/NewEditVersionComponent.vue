<template>
    <div>
        <div>
            <form @submit.prevent="onNewVersionSubmit">
                <div class="grid grid-cols-2 gap-x-3">
                    <div class="flex flex-grow flex-col">
                        <InputCustom v-model="form.make" required :label="__('version.selectMake')"
                                     id="make"
                                     name="make"
                                     label-field="name"
                                     value-field="id"
                                     :optionsSelect="makes"
                                     :customUpdateValue="makeValueUpdate"
                                     :error="form.errors.make" type="select"></InputCustom>
                    </div>
                    <div class="flex flex-grow flex-col">
                        <InputCustom v-model="form.model" required :label="__('version.selectModel')" id="model"
                                     name="model"
                                     :optionsSelect="filteredModels"
                                     label-field="name"
                                     value-field="id"
                                     :error="form.errors.model"
                                     type="select"></InputCustom>
                    </div>
                    <template v-if="form.make && form.model">
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.name" required :label="__('strings.name')" id="name"
                                         name="name"
                                         :error="form.errors.name" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.door_no" @keypress="regexInput($event, /^[0-9]+$/i)"
                                         :label="__('version.door_no')" id="door_no" name="door_no"
                                         :error="form.errors.door_no" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.engine_size" @keypress="regexInput($event, /^[0-9]+$/i)"
                                         :label="__('version.engine_size')" id="engine_size" name="engine_size"
                                         :error="form.errors.engine_size" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.kw_power" @keypress="regexInput($event, /^[0-9]+$/i)"
                                         :label="__('version.kw_power')" id="kw_power" name="kw_power"
                                         :error="form.errors.kw_power" type="text"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom class="w-full" :customUpdateValue="dateValueUpdate" v-model="form.from_date" :label="__('version.from_date')"
                                         id="from_date"
                                         name="from_date" :error="form.errors.from_date" type="date"></InputCustom>
                        </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom class="w-full" :is-date-disabled="(n) => n < form.from_date" v-model="form.to_date"  :label="__('version.to_date')"
                                         id="to_date"
                                         name="to_date" :error="form.errors.to_date" type="date"></InputCustom>
                        </div>
                    </template>
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
import {useForm} from "@inertiajs/inertia-vue3";
import {computed, getCurrentInstance, h, nextTick, onMounted, reactive, ref} from "vue";
import {NIcon, NTag} from "naive-ui";
import {Inertia} from "@inertiajs/inertia";

onMounted(() => {
    if(props.versions){
        form.make = props.versions.make_id
        form.model = props.versions.model_id
        form.name = props.versions.name
        form.door_no = props.versions.door_no
        form.engine_size = props.versions.engine_size
        form.kw_power = props.versions.kw_power
        form.from_date = props.versions.from_date ? new Date(props.versions.from_date) : null
        form.to_date = props.versions.to_date ? new Date(props.versions.to_date) : null
    }
})
const form = useForm({
    name: null,
    make: null,
    model: null,
    door_no: null,
    engine_size: null,
    kw_power: null,
    from_date: null,
    to_date: null,
})

const editInfoForm = useForm({
    name: null,
    make: null,
    model: null,
    door_no: null,
    engine_size: null,
    kw_power: null,
    from_date: null,
    to_date: null,
})


const props = defineProps({
    models: Array,
    makes: Array,
    versions: Array,
})

const filteredModels = computed(() => {
    return _.filter(props.models, (el) => {
        if (form.make && el.make_id == form.make) {
            return el;
        }
    })
})
const makeValueUpdate = (value) => {
    if(value != form.make){
        form.make = value
        form.model = null
    }
}
const dateValueUpdate = (value,type) => {
    form.from_date = value
    form.to_date = null
}
const onNewVersionSubmit = () => {
    if(props.versions){
        form.transform((el) => {
            return ({
                    name: el.name,
                    make: el.make ? el.make : null,
                    model: el.model ? el.model : null,
                    door_no: el.door_no ?? null,
                    engine_size: el.engine_size ?? null,
                    kw_power: el.kw_power ?? null,
                    from_date: el.from_date ? formatDate(el.from_date) : null,
                    to_date: el.to_date ? formatDate(el.to_date) : null,
                }
            )
        }).put(route('warehouse.versions.updateInfo',{
            id : props.versions.id
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
                    make: el.make ? el.make : null,
                    model: el.model ? el.model : null,
                    door_no: el.door_no ?? null,
                    engine_size: el.engine_size ?? null,
                    kw_power: el.kw_power ?? null,
                    from_date: el.from_date ? formatDate(el.from_date) : null,
                    to_date: el.to_date ? formatDate(el.to_date) : null,
                }
            )
        }).post(route('warehouse.versions.store'), {
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
