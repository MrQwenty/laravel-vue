<template>
    <Authenticated>
        <template #header>
            <n-space vertical class="w-full ">
<!--                <n-alert type="success" closable class="mb-4"
                         v-show="usePage().props.value.flash.message && showFlashMessage">
                    {{ usePage().props.value.flash.message }}
                </n-alert>
                <n-alert type="error" closable class="mb-4"
                         v-show="usePage().props.value.flash.error && showFlashError">
                    {{ usePage().props.value.flash.error }}
                </n-alert>-->
                <n-card class="!border-t-[3px] !border-t-primaryColor">
                    <n-page-header :subtitle="__('product.handleProduct')" @back="handleBack">
                        <template #title>
                            <a>
                                Prodotti
                            </a>
                        </template>
                        <template #header>
                            <n-breadcrumb>
<!--                                <n-breadcrumb-item v-for="b of routeInfo.breadcrumbs">{{ __(b.title) }}</n-breadcrumb-item>-->
                                <!--                                <n-breadcrumb-item>Versioni</n-breadcrumb-item>-->
                                <n-breadcrumb-item>Magazzino</n-breadcrumb-item>
                                <n-breadcrumb-item>Prodotti</n-breadcrumb-item>
                            </n-breadcrumb>
                        </template>
                        <template #avatar>
                            <font-awesome-icon icon="fa-duotone fa-tag" size="2xl"></font-awesome-icon>
                        </template>
                    </n-page-header>
                </n-card>

<!--                <n-alert v-show="props.filters" type="info" class="mt-3 "
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
                </n-alert>-->
            </n-space>
        </template>
        <n-card class="!border-t-[3px] !border-t-primaryColor" v-if="props.tab == 'product'">
            <div  >
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
                            :placeholder="__('product.searchByProduct')" clearable @clear="clearInput" v-model:value="inputValue"/>

                        <n-button
                            secondary
                            class="mx-3 rounded-lg text-white outline-none flex items-center gap-2 "
                            @click="searchString">
                            <font-awesome-icon icon="fa-duotone fa-magnifying-glass"
                                               class="text-primaryColor"></font-awesome-icon>
                        </n-button>
                    </div>
                    <div>
                        <n-button type="primary"  @click="goToNewProduct"
                        >
                            <font-awesome-icon class="text-md"
                                               :icon="'fa-duotone fa-plus'"></font-awesome-icon>&nbsp;{{
                                __('product.newProduct')
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
                    :row-key="(row) => row.sku"
                    @update:checked-row-keys="(key) => checkedRowKeysRef = key"
                />
<!--                <pagination :resource="products"/>-->
            </div>
        </n-card>

        <NewEditProductComponent v-if="props.tab == 'product/new'" :imgBaseUrl="props.imgBaseUrl" :conditions="props.conditions" :models="props.models" :makes="props.makes" :versions="props.versions" :categories="props.categories " :sides="props.sides" :replacements="props.replacements"
        ></NewEditProductComponent>
        <NewEditProductComponent v-if="props.tab == 'product/edit' " :imgBaseUrl="props.imgBaseUrl" :conditions="props.conditions" :models="props.models" :makes="props.makes" :versions="props.versions" :categories="props.categories " :sides="props.sides" :replacements="props.replacements" :products="props.productEdit"
        ></NewEditProductComponent>
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
import NewEditProductComponent from "@/Components/NewEditComponents/NewEditProductComponent.vue";
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
        {'title': $trans('menu.products')}
    ];
    switch (props.tab) {
        case 'product':
            route = 'product.products';
            break;
        case 'product/new':
            route = 'product.newProduct';
            breadcrumbs.push({
                title : 'product.newProduct'
            })
            break;
        case 'product/edit' :
            route = 'product.editProduct';
            breadcrumbs.push({
                title : 'product.editProduct'
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
        content: $trans('product.confirmDeleteProducts'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            deleteProducts()
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
const goToNewProduct = () => {
    Inertia.get(route('warehouse.products.newIndex'));
}
const columns = [
    {
        type: 'selection',
    },
    {
        title: "SKU",
        key: 'sku',
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
        title: $trans('product.replacement'),
        key: 'replacement',
        className: "td-text-gray"
    },
    {
        title: $trans('product.notes'),
        key: 'notes',
        className: "td-text-gray"
    },
    {
        title: $trans('replacement.price'),
        key: 'price',
        className: "td-text-gray"
    },
    {
        key: 'action',
        align: 'right',
        render(row) {
            return props.permissions ?   h(
                'div',
                {},
                {
                    default: () => [
                        h(
                            NButton,
                            {
                                size: 'small',
                                style: 'marginRight : 10px',
                                onClick: () => editProduct(row),
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
                                onPositiveClick: () => onDeleteProductConfirm(row),
                            }, {
                                default: () => [
                                    h(
                                        'div',
                                        {}, {
                                            default: () => $trans('product.confirmDeleteProduct')
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
    products: Array,
    tab : String,
    paths : Array|Object,
    filters : Array,
    imgBaseUrl : String,
    permissions : Boolean,
    replacements : Array,
    productEdit : Object,
    makes : Array,
    models : Array,
    versions : Array,
    categories : Array,
    sides : Array,
    conditions : Array,

})
const checkedRowKeysRef = ref([]);
const inputValue = ref('')
const showFlashMessage = ref(false);
const productTablePagination = reactive({});
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
    if (props.products) {
        productTablePagination.value = props.products.links;
        tableData.value = props.products.data.map((el) => {
            return {
                sku: el.sku,
                replacement : getTranslatedEntity(el.replacement.description),
                notes : getTranslatedEntity(el.notes),
                photo : el.photos.length > 0 ? el.photos[0] : el.replacement.photos[0],
                condition_id : el.condition_id,
                price : formatCurrencyEUR(el.price)
            }
        })
    }
    document.title = $trans('product.products')
})
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
const onDeleteProductConfirm = (row) => {
    return
    Inertia.delete(route('warehouse.products.destroy', {
        product: row.sku
    }), {
        data: {
            id: row.id
        },
        onFinish: () => {
            productTablePagination.value = props.products.links;
            tableData.value = props.products.data.map((el) => {
                return {
                    sku: el.sku,
                    replacement : getTranslatedEntity(el.replacement.description),
                    notes : getTranslatedEntity(el.notes),
                    photo : el.photos.length > 0 ? el.photos[0] : el.replacement.photos[0],
                    condition_id : el.condition_id,
                    price : formatCurrencyEUR(el.price)
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
watch(() => props.products, () => {
    return
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);
    if (urlParams.has('search')) {
        inputValue.value = urlParams.get('search')
    }
    if (props.products) {
        productTablePagination.value = props.products.links;
        tableData.value = props.products.data.map((el) => {
            return {
                sku: el.sku,
                replacement : getTranslatedEntity(el.replacement.description),
                notes : getTranslatedEntity(el.notes),
                photo : el.photos.length > 0 ? el.photos[0] : el.replacement.photos[0],
                condition_id : el.condition_id,
                price : formatCurrencyEUR(el.price)
            }
        })
    }
})
const clearInput = () => {
    return
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if(urlParams.has('search')){
        if(urlParams.has('replacement')){

            Inertia.get(route('warehouse.products.index'), {
                'replacement' : urlParams.get('replacement')})
        }else{

            Inertia.get(route('warehouse.products.index'), {})
        }
    }
}
const searchString = () => {
    return;
    let queryString = window.location.search;
    let urlParams = new URLSearchParams(queryString);

    if(urlParams.has('replacement')){

        inputValue.value == '' ?
            Inertia.get(route('warehouse.products.index'), {
                'replacement' : urlParams.get('replacement')
            })
            :
            Inertia.get(route('warehouse.products.index'), {
                'search': inputValue.value,
                'replacement' : urlParams.get('replacement')
            })
    }else{

        inputValue.value == '' ?
            Inertia.get(route('warehouse.products.index'), {})
            :
            Inertia.get(route('warehouse.products.index'), {
                'search': inputValue.value,
            })
    }

}
const str_replace = (str) => {
    if (str) {
        str = str.toString();
        return str.replace('.', ',')
    }

}
const filterActive = (active) => {
    return;
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
const editProduct = (row) => {
    Inertia.get(route('warehouse.products.editIndex', {
        sku: row.sku
    }))
}
const deleteProducts = () => {
    return;
    Inertia.delete(route('warehouse.products.destroyBulk', {
        skus: checkedRowKeysRef.value
    }), {
        data: {
            skus: checkedRowKeysRef.value
        },
        onSuccess: () => {
            productTablePagination.value = props.products.links;
            tableData.value = props.products.data.map((el) => {
                return {
                    sku: el.sku,
                    replacement : getTranslatedEntity(el.replacement.description),
                    notes : getTranslatedEntity(el.notes),
                    photo : el.photos.length > 0 ? el.photos[0] : el.replacement.photos[0],
                    condition_id : el.condition_id,
                    price : formatCurrencyEUR(el.price)
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
