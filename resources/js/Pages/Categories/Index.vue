<template>
  <AdminLayout>
    <Head title="Quản Lý Danh Mục" />
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header ">
        <div class="py-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">Danh Sách Danh Mục Món</h3>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus mr-1"></i> Thêm danh mục
            </button>
        </div>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle m-0">
          <thead>
            <tr>
              <th width="60" class="text-center">STT</th>
              <th>Tên Danh Mục</th>
              <th class="text-center">Số Lượng Món</th>
              <th width="120" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cat, index) in categories" :key="cat.id">
              <td class="text-center text-muted">{{ index + 1 }}</td>
              <td class="font-weight-bold">{{ cat.name }}</td>
              <td class="text-center">
                <span class="badge badge-info">{{ cat.products_count || 0 }} món</span>
              </td>
              <td class="text-center">
                <button class="btn btn-info btn-xs mr-1" @click="openEditModal(cat)"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-xs" @click="destroyCategory(cat.id)"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL FORM -->
    <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white py-2">
            <h5 class="modal-title font-weight-bold">{{ isEditing ? 'Sửa Danh Mục' : 'Thêm Danh Mục' }}</h5>
            <button type="button" class="close text-white" @click="showModal = false">&times;</button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="form-group mb-0">
                <label>Tên danh mục <span class="text-danger">*</span></label>
                <input v-model="form.name" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors.name }" placeholder="VD: Cà Phê, Trà Sữa..." required />
                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
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

defineProps({ categories: Array });
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
  id: null,
  name: '',
});

const openCreateModal = () => {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  showModal.value = true;
};

const openEditModal = (cat) => {
  isEditing.value = true;
  form.clearErrors();
  form.id = cat.id;
  form.name = cat.name;
  showModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/danh-muc/${form.id}`, { onSuccess: () => showModal.value = false });
  } else {
    form.post('/danh-muc', { onSuccess: () => showModal.value = false });
  }
};

const destroyCategory = (id) => {
  if (confirm('Bạn có chắc muốn xóa danh mục này?')) {
    useForm({}).delete(`/danh-muc/${id}`);
  }
};
</script>
