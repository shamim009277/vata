<template>
    <div class="sidebar-wrapper" data-simplebar="true">
        <div class="sidebar-header">
            <div>
                <img :src="AppLogo" class="logo-icon" alt="logo icon">
            </div>
            <div>
                <h4 class="logo-text">Rocker</h4>
            </div>
            <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
            </div>
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
                        <li v-for="(child, i) in menu.children" :key="i">
                            <Link :href="route(child.route)"><i class="bx bx-right-arrow-alt"></i> {{ child.label }}
                            </Link>
                        </li>
                    </ul>
                </template>

                <!-- If no children -->
                <template v-else>
                    <Link :href="route(menu.route)">
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

import { ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const activeIndex = ref(null)
const page = usePage();

const t = (group, key) => page.props.translations[group][key] || key;

const toggle = (index) => {
    activeIndex.value = activeIndex.value === index ? null : index
}

// Sidebar Menu Items
const menus = [
    {
        title: 'Dashboard',
        icon: 'bx bx-home-circle',
        route: 'dashboard',
    },
    {
        title: t('menu', 'purchase'),
        icon: 'bx bx-category',
        children: [
            { label: 'Brand', route: 'dashboard' },
            { label: 'Chat', route: 'dashboard' },
        ],
    },
    {
        title: 'Products',
        icon: 'bx bx-category',
        children: [
            { label: 'Brand', route: 'dashboard' },
            { label: 'Chat', route: 'dashboard' },
        ],
    },
    {
        title: t('menu', 'settings'),
        icon: 'bx bx-cog',
        children: [
            { label: t('menu', 'unit'), route: 'units.index' },
            { label: t('menu', 'customer'), route: 'customers.index' },
            { label: t('menu', 'supplier'), route: 'suppliers.index' },
            { label: t('menu', 'raw_material'), route: 'raw-materials.index' },
            { label: t('menu', 'item'), route: 'items.index' },
        ],
    },
]
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
