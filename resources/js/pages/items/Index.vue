<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';


const props = defineProps({
    items: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);

const showModal = ref(false);
const editingPlan = ref(false);
const spinBtn = ref(false);

const form = useForm({
    name: '',
    type: '',
    rate: '',
    is_active: true
});

const createItem = () => {
    showModal.value = true;
    editingPlan.value = false;
    form.reset();
}

const openEdit = (item) => {
    showModal.value = true;
    editingPlan.value = item;

    form.name = item.name;
    form.type = item.type;
    form.rate = item.rate;
    form.is_active = item.is_active;

}

const updateStatus = (item) => {
    router.put(`/items/${item.id}/status`, {
        is_active: item.is_active
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
        customClass: {
            title: 'swal-title-small',
            confirmButton: 'swal-btn-small swal-confirm-btn',
            cancelButton: 'swal-btn-small swal-cancel-btn',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/items/${id}`, {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Plan deleted successfully.',
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
                        title: 'Error!',
                        text: 'Something went wrong.',
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

const submit = () => {
    spinBtn.value = true;
    if (editingPlan.value) {
        form.put(`/items/${editingPlan.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
                spinBtn.value = false;
            }
        });
    } else {
        form.post('/items', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
                spinBtn.value = false;
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

    <Head title="শ্রেণি এবং রেট" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i>শ্রেণি এবং রেট</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createItem"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>নতুন শ্রেণি</button>
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
                                                    <th>শ্রেণির ধরণ</th>
                                                    <th>রেট</th>
                                                    <th>স্টাটাস</th>
                                                    <th>বাটন</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in items.data" :key="item.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td>{{ item.type }}</td>
                                                    <td>{{ item.rate }}</td>
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
                                                <li v-for="link in items.links" :key="link.label"
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
                                    'শ্রেণি আপডেট  করেন' : 'নতুন শ্রেণি যোগ করেন' }}
                                </h6>
                                <button type="button" class="btn-close" @click="showModal = false"></button>
                            </div>
                            <form @submit.prevent="submit">
                                <div class="modal-body row">
                                    <div class="col-12 mb-3">
                                        <label for="name" class="form-label">নাম <span class="text-danger">*</span></label>
                                        <Input id="name" type="text" v-model="form.name"
                                            :class="[form.errors.name ? 'border-danger mb-1' : '']" class="form-control form-control-sm"
                                            placeholder="নাম" required />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="type" class="form-label">শ্রেণির ধরণ <span class="text-danger">*</span></label>
                                        <select name="type" id="" class="form-control form-control-sm" v-model="form.type"
                                            :class="[form.errors.type ? 'border-danger mb-1' : '']" required>
                                            <option value="">সিলেক্ট শ্রেণির ধরণ</option>
                                            <option value="ইট">ইট</option>
                                            <option value="আধলা">আধলা</option>
                                        </select>
                                        <InputError :message="form.errors.type" />
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="rate" class="form-label">রেট (৳)<span class="text-danger">*</span></label>
                                        <Input id="rate" type="number" step="any" min="0" v-model="form.rate"
                                            :class="[form.errors.rate ? 'border-danger mb-1' : '']" required
                                            class="form-control form-control-sm" placeholder="রেট" />
                                        <InputError :message="form.errors.rate" />
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label for="interval" class="form-label">Status</label>
                                        <select class="single-select form-control form-control-sm"
                                            :class="[form.errors.is_active ? 'border-danger mb-1' : '']" required
                                            v-model="form.is_active">
                                            <option value="">সিলেক্ট স্ট্যাটাস</option>
                                            <option :value="true">এক্টিভ </option>
                                            <option :value="false">ইনএক্টিভ</option>
                                        </select>
                                        <InputError :message="form.errors.is_active" />
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i v-if="spinBtn" class="bx bx-loader bx-spin"></i>
                                        <i v-else class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>
                                        {{ editingPlan ? 'Update' : 'Save' }}
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
