<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';

const props = defineProps({
    items: Object,
    filters: Object,
    units: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

const showModal = ref(false);
const editingItem = ref(false);

const form = useForm({
    name: '',
    type: '',
    unit_id: '',
    cost_per_unit: '',
    stock_alert_quantity: '',
    is_active: 1
});

const createItem = () => {
    showModal.value = true;
    editingItem.value = false;
    form.reset();
}

const openEdit = (item) => {
    showModal.value = true;
    editingItem.value = item;

    form.name = item.name;
    form.type = item.type;
    form.unit_id = item.unit_id;
    form.cost_per_unit = item.cost_per_unit;
    form.stock_alert_quantity = item.stock_alert_quantity;
    form.is_active = item.is_active;
}

const updateStatus = (item) => {
    router.put(`/items/${item.id}/status`, {
        is_active: item.is_active ? 1 : 0
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
            form.delete(`/items/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Item deleted successfully.',
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
    if (editingItem.value) {
        form.put(`/items/${editingItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    } else {
        form.post('/items', {
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
    router.get(route('items.index'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>
<template>

    <Head title="Item" />
    <AppLayout1>
        <div class="row">
            <div class="col-lg-12">
                <h4 class="mb-3 text-primary text-center font-bold">{{ $t('messages.item.items') }}</h4>
            </div>
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i> {{ $t('messages.item.item_list') }}</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createItem"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>{{ $t('messages.item.add_item') }}</button>
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
                                            <label>{{ $t('messages.item.search') }}:<input v-model="search" type="search" class="form-control form-control-sm" placeholder="{{ $t('messages.item.search') }} ..." aria-controls="example"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="example" class="table table-striped table-bordered dataTable" style="width: 100%;" role="grid" aria-describedby="example_info">
                                            <thead>
                                                <tr role="row">
                                                    <th>{{ $t('table.sl') }}</th>
                                                    <th>{{ $t('table.name') }}</th>
                                                    <th>{{ $t('table.type') }}</th>
                                                    <th>{{ $t('table.unit') }}</th>
                                                    <th>{{ $t('table.cost_per_unit') }}</th>
                                                    <th>{{ $t('table.stock_alert_quantity') }}</th>
                                                    <th>{{ $t('table.status') }}</th>
                                                    <th>{{ $t('table.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in items.data" :key="item.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td>{{ item.type }}</td>
                                                    <td>{{ item.unit.name }}</td>
                                                    <td>{{ item.cost_per_unit }}</td>
                                                    <td>{{ item.stock_alert_quantity }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="item.is_active"
                                                                @change="updateStatus(item)" :checked="item.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(item)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(item.id)" class="text-danger" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ items.from }} to {{ items.to }} of {{ items.total }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination" style="display: flex; gap: 4px;">
                                                <li v-for="link in items.links" :key="link.label" class="paginate_button page-item" :class="{ active: link.active, disabled: !link.url }">
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
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingItem ? $t('messages.item.update_item') : $t('messages.item.add_item') }}</h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>
                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-12 mb-2">
                                        <label for="name" class="form-label">{{ $t('messages.item.name') }} <span class="text-danger">*</span></label>
                                        <Input id="name" type="text" v-model="form.name" :class="[form.errors.name ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.item.name') }}" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="type" class="form-label">{{ $t('messages.item.type') }} <span class="text-danger">*</span></label>
                                        <select class="single-select form-control" :class="[form.errors.type ? 'border-danger mb-1' : '']" v-model="form.type">
                                            <option value="">{{ $t('messages.item.select_type') }}</option>
                                            <option :value="'primary'" >Primary</option>
                                            <option :value="'secondary'">Secondary</option>
                                        </select>
                                        <InputError :message="form.errors.type" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="unit_id" class="form-label">{{ $t('messages.item.unit') }} <span class="text-danger">*</span></label>
                                        <select class="single-select form-control" :class="[form.errors.unit_id ? 'border-danger mb-1' : '']" v-model="form.unit_id">
                                            <option value="">{{ $t('messages.item.select_unit') }}</option>
                                            <option v-for="(unit,index) in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.unit_id" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="cost_per_unit" class="form-label">{{ $t('messages.item.cost_per_unit') }}</label>
                                        <Input id="cost_per_unit" type="number" step="any" v-model="form.cost_per_unit" :class="[form.errors.cost_per_unit ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.item.cost_per_unit') }}" />
                                        <InputError :message="form.errors.cost_per_unit" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="stock_alert_quantity" class="form-label">{{ $t('messages.item.stock_alert_quantity') }}</label>
                                        <Input id="stock_alert_quantity" type="number" step="any" v-model="form.stock_alert_quantity" :class="[form.errors.stock_alert_quantity ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.item.stock_alert_quantity') }}" />
                                        <InputError :message="form.errors.stock_alert_quantity" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">{{ $t('messages.item.status') }}</label>
                                        <select class="single-select form-control" :class="[form.errors.is_active ? 'border-danger mb-1' : '']" v-model="form.is_active">
                                            <option value="">{{ $t('messages.item.select_status') }}</option>
                                            <option :value="1">{{ $t('messages.item.active') }}</option>
                                            <option :value="0">{{ $t('messages.item.inactive') }}</option>
                                        </select>
                                        <InputError :message="form.errors.is_active" />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">{{ $t('messages.item.close') }}</button>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> {{ editingItem ? $t('messages.item.update') : $t('messages.item.save') }}</button>
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
