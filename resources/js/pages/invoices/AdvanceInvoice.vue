<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm,router,Link } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    invoices: Object,
    items: Object,
    invoiceNumber: String,
    deliveryNumber: String,
});


const itemsList = ref(props.items);

const showModal = ref(false);
const showDetailsModal = ref(false);
const showDeliveryModal = ref(false);
const selectedInvoice = ref(null);
const editInvoice = ref(false);
const spinBtn = ref(false);
const loading = ref(true); // spinner state


const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
const start_date = ref('');
const end_date = ref('');

// Main form
const form = useForm({
    invoice_no: props.invoiceNumber,
    delivery_no: props.deliveryNumber,
    invoice_date: new Date().toISOString().split('T')[0],
    phone: '',
    customer_name: '',
    address: '',
    type: '',
    rate: 0,
    delivery_date: '',
    payment_date: '',
    note: '',
    amount: 0,
    item_id: '',
    quantity: 0,
    sub_total: 0,
    total_amount: 0,
    discount: 0,
    rant: 0,
    paid_amount: 0,
    due_amount: 0,
    delivery_rant: 0,
    items: [],

    id:0,
    delivery_qty: 0,
    today_delivery_qty: 0,
    remaining_delivery_qty: 0,
});

const delivaryForm = useForm({
    invoice_no: props.invoiceNumber,
    delivery_no: props.deliveryNumber,
    customer_name: '',
    phone: '',
    address: '',
    delivery_date: '',
    note: '',
    

    next_delivery_date: '',
    note: '',
    item_id: '',
    customer_id: '',
    delivery_qty: 0,
    today_delivery_qty: 0,
    remaining_delivery_qty: 0,
    driver_name: '',
    driver_phone: '',
    truck_number: '',
    delivery_rant: 0,
});

const isValidPhone = ref(true);

// Bangladesh phone validation
const validatePhone = () => {
    const regex = /^01[3-9][0-9]{8}$/;
    isValidPhone.value = regex.test(form.phone);
    isValidPhone.value = regex.test(delivaryForm.driver_phone);
};

// Row change handlers
const onTypeChange = (row) => {
    const selected = itemsList.value.find(i => i.id === row.item_id);
    row.rate = Number(selected ? selected.rate : 0);
    row.amount = Number(row.rate) * Number(row.quantity || 0);
    calculateSubTotal();
    calculateTotalAmount();
    calculateDueAmount();
};

const onQuantityChange = (row) => {
    row.quantity = Number(row.quantity);
    row.amount = Number(row.rate) * Number(row.quantity);
    calculateSubTotal();
    calculateTotalAmount();
    calculateDueAmount();
};

// Calculation functions
const calculateSubTotal = () => {
    form.sub_total = form.items.reduce(
        (total, item) => total + Number(item.amount || 0),
        0
    );
};

const calculateTotalAmount = () => {
    form.total_amount =
        Number(form.sub_total) - Number(form.discount) + Number(form.rant);
};

const calculateDueAmount = () => {
    form.due_amount = Number(form.total_amount) - Number(form.paid_amount);
};

const calculatePaidAmount = () => {
    form.paid_amount = Number(form.total_amount) - Number(form.due_amount);
};

// Input handlers
const inputDiscount = () => {
    form.discount = Number(form.discount);
    if (form.discount > form.sub_total) {
        toast.error('ছাড় মোট মূল্য থেকে সর্বোচ্চ হতে পারে না');
        form.discount = 0;
    }
    calculateTotalAmount();
    calculateDueAmount();
    calculatePaidAmount();
};

const inputRant = () => {
    form.rant = Number(form.rant);
    if (form.rant > form.sub_total) {
        toast.error('গাড়ি ভাড়া মোট মূল্য থেকে সর্বোচ্চ হতে পারে না');
        form.rant = 0;
    }
    calculateTotalAmount();
    calculateDueAmount();
    calculatePaidAmount();
};

const inputPaidAmount = () => {
    form.paid_amount = Number(form.paid_amount);
    if (form.paid_amount > form.total_amount) {
        toast.error('নগদ মোট মূল্য থেকে সর্বোচ্চ হতে পারে না');
        form.paid_amount = 0;
    }
    calculateDueAmount();
};

const addRow = () => {
    form.items.push({
        item_id: '',
        rate: 0,
        quantity: 0,
        amount: 0,
    });
};

const showInvoiceDetails = (invoice) => {
    selectedInvoice.value = invoice;
    showDetailsModal.value = true;
};

const showDeliveryDetails = (invoice) => {
    selectedInvoice.value = invoice;
    showDeliveryModal.value = true;

    delivaryForm.customer_name = invoice.customer.name;
    delivaryForm.customer_id = invoice.customer_id;
    delivaryForm.phone = invoice.customer.phone;
    delivaryForm.address = invoice.customer.address;
    delivaryForm.invoice_no = invoice.invoice_no;
    if (invoice.delivery_date) {
        delivaryForm.delivery_date = new Date(invoice.delivery_date).toISOString().split('T')[0];
    } else {
        delivaryForm.delivery_date = new Date().toISOString().split('T')[0];
    }
};

const calculateRemainingDeliveryQty = () => {
    delivaryForm.remaining_delivery_qty = delivaryForm.delivery_qty - delivaryForm.today_delivery_qty;
};

const showUpdateDetails = (invoice) => {
    selectedInvoice.value = invoice;
    showModal.value = true;
    editInvoice.value = true;
    
    form.id = invoice.id;
    form.invoice_no = invoice.invoice_no;
    form.customer_name = invoice.customer.name;
    form.phone = invoice.customer.phone;
    form.address = invoice.customer.address;
    form.type = invoice.invoice_type;
    form.delivery_date = invoice.delivery_date ? invoice.delivery_date.split('T')[0] : '';
    form.invoice_date = invoice.invoice_date  ? invoice.invoice_date .split('T')[0] : '';



    form.items = invoice.invoice_details.map(detail => ({
        item_id: detail.item_id,
        rate: detail.rate,
        quantity: detail.quantity,
        amount: detail.amount,
    }));
    form.discount = invoice.discount;
    form.rant = invoice.rant;
    form.sub_total = invoice.total_amount;
    form.total_amount = invoice.total_amount;
    form.paid_amount = invoice.paid_amount;
    form.due_amount = invoice.due_amount;
};

const removeRow = (index) => {
    form.items.splice(index, 1);
    calculateSubTotal();
    calculateTotalAmount();
    calculateDueAmount();
};

// Form submit
const submit = () => {
    spinBtn.value = true;
    form.post(route('invoices.store'), {
        onSuccess: () => {
            spinBtn.value = false;
            showModal.value = false;
            form.reset();
            form.items = [];
        },
        onError: () => {
            spinBtn.value = false;
        },
    });
};

const invoiceDelivary = () => {
    spinBtn.value = true;
    delivaryForm.post(route('invoice.delivary'), {
        onSuccess: () => {
            spinBtn.value = false;
            showDeliveryModal.value = false;
            delivaryForm.reset();
        },
        onError: () => {
            spinBtn.value = false;
        },
    });
}

watch([search, perPage,start_date,end_date], () => {
    router.get(route('invoice.all'), {
        search: search.value,
        perPage: perPage.value,
        start_date: start_date.value,
        end_date: end_date.value,
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
            form.delete(`/invoices/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'মুছে ফেলা হয়েছে!',
                        text: 'চালান সফলভাবে মুছে ফেলা হয়েছে।',
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



// Phone validation watcher
watch(() => form.phone, () => validatePhone());
watch(() => delivaryForm.driver_phone, () => validatePhone());

watch(() => delivaryForm.item_id, (newVal) => {
    if (!selectedInvoice.value) return;

    const selectedItem = selectedInvoice.value.invoice_details.find(
        (d) => d.item.id === newVal
    );

    if (selectedItem) {
        delivaryForm.delivery_qty =
          Number(selectedItem.quantity || 0) -
          Number(selectedItem.delivery_quantity || 0);
      } else {
        delivaryForm.delivery_qty = 0;
      }
});

// Simulate loading
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 500);
});
</script>

<template>
    <Head title="আজকের চালান" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> অগ্রিম চালান
                                </a>
                            </h6>

                            <!-- Right-side controls -->
                            <div class="d-flex align-items-center gap-2">
                                <!-- Start Date -->
                                <input 
                                    type="date"
                                    v-model="start_date"
                                    class="form-control form-control-sm"
                                    style="width: 160px;"
                                    placeholder="শুরুর তারিখ"
                                />

                                <span>থেকে</span>

                                <!-- End Date -->
                                <input 
                                    type="date"
                                    v-model="end_date"
                                    class="form-control form-control-sm"
                                    style="width: 160px;"
                                    placeholder="শেষ তারিখ"
                                />
                            </div>
                        </div>
                    </div>
 
                    <div class="card-body">
                        <div v-if="loading" class="text-center my-5">
                            <i class="bx bx-loader bx-spin" style="font-size: 40px;"></i>
                        </div>
                        <div v-else class="table-responsive">
                            <div class="dataTables_wrapper dt-bootstrap5">
                                <div class="row mb-2">
                                    <div class="col-sm-12 col-md-6">
                                        <label>Show
                                            <select v-model="perPage" class="form-select form-select-sm d-inline w-auto ms-2">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select> entries
                                        </label>
                                    </div>
                                    <div class="col-sm-12 col-md-6 text-end">
                                        <label>অনুসন্ধান করুন:
                                            <input v-model="search" type="search" class="form-control form-control-sm d-inline w-auto ms-2" placeholder="অনুসন্ধান করুন ..." />
                                        </label>
                                    </div>
                                </div>

                                <table class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>কাস্টমার</th>
                                            <th>ঠিকানা</th>
                                            <th>পরিমাণ</th>
                                            <th>মূল্য</th>
                                            <th>মোট মূল্য</th>
                                            <th>ছাড়</th>
                                            <th>ভাড়া</th>
                                            <th>সর্বমোট</th>
                                            <th>নগদ</th>
                                            <th>বাকি</th>
                                            <th>বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="invoice in invoices.data" :key="invoice.id">
                                            <td>{{ invoice.invoice_no }}</td>
                                            <td>
                                                {{ invoice.customer.name }}<br>
                                                {{ invoice.customer.phone }}
                                            </td>
                                            <td>{{ invoice.customer.address }}</td>
                                            <td>{{ invoice.quantity }}</td>
                                            <td>{{ invoice.total_amount }}</td>
                                            <td>{{ invoice.total_amount }}</td>
                                            <td>{{ invoice.discount }}</td>
                                            <td>{{ invoice.rant }}</td>
                                            <td>{{ invoice.total_amount }}</td>
                                            <td>{{ invoice.paid_amount }}</td>
                                            <td>{{ invoice.due_amount }}</td>
                                            <td>
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showUpdateDetails(invoice)">
                                                                <i class="bx bx-box"></i>
                                                                আপডেট করুণ
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showDeliveryDetails(invoice)">
                                                                🚚 ডেলিভারি দিন
                                                            </a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="confirmDelete(invoice.id)">
                                                            <i class="bx bx-trash"></i>
                                                            চালান মুছে ফেলুন
                                                        </a></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showInvoiceDetails(invoice)">
                                                                <i class="bx bx-show"></i> 
                                                                চালান বিস্তারিত
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row align-items-center">
                                    <!-- Pagination Info -->
                                    <div class="col-sm-12 col-md-5 mb-2 mb-md-0">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="col-sm-12 col-md-7">
                                        <nav class="dataTables_paginate paging_simple_numbers" aria-label="Pagination">
                                            <ul class="pagination justify-content-md-end flex-wrap" style="gap: 4px;">
                                                <li v-for="link in invoices.links"
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

            <!-- Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h6 class="modal-title">
                                    <i class="bx bx-message-alt-add me-2"></i>
                                    {{ editInvoice ? 'চালান আপডেট  করেন' : 'নতুন চালান করেন' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-md-6 mb-3">
                                        <a href="javascript:;" class="btn btn-primary btn-sm me-2">নতুন কাস্টমার</a>
                                        <a href="javascript:;" class="btn btn-outline-success btn-sm">পুরাতন কাস্টমার</a>
                                    </div>
                                    <div class="col-md-3 mb-3 pe-md-0">
                                        <Input type="text" v-model="form.invoice_no" class="form-control form-control-sm" :class="[form.errors.invoice_no ? 'border-danger mb-1' : '']" placeholder="চালান নম্বর" readonly/>
                                        <InputError :message="form.errors.invoice_no" />
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <Input type="date" v-model="form.invoice_date" class="form-control form-control-sm" :class="[form.errors.invoice_date ? 'border-danger mb-1' : '']" placeholder="ফোন নম্বর"/>
                                        <InputError :message="form.errors.invoice_date" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ফোন নম্বর <span class="text-danger">*</span></label>
                                        <Input
                                          v-model="form.phone"
                                          class="form-control form-control-sm"
                                          placeholder="ফোন নম্বর"
                                          :class="[!isValidPhone && form.phone ? 'border-danger' : '', form.errors.phone ? 'border-danger mb-1' : '']"
                                          @input="validatePhone"
                                          required
                                        />
                                        <!-- Frontend validation message -->
                                        <small v-if="!isValidPhone && form.phone" class="text-danger">
                                          ভুল ফোন নম্বর! (সঠিক: 017XXXXXXXX)
                                        </small>
                                        <!-- Backend validation -->
                                        <InputError :message="form.errors.phone" />
                                      </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">কাস্টমারের নাম <span class="text-danger">*</span></label>
                                        <Input v-model="form.customer_name" class="form-control form-control-sm" placeholder="কাস্টমারের নাম" :class="[form.errors.customer_name ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.customer_name" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">কাস্টমারের ঠিকানা <span class="text-danger">*</span></label>
                                        <Input v-model="form.address" class="form-control form-control-sm" placeholder="কাস্টমারের ঠিকানা" :class="[form.errors.address ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.address" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">চালানের ধরণ <span class="text-danger">*</span></label>
                                        <select v-model="form.type" class="form-control form-control-sm" :class="[form.errors.type ? 'border-danger mb-1' : '']" required>
                                            <option value="">--- সিলেক্ট চালান ---</option>
                                            <option value="রেগুলার">রেগুলার চালান</option>
                                            <option value="অগ্রিম">অগ্রিম চালান</option>
                                        </select>
                                        <InputError :message="form.errors.type" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ডেলিভারি তারিখ <span class="text-danger">*</span></label>
                                        <Input type="date" v-model="form.delivery_date" class="form-control form-control-sm" :class="[form.errors.delivery_date ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.delivery_date" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="form.note" class="form-control form-control-sm" placeholder="নোট" :class="[form.errors.note ? 'border-danger mb-1' : '']" />
                                        <InputError :message="form.errors.note" />
                                    </div>

                                    <!-- Items Table -->
                                    <div class="col-12 mb-2">
                                        <table class="table table-bordered" style="width: 100%; font-size: 11px;">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th width="30%">শ্রেণি</th>
                                                    <th width="21%">রেট</th>
                                                    <th width="22%">পরিমাণ</th>
                                                    <th width="22%">মূল্য</th>
                                                    <th width="5%">
                                                        <a href="#" @click.prevent="addRow" class="text-white">
                                                            <i class="bx bx-plus"></i>
                                                        </a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index">
                                                    <td>
                                                        <select
                                                            v-model="item.item_id"
                                                            class="form-control form-control-sm"
                                                            @change="onTypeChange(item)"
                                                            :class="form.errors[`items.${index}.item_id`] ? 'border-danger mb-1' : ''"
                                                        >
                                                            <option value="">সিলেক্ট শ্রেণির ধরণ</option>
                                                            <option
                                                                v-for="it in itemsList"
                                                                :key="it.id"
                                                                :value="it.id"
                                                            >
                                                                {{ it.name }}
                                                            </option>
                                                        </select>
                                                
                                                        <InputError :message="form.errors[`items.${index}.item_id`]" />
                                                    </td>
                                                
                                                    <td>
                                                        <Input
                                                            type="number"
                                                            v-model="item.rate"
                                                            class="form-control form-control-sm"
                                                            placeholder="রেট"
                                                            :class="form.errors[`items.${index}.rate`] ? 'border-danger' : ''"
                                                        />
                                                        
                                                        <InputError :message="form.errors[`items.${index}.rate`]" />
                                                    </td>
                                                
                                                    <td>
                                                        <Input
                                                            type="number"
                                                            v-model="item.quantity"
                                                            class="form-control form-control-sm"
                                                            placeholder="পরিমাণ"
                                                            @input="onQuantityChange(item)"
                                                            :class="form.errors[`items.${index}.quantity`] ? 'border-danger' : ''"
                                                        />
                                                        <InputError :message="form.errors[`items.${index}.quantity`]" />
                                                    </td>
                                                
                                                    <td>
                                                        <Input
                                                            type="number"
                                                            v-model="item.amount"
                                                            class="form-control form-control-sm"
                                                            placeholder="মূল্য"
                                                            readonly
                                                            :class="form.errors[`items.${index}.amount`] ? 'border-danger' : ''"
                                                        />
                                                        <InputError :message="form.errors[`items.${index}.amount`]" />
                                                    </td>
                                                
                                                    <td>
                                                        <a href="#" @click.prevent="removeRow(index)">
                                                            <i class="bx bx-trash" style="font-size: larger;"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>


                                    <div class="col-6">
                                        <div class="row">
                                            <div class="col-sm-6 mb-2 pe-sm-0">
                                                <label class="form-label">পরিশোধিত তারিখ <span class="text-danger">*</span></label>
                                                <Input type="date" v-model="form.payment_date" class="form-control form-control-sm" :class="form.errors.payment_date ? 'border-danger' : ''" placeholder="পরিশোধিত তারিখ" />
                                                <InputError :message="form.errors.payment_date" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="row">
                                            <div class="col-sm-6 mb-2 pe-sm-0">
                                                <label class="form-label">মূল্য <span class="text-danger">*</span></label>
                                                <Input v-model="form.sub_total" class="form-control form-control-sm" placeholder="মোট মূল্য" readonly/>
                                                <InputError :message="form.errors.sub_total" />
                                            </div>

                                            <div class="col-sm-6 mb-2">
                                                <label class="form-label">ছাড় <span class="text-danger">*</span></label>
                                                <Input v-model="form.discount"  class="form-control form-control-sm" @input="inputDiscount()" placeholder="ছাড়" />
                                                <InputError :message="form.errors.discount" />
                                            </div>

                                            <div class="col-sm-6 mb-2 pe-sm-0">
                                                <label class="form-label">গাড়ি ভাড়া <span class="text-danger">*</span></label>
                                                <Input v-model="form.rant" class="form-control form-control-sm" @input="inputRant()" placeholder="গাড়ি ভাড়া" />
                                                <InputError :message="form.errors.rant" />
                                            </div>

                                            <div class="col-sm-6 mb-2">
                                                <label class="form-label">মোট মূল্য <span class="text-danger">*</span></label>
                                                <Input v-model="form.total_amount" class="form-control form-control-sm" placeholder="মোট মূল্য" readonly/>
                                                <InputError :message="form.errors.total_amount" />
                                            </div>

                                            <div class="col-sm-6 mb-2 pe-sm-0">
                                                <label class="form-label">নগদ <span class="text-danger">*</span></label>
                                                <Input v-model="form.paid_amount" class="form-control form-control-sm" @input="inputPaidAmount()" placeholder="নগদ" />
                                                <InputError :message="form.errors.paid_amount" />
                                            </div>

                                            <div class="col-sm-6 mb-2">
                                                <label class="form-label">বাকি <span class="text-danger">*</span></label>
                                                <Input v-model="form.due_amount" class="form-control form-control-sm" placeholder="বাকি" readonly/>
                                                <InputError :message="form.errors.due_amount" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                        {{ editingPlan ? 'Update' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Details Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showDetailsModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showDetailsModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h6 class="modal-title font-bold">
                                    <i class="bx bx-file me-2"></i>
                                    চালান এর বিস্তারিত
                                </h6>
                                <button type="button" class="btn-close" @click="showDetailsModal = false"></button>
                            </div>
            
                            <div class="modal-body" v-if="selectedInvoice">
                                <div class="row mb-3">
                                    <div class="col-md-6 challan-info">
                                        <p><strong>চালান নং:</strong> {{ selectedInvoice.invoice_no }}</p>
                                        <p><strong>চালান তৈরি করেছেন:</strong> {{ selectedInvoice.creator.name }}</p>
                                    </div>
                                    <div class="col-md-6 company-info">
                                        <h4 class="font-bold text-primary">এম.এম.বি ব্রিকস</h4>
                                        <p>হিলালিপাড়া,কাটাবাড়ি,গোবিন্দগঞ্জ</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>নাম:</strong> {{ selectedInvoice.customer.name }}</p>
                                            <p><strong>ফোন:</strong> {{ selectedInvoice.customer.phone }}</p>
                                            <p><strong>ঠিকানা:</strong> {{ selectedInvoice.customer.address }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>কাস্টমার আইডি:</strong> {{ selectedInvoice.customer.id }}</p>
                                            <p><strong>চালানের ধরণ:</strong> {{ selectedInvoice.invoice_type }}</p>
                                            <p><strong>ডেলিভারি তারিখ:</strong> {{ $formatDate(selectedInvoice.delivery_date) }}</p>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>তারিখ:</strong> {{ $formatDate(selectedInvoice.invoice_date) }}</p>
                                            <p><strong>সময়:</strong> {{ $formatTime12Hour(selectedInvoice.created_at) }}</p>
                                            <p><strong>সিজন:</strong> {{ selectedInvoice.season }}</p>
                                        </div>
                                    </div>
                                </div>
            
                                <table class="table table-bordered" style="font-size: small;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>শ্রেণি</th>
                                            <th>রেট</th>
                                            <th>পরিমাণ</th>
                                            <th>মূল্য</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, i) in selectedInvoice.invoice_details" :key="i">
                                            <td>{{ i + 1 }}</td>
                                            <td>{{ item.item.name }}</td>
                                            <td>{{ item.rate }}</td>
                                            <td>{{ item.quantity }}</td>
                                            <td>{{ item.amount }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="customer-danger text-center">
                                            <div v-if="selectedInvoice.due_amount > 0">
                                                <h4 class="text-danger">বাকিঃ {{ selectedInvoice.due_amount }}</h4>
                                                <p class="text-danger"><strong>পরিশোধের তারিখ:</strong> {{ $formatDate(selectedInvoice.next_payment_date) }}</p>
                                            </div>
                                            <div v-else>
                                                <h4 class="text-success text-center">পরিশোধিত</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="customer">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-hover" width="100%">
                                                        <tbody> 
                                                            <tr>
                                                                <th>মোট মূল্য</th>
                                                                <td>৳  {{ selectedInvoice.invoice_details.reduce((total, item) => Number(total) + Number(item.amount), 0).toFixed(2) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>গাড়ি ভাড়া</th>
                                                                <td>৳ {{ Number(selectedInvoice.rant).toFixed(2) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>জমা</th>
                                                                <td>৳ {{ Number(selectedInvoice.paid_amount).toFixed(2) }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-hover" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <th>ছাড়</th>
                                                                <td>৳ {{ Number(selectedInvoice.discount).toFixed(2) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>সর্বমোট</th>
                                                                <td>৳ {{ Number(selectedInvoice.total_amount).toFixed(2) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>বাকি</th>
                                                                <td>৳ {{ Number(selectedInvoice.due_amount).toFixed(2) }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button class="btn btn-secondary btn-sm" @click="showDetailsModal = false">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Delivery Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showDeliveryModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showDeliveryModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h5 class="modal-title font-bold">
                                    <i class="bx bx-file me-2"></i>
                                    নতুন ডেলিভারি 🚚
                                </h5>
                                <button type="button" class="btn-close" @click="showDeliveryModal = false"></button>
                            </div>


                        <form @submit.prevent="invoiceDelivary">
                            <div class="modal-body" v-if="selectedInvoice">
                                <div class="row m-1">
                                    <div class="text-center font-bold" style="background-color: #f2d0d0; padding: 6px 8px;color: #d00808;border-radius: 2px;">
                                        Kamran এর বাকি রয়েছেঃ 20,000 টাকা
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">ডেলিভারি নং<span class="text-danger">*</span></label>
                                        <Input v-model="delivaryForm.delivery_no" class="form-control form-control-sm" placeholder="ডেলিভারি নং" :class="[delivaryForm.errors.delivery_no ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="delivaryForm.errors.delivery_no" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0">
                                        <label class="form-label">চালান নং<span class="text-danger">*</span></label>
                                        <Input v-model="delivaryForm.invoice_no" class="form-control form-control-sm" placeholder="চালান নং" :class="[delivaryForm.errors.invoice_no ? 'border-danger mb-1' : '']" required readonly/>
                                        <InputError :message="delivaryForm.errors.invoice_no" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ডেলিভারি তারিখ<span class="text-danger">*</span></label>
                                        <Input type="date" v-model="delivaryForm.delivery_date" class="form-control form-control-sm" placeholder="চালান নং" :class="[delivaryForm.errors.delivery_date ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="delivaryForm.errors.delivery_date" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0">
                                        <label class="form-label">কাস্টমারের নাম<span class="text-danger">*</span></label>
                                        <input type="hidden" v-model="delivaryForm.customer_id"/>
                                        <Input v-model="delivaryForm.customer_name" class="form-control form-control-sm" placeholder="কাস্টমারের নাম" :class="[delivaryForm.errors.customer_name ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="delivaryForm.errors.customer_name" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">ফোন নম্বর<span class="text-danger">*</span></label>
                                        <Input v-model="delivaryForm.phone" class="form-control form-control-sm" placeholder="ফোন নম্বর" :class="[delivaryForm.errors.phone ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="delivaryForm.errors.phone" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ডেলিভারি ঠিকানা<span class="text-danger">*</span></label>
                                        <Input v-model="delivaryForm.address" class="form-control form-control-sm" placeholder="ডেলিভারি ঠিকানা" :class="[delivaryForm.errors.address ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="delivaryForm.errors.address" />
                                    </div>

                                    <div class="col-sm-6 col-md-8 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="delivaryForm.note" class="form-control form-control-sm" placeholder="নোট" :class="[delivaryForm.errors.note ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="delivaryForm.errors.note" />
                                    </div>

                                    <div v-if="parseFloat(delivaryForm.remaining_delivery_qty) > 0" class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">পরবর্তী ডেলিভারি তারিখ<span class="text-danger">*</span></label>
                                        <Input
                                            v-model="delivaryForm.next_delivery_date"
                                            type="date"
                                            class="form-control form-control-sm"
                                            :class="[delivaryForm.errors.next_delivery_date ? 'border-danger mb-1' : '']"
                                            required
                                        />
                                        <InputError :message="delivaryForm.errors.next_delivery_date" />
                                    </div>
                                </div>
            
                                <table class="table table-bordered" style="font-size: small;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th width="25%">শ্রেণি</th>
                                            <th>ডেলিভারি পাবে</th>
                                            <th>আজকের ডেলিভারি</th>
                                            <th>ডেলিভারি বাকি</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#</td>
                                            <td>
                                                <select v-model="delivaryForm.item_id" class="form-control form-control-sm">
                                                    <option value="">-- আইটেম নির্বাচন করুন --</option>
                                                    <option
                                                        v-for="(item, i) in selectedInvoice.invoice_details"
                                                        :key="i"
                                                        :value="item.item.id"
                                                    >
                                                        {{ item.item.name }}
                                                    </option>
                                                </select>
                                                <InputError :message="delivaryForm.errors.item_id" />
                                            </td>
                                            <td>
                                                <Input v-model="delivaryForm.delivery_qty" type="number" step="any" class="form-control form-control-sm" placeholder="ডেলিভারি পাবে" :class="[delivaryForm.errors.delivery_qty ? 'border-danger mb-1' : '']" readonly/>
                                                <InputError :message="delivaryForm.errors.delivery_qty" />
                                            </td>
                                            <td>
                                                <Input v-model="delivaryForm.today_delivery_qty" type="number" step="any" min="0" @change="calculateRemainingDeliveryQty" class="form-control form-control-sm" placeholder="আজকের ডেলিভারি" :class="[delivaryForm.errors.today_delivery_qty ? 'border-danger mb-1' : '']"/>
                                                <InputError :message="delivaryForm.errors.today_delivery_qty" />
                                            </td>
                                            <td>
                                                <Input v-model="delivaryForm.remaining_delivery_qty" type="number" step="any" min="0" class="form-control form-control-sm" placeholder="ডেলিভারি বাকি" :class="[delivaryForm.errors.remaining_delivery_qty ? 'border-danger mb-1' : '']" readonly/>
                                                <InputError :message="delivaryForm.errors.remaining_delivery_qty" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">ড্রাইভারের তথ্য</label>
                                        <Input v-model="delivaryForm.driver_name" class="form-control form-control-sm mb-2" placeholder="ড্রাইভারের নাম" :class="[delivaryForm.errors.driver_name ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="delivaryForm.errors.driver_name" />

                                        <Input
                                          v-model="delivaryForm.driver_phone"
                                          class="form-control form-control-sm mb-2"
                                          placeholder="ড্রাইভারের ফোন"
                                          :class="[!isValidPhone && delivaryForm.driver_phone ? 'border-danger' : '', delivaryForm.errors.driver_phone ? 'border-danger mb-1' : '']"
                                          @input="validatePhone"
                                          required
                                        />
                                        <InputError :message="delivaryForm.errors.driver_phone" />

                                        <Input v-model="delivaryForm.truck_number" class="form-control form-control-sm mb-2" placeholder="ট্রাক নম্বর" :class="[delivaryForm.errors.truck_number ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="delivaryForm.errors.truck_number" />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">গাড়ি ভাড়া</label>
                                        <Input v-model="delivaryForm.delivery_rant" class="form-control form-control-sm" :class="[delivaryForm.errors.delivery_rant ? 'border-danger mb-1' : '']" style="height: 70px;text-align: center;font-size: 20px;"/>
                                        <InputError :message="delivaryForm.errors.delivery_rant" />
                                    </div>
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button class="btn btn-secondary btn-sm" @click="showDeliveryModal = false">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                    <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                      Save
                                </button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </transition>
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
