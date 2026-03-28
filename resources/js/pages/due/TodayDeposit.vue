<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { Input } from '@/components/ui/input';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    payments: Object,
    business_store: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
const loading = ref(false);
const spinBtn = ref(false);

const showEditModal = ref(false);
const selectedPayment = ref(null);

const editForm = ref({
    id: null,
    payment_date: '',
    paid_amount: 0,
    payment_method: 'cash',
    next_payment_date: '',
    account_number: '',
    check_number: '',
    note: '',
});

const openEditModal = (payment) => {
    selectedPayment.value = payment;
    editForm.value = {
        id: payment.id,
        payment_date: payment.payment_date,
        paid_amount: payment.paid_amount,
        payment_method: payment.method || 'cash',
        next_payment_date: payment.invoice?.next_payment_date ? payment.invoice.next_payment_date.split('T')[0] : '',
        account_number: payment.account_number || '',
        check_number: payment.check_number || '',
        note: payment.note || '',
    };
    showEditModal.value = true;
};

const submitUpdate = () => {
    spinBtn.value = true;
    axios.put(route('invoices.payments.update', editForm.value.id), editForm.value)
        .then(response => {
            spinBtn.value = false;
            showEditModal.value = false;
            toast.success('পেমেন্ট সফলভাবে আপডেট করা হয়েছে!');
            router.reload({ preserveState: true });
        })
        .catch(error => {
            spinBtn.value = false;
            toast.error(error.response?.data?.message || 'পেমেন্ট আপডেট করতে ব্যর্থ হয়েছে।');
        });
};

const confirmDelete = (payment) => {
    Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: "ডিলিট করলে ইনভয়েস এবং কাস্টমারের ব্যালেন্স আগের অবস্থায় ফিরে যাবে!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন!',
        cancelButtonText: 'বাতিল',
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(route('invoices.payments.delete', payment.id))
                .then(() => {
                    toast.success('পেমেন্ট সফলভাবে মুছে ফেলা হয়েছে!');
                    router.reload({ preserveState: true });
                })
                .catch(error => {
                    toast.error('পেমেন্ট মুছতে ব্যর্থ হয়েছে।');
                });
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};

watch([search, perPage], () => {
    router.get(route('due.today_deposit'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const printPaymentReceipt = (p) => {
  // Use existing receipt printing logic if needed, or implement a standard one
  const win = window.open('', '_blank');
  if (!win) return;
  
  const formatDateStr = (d) => {
      const date = new Date(d);
      return `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth()+1).padStart(2, '0')}-${date.getFullYear()}`;
  };

  const makeCopy = (label) => `
    <div class="receipt">
      <div class="topbar">
        <span class="badge">${label}</span>
        <span class="title">পেমেন্ট রসিদ</span>
      </div>
      <div class="brand">${props.business_store?.store_name || 'এম.এম.বি ব্রিকস'}</div>
      <div class="sub">${props.business_store?.address || ''}</div>
      <div class="contact">মোবাইল: ${props.business_store?.phone || ''}</div>
      <div class="meta">
        <div><b>চালান নং:</b> ${p.invoice?.invoice_no || ''}</div>
        <div><b>তারিখ:</b> ${formatDateStr(p.payment_date)}</div>
      </div>
      <div class="section">
        <div class="section-title">কাস্টমার</div>
        <div class="section-body">
          <div><b>নাম:</b> ${p.customer?.name || ''}</div>
          <div><b>ফোন:</b> ${p.customer?.phone || ''}</div>
        </div>
      </div>
      <div class="box">
          <div class="box-title">পেমেন্ট তথ্য</div>
          <div><b>মেথড:</b> ${p.method || '-'}</div>
          <div><b>পরিমাণ:</b> ${Number(p.paid_amount).toFixed(2)}</div>
      </div>
      <div class="summary">
        <div><span>বাকি:</span><span>${Number(p.due_amount).toFixed(2)}</span></div>
      </div>
    </div>
  `;

  const html = `<html><head><style>
    body { font-family: 'Arial', sans-serif; font-size: 12px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .receipt { border: 1px solid #ccc; padding: 15px; border-radius: 5px; }
    .brand { font-size: 18px; font-weight: bold; text-align: center; }
    .text-center { text-align: center; }
    .topbar { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .badge { background: #007bff; color: white; padding: 2px 5px; border-radius: 3px; }
    .section { border: 1px solid #eee; margin: 10px 0; padding: 5px; }
    .box { border: 1px solid #eee; padding: 5px; margin-bottom: 10px; }
    .summary { border-top: 1px solid #eee; padding-top: 5px; text-align: right; }
  </style></head><body><div class="grid">${makeCopy('কাস্টমার কপি')}${makeCopy('অফিস কপি')}</div></body></html>`;
  
  win.document.write(html);
  win.document.close();
  setTimeout(() => win.print(), 300);
};

</script>

<template>
    <Head title="আজকে জমা" />
    <AppLayout1>
        <div class="page-content">
            <div class="card radius-10">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 text-uppercase">
                                <i class="bx bx-list-ul me-2"></i>আজকে জমা (Collected Payments)
                            </h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" v-model="search" class="form-control form-control-sm" placeholder="খুঁজুন (নাম/ফোন/চালান নং)..." style="width: 250px;" />
                            <select v-model="perPage" class="form-select form-select-sm" style="width: 80px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>তারিখ</th>
                                    <th>কাস্টমার</th>
                                    <th>চালান নং</th>
                                    <th>পেমেন্ট মেথড</th>
                                    <th>পরিমাণ (টাকা)</th>
                                    <th>অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(payment, index) in payments.data" :key="payment.id">
                                    <td>{{ (payments.current_page - 1) * payments.per_page + index + 1 }}</td>
                                    <td>{{ formatDate(payment.payment_date) }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">{{ payment.customer?.name }}</span>
                                            <small class="text-muted">{{ payment.customer?.phone }}</small>
                                        </div>
                                    </td>
                                    <td>{{ payment.invoice?.invoice_no }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark text-capitalize">{{ payment.method }}</span>
                                        <div v-if="payment.account_number" class="small">Acc: {{ payment.account_number }}</div>
                                        <div v-if="payment.check_number" class="small">Check: {{ payment.check_number }}</div>
                                    </td>
                                    <td class="fw-bold text-success">{{ Number(payment.paid_amount).toFixed(2) }}</td>
                                    <td>
                                        <div class="dropdown dropstart">
                                            <button class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-nocaret" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-caret-left me-1"></i><i class='bx bx-dots-vertical-rounded' ></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" @click.prevent="printPaymentReceipt(payment)">
                                                        <i class="bx bx-printer me-2"></i>রসিদ প্রিন্ট
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" @click.prevent="openEditModal(payment)">
                                                        <i class="bx bx-edit me-2"></i>আপডেট করুন
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(payment)">
                                                        <i class="bx bx-trash me-2"></i>ডিলিট করুন
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="7" class="text-center text-muted">আজকে কোনো পেমেন্ট পাওয়া যায়নি।</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3" v-if="payments.links.length > 3">
                        <div class="small text-muted">
                            Showing {{ payments.from }} to {{ payments.to }} of {{ payments.total }} entries
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li v-for="(link, k) in payments.links" :key="k" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                                    <Link class="page-link" :href="link.url || '#'" v-html="link.label" />
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Payment Modal -->
        <transition name="modal">
            <div class="modal-backdrop" v-if="showEditModal"></div>
        </transition>
        <transition name="slide-fade">
            <div class="modal d-block" v-if="showEditModal">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-header" style="border-top: 2px solid #004882;">
                            <h6 class="modal-title">
                                <i class="bx bx-edit me-2"></i>
                                পেমেন্ট আপডেট করুন (চালান: {{ selectedPayment?.invoice?.invoice_no }})
                            </h6>
                            <button type="button" class="btn-close" @click="showEditModal = false"></button>
                        </div>
                        <form @submit.prevent="submitUpdate">
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <div class="customer-danger text-center">
                                            <h4 class="text-danger">বাকিঃ {{ Number(selectedPayment?.invoice?.due_amount || 0).toFixed(2) }}</h4>
                                            <p class="text-danger"><strong>পরিশোধের তারিখ:</strong> {{ formatDate(selectedPayment?.invoice?.next_payment_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label">তারিখ</label>
                                        <Input type="date" v-model="editForm.payment_date" class="form-control form-control-sm" required />
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label">পেমেন্ট মেথড</label>
                                        <select v-model="editForm.payment_method" class="form-control form-control-sm" required>
                                            <option value="cash">ক্যাশ</option>
                                            <option value="mobile_banking">মোবাইল ব্যাংকিং</option>
                                            <option value="check">চেক</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label">পরিশোধিত পরিমাণ <span class="text-danger">*</span></label>
                                        <Input type="number" step="any" min="0" v-model="editForm.paid_amount" class="form-control form-control-sm" required />
                                    </div>
                                    <div class="col-sm-6" v-if="editForm.payment_method === 'mobile_banking'">
                                        <label class="form-label">একাউন্ট নম্বর</label>
                                        <Input v-model="editForm.account_number" class="form-control form-control-sm" placeholder="একাউন্ট নম্বর" />
                                    </div>
                                    <div class="col-sm-6" v-if="editForm.payment_method === 'check'">
                                        <label class="form-label">চেক নম্বর</label>
                                        <Input v-model="editForm.check_number" class="form-control form-control-sm" placeholder="চেক নম্বর" />
                                    </div>
                                    <div class="col-sm-6" v-if="selectedPayment?.invoice?.due_amount - (editForm.paid_amount - selectedPayment?.paid_amount) > 0">
                                        <label class="form-label text-danger">পরবর্তী পেমেন্টের তারিখ</label>
                                        <Input type="date" v-model="editForm.next_payment_date" class="form-control form-control-sm border-danger" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="editForm.note" class="form-control form-control-sm" placeholder="নোট" />
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" @click="showEditModal = false"><i class="bx bx-x me-1"></i> বাতিল</button>
                                <button type="submit" class="btn btn-primary btn-sm" :disabled="spinBtn">
                                    <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                    <i v-else class="bx bx-save me-1"></i> আপডেট করুন
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </AppLayout1>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
}
.modal {
    z-index: 1051;
}
.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
