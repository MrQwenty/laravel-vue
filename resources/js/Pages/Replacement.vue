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
                    <n-page-header :subtitle="__('replacement.handleReplacement')" @back="handleBack">
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
                            <font-awesome-icon icon="fa-duotone fa-engine" size="2xl"></font-awesome-icon>
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
        <n-card class="!border-t-[3px] !border-t-primaryColor" v-if="props.tab == 'replacement'">
            <div >
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
                            :placeholder="__('replacement.searchByReplacement')" @clear="clearInput" clearable v-model:value="inputValue"/>

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
                        <n-button type="primary"  v-if="props.permissions" @click="goToNewReplacement"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('replacement.newReplacement')
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
                <pagination :resource="replacements"/>
            </div>
        </n-card>

            <NewEditReplacementComponent v-if="props.tab == 'replacement/new'" :attributes="props.attributes" :versions="props.versions" :models="props.models" :makes="props.makes" :sides="props.sides" :categories="props.categories"
            ></NewEditReplacementComponent>
            <NewEditReplacementComponent v-if="props.tab == 'replacement/edit' " :attributes="props.attributes" :paths="props.paths" :replacements="props.replacementEdit" :versions="props.versions" :models="props.models" :makes="props.makes" :sides="props.sides" :categories="props.categories"
            ></NewEditReplacementComponent>
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
import {NButton, NPopconfirm, NTag, useDialog, NDialogProvider, NImage} from "naive-ui";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import NewEditReplacementComponent from "@/Components/NewEditComponents/NewEditReplacementComponent.vue";
watch(() => usePage().props.value.flash.message, () => {
        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
)
const clearInput = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if(urlParams.has('search')){
        if (urlParams.has('active')) {
            Inertia.get(route('warehouse.replacements.index'), {
                'active': urlParams.get('active')
            })
        } else {
            Inertia.get(route('warehouse.replacements.index'), {})
        }
    }
}
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
        {'title': $trans('menu.replacements')}
    ];
    switch (props.tab) {
        case 'replacement':
            route = 'replacement.replacements';
            break;
        case 'replacement/new':
            route = 'replacement.newReplacement';
            breadcrumbs.push({
                title : 'replacement.newReplacement'
            })
            break;
        case 'replacement/edit' :
            route = 'replacement.editReplacement';
            breadcrumbs.push({
                title : 'replacement.editReplacement'
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
        content: $trans('replacement.confirmDeleteReplacements'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteReplacements()
        }
    })
}
const deleteAlert = (row) => {
    dialog.error({
        title: $trans('strings.attention'),
        content: $trans('replacement.confirmDeleteReplacementCascade'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            onDeleteReplacementConfirm(row)
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

const goToNewReplacement = () => {
    Inertia.get(route('warehouse.replacements.newIndex'));
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
        key: 'logo',
        className: "td-text-gray",
        align: "center",
        render(row) {
            return h(
                NImage,
                {
                    src: props.imgBaseUrl + row.photo.path,
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
        title: $trans('replacement.reference'),
        key: 'reference',
        className: "td-text-gray"
    },
    {
        title: $trans('replacement.description'),
        key: 'description',
        className: "td-text-gray"
    },
    {
        title: $trans('replacement.price'),
        key: 'price',
        className: "td-text-gray"
    },
    {
        title : $trans('product.products'),
        key : 'products',
        render(row) {
            return h(
                'div',
                {
                    class: 'justify-center items-center relative'
                }, {
                    default: () => [
                        row.products ?? '0',
                        h(
                            FontAwesomeIcon,
                            {
                                icon: 'fa-duotone fa-arrow-up-right-from-square',
                                //class="absolute top-[2px] ml-3 text-md opacity-[0.7]" :class="linkedVersions(model.id) == 0 ? 'opacity-[0]' : null"
                                class: row.products == 0 ? 'absolute top-[2px] ml-3 text-md opacity-[0]' : 'absolute top-[2px] ml-3 text-md opacity-[0.7]',
                                onClick: () => {
                                    filterProducts(row.id)
                                }
                            }
                        )
                    ]
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
                    type: row.is_active ? 'success' : 'error',
                    round: true,
                    bordered: false,
                    onClick: () => {
                        if(props.permissions)
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
                                onClick: () => editReplacement(row),
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
                                            default: () => $trans('replacement.confirmDeleteReplacement')
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
                        )
                        ,]
                }
            ) : null
        }
    }

]
const props = defineProps({
    replacements: Array,
    replacementEdit : Object,
    makes: Array,
    models: Array,
    versions: Array,
    categories: Array,
    sides: Array,
    tab : String,
    paths : Array|Object,
    filters : Array,
    attributes : Array,
    imgBaseUrl : String,
    permissions : Boolean
})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const showFlashMessage = ref(false);
const replacementsTablePagination = reactive({});
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
    if (props.replacements) {
        replacementsTablePagination.value = props.replacements.links;
        tableData.value = props.replacements.data.map((el) => {
            return {
                id: el.id,
                reference : el.reference,
                description : getTranslatedEntity(el.description),
                price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                is_active : el.is_active,
                photo : el.photos[0],
                products : getProdCount(el)
            }
        })
    }
    document.title = $trans('replacement.replacements')
})
const getProdCount = (rep) => {
    return rep.products.length
}
const filterProducts = (rep) => {
    Inertia.get(route('warehouse.products.index'), {
        'replacement': rep
    })
}
function formatCurrencyEUR(number) {
    if(typeof number == 'string')number = number.replace(',','.')
    // Convert number to a string with two decimal places
    const formattedNumber = Number(number).toFixed(2);

    // Split the formatted number into integer and decimal parts
    if(isNaN(formattedNumber)){
        return null
    }
    const parts = formattedNumber.split('.');
    // Format the integer part by adding '.' every 3 digits
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    // console.log(integerPart,parts)
    // Construct the formatted currency string with ',' as decimal separator and '€' symbol
    const currencyString = `${integerPart},${parts[1]}€`;

    return currencyString;
}
const isActiveSwitch = (row) => {
    Inertia.put(route('warehouse.replacements.setActive', {
        id: row.id
    }), {
        id: row.id,
        is_active: !row.is_active,
    }, {
        onSuccess: () => {
            replacementsTablePagination.value = props.replacements.links;
            tableData.value = props.replacements.data.map((el) => {
                return {
                    id: el.id,
                    reference : el.reference,
                    description : getTranslatedEntity(el.description),
                    price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                    is_active : el.is_active,
                    photo : el.photos[0]
                }
            })
        }
    });
}
const onDeleteReplacementConfirm = (row) => {
    Inertia.delete(route('warehouse.replacements.destroy', {
        id: row.id
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            replacementsTablePagination.value = props.replacements.links;
            tableData.value = props.replacements.data.map((el) => {
                return {
                    id: el.id,
                    reference : el.reference,
                    description : getTranslatedEntity(el.description),
                    price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                    is_active : el.is_active,
                    photo : el.photos[0]
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
watch(() => props.replacements, () => {

    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (urlParams.has('active')) {
        value.value = urlParams.get('active')
    }
    if (props.replacements) {
        replacementsTablePagination.value = props.replacements.links;
        tableData.value = props.replacements.data.map((el) => {
            return {
                id: el.id,
                reference : el.reference,
                description : getTranslatedEntity(el.description),
                price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                is_active : el.is_active,
                photo : el.photos[0]
            }
        })
    }
})
const searchString = () => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if (urlParams.has('active')) {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.replacements.index'), {
                'active': urlParams.get('active')
            })
            :
            Inertia.get(route('warehouse.replacements.index'), {
                'search': inputValue.value,
                'active': urlParams.get('active')
            })
    } else {
        inputValue.value == '' ?
            Inertia.get(route('warehouse.replacements.index'), {})
            :
            Inertia.get(route('warehouse.replacements.index'), {
                'search': inputValue.value,
            })
    }
}
const filterActive = (active) => {
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if (active !== null) {
        if (active == 'true') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.replacements.index'), {
                    'search': urlParams.get('search'),
                    'active': true,
                })
            } else {
                Inertia.get(route('warehouse.replacements.index'), {
                    'active': true,
                })
            }
        } else if (active == 'false') {
            if (urlParams.has('search')) {
                Inertia.get(route('warehouse.replacements.index'), {
                    'search': urlParams.get('search'),
                    'active': false,
                })
            } else {
                Inertia.get(route('warehouse.replacements.index'), {
                    'active': false,
                })
            }
        }
    } else {
        if (urlParams.has('search')) {
            Inertia.get(route('warehouse.replacements.index'), {
                'search': urlParams.get('search')
            })
        } else {
            Inertia.get(route('warehouse.replacements.index'))
        }
    }
}
const editReplacement = (row) => {
    Inertia.get(route('warehouse.replacements.editIndex', {
        id: row.id
    }))
}
const isActiveSwitchBulk = (active) => {
    Inertia.put(route('warehouse.replacements.setActiveBulk', {
        ids: checkedRowKeysRef.value,
    }), {
        ids: checkedRowKeysRef.value,
        is_active: active == 'true' ? true : false
    }, {
        onSuccess: () => {
            replacementsTablePagination.value = props.replacements.links;
            tableData.value = props.replacements.data.map((el) => {
                return {
                    id: el.id,
                    reference : el.reference,
                    description : getTranslatedEntity(el.description),
                    price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                    is_active : el.is_active,
                    photo : el.photos[0]
                }
            })
        }
    })
}
const deleteReplacements = () => {
    Inertia.delete(route('warehouse.replacements.destroyBulk', {
        ids: checkedRowKeysRef.value
    }), {
        data: {
            ids: checkedRowKeysRef.value
        },
        onSuccess: () => {
            replacementsTablePagination.value = props.replacements.links;
            tableData.value = props.replacements.data.map((el) => {
                return {
                    id: el.id,
                    reference : el.reference,
                    description : getTranslatedEntity(el.description),
                    price : el.price != null ? formatCurrencyEUR(el.price) : '0€',
                    is_active : el.is_active,
                    photo : el.photos[0]
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
