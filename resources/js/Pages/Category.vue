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
                    <n-page-header :subtitle="__('category.handleCategory')" @back="handleBack">
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
                            <font-awesome-icon icon="fa-duotone fa-sitemap" size="2xl"></font-awesome-icon>
                        </template>
                    </n-page-header>
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
            <div v-if="props.tab == 'categories'">
                <n-alert type="default" v-show="checkedRowKeysRef.length > 0" vertical class="mb-3">
                    <template #icon>
                        <font-awesome-icon icon="fa-duotone fa-list-check"></font-awesome-icon>
                    </template>
                    {{ __('strings.bulkOperations') }}
                    <n-space justify="center" class="mt-3">
                        <n-button-group>
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
                            :placeholder="__('category.searchByCategory')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass"
                                               class="text-primaryColor"></font-awesome-icon>
                        </n-button>
                        <n-select id="filterActive" clearable @update:value="filterActive" v-model:value="value"
                                  :options="selectOptions"
                                  :placeholder="__('strings.activeFilterSelection')" type="select"/>
                    </div>
                    <div>
                        <n-button type="primary" v-if="props.permissions" @click="goToNewCategory"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('category.newCategory')
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
                    striped
                    :row-key="(row) => row.id"
                    @update:checked-row-keys="(key) => checkedRowKeysRef = key"
                />
                <pagination :resource="categories"/>
            </div>
            <NewEditCategoryComponent v-if="props.tab == 'categories/new'" :categories="props.categories"
            ></NewEditCategoryComponent>
            <NewEditCategoryComponent v-if="props.tab == 'categories/edit' " :categoryEdit="props.categoryEdit" :categories="props.categories"
            ></NewEditCategoryComponent>
        </n-card>
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
import NewEditMakeComponent from "@/Components/MMV/NewEditMakeComponent.vue";
import NewEditCategoryComponent from "@/Components/NewEditComponents/NewEditCategoryComponent.vue";

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
const routeInfo = computed(() => {
    let route = "";
    let breadcrumbs = [{'title' : $trans('menu.warehouse')},
        {'title': $trans('menu.categories')}
    ];
    switch (props.tab) {
        case 'categories':
            route = 'category.categories';
            break;
        case 'categories/new':
            route = 'category.newCategory';
            breadcrumbs.push({
                title : 'category.newCategory'
            })
            break;
        case 'categories/edit' :
            route = 'category.editCategory';
            breadcrumbs.push({
                title : 'category.editCategory'
            })
            break;
    }
    return {
        title: route,
        breadcrumbs : breadcrumbs,
    }
});

const value = ref(null);
const selMake = ref({});
const xRef = ref(0)
const yRef = ref(0)
const tableData = ref([]);
const dialog = useDialog();

const openDialogClick = () => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('category.confirmDeleteCategories'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteCategories()
        }
    })
}
const deleteAlert = (row) => {
    dialog.warning({
        title: $trans('strings.attention'),
        content: $trans('category.confirmDeleteCategoryCascade'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            onDeleteCategoryConfirm(row)
        }
    })
}
const selectOptions = [
    {
        label: $trans('strings.active'),
        value: 'true'
    },
    {
        label: $trans('strings.notActive'),
        value: 'false'
    }
]
const goToNewCategory = () => {
    Inertia.get(route('warehouse.categories.newIndex'));
}

const onClickoutside = () => {
    showDropdownRef.value = false
}

const columns = [
    {
        type: 'selection',
        disabled : () => !props.permissions
    },
    {
        title: $trans('category.name'),
        key: 'name',
        className: "td-text-gray",
        render(row){
            return h(
                'span',
                {
                },{
                    default : ()  => [
                    row.parent_is_active !== null ? h(
                        FontAwesomeIcon,
                        {
                            icon : 'fa-duotone fa-arrow-turn-down-right',
                            class: 'mr-2 text-gray-400'
                        }
                    ) : null,getTranslatedEntity(row.name)]
                }
            )
        }
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
                    type:  row.parent_is_active !== null && row.parent_is_active == 0 ? '' :  row.is_active ? 'success' : 'error'  ,
                    round: true,
                    bordered: false,
                    onClick: () => {
                        if(row.parent_is_active != 0)
                        return isActiveSwitch(row)
                    }
                },
                {
                    default: () => [h(
                        FontAwesomeIcon,
                        {

                            class : row.parent_is_active !== null && row.parent_is_active == 0 ? 'text-gray-400' : null,
                            icon: row.parent_is_active !== null && row.parent_is_active == 0 ? 'fa-duotone fa-minus' :  row.is_active ? "fa-duotone fa-check-circle" : "fa-duotone fa-times-circle"
                        }
                    ), h('span', {
                        style: {
                            marginLeft: '5px'
                        },
                        class : row.parent_is_active !== null && row.parent_is_active == 0 ? 'text-gray-400' : null
                    }, {
                        default: () => row.parent_is_active !== null && row.parent_is_active == 0 ? $trans('category.inheritedFromParent') :  row.is_active ? $trans('strings.active') : $trans('strings.notActive'),
                    })]
                }
            )

        }
    },
    {
        title: $trans('category.default_weight'),
        key: 'default_weight',
        className: "td-text-gray"
    },
    {
        key: 'action',
        align: 'right',
        render(row) {
            return props.permissions ?  h(
                'div',
                {},
                {
                    default: () => [

                        h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                onClick: () => editCategory(row),
                                disabled : !props.permissions
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
                                            default: () => $trans('category.confirmDeleteCategory')
                                        })
                                ],
                                activator: () => [
                                    h(
                                        NButton,
                                        {
                                            size: 'small',
                                            disabled : !props.permissions
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
    categories: Array,
    filters: Array,
    tab: String,
    categoryEdit: Object,
    permissions : Boolean,
})
const checkedRowKeysRef = ref([]);
const showEditCategory = ref(false);
const selCategory = ref({})
const inputValue = ref('')
const showFlashMessage = ref(false);
const categoryTablePagination = reactive({});
const newCats = ref([]);

const traverse =  (categories, is_active) => {
    categories.forEach ((category) => {
        category.default_weight = category.default_weight ? str_replace(category.default_weight) : '0,000';
        category.parent_is_active = is_active !== null ? is_active : null
        traverse(category.children, category.parent_is_active !== null && category.parent_is_active == 0 ? category.parent_is_active :  category.is_active);
        category.parent_id == null ?newCats.value.push(category) : null
    })

}

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
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (urlParams.has('active')) {
        value.value = urlParams.get('active')
    }
    if (props.categories && props.tab !== 'categories/new' && props.tab !== 'categories/edit') {
        newCats.value = []
        traverse(props.categories.data,null)

        categoryTablePagination.value = props.categories.links;
        tableData.value = newCats.value.map((el) => {

            return {
                id: el.id,
                name: el.name,
                depth : el.depth,
                parent_is_active : el.parent_is_active ,
                is_active: el.is_active,
                children : el.children,
                default_weight: el.default_weight ? str_replace(el.default_weight) : '0'
            }
        })
    }
    document.title = $trans('category.categories')
})
const isActiveSwitch = (category) => {
    Inertia.put(route('warehouse.categories.setActive', {
        id: category.id
    }), {
        id: category.id,
        is_active: !category.is_active,
    }, {
        onSuccess: () => {
            newCats.value = []
            traverse(props.categories.data,null)

            categoryTablePagination.value = props.categories.links;
            tableData.value = newCats.value.map((el) => {

                return {
                    id: el.id,
                    name: el.name,
                    depth : el.depth,
                    parent_is_active : el.parent_is_active ,
                    is_active: el.is_active,
                    children : el.children,
                    default_weight: el.default_weight ? str_replace(el.default_weight) : '0'
                }
            })
        }
    });
}
const onDeleteCategoryConfirm = (row) => {
    Inertia.delete(route('warehouse.categories.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            newCats.value = []
            traverse(props.categories.data,null)

            categoryTablePagination.value = props.categories.links;
            tableData.value = newCats.value.map((el) => {

                return {
                    id: el.id,
                    name: el.name,
                    depth : el.depth,
                    parent_is_active : el.parent_is_active ,
                    is_active: el.is_active,
                    children : el.children,
                    default_weight: el.default_weight ? str_replace(el.default_weight) : '0'
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
watch(() => props.categories, () => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (urlParams.has('active')) {
        value.value = urlParams.get('active')
    }
    if (props.categories) {
        newCats.value = []
        traverse(props.categories.data,null)

        categoryTablePagination.value = props.categories.links;
        tableData.value = newCats.value.map((el) => {
            return {
                id: el.id,
                name: el.name,
                depth : el.depth,
                parent_is_active : el.parent_is_active ,
                is_active: el.is_active,
                children : el.children,
                default_weight: el.default_weight ? str_replace(el.default_weight) : '0,000'
            }
        })
    }
})
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        if (urlParams.has('active')) {
            Inertia.get(route('warehouse.categories.index'), {
                'active': urlParams.get('active')
            })
        } else {
            Inertia.get(route('warehouse.categories.index'), {})
        }
    }

}
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if (urlParams.has('active')) {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.categories.index'), {
                'active': urlParams.get('active')
            })
            :
            Inertia.get(route('warehouse.categories.index'), {
                'search': inputValue.value,
                'active': urlParams.get('active')
            })
    } else {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.categories.index'), {})
            :
            Inertia.get(route('warehouse.categories.index'), {
                'search': inputValue.value,
            })
    }
}
const str_replace = (value) => {
    if (value === null) {
        return '0,000';
    } else if (typeof value === 'number') {
        return value.toFixed(3).replace('.', ',');
    } else {
        return value == 0 ? '0,000' : value;
    }

}
const filterActive = (active) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if (active !== null) {
        if (active == 'true') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.categories.index'), {
                    'search': urlParams.get('search'),
                    'active': true,
                })
            } else {
                Inertia.get(route('warehouse.categories.index'), {
                    'active': true,
                })
            }
        } else if (active == 'false') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.categories.index'), {
                    'search': urlParams.get('search'),
                    'active': false,
                })
            } else {
                Inertia.get(route('warehouse.categories.index'), {
                    'active': false,
                })
            }
        }
    } else {
        if (urlParams.has('search')) {
            Inertia.get(route('warehouse.categories.index'), {
                'search': urlParams.get('search')
            })
        } else {
            Inertia.get(route('warehouse.categories.index'))
        }
    }
}
const editCategory = (row) => {
    Inertia.get(route('warehouse.categories.editIndex', {
        id: row.id
    }))
}
const isActiveSwitchBulk = (active) => {
    Inertia.put(route('warehouse.categories.setActiveBulk', {
        ids: checkedRowKeysRef.value,
    }), {
        ids: checkedRowKeysRef.value,
        is_active: active == 'true' ? true : false
    }, {
        onSuccess: () => {
            newCats.value = []
            traverse(props.categories.data,null)

            categoryTablePagination.value = props.categories.links;
            tableData.value = newCats.value.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    depth : el.depth,
                    parent_is_active : el.parent_is_active ,
                    is_active: el.is_active,
                    children : el.children,
                    default_weight: el.default_weight ? str_replace(el.default_weight) : '0'
                }
            })
        }
    })
}
const deleteCategories = () => {
    Inertia.delete(route('warehouse.categories.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess: () => {
            newCats.value = []
            traverse(props.categories.data,null)

            categoryTablePagination.value = props.categories.links;
            tableData.value = newCats.value.map((el) => {
                return {
                    id: el.id,
                    name: el.name,
                    depth : el.depth,
                    parent_is_active : el.parent_is_active ,
                    is_active: el.is_active,
                    children : el.children,
                    default_weight: el.default_weight ? str_replace(el.default_weight) : '0'
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
