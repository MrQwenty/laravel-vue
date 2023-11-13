<script setup>
import BreezeApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link, usePage} from '@inertiajs/inertia-vue3';
import {ref, watch} from "vue";

const showFlashMessage = ref(true);
watch(() => usePage().props.value.flash.message, () => {
        showFlashMessage.value = true;
        setTimeout(() => {
            showFlashMessage.value = false;
            usePage().props.value.flash.message = null
        }, 3000);
    }
)
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div
            class=" bg-primaryColor-100 border-t-4 border-primaryColor-500 rounded-b text-primaryColor-800 px-4 py-3 shadow-md my-3"
            role="alert" v-if="$page.props.flash?.message && showFlashMessage">
            <div class="flex">
                <div>
                    <p class="text-sm">{{ $page.props.flash.message }}</p>
                </div>
            </div>
        </div>
        <div>
            <Link href="/">
                <BreezeApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
