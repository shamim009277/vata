<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

const showModal = ref(false);
const editingCustomer = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    contact_person: '',
    address: '',
    is_active: 1
});

const createCustomer = () => {
    showModal.value = true;
    editingCustomer.value = false;
    form.reset();
}

const openEdit = (customer) => {
    showModal.value = true;
    editingCustomer.value = customer;

    form.name = customer.name;
    form.email = customer.email;
    form.phone = customer.phone;
    form.contact_person = customer.contact_person;
    form.address = customer.address;
    form.is_active = customer.is_active;
}

const updateStatus = (customer) => {
    router.put(`/customers/${customer.id}/status`, {
        is_active: customer.is_active ? 1 : 0
    }, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Status updated successfully')
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
            form.delete(`/customers/${id}`, {
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
    if (editingCustomer.value) {
        form.put(`/customers/${editingCustomer.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    } else {
        form.post('/customers', {
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
    router.get(route('customers.index'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>
<template>

    <Head title="Customer" />
    <AppLayout1>
        <div class="row">
            <div class="col-lg-12">
                <h4 class="mb-3 text-primary text-center font-bold">{{ $t('messages.customer.customer') }}</h4>
            </div>
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i>{{ $t('messages.customer.customer_list') }}</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createCustomer"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>{{ $t('messages.customer.add_customer') }}</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="example_wrapper" class="dataTables_wrapper dt-bootstrap5">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="dataTables_length" id="example_length">
                                            <label>Show
                                                <select v-model="perPage" name="example_length" aria-controls="example" class="form-select form-select-sm">
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select> entries</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div id="example_filter" class="dataTables_filter">
                                            <label>{{ $t('messages.customer.search') }}:<input v-model="search" type="search" class="form-control form-control-sm" placeholder="{{ $t('messages.customer.search') }}" aria-controls="example"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="example" class="table table-striped table-bordered dataTable"
                                            style="width: 100%;" role="grid" aria-describedby="example_info">
                                            <thead>
                                                <tr role="row">
                                                    <th>{{ $t('messages.customer.sl') }}</th>
                                                    <th>{{ $t('messages.customer.name') }}</th>
                                                    <th>{{ $t('messages.customer.email') }}</th>
                                                    <th>{{ $t('messages.customer.phone') }}</th>
                                                    <th>{{ $t('messages.customer.contact_person') }}</th>
                                                    <th>{{ $t('messages.customer.address') }}</th>
                                                    <th>{{ $t('messages.customer.status') }}</th>
                                                    <th>{{ $t('messages.customer.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(customer, index) in customers.data" :key="customer.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ customer.name }}</td>
                                                    <td>{{ customer.email }}</td>
                                                    <td>{{ customer.phone }}</td>
                                                    <td>{{ customer.contact_person }}</td>
                                                    <td>{{ customer.address }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="customer.is_active"
                                                                @change="updateStatus(customer)" :checked="customer.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(customer)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(customer.id)" class="text-danger" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ customers.from }} to {{ customers.to }} of {{ customers.total }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination" style="display: flex; gap: 4px;">
                                                <li v-for="link in customers.links" :key="link.label" class="paginate_button page-item" :class="{ active: link.active, disabled: !link.url }">
                                                    <Link :href="link.url || '#'" v-html="link.label" class="page-link" :class="{ 'bg-primary text-white': link.active, 'text-muted': !link.url }" />
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
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingCustomer ?$t('messages.customer.update_customer') : $t('messages.customer.add_customer') }}</h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>
                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-12 mb-2">
                                        <label for="name" class="form-label">{{ $t('messages.customer.name') }} <span class="text-danger">*</span></label>
                                        <Input id="name" type="text" v-model="form.name" :class="[form.errors.name ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.customer.name') }}" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="email" class="form-label">{{ $t('messages.customer.email') }} <span class="text-danger">*</span></label>
                                        <Input id="email" type="email" v-model="form.email" :class="[form.errors.email ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.customer.email') }}" />
                                        <InputError :message="form.errors.email" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="phone" class="form-label">{{ $t('messages.customer.phone') }} <span class="text-danger">*</span></label>
                                        <Input id="phone" type="number" step="any" v-model="form.phone" :class="[form.errors.phone ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.customer.phone') }}" />
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="contact_person" class="form-label">{{ $t('messages.customer.contact_person') }} <span class="text-danger">*</span></label>
                                        <Input id="contact_person" type="text" v-model="form.contact_person" :class="[form.errors.contact_person ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.customer.contact_person') }}" />
                                        <InputError :message="form.errors.contact_person" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="address" class="form-label">{{ $t('messages.customer.address') }} <span class="text-danger">*</span></label>
                                        <Input id="address" type="text" v-model="form.address" :class="[form.errors.address ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.customer.address') }}" />
                                        <InputError :message="form.errors.address" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">{{ $t('messages.customer.status') }}</label>
                                        <select class="single-select form-control" :class="[form.errors.is_active ? 'border-danger mb-1' : '']" v-model="form.is_active">
                                            <option value="">{{ $t('messages.customer.select_status') }}</option>
                                            <option :value="1">{{ $t('messages.customer.active') }}</option>
                                            <option :value="0">{{ $t('messages.customer.inactive') }}</option>
                                        </select>
                                        <InputError :message="form.errors.is_active" />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">{{ $t('messages.button.close') }}</button>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> {{ editingCustomer ? $t('messages.button.update') : $t('messages.button.save') }}</button>
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
