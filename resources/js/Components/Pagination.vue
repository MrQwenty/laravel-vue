<template>
    <div v-if="links.length > 3" class="flex flex-wrap w-full justify-end mt-4" dusk="pagination">
        <n-pagination class="w-full justify-between" :item-count="itemCount" :on-update:page="goToPage" :page-size="props.resource.per_page" :page="resource.current_page">
            <template #prefix="{ itemCount, startIndex, endIndex }" class="w-full">
                Da {{ props.resource.from }} a {{ props.resource.to }} - Totale: {{ itemCount }}
            </template>
        </n-pagination>
    </div>
</template>

<script setup>

import {computed} from "vue";

const props = defineProps(['resource']);

const links = computed(() => {
    return props.resource.links;
})

const itemCount = computed(() => {
    return props.resource.total;
})

const goToPage = function (e) {
    const index = e;

    let destination = "";

    if (index == 0) {
        destination = links.value[0];
    } else if (index >= links.value.length) {
        destination = links.value[links.value.length];
    } else {
        destination = links.value[index];
    }

    window.location.href = destination.url;
}

</script>

<style scoped>

:deep(.n-pagination-prefix) {
    width: 100%;
}

</style>
