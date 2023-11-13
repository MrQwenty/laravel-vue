<template>
    <div class="">
        <nav class="bg-white border-gray-200 h-[39px]">
            <div class="h-full max-w-full flex flex-wrap items-center justify-between mx-auto px-4">
                <div class="flex flex-wrap content-center justify-between w-full">
                    <a href="https://www.shock-wave.it/" class="l flex items-center">
                        <img src="https://www.shock-wave.it/wp-content/uploads/2022/06/logo_shock_wave_nero.png"
                             class="h-5 mr-3" alt="ShockWave"
                        />
                    </a>
                    <div class="flex gap-x-2 justify-right">
                        <n-dropdown trigger="click" :options="optionsLang" @select="handleSelectLang">
                            <n-button size="small" type="primary" ghost>
                                <span :class="dropdownIcon"></span>
                            </n-button>
                        </n-dropdown>
                        <n-dropdown id="userDropdown" trigger="click" :options="options"
                                    @select="handleSelect">
                            <n-button size="small">
                                <font-awesome-icon icon="fa-duotone fa-user"></font-awesome-icon>
                            </n-button>
                        </n-dropdown>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>

<script setup>
import NavBarItem from "@/Components/NavBar/NavBarItem.vue";
import NavBarItemDropdown from "@/Components/NavBar/NavBarItemDropdown.vue";
import {usePage} from "@inertiajs/inertia-vue3";
import {NAvatar, NButton, NDropdown, NLayoutHeader, NMenu, NText} from "naive-ui";
import {computed, h, nextTick, onMounted, ref, useAttrs} from "vue";
import {Inertia} from "@inertiajs/inertia";
onMounted(() => {

    nextTick().then(() => {
        let langs = usePage().props.value.langs;
        langs.forEach((lang) => {
            if(usePage().props.value.currentLang == lang.value){
                dropdownIcon.value = lang.icon
            }
            optionsLang.value.push({
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
    });
})
const renderCustomHeader = () => {
    return h(
        'div',
        {
            style: 'display: flex; align-items: center; padding: 8px 12px;'
        },
        [
            h(NAvatar, {
                round: true,
                style: 'margin-right: 12px;',
                src: 'https://07akioni.oss-cn-beijing.aliyuncs.com/demo1.JPG'
            }),
            h('div', null, [
                h('div', null, [
                    h(NText, {depth: 2}, {default: () => usePage().props.value.auth.user.name})
                ]),
                h('div', {style: 'font-size: 12px;'}, [
                    h(NText, {depth: 3}, {default: () => usePage().props.value.auth.user.email})
                ])
            ])
        ]
    )
}

const dropdownIcon = ref(null);
const optionsLang = ref([]);

const options = [
    {
        key: 'header',
        type: 'render',
        render: renderCustomHeader
    },
    {
        key: 'header-divider',
        type: 'divider'
    },
    {
        label: 'Disconnetti',
        key: 'logout'
    }
];

const handleSelect = function (key, option) {
    if (key === 'logout') {
        Inertia.post(route('logout'));
    }
}
const handleSelectLang = (a) => {
    Inertia.get(route('lang',{
        lang :a
    }),{
        lang : a,
    }, {
        onFinish: () => {
            window.location.reload();
        }
    })
}

</script>


<style scoped lang="scss">
ul {
    list-style-type: none;
}

</style>
