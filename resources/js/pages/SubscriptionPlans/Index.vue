<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';


const props = defineProps({
    plans: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

const showModal = ref(false);
const editingPlan = ref(false);

const form = useForm({
    name: '',
    price: '',
    duration: '',
    description: '',
    interval: '',
    is_active: true
});

const createSubscription = () => {
    showModal.value = true;
    editingPlan.value = false;
    form.reset();
}

const openEdit = (plan) => {
    showModal.value = true;
    editingPlan.value = plan;

    form.name = plan.name;
    form.price = plan.price;
    form.duration = plan.duration;
    form.description = plan.description;
    form.interval = plan.interval;
    form.is_active = plan.is_active;

}

const updateStatus = (plan) => {
    router.put(`/plans/${plan.id}/status`, {
        is_active: plan.is_active
    }, {
        preserveScroll: true,
        onSuccess: () => {

        },
    });
}

const confirmDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/subscription-plans/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Plan deleted successfully.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    })
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong.',
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 1500
                    })
                }
            })
        }
    })
}

const submit = () => {
    if (editingPlan.value) {
        form.put(`/subscription-plans/${editingPlan.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    } else {
        form.post('/subscription-plans', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    }
};

// Watchers
watch([search, perPage], () => {
    router.get(route('subscription-plans.index'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>
<template>

    <Head title="সাবস্ক্রিপশন প্ল্যান" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i>Plan List</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createSubscription"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>Add Plan</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="example_wrapper" class="dataTables_wrapper dt-bootstrap5">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="dataTables_length" id="example_length">
                                            <label>Show
                                                <select v-model="perPage" name="example_length" aria-controls="example"
                                                    class="form-select form-select-sm">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select> entries</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div id="example_filter" class="dataTables_filter">
                                            <label>Search:<input v-model="search" type="search" class="form-control form-control-sm" placeholder="Search ..." aria-controls="example"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="example" class="table table-striped table-bordered dataTable" style="width: 100%; font-size: small;">
                                            <thead class="bg-primary text-white">
                                                <tr role="row">
                                                    <th>Sl</th>
                                                    <th>নাম</th>
                                                    <th>মূল্য</th>
                                                    <th>সময়</th>
                                                    <th>Interval</th>
                                                    <th>স্টাটাস</th>
                                                    <th>অ্যাকশন</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(plan, index) in plans.data" :key="plan.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ plan.name }}</td>
                                                    <td>{{ plan.price }}</td>
                                                    <td>{{ plan.duration }}</td>
                                                    <td>{{ plan.interval }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="plan.is_active"
                                                                @change="updateStatus(plan)" :checked="plan.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(plan)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(plan.id)" class="text-danger" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ plans.from }} to {{ plans.to }} of {{ plans.total }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination" style="display: flex; gap: 4px;">
                                                <li v-for="link in plans.links" :key="link.label"
                                                    class="paginate_button page-item"
                                                    :class="{ active: link.active, disabled: !link.url }">
                                                    <Link :href="link.url || '#'" v-html="link.label" class="page-link"
                                                        :class="{ 'bg-primary text-white': link.active, 'text-muted': !link.url }" />
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <transition name="modal">
                <div class="modal-backdrop" v-if="showModal"></div>
            </transition>

            <transition name="slide-fade">
                <div class="modal d-block" v-if="showModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header" style="border-top: 2px solid #004882;">
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingPlan ?
                                    'Update Subscription Plan' : 'Add Subscription Plan' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>
                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-12 mb-2">
                                        <label for="name" class="form-label">Name</label>
                                        <Input id="name" type="text" v-model="form.name"
                                            :class="[form.errors.name ? 'border-danger mb-1' : '']" class="form-control"
                                            placeholder="Premium" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="price" class="form-label">Price</label>
                                        <Input id="price" type="number" v-model="form.price"
                                            :class="[form.errors.price ? 'border-danger mb-1' : '']" class="form-control"
                                            placeholder="500" />
                                        <InputError :message="form.errors.price" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="duration" class="form-label">Duration</label>
                                        <Input id="duration" type="number" v-model="form.duration"
                                            :class="[form.errors.duration ? 'border-danger mb-1' : '']"
                                            class="form-control" placeholder="90" />
                                        <InputError :message="form.errors.duration" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">Interval</label>
                                        <Input id="interval" type="text" v-model="form.interval"
                                            :class="[form.errors.interval ? 'border-danger mb-1' : '']"
                                            class="form-control" placeholder="Month" />
                                        <InputError :message="form.errors.interval" />
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">Status</label>
                                        <select class="single-select form-control"
                                            :class="[form.errors.is_active ? 'border-danger mb-1' : '']"
                                            v-model="form.is_active">
                                            <option value="">Seelct status</option>
                                            <option :value="true">Active</option>
                                            <option :value="false">Inactive</option>
                                        </select>
                                        <InputError :message="form.errors.is_active" />
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">{{ editingPlan ? 'Update' :
                                        'Save' }}</button>
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

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
</style>
