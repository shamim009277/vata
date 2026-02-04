<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import VehicleNavbar from '@/components/VehicleNavbar.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    trips: Object,
    vehicles: Array,
});

const showModal = ref(false);
const isEditing = ref(false);
const form = useForm({
    id: null,
    vehicle_id: '',
    driver_name: '',
    start_location: '',
    end_location: '',
    start_time: '',
    end_time: '',
    purpose: '',
    status: 'running',
    income: 0,
    note: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (trip) => {
    isEditing.value = true;
    form.id = trip.id;
    form.vehicle_id = trip.vehicle_id;
    form.driver_name = trip.driver_name;
    form.start_location = trip.start_location;
    form.end_location = trip.end_location;
    form.start_time = trip.start_time;
    form.end_time = trip.end_time;
    form.purpose = trip.purpose;
    form.status = trip.status;
    form.income = trip.income;
    form.note = trip.note;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('vehicle-trips.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('vehicle-trips.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTrip = (id) => {
    if (confirm('Are you sure you want to delete this trip?')) {
        router.delete(route('vehicle-trips.destroy', id));
    }
};

const formatDateTime = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head title="Vehicle Trip" />

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
                                    <h5 class="mb-0">গাড়ির ট্রিপ</h5>
                                    <button @click="openCreateModal" class="btn btn-primary px-5"><i class="bx bx-plus"></i> নতুন ট্রিপ</button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th>ID</th>
                                                <th>গাড়ি</th>
                                                <th>ড্রাইভার</th>
                                                <th>রুট</th>
                                                <th>সময়</th>
                                                <th>উদ্দেশ্য</th>
                                                <th>স্ট্যাটাস</th>
                                                <th>আয়</th>
                                                <th>অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="trip in trips.data" :key="trip.id">
                                                <td>#{{ trip.id }}</td>
                                                <td>{{ trip.vehicle?.name }} ({{ trip.vehicle?.number }})</td>
                                                <td>{{ trip.driver_name }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><i class="bx bx-map-pin text-success"></i> {{ trip.start_location }}</span>
                                                        <span><i class="bx bx-map-pin text-danger"></i> {{ trip.end_location }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <small>শুরু: {{ formatDateTime(trip.start_time) }}</small>
                                                        <small>শেষ: {{ formatDateTime(trip.end_time) }}</small>
                                                    </div>
                                                </td>
                                                <td>{{ trip.purpose }}</td>
                                                <td>
                                                    <span v-if="trip.status === 'running'" class="badge bg-primary">চলমান</span>
                                                    <span v-else-if="trip.status === 'completed'" class="badge bg-success">সম্পন্ন</span>
                                                    <span v-else class="badge bg-danger">বাতিল</span>
                                                </td>
                                                <td>{{ trip.income }}</td>
                                                <td>
                                                    <button @click="openEditModal(trip)" class="btn btn-sm btn-info me-2"><i class="bx bx-edit"></i></button>
                                                    <button @click="deleteTrip(trip.id)" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr v-if="trips.data.length === 0">
                                                <td colspan="9" class="text-center">কোনো রেকর্ড পাওয়া যায়নি</td>
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
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isEditing ? 'ট্রিপ এডিট করুন' : 'নতুন ট্রিপ যোগ করুন' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">গাড়ি</label>
                                    <select v-model="form.vehicle_id" class="form-select" required>
                                        <option value="" disabled>গাড়ি সিলেক্ট করুন</option>
                                        <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.name }} - {{ v.number }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ড্রাইভার নাম</label>
                                    <input v-model="form.driver_name" type="text" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">শুরু লোকেশন</label>
                                    <input v-model="form.start_location" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">শেষ লোকেশন</label>
                                    <input v-model="form.end_location" type="text" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">শুরু সময়</label>
                                    <input v-model="form.start_time" type="datetime-local" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">শেষ সময়</label>
                                    <input v-model="form.end_time" type="datetime-local" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">উদ্দেশ্য</label>
                                    <input v-model="form.purpose" type="text" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">স্ট্যাটাস</label>
                                    <select v-model="form.status" class="form-select">
                                        <option value="running">Running</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">আয় (টাকা)</label>
                                    <input v-model="form.income" type="number" step="0.01" class="form-control">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">নোট</label>
                                    <textarea v-model="form.note" class="form-control" rows="2"></textarea>
                                </div>
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
