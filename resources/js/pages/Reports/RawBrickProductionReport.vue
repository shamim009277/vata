<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    productions: Array,
    filters: Object,
    seasons: Array,
    business_store: Object,
});

const form = ref({
    date: props.filters?.date || '',
    month: props.filters?.month || '',
    season: props.filters?.season || '',
});

const totalQty = computed(() => (props.productions || []).reduce((acc, p) => acc + (Number(p?.quantity) || 0), 0));

const applyFilters = () => {
    router.get(route('reports.raw_brick_production'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    form.value = { date: '', month: '', season: '' };
    applyFilters();
};

const downloadPdf = () => {
    const params = new URLSearchParams(form.value).toString();
    window.open(route('reports.raw_brick_production.pdf') + '?' + params, '_blank');
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="কাঁচা ইট প্রোডাকশন রিপোর্ট" />

    <!-- Print Layout (Visible only in print) -->
    <div class="print-layout d-none d-print-block">
        <div class="watermark" v-if="business_store">
            {{ business_store.store_name }}
        </div>
        <div class="text-center mb-4">
            <div v-if="business_store">
                <h4 class="mb-0 fw-bold">{{ business_store.store_name }}</h4>
                <p class="mb-0 small">{{ business_store.address }}</p>
                <p class="mb-2 small">Mobile: {{ business_store.phone }}</p>
            </div>
            <h6 class="mt-2 text-decoration-underline">কাঁচা ইট প্রোডাকশন রিপোর্ট</h6>
            <p class="small">Print Date: {{ new Date().toLocaleDateString() }}</p>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th style="width: 5%">SL</th>
                        <th>তারিখ</th>
                        <th>মাঠ</th>
                        <th class="text-end">পরিমাণ</th>
                        <th>সিজন</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(p, index) in productions" :key="p.id">
                        <td>{{ index + 1 }}</td>
                        <td>{{ p.product_date ? new Date(p.product_date).toLocaleDateString() : '' }}</td>
                        <td>{{ p.field?.name || '-' }}</td>
                        <td class="text-end">{{ p.quantity }}</td>
                        <td>{{ p.season || '-' }}</td>
                    </tr>
                    <tr v-if="productions.length === 0">
                        <td colspan="5" class="text-center">কোনো ডাটা পাওয়া যায়নি</td>
                    </tr>
                    <tr v-else style="background-color: #e8e8e8; font-weight: bold;">
                        <td colspan="3" class="text-end">মোট পরিমাণ:</td>
                        <td class="text-end">{{ totalQty }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Screen Layout (Hidden in print) -->
    <AppLayout1 class="d-print-none">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">কাঁচা ইট প্রোডাকশন রিপোর্ট</h6>
                        <div class="d-flex flex-wrap gap-2 mt-3 align-items-end">
                            <div class="form-group">
                                <label>তারিখ</label>
                                <input type="date" v-model="form.date" class="form-control form-control-sm" placeholder="তারিখ" @change="form.month = ''">
                            </div>
                            <div class="form-group">
                                <label>মাস</label>
                                <input type="month" v-model="form.month" class="form-control form-control-sm" placeholder="মাস" @change="form.date = ''">
                            </div>
                            <div class="form-group">
                                <label>সিজন</label>
                                <select v-model="form.season" class="form-select form-select-sm">
                                    <option value="">সকল সিজন</option>
                                    <option v-for="season in seasons" :key="season" :value="season">{{ season }}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button @click="applyFilters" class="btn btn-primary btn-sm"><i class="bx bx-filter"></i> ফিল্টার</button>
                                <button @click="resetFilters" class="btn btn-secondary btn-sm ms-2"><i class="bx bx-reset"></i> রিসেট</button>
                            </div>
                            <div class="ms-auto">
                                <button @click="printReport" class="btn btn-success btn-sm me-2"><i class="bx bx-printer"></i> প্রিন্ট</button>
                                <button @click="downloadPdf" class="btn btn-danger btn-sm"><i class="bx bxs-file-pdf"></i> PDF</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>তারিখ</th>
                                        <th>মাঠ</th>
                                        <th class="text-end">পরিমাণ</th>
                                        <th>সিজন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(p, index) in productions" :key="p.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ p.product_date ? new Date(p.product_date).toLocaleDateString() : '' }}</td>
                                        <td>{{ p.field?.name || '-' }}</td>
                                        <td class="text-end">{{ p.quantity }}</td>
                                        <td>{{ p.season || '-' }}</td>
                                    </tr>
                                    <tr v-if="productions.length === 0">
                                        <td colspan="5" class="text-center">কোনো ডাটা পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>

<style>
@media print {
    @page { size: auto; margin: 5mm; }
    body { margin: 0; padding: 0; background: white; overflow: visible; }
    .print-layout { width: 100%; background: white; }
    .print-layout table { width: 100% !important; border-collapse: collapse !important; font-size: 12px; }
    .print-layout th, .print-layout td { border: 1px solid #000 !important; padding: 4px 8px !important; }
    .print-layout th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 5rem;
        font-weight: bold;
        color: #000;
        opacity: 0.1;
        z-index: -1;
        white-space: nowrap;
        pointer-events: none;
        width: 100%;
        text-align: center;
        text-transform: uppercase;
    }
}
</style>
