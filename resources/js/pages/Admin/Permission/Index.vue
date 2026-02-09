<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import { route } from 'ziggy-js';
import { usePermission } from '@/Composables/usePermission';

const { hasPermission } = usePermission();

const props = defineProps({
    permissions: Array,
    menus: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    menu_id: '',
    name: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.menu_id = '';
    showModal.value = true;
};

const openEditModal = (permission) => {
    isEditing.value = true;
    form.id = permission.id;
    form.menu_id = permission.menu_id || '';
    form.name = permission.name;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.permissions.update', form.id), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'সফল!',
                    text: 'পারমিশন আপডেট হয়েছে।',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
        });
    } else {
        form.post(route('admin.permissions.store'), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'সফল!',
                    text: 'পারমিশন তৈরি হয়েছে।',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
        });
    }
};

const deletePermission = (id) => {
    Swal.fire({
        title: 'আপনি কি নিশ্চিত?',
        text: "মুছে ফেললে এটি আর ফিরে পাওয়া যাবে না!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন!',
        cancelButtonText: 'বাতিল'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.permissions.destroy', id), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'মুছে ফেলা হয়েছে!',
                        text: 'পারমিশন সফলভাবে মুছে ফেলা হয়েছে।',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Permission Management" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-key me-1"></i> পারমিশন ম্যানেজমেন্ট
                            </h6>
                            <button v-if="hasPermission('admin.permissions.create')" @click="openCreateModal" class="btn btn-primary btn-sm"><i class="fadeIn animated bx bx-plus-medical" style="font-size: small;"></i> নতুন পারমিশন</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>মেনু</th>
                                        <th>পারমিশন নাম</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="permission in permissions" :key="permission.id">
                                        <td>{{ permission.menu ? permission.menu.title : 'N/A' }}</td>
                                        <td>{{ permission.name }}</td>
                                        <td>
                                            <a v-if="hasPermission('admin.permissions.edit')" @click.prevent="openEditModal(permission)" class="text-primary" style="cursor: pointer;"><i class="fadeIn animated bx bx-edit hover:opacity-90" style="font-size: larger;"></i></a>
                                            <a v-if="hasPermission('admin.permissions.destroy')" @click.prevent="deletePermission(permission.id)" class="text-danger ms-2" style="cursor: pointer;"><i class="fadeIn animated bx bx-trash hover:opacity-90" style="font-size: larger;"></i></a>
                                        </td>
                                    </tr>
                                    <tr v-if="permissions.length === 0">
                                        <td colspan="3" class="text-center">কোনো তথ্য পাওয়া যায়নি</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="border-top: 2px solid #004882;">
                        <h5 class="modal-title font-bold">
                            <i class="bx" :class="isEditing ? 'bx-edit' : 'bx-plus-circle'"></i>
                            {{ isEditing ? 'পারমিশন এডিট করুন' : 'নতুন পারমিশন তৈরি করুন' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">মেনু নির্বাচন করুন</label>
                                <select v-model="form.menu_id" class="form-select">
                                    <option value="" disabled>মেনু নির্বাচন করুন</option>
                                    <option v-for="menu in menus" :key="menu.id" :value="menu.id">
                                        {{ menu.title }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">পারমিশন নাম <span v-if="!isEditing" class="text-muted text-sm">(কমা দিয়ে একাধিক লিখুন)</span></label>
                                <input v-model="form.name" type="text" class="form-control" placeholder="যেমন: create, edit, delete" required>
                                <small v-if="!isEditing" class="form-text text-muted">একাধিক পারমিশন যুক্ত করতে কমা (,) ব্যবহার করুন।</small>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-secondary btn-sm me-2" @click="closeModal"><i class="bx bx-x"></i> বন্ধ করুন</button>
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bx bx-save"></i> {{ isEditing ? 'আপডেট করুন' : 'সেভ করুন' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
