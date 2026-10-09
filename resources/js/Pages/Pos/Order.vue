<template>
  <Head title="Đặt món" />
  <div class="pos-container bg-light min-vh-100 d-flex flex-column">
    <!-- TOP NAVBAR -->
    <div class="bg-primary text-white p-2 p-md-3 d-flex align-items-center justify-content-between sticky-top shadow-sm no-print">
      <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-light mr-2 rounded-circle" @click="goBack">
          <i class="fas fa-arrow-left text-primary"></i>
        </button>
        <div>
          <h5 class="m-0 font-weight-bold">{{ table.name }}</h5>
          <small class="text-light-50">{{ table.area }}</small>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light font-weight-bold mr-2" @click="triggerPrintBill" :disabled="cart.length === 0">
          <i class="fas fa-print mr-1 text-dark"></i> In Bill
        </button>
        <button v-if="cart.length > 0 && table.status === 'occupied'" class="btn btn-sm btn-success font-weight-bold" @click="completeOrder">
          <i class="fas fa-check-circle mr-1"></i> Thanh Toán
        </button>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container-fluid flex-grow-1 p-2 p-md-3 no-print">
      <div class="row h-100">
        <!-- CỘT TRÁI: DANH SÁCH MÓN NƯỚC -->
        <div class="col-12 col-lg-7 col-xl-8 mb-3">
          <div class="bg-white p-2 rounded shadow-sm mb-3 overflow-auto text-nowrap">
            <button
              class="btn btn-sm mr-2 rounded-pill px-3"
              :class="selectedCategory === null ? 'btn-primary' : 'btn-outline-secondary'"
              @click="selectedCategory = null"
            >
              Tất cả
            </button>
            <button
              v-for="cat in categories"
              :key="cat.id"
              class="btn btn-sm mr-2 rounded-pill px-3"
              :class="selectedCategory === cat.id ? 'btn-primary' : 'btn-outline-secondary'"
              @click="selectedCategory = cat.id"
            >
              {{ cat.name }}
            </button>
          </div>

          <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-2">
            <div v-for="product in filteredProducts" :key="product.id" class="col mb-2">
              <div
                class="card h-100 border-0 shadow-sm rounded-lg product-card"
                @click="addToCart(product)"
              >
                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                  <h6 class="font-weight-bold mb-1 text-dark text-truncate">{{ product.name }}</h6>
                  <div class="text-primary font-weight-bold">{{ formatPrice(product.price) }}đ</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CỘT PHẢI: GIỎ HÀNG DESKTOP -->
        <div class="col-12 col-lg-5 col-xl-4 d-none d-lg-block">
          <div class="card border-0 shadow-sm h-100 d-flex flex-column position-sticky" style="top: 80px; max-height: calc(100vh - 100px);">
            <div class="card-header">
                <div class="bg-white font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-shopping-cart text-primary mr-2"></i>Đơn Hàng ({{ cartTotalItems }})</span>
                    <button class="btn btn-xs btn-outline-danger" @click="cart = []" v-if="cart.length > 0">Xóa hết</button>
                </div>
            </div>

            <div class="card-body p-2 overflow-auto flex-grow-1">
              <div v-if="cart.length === 0" class="text-center text-muted py-5">
                <i class="fas fa-mug-hot fa-3x mb-2 text-light"></i>
                <p>Chưa chọn món nào</p>
              </div>

              <div v-for="(item, index) in cart" :key="index" class="border-bottom py-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <div class="font-weight-bold">{{ item.name }}</div>
                    <small class="text-primary font-weight-bold">{{ formatPrice(item.price) }}đ</small>
                  </div>
                  <div class="d-flex align-items-center">
                    <button class="btn btn-xs btn-outline-danger px-2" @click="updateQuantity(index, -1)">-</button>
                    <span class="mx-2 font-weight-bold">{{ item.quantity }}</span>
                    <button class="btn btn-xs btn-outline-primary px-2" @click="updateQuantity(index, 1)">+</button>
                  </div>
                </div>

                <div class="mt-1 d-flex align-items-center">
                  <i class="fas fa-pen text-muted mr-1 text-xs"></i>
                  <input
                    type="text"
                    v-model="item.note"
                    class="form-control form-control-sm border-0 bg-light"
                    placeholder="Ghi chú (ít ngọt, 50% đá...)"
                  />
                </div>
              </div>
            </div>

            <div class="card-footer bg-white border-top">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="h6 mb-0 font-weight-bold">Tổng tiền:</span>
                <span class="h4 mb-0 font-weight-bold text-danger">{{ formatPrice(cartTotalPrice) }}đ</span>
              </div>
              <button class="btn btn-primary btn-block font-weight-bold py-2 shadow" :disabled="cart.length === 0" @click="submitOrder">
                <i class="fas fa-paper-plane mr-1"></i> XÁC NHẬN GỬI ĐƠN
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- GIỎ HÀNG MOBILE -->
    <div class="fixed-bottom bg-white border-top shadow-lg p-3 d-lg-none no-print" style="z-index: 1000;">
      <div class="d-flex justify-content-between align-items-center mb-2" @click="showCartDetail = !showCartDetail">
        <div>
          <span class="badge badge-primary rounded-circle p-2">{{ cartTotalItems }}</span>
          <strong class="ml-2">Đơn hàng hiện tại</strong>
          <i class="fas ml-1" :class="showCartDetail ? 'fa-chevron-down' : 'fa-chevron-up'"></i>
        </div>
        <div class="h5 m-0 font-weight-bold text-danger">{{ formatPrice(cartTotalPrice) }}đ</div>
      </div>

      <div v-if="showCartDetail" class="cart-items-list my-2 border-top pt-2" style="max-height: 280px; overflow-y: auto;">
        <div v-if="cart.length === 0" class="text-center text-muted py-3">Chưa chọn món nào</div>
        <div v-for="(item, index) in cart" :key="index" class="border-bottom pb-2 mb-2">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="font-weight-bold text-sm">{{ item.name }}</div>
              <small class="text-muted">{{ formatPrice(item.price) }}đ</small>
            </div>
            <div class="d-flex align-items-center">
              <button class="btn btn-xs btn-outline-danger px-2" @click="updateQuantity(index, -1)">-</button>
              <span class="mx-2 font-weight-bold">{{ item.quantity }}</span>
              <button class="btn btn-xs btn-outline-primary px-2" @click="updateQuantity(index, 1)">+</button>
            </div>
          </div>
          <input
            type="text"
            v-model="item.note"
            class="form-control form-control-sm bg-light mt-1"
            placeholder="Ghi chú (ít đường, không đá...)"
          />
        </div>
      </div>

      <button class="btn btn-primary btn-block font-weight-bold py-2 shadow" :disabled="cart.length === 0" @click="submitOrder">
        <i class="fas fa-paper-plane mr-1"></i> XÁC NHẬN GỬI ĐƠN
      </button>
    </div>

    <!-- KHU VỰC IN BILL DÙNG COMPONENT CHUẨN -->
    <ReceiptBill :table="table" :cartItems="cart" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ReceiptBill from '@/Components/ReceiptBill.vue';

const props = defineProps({
  table: Object,
  categories: Array,
  currentOrder: Object,
});

const selectedCategory = ref(null);
const showCartDetail = ref(false);
const cart = ref([]);

const filteredProducts = computed(() => {
  let list = [];
  props.categories.forEach(c => {
    if (selectedCategory.value === null || selectedCategory.value === c.id) {
      list.push(...c.products);
    }
  });
  return list;
});

onMounted(() => {
  if (props.currentOrder && props.currentOrder.items) {
    cart.value = props.currentOrder.items.map(i => ({
      product_id: i.product_id,
      name: i.product?.name || 'Món nước',
      price: i.price,
      quantity: i.quantity,
      note: i.note || ''
    }));
  }
});

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);

const addToCart = (product) => {
  const index = cart.value.findIndex(item => item.product_id === product.id);
  if (index !== -1) {
    cart.value[index].quantity++;
  } else {
    cart.value.push({
      product_id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      note: ''
    });
  }
};

const updateQuantity = (index, delta) => {
  cart.value[index].quantity += delta;
  if (cart.value[index].quantity <= 0) {
    cart.value.splice(index, 1);
  }
};

const cartTotalItems = computed(() => cart.value.reduce((sum, item) => sum + item.quantity, 0));
const cartTotalPrice = computed(() => cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));

const goBack = () => router.get('/pos');

const triggerPrintBill = () => {
  nextTick(() => {
    setTimeout(() => {
      window.print();
    }, 200);
  });
};

const submitOrder = () => {
  const isAutoPrint = localStorage.getItem('pos_auto_print') === 'true';

  router.post(`/pos/table/${props.table.id}/save`, {
    items: cart.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      if (isAutoPrint) {
        //triggerPrintBill();
      }
    }
  });
};

const completeOrder = () => {
  if (confirm(`Xác nhận thanh toán và giải phóng ${props.table.name}?`)) {
    router.post(`/pos/table/${props.table.id}/complete`);
  }
};
</script>

<style>
.product-card {
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}
.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.12) !important;
}
.text-xs {
  font-size: 0.75rem;
}
</style>
