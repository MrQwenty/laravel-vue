<template>
    <div v-if="props.type == 'value'">
        <div v-if="isEdit && props.permissions" class="h-[30px] justify-between flex flex-row items-center">
            <InputCustom  v-model="props.value" :translate="true" @update:value="props.onUpdateValue"></InputCustom>
            <div class="flex">
                <n-button size="small" style="margin-right: 10px" @click="handleConfirm">
                    <font-awesome-icon icon="fa-duotone fa-circle-check" class="text-green-400"></font-awesome-icon>
                </n-button>
                <n-button size="small" @click="handleCancel">
                    <font-awesome-icon icon="fa-duotone fa-circle-xmark" class="text-red-500"></font-awesome-icon>
                </n-button>
            </div>
        </div>
        <div v-else  class="h-[30px]  justify-between flex-row flex items-center" >
            <span>{{getTranslatedEntity(props.value)}}</span>
            <div v-if="permissions">
                <n-button size="small" style="margin-right: 10px" @click="handleOnClick">
                    <font-awesome-icon icon="fa-duotone fa-edit" class="text-indigo-500">

                    </font-awesome-icon>
                </n-button>
                <n-popconfirm :on-positive-click="deleteOption">
                    <div>Sicuro di voler cancellare questa opzione?</div>
                    <template #activator>
                        <n-button size="small" style="margin-right: 10px">
                            <font-awesome-icon icon="fa-duotone fa-trash" class="text-red-500">

                            </font-awesome-icon>
                        </n-button>
                    </template>
                </n-popconfirm>
                <n-button size="small" style="margin-right: 10px" @click="changePositionUp">
                    <font-awesome-icon icon="fa-duotone fa-chevron-up" >

                    </font-awesome-icon>
                </n-button>
                <n-button size="small" @click="changePositionDown">
                    <font-awesome-icon icon="fa-duotone fa-chevron-down"  >

                    </font-awesome-icon>
                </n-button>
            </div>
        </div>
    </div>
    <div v-else-if="props.type == 'position'">
        <div class="flex flex-row gap-x-2" v-if="props.permissions">
                <n-input size="small" @focus="focusInput" @update:value="(a) =>{ positionVal = a;hasEdited = true}" @keypress="regexInput($event, /^[0-9,]+$/i)" :value="positionVal" style="width : 50px;text-align: center"></n-input>

            <n-button v-if="hasEdited" size="small"  @click="confirmPositionChange">
                <font-awesome-icon icon="fa-duotone fa-circle-check" class="text-green-400"></font-awesome-icon>
            </n-button>
            <n-button v-if="hasEdited" size="small" @click="blurInput">
                <font-awesome-icon icon="fa-duotone fa-circle-xmark" class="text-red-500"></font-awesome-icon>
            </n-button>
        </div>
        <div class="flex  flex-row  gap-x-2"  v-else>
            <n-input size="small" :readonly="true"  :value="positionVal" style="width : 50px;text-align: center"></n-input>
        </div>
    </div>
</template>

<script setup>
import InputCustom from '@/Components/Input.vue';
import {onMounted, ref, watch} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";
import {Inertia} from "@inertiajs/inertia";

onMounted(() => {
})

const emit = defineEmits(['saveLastVal','cancelEdit','confirmEdit','deleteOption','changedPosition','focusInput','blurInput']);
const props = defineProps({
    value : Object,
    onUpdateValue : Function,
    openId : String,
    id : String,
    position : String,
    attribute_id : String,
    type : String,
    positionId : String,
    permissions : Boolean
})
const positionVal = ref('');
const lastVal = ref({});
const isEdit = ref(false);
const hasEdited = ref(false);
onMounted(() => {
    positionVal.value = JSON.parse(JSON.stringify(props.position));
})
watch(() => props.openId, () => {
        if(props.openId != props.id && isEdit.value == true){
            isEdit.value = false
            hasEdited.value = false;
            emit('cancelEdit')
        }
    }
)
watch(() => props.positionId, () => {
        if(props.positionId != props.id && isEdit.value){
            positionVal.value = props.position
            isEdit.value = false
            hasEdited.value = false;
            //emit('blurInput')
        }
    }
)
watch(() => props.position,() => {
    positionVal.value = props.position
})
const confirmPositionChange = () => {
    Inertia.put(route('warehouse.attributes.optionPositionInput',{
        attribute_id : props.attribute_id,
        id : props.id
    }),{
        position : positionVal.value
    },{
        onSuccess : () => {
            emit('changedPosition')
            isEdit.value = false;
            hasEdited.value = false;
            positionVal.value = props.position
        }
    })
}
const focusInput = () => {
    emit('focusInput')
    isEdit.value = true

}
const blurInput = () => {
    positionVal.value = props.position
    emit('blurInput')
    isEdit.value = false
    hasEdited.value = false
}
const handleOnClick = () => {
        emit('saveLastVal',props.value)
        isEdit.value = true
        lastVal.value = props.value
}
const handleCancel = () => {

    emit('cancelEdit',-1)
    isEdit.value = false;
    hasEdited.value = false;
}
const deleteOption = ()  => {
    emit('deleteOption')
}
const handleConfirm = () => {
    emit('confirmEdit');
    isEdit.value = false;
    hasEdited.value = false;
}
const changePositionDown = () => {
    Inertia.put(route('warehouse.attributes.optionPosition',{
        attribute_id : props.attribute_id,
        id : props.id
    }),{
        position : 'down'
    },{
        onFinish : () => {
            emit('changedPosition')
        }
    })
}
const changePositionUp = () => {
    Inertia.put(route('warehouse.attributes.optionPosition',{
        attribute_id : props.attribute_id,
        id : props.id
    }),{
        position : 'up'
    },{
        onFinish : () => {
            emit('changedPosition')
        }
    })
}
</script>
