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
                        <li v-for="(child, i) in menu.children" :key="i">
                            <Link :href="route(child.route)"><i class="bx bx-right-arrow-alt"></i> {{ child.label }}</Link>
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
        title: 'ড্যাশবোর্ড',
        icon: 'bx bx-home-circle',
        route: 'dashboard',
    },
    {
        title: 'Subscription',
        icon: 'bx bx-category',
        children: [
            { label: 'Plan List', route: 'subscription-plans.index' },
        ],
    },
    {
        title: 'চালান',
        icon: 'bx bx-receipt',
        children: [
            { label: 'আজকের চালান', route: 'invoices.index' },
            { label: 'অগ্রিম চালান', route: 'invoice.advance' },
            { label: 'সব চালান', route: 'invoice.all' },
        ],
    },
    {
        title: 'কাঁচা ইট প্রোডাকশন',
        icon: 'bx bx-box',
        children: [
            { label: 'আজকের প্রোডাকশন', route: 'row-productions.index' },
            { label: 'সব প্রোডাকশন', route: 'row-productions.all' },
        ],
    },
    {
        title: 'ডেলিভারি',
        icon: 'bx bx-rocket',
        children: [
            { label: 'আজকের ডেলিভারি', route: 'deliveries.index' },
            { label: 'আজ ডেলিভারি যাবে', route: 'invoice.advance' },
            { label: 'সব ডেলিভারি লিস্ট', route: 'invoice.all' },
        ],
    },
    {
        title: 'পেমেন্ট খাতা',
        icon: 'bx bx-bar-chart',
        route: 'payment-khata.index',
    },
    {
        title: 'লোড খাতা',
        icon: 'bx bx-bar-chart',
        route: 'loads.index',
    },
    {
        title: 'সেটিং',
        icon: 'bx bx-cog',
        children: [
            { label: 'শ্রেণি এবং রেট', route: 'items.index' },
            { label: 'খতিয়ান', route: 'payment_head.index' },
            { label: 'স্টক লিস্ট', route: 'stock-lists.index' },
            { label: 'মাঠ লিস্ট', route: 'field-lists.index' },
            { label: 'খতিয়ান', route: 'payment_head.index' },
            { label: 'ভাটার তথ্য', route: 'business-store.index' },
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
