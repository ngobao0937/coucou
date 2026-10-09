<template>
  <AdminLayout>
    <Head title="Quản Lý Danh Sách Bàn" />
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header">
        <div class="py-0 d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">Danh Sách Bàn</h3>
            <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus mr-1"></i> Thêm bàn mới
            </button>
        </div>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap align-middle m-0">
          <thead>
            <tr>
              <th width="60" class="text-center">STT</th>
              <th>Tên Bàn</th>
              <th>Khu Vực</th>
              <th class="text-center">Trạng Thái</th>
              <th width="120" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(t, index) in tables" :key="t.id">
              <td class="text-center text-muted">{{ index + 1 }}</td>
              <td class="font-weight-bold">{{ t.name }}</td>
              <td><span class="badge badge-light border">{{ t.area }}</span></td>
              <td class="text-center">
                <span class="badge" :class="t.status === 'occupied' ? 'badge-warning' : 'badge-success'">
                  {{ t.status === 'occupied' ? 'Đang có khách' : 'Bàn trống' }}
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-info btn-xs mr-1" @click="openEditModal(t)"><i class="fas fa-edit"></i></button>
                <button class="btn btn-danger btn-xs" @click="destroyTable(t.id)"><i class="fas fa-trash"></i></button>
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
            <h5 class="modal-title font-weight-bold">{{ isEditing ? 'Sửa Bàn' : 'Thêm Bàn Mới' }}</h5>
            <button type="button" class="close text-white" @click="showModal = false">&times;</button>
          </div>
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div class="form-group">
                <label>Tên bàn <span class="text-danger">*</span></label>
                <input v-model="form.name" type="text" class="form-control form-control-sm" placeholder="VD: Bàn 01, Bàn VIP..." required />
              </div>
              <div class="form-group mb-0">
                <label>Khu vực <span class="text-danger">*</span></label>
                <input v-model="form.area" type="text" class="form-control form-control-sm" placeholder="VD: Tầng trệt, Lầu 1, Sân thượng..." required />
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

defineProps({ tables: Array });
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
  id: null,
  name: '',
  area: 'Tầng trệt',
});

const openCreateModal = () => {
  isEditing.value = false;
  form.reset();
  form.area = 'Tầng trệt';
  showModal.value = true;
};

const openEditModal = (t) => {
  isEditing.value = true;
  form.id = t.id;
  form.name = t.name;
  form.area = t.area;
  showModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/quan-ly-ban/${form.id}`, { onSuccess: () => showModal.value = false });
  } else {
    form.post('/quan-ly-ban', { onSuccess: () => showModal.value = false });
  }
};

const destroyTable = (id) => {
  if (confirm('Bạn có chắc muốn xóa bàn này?')) {
    useForm({}).delete(`/quan-ly-ban/${id}`);
  }
};
</script>
