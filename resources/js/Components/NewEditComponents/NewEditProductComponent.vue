<template>
    <div class="grid lg:grid-cols-7 gap-5">
        <div :class="form.replacement ? 'col-span-5' : 'col-span-7'">
            <n-card class="!border-t-[3px] !border-t-primaryColor mb-5">
                <span class="text-xl">{{ __('product.productInfo') }}</span>
                <n-tabs type="line" animated @update:value="tabValueUpdate" :value="tabValue" >
                    <n-tab-pane name="repInfo"  :tab="__('product.productDetails')">
                        <div>
                            <div>
                                <form @submit.prevent="onNewProductSubmit">
                                    <InputCustom :filterable="true" :customUpdateValue="replacementValueUpdate" :label="__('product.selectReplacement')" required :error="form.errors.replacement" v-model="form.replacement" :optionsSelect="replacementsSelect" type="select"></InputCustom>
                                    <div v-if="form.replacement != null">

                                        <div class="grid lg:grid-cols-3 gap-x-3 flex">
                                            <InputCustom  :label="__('product.sku')" v-model="form.sku" required :error="form.errors.sku" ></InputCustom>
                                            <InputCustom :error="form.errors.condition" type="select" :label="__('product.condition')" v-model="form.condition" :optionsSelect="conditionSelect" required></InputCustom>


                                            <div class="flex flex-grow flex-col">
                                                <InputCustom :customUpdateValue="updateValueCurrency"  @focus="focusInput" @blur="blurInput" :suffix="true"
                                                             type="currency" v-model="form.price" :label="__('replacement.price')"
                                                             :error="form.errors.price"></InputCustom>
                                            </div>
                                        </div>
                                            <InputCustom type="textarea" :customStyle="{height : '80px'}" :translate="true" :label="__('product.notes')" v-model="form.notes" ></InputCustom>
                                        <div v-if="!edit" class="mb-2">
                                            <InputCustom type="upload" :hint="__('product.wallpaperImage')" :label="__('strings.image')"
                                                         id="logo"
                                                         name="logo"
                                                         :error="form.errors.images" >
                                                <n-upload
                                                    accept="image/*"
                                                    :default-upload="false"
                                                    @change="changeUpload"
                                                    list-type="image-card"
                                                    :default-file-list="previewList"
                                                >
                                                    {{ __('replacement.insertImage') }}
                                                </n-upload>
                                            </InputCustom>
                                        </div>
                                        <div v-else class="mb-2">
                                            <InputCustom type="upload" :label="__('strings.image')"
                                                         id="logo"
                                                         name="logo"
                                                         :error="form.errors.images">
                                                <n-upload
                                                    accept="image/*"
                                                    :default-upload="false"
                                                    @change="changeUpload"
                                                    list-type="image-card"
                                                    :default-file-list="previewList"
                                                >
                                                    {{ __('replacement.insertImage') }}
                                                </n-upload>
                                            </InputCustom>
                                        </div>
                                    </div>
                                    <div class="flex justify-between flex-row">
                                        <div class="flex gap-2 mt-3">
                                            <n-button type="error"
                                                      attr-type="button"
                                                      @click="goToBack"
                                                      :disabled="form.processing"> {{ __('strings.cancel') }}
                                            </n-button>
                                        </div>
                                        <div class="flex gap-2 mt-3">
                                            <n-button
                                                attr-type="submit"
                                                :disabled="form.processing">{{ __('strings.save') }}
                                            </n-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </n-tab-pane>
                    <n-tab-pane name="attributes"  :disabled="form.category == null" :tab="__('attribute.handleAttribute')">
                        <div>
                            <div class="border rounded p-3 py-4 pb-5 mt-5 relative" >
                                <div class="text-center absolute top-[-2px] left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-[5px] text-gray-400 ">
                                    {{ __('replacement.globalAttributes') }}<font-awesome-icon class="ml-2" icon="fa-duotone fa-globe"/></div>
                                <div class="grid lg:grid-cols-2 lg:grid-cols-3 gap-x-3">

                                    <div v-for="attribute in attributes.globalAttributes">
                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 1" @editedAttributes="editedAttribute = true" :attributable="true" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :translate="true"
                                                     :placeholder="__('strings.setDefValue')"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors['attributeInputs.'+attribute.id]?.replace('attribute inputs.'+attribute.id,attribute.name[usePage().props.value.currentLang])"></InputCustom>
                                        <InputCustom  :required="attribute.is_required" v-if="attribute.type_id == 2" :customUpdateValue="(e) => attributeValueUpdate(e,attribute.id)" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                      :placeholder="__('strings.setDefValue')"
                                                      type="select"
                                                      :optionsSelect="getSelectAttributeOptions(attribute)"
                                                      v-model="form.attributeInputs[attribute.id]" :error="form.errors['attributeInputs.'+attribute.id]?.replace('attribute inputs.'+attribute.id,attribute.name[usePage().props.value.currentLang])"></InputCustom>

                                    </div>

                                </div>
                                <div v-if="attributes.globalAttributes?.length == 0" class="w-full flex justify-center mt-2">
                                    {{ __('replacement.noGlobalAttribute') }}
                                </div>
                            </div>
                        </div>
                         <div>
                            <div class="border rounded p-3 py-4 pb-5 mt-7 relative">
                                <div class="text-center absolute top-[-2px] left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-[5px] text-gray-400 ">{{__('replacement.catAttributes')}}<font-awesome-icon class="ml-2" icon="fa-duotone fa-sitemap"/></div>
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-x-3">

                                    <div v-for="attribute in attributes.categoryAttributes">

                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 1" @editedAttributes="editedAttribute = true" :attributable="true" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :translate="true"
                                                     :placeholder="__('strings.setDefValue')"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors['attributeInputs.'+attribute.id]?.replace('attribute inputs.'+attribute.id,attribute.name[usePage().props.value.currentLang])"></InputCustom>
                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 2" :customUpdateValue="(e) => attributeValueUpdate(e,attribute.id)" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :placeholder="__('strings.setDefValue')"
                                                     type="select"
                                                     :optionsSelect="attribute.attribute_options.sort((a,b) => a.position < b.position).map((option) => {
                                                                     return {
                                                                         label : getTranslatedEntity(option.value),
                                                                         value : option.id
                                                                     }
                                                                 })"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors['attributeInputs.'+attribute.id]?.replace('attribute inputs.'+attribute.id,attribute.name[usePage().props.value.currentLang])"></InputCustom>
                                    </div>
                                </div>
                                <div v-if="attributes.categoryAttributes?.length == 0" class="w-full flex justify-center mt-2">
                                    {{__('replacement.noCatAttribute')}}
                                </div>
                            </div>
                        </div>
                        <div v-show="form.make">
                            <div class="grid grid-cols-2 gap-x-3">
                                <div class="flex flex-grow flex-col">
                                    <InputCustom v-model="form.model" required :label="__('version.selectModel')"
                                                 id="model"
                                                 name="model"
                                                 :options="filteredModels"
                                                 label-field="name"
                                                 value-field="id"
                                                 :error="form.errors.model"
                                                 type="select"></InputCustom>
                                </div>
                                <div class="flex flex-grow flex-col">
                                    <InputCustom v-model="form.version" required
                                                 :label="__('replacement.selectVersion')" id="version"
                                                 name="version"
                                                 :options="filteredVersions"
                                                 label-field="name"
                                                 value-field="id"
                                                 :error="form.errors.version"
                                                 type="select"></InputCustom>
                                </div>
                            </div>
                        </div>
                    </n-tab-pane>
                </n-tabs>

        </n-card>
        </div>
        <div :key="form.replacement" class="col-span-2">
            <n-card class="!border-t-[3px] !border-t-primaryColor mb-5 flex "  v-if="form.replacement">
                <div class="flex flex-col gap-y-1 overflow-auto">
                <span class="text-xl">{{ __('product.replacementInfo') }}</span>
                        {{__('product.replacementImages')}}</div>
                        <div class="mb-2 mt-1 flex flex-row gap-x-[9px] overflow-auto">
                            <div v-for="image in photos" class="">
                                <n-image :src="image.publicPath" object-fit="scale-down" class="w-[148px] h-[148px] border border-[#e5e7eb]" ></n-image>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-x-3 flex">

                            <div class="flex flex-grow flex-col">
                                <InputCustom :readonly="true" :customStyle="{fontSize : '22px',height : '50px',alignItems : 'center',flex : 1}"
                                             v-model="form.description" :translate="true" :label="__('product.description')"
                                             id="description"
                                             name="description"
                                             :error="form.errors.description"   type="text"></InputCustom>
                            </div>
                            <div class="flex flex-grow flex-col">
                                <InputCustom v-model="form.reference" :readonly="true" :label="__('replacement.reference_id')"
                                             id="reference"
                                             name="reference"
                                             :error="form.errors.reference" type="text"></InputCustom>
                            </div>
                            <div class="flex flex-grow flex-col">
                                <InputCustom v-model="form.side" :show="false"
                                             :label="__('side.side')" id="side"
                                             name="side"
                                             :optionsSelect="selectSides"
                                             :error="form.errors.side"
                                             type="select"></InputCustom>
                            </div>
                            <div class="flex flex-grow flex-col">
                                <InputCustom v-model="form.category" :show="false"
                                             :customUpdateValue="updateValueCat"
                                             :label="__('category.name')"
                                             :filterable="true"
                                             :optionsSelect="selectCategories"
                                             :error="form.errors.category"
                                             type="cascader"></InputCustom>
                            </div>
                        </div>

                        <div class="flex flex-grow flex-col">
                            <InputCustom :separator="' '" :show="false" :error="form.errors.version" v-model="form.version" :customUpdateValue="updateValue" :hint="showError ? __('strings.selectVersion') : ''"
                                         :optionsSelect="cascaderOptions" type="cascader"
                                         :filterable="true" :label="__('replacement.selectVersion')"
                            ></InputCustom>
                        </div>

            </n-card>
        </div>
    </div>

</template>

<script setup>

import Multiselect from "vue-multiselect";
import InputCustom from "@/Components/Input.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {computed, getCurrentInstance, h, nextTick, onMounted, onUnmounted, reactive, ref} from "vue";
import {NIcon, NTag, useDialog} from "naive-ui";
import {Inertia} from "@inertiajs/inertia";
onMounted(() => {
    selectSides.value = props.sides.map((side) => {
        return {
            value : side.id,
            label : getTranslatedEntity(side.value)
        }
    })
    conditionSelect.value = props.conditions.map((cond) => {
        return {
            value : cond.id,
            label : getTranslatedEntity(cond.value)
        }
    })
    replacementsSelect.value = getReplacements()
    cascaderOptions.value = getOptions()
    selectCategories.value = getCategories(props.categories)
    if (props.products) {
        props.products.photos.forEach((image) => {
            form.images.push({

                id : image.id,
                name : 'Image.'+image.id,
                status : 'finished',
                url : image.publicPath,

            })
        })
        setPreviewList(props.products.photos)
        form.replacement = props.products.replacement_id
        getCategoryAttributes()
        photos.value = props.products.replacement.photos;
        edit.value = true
        form.sku = props.products.sku;
        form.condition = props.products.condition_id
        form.notes = props.products.notes
        form.reference = props.products.replacement.reference;
        form.category = props.products.replacement.category_id
        form.version = props.products.replacement.version_id;
        form.price = formatCurrencyEUR(props.products.replacement.price)
        form.description = props.products.replacement.description
        form.side = props.products.replacement.side_id
    }
})
const getSelectAttributeOptions = (attribute) => {
    let x = attribute.attribute_options.map((option) => {
        return {
            label : getTranslatedEntity(option.value),
            value : option.id
        }
    })
    return x
}
const conditionSelect = ref([]);
const selectSides = ref([]);
const selectCategories = ref([]);
const cascaderOptions = ref([]);
const getOptions = () => {
    let options = props.makes.map((make) => {
        return make.models.length > 0 ? {
            value : make.id,
            label : make.name,
            children : make.models.map((model) => {
                return model.versions.length > 0 ? {
                    label  : model.name,
                    value : '#'+model.id,
                    children : model.versions.map((version) => {
                        return {
                            label : version.name,
                            value : version.id,
                        }
                    })
                } : {
                    value : model.id,
                    label  : model.name,
                }
            })
        } : {
            label : make.name,
            value : make.id
        }
    })
    return options;
}
const getCategories = (categories) => {
    let options = categories.map((category) => {
        return category.children && category.children.length > 0 ?
            {
                value : category.id,
                label : getTranslatedEntity(category.name),
                children : getCategories(category.children)
            }
            :
            {
                value : category.id,
                label : getTranslatedEntity(category.name),

            }
    })
    return options
}
const getReplacements = () => {
    let options = props.replacements.map((rep) => {
        return {
            label : getTranslatedEntity(rep.description) !== null ? rep.version.make.name + ' ' + rep.version.model.name + ' ' + rep.version.name + ' - ' +getTranslatedEntity(rep.description) : rep.version.make.name + ' ' + rep.version.model.name + ' ' + rep.version.name + ' - ' +rep.reference,
            value : rep.id
        }
    })
    return options
}
const getCategoryAttributes = (e)  => {
    return
    let replacement = props.replacements.filter((rep) => {
        return rep.id == form.replacement
    })[0]
    if(replacement.id == lastCategory){
        return
    }
    lastCategory = replacement.id
    axios.get(route('warehouse.attributes.getCategoryAttributes',{
        category_id : replacement.category_id
    })).then(({data}) => {
        if(data[0].categoryAttributes){
            if(data[1].globalAttributes){
                attributes.value = {categoryAttributes : data[0].categoryAttributes,globalAttributes : data[1].globalAttributes}
                attributes.value.categoryAttributes.forEach((e) => {
                    let value = {}
                    let option = {}
                    if(props.products){

                        value = props.products.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && props.products.sku == value.attributable_id
                        })[0]
                        //console.log('optioon global',e.attribute_options)
                        option = e.attribute_options.filter((option) => {
                            return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }else if(replacement){
                        value = replacement.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && replacement.id == value.attributable_id
                        })[0]
                        //console.log(value)
                        //console.log('optioon global',e.attribute_options)
                        option = e.attribute_options.filter((option) => {
                            return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }
                    form.attributeInputs[e.id] =  (replacement || props.products) && option && option.value ? e.type_id == 1 ? option.value : option.id : ''
                })
                attributes.value.globalAttributes.forEach((e) => {
                    let value = {}
                    let option = {}
                    if(props.products){

                        value = props.products.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && props.products.sku == value.attributable_id
                        })[0]
                        //console.log('optioon global',e.attribute_options)
                        option = e.attribute_options.filter((option) => {
                            return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }else if(replacement){
                        value = replacement.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && replacement.id == value.attributable_id
                        })[0]
                        //console.log(value)
                        //console.log('optioon global',e.attribute_options)
                        option = e.attribute_options.filter((option) => {
                            return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }
                    form.attributeInputs[e.id] = (replacement || props.products) && option && option.value ? e.type_id == 1 ? option.value : option.id : ''
                })
            }
        }else if(data[0].globalAttributes){
            attributes.value = {globalAttributes : data[0].globalAttributes}
            attributes.value.globalAttributes.forEach((e) => {
                form.attributeInputs[e.id] = ''
            })
        }
        else{
            attributes.value = {}
        }
    })
}
const replacementValueUpdate = (value) => {

    attributes.value = []
    if(form.replacement && editedAttribute.value){
        openDialogClick('replacement',value)
    }else{
        form.replacement = value
        getCategoryAttributes()
        return
        props.replacements.forEach((rep) => {
            if(rep.id == value){
                if(form.category != rep.category.id){
                    form.attributeInputs = {}
                }
                photos.value = rep.photos;
                form.description = rep.description;
                form.reference = rep.reference;
                form.version = rep.version.id;
                form.price = formatCurrencyEUR(rep.price);
                form.category = rep.category.id;
                form.side = rep.side.id;
            }
        })
    }
}
const photos = ref([])
const previewList = ref([])
const replacementsSelect = ref([])
const setPreviewList = (value) => {
    value.forEach((image,index) => {

        previewList.value.push({
            id : image.id,
            name : 'Image.'+image.id,
            status : 'finished',
            url : image.publicPath,

        })
    })
}
const tabValue = ref('repInfo');
const showError = ref(false);
const form = useForm({
    replacement : null,
    reference: null,
    version: null,
    category: null,
    side: null,
    description: null,
    price: null,
    images : [],
    attributeInputs : {},
    condition : null,
    notes : null,
    sku : null,
})
const changeUpload = (e) => {
    if(e.file.status == 'removed'){
        previewList.value = previewList.value.filter((img) => {
            return img.id != e.file.id
        })
    }else{

        previewList.value.push(e.file)
    }
    form.images = e.fileList;
}
const selectedCategory = ref(false)
const edit = ref(false);
const updateValueCat = (v) => {
    if(v){
        if(form.category && editedAttribute.value){
            openDialogClick('category',v)
        } else{

            form.category = v
            form.attributeInputs = {}
            selectedCategory.value = true
        }
    }else{
        form.category = v
        form.attributeInputs = {}
        selectedCategory.value = false
    }
}
const dialog = useDialog();

const openDialogClick = (type,value) => {
    dialog.warning({
        title: $trans('strings.attention'),
        content: $trans('replacement.editedAttributes'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            if(type == 'replacement'){
                editedAttribute.value = false
                form.attributeInputs = {}

                form.replacement = value
                getCategoryAttributes()
                props.replacements.forEach((rep) => {
                    if(rep.id == value){
                        if(form.category != rep.category.id){
                            form.attributeInputs = {}
                        }
                        photos.value = rep.photos;
                        form.description = rep.description;
                        form.reference = rep.reference;
                        form.version = rep.version.id;
                        form.price = formatCurrencyEUR(rep.price);
                        form.category = rep.category.id;
                        form.side = rep.side.id;
                    }
                })
            }else if (type == 'goBack'){
                window.history.back()
            }
        }
    })
}
const attributes = ref({})
const tabValueUpdate = (e) => {
    if(e == 'attributes'){
        getCategoryAttributes()
    }
    tabValue.value = e
}
const focusInput = (v) => {
    form.price = form.price != null ? getPlainNumberFromCurrency(form.price).toString().replace('.',',') : null
}
const blurInput = (v) => {
    form.price = form.price != null ? formatCurrencyEUR(form.price) : null
}
const updateValueCurrency = (v) => {
    form.price = v
}
let lastCategory = null;
const editedAttribute = ref(false);
function getPlainNumberFromCurrency(formattedCurrency) {
    if(formattedCurrency == null){
        return
    }
    // Remove any non-digit characters except '.' and ',' for separators
    const plainNumber = formattedCurrency.replace(/[^0-9,]/g, '');
    // Replace ',' with '.' for decimal separator consistency
    const numberWithDotSeparator = plainNumber.replace(',', '.');

    // Parse the plain number as a float and return it
    return parseFloat(numberWithDotSeparator);
}
const attributeValueUpdate = (value,id) => {
    form.attributeInputs[id] = value
    editedAttribute.value = true
}
function formatCurrencyEUR(number) {
    if(typeof number == 'string')number = number.replace(',','.')
    // Convert number to a string with two decimal places
    const formattedNumber = Number(number).toFixed(3);

    // Split the formatted number into integer and decimal parts
    if(isNaN(formattedNumber)){
        return null
    }
    const parts = formattedNumber.split('.');
    // Format the integer part by adding '.' every 3 digits
    const integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    // console.log(integerPart,parts)
    // Construct the formatted currency string with ',' as decimal separator and '€' symbol
    const currencyString = `${integerPart},${parts[1]}`;

    return currencyString;
}
const props = defineProps({
    replacements: Array,
    products : Object,
    models : Array,
    makes : Array,
    versions : Array,
    categories : Array,
    conditions : Array,
    sides : Array,
    imgBaseUrl : String,
})

const onNewProductSubmit = () => {
    return
    if (props.products) {
        form.transform((el) => {
            return ({
                    sku : el.sku,
                    condition : el.condition,
                    replacement : el.replacement,
                    notes : el.notes ? el.notes : null,
                    price : el.price ? getPlainNumberFromCurrency(el.price) : null,
                    images : el.images.length > 0 ? el.images.map((e) =>  e.file ? e.file : e) : null,
                    attributeInputs : el.attributeInputs,
                    _method: "PUT"
                }
            )
        }).post(route('warehouse.products.update', {
            product: props.products.sku
        }), {
            onFinish: () => {
                if (!form.hasErrors) {
                    //Inertia.get(route('warehouse.versions.index'))
                }
            }
        })
    } else {
        form.transform((el) => {
            return ({
                    sku : el.sku,
                    condition : el.condition,
                    replacement : el.replacement,
                    notes : el.notes ? el.notes : null,
                    price : el.price ? getPlainNumberFromCurrency(el.price) : null,
                    images : el.images.length > 0 ? el.images.map((e) =>  e.file) : null,
                    attributeInputs : el.attributeInputs,
                }
            )
        }).post(route('warehouse.products.store'), {
            onFinish: () => {
                if (!form.hasErrors) {
                    //Inertia.get(route('warehouse.versions.index'))
                }
            }
        })
    }
}

const goToBack = function () {

    if( editedAttribute.value){
        openDialogClick('goBack')
    }else{
        window.history.back()
    }
}

const str_replace = (str) => {
    if (str) {
        str = str.toString();
        return str.replace('.', ',')
    }

}
</script>


<style scoped>

:deep(.n-upload-file-list .n-upload-file.n-upload-file--image-card-type) {
    @apply w-[150px] h-[150px]
}
:deep(.n-upload-file-list.n-upload-file-list--grid) {
    display: grid;
    grid-template-columns: repeat(auto-fill, 150px);
    grid-gap: 8px;
    margin-top: 0;
} :deep(.n-upload-trigger.n-upload-trigger--image-card .n-upload-dragger){
      @apply w-[150px] h-[150px]
  }
:deep(.n-upload-trigger.n-upload-trigger--image-card){
    @apply w-[150px] h-[150px]
}
</style>
