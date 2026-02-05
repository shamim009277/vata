<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    permissions: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    name: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (permission) => {
    isEditing.value = true;
    form.id = permission.id;
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
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.permissions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deletePermission = (id) => {
    if (confirm('Are you sure you want to delete this permission?')) {
        router.delete(route('admin.permissions.destroy', id));
    }
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
                                <i class="fadeIn animated bx bx-key"></i> পারমিশন ম্যানেজমেন্ট
                            </h6>
                            <button @click="openCreateModal" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> নতুন পারমিশন</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>পারমিশন নাম</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="permission in permissions" :key="permission.id">
                                        <td>{{ permission.name }}</td>
                                        <td>
                                            <button @click="openEditModal(permission)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                            <button @click="deletePermission(permission.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                        </td>
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
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'পারমিশন এডিট করুন' : 'নতুন পারমিশন তৈরি করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">পারমিশন নাম</label>
                                <input v-model="form.name" type="text" class="form-control" required>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" class="btn btn-secondary me-2" @click="closeModal">বন্ধ করুন</button>
                                <button type="submit" class="btn btn-primary">{{ isEditing ? 'আপডেট করুন' : 'সেভ করুন' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
