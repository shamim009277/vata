<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    menus: Array,
    allMenus: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    title: '',
    url: '',
    route: '',
    icon: '',
    parent_id: null,
    order: 0,
    permission_name: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (menu) => {
    isEditing.value = true;
    form.id = menu.id;
    form.title = menu.title;
    form.url = menu.url;
    form.route = menu.route;
    form.icon = menu.icon;
    form.parent_id = menu.parent_id;
    form.order = menu.order;
    form.permission_name = menu.permission_name;
    form.is_active = Boolean(menu.is_active);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.menus.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.menus.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteMenu = (id) => {
    if (confirm('Are you sure you want to delete this menu?')) {
        router.delete(route('admin.menus.destroy', id));
    }
};
</script>

<template>
    <Head title="Menu Management" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <i class="fadeIn animated bx bx-menu"></i> মেনু ম্যানেজমেন্ট
                            </h6>
                            <button @click="openCreateModal" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i> নতুন মেনু</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>টাইটেল</th>
                                        <th>URL/Route</th>
                                        <th>আইকন</th>
                                        <th>অর্ডার</th>
                                        <th>পারমিশন</th>
                                        <th>স্ট্যাটাস</th>
                                        <th>অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="menu in menus" :key="menu.id">
                                        <!-- Parent Menu -->
                                        <tr class="bg-light">
                                            <td class="fw-bold">{{ menu.title }}</td>
                                            <td>
                                                <span v-if="menu.route" class="badge bg-info text-dark">{{ menu.route }}</span>
                                                <span v-else>{{ menu.url }}</span>
                                            </td>
                                            <td><i :class="menu.icon" v-if="menu.icon"></i> {{ menu.icon }}</td>
                                            <td>{{ menu.order }}</td>
                                            <td>{{ menu.permission_name }}</td>
                                            <td>
                                                <span v-if="menu.is_active" class="badge bg-success">Active</span>
                                                <span v-else class="badge bg-danger">Inactive</span>
                                            </td>
                                            <td>
                                                <button @click="openEditModal(menu)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                <button @click="deleteMenu(menu.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                            </td>
                                        </tr>
                                        <!-- Children Menus -->
                                        <tr v-for="child in menu.children" :key="child.id">
                                            <td class="ps-5">↳ {{ child.title }}</td>
                                            <td>
                                                <span v-if="child.route" class="badge bg-info text-dark">{{ child.route }}</span>
                                                <span v-else>{{ child.url }}</span>
                                            </td>
                                            <td><i :class="child.icon" v-if="child.icon"></i> {{ child.icon }}</td>
                                            <td>{{ child.order }}</td>
                                            <td>{{ child.permission_name }}</td>
                                            <td>
                                                <span v-if="child.is_active" class="badge bg-success">Active</span>
                                                <span v-else class="badge bg-danger">Inactive</span>
                                            </td>
                                            <td>
                                                <button @click="openEditModal(child)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                <button @click="deleteMenu(child.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'মেনু এডিট করুন' : 'নতুন মেনু তৈরি করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">টাইটেল</label>
                                    <input v-model="form.title" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">প্যারেন্ট মেনু</label>
                                    <select v-model="form.parent_id" class="form-select">
                                        <option :value="null">কোন প্যারেন্ট নেই (Top Level)</option>
                                        <option v-for="m in allMenus" :key="m.id" :value="m.id">{{ m.title }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Route Name (Optional)</label>
                                    <input v-model="form.route" type="text" class="form-control" placeholder="e.g. dashboard">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">URL (Optional)</label>
                                    <input v-model="form.url" type="text" class="form-control" placeholder="e.g. /custom-url">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">আইকন (Boxicons class)</label>
                                    <input v-model="form.icon" type="text" class="form-control" placeholder="e.g. bx bx-home">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">অর্ডার</label>
                                    <input v-model="form.order" type="number" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">পারমিশন নাম</label>
                                    <input v-model="form.permission_name" type="text" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input v-model="form.is_active" class="form-check-input" type="checkbox" id="isActive">
                                        <label class="form-check-label" for="isActive">অ্যাক্টিভ</label>
                                    </div>
                                </div>
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
