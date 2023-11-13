<template>
    <div>
        <div>
            <form @submit.prevent="onNewMakeSubmit">
                <div class="grid grid-cols-1 gap-x-3 flex">
                    <div class="flex gap-x-3">
                        <div class="flex w-full">
                            <InputCustom customClass="w-full"  v-model="form.name" required :label="__('strings.name')"
                                     id="name"
                                     name="name"
                                     :error="form.errors.name" type="text"/>
                        </div>
                        <div class="flex-col" :class="edit ? 'min-w-[200px]' : null">
                            <InputCustom type="upload" required :label="__('strings.logo')"
                                         id="logo"
                                         name="logo"
                                         :error="form.errors.logo">
                                <n-upload
                                    v-if="!edit"
                                    :max="1"
                                    accept="image/*"
                                    :action="route('warehouse.makes.store')"
                                    :default-upload="false"
                                    @change="changeUpload"
                                    list-type="image-card"
                                >
                                    {{__('make.insertLogo')}}
                                </n-upload>
                                <n-upload
                                    class="mr-5"
                                    v-if="edit && !newLogo "
                                    :max="1"
                                    accept="image/*"
                                    :show-remove-button="false"
                                    :default-upload="false"
                                    :file-list="previewFileList"
                                    @change="changeUpload"
                                    list-type="image-card"
                                >
                                Clicca per caricare un logo
                            </n-upload>
                            <n-upload
                                v-if="edit"
                                :max="1"
                                accept="image/*"
                                :show-remove-button="!edit"
                                :action="route('warehouse.makes.updateLogo')"
                                :default-upload="false"
                                @change="changeUpload"
                                list-type="image-card"
                            >
                                Clicca per inserire un nuovo logo
                            </n-upload>
                            </InputCustom>
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
    if (props.makes) {
        edit.value = true
        form.name = props.makes.name
    }
    if(props.logo){
        if(props.logo[0]){
            setPreviewList()
        }
    }
})
const form = useForm({
    name: null,
    logo : null,
})
const edit = ref(false);
const newLogo = ref(false);
const changeUpload = (e) => {
    if(edit){
        if(e.fileList.length > 0){
            newLogo.value = true
            form.logo = e.file
        }else{
            newLogo.value = false
            form.logo = null
        }
    }else{
        form.logo = e.file;
    }
}
const previewFileList = ref([])
const props = defineProps({
    models: Array,
    makes: Array,
    versions: Array,
    logo : Array,
    path : String,
})
const onNewMakeSubmit = () => {
    if (props.makes) {
        /*Inertia.post(route('warehouse.makes.updateLogo'),{
            logo : form.logo.file
        },{
            onSuccess : (e) => {
                form.transform((el) => {
                return ({
                        name: el.name,
                        logo : el.logo ? el.logo.file : null,
                    }
                )
            })
                .put(route('warehouse.makes.update', {
                    id: props.makes.id
                }), {
                    onFinish: () => {
                        if (!form.hasErrors) {
                            //Inertia.get(route('warehouse.versions.index'))
                        }
                    }
                })
            }
        })
        return*/
        form.transform((el) => {
            return ({
                    name: el.name,
                    logo : el.logo ? el.logo.file : null,
                    _method: "PUT"
                }
            )
        })
        .post(route('warehouse.makes.update', {
            id: props.makes.id
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
                    logo : el.logo ? el.logo.file : null,
                }
            )
        }).post(route('warehouse.makes.store'), {
            onFinish: () => {
                if (!form.hasErrors) {
                    //Inertia.get(route('warehouse.versions.index'))
                }
            },
        },
        )
    }
}
const setPreviewList = (logo) => {
    if(logo){
        previewFileList.value = []
        /*previewFileList.value =  [{
            id: "logo",
            name: "Logo",
            status: "finished",
            url: logo.url
        },]*/
    }else{

        previewFileList.value =  [{
            id: "logo",
            name: "Logo",
            status: "finished",
            url: props.path
        },]
    }

}
const goToBack = function () {
    window.history.back()
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
