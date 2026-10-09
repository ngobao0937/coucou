<template>
  <AdminLayout>
    <Head title="Sơ Đồ Bàn - POS" />
    <div class="container-fluid p-0 no-print">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-th mr-2"></i>Sơ Đồ Bàn</h4>
        <span class="badge badge-info px-3 py-2">Tổng: {{ tables.length }} bàn</span>
      </div>

      <!-- Grid Bàn -->
      <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2">
        <div v-for="table in tables" :key="table.id" class="col mb-3">
          <div
            class="card h-100 shadow-sm border-0 position-relative text-white"
            :class="table.status === 'occupied' ? 'bg-warning text-dark' : 'bg-light text-dark border'"
            style="border-radius: 12px; cursor: pointer; transition: transform 0.2s;"
            @click="goToOrder(table.id)"
          >
            <div class="card-body p-3 text-center d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <small :class="table.status === 'occupied' ? 'text-dark' : 'text-muted'">{{ table.area }}</small>
                  <span class="badge" :class="table.status === 'occupied' ? 'badge-danger' : 'badge-secondary'">
                    {{ table.status === 'occupied' ? 'Đang có khách' : 'Trống' }}
                  </span>
                </div>
                <h5 class="font-weight-bold my-2">{{ table.name }}</h5>
              </div>

              <div class="mt-2 border-top pt-2" v-if="table.current_order">
                <span class="d-block font-weight-bold text-danger">
                  {{ formatPrice(table.current_order.total_amount) }}đ
                </span>
                <small :class="table.status === 'occupied' ? 'text-dark' : 'text-muted'">{{ table.current_order.items?.length || 0 }} món</small>
              </div>
              <div class="mt-2 border-top pt-2 text-muted" v-else>
                <small>Chưa có đơn</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ tables: Array });

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);

const goToOrder = (tableId) => {
  router.get(`/pos/table/${tableId}`);
};
</script>
