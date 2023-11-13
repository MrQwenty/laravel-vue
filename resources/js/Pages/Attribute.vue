<template>
    <Authenticated>
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
                    <div class="flex flex-row justify-between">
                        <n-page-header :subtitle="__('attribute.handleAttribute')" @back="handleBack">
                            <template #title>
                                <a>
                                    {{ __(routeInfo.title) }}
                                </a>
                            </template>
                            <template #header>
                                <n-breadcrumb>
                                    <n-breadcrumb-item v-for="b of routeInfo.breadcrumbs">{{ __(b.title) }}</n-breadcrumb-item>
                                    <!--                                <n-breadcrumb-item>Versioni</n-breadcrumb-item>-->
                                </n-breadcrumb>
                            </template>
                            <template #avatar>
                                <font-awesome-icon :icon="props.tab == 'attribute/option' ? 'fa-duotone fa-list-dropdown' : 'fa-duotone fa-list-tree'" size="2xl"></font-awesome-icon>
                            </template>
                        </n-page-header><n-button v-if="props.tab == 'attribute/option'" type="error"  @click="goBack"
                    >
                        <font-awesome-icon class="text-md"
                                           :icon="'fa-duotone fa-arrow-left'"></font-awesome-icon>&nbsp;{{
                            __('strings.back')
                        }}
                    </n-button></div>
                </n-card>

                <n-alert v-show="props.filters" type="info" class="mt-3 "
                >
                    <span class="text-[#767c82]">
                        {{ __('strings.filteringFor') }}
                    </span>
                    <ul class="list-disc ">
                        <li v-for="filter in props.filters">
                            <div v-if="filter.value !== null">
                                <span class="text-[#767c82]">{{ filter.key }} -</span> <span v-if="filter.key == __('strings.typeFilter')"
                                class="font-medium">{{ filter.value }}</span><span v-else
                                                                                   class="font-medium">"{{ filter.value }}"</span>
                            </div>
                        </li>
                    </ul>
                </n-alert>
            </n-space>
        </template>
        <n-card class="!border-t-[3px] !border-t-primaryColor" v-if="props.tab == 'attributes'">
            <div>
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
                    <div class="flex w-full gap-2 mb-3">
                        <n-input
                            :placeholder="__('attribute.searchByAttribute')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass"
                                               class="text-primaryColor"></font-awesome-icon>
                        </n-button>
                        <n-select multiple id="filterActive" clearable @update:value="filterType" v-model:value="typeValue"
                                  :options="typeOptions"
                                  :placeholder="__('attribute.typeFilterSelection')" />
                        <n-select id="filterActive" clearable @update:value="filterShown" v-model:value="shownValue"
                                  :options="selectOptions"
                                  :placeholder="__('attribute.visibleFilterSelection')" type="select"/>
                    </div>
                    <div>
                        <n-button type="primary" v-if="props.permissions"  @click="goToNewAttribute"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('attribute.newAttribute')
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
                <pagination :resource="attributes"/>
            </div>
        </n-card>

        <NewEditAttributeComponent v-if="props.tab == 'attribute/new'" :categories="props.categories" :types="props.types"
        ></NewEditAttributeComponent>
        <NewEditAttributeComponent v-if="props.tab == 'attribute/edit' " :categories="props.categories" :types="props.types" :attributes="props.attributeEdit"
        ></NewEditAttributeComponent>
        <AttributeOptionComponent v-if="props.tab == 'attribute/option'" :permissions="props.permissions" :attributeOptions="props.attributeOptions" :attribute="props.attributeEdit"></AttributeOptionComponent>
    </Authenticated>
</template>
<script setup>
import Authenticated from '@/Layouts/Authenticated.vue';
import Pagination from "@/Components/Pagination.vue";
import {VueFinalModal} from 'vue-final-modal';
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import InputCustom from '@/Components/Input.vue';
import {computed, onMounted, reactive, ref, watch,h} from 'vue';
import {Inertia} from "@inertiajs/inertia";
import {NButton, NPopconfirm, NTag, useDialog,NDialogProvider} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import NewEditAttributeComponent from "@/Components/NewEditComponents/NewEditAttributeComponent.vue";
import AttributeOptionComponent from "@/Components/AttributeOptionComponent.vue";
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
const showErrorValue = ref(false)
const shownValue = ref(null)
const typeOptions = ref([]);
const showFlashError = ref(true);
const routeInfo = computed(() => {
    let route = "";
    let breadcrumbs = [{'title' : $trans('menu.warehouse')},
        {'title': $trans('menu.attributes')}
    ];
    switch (props.tab) {
        case 'attributes':
            route = 'attribute.attributes';
            break;
        case 'attribute/new':
            route = 'attribute.newAttribute';
            breadcrumbs.push({
                title : 'attribute.newAttribute'
            })
            break;
        case 'attribute/edit' :
            route = 'attribute.editAttribute';
            breadcrumbs.push({
                title : 'attribute.editAttribute'
            })
            break;
        case 'attribute/option' :
            route = 'attribute.attributeOptions';
            breadcrumbs.push({
                title : 'attribute.attributeOptions'
            })
    }
    return {
        title: route,
        breadcrumbs : breadcrumbs,
    }
});

const typeValue = ref([]);
const xRef = ref(0)
const yRef = ref(0)
const tableData = ref([]);
const dialog = useDialog();

const openDialogClick = () => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('attribute.confirmDeleteAttributes'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteAttributes()
        }
    })
}
const deleteAttributes = () => {
    Inertia.delete(route('warehouse.attributes.destroyBulk',{
        ids : checkedRowKeysRef.value
    }),{
        onFinish : () => {

            attributesTablePagination.value = props.attributes.links;
            tableData.value = props.attributes.data.map((el) => {
                return {
                    id: el.id,
                    name : getTranslatedEntity(el.name),
                    type_name : el.attribute_type ? el.attribute_type.name : null,
                    type_id : el.attribute_type ? el.attribute_type.id : null,
                    is_ecommerce_shown : el.is_ecommerce_shown ,
                }
            })
        }
    })
}
const selectOptions = [
    , {
        label: $trans('strings.visible'),
        value: 'true'
    },
    {
        label: $trans('strings.hidden'),
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

const goToNewAttribute = () => {
    Inertia.get(route('warehouse.attributes.newIndex'));
}

const onClickoutside = () => {
    showDropdownRef.value = false
}


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
        title: $trans('attribute.name'),
        key: 'name',
        className: "td-text-gray"
    },
    {
        title: $trans('attribute.type_id'),
        key: 'type_name',
        className: "td-text-gray"
    },
    {
        title: $trans('attribute.visibleEcommerce'),
        key: 'is_ecommerce_shown',
        align : 'center',
        className: "td-text-gray",
        render(row){
            return h(
                FontAwesomeIcon,
                {
                    icon : row.is_ecommerce_shown ? 'fa-duotone fa-eye' : 'fa-duotone fa-eye-slash',
                    class : row.is_ecommerce_shown ? 'text-sky-500' : 'text-red-500'
                }
            )
        }
    },
    {
        key: 'action',
        align: 'right',
        render(row) {
            return   h(
                'div',
                {},
                {
                    default: () => [
                        row.type_id == 2 ?
                            h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                onClick: () => attributeOptions(row.id),
                            }, {
                                default: () => [h(
                                    FontAwesomeIcon, {
                                        icon: 'fa-duotone fa-list',
                                        class: 'text-green-400'
                                    }
                                )]
                            }
                        ) : null,
                        props.permissions ? h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                onClick: () => editAttribute(row),
                            }, {
                                default: () => [h(
                                    FontAwesomeIcon, {
                                        icon: 'fa-duotone fa-edit',
                                        class: 'text-indigo-500'
                                    }
                                )]
                            }
                        ) : null,
                        props.permissions ? h(
                            NPopconfirm,
                            {
                                negativeText: $trans('strings.cancel'),
                                positiveText: $trans('strings.confirm'),
                                onPositiveClick: () => onDeleteAttributeConfirm(row),
                            }, {
                                default: () => [
                                    h(
                                        'div',
                                        {}, {
                                            default: () => $trans('attribute.confirmDeleteAttribute')
                                        })
                                ],
                                activator: () => [
                                    h(
                                        NButton,
                                        {
                                            size: 'small',
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
                        ) : null
                    ,]
                }
            )
        }
    }

]
const props = defineProps({
    attributes: Array,
    attributeEdit : Object,
    categories : Array,
    types : Array,
    tab : String,
    filters : Array,
    attributeOptions : Array,
    permissions : Boolean,
})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const showFlashMessage = ref(false);
const attributesTablePagination = reactive({});
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
    if(props.types){

        props.types.forEach((type) => {
            typeOptions.value.push(
                {
                    label : type.name,
                    value : type.id
                }
            )}
        )
    }
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if(urlParams.has('types')){
        typeValue.value = urlParams.get('types').split(',')
    }
    if(urlParams.has('shown')){
        shownValue.value = urlParams.get('shown')
    }
    if (props.attributes) {
        attributesTablePagination.value = props.attributes.links;
        tableData.value = props.attributes.data.map((el) => {
            return {
                id: el.id,
                name : getTranslatedEntity(el.name),
                type_name : el.attribute_type ? el.attribute_type.name : null,
                type_id : el.attribute_type ? el.attribute_type.id : null,
                is_ecommerce_shown : el.is_ecommerce_shown ,
            }
        })
    }
    document.title = $trans('attribute.attributes')
})
const onDeleteAttributeConfirm = (row) => {
    Inertia.delete(route('warehouse.attributes.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            attributesTablePagination.value = props.attributes.links;
            tableData.value = props.attributes.data.map((el) => {
                return {
                    id: el.id,
                    name : getTranslatedEntity(el.name),
                    type_name : el.attribute_type ? el.attribute_type.name : null,
                    type_id : el.attribute_type ? el.attribute_type.id : null,
                    is_ecommerce_shown : el.is_ecommerce_shown ,
                }
            })
        }
    })
}
watch(() => usePage().props.value.flash.message, () => {
        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
)
watch(() => props.attributes, () => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if(urlParams.has('types')){
        typeValue.value = urlParams.get('types').split(',')
    }
    if(urlParams.has('shown')){
        shownValue.value = urlParams.get('shown')
    }
    if (props.attributes) {
        attributesTablePagination.value = props.attributes.links;
        tableData.value = props.attributes.data.map((el) => {
            return {
                id: el.id,
                name : getTranslatedEntity(el.name),
                type_name : el.attribute_type ? el.attribute_type.name : null,
                type_id : el.attribute_type ? el.attribute_type.id : null,
                is_ecommerce_shown : el.is_ecommerce_shown ,
            }
        })
    }
})
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        if(urlParams.has('shown')){
            if (urlParams.has('types')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types'),
                    'shown' : urlParams.get('shown')
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'), {
                    'shown' : urlParams.get('shown')})
            }
        }else{

            if (urlParams.has('types')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types')
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'), {})
            }
        }
    }

}
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('shown')){

        if (urlParams.has('types')) {
            inputValue.value == '' ?
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types'),
                    'shown' : urlParams.get('shown')
                })
                :
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': inputValue.value,
                    'types': urlParams.get('types'),
                    'shown' : urlParams.get('shown')
                })
        } else {
            inputValue.value == '' ?
                Inertia.get(route('warehouse.attributes.index'), {
                    'shown' : urlParams.get('shown')})
                :
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': inputValue.value,
                    'shown' : urlParams.get('shown')
                })
        }
    }else{

        if (urlParams.has('types')) {
            inputValue.value == '' ?
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types')
                })
                :
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': inputValue.value,
                    'types': urlParams.get('types')
                })
        } else {
            inputValue.value == '' ?
                Inertia.get(route('warehouse.attributes.index'), {})
                :
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': inputValue.value,
                })
        }
    }
}
const filterType = (active) => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('shown')){
        if (active !== null && Object.keys(active).length > 0) {
            active = active.join(',');
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'types': active,
                    'shown' : urlParams.get('shown')
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': active,
                    'shown' : urlParams.get('shown')
                })
            }
        } else {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'shown' : urlParams.get('shown')
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'),{
                    'shown' : urlParams.get('shown')
                })
            }
        }
    }else{
        if (active !== null && Object.keys(active).length > 0) {
            active = active.join(',');
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'types': active,
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': active,
                })
            }
        } else {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search')
                })
            } else {
                Inertia.get(route('warehouse.attributes.index'))
            }
        }
    }
}
const filterShown = (shown) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (shown !== null) {
        shown = shown == 'true'
        if (urlParams.has('search')) {
            if(urlParams.has('types')){
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'types': urlParams.get('types'),
                    'shown' : shown
                })
            }else{
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'shown' : shown
                })
            }
        } else {
            if(urlParams.has('types')){
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types'),
                    'shown' : shown
                })
            }else{
                Inertia.get(route('warehouse.attributes.index'), {
                    'shown' : shown
                })
            }
        }
    } else {
        if (urlParams.has('search')) {
            if(urlParams.has('types')){
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search'),
                    'types': urlParams.get('types'),
                })
            }else{
                Inertia.get(route('warehouse.attributes.index'), {
                    'search': urlParams.get('search')
                })
            }
        } else {
            if(urlParams.has('types')){
                Inertia.get(route('warehouse.attributes.index'), {
                    'types': urlParams.get('types'),
                })
            }else{
                Inertia.get(route('warehouse.attributes.index'))
            }
        }
    }
}
const editAttribute = (row) => {
    Inertia.get(route('warehouse.attributes.editIndex', {
        id: row.id
    }))
}
const deleteAttribute = () => {
    Inertia.delete(route('warehouse.attributes.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess: () => {
            attributesTablePagination.value = props.attributes.links;
            tableData.value = props.attributes.data.map((el) => {
                return {
                    id: el.id,
                    name : getTranslatedEntity(el.name),
                    type_name : el.attribute_type ? el.attribute_type.name : null,
                    type_id : el.attribute_type ? el.attribute_type.id : null,
                    is_ecommerce_shown : el.is_ecommerce_shown ,
                }
            })
        }
    })
}
const attributeOptions = (type) => {
    Inertia.get(route('warehouse.attributes.options',{
        id : type
    }))
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
