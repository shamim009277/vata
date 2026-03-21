<script lang="ts" setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    stats: Object,
    tables: Object,
    charts: Object,
    filters: Object
});

const filter = ref(props.filters?.filter || 'season');
const date = ref(props.filters?.date || '');
const month = ref(props.filters?.month || '');

const applyFilter = (type) => {
    filter.value = type;
    date.value = '';
    month.value = '';
    
    router.get(route('dashboard'), { filter: type }, { preserveState: true, preserveScroll: true });
};

const handleDateChange = () => {
    if(date.value) {
        filter.value = '';
        month.value = '';
        router.get(route('dashboard'), { date: date.value }, { preserveState: true, preserveScroll: true });
    }
};

const handleMonthChange = () => {
    if(month.value) {
        filter.value = '';
        date.value = '';
        router.get(route('dashboard'), { month: month.value }, { preserveState: true, preserveScroll: true });
    }
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('bn-BD', { style: 'currency', currency: 'BDT' }).format(amount || 0);
};

// Format number
const formatNumber = (num) => {
    return new Intl.NumberFormat('bn-BD').format(num || 0);
};

// Compute totals
const calculateTotal = (tableData, key) => {
    if (!tableData) return 0;
    return tableData.reduce((acc, item) => acc + (Number(item[key]) || 0), 0);
};

// --- Chart Configurations ---

// Helper to check if chart has any data
const hasChartData = (series) => {
    if (!series || !series.length) return false;
    
    // For single array of numbers (Pie)
    if (typeof series[0] === 'number' || typeof series[0] === 'string') {
        return series.some(val => Number(val) > 0);
    }
    
    // For nested arrays/objects (Radar, Bar)
    return series.some(s => s.data && s.data.some(val => Number(val) > 0));
};

// 1. Pie Chart Options
const pieChartOptions = computed(() => ({
    chart: { type: 'pie', height: 350 },
    labels: props.charts?.pie?.labels || [],
    colors: ['#0d6efd', '#17a00e'], // Blue for Challan, Green for Delivery
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val) => `${val.toFixed(1)}%` },
    tooltip: { y: { formatter: (val) => `${formatNumber(val)}` } }
}));
const pieChartSeries = computed(() => props.charts?.pie?.series?.map(Number) || []);

// 2. Radar Chart Options
const radarChartOptions = computed(() => ({
    chart: { type: 'radar', height: 350, toolbar: { show: false } },
    labels: props.charts?.radar?.labels || [],
    stroke: { width: 2 },
    fill: { opacity: 0.2 },
    markers: { size: 4 },
    colors: ['#f41127'],
    yaxis: { show: false },
    tooltip: { y: { formatter: (val) => `৳ ${formatNumber(val)}` } }
}));
const radarChartSeries = computed(() => props.charts?.radar?.series || []);

// 3. Bar Chart Options
const barChartOptions = computed(() => ({
    chart: { type: 'bar', height: 350, stacked: false, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' } },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: { categories: props.charts?.bar?.labels || [] },
    yaxis: { title: { text: 'পরিমাণ' } },
    fill: { opacity: 1 },
    colors: ['#0d6efd', '#17a00e'],
    tooltip: { y: { formatter: (val) => `${formatNumber(val)}` } }
}));
const barChartSeries = computed(() => props.charts?.bar?.series || []);

</script>
<template>
    <Head title="Dashboard" />
    <AppLayout1>
        <!-- 🔹 Filter Section -->
        <div class="row g-3 align-items-center mb-4">
            <!-- Left Side: Filter Buttons -->
            <div class="col-12 col-lg-8">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm shadow-sm d-flex align-items-center gap-1" 
                        :class="filter === 'today' ? 'btn-primary' : 'btn-white border'" 
                        @click="applyFilter('today')">
                        <i class='bx bx-calendar-check'></i> আজকের হিসাব
                    </button>

                    <button class="btn btn-sm shadow-sm d-flex align-items-center gap-1" 
                        :class="filter === 'last_7_days' ? 'btn-primary' : 'btn-white border'" 
                        @click="applyFilter('last_7_days')">
                        <i class='bx bx-time-five'></i> শেষ ৭ দিন
                    </button>

                    <button class="btn btn-sm shadow-sm d-flex align-items-center gap-1" 
                        :class="filter === 'last_15_days' ? 'btn-primary' : 'btn-white border'" 
                        @click="applyFilter('last_15_days')">
                        <i class='bx bx-calendar'></i> শেষ ১৫ দিন
                    </button>

                    <button class="btn btn-sm shadow-sm d-flex align-items-center gap-1" 
                        :class="filter === 'season' ? 'btn-primary' : 'btn-white border'" 
                        @click="applyFilter('season')">
                        <i class='bx bx-layer'></i> সিজন অনুযায়ী
                    </button>
                </div>
            </div>

            <!-- Right Side: Custom Date/Month -->
            <div class="col-12 col-lg-4">
                <div class="d-flex gap-2 justify-content-lg-end">
                    <div class="input-group input-group-sm w-auto shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class='bx bx-calendar text-primary'></i></span>
                        <input type="date" class="form-control border-start-0 ps-0" v-model="date" @change="handleDateChange">
                    </div>
                    <div class="input-group input-group-sm w-auto shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class='bx bx-calendar-event text-success'></i></span>
                        <input type="month" class="form-control border-start-0 ps-0" v-model="month" @change="handleMonthChange">
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Stats Cards Section -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-0 border-start border-4 border-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary font-14">মোট বিক্রয় (ভাড়া সহ)</p>
                                <h4 class="my-1 text-info font-20 fw-bold">{{ formatCurrency(stats?.total_sales) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                <i class='bx bxs-cart'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 border-start border-4 border-danger shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary font-14">নগদ বিক্রয়</p>
                                <h4 class="my-1 text-danger font-20 fw-bold">{{ formatCurrency(stats?.cash_sales) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                <i class='bx bxs-wallet'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 border-start border-4 border-success shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary font-14">বাকি বিক্রয়</p>
                                <h4 class="my-1 text-success font-20 fw-bold">{{ formatCurrency(stats?.due_sales) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                <i class='bx bxs-bar-chart-alt-2'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 border-start border-4 border-warning shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary font-14">মোট খরচ/পেমেন্ট</p>
                                <h4 class="my-1 text-warning font-20 fw-bold">{{ formatCurrency(stats?.total_payment) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                <i class='bx bxs-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Charts Section -->
        <div class="row g-3 mb-4">
            <!-- Pie Chart: Challan vs Delivery -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm radius-10 h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 fw-bold"><i class='bx bx-pie-chart-alt text-primary me-1'></i>চালান বনাম ডেলিভারি</h6>
                    </div>
                    <div class="card-body">
                        <VueApexCharts v-if="hasChartData(pieChartSeries)" type="pie" height="350" :options="pieChartOptions" :series="pieChartSeries" />
                        <div v-else class="d-flex justify-content-center align-items-center text-muted" style="height: 350px;">
                            <p>কোন তথ্য পাওয়া যায়নি</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Radar Chart: Sales & Payments -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm radius-10 h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 fw-bold"><i class='bx bx-radar text-danger me-1'></i>বিক্রয় ও পেমেন্ট চিত্র</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <VueApexCharts v-if="hasChartData(radarChartSeries)" type="radar" height="350" :options="radarChartOptions" :series="radarChartSeries" />
                        <div v-else class="d-flex justify-content-center align-items-center text-muted w-100" style="height: 350px;">
                            <p>কোন তথ্য পাওয়া যায়নি</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart: Monthly Comparison -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm radius-10 h-100">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 fw-bold"><i class='bx bx-bar-chart text-success me-1'></i>মাসিক চালান ও ডেলিভারি</h6>
                    </div>
                    <div class="card-body">
                        <VueApexCharts v-if="hasChartData(barChartSeries)" type="bar" height="350" :options="barChartOptions" :series="barChartSeries" />
                        <div v-else class="d-flex justify-content-center align-items-center text-muted" style="height: 350px;">
                            <p>কোন তথ্য পাওয়া যায়নি</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Tables Grid Section -->
        <div class="row g-3">
            <!-- Invoice Table -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-info">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-file text-info me-1'></i>চালান বিবরণ</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-gradient-scooter text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">শ্রেণি</th>
                                        <th>চালান</th>
                                        <th class="text-end">পরিমাণ</th>
                                        <th class="text-end pe-3">মোট মূল্য</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.invoice" :key="index">
                                        <td class="ps-3">{{ item.category }}</td>
                                        <td>{{ item.invoice_count }}</td>
                                        <td class="text-end">{{ formatNumber(item.total_quantity) }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatCurrency(item.total_amount) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.invoice?.length">
                                        <td colspan="4" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.invoice?.length">
                                    <tr>
                                        <td class="ps-3" colspan="2">মোট</td>
                                        <td class="text-end">{{ formatNumber(calculateTotal(tables.invoice, 'total_quantity')) }}</td>
                                        <td class="text-end pe-3">{{ formatCurrency(calculateTotal(tables.invoice, 'total_amount')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Table -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-danger">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-money text-danger me-1'></i>খরচ/পেমেন্ট</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-gradient-bloody text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">খাত</th>
                                        <th class="text-end">পরিমাণ</th>
                                        <th class="text-end pe-3">পরিশোধ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.payment" :key="index">
                                        <td class="ps-3">{{ item.head_name }}</td>
                                        <td class="text-end">{{ formatCurrency(item.amount) }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatCurrency(item.paid) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.payment?.length">
                                        <td colspan="3" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.payment?.length">
                                    <tr>
                                        <td class="ps-3">মোট</td>
                                        <td class="text-end">{{ formatCurrency(calculateTotal(tables.payment, 'amount')) }}</td>
                                        <td class="text-end pe-3">{{ formatCurrency(calculateTotal(tables.payment, 'paid')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Production Table -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-primary">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-layer text-primary me-1'></i>প্রোডাকশন</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-primary text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">মাঠ</th>
                                        <th class="text-end pe-3">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.production" :key="index">
                                        <td class="ps-3">{{ item.field_name }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatNumber(item.quantity) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.production?.length">
                                        <td colspan="2" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.production?.length">
                                    <tr>
                                        <td class="ps-3">মোট</td>
                                        <td class="text-end pe-3">{{ formatNumber(calculateTotal(tables.production, 'quantity')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Table -->
            <div class="col-12 col-md-6 col-xl-4">
               <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-warning">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-truck text-warning me-1'></i>ডেলিভারি</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-gradient-blooker text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">শ্রেণি</th>
                                        <th class="text-end pe-3">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.delivery" :key="index">
                                        <td class="ps-3">{{ item.item_name }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatNumber(item.quantity) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.delivery?.length">
                                        <td colspan="2" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.delivery?.length">
                                    <tr>
                                        <td class="ps-3">মোট</td>
                                        <td class="text-end pe-3">{{ formatNumber(calculateTotal(tables.delivery, 'quantity')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load Table -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-success">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-up-arrow-circle text-success me-1'></i>লোড</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-gradient-ohhappiness text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">বিবরণ</th>
                                        <th class="text-end pe-3">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.load" :key="index">
                                        <td class="ps-3">{{ item.item_name }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatNumber(item.quantity) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.load?.length">
                                        <td colspan="2" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.load?.length">
                                    <tr>
                                        <td class="ps-3">মোট</td>
                                        <td class="text-end pe-3">{{ formatNumber(calculateTotal(tables.load, 'quantity')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unload Table -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card shadow-sm radius-10 h-100 border-0 border-top border-4 border-secondary">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class='bx bx-down-arrow-circle text-secondary me-1'></i>আনলোড</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-hover mb-0 align-middle table-sm table-striped">
                                <thead class="bg-secondary text-white sticky-top">
                                    <tr>
                                        <th class="ps-3">শ্রেণি</th>
                                        <th class="text-end pe-3">পরিমাণ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in tables?.unload" :key="index">
                                        <td class="ps-3">{{ item.item_name }}</td>
                                        <td class="text-end pe-3 fw-bold">{{ formatNumber(item.total_quantity) }}</td>
                                    </tr>
                                    <tr v-if="!tables?.unload?.length">
                                        <td colspan="2" class="text-center text-muted py-3">কোন তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold" v-if="tables?.unload?.length">
                                    <tr>
                                        <td class="ps-3">মোট</td>
                                        <td class="text-end pe-3">{{ formatNumber(calculateTotal(tables.unload, 'total_quantity')) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout1>
</template>

<style scoped>
.radius-10 {
    border-radius: 10px !important;
}

.widgets-icons-2 {
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ededed;
    font-size: 22px;
}

.bg-gradient-scooter {
    background: #17ead9;
    background: -webkit-linear-gradient(45deg, #17ead9, #6078ea) !important;
    background: linear-gradient(45deg, #17ead9, #6078ea) !important;
}

.bg-gradient-bloody {
    background: #f54ea2;
    background: -webkit-linear-gradient(45deg, #f54ea2, #ff7676) !important;
    background: linear-gradient(45deg, #f54ea2, #ff7676) !important;
}

.bg-gradient-ohhappiness {
    background: #00b09b;
    background: -webkit-linear-gradient(45deg, #00b09b, #96c93d) !important;
    background: linear-gradient(45deg, #00b09b, #96c93d) !important;
}

.bg-gradient-blooker {
    background: #ffdf40;
    background: -webkit-linear-gradient(45deg, #ffdf40, #ff8359) !important;
    background: linear-gradient(45deg, #ffdf40, #ff8359) !important;
}

.font-20 {
    font-size: 20px;
}
.font-14 {
    font-size: 14px;
}
</style>
