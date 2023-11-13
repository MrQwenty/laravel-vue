<template>
    <div>
        <div>
            <form @submit.prevent="onNewModelSubmit">
                <div class="grid grid-cols-2 gap-x-3">
                    <div class="flex flex-grow flex-col">
                        <InputCustom v-model="form.make" required :label="__('model.selectMake')"
                                     id="make"
                                     name="make"
                                     label-field="name"
                                     value-field="id"
                                     :optionsSelect="makes"
                                     :error="form.errors.make" type="select"></InputCustom>
                    </div>
                        <div class="flex flex-grow flex-col">
                            <InputCustom v-model="form.name" required :label="__('strings.name')" id="name"
                                         name="name"
                                         :error="form.errors.name" type="text"></InputCustom>
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
import {useForm} from "@inertiajs/inertia-vue3";
import {computed, getCurrentInstance, h, nextTick, onMounted, reactive, ref} from "vue";
import {NIcon, NTag} from "naive-ui";
import {Inertia} from "@inertiajs/inertia";

onMounted(() => {
    if(props.models){
        form.make = props.models.make_id
        form.name = props.models.name
    }
})
const form = useForm({
    name: null,
    make: null,
})



const props = defineProps({
    models: Array,
    makes: Array,
    versions: Array,
})


const onNewModelSubmit = () => {
    if(props.models){
        form.transform((el) => {
            return ({
                    name: el.name,
                    make: el.make ? el.make : null,
                }
            )
        }).put(route('warehouse.models.update',{
            id : props.models.id
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
                }
            )
        }).post(route('warehouse.models.store'), {
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
