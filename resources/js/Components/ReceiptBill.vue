<template>
  <div id="receipt-print" class="print-only" v-if="hasData">
    <div class="receipt-header text-center">
      <div class="w-100 d-flex justify-content-center">
        <img :src="'/images/qr.jpg'" alt="QR Code" class="mb-2" style="width: 130px; height: 130px;" />
      </div>
      <h2 class="font-weight-bold m-0">COUCOU CAFE</h2>
      <p class="m-0 text-sm">Hotline: 078.337.0049</p>
      <hr class="dashed-line" />
      <h4 class="font-weight-bold my-1">HOÁ ĐƠN THANH TOÁN</h4>
      <div class="text-sm">Bàn: <strong>{{ order?.table?.name || table?.name }}</strong></div>
      <div class="text-sm">Ngày: {{ formatDate(order?.created_at) }}</div>
    </div>

    <hr class="dashed-line" />

    <table class="receipt-table w-100 text-sm">
      <thead>
        <tr>
          <th class="text-left">Món</th>
          <th class="text-center" width="30px">SL</th>
          <th class="text-right" width="65px">T.Tiền</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in orderItems" :key="index">
          <td class="text-left">
            {{ item.product?.name || item.name }}
            <div v-if="item.note" class="font-italic">-> {{ item.note }}</div>
          </td>
          <td class="text-center" style="vertical-align: top;">{{ item.quantity }}</td>
          <td class="text-right" style="vertical-align: top;">{{ formatPrice(item.price * item.quantity) }}</td>
        </tr>
      </tbody>
    </table>

    <hr class="dashed-line" />

    <div class="receipt-footer text-sm">
      <div class="d-flex justify-content-between font-weight-bold h5">
        <span>TỔNG CỘNG:</span>
        <span>{{ formatPrice(totalAmount) }}đ</span>
      </div>
      <hr class="dashed-line" />
      <div class="text-center mt-3">
        <p class="m-0 font-weight-bold">Cảm ơn quý khách & Hẹn gặp lại!</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  order: Object,     // Dành cho dữ liệu Order tự động in từ API
  table: Object,     // Dành cho dữ liệu Table ở trang POS
  cartItems: Array   // Dành cho giỏ hàng ở trang POS
});

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);

const formatDate = (dateStr) => {
  if (!dateStr) return new Date().toLocaleString('vi-VN');
  return new Date(dateStr).toLocaleString('vi-VN');
};

// Kiểm tra có dữ liệu để in (đơn từ API hoặc giỏ hàng)
const hasData = computed(() => {
  return !!(props.order || (props.cartItems && props.cartItems.length > 0));
});

const orderItems = computed(() => {
  if (props.order && props.order.items) return props.order.items;
  return props.cartItems || [];
});

const totalAmount = computed(() => {
  if (props.order && props.order.total_amount) return props.order.total_amount;
  return (props.cartItems || []).reduce((sum, item) => sum + (item.price * item.quantity), 0);
});
</script>

<style>
.print-only {
  display: none;
}

@media print {
  body * {
    visibility: hidden !important;
  }
  .no-print {
    display: none !important;
  }
  .print-only, .print-only * {
    visibility: visible !important;
  }
  .print-only {
    display: block !important;
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 80mm !important;
    padding: 5mm !important;
    font-family: 'Courier New', Courier, monospace !important;
    color: #000 !important;
    background: #fff !important;
  }
  .dashed-line {
    border-top: 1px dashed #000 !important;
    margin: 5px 0 !important;
  }
  .receipt-table th, .receipt-table td {
    padding: 3px 0 !important;
  }
}
</style>
