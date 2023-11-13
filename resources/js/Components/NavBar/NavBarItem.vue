<template>
    <div>
        <a v-if="routeName == null"  @click="$emit('click:toggle', $event,title)"
           class="nav-bar-item">
            <font-awesome-icon :icon="iconNameCustom" class="fa-icon"></font-awesome-icon>
            <span class="title">{{ title }}</span>
        </a>
        <a v-else :href="route(routeName)"
           class="nav-bar-item">
            <font-awesome-icon :icon="iconNameCustom" class="fa-icon"></font-awesome-icon>
            <span class="title">{{ title }}</span>
        </a>
    </div>
</template>

<script setup>

defineProps({
    routeName: String,
    title: String,
    iconNameCustom : String
});

</script>

<style scoped>

.nav-bar-item {
    @apply flex items-center p-2 text-sm text-gray-400 rounded-lg hover:bg-gray-100  hover:text-primaryColor
}

.nav-bar-item .fa-icon {
    @apply w-5 h-5
}

.nav-bar-item .title {
    @apply ml-2
}

</style>
