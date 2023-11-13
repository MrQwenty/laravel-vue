<template>
    <BreezeAuthenticatedLayout>
        <template #header>
            <n-space vertical class="w-full ">
                <n-alert type="success" closable class="mb-4"
                         v-show="usePage().props.value.flash.message">
                    {{ usePage().props.value.flash.message }}
                </n-alert>
                <n-card class="!border-t-[3px] !border-t-primaryColor">
                    <n-page-header :subtitle="__('user.handleUsers')" @back="handleBack">
                        <template #title>
                            <a>
                                {{ __(routeInfo.title) }}
                            </a>
                        </template>
                        <template #avatar>
                            <font-awesome-icon icon="fa-duotone fa-user" size="2xl"></font-awesome-icon>
                        </template>
                    </n-page-header>
                    <template #header>
                        <n-breadcrumb>
                            <n-breadcrumb-item v-for="b of routeInfo.breadcrumbs">{{ __(b.title) }}</n-breadcrumb-item>
                            <!--                                <n-breadcrumb-item>Versioni</n-breadcrumb-item>-->
                        </n-breadcrumb>
                    </template>
                </n-card>

                <n-alert v-show="props.filters" type="info" class="mt-3 "
                >
                    <span class="text-[#767c82]">
                        {{ __('strings.filteringFor') }}
                    </span>
                    <ul class="list-disc ">
                        <li v-for="filter in props.filters">
                            <div v-if="filter.value !== null">
                                <span class="text-[#767c82]">{{ filter.key }} -</span> <span
                                class="font-medium">"{{ filter.value }}"</span>
                            </div>
                        </li>
                    </ul>
                </n-alert>
            </n-space>
        </template>
        <n-card class="!border-t-[3px] !border-t-primaryColor">
            <div v-if="props.tab == 'users'">
                <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
                    <template #icon>
                        <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
                    </template>
                    {{ __('strings.bulkOperations') }}
                    <n-space justify="center" class="mt-3">
                        <!--                        <n-button-group>
                                                    <n-button type="success" size="small" secondary round bordered="false"
                                                              @click="()=>isActiveSwitchBulk('true')">
                                                        <font-awesome-icon :icon="'fa-duotone fa-check-circle'"
                                                                           class="mr-[3px]"></font-awesome-icon>
                                                        {{ __('strings.active') }}
                                                    </n-button>
                                                    <n-button type="error" size="small" secondary round bordered="false" class="mr-3"
                                                              @click="()=>isActiveSwitchBulk('false')">
                                                        <font-awesome-icon :icon="'fa-duotone fa-times-circle'"
                                                                           class="mr-[3px]"></font-awesome-icon>
                                                        {{ __('strings.notActive') }}
                                                    </n-button>
                                                </n-button-group>-->
                        <n-button type="error" @click="openDialogClick" secondary :round="true" size="small">
                            <font-awesome-icon icon="fa-duotone fa-trash" class="mr-1">

                            </font-awesome-icon>
                            {{ __('strings.deleteSelected') }}
                        </n-button>
                    </n-space>
                </n-alert>
                <div class="flex w-full justify-between mb-2 gap-2">
                    <div class="flex w-[300px] gap-2 mb-3">
                        <n-input
                            :placeholder="__('user.searchByUser')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass"
                                               class="text-primaryColor"></font-awesome-icon>
                        </n-button>
                    </div>
                    <div>
                        <n-button type="primary" @click="goToNewUser"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('user.newUser')
                            }}
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
                <pagination :resource="users"/>
            </div>
            <NewEditUserComponent v-if="props.tab == 'users/new' "
            ></NewEditUserComponent>
            <NewEditUserComponent v-if="props.tab == 'users/edit' " :roles="props.roles" :permissions="props.permissions" :user="props.userEdit"
            ></NewEditUserComponent>
        </n-card>
    </BreezeAuthenticatedLayout>
</template>

<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Pagination from "@/Components/Pagination.vue";
import {VueFinalModal} from 'vue-final-modal';
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import InputCustom from '@/Components/Input.vue';
import NewEditUserComponent from "@/Components/NewEditComponents/NewEditUserComponent.vue";


import {onMounted, ref, watch} from 'vue';
import {Inertia} from "@inertiajs/inertia";
import {NButton, NPopconfirm, NTag, useDialog} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const routeInfo = computed(() => {
    let route = "";
    let breadcrumbs = [
        {'title': $trans('menu.admininstration')}
    ];
    switch (props.tab) {
        case 'users':
            route = 'user.users';
            breadcrumbs.push({
                title: route
            })
            break;
        case 'users/new':
            route = 'user.newUser';
            breadcrumbs.push({
                title: 'user.users'
            }, {title: 'user.newUser'})
            break;
        case 'users/edit' :
            route = 'user.editUser';
            breadcrumbs.push({
                title: 'user.users'
            }, {title: 'user.editUser'})
            break;
    }
    return {
        title: route,
        breadcrumbs: breadcrumbs,
    }
});

const value = ref(null);
const xRef = ref(0)
const yRef = ref(0)
const tableData = ref([]);
const dialog = useDialog();

const openDialogClick = () => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('user.confirmDeleteUsers'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteUsers()
        }
    })
}
watch(() => props.users, () => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (props.users) {
        userTablePagination.value = props.users.links;
        tableData.value = props.users.data.map((el) => {
            return {
                id: el.id,
                name: el.name,
                type: el.type,
                email: el.email,
                is_admin: el.is_admin
            }
        })
    }
})
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

const pagination = reactive({
    page: 2,
    showSizePicker: false,
    itemCount: 20,
    onChange: (page) => {
        pagination.page = page
    },
});

const onClickoutside = () => {
    showDropdownRef.value = false
}


const columns = [
    {
        type: 'selection',
        disabled : (row) => row.id == usePage().props.value.auth.user.id
    },
    {
        title: "ID",
        key: 'id',
        className: "td-text-gray"
    },
    {
        title: $trans('user.name'),
        key: 'name',
        className: "td-text-gray"
    },
    {
        title: $trans('user.type'),
        key: 'type',
        className: "td-text-gray",
        align: "center",
        render(row) {
            return h(
                NTag,
                {
                    style: {
                        marginRight: '5px'
                    },
                    type: row.type == 'CRM' ? 'primary' : 'warning',
                    round: true,
                    size: 'small',
                    bordered: false,
                    onClick: () => {
                        return isActiveSwitch(row)
                    }
                },
                {
                    default: () => [h('span', {
                        style: {}
                    }, {
                        default: () => row.type,
                    })]
                }
            )

        }
    },
    {
        title: $trans('user.email'),
        key: 'email',
        className: "td-text-gray"
    },
    {
        title: $trans('user.role'),
        key: 'is_admin',
        className: "td-text-gray",
        align: "center",
        render(row) {
            return h(
                NTag,
                {
                    style: {
                        marginRight: '2px'
                    },
                    type: row.is_admin ? '' : 'success',
                    color: row.is_admin ? {color: '#d9d3ef', textColor: '#905aed'} : '',
                    round: true,
                    size: 'small',
                    bordered: false,
                    onClick: () => {
                        return isActiveSwitch(row)
                    }
                },
                {
                    default: () => [h('span', {
                        style: {}
                    }, {
                        default: () => row.is_admin ? $trans('user.is_admin') : $trans('user.baseUser'),
                    })]
                }
            )

        }
    },

    {
        key: 'action',
        align: 'right',
        render(row) {
            return h(
                'div',
                {},
                {
                    default: () => [

                        h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                onClick: () => editUser(row),
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
                                onPositiveClick: () => onDeleteUserConfirm(row),
                            }, {
                                default: () => [
                                    h(
                                        'div',
                                        {}, {
                                            default: () => $trans('user.confirmDeleteUser')
                                        })
                                ],
                                activator: () => [
                                    h(
                                        NButton,
                                        {
                                            size: 'small',
                                            disabled : row.id == usePage().props.value.auth.user.id
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
            )
        }
    }

]
const props = defineProps({
    users: Array,
    tab: String,
    userEdit: Object,
    filters: Array,
    roles : Array,
    permissions : Array,
})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const showFlashMessage = ref(false);
const userTablePagination = reactive({});
onMounted(() => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (props.users) {
        userTablePagination.value = props.users.links;
        tableData.value = props.users.data.map((el) => {
            return {
                id: el.id,
                name: el.name,
                type: el.type,
                email: el.email,
                is_admin: el.is_admin
            }
        })
    }
    document.title = $trans('user.users')
    if (usePage().props.value.flash.message) {
        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
})
const onDeleteUserConfirm = (row) => {
    if(row.id == usePage().props.value.auth.user.id ){
        return
    }
    Inertia.delete(route('admininstration.users.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            userTablePagination.value = props.users.links;
            tableData.value = props.users.data.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    type: el.type,
                    email: el.email,
                    is_admin: el.is_admin
                }
            })
        }
    })
}
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        Inertia.get(route('admininstration.users.index'))
    }
}
const searchString = () => {
    inputValue.value == '' ?
        Inertia.get(route('admininstration.users.index'))
        :
        Inertia.get(route('admininstration.users.index'), {
            'search': inputValue.value
        })
}
const goToNewUser = () => {
    Inertia.get(route('admininstration.users.newUser'));
}
const editUser = (user) => {
    Inertia.get(route('admininstration.users.editUser'), {
        id: user.id
    })
}
const deleteUsers = () => {
    Inertia.delete(route('admininstration.users.destroyBulk', {
        'ids': checkedRowKeysRef.value
    }), {
        onSuccess: () => {

            userTablePagination.value = props.users.links;
            tableData.value = props.users.data.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    type: el.type,
                    email: el.email,
                    is_admin: el.is_admin
                }
            })
        }
    })
}
</script>

<style scoped>

:deep(td.td-text-gray) {
    @apply text-gray-500 font-normal;
}

:deep(.n-submenu-children) {
}

</style>
