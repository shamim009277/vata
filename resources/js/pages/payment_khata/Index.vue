<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm,router,Link } from '@inertiajs/vue3';
import { ref, watch, onMounted,computed } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    items: Object,
});

const showForm = ref(false);

const form = useForm({
    product_date: new Date().toISOString().split('T')[0],
    payment_head_id: '',
    payment_type: '',
    payment_details: '',
    quantity: '',
    total_bill: '',
    payment_amount: '',
});

// show/hide form toggle
const newPayment = () => {
    showForm.value = !showForm.value;
};

</script>

<template>
    <Head title="পেমেন্ট খাতা" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> পেমেন্ট খাতা
                                </a>
                            </h6>

                            <!-- Right-side controls -->
                            <div class="d-flex align-items-center gap-2">
                                <!-- Date Filter -->
                                <input
                                    type="date"
                                    v-model="date"
                                    class="form-control form-control-sm"
                                    style="width: 160px;"
                                />

                                <!-- Edit Button -->
                                <button class="btn btn-primary btn-sm" @click="newPayment">
                                    <i class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                    নতুন পেমেন্ট
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <div class="dataTables_wrapper dt-bootstrap5">
                                <table class="" width="100%" v-if="showForm">
                                    <tr>
                                        <td>
                                            <label class="form-label">খতিয়ান</label>
                                            <select
                                                v-model="form.payment_head_id"
                                                class="form-control form-control-sm"
                                                :class="form.errors.payment_head_id ? 'border-danger mb-1' : ''"
                                                style="height: 32px; font-size: 12px;" required
                                            >
                                                <option value="">সিলেক্ট মাঠ</option>
                                                <option v-for="it in items" :key="it.id" :value="it.id">
                                                    {{ it.name }}
                                                </option>
                                            </select>
                                            <InputError :message="form.errors.payment_head_id" />
                                        </td>
                                        <td>
                                            <label class="form-label">পেমেন্টের ধরন</label>
                                            <select v-model="form.payment_type" class="form-control form-control-sm" :class="[form.errors.payment_type ? 'border-danger mb-1' : '']">
                                                <option value="regular">রেগুলার</option>
                                                <option value="advance">অগ্রিম</option>
                                            </select>
                                            <InputError :message="form.errors.payment_type" />
                                        </td>

                                        <td>
                                            <label class="form-label">পেমেন্টের বিবরন</label>
                                            <Input type="text" v-model="form.payment_details" class="form-control form-control-sm" :class="[form.errors.payment_details ? 'border-danger mb-1' : '']" placeholder="পেমেন্টের বিবরন"/>
                                            <InputError :message="form.errors.payment_details" />
                                        </td>
                                        <td>
                                            <label class="form-label">পরিমাণ</label>
                                            <Input type="number" v-model="form.quantity" min="0" class="form-control form-control-sm" :class="[form.errors.quantity ? 'border-danger mb-1' : '']" placeholder="ফোন নম্বর"/>
                                            <InputError :message="form.errors.quantity" />
                                        </td>
                                        <td>
                                            <label class="form-label">মোট বিল</label>
                                            <Input type="number" v-model="form.total_bill" min="0" class="form-control form-control-sm" :class="[form.errors.total_bill ? 'border-danger mb-1' : '']" placeholder="ফোন নম্বর"/>
                                            <InputError :message="form.errors.total_bill" />
                                        </td>
                                        <td>
                                            <label class="form-label">পেমেন্ট </label>
                                            <Input type="number" v-model="form.payment_amount" min="0" class="form-control form-control-sm" :class="[form.errors.payment_amount ? 'border-danger mb-1' : '']" placeholder="পেমেন্ট পরিমাণ" />
                                            <InputError :message="form.errors.payment_amount" />
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm mt-4" @click="addItem" style="width: 100%;">সেভ করুন</button>
                                        </td>
                                    </tr>
                                </table>
                                <table class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">

                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th>তারিখ</th>
                                            <th>খতিয়ান</th>
                                            <th>পেমেন্টের বিবরন</th>
                                            <th>পরিমাণ</th>
                                            <th>মোট বিল</th>
                                            <th>পেমেন্ট</th>
                                            <th>বাকি</th>
                                            <th>বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}
.modal {
    position: fixed;
    inset: 0;
    overflow: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.challan-info {
    line-height: 0.5;
}

.company-info {
    line-height: 0.5;
    text-align: right;
}
.company-info h4 {
    font-size: 16px;
    font-weight: bold;
}

.customer {
    background: linear-gradient(145deg, #ffffff, #f9fafb);
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    line-height: 1.4;
    color: #333;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    position: relative;
}

.customer-danger {
    background: linear-gradient(145deg, #ffffff, #f9fafb);
    border: 1px solid rgb(234, 45, 45);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    line-height: 1.0;
    color: #333;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    transition: all 0.3s ease;
    position: relative;
}

.customer:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.customer::before {
    position: absolute;
    top: -10px;
    left: 14px;
    background: #fff;
    font-size: 12px;
    font-weight: 600;
    color: #004882;
    padding: 0 6px;
    border-radius: 4px;
}

.customer p {
    margin: 3px 0;
}

</style>
