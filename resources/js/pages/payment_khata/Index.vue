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
    payments: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 20);
const date = ref(props.filters?.date || new Date().toISOString().split('T')[0]);

const showForm = ref(false);
const spinBtn = ref(false);
const isEditing = ref(false);
const loading = ref(true);

const form = useForm({
    payment_date: date.value,
    payment_head_id: '',
    payment_type: 'regular',
    payment_details: '',
    quantity: 0,
    amount: 0,
    paid_amount: 0,
    due_amount: 0,
    id: '',
});

// show/hide form toggle
const newPayment = () => {
    showForm.value = !showForm.value;
};

function calculateRemaining() {
    if (!form.value) return;

    if (form.value.payment_type === 'advance') {
        form.value.due_amount = form.value.due_amount || 0;
        return;
    }

    const total = parseFloat(form.value.amount) || 0;
    const payment = parseFloat(form.value.paid_amount) || 0;
    form.value.due_amount = total - payment;
}

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

function openEdit(payment) {
    isEditing.value = true;
    showForm.value = true;

    form.payment_date = payment.payment_date
        ? payment.payment_date.substring(0, 10)
        : null;

    form.payment_head_id = payment.payment_head_id;
    form.payment_type = payment.payment_type == 'রেগুলার' ? 'regular' : 'advance';
    form.payment_details = payment.payment_details;
    form.quantity = payment.quantity;
    form.amount = payment.amount;
    form.paid_amount = payment.paid_amount;
    form.due_amount = payment.due_amount;

    form.id = payment.id;
}

// Form submit
function submit() {
    spinBtn.value = true;

    if (isEditing.value) {
        form.put(route("payment-khata.update", form.id), {
            onSuccess: () => {
                spinBtn.value = false;
                showForm.value = false;
            },
            onError: () => {
                spinBtn.value = false;
            }
        });
    } else {
        form.post(route("payment-khata.store"), {
            onSuccess: () => {
                spinBtn.value = false;
                showForm.value = false;
                form.reset();
            },
            onError: () => {
                spinBtn.value = false;
            }
        });
    }
}

watch([search, perPage,date], () => {
    router.get(route('payment-khata.index'), {
        search: search.value,
        perPage: perPage.value,
        date: date.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const confirmDelete = (id) => {
    Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: "মুছে ফেললে এটি আর ফিরে পাওয়া যাবে না!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন!',
        cancelButtonText: 'বাতিল',
        customClass: {
            title: 'swal-title-small',
            confirmButton: 'swal-btn-small swal-confirm-btn',
            cancelButton: 'swal-btn-small swal-cancel-btn',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/payment-khata/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'মুছে ফেলা হয়েছে!',
                        text: 'পেমেন্ট খাতা সফলভাবে মুছে ফেলা হয়েছে।',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        customClass: {
                            title: 'swal-title-small',
                        },
                    })
                },
                onError: () => {
                    Swal.fire({
                        title: 'ত্রুটি!',
                        text: 'কিছু ভুল হয়েছে, দয়া করে আবার চেষ্টা করুন।',
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 1500,
                        customClass: {
                            title: 'swal-title-small',
                        },
                    })
                }
            })
        }
    })
}


// Simulate loading
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 500);
});

</script>

<template>
    <Head title="পেমেন্ট খাতা" />
    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                        <!-- Title -->
                        <h6 class="mb-0 text-primary d-flex align-items-center">
                            <i class="fadeIn animated bx bx-list-ul me-2"></i> পেমেন্ট খাতা
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

                            <!-- New Payment Button -->
                            <button class="btn btn-primary btn-sm" @click="newPayment">
                                <i class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                নতুন পেমেন্ট
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div v-if="loading" class="text-center my-5">
                            <i class="bx bx-loader bx-spin" style="font-size: 40px;"></i>
                        </div>
                        <div v-else>
                            <!-- Payment Form -->
                            <div class="table-responsive" v-if="showForm">
                                <form @submit.prevent="submit">
                                    <table class="table table-striped table-bordered" style="width: 100%; font-size: small;">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label class="form-label">তারিখ</label>
                                                    <input
                                                        type="date"
                                                        v-model="form.payment_date"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.payment_date}"
                                                        style="height: 32px; font-size: 12px;"
                                                    />
                                                    <InputError :message="form.errors.payment_date" />
                                                </td>
                                                <td>
                                                    <label class="form-label">খতিয়ান</label>
                                                    <select
                                                        v-model="form.payment_head_id"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.payment_head_id}"
                                                        style="height: 32px; font-size: 12px;"
                                                        required
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
                                                    <select
                                                        v-model="form.payment_type"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.payment_type}"
                                                        required
                                                    >
                                                        <option value="regular">রেগুলার</option>
                                                        <option value="advance">অগ্রিম</option>
                                                    </select>
                                                    <InputError :message="form.errors.payment_type" />
                                                </td>

                                                <td>
                                                    <label class="form-label">পেমেন্টের বিবরন</label>
                                                    <Input
                                                        type="text"
                                                        v-model="form.payment_details"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.payment_details}"
                                                        placeholder="পেমেন্টের বিবরন"
                                                    />
                                                    <InputError :message="form.errors.payment_details" />
                                                </td>

                                                <td>
                                                    <label class="form-label">পরিমাণ</label>
                                                    <Input
                                                        type="number"
                                                        v-model="form.quantity"
                                                        min="0"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.quantity}"
                                                        :readonly="form.payment_type === 'advance'"
                                                        placeholder="পরিমাণ"
                                                    />
                                                    <InputError :message="form.errors.quantity" />
                                                </td>

                                                <td>
                                                    <label class="form-label">মোট বিল</label>
                                                    <Input
                                                        type="number"
                                                        v-model="form.amount"
                                                        min="0"
                                                        @input="calculateRemaining"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.amount}"
                                                        :readonly="form.payment_type === 'advance'"
                                                        placeholder="মোট বিল"
                                                    />
                                                    <InputError :message="form.errors.amount" />
                                                </td>
                                                <td>
                                                    <label class="form-label">পেমেন্ট</label>
                                                    <Input
                                                        type="number"
                                                        v-model="form.paid_amount"
                                                        min="0"
                                                        @input="calculateRemaining"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.paid_amount}"
                                                        placeholder="পেমেন্ট"
                                                        required
                                                    />
                                                    <InputError :message="form.errors.paid_amount" />
                                                </td>
                                                <td>
                                                    <label class="form-label">বাকি</label>
                                                    <Input
                                                        type="number"
                                                        v-model="form.due_amount"
                                                        min="0"
                                                        :value="form.due_amount"
                                                        class="form-control form-control-sm"
                                                        :class="{'border-danger mb-1': form.errors.due_amount}"
                                                        :readonly="form.payment_type === 'advance'"
                                                        placeholder="বাকি"
                                                        readonly
                                                    />
                                                    <InputError :message="form.errors.due_amount" />
                                                </td>
                                                <td>
                                                    <button type="submit"
                                                        class="btn btn-primary btn-sm w-100 mt-4"
                                                    >
                                                    <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                                    <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                                    {{ isEditing ? 'আপডেট' : 'সেভ করুন' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </form>
                            </div>

                            <!-- Payment Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" style="width: 100%; font-size: small;">
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
                                        <tr v-for="(payment, index) in payments.data" :key="payment.id">
                                            <td>{{ index+1 }}</td>
                                            <td>{{ formatDate(payment.payment_date) }}</td>
                                            <td>{{ payment.payment_head?.name }}</td>
                                            <td>{{ payment.payment_details }}</td>
                                            <td>{{ payment.quantity }}</td>
                                            <td>{{ payment.amount }}</td>
                                            <td>{{ payment.paid_amount }}</td>
                                            <td>{{ payment.due_amount }}</td>
                                            <td>
                                                    <!-- Edit -->
                                                    <a
                                                        @click="!payment.is_locked && openEdit(payment)"
                                                        :class="payment.is_locked ? 'text-gray-300 cursor-not-allowed' : 'text-primary cursor-pointer'"
                                                    >
                                                        <i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i>
                                                    </a>

                                                    <!-- Delete -->
                                                    <a
                                                        @click.prevent="!payment.is_locked && confirmDelete(payment.id)"
                                                        :class="payment.is_locked ? 'text-gray-300 cursor-not-allowed' : 'text-danger cursor-pointer'"
                                                    >
                                                        <i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i>
                                                    </a>
                                                </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row align-items-center">
                                    <!-- Pagination Info -->
                                    <div class="col-sm-12 col-md-5 mb-2 mb-md-0">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="col-sm-12 col-md-7">
                                        <nav class="dataTables_paginate paging_simple_numbers" aria-label="Pagination">
                                            <ul class="pagination justify-content-md-end flex-wrap" style="gap: 4px;">
                                                <li v-for="link in payments.links"
                                                    :key="link.label"
                                                    class="page-item"
                                                    :class="{
                                                        active: link.active,
                                                        disabled: !link.url
                                                    }">
                                                    <Link
                                                        :href="link.url || '#'"
                                                        class="page-link"
                                                        v-html="link.label"
                                                        :class="{
                                                            'bg-primary text-white border-primary': link.active,
                                                            'text-muted cursor-not-allowed': !link.url
                                                        }"
                                                    />
                                                </li>
                                            </ul>
                                        </nav>
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
