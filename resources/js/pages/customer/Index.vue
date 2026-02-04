<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

watch(search, debounce((value) => {
    router.get(route('customers.index'), { search: value, perPage: perPage.value }, { preserveState: true, replace: true });
}, 300));

watch(perPage, (value) => {
    router.get(route('customers.index'), { search: search.value, perPage: value }, { preserveState: true, replace: true });
});

</script>

<template>
    <Head title="Customer List" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-lg-3 col-xl-2">
                                <div class="btn btn-outline-success w-100" style="cursor: default;">
                                    কাস্টমার: {{ customers.total }} জন
                                </div>
                            </div>
                            <div class="col-lg-9 col-xl-10">
                                <form class="float-lg-end">
                                    <div class="row row-cols-lg-auto g-2">
                                        <div class="col-12">
                                            <div class="position-relative">
                                                <input v-model="search" type="text" class="form-control ps-5" placeholder="সার্চ করুন...">
                                                <span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" style="width:100%">
                                <thead class="bg-success text-white">
                                    <tr>
                                        <th>আইডি</th>
                                        <th>নাম, ঠিকানা, ফোন নম্বর</th>
                                        <th>ডেলিভারি</th>
                                        <th>টাকা</th>
                                        <th>বাকি পরিশোধের তারিখ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="customer in customers.data" :key="customer.id" @click="router.visit(route('customers.show', customer.id))" style="cursor: pointer;">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">#{{ customer.id }}</span>
                                                <small class="text-muted">Season: {{ customer.season }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ customer.name }}</span>
                                                <small class="text-muted">{{ customer.address }}</small>
                                                <small class="text-primary">{{ customer.phone }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary mb-1">অর্ডার: {{ customer.total_item }}</span>
                                                <span class="badge bg-info mb-1 text-dark">ডেলিভারি: {{ customer.total_delivered || 0 }}</span>
                                                <span class="badge bg-warning text-dark">বাকি: {{ Number(customer.total_item) - Number(customer.total_delivered || 0) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-primary fw-bold">মোট: {{ customer.total_amount }}</span>
                                                <span class="text-success">জমা: {{ customer.paid_amount }}</span>
                                                <span class="text-danger">বাকি: {{ customer.due_amount }}</span>
                                            </div>
                                        </td>
                                        <td>{{ customer.next_payment_date }}</td>
                                    </tr>
                                    <tr v-if="customers.data.length === 0">
                                        <td colspan="5" class="text-center">কোনো কাস্টমার পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info">
                                    Showing {{ customers.from }} to {{ customers.to }} of {{ customers.total }} entries
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <nav aria-label="Pagination">
                                    <ul class="pagination justify-content-end">
                                        <li v-for="(link, k) in customers.links" :key="k" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                                            <Link class="page-link" :href="link.url || '#'" v-html="link.label" />
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
