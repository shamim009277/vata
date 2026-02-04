<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import VehicleNavbar from '@/components/VehicleNavbar.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    vehicles: Object,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    name: '',
    number: '',
    driver_name: '',
    driver_phone: '',
    status: 'active',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (vehicle) => {
    isEditing.value = true;
    form.id = vehicle.id;
    form.name = vehicle.name;
    form.number = vehicle.number;
    form.driver_name = vehicle.driver_name;
    form.driver_phone = vehicle.driver_phone;
    form.status = vehicle.status;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('vehicles.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('vehicles.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteVehicle = (id) => {
    if (confirm('Are you sure you want to delete this vehicle?')) {
        router.delete(route('vehicles.destroy', id));
    }
};
</script>

<template>
    <Head title="Vehicle List" />

    <AppLayout1>
        <div class="row">
            <div class="col-12">
                <div class="card radius-2 border-top border-0 border-2 border-primary">
                    <div class="card-header">
                        <div class="card-title d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
                            <h6 class="mb-0 text-primary d-flex align-items-center">
                                <a href="javascript:;" class="me-2">
                                    <i class="fadeIn animated bx bx-car"></i> গাড়ির হিসাব
                                </a>
                            </h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <VehicleNavbar />
                        
                        <div class="tab-content py-3">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="mb-0">গাড়ি লিস্ট</h5>
                                    <button @click="openCreateModal" class="btn btn-primary px-5"><i class="bx bx-plus"></i> নতুন গাড়ি</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>ID</th>
                                                <th>গাড়ির নাম/নম্বর</th>
                                                <th>ড্রাইভার</th>
                                                <th>স্ট্যাটাস</th>
                                                <th>অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="vehicle in vehicles.data" :key="vehicle.id">
                                                <td>#{{ vehicle.id }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold">{{ vehicle.name }}</span>
                                                        <small class="text-secondary">{{ vehicle.number }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>{{ vehicle.driver_name || 'N/A' }}</span>
                                                        <small class="text-secondary">{{ vehicle.driver_phone }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span v-if="vehicle.status === 'active'" class="badge bg-success">সক্রিয়</span>
                                                    <span v-else-if="vehicle.status === 'maintenance'" class="badge bg-warning">মেরামত</span>
                                                    <span v-else class="badge bg-danger">নিষ্ক্রিয়</span>
                                                </td>
                                                <td>
                                                    <button @click="openEditModal(vehicle)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                    <button @click="deleteVehicle(vehicle.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="vehicles.data.length === 0">
                                                <td colspan="5" class="text-center">কোনো রেকর্ড পাওয়া যায়নি</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'গাড়ি এডিট করুন' : 'নতুন গাড়ি যোগ করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">গাড়ির নাম</label>
                                <input v-model="form.name" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">গাড়ির নম্বর</label>
                                <input v-model="form.number" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ড্রাইভার নাম</label>
                                <input v-model="form.driver_name" type="text" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ড্রাইভার ফোন</label>
                                <input v-model="form.driver_phone" type="text" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">স্ট্যাটাস</label>
                                <select v-model="form.status" class="form-select">
                                    <option value="active">সক্রিয়</option>
                                    <option value="maintenance">মেরামত</option>
                                    <option value="inactive">নিষ্ক্রিয়</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                    {{ isEditing ? 'আপডেট' : 'সেভ করুন' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout1>
</template>
