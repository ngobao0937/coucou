<template>
  <AdminLayout>
    <Head title="Quản Lý Đơn Hàng - COUCOU" />
    <div class="card card-outline card-primary shadow-sm">
      <!-- HEADER & LỌC TÌM KIẾM -->
      <div class="card-header py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h3 class="card-title font-weight-bold">Danh Sách Đơn Hàng</h3>

          <div class="d-flex align-items-center gap-2 flex-wrap">
            <input
              v-model="search"
              type="text"
              class="form-control form-control-sm"
              placeholder="Tìm theo bàn hoặc Mã đơn..."
              style="width: 200px;"
              @keyup.enter="handleFilter"
            />
            <select v-model="statusFilter" class="form-control form-control-sm" style="width: 160px;" @change="handleFilter">
              <option value="">-- Tất cả trạng thái --</option>
              <option value="pending">Đang phục vụ</option>
              <option value="completed">Hoàn thành</option>
              <option value="cancelled">Đã hủy</option>
            </select>
          </div>
        </div>
      </div>

      <!-- BANG DANH SACH DON HANG -->
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle m-0">
          <thead>
            <tr>
              <th width="70" class="text-center">Mã Đơn</th>
              <th>Bàn</th>
              <th>Thời Gian</th>
              <th>Số Món</th>
              <th>Tổng Tiền</th>
              <th class="text-center">Trạng Thái</th>
              <th width="120" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in orders.data" :key="order.id">
              <td class="text-center font-weight-bold">#{{ order.id }}</td>
              <td class="font-weight-bold">{{ order.table?.name || 'N/A' }} <small class="text-muted">({{ order.table?.area }})</small></td>
              <td class="text-center">{{ formatDate(order.created_at) }}</td>
              <td class="text-center"><span class="badge badge-light border">{{ order.items?.length || 0 }} món</span></td>
              <td class="text-danger font-weight-bold text-right">{{ formatPrice(order.total_amount) }}đ</td>
              <td class="text-center">
                <span class="badge" :class="getStatusBadge(order.status)">
                  {{ getStatusText(order.status) }}
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-info btn-xs mr-1" title="Xem & Sửa món" @click="openDetailModal(order)">
                  <i class="fas fa-eye"></i> Chi tiết
                </button>
                <button
                  v-if="order.status === 'pending'"
                  class="btn btn-danger btn-xs"
                  title="Hủy đơn"
                  @click="cancelOrder(order.id)"
                >
                  <i class="fas fa-ban"></i>
                </button>
              </td>
            </tr>
            <tr v-if="orders.data.length === 0">
              <td colspan="7" class="text-center text-muted py-4">Không tìm thấy đơn hàng nào.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL XEM CHI TIẾT VÀ SỬA MÓN BÊN TRONG ĐƠN -->
    <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white py-2">
            <h5 class="modal-title font-weight-bold">Chi Tiết Đơn Hàng #{{ selectedOrder.id }} - {{ selectedOrder.table?.name }}</h5>
            <button type="button" class="close text-white" @click="showModal = false">&times;</button>
          </div>

          <div class="modal-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered table-sm m-0">
                <thead class="bg-light">
                  <tr>
                    <th>Tên Món</th>
                    <th width="120" class="text-center">Đơn Giá</th>
                    <th width="120" class="text-center">Số Lượng</th>
                    <th>Ghi Chú Món</th>
                    <th width="120" class="text-right">Thành Tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in modalItems" :key="idx">
                    <td class="align-middle font-weight-bold">{{ item.product?.name || item.name }}</td>
                    <td class="align-middle text-center">{{ formatPrice(item.price) }}đ</td>
                    <td class="align-middle text-center">
                      <input
                        v-if="selectedOrder.status === 'pending'"
                        type="number"
                        min="1"
                        v-model.number="item.quantity"
                        class="form-control form-control-sm text-center"
                      />
                      <span v-else>{{ item.quantity }}</span>
                    </td>
                    <td class="align-middle">
                      <input
                        v-if="selectedOrder.status === 'pending'"
                        type="text"
                        v-model="item.note"
                        class="form-control form-control-sm"
                        placeholder="VD: Ít ngọt..."
                      />
                      <span v-else class="text-muted">{{ item.note || '-' }}</span>
                    </td>
                    <td class="align-middle text-right font-weight-bold text-danger">
                      {{ formatPrice(item.price * item.quantity) }}đ
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <div class="text-muted text-sm">Người tạo: {{ selectedOrder.user?.name || 'N/A' }}</div>
              <div class="h5 m-0 font-weight-bold">Tổng tiền: <span class="text-danger">{{ formatPrice(calculatedTotal) }}đ</span></div>
            </div>
          </div>

          <div class="modal-footer justify-content-between py-2">
            <button type="button" class="btn btn-default btn-sm" @click="showModal = false">Đóng</button>
            <button
              v-if="selectedOrder.status === 'pending'"
              type="button"
              class="btn btn-primary btn-sm"
              @click="saveOrderChanges"
            >
              <i class="fas fa-save mr-1"></i> Lưu thay đổi
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  orders: Object,
  filters: Object
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showModal = ref(false);
const selectedOrder = ref(null);
const modalItems = ref([]);

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);
const formatDate = (dateStr) => new Date(dateStr).toLocaleString('vi-VN');

const getStatusBadge = (status) => {
  if (status === 'pending') return 'badge-warning';
  if (status === 'completed') return 'badge-success';
  return 'badge-danger';
};

const getStatusText = (status) => {
  if (status === 'pending') return 'Đang phục vụ';
  if (status === 'completed') return 'Hoàn thành';
  return 'Đã hủy';
};

const handleFilter = () => {
  router.get('/don-hang', {
    search: search.value,
    status: statusFilter.value
  }, { preserveState: true });
};

const openDetailModal = (order) => {
  selectedOrder.value = order;
  modalItems.value = order.items.map(i => ({
    product_id: i.product_id,
    name: i.product?.name,
    price: i.price,
    quantity: i.quantity,
    note: i.note || ''
  }));
  showModal.value = true;
};

const calculatedTotal = computed(() => {
  return modalItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const saveOrderChanges = () => {
  router.put(`/don-hang/${selectedOrder.value.id}`, {
    items: modalItems.value
  }, {
    onSuccess: () => showModal.value = false
  });
};

const cancelOrder = (orderId) => {
  if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
    useForm({}).post(`/don-hang/${orderId}/cancel`);
  }
};
</script>
