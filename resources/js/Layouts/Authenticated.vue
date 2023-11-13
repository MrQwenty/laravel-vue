<template>
    <n-config-provider :locale="itIT" :date-locale="dateItIT" :theme-overrides="themeOverrides">
        <div class="min-h-screen ">
            <n-layout class="h-screen relative">
                <n-layout-header class="h-fit" bordered>
                    <TopBar></TopBar>
                </n-layout-header>
                <n-layout has-sider bordered position="absolute" style="top: 40px;">
                    <n-layout-sider
                        bordered
                        collapse-mode="width"
                        :width="240"
                        :collapsed="collapsed"
                        show-trigger
                        @collapse="collapsed = true"
                        @expand="collapsed = false"
                    >
                        <NavMenu :menu="pageProps.menu"></NavMenu>
                    </n-layout-sider>
                    <n-layout>
                        <div class="  h-[100%]">
                            <div class="flex flex-col bg-gray-100">
                                <div class="grow flex-auto  my-8 mx-6  ">
                                    <header v-if="$slots.header"
                                            class="flex justify-between items-center mb-[28px]">
                                        <slot name="header"></slot>
                                        <slot name="actions"></slot>
                                    </header>
                                    <main>

                                        <slot/>

                                    </main>
                                </div>
                            </div>
                        </div>
                    </n-layout>
                </n-layout>
            </n-layout>
        </div>
    </n-config-provider>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue';
import BreezeApplicationLogo from '@/Components/ApplicationLogo.vue';
import BreezeDropdown from '@/Components/Dropdown.vue';
import BreezeDropdownLink from '@/Components/DropdownLink.vue';
import BreezeNavLink from '@/Components/NavLink.vue';
import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import {Link, usePage} from '@inertiajs/inertia-vue3';
import TopBar from "@/Components/NavBar/TopBar.vue";
import Footer from "@/Components/Footer/Footer.vue"
import {NConfigProvider, NLayout, NLayoutSider, NLayoutHeader, NDialogProvider} from 'naive-ui'
import {itIT, dateItIT} from "naive-ui";
import NavMenu from "@/Components/NavBar/NavMenu.vue";

const showingNavigationDropdown = ref(false);
const userSettings = ref(false)
const pageProps = computed(() => {
    return usePage().props.value;
})

const collapsed = ref(false);

/**
 * Use this for type hints under js file
 * @type import('naive-ui').GlobalThemeOverrides
 */
const themeOverrides = {
    common: {
        primaryColor: '#61c3d7'
    },
    // Button: {
    //     textColor: '#FF0000'
    // },
    // Select: {
    //     peers: {
    //         InternalSelection: {
    //             textColor: '#FF0000'
    //         }
    //     }
    // }
    // ...
}

</script>
<style scoped>
 :deep(.n-dropdown-option){
     @apply ml-[100px]
 }
</style>


