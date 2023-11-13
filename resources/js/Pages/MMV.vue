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
                    <n-page-header :subtitle="tab == 'version' ? __('version.subTitle') : tab == 'make' ? __('make.subTitle') : tab == 'model' ? __('model.subTitle') : null" @back="handleBack">
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
                            <font-awesome-icon
                                :icon="(tab == 'version' || tab ==  'version/new' || tab ==  'version/edit') ? 'fa-duotone fa-list' :
                                (tab == 'make' || tab == 'make/new' || tab == 'make/edit') ? 'fa-duotone fa-database' :
                                 (tab == 'model' || tab == 'model/new' || tab == 'model/edit') ? 'fa-duotone fa-car' : null"
                                size="2xl"></font-awesome-icon>
                        </template>
                        <!--                    <template #extra>-->
                        <!--                        <n-space>-->
                        <!--                            <n-button>Refresh</n-button>-->
                        <!--                            <n-dropdown :options="options" placement="bottom-start">-->
                        <!--                                <n-button :bordered="false" style="padding: 0 4px">-->
                        <!--                                    ···-->
                        <!--                                </n-button>-->
                        <!--                            </n-dropdown>-->
                        <!--                        </n-space>-->
                        <!--                    </template>-->
                        <!--                    <template #footer>-->
                        <!--                        As of April 3, 2021-->
                        <!--                    </template>-->
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

            <MakeTableComponent v-if="props.tab == 'make'" :permissions="props.permissions" :imgBaseUrl="props.imgBaseUrl" :makes="props.makes" :models="props.models"
                                :versions="props.versions"></MakeTableComponent>

            <ModelTableComponent v-if="props.tab == 'model'" :permissions="props.permissions" :makes="props.makes" :models="props.models"
                                 :versions="props.versions"></ModelTableComponent>

            <VersionTableComponent v-if="props.tab == 'version'" :permissions="props.permissions" :makes="props.makes" :models="props.models"
                                   :versions="versions"></VersionTableComponent>

            <NewEditVersionComponent v-if="props.tab == 'version/new'" :makes="props.makes"
                                     :models="props.models"></NewEditVersionComponent>

            <NewEditVersionComponent v-if="props.tab == 'version/edit'" :makes="props.makes"
                                     :models="props.models" :versions="versions"></NewEditVersionComponent>
            <NewEditModelComponent v-if="props.tab == 'model/new'" :makes="props.makes"
                                     ></NewEditModelComponent>

            <NewEditModelComponent v-if="props.tab == 'model/edit'" :makes="props.makes"
                                     :models="props.models" :versions="versions"></NewEditModelComponent>
            <NewEditMakeComponent v-if="props.tab == 'make/edit'" :logo="props.logo" :path="props.path" :makes="props.makes"
                                    ></NewEditMakeComponent>
            <NewEditMakeComponent v-if="props.tab == 'make/new'"
                                   ></NewEditMakeComponent>

        </n-card>
    </Authenticated>
</template>

<script setup>
import Authenticated from '@/Layouts/Authenticated.vue';
import MakeTableComponent from "@/Components/MMV/MakeTableComponent.vue";
import ModelTableComponent from "@/Components/MMV/ModelTableComponent.vue";
import VersionTableComponent from "@/Components/MMV/VersionTableComponent.vue";
import {computed, onMounted, ref, watch} from 'vue';
import {usePage} from "@inertiajs/inertia-vue3";
import NewEditVersionComponent from "@/Components/MMV/NewEditVersionComponent.vue";
import NewEditMakeComponent from "@/Components/MMV/NewEditMakeComponent.vue";
import {breadcrumbDark} from "naive-ui";
import NewEditModelComponent from "@/Components/MMV/NewEditModelComponent.vue";
const showFlashMessage = ref(true);
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
})
const showFlashError = ref(true);
const routeInfo = computed(() => {

    let route = "";
    let breadcrumbs = [{'title':$trans('menu.warehouse')},
        {'title': 'MMV'}
    ];

    switch (props.tab) {
        case 'make':
            route = 'make.makes';
            breadcrumbs.push({
                title: route
            })
            break;
        case 'model':
            route = 'model.models';
            breadcrumbs.push({
                title: route
            })
            break;
        case 'version':
            route = 'version.versions';
            breadcrumbs.push({
                title: route
            })
            break;
        case 'version/new':
            route = 'version.newVersion';
            breadcrumbs.push({
                title: 'version.versions'
            }, {
                title: 'version.newVersion'
            })
            break;
        case 'version/edit':
            route = 'version.editVersion';
            breadcrumbs.push({
                title: 'version.versions'
            }, {
                title: 'version.editVersion'
            })
        break;
        case 'model/new':
            route = 'model.newModel';
            breadcrumbs.push({
                title: 'model.models'
            }, {
                title: 'model.newModel'
            })
            break;
        case 'model/edit':
            route = 'model.editModel';
            breadcrumbs.push({
                title: 'model.models'
            }, {
                title: 'model.editModel'
            })
            break;
        case 'make/new':
            route = 'make.newMake';
            breadcrumbs.push({
                title: 'make.makes'
            }, {
                title: 'make.newMake'
            })
            break;
        case 'make/edit':
            route = 'make.editMake';
            breadcrumbs.push({
                title: 'make.makes'
            }, {
                title: 'make.editMake'
            })
            break;
    }

    return {
        title: route,
        breadcrumbs: breadcrumbs
    }
});

const props = defineProps({
    makes: Array,
    tab: String,
    models: Array,
    versions: Array,
    filters : Array,
    logo : Array,
    path : String,
    imgBaseUrl : String,
    permissions : Boolean,
})

const getIcon = () => {
    let menu = usePage().props.value.menu

    menu.forEach((el) => {

    })
}

</script>

<style scoped>

</style>
