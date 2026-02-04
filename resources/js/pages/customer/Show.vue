<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    customer: Object
});

const activeTab = ref('dashboard');
</script>

<template>
    <Head :title="customer.name" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-user-circle me-2"></i> কাস্টমার প্রোফাইল: {{ customer.name }}
                            </h6>
                            <div>
                                <Link :href="route('delivery-challans.create', { customer_id: customer.id })" class="btn btn-sm btn-primary me-2">
                                    <i class="bx bx-plus"></i> ডেলিভারি চালান যুক্ত করুন
                                </Link>
                                <Link :href="route('customers.index')" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back"></i> ফিরে যান
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-success" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: activeTab === 'dashboard' }" @click.prevent="activeTab = 'dashboard'" href="#" role="tab">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-home font-18 me-1'></i></div>
                                        <div class="tab-title">ড্যাশবোর্ড</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: activeTab === 'challan' }" @click.prevent="activeTab = 'challan'" href="#" role="tab">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-file font-18 me-1'></i></div>
                                        <div class="tab-title">চালান</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: activeTab === 'delivery' }" @click.prevent="activeTab = 'delivery'" href="#" role="tab">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-truck font-18 me-1'></i></div>
                                        <div class="tab-title">ডেলিভারি</div>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" :class="{ active: activeTab === 'payment' }" @click.prevent="activeTab = 'payment'" href="#" role="tab">
                                    <div class="d-flex align-items-center">
                                        <div class="tab-icon"><i class='bx bx-money font-18 me-1'></i></div>
                                        <div class="tab-title">পেমেন্ট</div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content py-3">
                            <!-- Dashboard Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': activeTab === 'dashboard' }" id="dashboard" role="tabpanel">
                                <!-- Stats Cards -->
                                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-info">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট আইটেম</p>
                                                        <h4 class="my-1 text-info">{{ customer.total_item }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bx-cart'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-primary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">মোট টাকা</p>
                                                        <h4 class="my-1 text-primary">{{ customer.total_amount }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto"><i class='bx bx-money'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-success">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">জমা</p>
                                                        <h4 class="my-1 text-success">{{ customer.paid_amount }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bx-check-circle'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card radius-10 border-start border-0 border-4 border-danger">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <p class="mb-0 text-secondary">বাকি</p>
                                                        <h4 class="my-1 text-danger">{{ customer.due_amount }}</h4>
                                                    </div>
                                                    <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto"><i class='bx bx-error-circle'></i></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Details -->
                                <div class="card mt-3 shadow-none border">
                                    <div class="card-body">
                                        <h6 class="mb-3 text-primary">কাস্টমার বিস্তারিত</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tbody>
                                                        <tr>
                                                            <th width="150">নাম:</th>
                                                            <td>{{ customer.name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>ফোন:</th>
                                                            <td>{{ customer.phone }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tbody>
                                                        <tr>
                                                            <th width="150">ঠিকানা:</th>
                                                            <td>{{ customer.address }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>সিজন:</th>
                                                            <td>{{ customer.season }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Challan Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': activeTab === 'challan' }" id="challan" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>চালান নং</th>
                                                <th>তারিখ</th>
                                                <th>ধরণ</th>
                                                <th>পরিমাণ</th>
                                                <th>মোট টাকা</th>
                                                <th>জমা</th>
                                                <th>বাকি</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="invoice in customer.invoices" :key="invoice.id">
                                                <td>{{ invoice.invoice_no }}</td>
                                                <td>{{ invoice.invoice_date }}</td>
                                                <td>{{ invoice.invoice_type }}</td>
                                                <td>{{ invoice.quantity }}</td>
                                                <td>{{ invoice.total_amount }}</td>
                                                <td>{{ invoice.paid_amount }}</td>
                                                <td>{{ invoice.due_amount }}</td>
                                            </tr>
                                            <tr v-if="!customer.invoices || customer.invoices.length === 0">
                                                <td colspan="7" class="text-center text-muted">কোনো চালান পাওয়া যায়নি</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Delivery Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': activeTab === 'delivery' }" id="delivery" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>ডেলিভারি নং</th>
                                                <th>তারিখ</th>
                                                <th>আইটেম</th>
                                                <th>পরিমাণ</th>
                                                <th>ট্রাক নং</th>
                                                <th>ড্রাইভার</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="delivery in customer.deliveries" :key="delivery.id">
                                                <td>{{ delivery.delivery_no }}</td>
                                                <td>{{ delivery.delivery_date }}</td>
                                                <td>{{ delivery.item?.name }}</td>
                                                <td>{{ delivery.delivery_qty }}</td>
                                                <td>{{ delivery.truck_number }}</td>
                                                <td>{{ delivery.driver_name }}</td>
                                            </tr>
                                            <tr v-if="!customer.deliveries || customer.deliveries.length === 0">
                                                <td colspan="6" class="text-center text-muted">কোনো ডেলিভারি তথ্য পাওয়া যায়নি</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Payment Tab -->
                            <div class="tab-pane fade" :class="{ 'show active': activeTab === 'payment' }" id="payment" role="tabpanel">
                                <div class="alert alert-info border-0 bg-info alert-dismissible fade show py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="font-35 text-white"><i class='bx bx-info-circle'></i>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0 text-white">পেমেন্ট তথ্য</h6>
                                            <div class="text-white">পেমেন্ট মডিউল শীঘ্রই আসছে...</div>
                                        </div>
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
