<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import VehicleNavbar from '@/components/VehicleNavbar.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: Object,
    filters: Object,
});

const filterType = ref(props.filters?.filter_type || 'all');
const date = ref(props.filters?.date || '');
const month = ref(props.filters?.month || '');

const applyFilter = (type) => {
    filterType.value = type;
    date.value = '';
    month.value = '';
    
    router.get(route('vehicles.dashboard'), {
        filter_type: type
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const onDateChange = () => {
    filterType.value = '';
    month.value = '';
    router.get(route('vehicles.dashboard'), {
        date: date.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const onMonthChange = () => {
    filterType.value = '';
    date.value = '';
    router.get(route('vehicles.dashboard'), {
        month: month.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};
</script>

<template>
    <Head title="Vehicle Dashboard" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-car"></i> গাড়ির হিসাব
                                </a>
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <VehicleNavbar />
                        
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn" :class="filterType === 'today' ? 'btn-primary' : 'btn-outline-primary'" @click="applyFilter('today')">আজকের হিসাব</button>
                                        <button type="button" class="btn" :class="filterType === 'last_7_days' ? 'btn-success' : 'btn-outline-success'" @click="applyFilter('last_7_days')">গত ৭ দিনের হিসাব</button>
                                        <button type="button" class="btn" :class="filterType === 'last_15_days' ? 'btn-info' : 'btn-outline-info'" @click="applyFilter('last_15_days')">গত ১৫ দিনের হিসাব</button>
                                        <button type="button" class="btn" :class="filterType === 'all' ? 'btn-warning' : 'btn-outline-warning'" @click="applyFilter('all')">সর্বমোট হিসাব</button>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <input type="month" class="form-control" v-model="month" @change="onMonthChange" placeholder="মাস">
                                        <input type="date" class="form-control" v-model="date" @change="onDateChange" placeholder="তারিখ">
                                    </div>
                                </div>

                                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-3 border-info">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট গাড়ি</p>
                                                        <h4 class="my-1 text-info">{{ stats.total_vehicles }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                                        <i class='bx bx-bus'></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-3 border-danger">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট ট্রিপ</p>
                                                        <h4 class="my-1 text-danger">{{ stats.total_trips }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                                        <i class='bx bx-trip'></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-3 border-success">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট আয়</p>
                                                        <h4 class="my-1 text-success">{{ stats.total_income }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                                        <i class='bx bx-money'></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-3 border-warning">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট ব্যায়</p>
                                                        <h4 class="my-1 text-warning">{{ stats.total_expense }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                                        <i class='bx bx-wallet'></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
