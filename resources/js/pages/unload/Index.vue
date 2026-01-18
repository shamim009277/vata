<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm,router,Link } from '@inertiajs/vue3';
import { ref, watch, onMounted,computed } from 'vue';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';

const props = defineProps({
    unloads: Object,
    filters: Object,
    loads: Object,
    items: Object,
});

const itemsList = ref(props.items);

const showModal = ref(false);
const showDetailsModal = ref(false);
const selectedUnload = ref(null);
const editUnload = ref(false);
const spinBtn = ref(false);
const loading = ref(true);
const loadQuantity = ref(0) 

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);
const date = ref(props.filters?.date || new Date().toISOString().split('T')[0]);

// Main form
const form = useForm({
    unload_date: new Date().toISOString().split('T')[0],
    note: '',
    load: '',
    quantity: 0,
    items: [],

    id: '',
    loadQuantity:''
});

// Modal and row functions
const createUnload = () => {
    showModal.value = true;
    editUnload.value = false;
    form.reset();
    form.items = [];
};

const alreadySelectedIds = computed(() => {
    return form.items.map(i => i.item_id).filter(id => id);
});

const uniqueLoads = computed(() => {
    const map = new Map()

    props.loads.forEach(load => {
        const key = `${load.loading_date}-${load.round}`

        if (!map.has(key)) {
            map.set(key, {
                loading_date: load.loading_date,
                round: load.round,
                total_quantity: Number(load.quantity)
            })
        } else {
            map.get(key).total_quantity += Number(load.quantity)
        }
    })

    return Array.from(map.values())
})

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
        item_id: '',
        quantity: 0,
    });
};

const removeRow = (index) => {
    form.items.splice(index, 1);
};

const totalItemQuantity = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (Number(item.quantity) || 0)
    }, 0)
})

const checkQuantityLimit = () => {
    if (totalItemQuantity.value > loadQuantity.value *2) {
        toast.error('মোট আইটেমের পরিমাণ লোডের পরিমাণের বেশি হতে পারবে না')
        form.items[form.items.length - 1].quantity = 0
    }
}

const showDetails = (unload) => {
    selectedUnload.value = unload;
    showDetailsModal.value = true;
}

const percentage = (quantity,load_quantity) => {
    return (quantity / load_quantity) * 100;
}


const showUpdate = (unload) => {
    selectedUnload.value = unload;
    showModal.value = true;
    editUnload.value = true;

    form.reset();
    form.items = [];

    form.unload_date = selectedUnload.value.unload_date
        ? selectedUnload.value.unload_date.substring(0, 10)
        : null;

    form.note = selectedUnload.value.note ?? '';
    form.loadQuantity = selectedUnload.value.load_quantity ?? '';
    form.id = selectedUnload.value.id;

    form.load = `${selectedUnload.value.loading_date.substring(0, 10)}-${selectedUnload.value.round}`;

    if (selectedUnload.value.details.length > 0) {
        form.items = selectedUnload.value.details.map(detail => ({
            item_id: detail.item_id,
            quantity: detail.quantity,
        }));
    } else {
        toast.error('No field data found inside production');
    }
};



// Form submit
const submit = () => {
    spinBtn.value = true;
    form.post(route('unloads.store'), {
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

const lockProduction = () => {
    spinBtn.value = true;
    lockForm.post(route('row-productions.lock'), {
        onSuccess: () => {
            lockForm.reset();
            spinBtn.value = false;
        },
        onError: () => {
            spinBtn.value = false;
        },
    });
};

watch(
    () => form.load,
    (loadStr) => {
        if (loadStr) {
            const selected = uniqueLoads.value.find(
                l => `${l.loading_date}-${l.round}` === loadStr
            );
            loadQuantity.value = selected ? selected.total_quantity : 0;
        } else {
            loadQuantity.value = 0;
        }
    }
);


watch([search, perPage,date], () => {
    router.get(route('row-productions.index'), {
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
            form.delete(`/unloads/${id}`, {
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
    <Head title="আনলোড খাতা" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">

                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> আনলোড খাতা
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
                                <button class="btn btn-primary btn-sm" @click="createUnload">
                                    <i class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                    নতুন আনলোড
                                </button>

                                <!-- Edit Button -->
                                <button class="btn btn-primary btn-sm" @click="lockProduction">
                                    <i class="fadeIn animated bx bx-lock me-1" style="font-size: small;"></i>
                                    লক করুন
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
                                            <th width="5%">#</th>
                                            <th width="15%">আনলোড তারিখ</th>
                                            <th width="15%">রেফারেন্স লোড</th>
                                            <th width="15%">লোডের পরিমাণ</th>
                                            <th width="15%">আনলোডের পরিমাণ</th>
                                            <th width="15%">নোট</th>
                                            <th width="10%">বাটন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(unload,index) in unloads.data" :key="unload.id">
                                            <td>{{index + 1}}</td>
                                            <td>{{formatDate(unload.unload_date)}}</td>
                                            <td>{{formatDate(unload.loading_date)}}::{{unload.round}}</td>
                                            <td>{{unload.load_quantity}}</td>
                                            <td>{{ unload.details.reduce((total, item) => total + item.quantity, 0) }}</td>
                                            <td>{{unload.note}}</td>
                                            <td>
                                                <div class="dropdown dropstart">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bx bx-dots-vertical-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showUpdate(unload)">
                                                                <i class="bx bx-box"></i>
                                                                আপডেট করুণ
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click.prevent="confirmDelete(unload.id)">
                                                                <i class="bx bx-trash"></i>
                                                                চালান মুছে ফেলুন
                                                            </a></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" @click="showDetails(unload)"><i class="bx bx-show"></i> 
                                                                আনলোড বিস্তারিত
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
                                            Showing {{ unloads.from }} to {{ unloads.to }} of {{ unloads.total }} entries
                                        </div>
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="col-sm-12 col-md-7">
                                        <nav class="dataTables_paginate paging_simple_numbers" aria-label="Pagination">
                                            <ul class="pagination justify-content-md-end flex-wrap" style="gap: 4px;">
                                                <li v-for="link in unloads.links"
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
                                    {{ editUnload ? 'আনলোড আপডেট  করেন' : 'নতুন আনলোড করেন' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-md-4 mb-3 pe-0">
                                        <label class="form-label">তারিখ</label>
                                        <Input type="date" v-model="form.unload_date" class="form-control form-control-sm" :class="[form.errors.unload_date ? 'border-danger mb-1' : '']" placeholder="ফোন নম্বর" readonly/>
                                        <InputError :message="form.errors.unload_date" />
                                    </div>

                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">তারিখ & রাউন্ড</label>
                                        <select v-model="form.load" class="form-control form-control-sm">
                                            <option value="">লোড সিলেক্ট করুন</option>
                                            <option
                                                v-for="load in uniqueLoads"
                                                :key="load.loading_date + load.round"
                                                :value="`${load.loading_date}-${load.round}`"
                                            >
                                                {{ load.loading_date }} :: {{ load.round }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.load" />
                                    </div>

                                    <div class="col-md-8 mb-3 pe-0">
                                        <label class="form-label">নোট</label>
                                        <Input v-model="form.note" class="form-control form-control-sm" placeholder="নোট" :class="[form.errors.note ? 'border-danger mb-1' : '']" />
                                        <InputError :message="form.errors.note" />
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">লোডের পরিমান</label>
                                        <Input v-model ="form.loadQuantity" :value="loadQuantity" class="form-control form-control-sm" placeholder="লোডের পরিমান" :class="[loadQuantity ? 'border-danger mb-1' : '']" readonly />
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
                                                        <a href="#" @click.prevent="!editUnload && addRow()" :class="editUnload ? 'text-muted' : 'text-white'" :style="{ pointerEvents: editUnload ? 'none' : 'auto' }">
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
                                                            v-model="item.item_id"
                                                            class="form-control form-control-sm"
                                                            :class="form.errors[`items.${index}.item_id`] ? 'border-danger mb-1' : ''"
                                                            style="height: 32px; font-size: 12px;" required :disabled="editUnload"
                                                        >
                                                            <option value="">সিলেক্ট শ্রেণি</option>
                                                            <option v-for="it in itemsList" :key="it.id" :value="it.id" :disabled="alreadySelectedIds.includes(it.id) && item.item_id !== it.id">
                                                                {{ it.name }}
                                                            </option>
                                                        </select>
                                                        <InputError :message="form.errors[`items.${index}.item_id`]" />
                                                    </td>

                                                    <!-- পরিমাণ -->
                                                    <td>
                                                        <input
                                                            type="number"
                                                            v-model="item.quantity"
                                                            class="form-control form-control-sm"
                                                            placeholder="পরিমাণ"
                                                            @input="checkQuantityLimit"
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
                                        {{ editUnload ? 'Update' : 'Save' }}
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
                                    আনলোড এর বিস্তারিত
                                </h6>
                                <button type="button" class="btn-close" @click="showDetailsModal = false"></button>
                            </div>
            
                            <div class="modal-body" v-if="selectedUnload">
                                <div class="row mb-3">
                                    <div class="col-md-6 challan-info">
                                        <p><strong>আনলোড তারিখ: </strong> {{formatDate(selectedUnload.unload_date)}}</p>
                                        <p><strong>রেফারেন্স লোড: </strong> {{ formatDate(selectedUnload.loading_date) }}::{{selectedUnload.round}}</p>
                                        <p><strong>লোডের পরিমাণ: </strong> {{selectedUnload.load_quantity}}</p>
                                    </div>
                                </div>
            
                                <table class="table table-bordered" style="font-size: small;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th v-for="(item,index) in selectedUnload.details" :key="index">{{item.item.name}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>  
                                            <td>পরিমাণ</td>
                                            <td v-for="(item,index) in selectedUnload.details" :key="index">{{item.quantity}}</td>
                                        </tr>
                                        <tr>  
                                            <td>শতকরা পরিমাণ</td>
                                            <td v-for="(item,index) in selectedUnload.details" :key="index">{{percentage(item.quantity,selectedUnload.load_quantity)}}%</td>
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
