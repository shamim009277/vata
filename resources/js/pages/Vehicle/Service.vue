<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import VehicleNavbar from '@/components/VehicleNavbar.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    services: Object,
    vehicles: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    vehicle_id: '',
    date: new Date().toISOString().substr(0, 10),
    description: '',
    cost: '',
    workshop_name: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.date = new Date().toISOString().substr(0, 10);
    showModal.value = true;
};

const openEditModal = (service) => {
    isEditing.value = true;
    form.id = service.id;
    form.vehicle_id = service.vehicle_id;
    form.date = service.date;
    form.description = service.description;
    form.cost = service.cost;
    form.workshop_name = service.workshop_name;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('vehicle-services.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('vehicle-services.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteService = (id) => {
    if (confirm('Are you sure you want to delete this service record?')) {
        router.delete(route('vehicle-services.destroy', id));
    }
};
</script>

<template>
    <Head title="Vehicle Service" />

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
                                    <h5 class="mb-0">গাড়ির সার্ভিস</h5>
                                    <button @click="openCreateModal" class="btn btn-primary px-5"><i class="bx bx-plus"></i> নতুন রেকর্ড</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>তারিখ</th>
                                                <th>গাড়ি</th>
                                                <th>বিবরণ</th>
                                                <th>খরচ (টাকা)</th>
                                                <th>ওয়ার্কশপ</th>
                                                <th>অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="service in services.data" :key="service.id">
                                                <td>{{ service.date }}</td>
                                                <td>{{ service.vehicle?.name }} ({{ service.vehicle?.number }})</td>
                                                <td>{{ service.description }}</td>
                                                <td>{{ service.cost }}</td>
                                                <td>{{ service.workshop_name || '-' }}</td>
                                                <td>
                                                    <button @click="openEditModal(service)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                    <button @click="deleteService(service.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="services.data.length === 0">
                                                <td colspan="6" class="text-center">কোনো রেকর্ড পাওয়া যায়নি</td>
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
                        <h5 class="modal-title">{{ isEditing ? 'সার্ভিস রেকর্ড এডিট করুন' : 'নতুন সার্ভিস রেকর্ড' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="mb-3">
                                <label class="form-label">গাড়ি</label>
                                <select v-model="form.vehicle_id" class="form-select" required>
                                    <option value="" disabled>গাড়ি সিলেক্ট করুন</option>
                                    <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.name }} - {{ v.number }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">তারিখ</label>
                                <input v-model="form.date" type="date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">বিবরণ</label>
                                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">খরচ (টাকা)</label>
                                <input v-model="form.cost" type="number" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ওয়ার্কশপ নাম</label>
                                <input v-model="form.workshop_name" type="text" class="form-control">
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
