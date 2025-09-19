<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import Swal from 'sweetalert2';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Pagination from '@/components/Pagination.vue';
import TableSearchFilter from '@/components/TableSearchFilter.vue';

const props = defineProps({
    modules: Object,
    filters: Object
});

const breadcrumbItems = [
    {
        title: 'Setting',
        href: '#',
    },
    {
        title: 'Modules',
        href: '/modules',
    },
];

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.perPage || 10);
const showModal = ref(false);
const editingModule = ref(false);
const moduleList = ref([...props.modules.data]);

const form = useForm({
    name: '',
    is_active: true
});

const createModule = () => {
    showModal.value = true;
    editingModule.value = false;
    form.reset();
};

const openEdit = (module) => {
    showModal.value = true;
    editingModule.value = module;
    form.name = module.name;
    form.is_active = module.is_active;
};

const updateStatus = (module) => {
    router.put(`/modules/${module.id}/status`, {
        is_active: module.is_active
    }, {
        preserveScroll: true,
    });
};

const submit = () => {
    if (editingModule.value) {
        form.put(`/modules/${editingModule.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    } else {
        form.post('/modules', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                showModal.value = false;
            }
        });
    }
};

const confirmDelete = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to delete this module!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            form.get(`/modules/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Module deleted successfully.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    form.reset();
                    showModal.value = false;
                },
                onError: () => {
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                }
            });
        }
    });
};

watch([search, perPage], ([newSearch, newPerPage]) => {
    router.get(route('modules.index'), {
        search: newSearch,
        perPage: newPerPage,
    }, {
        preserveState: true,
        replace: true,
    });
});


watch(() => props.modules.data, (newData) => {
    moduleList.value = newData;
});
</script>

<template>

    <Head title="Module" />
    <AppLayout1>
        <div class="row">
            <Breadcrumb :breadcrumbItems="breadcrumbItems" title="Modules" />

            <div class="col-lg-12">
                <h4 class="mb-3 text-primary text-center font-bold">Modules</h4>
            </div>
            <div class="col-12 col-lg-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between justify-center align-items-center"
                            style="margin-bottom: 0;">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2"><i class="fadeIn animated bx bx-list-ul"></i>Module List</a>
                            </h6>
                            <button class="btn btn-primary btn-sm" @click="createModule"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i>Add Module</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="example_wrapper" class="dataTables_wrapper dt-bootstrap5">
                                <TableSearchFilter v-model:perPage="perPage" v-model:search="search" />

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="example" class="table table-striped table-bordered dataTable" style="width: 100%;" role="grid" aria-describedby="example_info">
                                            <thead>
                                                <tr role="row">
                                                    <th width="10%">Sl</th>
                                                    <th width="50%">Name</th>
                                                    <th width="30%">Status</th>
                                                    <th width="10%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(module, index) in moduleList" :key="module.id">
                                                    <td>{{ index + 1 }}</td>
                                                    <td>{{ module.name }}</td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="flexSwitchCheckChecked" v-model="module.is_active"
                                                                @change="updateStatus(module)"
                                                                :checked="module.is_active">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a @click="openEdit(module)" class="text-primary"
                                                            style="cursor: pointer;"><i
                                                                class="fadeIn animated bx bx-edit hover:opacity-90"
                                                                style="font-size: larger;"></i></a>
                                                        <a @click.prevent="confirmDelete(module.id)" class="text-danger"
                                                            style="cursor: pointer;"><i
                                                                class="fadeIn animated bx bx-trash hover:opacity-90"
                                                                style="font-size: larger;"></i></a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                               <Pagination :modules="modules" />
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
                                <h6 class="modal-title"><i class="bx bx-message-alt-add me-2"></i> {{ editingModule ?
                                    'Update Module' : 'Add Module' }}
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
                                    <button type="button" class="btn btn-secondary btn-sm" @click="showModal = false">Close</button>
                                    <button type="submit" class="btn btn-primary btn-sm">{{ editingPlan ? 'Update' :'Save' }}</button>
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
