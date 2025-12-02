<template>
    <div>
        <!-- Header Info -->
        <div class="d-flex align-items-start justify-between mb-3">
            <!-- Left Side -->
            <div class="challan-address w-1/2">
                <p>
                    <strong>ডেলিভারি নং:</strong>
                    {{ delivery.invoice.invoice_no }}
                </p>
                <p>
                    <strong>ডেলিভারি তৈরি করেছেন:</strong>
                    {{ delivery.creator.name }}
                </p>
            </div>

            <!-- Right Side -->
            <div class="company-info w-1/2 text-right">
                <h4 class="text-primary mb-1">এম.এম.বি ব্রিকস</h4>
                <p>হিলালিপাড়া, কাটাবাড়ি, গোবিন্দগঞ্জ</p>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="row-info d-flex mb-3">
            <!-- Column 1: Left aligned -->
            <div class="info-col-left">
                <p>
                    <strong>নাম:</strong>
                    {{ delivery.customer.name }}
                </p>
                <p>
                    <strong>ফোন:</strong>
                    {{ delivery.customer.phone }}
                </p>
                <p>
                    <strong>ঠিকানা:</strong>
                    {{ delivery.customer.address }}
                </p>
                <p>
                    <strong>নোট:</strong>
                    {{ delivery.note || '-' }}
                </p>
            </div>

            <!-- Column 3: Right aligned -->
            <div class="info-col-right">
                <p>
                    <strong>ডেলিভারি তারিখ:</strong>
                    {{ formatDate(delivery.delivery_date) }}
                </p>
                <p>
                    <strong>ড্রাইভার:</strong>
                    {{ delivery.driver_name }}
                </p>
                <p>
                    <strong>ফোন:</strong>
                    {{ delivery.driver_phone }}
                </p>
                <p>
                    <strong>ট্রাক নম্বর:</strong>
                    {{ delivery.truck_number }}
                </p>
                <p>
                    <strong>সিজন:</strong>
                    {{ delivery.season }}
                </p>
            </div>
        </div>

        <!-- Item Table -->
        <table class="table table-bordered" style="font-size: small;">
            <thead class="bg-primary text-white">
                <tr>
                    <th>#</th>
                    <th>শ্রেণি</th>
                    <th>রেট</th>
                    <th>পরিমাণ</th>
                    <th>মূল্য</th>
                    <th>ডেলিভারি</th>
                    <th>ডেলিভারি বাকি</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ delivery.item.name }}</td>
                    <td>{{ delivery.rate }}</td>
                    <td>{{ delivery.quantity }}</td>
                    <td>{{ delivery.amount }}</td>
                    <td>{{ delivery.delivery_qty }}</td>
                    <td>{{ delivery.quantity - delivery.delivery_qty }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
const props = defineProps({
    delivery: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('bn-BD', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<style scoped>
.challan-address {
    text-align: left;
    line-height: 0.5;
}
.company-info {
    text-align: right;
    line-height: 0.5;
}
.d-flex {
    display: flex;
}
.justify-between {
    justify-content: space-between;
}
.align-items-start {
    align-items: flex-start;
}
.w-1\/2 {
    width: 50%;
}
.mb-3 {
    margin-bottom: 1rem;
}
.mb-1 {
    margin-bottom: 0.25rem;
}
.row-info {
    display: flex;
    gap: 15px;
    margin-bottom: 1rem;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}
.info-col-left {
    width: 50%;
    text-align: left;
}
.info-col-right {
    width: 50%;
    text-align: right;
}
.info-col-left p,
.info-col-middle p,
.info-col-right p {
    margin: 5px 0;
    font-size: 12px;
}
.info-col-left p strong,
.info-col-middle p strong,
.info-col-right p strong {
    color: #004882;
}
</style>
