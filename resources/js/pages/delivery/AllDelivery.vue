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

const page = usePage();

const selectedDelivery = ref(null);
const selectedInvoice = ref(null);
const printSection = ref(null);

const showModal = ref(false);
const showDetailsModal = ref(false);
const showPrintModal = ref(false);
const showUpdateModal = ref(false);
const spinBtn = ref(false);
const loading = ref(true);
const isValidPhone = ref(true);


const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
const start_date = ref(props.filters?.start_date || '');
const end_date = ref(props.filters?.end_date || '');

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
    send_sms: false,
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

const showUpdateDetails = async (delivery) => {
    selectedDelivery.value = delivery;
    form.invoice_no = delivery.invoice_no;
    form.delivery_no = delivery.delivery_no;
    form.customer_name = delivery.customer?.name;
    form.phone = delivery.customer?.phone;
    form.address = delivery.address;
    form.delivery_date = delivery.delivery_date;
    form.note = delivery.note;
    form.item_id = delivery.item_id;
    form.customer_id = delivery.customer_id;
    form.today_delivery_qty = delivery.delivery_qty;
    form.driver_name = delivery.driver_name;
    form.driver_phone = delivery.driver_phone;
    form.truck_number = delivery.truck_number;
    form.delivery_rant = delivery.delivery_rant;
    
    showUpdateModal.value = true;

    // Fetch invoice details to populate calculations
    try {
        const response = await axios.get(`/delivery/invoice`, {
            params: { q: form.invoice_no }
        });
        selectedInvoice.value = response.data;
        
        // Trigger watcher logic manually or let watcher handle it if item_id triggers it
        // Since item_id is already set, watcher might not trigger if it was same.
        // So we manually calculate.
        const selectedItem = selectedInvoice.value.invoice_details.find(
            (d) => d.item.id === form.item_id
        );

        if (selectedItem) {
             let qty = Number(selectedItem.quantity || 0) - Number(selectedItem.delivery_quantity || 0);
             // Add back current delivery qty
             qty += Number(delivery.delivery_qty || 0);
             form.delivery_qty = qty;
        } else {
             form.delivery_qty = 0;
        }
        calculateRemainingDeliveryQty();

    } catch (error) {
        console.error(error);
        toast.error('ইনভয়েস তথ্য লোড করতে সমস্যা হয়েছে');
    }
};

const updateDelivery = () => {
    form.put(route('deliveries.update', selectedDelivery.value.id), {
        onSuccess: () => {
            showUpdateModal.value = false;
            form.reset();
            toast.success('ডেলিভারি আপডেট সফল হয়েছে!');
        },
        onError: (errors) => {
             if (errors.message) {
                 toast.error(errors.message);
             }
        }
    });
};

const printChallan = (delivery) => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    };

    const formatTime12Hour = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes}:${seconds} ${ampm}`;
    };

    const deliveryDetailsHtml = (title) => `
        <div class="challan-box">
            <div class="header">
                <div class="title-section">
                    <div class="copy-title">${title}</div>
                    <div class="challan-info">
                        <p><strong>চালান নং:</strong> ${delivery.delivery_no}</p>
                        <p><strong>ইনভয়েস নং:</strong> ${delivery.invoice_no}</p>
                        <p><strong>তৈরি করেছেন:</strong> ${delivery.creator?.name || 'Admin'}</p>
                    </div>
                </div>
                <div class="company-info">
                    <h1 class="company-name">${usePage().props.business_store?.short_name || usePage().props.business_store?.store_name || 'এম.এম.বি ব্রিকস'}</h1>
                    <p class="company-address">${usePage().props.business_store?.address || 'মাস্টারবাড়ি মির্জাপুর জয়দেবপুর'}</p>
                    <p class="company-phone">মোবাইল: ${usePage().props.business_store?.phone || '01457636958,01746345643'}</p>
                </div>
            </div>
            
            <h2 style="text-align: center; font-size: 18px; margin: 0 0 10px 0; text-decoration: underline;">ডেলিভারি চালান</h2>

            <div class="info-grid">
                <div class="info-box">
                    <p><strong>কাস্টমার:</strong> ${delivery.customer?.name || ''}</p>
                    <p><strong>ফোন:</strong> ${delivery.customer?.phone || ''}</p>
                    <p><strong>ঠিকানা:</strong> ${delivery.address || delivery.customer?.address || ''}</p>
                </div>
                <div class="info-box">
                    <p><strong>ড্রাইভার:</strong> ${delivery.driver_name || '-'}</p>
                    <p><strong>ড্রাইভার ফোন:</strong> ${delivery.driver_phone || '-'}</p>
                    <p><strong>ট্রাক নম্বর:</strong> ${delivery.truck_number || '-'}</p>
                </div>
                <div class="info-box text-right">
                    <p><strong>তারিখ:</strong> ${formatDate(delivery.delivery_date)}</p>
                    <p><strong>সময়:</strong> ${formatTime12Hour(delivery.created_at)}</p>
                    <p><strong>সিজন:</strong> ${delivery.season || ''}</p>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 55%">বিবরণ</th>
                        <th style="width: 20%">ট্রাক</th>
                        <th style="width: 20%">পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>${delivery.item?.name || ''}</td>
                        <td>${delivery.truck_number || '-'}</td>
                        <td>${delivery.quantity}</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer-section">
                <div class="signatures">
                    <div class="sig-box"><p>গ্রহীতার স্বাক্ষর</p></div>
                    <div class="sig-box"><p>কর্তৃপক্ষের স্বাক্ষর</p></div>
                </div>
            </div>
        </div>
    `;

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>ডেলিভারি চালান - ${delivery.delivery_no}</title>
            <style>
                @page { size: A4 landscape; margin: 10mm; }
                body { font-family: 'Bangla', sans-serif; margin: 0; padding: 0; background: #fff; }
                .container { display: flex; gap: 20mm; width: 100%; height: 100%; box-sizing: border-box; }
                .challan-box { 
                    flex: 1; 
                    border: 1px solid #333; 
                    padding: 20px; 
                    position: relative; 
                    height: 100%; 
                    box-sizing: border-box; 
                    background: #fff;
                    display: flex;
                    flex-direction: column;
                }
                
                .header { display: flex; justify-content: space-between; border-bottom: 2px solid #004882; padding-bottom: 10px; margin-bottom: 15px; }
                .company-info { text-align: right; }
                .company-name { font-size: 24px; font-weight: bold; margin: 0; color: #2c3e50; }
                .company-address { font-size: 12px; margin: 0; color: #2c3e50; }
                .company-phone { font-size: 12px; margin: 0; font-weight: bold; color: #2c3e50; }
                
                .title-section { display: flex; flex-direction: column; gap: 5px; }
                .copy-title { 
                    font-size: 14px; 
                    font-weight: bold; 
                    background: #004882; 
                    color: #fff; 
                    padding: 4px 12px; 
                    border-radius: 4px; 
                    width: fit-content;
                }
                .challan-info p { margin: 2px 0; font-size: 13px; }
                
                .info-grid { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 20px; }
                .info-box { background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #eee; }
                .info-box p { margin: 4px 0; }
                .text-right { text-align: right; }
                
                .table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px; }
                .table th { background-color: #004882; color: #fff; padding: 8px; text-align: left; border: 1px solid #004882; }
                .table td { border: 1px solid #ddd; padding: 8px; }
                .table tr:nth-child(even) { background-color: #f9f9f9; }
                
                .footer-section { margin-top: auto; padding-top: 20px; border-top: 1px solid #eee; }
                
                .signatures { display: flex; justify-content: space-between; margin-top: 50px; font-size: 13px; }
                .sig-box { border-top: 1px solid #333; padding-top: 5px; width: 40%; text-align: center; color: #333; }
                
                /* Print specific adjustments */
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                    .container { gap: 10mm; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                ${deliveryDetailsHtml('কাস্টমার কপি')}
                ${deliveryDetailsHtml('অফিস কপি')}
            </div>
            <script>
                window.onload = function() { setTimeout(function() { window.print(); }, 500); }
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
}

const printThermal = (delivery) => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    };

    const formatTime12Hour = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return `${hours}:${minutes} ${ampm}`;
    };

    const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Challan - ${delivery.delivery_no}</title>
            <style>
                @page { size: 80mm auto; margin: 0; }
                body { 
                    font-family: 'Bangla', sans-serif; 
                    margin: 0; 
                    padding: 5px; 
                    background: #fff; 
                    width: 72mm;
                    font-size: 12px;
                    color: #000;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .bold { font-weight: bold; }
                
                .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
                .company-name { font-size: 16px; margin: 0; font-weight: bold; }
                .company-info { font-size: 10px; margin: 2px 0; }
                
                .invoice-details { margin-bottom: 10px; font-size: 10px; }
                .customer-details { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
                
                table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                th { border-bottom: 1px solid #000; padding: 2px; text-align: left; font-size: 10px; }
                td { padding: 2px; vertical-align: top; font-size: 11px; }
                .amount-col { text-align: right; }
                
                .footer { margin-top: 15px; text-align: center; font-size: 10px; border-top: 1px solid #000; padding-top: 5px; }
                
                @media print {
                    body { margin: 0; padding: 5px; }
                }
            </style>
        </head>
        <body>
            <div class="header text-center">
        <div class="company-name">${usePage().props.business_store?.short_name || usePage().props.business_store?.store_name || 'এম.এম.বি ব্রিকস'}</div>
        <p class="company-info">${usePage().props.business_store?.address || 'মাস্টারবাড়ি মির্জাপুর জয়দেবপুর'}</p>
        <p class="company-info">মোবাইল: ${usePage().props.business_store?.phone || '01457636958,01746345643'}</p>
    </div>
            
            <h2 class="text-center" style="font-size: 14px; margin: 5px 0 10px 0; text-decoration: underline;">ডেলিভারি চালান</h2>

            <div class="invoice-details">
                <div style="display: flex; justify-content: space-between;">
                    <span>চালান: ${delivery.delivery_no}</span>
                    <span>তারিখ: ${formatDate(delivery.delivery_date)}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>ইনভয়েস: ${delivery.invoice_no}</span>
                    <span>তৈরি: ${delivery.creator?.name || 'Admin'}</span>
                </div>
            </div>
            
            <div class="customer-details">
                <p style="margin: 2px 0;"><strong>কাস্টমার:</strong> ${delivery.customer?.name || 'Walk-in'}</p>
                <p style="margin: 2px 0;"><strong>ফোন:</strong> ${delivery.customer?.phone || ''}</p>
                <p style="margin: 2px 0;"><strong>ঠিকানা:</strong> ${delivery.address || delivery.customer?.address || ''}</p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%">আইটেম</th>
                        <th style="width: 25%">ট্রাক</th>
                        <th style="width: 25%" class="amount-col">পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>${delivery.item?.name || 'Item'}</td>
                        <td>${delivery.truck_number || '-'}</td>
                        <td class="amount-col bold">${delivery.quantity}</td>
                    </tr>
                </tbody>
            </table>

            <div class="customer-details" style="border-bottom: none; border-top: 1px dashed #000; padding-top: 5px;">
                <p style="margin: 2px 0;"><strong>ড্রাইভার:</strong> ${delivery.driver_name || '-'}</p>
                <p style="margin: 2px 0;"><strong>ড্রাইভার ফোন:</strong> ${delivery.driver_phone || '-'}</p>
            </div>
            
            <div class="footer">
                <p>ধন্যবাদ, আবার আসবেন!</p>
            </div>
            
            <script>
                window.onload = function() { setTimeout(function() { window.print(); }, 500); }
            <\/script>
        </body>
        </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
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
        let qty = Number(selectedItem.quantity || 0) - Number(selectedItem.delivery_quantity || 0);
        
        // If updating and this is the original item, add back the current delivery qty
        if (showUpdateModal.value && selectedDelivery.value && selectedDelivery.value.item_id === newVal) {
             qty += Number(selectedDelivery.value.delivery_qty || 0);
        }

        form.delivery_qty = qty;
    } else {
        form.delivery_qty = 0;
    }
    calculateRemainingDeliveryQty();
});

watch([search, perPage, start_date, end_date], () => {
    router.get(route('delivery.all'), {
        search: search.value,
        perPage: perPage.value,
        start_date: start_date.value,
        end_date: end_date.value,
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
    <Head title="সব ডেলিভারি" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> সব ডেলিভারি
                                </a>
                            </h6>

                            <!-- Right-side controls -->
                            <div class="d-flex align-items-center gap-2">
                                <!-- Date Filter -->
                                <input 
                                    type="date"
                                    v-model="start_date"
                                    class="form-control form-control-sm"
                                    style="width: 140px;"
                                    placeholder="শুরুর তারিখ"
                                />
                                <span class="text-muted">-</span>
                                <input 
                                    type="date"
                                    v-model="end_date"
                                    class="form-control form-control-sm"
                                    style="width: 140px;"
                                    placeholder="শেষ তারিখ"
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
                                                                <i class="bx bx-printer"></i> প্রিন্ট ডেলিভারি (নরমাল)
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="printThermal(delivary)">
                                                                <i class="bx bx-receipt"></i> প্রিন্ট ডেলিভারি (থার্মাল)
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
                                        {{ selectedInvoice.customer.name }} এর বাকি রয়েছেঃ {{ Number(selectedInvoice.customer.due_amount || 0).toFixed(2) }} টাকা
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
            
                            <div class="modal-footer d-flex justify-content-between">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="smsSwitchAll" v-model="form.send_sms">
                                    <label class="form-check-label" for="smsSwitchAll">SMS পাঠান</label>
                                </div>
                                <div>
                                    <button class="btn btn-secondary btn-sm me-2" @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                        Save
                                    </button>
                                </div>
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
                                        <h4 class="font-bold text-primary">{{ page.props.business_store?.short_name || page.props.business_store?.store_name || 'এম.এম.বি ব্রিকস' }}</h4>
                                        <p>{{ page.props.business_store?.address || 'হিলালিপাড়া,কাটাবাড়ি,গোবিন্দগঞ্জ' }}</p>
                                        <p>মোবাইল: {{ page.props.business_store?.phone || '০১৭১৩৭০৫৮৫৯' }}</p>
                                        <p v-if="selectedDelivery.customer" class="text-danger fw-bold">কাস্টমারের বাকি: {{ Number(selectedDelivery.customer.due_amount || 0).toFixed(2) }} টাকা</p>
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

            <!-- Update Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showUpdateModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showUpdateModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h5 class="modal-title font-bold">
                                    <i class="bx bx-edit me-2"></i>
                                    ডেলিভারি আপডেট করুন 📝
                                </h5>
                                <button type="button" class="btn-close" @click="showUpdateModal = false"></button>
                            </div>

                        <form @submit.prevent="updateDelivery">
                            <div class="modal-body" v-if="showUpdateModal">
                                <div class="row m-1">
                                    <div class="text-center font-bold" style="background-color: #f2d0d0; padding: 6px 8px;color: #d00808;border-radius: 2px;" v-if="selectedInvoice">
                                        {{ selectedInvoice.customer.name }} এর বাকি রয়েছেঃ {{ Number(selectedInvoice.customer.due_amount || 0).toFixed(2) }} টাকা
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
                                        <Input v-model="form.invoice_no" class="form-control form-control-sm" placeholder="চালান নং" :class="[form.errors.invoice_no ? 'border-danger mb-1' : '']" required readonly/>
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
                                                <select v-model="form.item_id" class="form-control form-control-sm" disabled>
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
                                <button class="btn btn-secondary btn-sm" @click="showUpdateModal = false">Close</button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i v-if="form.processing" class="bx bx-loader bx-spin"></i>
                                    <i v-else class="fadeIn animated bx bx-edit me-1" style="font-size: small;"></i>
                                      Update
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
