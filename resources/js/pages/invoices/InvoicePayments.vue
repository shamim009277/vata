<script setup>
import AppLayout1 from '@/layouts/AppLayout1.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  invoice: Object,
  payments: Array,
  business_store: Object,
});
</script>

<template>
  <Head title="পেমেন্ট বিস্তারিত" />
  <AppLayout1>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title">পেমেন্ট বিস্তারিত (চালান: {{ invoice.invoice_no }})</h6>
            <Link :href="route('invoice.all')" class="btn btn-secondary btn-sm">পিছনে যান</Link>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <strong>কাস্টমার:</strong> {{ invoice.customer?.name }} |
              <strong>ফোন:</strong> {{ invoice.customer?.phone }} |
              <strong>ঠিকানা:</strong> {{ invoice.customer?.address }}
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>তারিখ</th>
                    <th class="text-end">মোট</th>
                    <th class="text-end">নগদ</th>
                    <th class="text-end">বাকি</th>
                    <th>মেথড</th>
                    <th>নোট</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in payments" :key="p.id">
                    <td>{{ p.payment_date }}</td>
                    <td class="text-end">{{ Number(p.total_amount).toFixed(2) }}</td>
                    <td class="text-end">{{ Number(p.paid_amount).toFixed(2) }}</td>
                    <td class="text-end">{{ Number(p.due_amount).toFixed(2) }}</td>
                    <td>{{ p.method || '-' }}</td>
                    <td>{{ p.note || '-' }}</td>
                  </tr>
                  <tr v-if="!payments || payments.length === 0">
                    <td colspan="6" class="text-center">কোনো পেমেন্ট পাওয়া যায়নি</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout1>
</template>

