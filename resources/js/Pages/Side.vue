<template>
    <BreezeAuthenticatedLayout>
        <template #header>
            <n-space vertical class="w-full ">
                <n-alert type="success" closable class="mb-4"
                         v-show="usePage().props.value.flash.message && showFlashMessage">
                    {{ usePage().props.value.flash.message }}
                </n-alert>
                <n-alert type="error" closable class="mb-4"
                         v-show="usePage().props.value.flash.error && showFlashError">
                    {{ usePage().props.value.flash.error }}
                </n-alert>
                <n-card class="!border-t-[3px] !border-t-primaryColor">
                    <n-page-header :subtitle="__('side.handleSide')" @back="handleBack">
                        <template #title>
                            <a>
                                {{ __(routeInfo.title) }}
                            </a>
                        </template>
                        <template #header>
                            <n-breadcrumb>
                                <n-breadcrumb-item v-for="b of routeInfo.breadcrumbs">{{ __(b.title) }}</n-breadcrumb-item>
                            </n-breadcrumb>
                        </template>
                        <template #avatar>
                            <font-awesome-icon icon="fa-duotone fa-arrows-left-right" size="2xl"></font-awesome-icon>
                        </template>
                    </n-page-header>
                </n-card>

                <n-alert v-show="props.filters" type="info"  class="mt-3 "
                >
                    <span class="text-[#767c82]">
                        {{__('strings.filteringFor')}}
                    </span>
                    <ul class="list-disc ">
                        <li v-for="filter in props.filters">
                            <div v-if="filter.value !== null" >
                                <span class="text-[#767c82]">{{ filter.key }} -</span> <span class="font-medium">"{{filter.value}}"</span>
                            </div>
                        </li>
                    </ul>
                </n-alert>
            </n-space>
        </template>
        <n-card class="!border-t-[3px] !border-t-primaryColor">
            <div v-if="props.tab == 'sides'">
                <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
                    <template #icon>
                        <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
                    </template>
                    {{ __('strings.bulkOperations') }}
                    <n-space justify="center" class="mt-3">
<!--                        <n-button-group>
                            <n-button type="success" size="small" secondary round bordered="false" @click="()=>isActiveSwitchBulk('true')">
                                <font-awesome-icon :icon="'fa-duotone fa-check-circle'" class="mr-[3px]"></font-awesome-icon>
                                {{ __('strings.active') }}
                            </n-button>
                            <n-button type="error" size="small" secondary round bordered="false" class="mr-3" @click="()=>isActiveSwitchBulk('false')">
                                <font-awesome-icon :icon="'fa-duotone fa-times-circle'" class="mr-[3px]"></font-awesome-icon>
                                {{ __('strings.notActive') }}
                            </n-button>
                        </n-button-group>-->
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
                            :placeholder="__('side.searchBySide')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass" class="text-primaryColor"></font-awesome-icon>
                        </n-button>
<!--                        <n-select id="filterActive" clearable @update:value="filterActive" v-model:value="value" :options="selectOptions"
                                  :placeholder="__('strings.activeFilterSelection')" type="select"/>-->
                    </div>
                    <div>
                        <n-button type="primary" v-if="props.permissions" @click="goToNewSide"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"> </font-awesome-icon>&nbsp;{{ __('side.newSide') }}
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
                <pagination :resource="sides"/>
            </div>
            <NewEditSideComponent v-if="props.tab == 'sides/new'"
            ></NewEditSideComponent>
            <NewEditSideComponent v-if="props.tab == 'sides/edit' " :sides="props.sideEdit"
            ></NewEditSideComponent>
        </n-card>
    </BreezeAuthenticatedLayout>
</template>

<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import Pagination from "@/Components/Pagination.vue";
import InputCustom from '@/Components/Input.vue';
import {VueFinalModal} from 'vue-final-modal';
import {useForm,usePage} from "@inertiajs/inertia-vue3";

import NewEditSideComponent from "@/Components/NewEditComponents/NewEditSideComponent.vue";


import {h,onMounted, ref, watch} from 'vue';
import {Inertia} from "@inertiajs/inertia";
import {NButton, NPopconfirm} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
watch(() => usePage().props.value.flash.message, () => {
        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
)
watch(() => usePage().props.value.flash.error, () => {
        showFlashError.value = true;
        setTimeout(() => {
            showFlashError.value = false;
            usePage().props.value.flash.error = null
        }, 3000);
    }
)
const showFlashError = ref(true);
const props = defineProps({
    sides: Array,
    tab : String,
    filters : Array,
    sideEdit : Object,
    permissions : Boolean

})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const routeInfo = computed(() => {
    let route = "";

    let breadcrumbs = [{'title' : $trans('menu.warehouse')},
        {'title': $trans('menu.sides')}
    ];
    switch (props.tab) {
        case 'sides':
            route = 'side.sides';
            break;
        case 'sides/new':
            route = 'side.newSide';
            breadcrumbs.push({
                title : 'side.newSide'
            })
            break;
        case 'sides/edit' :
            route = 'side.editSide';
            breadcrumbs.push({
                title : 'side.editSide'
            })
            break;
    }
    return {
        title: route,
        breadcrumbs : breadcrumbs
    }
});

const value = ref(null);
const selMake = ref({});
const xRef = ref(0)
const yRef = ref(0)
const tableData = ref([]);
//const dialog = useDialog();
const openDialogClick = () => {
    dialog.error({
        title : $trans('strings.attention'),
        content : $trans('make.confirmDeleteMakes'),
        positiveText :  $trans('strings.confirm'),
        negativeText : $trans('strings.cancel'),
        maskClosable : false,
        onPositiveClick : () => {
            deleteMakes()
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

const pagination = reactive({
    page: 2,
    showSizePicker: false,
    itemCount: 20,
    onChange: (page) => {
        pagination.page = page
    },
});

const goToNewCategory = () => {
    Inertia.get(route('warehouse.categories.newIndex'));
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
    /*{
        type: 'selection'
    },*/
    {
        title: "ID",
        key: 'id',
        className: "td-text-gray"
    },
    {
        title: $trans('side.value'),
        key: 'value',
        className: "td-text-gray"
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
                                onClick: () => editSide(row),
                            },{
                                default : () => [h(
                                    FontAwesomeIcon,{
                                        icon : 'fa-duotone fa-edit',
                                        class : 'text-indigo-500'
                                    }
                                )]
                            }
                        ),
                    ]
                }
            ) : null
        }
    }

]

const sideTablePagination = reactive({});
onMounted(() => {

    if(usePage().props.value.flash.error){

        showFlashError.value = true;
        setTimeout(() => {
            showFlashError.value = false;
            usePage().props.value.flash.error = null
        }, 3000);
    }
    if(usePage().props.value.flash.message){

        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        inputValue.value = urlParams.get('search')
    }
    if(props.sides){
        sideTablePagination.value = props.sides.links;
        tableData.value = props.sides.data.map((el) => {
            return {
                id: el.id,
                value: getTranslatedEntity(el.value),
            }
        })
    }
    document.title = $trans('side.sides')
})
watch(() => props.sides,() => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        inputValue.value = urlParams.get('search')
    }
    if(props.sides){
        sideTablePagination.value = props.sides.links;
        tableData.value = props.sides.data.map((el) => {
            return {
                id: el.id,
                value: getTranslatedEntity(el.value),
            }
        })
    }
})
const onNewSideSubmit = () => {
    if(form.name == null){
        form.setError('name','Il campo nome è obbligatorio')
        return;
    }
    form.post(route('warehouse.sides.store'), {
        onFinish: () => {
            if(!form.hasErrors){
                form.reset('name')
                showNewSide.value = false;
            }
        },
    });
}
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if(urlParams.has('searchString')){
        Inertia.get(route('warehouse.sides.index'))
    }
}
const searchString = () => {
    inputValue.value == '' ?
        Inertia.get(route('warehouse.sides.index'))
        :
        Inertia.get(route('warehouse.sides.index'),{
            'search' : inputValue.value
        })
}
const goToNewSide = () => {
    Inertia.get(route('warehouse.sides.newIndex'))
}
const editSide = (row) => {
    Inertia.get(route('warehouse.sides.editIndex',{
        id : row.id
    }))
}
</script>

<style scoped lang="scss">

:deep(td.td-text-gray) {
    @apply text-gray-500 font-normal;
}

</style>
