<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';

const props = defineProps({
    units: Object,
    filters: Object,
    unitStandards: Object,
    roots: Object,
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);

const showModal = ref(false);
const editingPlan = ref(false);

const form = useForm({
    name: '',
    code: '',
    conversion_rate: '',
    root_id: '',
    unit_standards: '',
    is_active: 1
});

const createSubscription = () => {
    showModal.value = true;
    editingPlan.value = false;
    form.reset();
}

const openEdit = (unit) => {
    showModal.value = true;
    editingPlan.value = unit;

    form.name = unit.name;
    form.code = unit.code;
    form.conversion_rate = unit.conversion_rate;
    form.root_id = unit.root_id ?? '';
    form.unit_standards = unit.unit_standards ?? '';
    form.is_active = unit.is_active;
}

const updateStatus = (unit) => {
    router.put(`/units/${unit.id}/status`, {
        is_active: unit.is_active ? 1 : 0
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
            form.delete(`/units/${id}`, {
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
        form.put(`/units/${editingPlan.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    } else {
        form.post('/units', {
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
    router.get(route('units.index'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>
<template>

    <Head title="Unit" />
    <AppLayout1>
        <div class="row">
            <div class="col-lg-12">
                <h4 class="mb-3 text-primary text-center font-bold">{{ $t('messages.unit.unit') }}</h4>
            </div>
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i> {{ $t('messages.unit.unit_list') }}</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createSubscription"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> {{ $t('messages.unit.unit_create') }}</button>
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
                                            <label>{{$t('messages.unit.search')}}:<input v-model="search" type="search" class="form-control form-control-sm" :placeholder="$t('messages.unit.search') + ' ...'" aria-controls="example"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="example" class="table table-striped table-bordered dataTable"
                                            style="width: 100%;" role="grid" aria-describedby="example_info">
                                            <thead>
                                                <tr role="row">
                                                    <th>{{ $t('table.sl') }}</th>
                                                    <th>{{ $t('table.name') }}</th>
                                                    <th>{{ $t('table.code') }}</th>
                                                    <th>{{ $t('table.rate') }}</th>
                                                    <th>{{ $t('table.root') }}</th>
                                                    <th>{{ $t('table.unit_standard') }}</th>
                                                    <th>{{ $t('table.status') }}</th>
                                                    <th>{{ $t('table.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(unit, index) in units.data" :key="unit.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ unit.name }}</td>
                                                    <td>{{ unit.code }}</td>
                                                    <td>{{ unit.conversion_rate }}</td>
                                                    <td>{{ unit.root?.name??'N/A' }}</td>
                                                    <td>{{ unit.standard_label  }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="unit.is_active"
                                                                @change="updateStatus(unit)" :checked="unit.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(unit)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(unit.id)" class="text-danger" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ units.from }} to {{ units.to }} of {{ units.total }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination" style="display: flex; gap: 4px;">
                                                <li v-for="link in units.links" :key="link.label"
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
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingPlan ? $t('messages.update_unit') : $t('messages.add_unit') }}</h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>
                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-12 mb-2">
                                        <label for="name" class="form-label">{{ $t('messages.name') }} <span class="text-danger">*</span></label>
                                        <Input id="name" type="text" v-model="form.name" :class="[form.errors.name ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.Kilogram') }}" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="code" class="form-label">{{ $t('messages.code') }} <span class="text-danger">*</span></label>
                                        <Input id="code" type="text" v-model="form.code" :class="[form.errors.code ? 'border-danger mb-1' : '']" class="form-control" placeholder="{{ $t('messages.kg') }}" />
                                        <InputError :message="form.errors.code" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="conversion_rate" class="form-label">{{ $t('messages.conversion_rate') }}</label>
                                        <Input id="conversion_rate" type="number" step="any" v-model="form.conversion_rate" :class="[form.errors.conversion_rate ? 'border-danger mb-1' : '']" class="form-control" placeholder="1" />
                                        <InputError :message="form.errors.conversion_rate" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="root_id" class="form-label">{{ $t('messages.root') }}</label>
                                        <select class="single-select form-control" :class="[form.errors.root_id ? 'border-danger mb-1' : '']" v-model="form.root_id">
                                            <option value="">{{ $t('messages.select_root') }}</option>
                                            <option v-for="(root,index) in roots" :key="root.id" :value="index">{{ root }}</option>
                                        </select>
                                        <InputError :message="form.errors.root_id" />
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label for="unit_standards" class="form-label">{{ $t('messages.unit_standards') }}</label>
                                        <select class="single-select form-control" :class="[form.errors.unit_standards ? 'border-danger mb-1' : '']" v-model="form.unit_standards">
                                            <option value="">{{ $t('messages.select_standard') }}</option>
                                            <option v-for="(standard,index) in unitStandards" :key="standard.id" :value="index">{{ standard }}</option>
                                        </select>
                                        <InputError :message="form.errors.unit_standards" />
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">{{ $t('messages.status') }}</label>
                                        <select class="single-select form-control" :class="[form.errors.is_active ? 'border-danger mb-1' : '']" v-model="form.is_active">
                                            <option value="">{{ $t('messages.select_status') }}</option>
                                            <option :value="1">{{ $t('messages.active') }}</option>
                                            <option :value="0">{{ $t('messages.inactive') }}</option>
                                        </select>
                                        <InputError :message="form.errors.is_active" />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">{{ $t('button.close') }}</button>
                                    <button type="submit" class="btn btn-primary btn-sm">{{ editingPlan ? $t('button.update') : $t('button.save') }}</button>
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
