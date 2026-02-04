<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentRoute = computed(() => page.url);

const isActive = (routePattern) => {
    return routePattern === route().current();
};
</script>

<template>
    <ul class="nav nav-tabs nav-success" role="tablist">
        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': isActive('vehicles.dashboard') }" :href="route('vehicles.dashboard')">
                <div class="d-flex align-items-center">
                    <div class="tab-icon"><i class="bx bx-home-circle font-18 me-1"></i></div>
                    <div class="tab-title">ড্যাশবোর্ড</div>
                </div>
            </Link>
        </li>
        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': isActive('vehicles.index') }" :href="route('vehicles.index')">
                <div class="d-flex align-items-center">
                    <div class="tab-icon"><i class="bx bx-bus font-18 me-1"></i></div>
                    <div class="tab-title">গাড়ি লিস্ট</div>
                </div>
            </Link>
        </li>
        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': isActive('vehicle-trips.index') }" :href="route('vehicle-trips.index')">
                <div class="d-flex align-items-center">
                    <div class="tab-icon"><i class="bx bx-trip font-18 me-1"></i></div>
                    <div class="tab-title">গাড়ির ট্রিপ</div>
                </div>
            </Link>
        </li>
        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': isActive('vehicle-fuels.index') }" :href="route('vehicle-fuels.index')">
                <div class="d-flex align-items-center">
                    <div class="tab-icon"><i class="bx bx-gas-pump font-18 me-1"></i></div>
                    <div class="tab-title">গাড়ির ফুয়েল কস্ট</div>
                </div>
            </Link>
        </li>
        <li class="nav-item" role="presentation">
            <Link class="nav-link" :class="{ 'active': isActive('vehicle-services.index') }" :href="route('vehicle-services.index')">
                <div class="d-flex align-items-center">
                    <div class="tab-icon"><i class="bx bx-wrench font-18 me-1"></i></div>
                    <div class="tab-title">গাড়ির সার্ভিস</div>
                </div>
            </Link>
        </li>
    </ul>
</template>
