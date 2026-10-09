<template>
  <AdminLayout>
    <Head title="Danh Sách Người Dùng & Phân Quyền" />
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header border-0 py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="input-group input-group-sm" style="width: 220px;">
              <input v-model="search" type="text" class="form-control" placeholder="Tìm tên, username, email..." />
              <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
              </div>
            </div>
          </div>

          <button class="btn btn-primary btn-sm" @click="openCreateModal">
            <i class="fas fa-plus mr-1"></i> Thêm người dùng
          </button>
        </div>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped text-nowrap align-middle m-0">
          <thead>
            <tr>
              <th width="50" class="text-center">STT</th>
              <th>Họ và Tên</th>
              <th>Tên Đăng Nhập</th>
              <th>Email</th>
              <th width="100" class="text-center">Vai Trò</th>
              <th width="90" class="text-center pr-4">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(u, index) in filteredUsers" :key="u.id">
              <td class="text-center text-muted">{{ index + 1 }}</td>
              <td class="font-weight-bold">{{ u.name }}</td>
              <td>{{ u.username }}</td>
              <td>{{ u.email }}</td>
              <td class="text-center">
                <span class="badge" :class="u.role === 'admin' ? 'badge-danger' : 'badge-info'">
                  {{ u.role === 'admin' ? 'Quản trị' : 'Người dùng' }}
                </span>
              </td>
              <td class="text-center pr-4">
                <button class="btn btn-info btn-xs mr-1" title="Chỉnh sửa" @click="openEditModal(u)">
                  <i class="fas fa-user-shield"></i>
                </button>
                <button class="btn btn-danger btn-xs" title="Xóa" @click="openDeleteModal(u)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL THÊM / SỬA NGƯỜI DÙNG & PHÂN QUYỀN CHI TIẾT -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white py-2">
            <h5 class="modal-title font-weight-bold text-white">
              {{ isEditing ? 'Cập Nhật Tài Khoản' : 'Thêm Mới Tài Khoản' }}
            </h5>
            <button type="button" class="close text-white" @click="closeModal">&times;</button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body">
                    <div class="form-group">
                        <label>Tên đăng nhập<span class="text-danger">*</span></label>
                        <input v-model="form.username" type="text" class="form-control form-control-sm" placeholder="VD: nguyenvana" :class="{ 'is-invalid': form.errors.username }" />
                        <div v-if="form.errors.username" class="invalid-feedback">{{ form.errors.username }}</div>
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu <small v-if="isEditing" class="text-muted font-italic">(Để trống nếu không đổi)</small></label>
                        <input v-model="form.password" type="password" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors.password }" />

                        <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                    </div>
                  <div class="form-group">
                    <label>Họ và Tên <span class="text-danger">*</span></label>
                    <input v-model="form.name" type="text" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors.name }" />
                    <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
                  </div>

                  <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input v-model="form.email" type="email" class="form-control form-control-sm" :class="{ 'is-invalid': form.errors.email }" />
                    <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                  </div>

                  <div class="form-group">
                    <label>Vai Trò</label>
                    <select v-model="form.role" class="form-control form-control-sm">
                      <option value="user">Người dùng</option>
                      <option value="admin">Quản trị viên</option>
                    </select>
                  </div>
            </div>

            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default btn-sm" @click="closeModal">Hủy</button>
              <button type="submit" class="btn btn-primary btn-sm" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Lưu cập nhật' : 'Thêm mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL XÁC NHẬN XÓA -->
    <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title font-weight-bold">Xác Nhận Xóa</h5>
            <button type="button" class="close" @click="showDeleteModal = false">&times;</button>
          </div>
          <div class="modal-body text-center">
            <p class="m-0">Bạn có chắc muốn xóa tài khoản<br><strong>{{ selectedItem?.name }}</strong>?</p>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default btn-sm" @click="showDeleteModal = false">Hủy</button>
            <button type="button" class="btn btn-danger btn-sm" :disabled="deleteForm.processing" @click="confirmDelete">Xóa ngay</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  users: Array,
});

const search = ref('');
const departmentFilter = ref('');
const positionFilter = ref('');

const filteredUsers = computed(() => {
  return props.users.filter(u => {
    const keyword = search.value.toLowerCase().trim();
    const matchesSearch = !keyword ||
      u.name.toLowerCase().includes(keyword) ||
      (u.username && u.username.toLowerCase().includes(keyword)) ||
      u.email.toLowerCase().includes(keyword);
    const matchesDept = !departmentFilter.value || u.department_id == departmentFilter.value;
    const matchesPos = !positionFilter.value || u.position_id == positionFilter.value;
    return matchesSearch && matchesDept && matchesPos;
  });
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedItem = ref(null);

const form = useForm({
  id: null,
  name: '',
  username: '',
  email: '',
  password: '',
  department_id: '',
  position_id: '',
  role: 'user',
  permissions: [],
});

const deleteForm = useForm({});

const openCreateModal = () => {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  showModal.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  form.clearErrors();
  form.id = user.id;
  form.name = user.name;
  form.username = user.username || '';
  form.email = user.email;
  form.password = '';
  form.department_id = user.department_id || '';
  form.position_id = user.position_id || '';
  form.role = user.role || 'user';
  form.permissions = user.direct_permissions ? user.direct_permissions.map(p => p.id) : [];
  showModal.value = true;
};

const openDeleteModal = (user) => {
  selectedItem.value = user;
  showDeleteModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/nguoi-dung/${form.id}`, { onSuccess: () => closeModal() });
  } else {
    form.post('/nguoi-dung', { onSuccess: () => closeModal() });
  }
};

const confirmDelete = () => {
  if (!selectedItem.value) return;
  deleteForm.delete(`/nguoi-dung/${selectedItem.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      selectedItem.value = null;
    }
  });
};

</script>
