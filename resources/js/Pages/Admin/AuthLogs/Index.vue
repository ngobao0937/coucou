<template>
  <AdminLayout>
    <Head title="Lịch Sử Đăng Nhập / Đăng Xuất" />
    <div class="content-header p-0 mb-3">
      <div class="container-fluid p-0">
        <span class="text-uppercase text-muted text-xs font-weight-bold">Bảo mật hệ thống</span>
        <h4 class="m-0 font-weight-bold text-dark">Lịch Sử Đăng Nhập / Đăng Xuất</h4>
        <small class="text-muted"><i class="fas fa-user-shield mr-1"></i>Theo dõi chi tiết IP, thiết bị và thời gian truy cập của cán bộ xã</small>
      </div>
    </div>

    <!-- Log Table -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header py-2">
        <div class="row align-items-center">
          <div class="col-md-9 my-1">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control form-control-sm"
              placeholder="Tìm theo tên cán bộ, email, IP..."
              @keyup.enter="handleFilter"
            />
          </div>
          <div class="col-md-2 my-1">
            <select v-model="selectedType" class="form-control form-control-sm" @change="handleFilter">
              <option value="">-- Tất cả trạng thái --</option>
              <option value="login_success">Đăng nhập thành công</option>
              <option value="logout">Đăng xuất</option>
              <option value="login_failed">Đăng nhập thất bại</option>
            </select>
          </div>
          <div class="col-md-1 my-1">
            <button class="btn btn-sm btn-primary w-100 font-weight-bold" @click="handleFilter">
              <i class="fas fa-search mr-1"></i>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light text-uppercase text-sm">
            <tr>
              <th style="width: 170px">Thời gian</th>
              <th>Tài khoản</th>
              <th>Hành động</th>
              <th width="160">Địa chỉ IP</th>
              <th>Thiết bị & Trình duyệt</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs.data" :key="log.id">
              <td class="text-monospace font-weight-bold text-muted text-center">
                {{ formatDate(log.created_at) }}
              </td>
              <td>
                <div v-if="log.causer" class="font-weight-bold text-dark">{{ log.causer.name }}</div>
                <div v-else class="text-danger font-weight-bold">
                  {{ log.properties?.attempted_email || 'Không xác định' }}
                </div>
                <small class="text-muted">{{ log.causer?.email || 'N/A' }}</small>
              </td>
              <td>
                <span class="badge" :class="getBadgeClass(log.properties?.event_type)">
                  {{ log.description }}
                </span>
              </td>
              <td class="text-monospace font-weight-bold text-center">
                <i class="fas fa-network-wired text-muted mr-1"></i>{{ log.properties?.ip || 'N/A' }}
              </td>
              <td class="text-muted" style="max-width: 250px" :title="log.properties?.user_agent">
                {{ log.properties?.user_agent }}
              </td>
            </tr>
            <tr v-if="logs.data.length === 0">
              <td colspan="5" class="text-center py-4 text-muted">Không ghi nhận lịch sử truy cập nào</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { router , Head } from '@inertiajs/vue3';

const props = defineProps({
  logs: Object,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const selectedType = ref(props.filters?.type || '');

const handleFilter = () => {
  router.get('/auth-logs', {
    search: searchQuery.value,
    type: selectedType.value,
  }, { preserveState: true, preserveScroll: true });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('vi-VN');
};

const getBadgeClass = (type) => {
  switch (type) {
    case 'login_success': return 'badge-success';
    case 'logout': return 'badge-secondary';
    case 'login_failed': return 'badge-danger';
    default: return 'badge-info';
  }
};
</script>
