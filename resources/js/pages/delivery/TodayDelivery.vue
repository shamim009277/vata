<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import DeliveryChallan from './Print.vue';
import { Head, useForm, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';
import axios from 'axios'

const props = defineProps({
    deliveries: Object,
    items: Object,
    deliveryNumber: String,
});

const selectedDelivery = ref(null);
const selectedInvoice = ref(null);
const printSection = ref(null);

const showModal = ref(false);
const showDetailsModal = ref(false);
const showPrintModal = ref(false);
const spinBtn = ref(false);
const loading = ref(true);
const isValidPhone = ref(true);


const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
const date = ref(props.filters?.date || new Date().toISOString().split('T')[0]);

const form = useForm({
    invoice_no: '',
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
    due_amount: 0,
});

const createDelivery = () => {
    showModal.value = true;
    form.reset();
};

const fetchInvoice = async () => {
    if (form.invoice_no.length < 6) {
        selectedInvoice.value = false
        //form.reset();
        return
    }

    try {
        const response = await axios.get(`/delivery/invoice`, {
            params: { q: form.invoice_no }
        })
        selectedInvoice.value = response.data

        form.customer_name = response.data.customer.name
        form.customer_id = response.data.customer.id
        form.phone = response.data.customer.phone
        form.address = response.data.customer.address
        if (response.data.delivery_date) {
            form.delivery_date = new Date(response.data.delivery_date).toISOString().split('T')[0];
        } else {
            form.delivery_date = new Date().toISOString().split('T')[0];
        }
        form.due_amount = response.data.due_amount
    } catch (error) {
        toast.error('আইডি অনুসন্ধান করা যায় না');
        form.invoice_no = ''
        console.error(error)
    }
}

const calculateRemainingDeliveryQty = () => {
    form.remaining_delivery_qty = form.delivery_qty - form.today_delivery_qty;
};

const validateDeliveryQty = () => {
    if (form.today_delivery_qty > form.delivery_qty) {
        toast.error('আজকের ডেলিভারি পাবে সর্বোচ্চ ' + form.delivery_qty + ' পর্যন্ত হতে পারে');
        form.today_delivery_qty = form.delivery_qty;
    }   
};

const validatePhone = () => {
    const regex = /^01[3-9][0-9]{8}$/;
    isValidPhone.value = regex.test(form.driver_phone);
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

const showDeliveryDetails = (delivery) => {
    selectedDelivery.value = delivery;
    showDetailsModal.value = true;
};

const printChallan = (delivery) => {
    selectedDelivery.value = delivery;
    showPrintModal.value = true;
}

const createDelivary = () => {
    spinBtn.value = true;
    form.post(route('deliveries.store'), {
        onSuccess: () => {
            spinBtn.value = false;
            showModal.value = false;
            form.reset();
        },
        onError: () => {
            spinBtn.value = false;
        },
    });
}

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

        if (!result.isConfirmed) {
            return;
        }

        form.delete(`/deliveries/${id}`, {
            preserveScroll: true,
            preserveState: true,

            onSuccess: (page) => {
                // Backend যদি success session return করে
                if (page.props.flash?.success) {
                    Swal.fire({
                        title: 'মুছে ফেলা হয়েছে!',
                        text: page.props.flash.success,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        customClass: { title: 'swal-title-small' },
                    });
                }
            },

            onError: (errors) => {
                Swal.fire({
                    title: 'ত্রুটি!',
                    text: Object.values(errors)[0] ?? 'কিছু ভুল হয়েছে, দয়া করে আবার চেষ্টা করুন।',
                    icon: 'error',
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: { title: 'swal-title-small' },
                })
            },

            onFinish: () => {
                
            }
        });

    });
}

// Print modal content
const printDelivery = () => {
  if (!printSection.value) return;

  const printContent = printSection.value.innerHTML;
  const newWin = window.open('', '_blank');
  newWin.document.write(`
    <html>
      <head>
        <title>ডেলিভারি চালান</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; }
          .challan-container { display: flex; justify-content: space-between; gap: 20px; }
          .challan-box { width: 48%; border: 1px solid #000; padding: 15px; box-sizing: border-box; }
          h4 { font-weight: bold; font-size: 16px; margin-bottom: 10px; }
          table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
          th, td { border: 1px solid #000; padding: 5px; text-align: left; }
          .text-center { text-align: center; }
          .border-bottom { border-bottom: 2px solid #000; }
          .row-info { display: flex; gap: 20px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; background-color: #fdfdfd; margin-bottom: 15px; }
          .info-col-left { width: 50%; text-align: left; }
          .info-col-right { width: 50%; text-align: right; }
          .info-col-left p, .info-col-right p { margin: 5px 0; font-size: 13px; }
          .info-col-left p strong, .info-col-right p strong { color: #004882; font-weight: 600; }
          .signature { display: flex; justify-content: space-between; margin-top: 25px; }
          .signature div { width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px; }
          @media print {
            body { margin: 0; }
            .challan-container { display: flex; justify-content: space-between; gap: 20px; }
            .challan-box { page-break-inside: avoid; }
          }
        </style>
      </head>
      <body>
        ${printContent}
      </body>
    </html>
  `);
  newWin.document.close();
  newWin.focus();
  newWin.print();
  newWin.close();
};



watch(() => form.item_id, (newVal) => {
    if (!selectedInvoice.value) return;

    const selectedItem = selectedInvoice.value.invoice_details.find(
        (d) => d.item.id === newVal
    );

    if (selectedItem) {
        form.delivery_qty =
          Number(selectedItem.quantity || 0) -
          Number(selectedItem.delivery_quantity || 0);
      } else {
        form.delivery_qty = 0;
      }
      calculateRemainingDeliveryQty();
});

watch([search, perPage,date], () => {
    router.get(route('delivery.index'), {
        search: search.value,
        perPage: perPage.value,
        date: date.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

watch(
    () => usePage().props.flash.error,
    (value) => {
        if (value) {
            Swal.fire({
                title: 'ত্রুটি!',
                text: value,
                icon: 'error',
                timer: 1800,
                showConfirmButton: false
            })
        }
    }
)

// Simulate loading
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 500);
});
</script>

<template>
    <Head title="আজকের ডেলিভারি" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> আজকের ডেলিভারি
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

                                <!-- Create Button -->
                                <button class="btn btn-primary btn-sm" @click="createDelivery">
                                    <i class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                    নতুন ডেলিভারি
                                </button>
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
                                            <th>চালান নাম্বার</th>
                                            <th>কাস্টমার</th>
                                            <th>ঠিকানা</th>
                                            <th>শ্রেণি</th>
                                            <th>ক্রয়</th>
                                            <th>ডেলিভারি</th>
                                            <th>ডেলিভারি বাকি</th>
                                            <th>ড্রাইভার</th>
                                            <th>ড্রাইভার ফোন</th>
                                            <th>ট্রাক</th>
                                            <th>তারিখ</th>
                                            <th>বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="delivary in deliveries.data" :key="delivary.id">
                                            <td>{{ delivary.delivery_no }}</td>
                                            <td>{{ delivary.invoice_no }}</td>
                                            <td>
                                                {{ delivary.customer.name }}<br>
                                                {{ delivary.customer.phone }}
                                            </td>
                                            <td>{{ delivary.customer.address }}</td>
                                            <td>{{ delivary.item.name }}</td>
                                            <td>{{ delivary.invoice.quantity }}</td>
                                            <td>{{ delivary.quantity }}</td>
                                            <td>{{ delivary.invoice.quantity - delivary.quantity }}</td>
                                            <td>{{ delivary.driver_name }}</td>
                                            <td>{{ delivary.driver_phone }}</td>
                                            <td>{{ delivary.truck_number }}</td>
                                            <td>{{ formatDate(delivary.delivery_date) }}</td>
                                            <td>
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="printChallan(delivary)">
                                                                <i class="bx bx-printer"></i> প্রিন্ট ডেলিভারি
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showUpdateDetails(delivary)">
                                                                <i class="bx bx-box"></i> আপডেট করুণ
                                                            </a>
                                                        </li>
                                                        <li><a class="dropdown-item" href="#" @click.prevent="confirmDelete(delivary.id)">
                                                            <i class="bx bx-trash"></i> ডেলিভারি মুছে ফেলুন
                                                        </a></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showDeliveryDetails(delivary)">
                                                                <i class="bx bx-show"></i> ডেলিভারি বিস্তারিত
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
                                            Showing {{ deliveries.from }} to {{ deliveries.to }} of {{ deliveries.total }} entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="col-sm-12 col-md-7">
                                        <nav class="dataTables_paginate paging_simple_numbers" aria-label="Pagination">
                                            <ul class="pagination justify-content-md-end flex-wrap" style="gap: 4px;">
                                                <li v-for="link in deliveries.links"
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
                                <h5 class="modal-title font-bold">
                                    <i class="bx bx-file me-2"></i>
                                    নতুন ডেলিভারি 🚚
                                </h5>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>


                        <form @submit.prevent="createDelivary">
                            <div class="modal-body" v-if="showModal">
                                <div class="row m-1">
                                    <div class="text-center font-bold" style="background-color: #f2d0d0; padding: 6px 8px;color: #d00808;border-radius: 2px;" v-if="selectedInvoice">
                                        {{ selectedInvoice.customer.name }} এর বাকি রয়েছেঃ {{ selectedInvoice.due_amount }} টাকা
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">ডেলিভারি নং<span class="text-danger">*</span></label>
                                        <Input v-model="form.delivery_no" class="form-control form-control-sm" placeholder="ডেলিভারি নং" :class="[form.errors.delivery_no ? 'border-danger mb-1' : '']" required readonly/>
                                        <InputError :message="form.errors.delivery_no" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0">
                                        <label class="form-label">চালান নং<span class="text-danger">*</span></label>
                                        <Input v-model="form.invoice_no" class="form-control form-control-sm" placeholder="চালান নং" @input="fetchInvoice" :class="[form.errors.invoice_no ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.invoice_no" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ডেলিভারি তারিখ<span class="text-danger">*</span></label>
                                        <Input type="date" v-model="form.delivery_date" class="form-control form-control-sm" placeholder="চালান নং" :class="[form.errors.delivery_date ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.delivery_date" />
                                    </div>

                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0">
                                        <label class="form-label">কাস্টমারের নাম<span class="text-danger">*</span></label>
                                        <input type="hidden" v-model="form.customer_id"/>
                                        <Input v-model="form.customer_name" class="form-control form-control-sm" placeholder="কাস্টমারের নাম" :class="[form.errors.customer_name ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.customer_name" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">ফোন নম্বর<span class="text-danger">*</span></label>
                                        <Input v-model="form.phone" class="form-control form-control-sm" placeholder="ফোন নম্বর" :class="[form.errors.phone ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">ডেলিভারি ঠিকানা<span class="text-danger">*</span></label>
                                        <Input v-model="form.address" class="form-control form-control-sm" placeholder="ডেলিভারি ঠিকানা" :class="[form.errors.address ? 'border-danger mb-1' : '']" required/>
                                        <InputError :message="form.errors.address" />
                                    </div>

                                    <div class="col-sm-6 col-md-8 mb-3 pe-md-0 pe-sm-0">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="form.note" class="form-control form-control-sm" placeholder="নোট" :class="[form.errors.note ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="form.errors.note" />
                                    </div>

                                    <div v-if="parseFloat(form.remaining_delivery_qty) > 0" class="col-sm-6 col-md-4 mb-3">
                                        <label class="form-label">পরবর্তী ডেলিভারি তারিখ<span class="text-danger">*</span></label>
                                        <Input
                                            v-model="form.next_delivery_date"
                                            type="date"
                                            class="form-control form-control-sm"
                                            :class="[form.errors.next_delivery_date ? 'border-danger mb-1' : '']"
                                            required
                                        />
                                        <InputError :message="form.errors.next_delivery_date" />
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
                                                <select v-model="form.item_id" class="form-control form-control-sm">
                                                    <option value="">-- আইটেম নির্বাচন করুন --</option>
                                                    <option v-if="selectedInvoice" 
                                                        v-for="(item, i) in selectedInvoice.invoice_details"
                                                        :key="i"
                                                        :value="item.item.id"
                                                    >
                                                        {{ item.item.name }}
                                                    </option>
                                                </select>
                                                <InputError :message="form.errors.item_id" />
                                            </td>
                                            <td>
                                                <Input v-model="form.delivery_qty" type="number" step="any" class="form-control form-control-sm" placeholder="ডেলিভারি পাবে" :class="[form.errors.delivery_qty ? 'border-danger mb-1' : '']" readonly/>
                                                <InputError :message="form.errors.delivery_qty" />
                                            </td>
                                            <td>
                                                <Input v-model="form.today_delivery_qty" type="number" step="any" min="0" @change="calculateRemainingDeliveryQty" @input="validateDeliveryQty()" class="form-control form-control-sm" placeholder="আজকের ডেলিভারি" :class="[form.errors.today_delivery_qty ? 'border-danger mb-1' : '']"/>
                                                <InputError :message="form.errors.today_delivery_qty" />
                                            </td>
                                            <td>
                                                <Input v-model="form.remaining_delivery_qty" type="number" step="any" min="0" class="form-control form-control-sm" placeholder="ডেলিভারি বাকি" :class="[form.errors.remaining_delivery_qty ? 'border-danger mb-1' : '']" readonly/>
                                                <InputError :message="form.errors.remaining_delivery_qty" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">ড্রাইভারের তথ্য</label>
                                        <Input v-model="form.driver_name" class="form-control form-control-sm mb-2" placeholder="ড্রাইভারের নাম" :class="[form.errors.driver_name ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="form.errors.driver_name" />

                                        <Input
                                          v-model="form.driver_phone"
                                          class="form-control form-control-sm mb-2"
                                          placeholder="ড্রাইভারের ফোন"
                                          :class="[!isValidPhone && form.driver_phone ? 'border-danger' : '', form.errors.driver_phone ? 'border-danger mb-1' : '']"
                                          @input="validatePhone"
                                          required
                                        />
                                        <InputError :message="form.errors.driver_phone" />

                                        <Input v-model="form.truck_number" class="form-control form-control-sm mb-2" placeholder="ট্রাক নম্বর" :class="[form.errors.truck_number ? 'border-danger mb-1' : '']"/>
                                        <InputError :message="form.errors.truck_number" />
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">গাড়ি ভাড়া</label>
                                        <Input v-model="form.delivery_rant" class="form-control form-control-sm" :class="[form.errors.delivery_rant ? 'border-danger mb-1' : '']" style="height: 70px;text-align: center;font-size: 20px;"/>
                                        <InputError :message="form.errors.delivery_rant" />
                                    </div>
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button class="btn btn-secondary btn-sm" @click="showModal = false">Close</button>
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
                                    ডেলিভারি বিস্তারিত
                                </h6>
                                <button type="button" class="btn-close" @click="showDetailsModal = false"></button>
                            </div>
            
                            <div class="modal-body" v-if="selectedDelivery">
                                <div class="row mb-3">
                                    <div class="col-md-6 challan-info">
                                        <p><strong>ডেলিভারি নং:</strong> {{ selectedDelivery.invoice.invoice_no }}</p>
                                        <p><strong>ডেলিভারি তৈরি করেছেন:</strong> {{ selectedDelivery.creator.name }}</p>
                                    </div>
                                    <div class="col-md-6 company-info">
                                        <h4 class="font-bold text-primary">এম.এম.বি ব্রিকস</h4>
                                        <p>হিলালিপাড়া,কাটাবাড়ি,গোবিন্দগঞ্জ</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>নাম:</strong> {{ selectedDelivery.customer.name }}</p>
                                            <p><strong>ফোন:</strong> {{ selectedDelivery.customer.phone }}</p>
                                            <p><strong>ঠিকানা:</strong> {{ selectedDelivery.customer.address }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>কাস্টমার আইডি:</strong> {{ selectedDelivery.customer.id }}</p>
                                            <p><strong>ডেলিভারি তারিখ:</strong> {{ formatDate(selectedDelivery.delivery_date) }}</p>
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-4">
                                        <div class="customer">
                                            <p><strong>তারিখ:</strong>{{ formatDate(selectedDelivery.delivery_date) }}</p>
                                            <p><strong>সময়:</strong> </p>
                                            <p><strong>সিজন:</strong> {{ selectedDelivery.season }}</p>
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
                                            <th>ডেলিভারি</th>
                                            <th>ডেলিভারি বাকি</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>{{ selectedDelivery.item.name }}</td>
                                            <td>{{ selectedDelivery.rate }}</td>
                                            <td>{{ selectedDelivery.quantity }}</td>
                                            <td>{{ selectedDelivery.amount }}</td>
                                            <td>{{ selectedDelivery.delivery_qty }}</td>
                                            <td>{{ selectedDelivery.quantity - selectedDelivery.delivery_qty }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
            
                            <div class="modal-footer">
                                <button class="btn btn-secondary btn-sm" @click="showDetailsModal = false">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>


            <!-- Print Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showPrintModal"></div>
            </transition>

            <transition name="slide-fade">
    <div class="modal d-block" v-if="showPrintModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <!-- Header -->
          <div class="modal-header" style="border-top: 3px solid #004882;">
            <h6 class="modal-title font-bold">
              <i class="bx bx-file me-2"></i>
              ডেলিভারি প্রিন্ট প্রিভিউ
            </h6>
            <button type="button" class="btn-close" @click="showPrintModal = false"></button>
          </div>

          <!-- Body -->
          <div class="modal-body" v-if="selectedDelivery">
            <div class="d-flex justify-end gap-2 mb-3">
              <button class="btn btn-secondary btn-sm" @click="showPrintModal = false">Cancel</button>
              <button class="btn btn-primary btn-sm" @click="printDelivery">Print</button>
            </div>

            <!-- Printable Section -->
            <div ref="printSection" class="print-challan">
              <div class="challan-container">
                <!-- Left Side: Customer Copy -->
                <div class="challan-box">
                  <h4 class="text-center font-semibold border-bottom pb-1 mb-2">Customer Copy</h4>
                  <DeliveryChallan :delivery="selectedDelivery" />
                </div>

                <!-- Right Side: Office Copy -->
                <div class="challan-box">
                  <h4 class="text-center font-semibold border-bottom pb-1 mb-2">Office Copy</h4>
                  <DeliveryChallan :delivery="selectedDelivery" />
                </div>
              </div>
            </div>

          </div>
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
