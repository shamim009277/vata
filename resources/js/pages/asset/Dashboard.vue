<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'

defineProps({
    transactions: Object,
})

const showModal = ref(false)
const showIssueModal = ref(false)
const editStock = ref(false)
const preview = ref(null)
const spinBtn = ref(false)
const issueModal = ref(false)
const issuePreview = ref(null)
const issueSpin = ref(false)

const form = useForm({
    product_name: '',
    product_category: '',
    vendor: '',
    quantity: '',
    unit_price: '',
    total_price: '',
    location: '',
    photo: null,
    has_warranty: false,
    warranty_expiry_date: '',
    form: 1
})

const issueForm = useForm({
    product_name: '',
    location: '',
    name: '',
    quantity: '',
    image: null,
    issue_date: null,
    remarks: ''
})

const productSuggestions = ref([])
const suggestOpen = ref(false)
const suggestLoading = ref(false)
const searchTimer = ref(null)

const fetchProductSuggestions = async (query) => {
    try {
        suggestLoading.value = true
        const url = `${route('assets.products.search')}?q=${encodeURIComponent(query)}`
        const res = await fetch(url, {
            headers: { Accept: 'application/json' },
        })
        if (!res.ok) throw new Error('Network error')

        const data = await res.json()
        productSuggestions.value = Array.isArray(data)
            ? data
            : data?.data ?? []

        suggestOpen.value = true
    } catch (e) {
        productSuggestions.value = []
        suggestOpen.value = false
    } finally {
        suggestLoading.value = false
    }
}

const isSelection = ref(false)

const selectSuggestion = (p) => {
    isSelection.value = true
    issueForm.product_name = p?.product_name || ''
    suggestOpen.value = false
    productSuggestions.value = []
}

watch(
    () => issueForm.product_name,
    (val) => {
        if (isSelection.value) {
            isSelection.value = false
            return
        }

        if (searchTimer.value) clearTimeout(searchTimer.value)

        if (!val || val.length < 2) {
            productSuggestions.value = []
            suggestOpen.value = false
            return
        }

        searchTimer.value = setTimeout(() => {
            fetchProductSuggestions(val)
        }, 250)
    }
)

const createNewStock = () => {
    showModal.value = true
}

const openIssueModal = () => {
    issueModal.value = true
}

const calculateTotal = computed(() => {
    return Number(form.quantity || 0) * Number(form.unit_price || 0)
})

const handleFile = (e) => {
    form.photo = e.target.files[0]

    if (form.photo) {
        preview.value = URL.createObjectURL(form.photo)
    }
}

const handleIssueFile = (e) => {
    issueForm.image = e.target.files[0]

    if (issueForm.image) {
        issuePreview.value = URL.createObjectURL(issueForm.image)
    }
}

// Stock submit
const submit = () => {
    spinBtn.value = true

    form.post(route('assets.store'), {
        forceFormData: true,
        onSuccess: () => {
            spinBtn.value = false
            showModal.value = false
            form.reset()
            preview.value = null
        },
        onError: () => {
            spinBtn.value = false
        },
    })
}

// Issue submit
const submitIssue = () => {
    issueSpin.value = true

    issueForm.post(route('assets.issue.store'), {
        forceFormData: true,
        onSuccess: () => {
            issueSpin.value = false
            issueModal.value = false
            issueForm.reset()
            issuePreview.value = null
        },
        onError: () => {
            issueSpin.value = false
        },
    })
}
</script>



<template>
    <Head title="মালামাল" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <!-- Title -->
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-list-ul"></i> মালামাল
                                </a>
                            </h6>
                            <!-- Right-side controls -->
                            <div class="d-flex align-items-center gap-2">
                                <!-- Create Button -->
                                <button class="btn btn-primary btn-sm" @click="createNewStock">
                                    <i class="fadeIn animated bx bx-plus-medical me-1" style="font-size: small;"></i>
                                    নিউ স্টক
                                </button>

                                <!-- Edit Button -->
                                <button class="btn btn-primary btn-sm" @click="openIssueModal">
                                    <i class="fadeIn animated bx bx-lock me-1" style="font-size: small;"></i>
                                    ইসু করুন
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <ul class="nav nav-tabs nav-success" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" role="tab" aria-selected="true">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-home font-18 me-1'></i></div>
                                        <div class="tab-title">ড্যাশবোর্ড</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#stocklist" role="tab" aria-selected="false">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-grid font-18 me-1'></i></div>
                                        <div class="tab-title">স্টক তালিকা</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#issuelist" role="tab" aria-selected="false">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-microphone font-18 me-1'></i></div>
                                        <div class="tab-title">ইস্যু লিস্ট</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#issuelist" role="tab" aria-selected="false">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-microphone font-18 me-1'></i></div>
                                        <div class="tab-title">নষ্ট আইটেম</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#issuelist" role="tab" aria-selected="false">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-microphone font-18 me-1'></i></div>
                                        <div class="tab-title">হারানো আইটেম</div>
                                    </div>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-info">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট অ্যাসেট</p>
                                                        <h4 class="my-1 text-info">4805</h4>
                                                        <p class="mb-0 font-13">+2.5% from last week</p>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bxs-home'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-danger">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">বর্তমান স্টক</p>
                                                        <h4 class="my-1 text-danger">$84,245</h4>
                                                        <p class="mb-0 font-13">+5.4% from last week</p>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bx-check'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-success">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">নষ্ট আইটেম</p>
                                                        <h4 class="my-1 text-success">34.6%</h4>
                                                        <p class="mb-0 font-13">-4.5% from last week</p>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-alert-triangle' ></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-warning">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">হারানো আইটেম</p>
                                                        <h4 class="my-1 text-warning">8.4K</h4>
                                                        <p class="mb-0 font-13">+8.4% from last week</p>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bx-minus'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                </div>

                                <div class="row">
                                   <div class="col-md-12">
                                    <h6>স্টক ইন-আউট ইতিহাস</h6>
                                       <table class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="15%">টাইপ</th>
                                                    <th width="15%">তারিখ</th>
                                                    <th width="15%">প্রোডাক্ট</th>
                                                    <th width="15%">পরিমাণ</th>
                                                    <th width="15%">স্টেটাস</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(transaction,index) in transactions.data" :key="index">
                                                    <td>{{ index+1 }}</td>
                                                    <td>{{ transaction.product_category }}</td>
                                                    <td>{{ transaction.date }}</td>
                                                    <td>{{ transaction.product_name }}</td>
                                                    <td>{{ transaction.quantity }}</td>
                                                    <td>{{ transaction.status }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                   </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="stocklist" role="tabpanel">
                                <div class="row">
                                   <div class="col-md-12">
                                       <table class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>#</th>
                                                    <th>ছবি</th>
                                                    <th>প্রোডাক্ট</th>
                                                    <th>ক্যাটাগরি</th>
                                                    <th>মোট</th>
                                                    <th>বর্তমান</th>
                                                    <th>ইস্যু</th>
                                                    <th>নষ্ট</th>
                                                    <th>হারা</th>
                                                    <th>একক মূল্য</th>
                                                    <th>মোট মূল্য</th>
                                                    <th>অ্যাকশন</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                   </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="issuelist" role="tabpanel">
                                <div class="row">
                                   <div class="col-md-12">
                                       <table class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">
                                            <thead class="bg-primary text-white">
                                                <tr>
                                                    <th>#</th>
                                                    <th>ছবি</th>
                                                    <th>প্রোডাক্ট</th>
                                                    <th>ক্যাটাগরি</th>
                                                    <th>কার কাছে</th>
                                                    <th>লোকেশন</th>
                                                    <th>পরিমাণ</th>
                                                    <th>ইস্যু তারিখ</th>
                                                    <th>অ্যাকশন</th>
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
                                    {{ editStock ? 'মালামাল আপডেট  করেন' : 'নতুন মালামাল এন্ট্রি' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="modal-body row">

                                    <div class="col-md-6 mb-3 pe-0">
                                        <label class="form-label">প্রোডাক্টের নাম</label>
                                        <Input type="text"
                                            v-model="form.product_name"
                                            class="form-control form-control-sm"
                                            :class="form.errors.product_name ? 'border-danger mb-1' : ''"
                                            placeholder="প্রোডাক্টের নাম"
                                        />
                                        <InputError :message="form.errors.product_name" /><br>

                                        <label class="form-label">ক্যাটাগরি</label>
                                        <select
                                            v-model="form.product_category"
                                            class="form-control form-control-sm"
                                            :class="form.errors.product_category ? 'border-danger mb-1' : ''"
                                        >
                                            <option value="">লোড ক্যাটাগরি</option>
                                            <option value="Tools">Tools</option>
                                            <option value="Machines">Machines</option>
                                            <option value="Electronics">Electronics</option>
                                            <option value="Furniture">Furniture</option>
                                            <option value="Others">Others</option>
                                        </select>
                                        <InputError :message="form.errors.product_category" /><br>

                                        <label class="form-label">ভেন্ডর/দোকান</label>
                                        <Input
                                            type="text"
                                            v-model="form.vendor"
                                            class="form-control form-control-sm"
                                            :class="form.errors.vendor ? 'border-danger mb-1' : ''"
                                            placeholder="ভেন্ডর/দোকান"
                                        />
                                        <InputError :message="form.errors.vendor" /><br>

                                        <label class="form-label">পরিমাণ</label>
                                        <Input
                                            type="number"
                                            min="0"
                                            v-model="form.quantity"
                                            class="form-control form-control-sm"
                                            :class="form.errors.quantity ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="form.errors.quantity" /><br>

                                        <label class="form-label">একক মূল্য</label>
                                        <Input
                                            type="number"
                                            min="0"
                                            v-model="form.unit_price"
                                            class="form-control form-control-sm"
                                            :class="form.errors.unit_price ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="form.errors.unit_price" /><br>

                                        <label class="form-label">মোট মূল্য</label>
                                        <Input
                                            type="number"
                                            v-model="form.total_price"
                                            :value="calculateTotal"
                                            class="form-control form-control-sm"
                                            readonly
                                        />
                                    </div>

                                    <!-- RIGHT SIDE -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ফটো</label>
                                        <input
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            class="form-control form-control-sm"
                                            @change="handleFile"
                                        />
                                        <InputError :message="form.errors.photo" />

                                        <!-- Image Preview -->
                                        <div v-if="preview" class="mt-2">
                                            <img :src="preview" class="img-thumbnail" width="150">
                                        </div>

                                        <div class="mb-4 mt-5">
                                            <label class="form-check-label mt-2">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input me-1"
                                                    v-model="form.has_warranty"
                                                >
                                                ওয়ারেন্টি আছে ?
                                            </label>
                                        </div>

                                        <div class="mb-3" v-if="form.has_warranty">
                                            <label class="form-label">মেয়াদ শেষ তারিখ</label>
                                            <input
                                                type="date"
                                                class="form-control form-control-sm"
                                                v-model="form.warranty_expiry_date"
                                                :class="form.errors.warranty_expiry_date ? 'border-danger' : ''"
                                            >
                                            <InputError :message="form.errors.warranty_expiry_date" />
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">
                                        Close
                                    </button>

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="bx bx-plus-medical me-1"></i>
                                        {{ editStock ? 'Update' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>

            <transition name="modal">
                <div class="modal-backdrop" v-if="issueModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="issueModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h6 class="modal-title">
                                    <i class="bx bx-message-square-edit me-2"></i>
                                    ইস্যু এন্ট্রি
                                </h6>
                                <button type="button" class="btn-close" @click="issueModal = false"></button>
                            </div>

                            <form @submit.prevent="submitIssue">
                                <div class="modal-body row">

                                    <div class="col-md-6 mb-3 pe-0">
                                        <label class="form-label">প্রোডাক্টের নাম</label>
                                        <div class="position-relative">
                                            <Input type="text"
                                                v-model="issueForm.product_name"
                                                class="form-control form-control-sm"
                                                :class="issueForm.errors.product_name ? 'border-danger mb-1' : ''"
                                                placeholder="প্রোডাক্টের নাম"
                                                @focus="suggestOpen = true"
                                                @blur="setTimeout(()=>{suggestOpen=false},200)"
                                            />
                                            <div class="suggest-box" v-if="suggestOpen && productSuggestions.length">
                                                <ul class="list-unstyled mb-0">
                                                    <li v-for="p in productSuggestions" :key="p.id" class="suggest-item" @mousedown.prevent="selectSuggestion(p)">{{ p.product_name }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <InputError :message="issueForm.errors.product_name" /><br>

                                        <label class="form-label">ইসু তারিখ</label>
                                        <Input type="date"
                                            v-model="issueForm.issue_date"
                                            class="form-control form-control-sm"
                                            :class="issueForm.errors.issue_date ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="issueForm.errors.issue_date" /><br>

                                        <label class="form-label">নাম</label>
                                        <Input type="text"
                                            v-model="issueForm.name"
                                            class="form-control form-control-sm"
                                            :class="issueForm.errors.name ? 'border-danger mb-1' : ''"
                                            placeholder="কার কাছে"
                                        />
                                        <InputError :message="issueForm.errors.name" /><br>

                                        <label class="form-label">লোকেশন</label>
                                        <Input type="text"
                                            v-model="issueForm.location"
                                            class="form-control form-control-sm"
                                            :class="issueForm.errors.location ? 'border-danger mb-1' : ''"
                                            placeholder="লোকেশন"
                                        />
                                        <InputError :message="issueForm.errors.location" /><br>

                                        

                                        <label class="form-label">পরিমাণ</label>
                                        <Input type="number"
                                            min="0"
                                            v-model="issueForm.quantity"
                                            class="form-control form-control-sm"
                                            :class="issueForm.errors.quantity ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="issueForm.errors.quantity" /><br>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ইমেজ</label>
                                        <input
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            class="form-control form-control-sm"
                                            @change="handleIssueFile"
                                        />
                                        <InputError :message="issueForm.errors.image" />

                                        <div v-if="issuePreview" class="mt-2">
                                            <img :src="issuePreview" class="img-thumbnail" width="150">
                                        </div>

                                        <label class="form-label mt-3">মন্তব্য</label>
                                        <textarea
                                            v-model="issueForm.remarks"
                                            class="form-control form-control-sm"
                                            :class="issueForm.errors.remarks ? 'border-danger mb-1' : ''"
                                            rows="3"
                                            placeholder="Remarks"
                                        ></textarea>
                                        <InputError :message="issueForm.errors.remarks" />
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="issueModal = false">
                                        Close
                                    </button>

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="issueSpin" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="bx bx-send me-1"></i>
                                        Save Issue
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </transition>


            <!-- Issue Modal -->
            <transition name="modal">
                <div class="modal-backdrop" v-if="showIssueModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showIssueModal">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h6 class="modal-title">
                                    <i class="bx bx-message-alt-add me-2"></i>
                                    {{ editStock ? 'মালামাল আপডেট  করেন' : 'নতুন মালামাল এন্ট্রি' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showIssueModal = false"></button>
                            </div>

                            <form @submit.prevent="submit">
                                <div class="modal-body row">

                                    <div class="col-md-6 mb-3 pe-0">
                                        <label class="form-label">প্রোডাক্টের নাম</label>
                                        <Input type="text"
                                            v-model="form.product_name"
                                            class="form-control form-control-sm"
                                            :class="form.errors.product_name ? 'border-danger mb-1' : ''"
                                            placeholder="প্রোডাক্টের নাম"
                                        />
                                        <InputError :message="form.errors.product_name" /><br>

                                        <label class="form-label">ক্যাটাগরি</label>
                                        <select
                                            v-model="form.product_category"
                                            class="form-control form-control-sm"
                                            :class="form.errors.product_category ? 'border-danger mb-1' : ''"
                                        >
                                            <option value="">লোড ক্যাটাগরি</option>
                                            <option value="Tools">Tools</option>
                                            <option value="Machines">Machines</option>
                                            <option value="Electronics">Electronics</option>
                                            <option value="Furniture">Furniture</option>
                                            <option value="Others">Others</option>
                                        </select>
                                        <InputError :message="form.errors.product_category" /><br>

                                        <label class="form-label">ভেন্ডর/দোকান</label>
                                        <Input
                                            type="text"
                                            v-model="form.vendor"
                                            class="form-control form-control-sm"
                                            :class="form.errors.vendor ? 'border-danger mb-1' : ''"
                                            placeholder="ভেন্ডর/দোকান"
                                        />
                                        <InputError :message="form.errors.vendor" /><br>

                                        <label class="form-label">পরিমাণ</label>
                                        <Input
                                            type="number"
                                            min="0"
                                            v-model="form.quantity"
                                            class="form-control form-control-sm"
                                            :class="form.errors.quantity ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="form.errors.quantity" /><br>

                                        <label class="form-label">একক মূল্য</label>
                                        <Input
                                            type="number"
                                            min="0"
                                            v-model="form.unit_price"
                                            class="form-control form-control-sm"
                                            :class="form.errors.unit_price ? 'border-danger mb-1' : ''"
                                        />
                                        <InputError :message="form.errors.unit_price" /><br>

                                        <label class="form-label">মোট মূল্য</label>
                                        <Input
                                            type="number"
                                            v-model="form.total_price"
                                            :value="calculateTotal"
                                            class="form-control form-control-sm"
                                            readonly
                                        />
                                    </div>

                                    <!-- RIGHT SIDE -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ফটো</label>
                                        <input
                                            type="file"
                                            accept=".jpg,.jpeg,.png"
                                            class="form-control form-control-sm"
                                            @change="handleFile"
                                        />
                                        <InputError :message="form.errors.photo" />

                                        <!-- Image Preview -->
                                        <div v-if="preview" class="mt-2">
                                            <img :src="preview" class="img-thumbnail" width="150">
                                        </div>

                                        <div class="mb-4 mt-5">
                                            <label class="form-check-label mt-2">
                                                <input
                                                    type="checkbox"
                                                    class="form-check-input me-1"
                                                    v-model="form.has_warranty"
                                                >
                                                ওয়ারেন্টি আছে ?
                                            </label>
                                        </div>

                                        <div class="mb-3" v-if="form.has_warranty">
                                            <label class="form-label">মেয়াদ শেষ তারিখ</label>
                                            <input
                                                type="date"
                                                class="form-control form-control-sm"
                                                v-model="form.warranty_expiry_date"
                                                :class="form.errors.warranty_expiry_date ? 'border-danger' : ''"
                                            >
                                            <InputError :message="form.errors.warranty_expiry_date" />
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">
                                        Close
                                    </button>

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="bx bx-plus-medical me-1"></i>
                                        {{ editStock ? 'Update' : 'Save' }}
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
.nav-tabs {
    border-bottom: none;
}
.nav-tabs .nav-link {
    border: none !important;
    background: transparent;
    color: #6c757d;
}
.nav-tabs .nav-link:hover {
    border: none;
    color: #198754; /* success color */
}

.nav-tabs .nav-link.active {
    border: none !important;
    border-bottom: 3px solid #198754 !important; /* success green */
    color: #198754;
    background: transparent;
    font-weight: 600;
}

.suggest-box {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    z-index: 2000;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    max-height: 240px;
    overflow: auto;
}

.suggest-item {
    padding: 8px 12px;
    cursor: pointer;
}

.suggest-item:hover {
    background: #f3f4f6;
}
</style>
