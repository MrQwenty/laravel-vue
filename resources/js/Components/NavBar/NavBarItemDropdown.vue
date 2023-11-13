<template>
    <div class="relative list-none" v-click-out="checkClickOut">
        <NavBarItem :title="title" :iconNameCustom="iconNameCustom" @click:toggle="toggleDropdown"></NavBarItem>
        <div ref="dropdownMenu">
            <div v-if="isMenuToggled"
                 class="z-10 font-sm bg-white divide-y divide-gray-100 rounded-lg shadow w-full absolute top-[50px] text-left">
                <ul class="py-1 space-y-2">
                    <li v-for="item in items">
                        <div class="relative">
                            <button v-if="item.routeName == 'logout'" @click="goLogout"
                                    class="flex p-2 pl-4 w-full text-sm font-sm text-gray-700 rounded-lg transition duration-75 group hover:bg-gray-100">
                                <span class="title">{{ item.title }}</span>
                            </button>
                            <a v-else-if="!item.separator && !item.items "
                               :href="route(item.routeName)"
                               :class="'flex p-2 pl-4 w-full text-sm font-sm text-gray-700 rounded-lg transition duration-75 group hover:bg-gray-100'">{{
                                    item.title
                                }}
                            </a>
                            <div v-else-if="!item.separator && item.items && item.items.length > 0 "
                                 class="justify-between w-full ">
                                <button
                                    @click="showSubDropdown = item.title == subMenuOpen ? !showSubDropdown : subMenuOpen = item.title"
                                    :class="'justify-between flex p-2 pl-4 w-full text-sm font-sm text-gray-700 rounded-lg transition duration-75 group hover:bg-gray-100'">
                                    {{
                                        item.title
                                    }}
                                    <div class="text-gray-900 mr-1">
                                        <font-awesome-icon :icon="showSubDropdown ? 'fa-duotone fa-chevron-down' :'fa-duotone fa-chevron-right'"
                                                           class="h-3 w-3"></font-awesome-icon>
                                    </div>
                                </button>
                                <div v-if="showSubDropdown && item.title == subMenuOpen"
                                     class="z-10 font-sm bg-white divide-y divide-gray-100 rounded-lg  w-full absolute left-[140px] top-0 text-left shadow">
                                    <ul class="py-1 space-y-2">
                                        <li v-for="item in item.items">
                                            <a
                                                :href="route(item.routeName)"
                                                :class="'flex p-2 pl-4 w-full text-sm font-sm text-gray-700 rounded-lg transition duration-75 group hover:bg-gray-100'">{{
                                                    item.title
                                                }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <hr v-if="item.separator">
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import NavBarItem from "@/Components/NavBar/NavBarItem.vue";
import {ref, watch} from "vue";
import {Inertia} from "@inertiajs/inertia";


defineProps({
    iconNameCustom: String,
    items: Array,
    title: String,
})
const isMenuToggled = ref(false);
const showSubDropdown = ref(false);
const subMenuOpen = ref('');

const onViewListener = ref(null);
const dropdownMenu = ref(null);

const toggleDropdown = (event, title) => {

    event.preventDefault();


    isMenuToggled.value = !isMenuToggled.value;

};
const goLogout = () => {
    Inertia.post(route('logout'));
}

const checkClickOut = (event) => {
    if (isMenuToggled.value == true) {
        showSubDropdown.value == true ? showSubDropdown.value = false : null;
        isMenuToggled.value = false;
    }
}

</script>

<style scoped>
</style>
