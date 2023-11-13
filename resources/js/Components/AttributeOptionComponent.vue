<template>

        <n-card class="!border-t-[3px] !border-t-primaryColor" >
            <div >
                <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
                    <template #icon>
                        <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
                    </template>
                    {{ __('strings.bulkOperations') }}
                    <n-space justify="center" class="mt-3">
                        <n-button type="error" @click="openDialogClick" secondary round="true" size="small">
                            <font-awesome-icon icon="fa-duotone fa-trash" class="mr-1">

                            </font-awesome-icon>
                            {{ __('strings.deleteSelected') }}
                        </n-button>
                    </n-space>
                </n-alert>
                <div class="flex w-full justify-between mb-2 gap-2">
                    <div class="flex w-[300px] gap-2 mb-3">
                        <n-input
                            :placeholder="__('attribute.searchByOption')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass"
                                               class="text-primaryColor"></font-awesome-icon>
                        </n-button>
                    </div>
                    <div>
                        <n-button type="primary" v-if="props.permissions"  @click="showModal = true"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('attribute.addOption')
                            }}
                        </n-button>
                        <n-modal
                            v-model:show="showModal"
                            preset="card"
                            class="!w-[450px]"
                            :title="__('attribute.newOption')"
                            :positive-text="__('strings.confirm')"
                            :negative-text="__('strings.cancel')"
                            @positive-click="confirmNewOption"
                            @negative-click="closeModal"
                            :on-after-leave="closeModal"

                        >
                            <InputCustom required :error="form.errors.value" :label="__('attribute.value')" :translate="true" v-model="form.value" ></InputCustom>
                            <div class="flex flex-row justify-between mt-2">

                                <n-button type="error" size="small"  @click="closeModal"
                                >Indietro
                                </n-button>
                                <n-button type="primary" size="small" @click="confirmNewOption"
                                >Conferma
                                </n-button>
                            </div>
                        </n-modal>
                    </div>
                </div>
                <n-data-table
                    remote
                    :columns="columns"
                    :data="tableData"
                    size="small"
                    :pagination="false"
                    :row-key="(row) => row.id"
                    :row-props="rowProps"
                    @update:checked-row-keys="(key) => checkedRowKeysRef = key"
                />
                <pagination :resource="props.attributeOptions"/>
            </div>
        </n-card>
</template>
<script setup>
import Authenticated from '@/Layouts/Authenticated.vue';
import Pagination from "@/Components/Pagination.vue";
import {VueFinalModal} from 'vue-final-modal';
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import InputCustom from '@/Components/Input.vue';
import ShowOrEdit from '@/Components/ShowOrEditInput.vue'
import {computed, onMounted, reactive, ref, watch, h, nextTick, defineComponent} from 'vue';
import {Inertia} from "@inertiajs/inertia";
import {NButton, NPopconfirm, NTag, useDialog, NDialogProvider, NInput, NDropdown, NInputGroup} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import NewEditAttributeComponent from "@/Components/NewEditComponents/NewEditAttributeComponent.vue";
const tableData = ref([]);
const dialog = useDialog();

const openDialogClick = () => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('attribute.confirmDeleteOptions'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteOptions()
        }
    })
}
const confirmNewOption = () => {
    form.post(route('warehouse.attributes.optionNew',{
        attribute_id : props.attribute.id
    }),{
        onFinish : () =>{
            if(!form.hasErrors){
                showModal.value = false;
                form.value = null;
                attributesTablePagination.value = props.attributeOptions.links;
                tableData.value = props.attributeOptions.data.map((el) => {
                    return {
                        id: el.id,
                        value : el.value,
                        position : el.position,
                        name : getTranslatedEntity(props.attribute.name),
                    }
                })
            }
        }
    })
}
const form = useForm({
    value : null
})
const closeModal = () => {
    form.clearErrors();
    showModal.value = false
    form.value = null
}
const showModal = ref(false);
const columns = [
    {
        type: 'selection',
    },
    {
        title: "ID",
        key: 'id',
        className: "td-text-gray"
    },
    {
        title: $trans('attribute.position'),
        key: 'position',
        className: "td-text-gray",
        render(row){
            return h(
                ShowOrEdit,
                {
                    type : 'position',
                    id : row.id,
                    position : row.position,
                    attribute_id : props.attribute.id,
                    positionId : positionId.value,
                    permissions : props.permissions,
                    onFocusInput : () => positionId.value = row.id,
                    onBlurInput : () => positionId.value = -1,
                    onChangedPosition : () => {

                        attributesTablePagination.value = props.attributeOptions.links;
                        tableData.value = props.attributeOptions.data.map((el) => {
                            return {
                                id: el.id,
                                value : el.value,
                                position : el.position,
                                name : getTranslatedEntity(props.attribute.name),
                            }
                        })
                    },

                }
            )

        }
    },
    {
        title: $trans('attribute.name'),
        key: 'name',
        className: "td-text-gray"
    },
    {
        title: $trans('attribute.value'),
        key: 'type_name',
        className: "td-text-gray",
        render(row,index){
            return h(
                'div',
                {
                },{
                    default : () => [
                        h(
                            ShowOrEdit,{
                                type : 'value',
                                openId : openId.value,
                                id : row.id,
                                permissions : props.permissions,
                                onSaveLastVal : (a)=> {
                                    openId.value = row.id
                                    lastVal.value = JSON.parse(JSON.stringify(a))
                                },
                                onCancelEdit : (a) => {
                                    if(a == -1 ) openId.value = a
                                    resetValues(row.id)
                                },
                                onConfirmEdit : () => {
                                  editOption(row)
                                },
                                onDeleteOption : () => {
                                    onDeleteOptionConfirm(row.id)
                                },
                                onChangedPosition : () => {

                                    attributesTablePagination.value = props.attributeOptions.links;
                                    tableData.value = props.attributeOptions.data.map((el) => {
                                        return {
                                            id: el.id,
                                            value : el.value,
                                            position : el.position,
                                            name : getTranslatedEntity(props.attribute.name),
                                        }
                                    })
                                },
                                value : row.value,
                                position : row.position,
                                attribute_id : props.attribute.id
                            }
                        )
                    ]
                }
            )
        }
    },
]
const positionId = ref(-1);
const openId = ref(-1);
const lastVal = ref(null);
const positionValue = ref('');
const edited = ref(false)
const props = defineProps({
    attribute: Object,
    attributeOptions : Array,
    permissions : Boolean,
})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const attributesTablePagination = reactive({});
const resetValues = (id) => {
    attributesTablePagination.value = props.attributeOptions.links;
    tableData.value = props.attributeOptions.data.map((el) => {
        return {
            id: el.id,
            value :  el.id == id ? lastVal.value : el.value,
            position : el.position,
            name : getTranslatedEntity(props.attribute.name),
        }
    })
    lastVal.value = {}
}
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        Inertia.get(route('warehouse.attributes.options',
            {id: props.attribute.id}), {

        })
    }
}
const searchString = () => {
    inputValue.value == '' ?
        Inertia.get(route('warehouse.attributes.options',
            {id: props.attribute.id}), {

        })
        :
        Inertia.get(route('warehouse.attributes.options',{
            id : props.attribute.id
        }), {
            'search': inputValue.value,
        })

}
onMounted(() => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (props.attributeOptions) {
        attributesTablePagination.value = props.attributeOptions.links;
        tableData.value = props.attributeOptions.data.map((el) => {
            return {
                id: el.id,
                value : el.value,
                position : el.position,
                name : getTranslatedEntity(props.attribute.name),
            }
        })
    }
    document.title = $trans('attribute.attributes')
})
const onDeleteOptionConfirm = (id) => {
    Inertia.delete(route('warehouse.attributes.optionDestroy', {
        attribute_id : props.attribute.id,
        id: id
    }), {
        data: {
            id: id
        },
        onFinish: () => {
            attributesTablePagination.value = props.attributeOptions.links;
            tableData.value = props.attributeOptions.data.map((el) => {
                return {
                    id: el.id,
                    value : el.value,
                    position : el.position,
                    name : getTranslatedEntity(props.attribute.name),
                }
            })
        }
    })
}
const editOption = (row) => {
    Inertia.put(route('warehouse.attributes.optionEdit',{
        attribute_id : props.attribute.id,
        id : row.id
    }),{
        value : row.value
    },{
        onFinish: () => {
            attributesTablePagination.value = props.attributeOptions.links;
            tableData.value = props.attributeOptions.data.map((el) => {
                return {
                    id: el.id,
                    value : el.value,
                    position : el.position,
                    name : getTranslatedEntity(props.attribute.name),
                }
            })
        }
    })
}
const deleteOptions = () => {
    Inertia.delete(route('warehouse.attributes.optionDeleteBulk',{
        attribute_id : props.attribute.id,
        ids : checkedRowKeysRef.value
    }),{
        onFinish : () => {
            checkedRowKeysRef.value = []
            attributesTablePagination.value = props.attributeOptions.links;
            tableData.value = props.attributeOptions.data.map((el) => {
                return {
                    id: el.id,
                    value : el.value,
                    position : el.position,
                    name : getTranslatedEntity(props.attribute.name),
                }
            })
        }
    })
}
const goBack = () => {
    Inertia.get(route('warehouse.attributes.index'))
}
</script>

<style scoped lang="scss">

:deep(td.td-text-gray) {
    @apply text-gray-500 font-normal;
}
</style>
