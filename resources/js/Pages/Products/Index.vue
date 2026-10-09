<template>
  <AdminLayout>
    <Head title="Quản Lý Món Nước" />
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header">
        <div class=" py-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">Danh Sách Món Nước</h3>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus mr-1"></i> Thêm món mới
            </button>
        </div>

      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle">
          <thead>
            <tr>
              <th width="50" class="text-center">STT</th>
              <th>Tên Món</th>
              <th>Danh Mục</th>
              <th>Giá Bán</th>
              <th class="text-center">Trạng Thái</th>
              <th width="100" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in products" :key="p.id">
              <td class="text-center">{{ index + 1 }}</td>
              <td class="font-weight-bold">{{ p.name }}</td>
              <td><span class="badge badge-light border">{{ p.category?.name }}</span></td>
              <td class="text-success font-weight-bold">{{ formatPrice(p.price) }}đ</td>
              <td class="text-center">
                <span class="badge" :class="p.is_available ? 'badge-success' : 'badge-danger'">
                  {{ p.is_available ? 'Đang bán' : 'Hết hàng' }}
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-info btn-xs mr-1" @click="openEditModal(p)"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-xs" @click="destroyProduct(p.id)"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL FORM -->
    <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white py-2">
            <h5 class="modal-title font-weight-bold">{{ isEditing ? 'Sửa Món Nước' : 'Thêm Món Nước' }}</h5>
            <button type="button" class="close text-white" @click="showModal = false">&times;</button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="form-group">
                <label>Tên món nước <span class="text-danger">*</span></label>
                <input v-model="form.name" type="text" class="form-control form-control-sm" required />
              </div>
              <div class="form-group">
                <label>Danh mục <span class="text-danger">*</span></label>
                <select v-model="form.category_id" class="form-control form-control-sm" required>
                  <option value="">-- Chọn danh mục --</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>Giá bán (VNĐ) <span class="text-danger">*</span></label>
                <input v-model="form.price" type="number" step="1000" class="form-control form-control-sm" required />
              </div>
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input v-model="form.is_available" type="checkbox" class="custom-control-input" id="availableSwitch">
                  <label class="custom-control-label" for="availableSwitch">Đang phục vụ (Còn hàng)</label>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between py-2">
              <button type="button" class="btn btn-default btn-sm" @click="showModal = false">Hủy</button>
              <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">Lưu lại</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ products: Array, categories: Array });
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
  id: null,
  name: '',
  category_id: '',
  price: 0,
  is_available: true,
});

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val);

const openCreateModal = () => {
  isEditing.value = false;
  form.reset();
  form.is_available = true;
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  form.id = product.id;
  form.name = product.name;
  form.category_id = product.category_id;
  form.price = product.price;
  form.is_available = Boolean(product.is_available);
  showModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/mon-nuoc/${form.id}`, { onSuccess: () => showModal.value = false });
  } else {
    form.post('/mon-nuoc', { onSuccess: () => showModal.value = false });
  }
};

const destroyProduct = (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa món này?')) {
    useForm({}).delete(`/mon-nuoc/${id}`);
  }
};
</script>
