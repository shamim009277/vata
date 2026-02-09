<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    customer: Object,
    invoices: Array,
});

const form = useForm({
    customer_id: props.customer?.id || '',
    invoice_no: '',
    delivery_date: new Date().toISOString().substr(0, 10),
    driver_name: '',
    truck_number: '',
    note: '',
    items: [],
});

const isLoading = ref(false);

const fetchInvoiceItems = async () => {
    if (!form.invoice_no) {
        form.items = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(route('delivery-challans.invoice-items', form.invoice_no));
        form.items = response.data.map(item => ({
            item_id: item.item_id,
            name: item.item.name,
            order_qty: item.quantity,
            prev_delivered: item.delivery_quantity || 0,
            current_delivery: 0, // Default to 0
            max_deliverable: item.quantity - (item.delivery_quantity || 0)
        }));
    } catch (error) {
        console.error('Error fetching items:', error);
        alert('চালান তথ্য লোড করতে সমস্যা হয়েছে');
    } finally {
        isLoading.value = false;
    }
};

const submit = () => {
    // Filter out items with 0 delivery quantity to avoid empty records? 
    // Or send all and handle in backend.
    // Let's validate at least one item has quantity > 0
    const hasQuantity = form.items.some(item => item.current_delivery > 0);
    if (!hasQuantity) {
        alert('অন্তত একটি আইটেমের ডেলিভারি পরিমাণ দিন');
        return;
    }
    
    form.post(route('delivery-challans.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="ডেলিভারি চালান তৈরি করুন" />

    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title text-primary">নতুন ডেলিভারি চালান</h5>
                            <Link :href="route('customers.show', customer.id)" class="btn btn-sm btn-secondary">
                                <i class="bx bx-arrow-back"></i> ফিরে যান
                            </Link>
                        </div>
                        <hr />
                        
                        <form @submit.prevent="submit">
                            <div class="row g-3">
                                <!-- Customer Info -->
                                <div class="col-md-6">
                                    <label class="form-label">কাস্টমার</label>
                                    <input type="text" class="form-control" :value="customer?.name" disabled readonly>
                                    <input type="hidden" v-model="form.customer_id">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">তারিখ</label>
                                    <input type="date" class="form-control" v-model="form.delivery_date" required>
                                </div>

                                <!-- Driver Info -->
                                <div class="col-md-6">
                                    <label class="form-label">ড্রাইভার নাম</label>
                                    <input type="text" class="form-control" v-model="form.driver_name" placeholder="ড্রাইভারের নাম লিখুন">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ট্রাক নম্বর</label>
                                    <input type="text" class="form-control" v-model="form.truck_number" placeholder="ট্রাক নম্বর লিখুন">
                                </div>

                                <!-- Invoice Selection -->
                                <div class="col-12">
                                    <label class="form-label">চালান / মেমো নং নির্বাচন করুন</label>
                                    <select class="form-select" v-model="form.invoice_no" @change="fetchInvoiceItems" required>
                                        <option value="">মেমো নির্বাচন করুন...</option>
                                        <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.invoice_no">
                                            #{{ invoice.invoice_no }} - {{ invoice.invoice_date }} (Type: {{ invoice.invoice_type }})
                                        </option>
                                    </select>
                                </div>

                                <!-- Items Table -->
                                <div class="col-12" v-if="form.items.length > 0">
                                    <label class="form-label mb-2">আইটেম তালিকা</label>
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>আইটেম</th>
                                                    <th width="15%">অর্ডার</th>
                                                    <th width="15%">ডেলিভারি হয়েছে</th>
                                                    <th width="15%">বাকি</th>
                                                    <th width="20%">বর্তমান ডেলিভারি</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-center">{{ item.order_qty }}</td>
                                                    <td class="text-center">{{ item.prev_delivered }}</td>
                                                    <td class="text-center">{{ item.max_deliverable }}</td>
                                                    <td>
                                                        <input type="number" 
                                                               class="form-control form-control-sm text-center" 
                                                               v-model="item.current_delivery"
                                                               min="0" 
                                                               :max="item.max_deliverable"
                                                               placeholder="0">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 text-center py-3" v-else-if="isLoading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">নোট (ঐচ্ছিক)</label>
                                    <textarea class="form-control" v-model="form.note" rows="2"></textarea>
                                </div>

                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                            <i class="bx bx-save me-1"></i> সেভ করুন
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
