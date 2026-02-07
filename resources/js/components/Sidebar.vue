<template>
    <div class="sidebar-wrapper" data-simplebar="true">
        <div class="sidebar-header">
            <div><img :src="AppLogo" @click="$emit('toggle-sidebar')" class="logo-icon" alt="logo icon"></div>
            <div><h4 class="logo-text">Rocker</h4></div>
            <div class="toggle-icon ms-auto" @click="$emit('toggle-sidebar')"><i class='bx bx-arrow-to-left'></i></div>
        </div>

        <ul class="metismenu" id="menu">
            <li v-for="(menu, index) in menus" :key="index" :class="{ 'mm-active': activeIndex === index }">
                <!-- If has children (submenu) -->
                <template v-if="menu.children && menu.children.length">
                    <a href="javascript:;" class="has-arrow" :aria-expanded="activeIndex === index ? 'true' : 'false'"
                        @click="toggle(index)">
                        <div class="parent-icon"><i :class="menu.icon"></i></div>
                        <div class="menu-title">{{ menu.title }}</div>
                    </a>
                    <ul class="mm-collapse" :class="{ 'mm-show': activeIndex === index }">
                        <li v-for="(child, i) in menu.children" :key="i" :class="{ 'mm-active': isRouteActive(child) }">
                            <a v-if="child.url" :href="child.url" target="_blank"><i class="bx bx-right-arrow-alt"></i> {{ child.title }}</a>
                            <Link v-else :href="route(child.route)"><i class="bx bx-right-arrow-alt"></i> {{ child.title }}</Link>
                        </li>
                    </ul>
                </template>

                <!-- If no children -->
                <template v-else>
                    <a v-if="menu.url" :href="menu.url" target="_blank">
                        <div class="parent-icon"><i :class="menu.icon"></i></div>
                        <div class="menu-title">{{ menu.title }}</div>
                    </a>
                    <Link v-else :href="route(menu.route)">
                        <div class="parent-icon"><i :class="menu.icon"></i></div>
                        <div class="menu-title">{{ menu.title }}</div>
                    </Link>
                </template>
            </li>
        </ul>
        <!--end navigation-->
    </div>
</template>

<script setup>
import AppLogo from '@/image/logo-icon.png';
import { ref, computed, onMounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js';

const page = usePage()
const activeIndex = ref(null)

const toggle = (index) => {
    activeIndex.value = activeIndex.value === index ? null : index
}

// Sidebar Menu Items
const menus = computed(() => page.props.menus)

const isActive = (menu) => {
    if (menu.children) {
        return menu.children.some(child => isRouteActive(child));
    }
    return isRouteActive(menu);
}

const isRouteActive = (item) => {
    // Check by URL match if available
    if (item.url) {
        return page.url === item.url;
    }
    // Check by route match if available (assumes Ziggy's route() is global)
    if (item.route && typeof route === 'function') {
        try {
            return route().current(item.route);
        } catch (e) {
            return false;
        }
    }
    return false;
}

const checkActiveMenu = () => {
    if (!menus.value) return;
    const index = menus.value.findIndex(menu => isActive(menu));
    if (index !== -1) {
        activeIndex.value = index;
    }
}

onMounted(() => {
    checkActiveMenu();
})

watch(() => page.url, () => {
    checkActiveMenu();
})
</script>

<style scoped>
.submenu {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.5s ease-in-out;
}
.submenu.open {
    max-height: 500px;
}
</style>
