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
import { Link, router } from '@inertiajs/vue3'

const activeIndex = ref(null)

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
        title: 'Products',
        icon: 'bx bx-category',
        children: [
            { label: 'Brand', route: 'dashboard' },
            { label: 'Chat', route: 'dashboard' },
        ],
    },
    {
        title: 'Subscription',
        icon: 'bx bx-category',
        children: [
            { label: 'Plan List', route: 'subscription-plans.index' },
        ],
    },
    {
        title: 'Billing & Subscription',
        icon: 'bx bx-category',
        children: [
            { label: 'Active Plan', route: 'subscription-plans.index' },
            { label: 'Payment History', route: 'subscription-plans.index' },
        ],
    },
    {
        title: 'Settings',
        icon: 'bx bx-cog',
        children: [
            { label: 'Business Setting', route: 'subscription-plans.index' },
            { label: 'Module', route: 'modules.index' },
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
