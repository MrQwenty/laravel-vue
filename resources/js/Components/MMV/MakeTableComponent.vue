<template>
    <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
        <template #icon>
            <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
        </template>
        {{ __('strings.bulkOperations') }}
        <n-space justify="center" class="mt-3">
            <n-button-group>
                <n-button type="success" size="small" secondary round bordered="false"
                          @click="()=>isActiveSwitchBulk('true')">
                    <font-awesome-icon :icon="'fa-duotone fa-check-circle'" class="mr-[3px]"></font-awesome-icon>
                    {{ __('strings.active') }}
                </n-button>
                <n-button type="error" size="small" secondary round bordered="false" class="mr-3"
                          @click="()=>isActiveSwitchBulk('false')">
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
                :placeholder="__('make.searchByMake')" clearable @clear="clearInput" v-model:value="inputValue"/>

            <n-button
                secondary
                class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                @click="searchString">
                <font-awesome-icon icon="fa-duotone fa-magnifying-glass" class="text-primaryColor"></font-awesome-icon>
            </n-button>
            <n-select id="filterActive" clearable @update:value="filterActive" v-model:value="value"
                      :options="selectOptions"
                      :placeholder="__('strings.activeFilterSelection')" type="select"/>
        </div>
        <div>
            <n-button type="primary" v-if="props.permissions" @click="goToNewMake"
            >
                <font-awesome-icon class="text-md"
                                   :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{ __('make.newMake') }}
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
    <pagination :resource="makes"/>
</template>

<script setup>

import moment from 'moment';
import Pagination from "@/Components/Pagination.vue";
import InputCustom from '@/Components/Input.vue';
import {VueFinalModal} from 'vue-final-modal';
import {useForm} from "@inertiajs/inertia-vue3";


import {h, nextTick, onMounted, reactive, ref} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {NButton, NIcon, NImage, NPopconfirm, NTag, useDialog} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const checkedRowKeysRef = ref([]);
const value = ref(null);
const inputValue = ref('');
const selMake = ref({});
const xRef = ref(0)
const yRef = ref(0)
const dialog = useDialog();
const tableData = ref([]);
const openDialogClick = () => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('make.confirmDeleteMakes'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteMakes()
        }
    })
}
const deleteAlert = (row) => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('make.confirmDeleteMakeCascade'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            onDeleteMakeConfirm(row)
        }
    })
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
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        if (urlParams.has('active')) {
            Inertia.get(route('warehouse.makes.index'), {
                'active': urlParams.get('active')
            })
        } else {
            Inertia.get(route('warehouse.makes.index'))
        }
    }
}
const makeTablePagination = reactive({});

const pagination = reactive({
    page: 2,
    showSizePicker: false,
    itemCount: 20,
    onChange: (page) => {
        pagination.page = page
    },
});

const goToNewVersion = () => {
    Inertia.get(route('warehouse.makes.newIndex'));
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
        key: 'logo',
        className: "td-text-gray",
        align: "center",
        render(row) {
            return h(
                NImage,
                {
                    src: props.imgBaseUrl + row.logo.path,
                    style: {
                        width: "70px",
                        verticalAlign: "middle",
                        minWidth: "70px"
                    }
                }
            )
        }
    },
    {
        title: $trans('make.name'),
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
        title: $trans('model.models'),
        align: 'center',
        key: 'models',
        render(row) {
            return h(
                'div',
                {
                    class: 'justify-center items-center relative'
                }, {
                    default: () => [
                        linkedModels(row.id),
                        h(
                            FontAwesomeIcon,
                            {
                                icon: 'fa-duotone fa-arrow-up-right-from-square',
                                //class="absolute top-[2px] ml-3 text-md opacity-[0.7]" :class="linkedVersions(model.id) == 0 ? 'opacity-[0]' : null"
                                class: linkedModels(row.id) == 0 ? 'absolute top-[2px] ml-3 text-md opacity-[0]' : 'absolute top-[2px] ml-3 text-md opacity-[0.7]',
                                onClick: () => {
                                    filterModel(row.id)
                                }
                            }
                        )
                    ]
                }
            )
        }
    },
    {
        title: $trans('version.versions'),
        align: 'center',
        key: 'versions',
        render(row) {
            return h(
                'div',
                {
                    class: 'justify-center items-center relative'
                }, {
                    default: () => [
                        linkedVersions(row.id),
                        h(
                            FontAwesomeIcon,
                            {
                                icon: 'fa-duotone fa-arrow-up-right-from-square',
                                //class="absolute top-[2px] ml-3 text-md opacity-[0.7]" :class="linkedVersions(model.id) == 0 ? 'opacity-[0]' : null"
                                class: linkedVersions(row.id) == 0 ? 'absolute top-[2px] ml-3 text-md opacity-[0]' : 'absolute top-[2px] ml-3 text-md opacity-[0.7]',
                                onClick: () => {
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
        key: 'action',
        align: 'right',
        render(row) {
            return props.permissions ? h(
                'div',
                {
                },
                {
                    default: () => [
                        h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                disabled : !props.permissions,
                                onClick: () => editMake(row),
                            }, {
                                default: () => [h(
                                    FontAwesomeIcon, {
                                        icon: 'fa-duotone fa-edit',
                                        class: 'text-indigo-500'
                                    }
                                )]
                            }
                        ),
                        h(
                            NPopconfirm,
                            {
                                negativeText: $trans('strings.cancel'),
                                positiveText: $trans('strings.confirm'),
                                onPositiveClick: () => deleteAlert(row),
                            }, {
                                default: () => [
                                    h(
                                        'div',
                                        {}, {
                                            default: () => $trans('make.confirmDeleteMake')
                                        })
                                ],
                                activator: () => [
                                    h(
                                        NButton,
                                        {
                                            'size': 'small',
                                            disabled : !props.permissions,
                                        },
                                        {
                                            default: () => [h(
                                                FontAwesomeIcon, {
                                                    icon: 'fa-duotone fa-trash',
                                                    class: 'text-red-500'
                                                }
                                            )
                                            ]
                                        }
                                    )
                                ],
                                icon: () => [
                                    h(
                                        FontAwesomeIcon,
                                        {
                                            icon: 'fa-solid fa-triangle-exclamation'
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
const props = defineProps({
    makes: Array,
    models: Array,
    versions: Array,
    imgBaseUrl: String,
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

    makeTablePagination.value = props.makes.links;
    tableData.value = props.makes.data.map((el) => {
        return {
            id: el.id,
            name: el.name,
            is_active: el.is_active,
            logo: el.logo
        }
    })
    document.title = $trans('make.makes');
})

const isActiveSwitch = (make) => {
    Inertia.put(route('warehouse.makes.setActive', {
            id: make.id
        }), {
            id: make.id,
            is_active: !make.is_active,
        },
        {
            onSuccess: () => {
                makeTablePagination.value = props.makes.links;
                tableData.value = props.makes.data.map((el) => {
                    return {
                        id: el.id,
                        name: el.name,
                        is_active: el.is_active,
                        logo: el.logo
                    }
                })
            }
        })
}

const onDeleteMakeConfirm = (row) => {
    Inertia.delete(route('warehouse.makes.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            makeTablePagination.value = props.makes.links;
            tableData.value = props.makes.data.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    is_active: el.is_active,
                    logo: el.logo
                }
            })
        }
    })
}
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('active')) {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.makes.index'), {
                'active': urlParams.get('active')
            })
            :
            Inertia.get(route('warehouse.makes.index'), {
                'search': inputValue.value,
                'active': urlParams.get('active')
            })
    } else {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.makes.index'))
            :
            Inertia.get(route('warehouse.makes.index'), {
                'search': inputValue.value
            })
    }
}
const linkedModels = (make) => {
    return props.models.filter((e) => {
        return e.make_id == make
    }).length;
}
const linkedVersions = (make) => {
    return props.versions.filter((e) => {
        return e.make_id == make
    }).length;
}
const filterModel = (make) => {
    Inertia.get(route('warehouse.models.index'), {
        'make': make
    })
}
const filterVersion = (make) => {
    Inertia.get(route('warehouse.versions.index'), {
        'make': make
    })
}

const filterActive = (active) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if (active !== null) {
        if (active == 'true') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.makes.index'), {
                    'search': urlParams.get('search'),
                    'active': true,
                })
            } else {
                Inertia.get(route('warehouse.makes.index'), {
                    'active': true,
                })
            }
        } else if (active == 'false') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.makes.index'), {
                    'search': urlParams.get('search'),
                    'active': false,
                })
            } else {
                Inertia.get(route('warehouse.makes.index'), {
                    'active': false,
                })
            }
        }
    } else {
        if (urlParams.has('search')) {
            Inertia.get(route('warehouse.makes.index'), {
                'search': urlParams.get('search')
            })
        } else {
            Inertia.get(route('warehouse.makes.index'))
        }
    }
}
const goToNewMake = () => {
    Inertia.get(route('warehouse.makes.newIndex'),{

    },{
        onFinish : (e) => {
        }
    });
}
const editMake = (row) => {
    Inertia.get(route('warehouse.makes.editIndex', {
        id: row.id
    }));
}
const deleteMakes = () => {
    Inertia.delete(route('warehouse.makes.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess: () => {
            makeTablePagination.value = props.makes.links;
            tableData.value = props.makes.data.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    is_active: el.is_active,
                    logo: el.logo
                }
            })
        }
    })
}
const isActiveSwitchBulk = (active) => {
    Inertia.put(route('warehouse.makes.setActiveBulk', {
        ids: checkedRowKeysRef.value,
    }), {
        ids: checkedRowKeysRef.value,
        is_active: active == 'true' ? true : false
    }, {
        onSuccess: () => {
            makeTablePagination.value = props.makes.links;

            tableData.value = props.makes.data.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    is_active: el.is_active,
                    logo: el.logo
                }
            })
        }
    })
}
const getImage = (path) => {
    return 'public/' + path
}
</script>

<style scoped lang="scss">

:deep(td.td-text-gray) {
    @apply text-gray-500 font-normal;
}
</style>
