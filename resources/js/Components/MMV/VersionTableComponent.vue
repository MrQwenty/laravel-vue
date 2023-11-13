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
                :placeholder="__('version.searchByVersion')" clearable @clear="clearInput" v-model:value="inputValue"/>

            <n-button
                secondary
                class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                @click="searchString">
                <font-awesome-icon icon="fa-duotone fa-magnifying-glass" class="text-primaryColor"></font-awesome-icon>
            </n-button>
            <!--            <select >-->
            <!--                <option selected value="">Tutti gli stati</option>-->
            <!--                <option value="true">Attivo</option>-->
            <!--                <option value="false">Non attivo</option>-->
            <!--            </select>-->
            <n-select id="filterActive" clearable @update:value="filterActive" v-model:value="value" :options="selectOptions"
                      :placeholder="__('strings.activeFilterSelection')" type="select"/>
        </div>
        <div>
            <n-button type="primary" v-if="props.permissions" @click="goToNewVersion"
                     >
                <font-awesome-icon class="text-md"
                                   :icon="'fa-duotone fa-plus'"> </font-awesome-icon>&nbsp;{{ __('version.newVersion') }}
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
    <pagination :resource="versions"></pagination>
</template>

<script setup>
import moment from 'moment';
import Pagination from "@/Components/Pagination.vue";
import InputCustom from "@/Components/Input.vue";
import {getCurrentInstance, onMounted, ref, h, reactive, nextTick} from "vue";
import {VueFinalModal} from 'vue-final-modal';
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import Multiselect from 'vue-multiselect';
import {Inertia} from "@inertiajs/inertia";
import {NSelect, NInput, NButton, NDataTable, NTag, NIcon, NPopconfirm, useDialog} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
const showDropdownRef = ref(false)
const xRef = ref(0)
const yRef = ref(0)

const dialog = useDialog();

const openDialogClick = () => {
    dialog.error({
        title : $trans('strings.attention'),
        content : $trans('version.confirmDeleteVersions'),
        positiveText :  $trans('strings.confirm'),
        negativeText : $trans('strings.cancel'),
        maskClosable : false,
        onPositiveClick : () => {
            deleteVersions()
        }
    })
}
const deleteAlert = (row) => {
    dialog.error({
        title : $trans('strings.attention'),
        content : $trans('version.confirmDeleteVersionCascade'),
        positiveText :  $trans('strings.confirm'),
        negativeText : $trans('strings.cancel'),
        maskClosable : false,
        onPositiveClick : () => {
            onDeleteVersionConfirm(row)
        }
    })
}
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
        key: 'model',
        className: "td-text-gray"
    },
    {
        title: $trans('version.name'),
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
                                size : 'small',
                                disabled : !props.permissions,
                                style : 'marginRight : 10px',
                                onClick: () => editVersion(row),
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
                                        default : () => $trans('version.confirmDeleteVersion')
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
const editForm = useForm({
    name: null
})
const value = ref(null);
const showNewVersion = ref(false);
const showEditVersion = ref(false);
const showDelVersion = ref(false);
const showVersionInfo = ref(false);
const showEditVersionInfo = ref(false);
const selVersion = ref(false);
const inputValue = ref('');
const tableData = ref([]);
const checkedRowKeysRef = ref([]);
const instance = ref(getCurrentInstance());
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
const multiSelectMakes = ref([]);
const multiselectModels = ref([]);

const versionTablePagination = reactive({});

const pagination = reactive({
    page: 2,
    showSizePicker: false,
    itemCount: 20,
    onChange: (page) => {
        pagination.page = page
    },
});

const props = defineProps({
    models: Array,
    makes: Array,
    versions: Array,
    permissions : Boolean,
})
onMounted(() => {
    const instance = getCurrentInstance();

    multiSelectMakes.value = instance.vnode.props.makes.filter((make) => {
        return make.models.length > 0;
    });

    multiselectModels.value = instance.vnode.props.models;

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (urlParams.has('active')) {
        value.value = urlParams.get('active')
    }

    versionTablePagination.value = instance.vnode.props.versions.links;

    tableData.value = instance.vnode.props.versions.data.map((el) => {
        return {
            id: el.id,
            make: el.make.name,
            model: el.model.name,
            name: el.name,
            is_active: el.is_active
        }
    })
    document.title = $trans('version.versions');
})
const onNewVersionSubmit = () => {
    form.transform((el) => {
        return ({
                name: el.name,
                make: el.make ? el.make.id : null,
                model: el.model ? el.model.id : null,
                door_no: el.door_no ?? null,
                engine_size: el.engine_size ?? null,
                kw_power: el.kw_power ?? null,
                from_date: el.from_date ?? null,
                to_date: el.to_date ?? null,
            }
        )
    }).post(route('warehouse.versions.store'), {
        onFinish: () => {
            if (!form.hasErrors) {
                showNewVersion.value = false;
                form.reset(['name', 'make', 'model'])
            }
        }
    })
}
const isActiveSwitch = (version) => {
    Inertia.put(route('warehouse.versions.setActive', {
        id: version.id
    }), {
        id: version.id,
        is_active: !version.is_active
    }, {
        onSuccess: () => {
            versionTablePagination.value = props.versions.links;

            tableData.value = props.versions.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    model: el.model.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
const isActiveSwitchBulk = (active) => {
    Inertia.put(route('warehouse.versions.setActiveBulk',{
        ids : checkedRowKeysRef.value,
    }),{
        ids : checkedRowKeysRef.value,
        is_active : active == 'true' ? true : false
    },{
        onSuccess : () => {
            versionTablePagination.value = props.versions.links;

            tableData.value = props.versions.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    model: el.model.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
const onEditVersionSubmit = () => {
    editForm.put(route('warehouse.versions.update', {
        id: selVersion.value.id
    }), {
        onFinish: () => {
            if (!editForm.hasErrors) {
                editForm.reset('name')
                showEditVersion.value = false;
            }
        }
    })
}
const onDeleteVersionConfirm = (version) => {
    Inertia.delete(route('warehouse.versions.destroy', {
        id: version.id
    }), {
        data: {
            id: version.id
        },
        onSuccess : () => {
            versionTablePagination.value = props.versions.links;
            tableData.value = props.versions.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    model: el.model.name,
                    name: el.name,
                    is_active: el.is_active
                }
            })
        }
    })
}
const selectedMake = () => {

    form.model = null;
    form.clearErrors()
    multiselectModels.value = instance.value.vnode.props.models;
    const formData = {...form.data()};
    multiselectModels.value = multiselectModels.value.filter((model) => {
        return model.make_id == formData.make.id
    })
}
const editVersion = (row) => {
    Inertia.get(route('warehouse.versions.editIndex',{
        id : row.id
    }));
}
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        if (urlParams.has('make') || urlParams.has('model')) {
            if (urlParams.has('model')) {

                urlParams.has('active') ?

                    Inertia.get(route('warehouse.versions.index'), {
                        'model': urlParams.get('model'),
                        'active' : urlParams.get('active'),
                    })
                    :
                    Inertia.get(route('warehouse.versions.index'), {
                        'model': urlParams.get('model')
                    })
            } else {
                if (urlParams.has('make')) {
                    urlParams.has('active') ?

                        Inertia.get(route('warehouse.versions.index'), {
                            'make': urlParams.get('make'),
                            'active' : urlParams.get('active')
                        }):
                        Inertia.get(route('warehouse.versions.index'), {
                            'make': urlParams.get('make')
                        })
                }
            }
        } else {
            urlParams.has('active') ?
                Inertia.get(route('warehouse.versions.index'), {
                    'active': urlParams.get('active')
                }):
                Inertia.get(route('warehouse.versions.index'))

        }
    }

}
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('make') || urlParams.has('model')) {
        if (urlParams.has('model')) {

            inputValue.value == '' ?
                urlParams.has('active') ?

                    Inertia.get(route('warehouse.versions.index'), {
                        'model': urlParams.get('model'),
                        'active' : urlParams.get('active'),
                    })
                    :
                Inertia.get(route('warehouse.versions.index'), {
                    'model': urlParams.get('model')
                })
                :
                urlParams.has('active') ?
                Inertia.get(route('warehouse.versions.index'), {
                    'model': urlParams.get('model'),
                    'search': inputValue.value,
                    'active' : urlParams.get('active')
                })
                    :
                    Inertia.get(route('warehouse.versions.index'), {
                        'model': urlParams.get('model'),
                        'search': inputValue.value,
                    })
        } else {
            if (urlParams.has('make')) {
                inputValue.value == '' ?
                    urlParams.has('active') ?

                        Inertia.get(route('warehouse.versions.index'), {
                            'make': urlParams.get('make'),
                            'active' : urlParams.get('active')
                        }):
                    Inertia.get(route('warehouse.versions.index'), {
                        'make': urlParams.get('make')
                    })
                    :
                    urlParams.has('active') ?
                    Inertia.get(route('warehouse.versions.index'), {
                        'make': urlParams.get('make'),
                        'search': inputValue.value,
                        'active' : urlParams.get('active')
                    })
                        :
                        Inertia.get(route('warehouse.versions.index'), {
                            'make': urlParams.get('make'),
                            'search': inputValue.value,
                        })
            }
        }
    } else {
        inputValue.value == '' ?
            urlParams.has('active') ?
                Inertia.get(route('warehouse.versions.index'), {
                    'active': urlParams.get('active')
                }):
            Inertia.get(route('warehouse.versions.index'))
            :
            urlParams.has('active') ?
            Inertia.get(route('warehouse.versions.index'), {
                'search': inputValue.value,
                'active' : urlParams.get('active')
            })
                :
                Inertia.get(route('warehouse.versions.index'), {
                    'search': inputValue.value,
                })
    }
}
const formatDate = (date) => {
    if (date) {
        let data = new Date(date);
        return (data.getDate() >= 10 ? data.getDate() : '0' + data.getDate()) + '/' + (data.getMonth() + 1 >= 10 ? data.getMonth() + 1 : ('0' + (data.getMonth() + 1))) + '/' + data.getFullYear();
    }
}
const filterActive = (active) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if(active !== null){
        if(active == 'true'){
            if(urlParams.has('model') || urlParams.has('make')){
                if(urlParams.has('model')){
                    if(urlParams.has('search')){
                        Inertia.get(route('warehouse.versions.index'),{
                            'model' : urlParams.get('model'),
                            'search' : urlParams.get('search'),
                            'active' : true,
                        })
                    }else{
                        Inertia.get(route('warehouse.versions.index'),{
                            'model' : urlParams.get('model'),
                            'active' : true,
                        })
                    }
                }else{
                    if(urlParams.has('search')){
                        Inertia.get(route('warehouse.versions.index'),{
                            'make' : urlParams.get('make'),
                            'search' : urlParams.get('search'),
                            'active' : true,
                        })
                    }else{
                        Inertia.get(route('warehouse.versions.index'),{
                            'make' : urlParams.get('make'),
                            'active' : true,
                        })
                    }
                }
            }else{
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.versions.index'),{
                        'search' : urlParams.get('search'),
                        'active' : true,
                    })
                }else{
                    Inertia.get(route('warehouse.versions.index'),{
                        'active' : true,
                    })
                }
            }
        }else if (active == 'false'){
            if(urlParams.has('model') || urlParams.has('make')){
                if(urlParams.has('model')){
                    if(urlParams.has('search')){
                        Inertia.get(route('warehouse.versions.index'),{
                            'model' : urlParams.get('model'),
                            'search' : urlParams.get('search'),
                            'active' : false,
                        })
                    }else{
                        Inertia.get(route('warehouse.versions.index'),{
                            'model' : urlParams.get('model'),
                            'active' : false,
                        })
                    }
                }else{
                    if(urlParams.has('search')){
                        Inertia.get(route('warehouse.versions.index'),{
                            'make' : urlParams.get('make'),
                            'search' : urlParams.get('search'),
                            'active' : false
                        })
                    }else{
                        Inertia.get(route('warehouse.versions.index'),{
                            'make' : urlParams.get('make'),
                            'active' : false,
                        })
                    }
                }
            }else{
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.versions.index'),{
                        'search' : urlParams.get('search'),
                        'active' : false,
                    })
                }else{
                    Inertia.get(route('warehouse.versions.index'),{
                        'active' : false,
                    })
                }
            }
        }
    }else{
        if(urlParams.has('model') || urlParams.has('make')){
            if(urlParams.has('model')){
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.versions.index'),{
                        'model' : urlParams.get('model'),
                        'search' : urlParams.get('search'),
                    })
                }else{
                    Inertia.get(route('warehouse.versions.index'),{
                        'model' : urlParams.get('model'),
                    })
                }
            }else{
                if(urlParams.has('search')){
                    Inertia.get(route('warehouse.versions.index'),{
                        'make' : urlParams.get('make'),
                        'search' : urlParams.get('search'),
                    })
                }else{
                    Inertia.get(route('warehouse.versions.index'),{
                        'make' : urlParams.get('make')
                    })
                }
            }
        }else{
            if(urlParams.has('search')){
                Inertia.get(route('warehouse.versions.index'),{
                    'search' : urlParams.get('search')
                })
            }else{
                Inertia.get(route('warehouse.versions.index'))
            }
        }
    }
}
const onEditVersionInfoSubmit = () => {
    editInfoForm.transform((el) => {
        return ({
            name: el.name,
            make: el.make ? el.make.id : null,
            model: el.model ? el.model.id : null,
            door_no: el.door_no ?? null,
            engine_size: el.engine_size ?? null,
            kw_power: el.kw_power ?? null,
            from_date: el.from_date ?? null,
            to_date: el.to_date ?? null,
        })
    }).put(route('warehouse.versions.updateInfo', {
        id: selVersion.value.id
    }), {
        onFinish: () => {
            if (!editInfoForm.hasErrors) {
                showEditVersionInfo.value = false;
            }
        }
    })
}
const setupEditInfoForm = (version) => {
    editInfoForm.clearErrors();
    editInfoForm.door_no = version.door_no;
    editInfoForm.name = version.name;
    editInfoForm.kw_power = version.kw_power;
    editInfoForm.engine_size = version.engine_size;
    editInfoForm.make = version.make;
    editInfoForm.model = version.model;
    editInfoForm.from_date = version.from_date;
    editInfoForm.to_date = version.to_date;

    selVersion.value = version;
    showEditVersionInfo.value = true;
}
const selectedEditMake = () => {

    editInfoForm.model = null;
    editInfoForm.clearErrors()
    multiselectModels.value = instance.value.vnode.props.models;
    const formData = {...editInfoForm.data()};
    multiselectModels.value = multiselectModels.value.filter((model) => {
        return model.make_id == formData.make.id
    })
}
const deleteVersions = () => {
    Inertia.delete(route('warehouse.versions.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess : () => {
            versionTablePagination.value = props.versions.links;
            tableData.value = props.versions.data.map((el) => {
                return {
                    id: el.id,
                    make: el.make.name,
                    model: el.model.name,
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
