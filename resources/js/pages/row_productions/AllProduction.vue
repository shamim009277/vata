<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm,router,Link } from '@inertiajs/vue3';
import { ref, watch, onMounted,computed } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    productions: Object,
    fields: Object,
    filters: Object,
});

const fieldsList = ref(props.fields);
const showModal = ref(false);
const selectedProduction = ref(null);
const editProduction = ref(false);
const spinBtn = ref(false);
const loading = ref(true);

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
//const date = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const start_date = ref('');
const end_date = ref('');


// Main form
const form = useForm({
    product_date: new Date().toISOString().split('T')[0],
    note: '',
    quantity: 0,
    items: [],
    id: ''
});

const lockForm = useForm({
    product_date: new Date().toISOString().split('T')[0],
});

// Modal and row functions
const createProduction = () => {
    showModal.value = true;
    editProduction.value = false;
    form.reset();
    form.items = [];
};

const alreadySelectedIds = computed(() => {
    return form.items.map(i => i.field_list_id).filter(id => id);
});

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

const addRow = () => {
    form.items.push({
        field_list_id: '',
        quantity: 0,
    });
};

const removeRow = (index) => {
    form.items.splice(index, 1);
};

const openEdit = (production) => {
    showModal.value = true;
    editProduction.value = true;

    form.reset();
    form.items = [];

    form.product_date = production.product_date
        ? production.product_date.substring(0, 10)
        : null;

    form.note = production.note ?? '';
    form.quantity = production.quantity ?? '';
    form.id = production.id;

    if (production.field) {
        form.items.push({
            field_list_id: production.field.id,
            quantity: production.quantity,
        });
    } else {
        toast.error('No field data found inside production');
    }
};

// Form submit
const submit = () => {
    spinBtn.value = true;
    form.post(route('row-productions.store'), {
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

watch([search, perPage,start_date,end_date], () => {
    router.get(route('row-productions.all'), {
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
            form.delete(`/row-productions/${id}`, {
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

// Simulate loading
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 500);
});
</script>

<template>
    <Head title="সব প্রোডাকশন" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> সব প্রোডাকশন
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
                                            <th width="5%">#</th>
                                            <th>তারিখ</th>
                                            <th>মাঠ</th>
                                            <th>পরিমাণ</th>
                                            <th width="30%">নোট</th>
                                            <th width="10%">বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(production, index) in productions.data" :key="production.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ formatDate(production.product_date) }}</td>
                                            <td>{{ production.field.name }}</td>
                                            <td>{{ production.quantity }}</td>
                                            <td>{{ production.note }}</td>
                                            <td>
                                                <!-- Edit -->
                                                <a
                                                    @click="!production.is_locked && openEdit(production)"
                                                    :class="production.is_locked ? 'text-gray-300 cursor-not-allowed' : 'text-primary cursor-pointer'"
                                                >
                                                    <i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i>
                                                </a>

                                                <!-- Delete -->
                                                <a
                                                    @click.prevent="!production.is_locked && confirmDelete(production.id)"
                                                    :class="production.is_locked ? 'text-gray-300 cursor-not-allowed' : 'text-danger cursor-pointer'"
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
                                            Showing {{ productions.from }} to {{ productions.to }} of {{ productions.total }} entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="col-sm-12 col-md-7">
                                        <nav class="dataTables_paginate paging_simple_numbers" aria-label="Pagination">
                                            <ul class="pagination justify-content-md-end flex-wrap" style="gap: 4px;">
                                                <li v-for="link in productions.links"
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
                                    {{ editProduction ? 'প্রোডাকশন আপডেট  করেন' : 'নতুন প্রোডাকশন করেন' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-md-4 mb-3 pe-0">
                                        <label class="form-label">তারিখ</label>
                                        <Input type="date" v-model="form.product_date" class="form-control form-control-sm" :class="[form.errors.product_date ? 'border-danger mb-1' : '']" placeholder="ফোন নম্বর" readonly/>
                                        <InputError :message="form.errors.product_date" />
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="form.note" class="form-control form-control-sm" placeholder="নোট" :class="[form.errors.note ? 'border-danger mb-1' : '']" />
                                        <InputError :message="form.errors.note" />
                                    </div>

                                    <!-- Items Table -->
                                    <div class="col-12 mb-2">
                                        <div v-if="form.errors.items" class="text-danger mb-2" style="font-size: 13px;">
                                            {{ form.errors.items }}
                                        </div>
                                        <table class="table table-sm table-bordered align-middle" style="font-size: 12px; width: 100%;">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th style="width: 40%;">শ্রেণি</th>
                                                    <th style="width: 30%;">পরিমাণ</th>
                                                    <th style="width: 8%; text-align: center;">
                                                        <a href="#" @click.prevent="!editProduction && addRow()" :class="editProduction ? 'text-muted' : 'text-white'" :style="{ pointerEvents: editProduction ? 'none' : 'auto' }">
                                                            <i class="bx bx-plus" style="font-size: 18px;"></i>
                                                        </a>
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr v-for="(item, index) in form.items" :key="index" style="vertical-align: middle;">
                                                    <!-- শ্রেণি -->
                                                    <td>
                                                        <select
                                                            v-model="item.field_list_id"
                                                            class="form-control form-control-sm"
                                                            :class="form.errors[`items.${index}.field_list_id`] ? 'border-danger mb-1' : ''"
                                                            style="height: 32px; font-size: 12px;" required :disabled="editProduction"
                                                        >
                                                            <option value="">সিলেক্ট মাঠ</option>
                                                            <option v-for="it in fieldsList" :key="it.id" :value="it.id" :disabled="alreadySelectedIds.includes(it.id) && item.field_list_id !== it.id">
                                                                {{ it.name }}
                                                            </option>
                                                        </select>
                                                        <InputError :message="form.errors[`items.${index}.field_list_id`]" />
                                                    </td>

                                                    <!-- পরিমাণ -->
                                                    <td>
                                                        <input
                                                            type="number"
                                                            v-model="item.quantity"
                                                            class="form-control form-control-sm"
                                                            placeholder="পরিমাণ"
                                                            min="0"
                                                            :class="form.errors[`items.${index}.quantity`] ? 'border-danger' : ''"
                                                            style="height: 32px; font-size: 12px;"
                                                        />
                                                        <InputError :message="form.errors[`items.${index}.quantity`]" />
                                                    </td>

                                                    <!-- Remove Row -->
                                                    <td style="text-align: center;">
                                                        <a href="#" @click.prevent="removeRow(index)" class="text-danger">
                                                            <i class="bx bx-trash" style="font-size: 18px;"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                        {{ editProduction ? 'Update' : 'Save' }}
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
