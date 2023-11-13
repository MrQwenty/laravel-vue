<script setup>
import {computed, onMounted, ref, h, onBeforeMount, nextTick, useAttrs, watch} from 'vue';
import {NInput, NFormItem, NImage} from 'naive-ui'
import {usePage} from "@inertiajs/inertia-vue3";
import {useCurrencyInput} from "vue-currency-input";

defineOptions({
    inheritAttrs: false
})

const props = defineProps(['modelValue', 'label', 'error', 'id', 'customClass','options','customValue','hint','customUpdateValue']);

const emit = defineEmits(['update:modelValue','editedAttributes']);

const input = ref(null);
const formItem = ref(null);

const currencyInput = useCurrencyInput(props.options)

const inputValue = ref(null);

const edits = ref({});

const showEdits = ref(false);

const dropdownIcon = ref(null);
const dropdownValue = ref(null);

const disableValidation = ref(false);
const options = ref([]);
const errorStatus = computed({
    get() {
        let validationStatus = (props.error == null ? null : (!!String(props.error).length ? 'error' : null));

        if (disableValidation.value === true) {
            disableValidation.value = false;
        }

        return {
            validationStatus: validationStatus,
            feedback: validationStatus ? props.error : null,
        }
    }
})
onMounted(() => {
    if(props.customValue){
        let trans = (useAttrs().translate)
        nextTick().then(() => {
            if(trans){
                inputValue.value = props.customValue;
                let langs = usePage().props.value.langs;
                dropdownValue.value
                langs.forEach((lang) => {
                    edits.value[lang.value] = false;
                    if(usePage().props.value.currentLang == lang.value){
                        dropdownValue.value = lang.value
                        dropdownIcon.value = lang.icon
                    }
                    options.value.push({
                        key: lang.value,
                        value: lang.value,
                        label: (row) => {
                            return h(
                                'div',
                                {
                                    class: lang.icon,
                                }
                            )
                        }
                    })
                });
                inputValue.value = getTranslatedEntity(inputValue.value,dropdownValue.value,false)
            }
        });
    }else{

        let trans = (useAttrs().translate)
        nextTick().then(() => {
            if(trans){
                inputValue.value = props.modelValue;
                let langs = usePage().props.value.langs;
                dropdownValue.value
                langs.forEach((lang) => {
                    edits.value[lang.value] = false;
                    if(usePage().props.value.currentLang == lang.value){
                        dropdownValue.value = lang.value
                        dropdownIcon.value = lang.icon
                    }
                    options.value.push({
                        key: lang.value,
                        value: lang.value,
                        label: (row) => {
                            return h(
                                'div',
                                {
                                    class: lang.icon,
                                }
                            )
                        }
                    })
                });
                inputValue.value = getTranslatedEntity(inputValue.value,dropdownValue.value,false)
            }
        });
    }
});
const handleSelect = (a) => {
    if(edits.value[dropdownValue.value] == true){
        showEdits.value = true
    }
    if(showEdits.value == true){
        let langs = usePage().props.value.langs;
        if(edits.value[a] == true) showEdits.value = false
        langs.forEach((lang) => {
            if(edits.value[lang.value] == true && lang.value != a ){
                showEdits.value = true
            }
        })
    }
    let langs = usePage().props.value.langs;
    dropdownValue.value = a
    let lang = langs.find((e) => {
        return e.value == a;
    })
    dropdownIcon.value = lang.icon;

    if (props.modelValue) {
        inputValue.value = props.modelValue;
        if (inputValue.value instanceof Object) {
            inputValue.value = getTranslatedEntity(inputValue.value,a,false)
        }
    }
    if(props.customValue){

        inputValue.value = props.customValue;
        if (inputValue.value instanceof Object) {
            inputValue.value = getTranslatedEntity(inputValue.value,a,false)
        }
    }
}
const emitInputEvent = function ($event,trans,attributable) {
    if(trans == 'translate'){
        if(attributable){
            emit('editedAttributes')
        }
        if(props.customValue){
            if(props.customValue instanceof Object){
                edits.value[dropdownValue.value] = true
                inputValue.value = $event
                emit('update:modelValue', Object.assign(props.customValue,{[dropdownValue.value] : $event}));

            }
        }
        else if(props.modelValue instanceof Object){
            edits.value[dropdownValue.value] = true
            inputValue.value = $event
            emit('update:modelValue', Object.assign(props.modelValue,{[dropdownValue.value] : $event}));
        }else{
            inputValue.value = $event
            emit('update:modelValue', Object.assign({},{[dropdownValue.value] : $event}));
        }

    }else{
        emit('update:modelValue', $event);
    }
}
</script>

<template>
    <n-form-item :class="props.customClass" ref="formItem"
                 :label="props.label"
                 :required="$attrs.required"
                 :validation-status="errorStatus.validationStatus"
                 :feedback="errorStatus.feedback">
        <n-input-group>

            <template v-if="$attrs.type == 'select'">
                <n-select v-bind="$attrs" :multiple="$attrs.multiple" :options="$attrs.optionsSelect" :value="props.modelValue" :on-update:value="props.customUpdateValue ? props.customUpdateValue : emitInputEvent" ref="input"
                          :id="props.id">
                </n-select>
            </template>
            <template v-else-if="$attrs.type == 'cascader'">
                <n-cascader :clearable="$attrs.clearable" v-bind="$attrs" :multiple="$attrs.multiple" :options="$attrs.optionsSelect" :value="props.modelValue" :on-update:value="props.customUpdateValue ? props.customUpdateValue : emitInputEvent" ref="input"
                          :id="props.id">
                </n-cascader>
            </template>
            <template v-else-if="$attrs.type == 'upload'">
                <slot></slot>
            </template>
            <template v-else-if="$attrs.type == 'currency'">
                <n-input  v-bind="$attrs"  :value="props.modelValue" @input="props.customUpdateValue"  :id="props.id">
                    <template v-if="$attrs.suffix" #suffix>
                        €
                    </template>
                </n-input>
            </template>
            <template v-else-if="$attrs.type == 'date'">
                <n-date-picker format="dd/MM/yyyy" v-bind="$attrs" :value="props.modelValue"
                               :on-update:value="props.customUpdateValue ? props.customUpdateValue : emitInputEvent" ref="input"
                               :id="props.id">
                </n-date-picker>
            </template>
            <template v-else-if="$attrs.type == 'textarea' && $attrs.translate">
                <n-input v-bind="$attrs" :style="$attrs.customStyle" :value="inputValue" @input="(e) => emitInputEvent(e,'translate',$attrs.attributable)" ref="input"
                         :id="props.id" type="textarea">
                </n-input>
            </template>
            <template v-else-if="$attrs.translate">
                <n-input v-bind="$attrs" :style="$attrs.customStyle" :value="inputValue" @input="(e) => emitInputEvent(e,'translate',$attrs.attributable)" ref="input"
                         :id="props.id">
                </n-input>
            </template>
            <template v-else>
                <n-input v-bind="$attrs" :value="props.modelValue" @input="emitInputEvent" ref="input"
                         :id="props.id">
                </n-input>
            </template>
            <template v-if="$attrs.translate">
                <n-dropdown  trigger="click" :options="options" @select="handleSelect">
                    <n-button :style="{height : $attrs.customStyle && $attrs.customStyle.height ? $attrs.customStyle.height : ''}" type="primary" ghost>
                        <span :class="dropdownIcon"></span>
                    </n-button>
                </n-dropdown>
            </template>
        </n-input-group>

        <template #feedback>

            <div v-if="props.hint" class="text-xs mb-1 hint">
                {{
                    props.hint
                }}
            </div>
            <div  v-if="showEdits">
                <div class="text-xs !text-amber-600 mb-1">
                    {{ __('strings.langEdits') }}
                </div>
            </div>
            <div class="text-xs mb-1">
                {{ errorStatus.feedback }}
            </div>
        </template>
    </n-form-item>
</template>
