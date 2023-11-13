<template>
    <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
    <template #icon>
        <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
    </template>
    {{ __('strings.bulkOperations') }}
    <n-space justify="center" class="mt-3">
        <n-button-group>
            <n-button type="success" size="small" secondary round bordered="false" @click="()=>isActiveSwitchBulk('true')">
                <font-awesome-icon :icon="'fa-duotone fa-check-circle'" class="mr-[3px]"></font-awesome-icon>
                {{ __('strings.active') }}
            </n-button>
            <n-button type="error" size="small" secondary round bordered="false" class="mr-3" @click="()=>isActiveSwitchBulk('false')">
                <font-awesome-icon :icon="'fa-duotone fa-times-circle'" class="mr-[3px]"></font-awesome-icon>
                {{ __('strings.notActive') }}
            </n-button>
        </n-button-group>
        <n-button type="error" @click="openDialogClick" secondary round="true" size="small">
            <font-awesome-icon icon="fa-duotone fa-trash" class="mr-1">

            </font-awesome-icon>
            {{ __('strings.deleteSelected') }}
        </n-button>
    </n-space>
</n-alert>
    <div class="flex w-full justify-between mb-2 gap-2">
        <div class="flex w-[600px] gap-2 mb-3">
            <n-input
                :placeholder="__('model.searchByModel')" clearable @clear="clearInput" v-model:value="inputValue"/>

            <n-button
                secondary
                class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                @click="searchString">
                <font-awesome-icon icon="fa-duotone fa-magnifying-glass" class="text-primaryColor"></font-awesome-icon>
            </n-button>
            <n-select id="filterActive" clearable @update:value="filterActive" v-model:value="value" :options="selectOptions"
                      :placeholder="__('strings.activeFilterSelection')" type="select"/>
        </div>
        <div>
            <n-button type="primary" v-if="props.permissions" @click="goToNewModel"
            >
                <font-awesome-icon class="text-md"
                                   :icon="'fa-duotone fa-plus'"> </font-awesome-icon>&nbsp;{{ __('model.newModel') }}
            </n-button>
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
    <n-dropdown
        placement="bottom-start"
        trigger="manual"
        :x="xRef"
        :y="yRef"
        :options="options"
        :show="showDropdownRef"
        :on-clickoutside="onClickoutside"
        @select="handleSelect"
    >
        <n-button>Go For a Trip</n-button>
    </n-dropdown>
    <pagination :resource="models"/>
</template>

<script setup>
import moment from 'moment';
import Pagination from "@/Components/Pagination.vue";
import {h, nextTick, onMounted, reactive, ref} from "vue";
import {VueFinalModal} from 'vue-final-modal';
import {useForm} from "@inertiajs/inertia-vue3";
import Multiselect from 'vue-multiselect';
import {Inertia} from "@inertiajs/inertia";
import InputCustom from '@/Components/Input.vue'
import {NButton, NIcon, NPopconfirm, NTag, useDialog} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
const showDropdownRef = ref(false)
const xRef = ref(0)
const yRef = ref(0)
const dialog = useDialog();
const openDialogClick = () => {
    dialog.error({
        title : $trans('strings.attention'),
        content : $trans('model.confirmDeleteModels'),
        positiveText :  $trans('strings.confirm'),
        negativeText : $trans('strings.cancel'),
        maskClosable : false,
        onPositiveClick : () => {
            deleteModels()
        }
    })
}
const deleteAlert = (roow) => {
    dialog.error({
        title : $trans('strings.attention'),
        content : $trans('model.confirmDeleteModelCascade'),
        positiveText :  $trans('strings.confirm'),
        negativeText : $trans('strings.cancel'),
        maskClosable : false,
        onPositiveClick : () => {
            onDeleteModelConfirm(row)
        }
    })
}
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){

        if(urlParams.has('make')){
            if(urlParams.has('active')){
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make'),
                    'active' : urlParams.get('active')
                })
            }else{
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make')
                })
            }
        }
        else{
            if(urlParams.has('active')){
                Inertia.get(route('warehouse.models.index'),{
                    'active' : urlParams.get('active')
                })
            }else{
                Inertia.get(route('warehouse.models.index'))
            }
        }
    }
}
const selectOptions = [
    , {
        label: $trans('strings.active'),
        value: 'true'
    },
    {
        label: $trans('strings.notActive'),
        value: 'false'
    }
]
const modelTablePagination = reactive({});

const pagination = reactive({
    page: 2,
    showSizePicker: false,
    itemCount: 20,
    onChange: (page) => {
        pagination.page = page
    },
});
const options = [
    {
        label() {
            return h('span', {}, "Vai alla marca associata")
        },
        icon() {
            return h(NIcon, {
                align: "right", on: {}
            }, {
                default: () => h(FontAwesomeIcon, {
                    icon: "fa-duotone fa-car"
                })
            })
        },
        key: 'edit'
    },
];
const handleSelect = () => {
    Inertia.get(route('warehouse.makes.index'));
}

const goToNewVersion = () => {
    Inertia.get(route('warehouse.versions.newIndex'));
}

const onClickoutside = () => {
    showDropdownRef.value = false
}

const rowProps = function (row) {
    return {
        onContextmenu: (e) => {
            e.preventDefault()
            showDropdownRef.value = false
            nextTick().then(() => {
                showDropdownRef.value = true
                xRef.value = e.clientX
                yRef.value = e.clientY
            })
        }
    }
};

const columns = [
    {
        type: 'selection',
        disabled : () => !props.permissions
    },
    {
        title: "ID",
        key: 'id',
        className: "td-text-gray"
    },
    {
        title: $trans('make.name'),
        key: 'make',
        className: "td-text-gray"
    },
    {
        title: $trans('model.name'),
        key: 'name',
        className: "td-text-gray"
    },
    {
        title: $trans('strings.active'),
        align: "center",
        key: 'is_active',
        render(row) {
            return h(
                NTag,
                {
                    style: {
                        marginRight: '6px'
                    },
                    type: row.is_active ? 'success' : 'error',
                    round: true,
                    bordered: false,
                    onClick: () => {
                        return isActiveSwitch(row)
                    }
                },
                {
                    default: () => [h(
                        FontAwesomeIcon,
                        {
                            icon: row.is_active ? "fa-duotone fa-check-circle" : "fa-duotone fa-times-circle"
                        }
                    ), h('span', {
                        style: {
                            marginLeft: '5px'
                        }
                    }, {
                        default: () => row.is_active ? $trans('strings.active') : $trans('strings.notActive'),
                    })]
                }
            )

        }
    },
    {
        title : $trans('version.versions'),
        align : 'center',
        key : 'versions',
        render(row){
              return h(
                  'div',
                  {
                      class : 'justify-center items-center relative'
                  },{
                      default : () => [
                           linkedVersions(row.id),
                          h(
                              FontAwesomeIcon,
                              {
                                  icon : 'fa-duotone fa-arrow-up-right-from-square',
                                  //class="absolute top-[2px] ml-3 text-md opacity-[0.7]" :class="linkedVersions(model.id) == 0 ? 'opacity-[0]' : null"
                                  class : linkedVersions(row.id) == 0 ?  'absolute top-[2px] ml-3 text-md opacity-[0]' : 'absolute top-[2px] ml-3 text-md opacity-[0.7]',
                                  onClick : () => {
                                      filterVersion(row.id)
                                  }
                              }
                          )
                      ]
                  }
              )
        }
    },
    {
        key : 'action',
        align : 'right',
        render(row){
            return props.permissions ? h(
                'div',
                {
                },
                {
                    default : () => [

                        h(
                            NButton,
                            {
                                disabled : !props.permissions,
                                size : 'small',
                                style : 'marginRight : 10px',
                                onClick: () => editModel(row),
                            },{
                                default : () => [h(
                                    FontAwesomeIcon,{
                                        icon : 'fa-duotone fa-edit',
                                        class : 'text-indigo-500'
                                    }
                                )]
                            }
                        ),
                        h(
                            NPopconfirm,
                            {
                                negativeText : $trans('strings.cancel'),
                                positiveText : $trans('strings.confirm'),
                                onPositiveClick: () => deleteAlert(row),
                            },{
                                default : () => [
                                    h(
                                        'div',
                                        {

                                        },{
                                            default : () => $trans('model.confirmDeleteModel')
                                        })
                                ],
                                activator : () => [
                                    h(
                                        NButton,
                                        {
                                            disabled : !props.permissions,
                                            size : 'small',
                                        },
                                        {
                                            default : () => [h(
                                                FontAwesomeIcon,{
                                                    icon : 'fa-duotone fa-trash',
                                                    class : 'text-red-500'
                                                }
                                            )
                                            ]
                                        }
                                    )
                                ],
                                icon : () => [
                                    h(
                                        FontAwesomeIcon,
                                        {
                                            icon : 'fa-solid fa-triangle-exclamation'
                                        }
                                    )
                                ]
                            }
                        )
                        ,]
                }
            ) : null
        }
    }

]
const form = useForm({
    name: null,
    make: null,
})
const editForm = useForm({
    name: null,
    make : null,
})
const value = ref(null);
const inputValue = ref('');
const checkedRowKeysRef = ref([]);
const tableData = ref([]);
const props = defineProps({
    models: Array,
    makes: Array,
    versions : Array,
    permissions : Boolean,
})
onMounted(() => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (urlParams.has('active')) {
        value.value = urlParams.get('active')
    }

    modelTablePagination.value = props.models.links;
    tableData.value = props.models.data.map((el) => {
        return {
            id: el.id,
            make: el.make.name,
            name: el.name,
            is_active: el.is_active
        }
    })
    document.title = $trans('model.models');
})
const isActiveSwitch = (model) => {
    Inertia.put(route('warehouse.models.setActive', {
        id: model.id
    }), {
        id: model.id,
        is_active: !model.is_active
    },{
    onSuccess: () => {
        modelTablePagination.value = props.models.links;
        tableData.value = props.models.data.map((el) => {
            return {
                id: el.id,
                make: el.make.name,
                name: el.name,
                is_active: el.is_active
            }
        })
    }}
    )
}
const isActiveSwitchBulk = (active) => {
    Inertia.put(route('warehouse.models.setActiveBulk',{
        ids : checkedRowKeysRef.value,
    }),{
        ids : checkedRowKeysRef.value,
        is_active : active == 'true' ? true : false
    },{
        onSuccess : () => {
            modelTablePagination.value = props.models.links;

            tableData.value = props.models.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
const onEditModelSubmit = () => {
    editForm.transform((el) => {
        return {
            name : el.name,
            make : el.make ? el.make.id : null
        }
    }).put(route('warehouse.models.update',{
        id : selModel.value.id
    }),{
        onFinish : () => {
            if(!editForm.hasErrors){
                editForm.reset('name');
                showEditModel.value = false;
            }
        }
    })
}
const onDeleteModelConfirm = (row) => {
    Inertia.delete(route('warehouse.models.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onSuccess: () => {
            modelTablePagination.value = props.models.links;
            tableData.value = props.models.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('make')){
        if(urlParams.has('active')){

            inputValue.value == '' ?
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make'),
                    'active' : urlParams.get('active')
                })
                :
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make'),
                    'search' : inputValue.value,
                    'active' : urlParams.get('active')
                })
        }else{
            inputValue.value == '' ?
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make')
                })
                :
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make'),
                    'search' : inputValue.value,
                })
        }
    }else{
        if(urlParams.has('active')){
            inputValue.value == '' ?
                Inertia.get(route('warehouse.models.index'),{
                    'active' : urlParams.get('active')
                })
                :
                Inertia.get(route('warehouse.models.index'),{
                    'search' : inputValue.value,
                    'active' : urlParams.get('active')
                })
        }else{
            inputValue.value == '' ?
                Inertia.get(route('warehouse.models.index'))
                :
                Inertia.get(route('warehouse.models.index'),{
                    'search' : inputValue.value
                })
        }
    }
}
const linkedVersions = (make) => {
    return props.versions.filter((e) => {
        return e.model_id == make
    }).length;
}
const filterVersion = (model) => {
    Inertia.get(route('warehouse.versions.index'),{
        'model' : model
    })
}
const filterActive = (active) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if(active !== null){
        if(active == 'true'){
            if(urlParams.has('make')){
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.models.index'),{
                        'make' : urlParams.get('make'),
                        'search' : urlParams.get('search'),
                        'active' : true,
                    })
                }else{
                    Inertia.get(route('warehouse.models.index'),{
                        'make' : urlParams.get('make'),
                        'active' : true,
                    })
                }

            }else{
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.models.index'),{
                        'search' : urlParams.get('search'),
                        'active' : true,
                    })
                }else{
                    Inertia.get(route('warehouse.models.index'),{
                        'active' : true,
                    })
                }
            }
        }else if (active == 'false'){
            if(urlParams.has('make')){
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.models.index'),{
                        'make' : urlParams.get('make'),
                        'search' : urlParams.get('search'),
                        'active' : false
                    })
                }else{
                    Inertia.get(route('warehouse.models.index'),{
                        'make' : urlParams.get('make'),
                        'active' : false,
                    })
                }
            }else{
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.models.index'),{
                        'search' : urlParams.get('search'),
                        'active' : false,
                    })
                }else{
                    Inertia.get(route('warehouse.models.index'),{
                        'active' : false,
                    })
                }
            }
        }
    }else{
        if(urlParams.has('make')){
            if(urlParams.has('search')){
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make'),
                    'search' : urlParams.get('search'),
                })
            }else{
                Inertia.get(route('warehouse.models.index'),{
                    'make' : urlParams.get('make')
                })
            }
        }else{
            if(urlParams.has('search')){
                Inertia.get(route('warehouse.models.index'),{
                    'search' : urlParams.get('search')
                })
            }else{
                Inertia.get(route('warehouse.models.index'))
            }
        }
    }
}
const goToNewModel = () => {
    Inertia.get(route('warehouse.models.newIndex'));
}
const editModel = (row) => {
    Inertia.get(route('warehouse.models.editIndex',{
        id : row.id
    }));
}
const deleteModels = () => {
    Inertia.delete(route('warehouse.models.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess : () => {
            modelTablePagination.value = props.models.links;
            tableData.value = props.models.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
</script>

<style scoped lang="scss">

:deep(td.td-text-gray) {
    @apply text-gray-500 font-normal;
}
</style>
