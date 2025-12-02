<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';
import axios from 'axios'


const props = defineProps({
    heads: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.perPage || 10);

const showModal = ref(false);
const editingHead = ref(false);
const spinBtn = ref(false);
const suggestions = ref([]);
const showSuggestions = ref(false);

const form = useForm({
    name: '',
    group: '',
    is_active: true
});

const createHead = () => {
    showModal.value = true;
    editingHead.value = false;
    form.reset();
}

const openEdit = (item) => {
    showModal.value = true;
    editingHead.value = item;

    form.name = item.name;
    form.group = item.group;
    form.is_active = item.is_active;
}



const fetchGroups = async () => {
    if (form.group.length < 1) {
        showSuggestions.value = false
        return
    }

    try {
        const response = await axios.get(`/payment_head/groups`, {
            params: { q: form.group }
        })
        suggestions.value = response.data
        showSuggestions.value = true
    } catch (error) {
        console.error(error)
    }
}

const hideSuggestions = () => {
    showSuggestions.value = false;
}

const selectGroup = (group) => {
    form.group = group;
    showSuggestions.value = false;
}

const updateStatus = (item) => {
    router.put(`/payment-head/${item.id}/status`, {
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
            form.delete(`/payment-head/${id}`, {
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
    if (editingHead.value) {
        form.put(`/payment_head/${editingHead.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
                spinBtn.value = false;
            }
        });
    } else {
        form.post('/payment_head', {
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
    router.get(route('payment-head.index'), {
        search: search.value,
        perPage: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
});
</script>
<template>

    <Head title="খতিয়ান" />
    <AppLayout1>
        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center" style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i>খতিয়ান</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createHead"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>নতুন খতিয়ান</button>
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
                                                    <th>গ্রুপ</th>
                                                    <th>স্টাটাস</th>
                                                    <th>বাটন</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(head, index) in heads.data" :key="head.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ head.name }}</td>
                                                    <td>{{ head.group }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="head.is_active"
                                                                @change="updateStatus(head)" :checked="head.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(head)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(head.id)" class="text-danger" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12 col-md-5">
                                        <div class="dataTables_info" role="status" aria-live="polite">
                                            Showing {{ heads.from }} to {{ heads.to }} of {{ heads.total }} entries
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-7">
                                        <div class="dataTables_paginate paging_simple_numbers">
                                            <ul class="pagination" style="display: flex; gap: 4px;">
                                                <li v-for="link in heads.links" :key="link.label"
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
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingHead ?
                                    'খতিয়ান আপডেট  করেন' : 'নতুন খতিয়ান যোগ করেন' }}
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
                                        <label for="group" class="form-label">গ্রুপ</label>
                                        <input 
                                            id="group" 
                                            type="text" 
                                            v-model="form.group"
                                            class="form-control form-control-sm"
                                            :class="[form.errors.group ? 'border-danger mb-1' : '']"
                                            placeholder="গ্রুপ"
                                            @focus="fetchGroups"
                                            @input="fetchGroups"
                                            @blur="hideSuggestions"
                                            autocomplete="off"
                                        />
                                        <InputError :message="form.errors.group" />

                                        <ul v-if="showSuggestions && suggestions.length"
                                            class="list-group position-absolute w-95"
                                            style="z-index: 1000; max-height: 150px; overflow-y: auto;">
                                            <li 
                                                v-for="(item, index) in suggestions" 
                                                :key="index"
                                                class="list-group-item list-group-item-action py-1"
                                                @mousedown.prevent="selectGroup(item)"
                                            >
                                                {{ item }}
                                            </li>
                                        </ul>
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
                                        {{ editingHead ? 'Update' : 'Save' }}
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
