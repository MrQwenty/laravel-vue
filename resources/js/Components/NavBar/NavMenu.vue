<script setup>
import NavBarItem from "@/Components/NavBar/NavBarItem.vue";
import NavBarItemDropdown from "@/Components/NavBar/NavBarItemDropdown.vue";
import {usePage} from "@inertiajs/inertia-vue3";
import {NIcon, NMenu} from "naive-ui";
import {computed, h, onMounted} from "vue";
import {Inertia} from "@inertiajs/inertia";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const props = defineProps({
    menu: Array
})

const menuOptions = [];

const currentRoute = computed(() => {
    return route().current();
})

const userDropdownItems = [
    {
        routeName: 'logout',
        title: 'Logout',
    },
]

const handleNavigation = function (event) {
    Inertia.get(route(event));
}

const renderMenuIcon = function (option) {
    console.log(option)
    // return render placeholder for indent

    if (option.iconNameCustom) {
        return h(NIcon, null, {
            default: () => h(
                FontAwesomeIcon, {
                icon: option.iconNameCustom
            })
        });
    } else {
    }
    return null;

};

onMounted(() => {
})

</script>


<template>
    <n-menu v-model:value="currentRoute" :render-icon="renderMenuIcon" :options="props.menu"
            @update:value="handleNavigation"/>
</template>

<style scoped>

</style>
