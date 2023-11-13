<template>
    <n-card class="!border-t-[3px] !border-t-primaryColor mb-5">
        <div class="flex flex-grow flex-col">
            <InputCustom required :customStyle="{fontSize : '22px',height : '50px',alignItems : 'center',flex : 1}"
                         v-model="form.description" :translate="true" :label="__('replacement.description')"
                         id="description"
                         name="description"
                         :error="form.errors.description"   type="text"></InputCustom>
        </div>
    </n-card>
    <n-card class="!border-t-[3px] !border-t-primaryColor mb-5">
        <div>
            <div>
                <form @submit.prevent="onNewReplacementSubmit">
                    <n-tabs type="line" animated @update:value="tabValueUpdate" :value="tabValue" >
                        <n-tab-pane name="genInfo"  :tab="__('replacement.generalInfo')">

                            <div class="grid grid-cols-2 gap-x-3 flex">
                                <div class="flex flex-grow flex-col">
                                    <InputCustom v-model="form.reference" required :label="__('replacement.reference_id')"
                                                 id="reference"
                                                 name="reference"
                                                 :error="form.errors.reference" type="text"></InputCustom>
                                </div>
                                <div class="flex flex-grow flex-col">
                                    <InputCustom :customUpdateValue="updateValueCurrency" @focus="focusInput" @blur="blurInput" :suffix="true"
                                                 type="currency" v-model="form.price" :label="__('replacement.price')"
                                                 :error="form.errors.price"></InputCustom>
                                </div>
                                <div class="flex flex-grow flex-col">
                                    <InputCustom v-model="form.side" required
                                                 :label="__('side.side')" id="side"
                                                 name="side"
                                                 :optionsSelect="selectSides"
                                                 :error="form.errors.side"
                                                 type="select"></InputCustom>
                                </div>
                                <div class="flex flex-grow flex-col">
                                    <InputCustom v-model="form.category" required
                                                 :customUpdateValue="updateValueCat"
                                                 :label="__('category.name')"
                                                 :filterable="true"
                                                 :clearable="true"
                                                 :optionsSelect="selectCategories"
                                                 :error="form.errors.category"
                                                 type="cascader"></InputCustom>
                                </div>
                            </div>

                            <div class="flex flex-grow flex-col">
                                <InputCustom :separator="' '" :error="form.errors.version" v-model="form.version" :clearable="true" :customUpdateValue="updateValue" :hint="showError ? __('strings.selectVersion') : ''" required
                                             :optionsSelect="cascaderOptions" type="cascader"
                                             :filterable="true" :label="__('replacement.selectVersion')"
                                ></InputCustom>
                            </div>
                            <div v-if="!edit" class="mb-2">
                                <InputCustom type="upload"  :hint="__('replacement.previewImage')" required :label="__('strings.image')"
                                             id="logo"
                                             name="logo"
                                             :error="form.errors.images" >
                                    <n-upload
                                        accept="image/*"
                                        :action="route('warehouse.makes.store')"
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
                                <InputCustom type="upload" :hint="__('replacement.previewImage')" required :label="__('strings.image')"
                                             id="logo"
                                             name="logo"
                                             :error="form.errors.images">
                                    <n-upload
                                        accept="image/*"
                                        :action="route('warehouse.makes.store')"
                                        :default-upload="false"
                                        @change="changeUpload"
                                        :show-remove-button="form.images.length > 1"
                                        list-type="image-card"
                                        :default-file-list="previewList"
                                    >
                                        {{ __('replacement.insertImage') }}
                                    </n-upload>
                                </InputCustom>
                            </div>
                        </n-tab-pane>
                        <n-tab-pane name="attributes"  :disabled="form.category == null" :tab="__('attribute.handleAttribute')">
                            <div>
                                <div class="border rounded p-3 py-4 pb-5 mt-5 relative" >
                                    <div class="text-center absolute top-[-2px] left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white px-[5px] text-gray-400 ">
                                        {{ __('replacement.globalAttributes') }}<font-awesome-icon class="ml-2" icon="fa-duotone fa-globe"/></div>
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-x-3">

                                    <div v-for="attribute in attributes.globalAttributes">
                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 1" @editedAttributes="editedAttribute = true" :attributable="true" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :translate="true"
                                                     :placeholder="__('strings.setDefValue')"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors.attributeInputs"></InputCustom>
                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 2" :customUpdateValue="(e) => attributeValueUpdate(e,attribute.id)" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :placeholder="__('strings.setDefValue')"
                                                     type="select"
                                                     :optionsSelect="attribute.attribute_options.map((option) => {
                                                         return {
                                                             label : getTranslatedEntity(option.value),
                                                             value : option.id
                                                         }
                                                     })"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors.attributeInputs"></InputCustom>
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
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors.attributeInputs"></InputCustom>
                                        <InputCustom :required="attribute.is_required" v-if="attribute.type_id == 2" :customUpdateValue="(e) => attributeValueUpdate(e,attribute.id)" :label="''+getTranslatedEntity(attribute.name) + (attribute.is_required ? '' : '')"
                                                     :placeholder="__('strings.setDefValue')"
                                                     type="select"
                                                     :optionsSelect="attribute.attribute_options.map((option) => {
                                                         return {
                                                             label : getTranslatedEntity(option.value),
                                                             value : option.id
                                                         }
                                                     })"
                                                     v-model="form.attributeInputs[attribute.id]" :error="form.errors.attributeInputs"></InputCustom>
                                    </div>
                                </div>
                                    <div v-if="attributes.categoryAttributes?.length == 0" class="w-full flex justify-center mt-2">
                                        {{__('replacement.noCatAttribute')}}
                                    </div>
                                </div>
                                <div class="mt-2 text-gray-400"><span class="text-[#d03050]">*</span> {{__('replacement.requiredAttribute')}}</div>
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
    </n-card>
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
    window.addEventListener('beforeunload',(e) => {
        e.preventDefault()
        /*const confirmationMessage = 'Are you sure you want to leave?';
        e.returnValue = confirmationMessage;

        return confirmationMessage;*/
    })
    cascaderOptions.value = getOptions()
    selectCategories.value = getCategories(props.categories)
    selectSides.value = props.sides.map((side) => {
        return {
            value : side.id,
            label : getTranslatedEntity(side.value)
        }
    })
    if (props.replacements) {
        props.replacements.photos.forEach((image,index) => {
            form.images.push({

                id : image.id,
                name : 'Image.'+image.id,
                status : 'finished',
                url : image.publicPath,

            })
        })
        setPreviewList(props.replacements.photos)
        edit.value = true
        form.reference = props.replacements.reference;
        form.category = props.replacements.category_id
        form.version = props.replacements.version_id;
        form.price = formatCurrencyEUR(props.replacements.price)
        form.description = props.replacements.description
        form.side = props.replacements.side_id
    }
})
const previewList = ref([])
const setPreviewList = (images) => {
    images.forEach((image,index) => {
        previewList.value.push({
            id : image.id,
            name : 'Image.'+image.id,
            status : 'finished',
            url : image.publicPath,

        })
    })
}
const tabValue = ref('genInfo');
const showError = ref(false);
const form = useForm({
    reference: null,
    version: null,
    category: null,
    side: null,
    description: null,
    price: null,
    images : [],
    attributeInputs : {},
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
const selectCategories = ref([]);
const selectSides = ref([]);
const filteredModels = computed(() => {
    return _.filter(props.models, (el) => {
        if (form.make && el.make_id == form.make) {
            return el;
        }
    })
})
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
onUnmounted(() => {
    window.removeEventListener('beforeunload',(e) => {
        e.preventDefault()
    })
})
const dialog = useDialog();

const openDialogClick = (type,v) => {
    dialog.warning({
        title: $trans('strings.attention'),
        content: $trans('replacement.editedAttributes'),
        positiveText: $trans('strings.confirm'),
        negativeText: $trans('strings.cancel'),
        maskClosable: false,
        onPositiveClick: () => {
            if(type == 'category'){
                form.category = v
                form.attributeInputs = {}
                selectedCategory.value = true
                editedAttribute.value = false
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
const getCategoryAttributes = (e)  => {
    if(form.category == lastCategory){
        return
    }
    lastCategory = form.category
    axios.get(route('warehouse.attributes.getCategoryAttributes',{
        category_id : form.category
    })).then(({data}) => {
        if(data[0].categoryAttributes){
            if(data[1].globalAttributes){
                attributes.value = {categoryAttributes : data[0].categoryAttributes,globalAttributes : data[1].globalAttributes}
                attributes.value.categoryAttributes.forEach((e) => {
                    let value = {}
                    let option = {}
                    if(props.replacements){
                        value = props.replacements.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && props.replacements.id == value.attributable_id
                        })[0]
                        //console.log(value)
                        //console.log('optioon global',e.attribute_options)
                        option = e.attribute_options.filter((option) => {
                            return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }
                    form.attributeInputs[e.id] = props.replacements && option && option.value ? e.type_id == 1 ? option.value : option.id : ''
                })
                attributes.value.globalAttributes.forEach((e) => {
                    let value = {}
                    let option = {}
                    if(props.replacements){
                        value = props.replacements.attribute_values.filter((value) => {
                            return value.attribute_id == e.id && props.replacements.id == value.attributable_id
                        })[0]
                        //console.log(value)
                        //console.log('optioon global',e.attribute_options)
                         option = e.attribute_options.filter((option) => {
                             return value ? option.id == value.attribute_option_id : false
                        })[0]
                    }
                    form.attributeInputs[e.id] = props.replacements && option && option.value ? e.type_id == 1 ? option.value : option.id : ''
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
const updateValue = (v) => {
    if(v.charAt(0) !== 'V'){
        showError.value = true
        form.version = null;
    }else{
        showError.value = false
        form.version = v
    }
}
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
const filteredVersions = computed(() => {
    return _.filter(props.versions, (el) => {
        if ((form.make && el.make_id == form.make) && (form.model && el.model_id == form.model)) {
            return el;
        }
    })
})
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
const cascaderOptions = ref([])
const props = defineProps({
    replacements: Object,
    makes: Array,
    models: Array,
    versions: Array,
    sides: Array,
    categories: Array,
    paths : Array|Object,
    attributes : Array,
})
const onNewReplacementSubmit = () => {
    if (props.replacements) {
        form.transform((el) => {
            return ({
                    version : el.version,
                    reference : el.reference,
                    category : el.category,
                    side : el.side,
                    price : el.price ? getPlainNumberFromCurrency(el.price) : null,
                    description : el.description ? el.description : null,
                    images : el.images.length > 0 ? el.images.map((e) =>  e.file ? e.file : e) : null,
                    attributeInputs : el.attributeInputs,
                    _method: "PUT"
                }
            )
        }).post(route('warehouse.replacements.update', {
            id: props.replacements.id
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
                    version : el.version,
                    reference : el.reference,
                    category : el.category,
                    side : el.side,
                    price : el.price ? getPlainNumberFromCurrency(el.price) : null,
                    description : el.description ? el.description : null,
                    images : el.images.length > 0 ? el.images.map((e) =>  e.file) : null,
                    attributeInputs : el.attributeInputs,
                }
            )
        }).post(route('warehouse.replacements.store'), {
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
const formatDate = (temp) => {
    let date = new Date(temp)
    let y = date.getFullYear();
    let m = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
    let d = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
    return y + '-' + m + '-' + d
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
