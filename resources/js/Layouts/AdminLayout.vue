<template>
  <div class="wrapper">
    <!-- Navbar Top -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="#" role="button" @click.prevent="togglePushMenu">
            <i class="fas fa-bars"></i>
          </a>
        </li>
      </ul>

      <ul class="navbar-nav ml-auto align-items-center">
        <!-- TOGGLE TỰ ĐỘNG IN BILL KẾ BÊN AVATAR -->
        <li class="nav-item mr-3">
          <div class="custom-control custom-switch d-flex align-items-center">
            <input
              type="checkbox"
              class="custom-control-input"
              id="globalAutoPrintSwitch"
              v-model="autoPrint"
              @change="toggleAutoPrint"
            >
            <label class="custom-control-label font-weight-bold text-sm cursor-pointer select-none" for="globalAutoPrintSwitch">
              <i class="fas fa-print mr-1" :class="autoPrint ? 'text-success' : 'text-muted'"></i>
              Tự động in {{ autoPrint ? '(BẬT)' : '(TẮT)' }}
            </label>
          </div>
        </li>

        <!-- AVATAR & DROPDOWN TÀI KHOẢN -->
        <li class="nav-item dropdown user-menu" ref="userMenuRef">
          <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" @click.prevent="toggleUserMenu">
            <img :src="user?.avatar || '/images/avatar-default.jpg'" class="user-image img-circle elevation-1 mr-2 mt-0" alt="User Image" style="width: 23px; height: 23px;">
          </a>

          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" :class="{ show: showUserMenu }" style="left: auto; right: 0;">
            <div class="dropdown-item bg-light py-3 border-bottom">
                <div class="d-flex gap-3 align-items-center">
                    <img :src="user?.avatar || '/images/avatar-default.jpg'" class="img-circle elevation-2 mb-2" style="width: 50px; height: 50px; object-fit: cover;" alt="User Image">
                    <div class="w-100">
                        <p class="mb-0 font-weight-bold">{{ user?.name }}</p>
                        <small class="text-muted d-block">{{ user?.email }}</small>
                        <small v-if="user?.position?.name" class="badge badge-info mt-1">
                            {{ user?.position?.name }}
                        </small>
                    </div>
                </div>
            </div>

            <a href="#" class="dropdown-item py-2" @click.prevent="openProfileModal">
              <i class="fas fa-user-cog mr-2"></i> Thay đổi thông tin tài khoản
            </a>

            <div class="dropdown-divider"></div>

            <a href="#" class="dropdown-item py-2 text-danger" @click.prevent="logout">
              <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
            </a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <Link href="/dashboard" class="brand-link mb-1">
        <img :src="'/images/coucou.jpg'" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8; object-fit: cover; height: 28px; width: 28px; margin-left: .5rem;">
        <span class="brand-text font-weight-light">CAFE COUCOU</span>
      </Link>

      <div class="sidebar pb-4">
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" role="menu" data-accordion="false">
            <li class="nav-item">
              <Link href="/dashboard" class="nav-link" :class="{ active: $page.url.startsWith('/dashboard') }">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/pos" class="nav-link" :class="{ active: $page.url.startsWith('/pos') }">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Đặt Món</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/don-hang" class="nav-link" :class="{ active: $page.url.startsWith('/don-hang') }">
                <i class="nav-icon fas fa-file-invoice-dollar"></i>
                <p>Quản Lý Đơn Hàng</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/quan-ly-ban" class="nav-link" :class="{ active: $page.url.startsWith('/quan-ly-ban') }">
                <i class="nav-icon fas fa-th"></i>
                <p>Quản Lý Bàn</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/mon-nuoc" class="nav-link" :class="{ active: $page.url.startsWith('/mon-nuoc') }">
                <i class="nav-icon fas fa-mug-hot"></i>
                <p>Quản Lý Món Nước</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/danh-muc" class="nav-link" :class="{ active: $page.url.startsWith('/danh-muc') }">
                <i class="nav-icon fas fa-list"></i>
                <p>Danh Mục Món</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link href="/nguoi-dung" class="nav-link" :class="{ active: $page.url.startsWith('/nguoi-dung') }">
                <i class="nav-icon fas fa-users"></i>
                <p>Người Dùng</p>
              </Link>
            </li>
          </ul>
        </nav>
      </div>
    </aside>

    <div id="sidebar-overlay" @click="closeMobileSidebar"></div>

    <div class="content-wrapper p-3">
      <slot />
    </div>

    <!-- HÓA ĐƠN BILL DÙNG CHUNG CHO CẢ HỆ THỐNG -->
    <ReceiptBill :order="autoPrintOrder" />

    <!-- MODAL THAY ĐỔI THÔNG TIN TÀI KHOẢN -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true" :class="{ show: showProfileModal, 'd-block': showProfileModal }">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Thay Đổi Thông Tin Tài Khoản</h5>
            <button type="button" class="close text-white" @click="closeProfileModal">
              <span>&times;</span>
            </button>
          </div>

          <form @submit.prevent="updateProfile">
            <div class="modal-body">
              <div class="form-group">
                <label>Họ và Tên <span class="text-danger">*</span></label>
                <input v-model="profileForm.name" type="text" class="form-control" :class="{ 'is-invalid': profileForm.errors.name }" required />
                <div class="invalid-feedback" v-if="profileForm.errors.name">{{ profileForm.errors.name }}</div>
              </div>

              <div class="form-group">
                <label>Địa chỉ Email <span class="text-danger">*</span></label>
                <input v-model="profileForm.email" type="email" class="form-control" :class="{ 'is-invalid': profileForm.errors.email }" required />
                <div class="invalid-feedback" v-if="profileForm.errors.email">{{ profileForm.errors.email }}</div>
              </div>

              <hr />

              <p class="text-muted text-sm">Để trống nếu không muốn đổi mật khẩu:</p>

              <div class="form-group">
                <label>Mật khẩu mới</label>
                <input v-model="profileForm.password" type="password" class="form-control" :class="{ 'is-invalid': profileForm.errors.password }" placeholder="Nhập mật khẩu mới..." />
                <div class="invalid-feedback" v-if="profileForm.errors.password">{{ profileForm.errors.password }}</div>
              </div>

              <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <input v-model="profileForm.password_confirmation" type="password" class="form-control" placeholder="Xác nhận mật khẩu..." />
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeProfileModal">Hủy bỏ</button>
              <button type="submit" class="btn btn-primary" :disabled="profileForm.processing">
                <i class="fas fa-save mr-1"></i> {{ profileForm.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-if="showProfileModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import ReceiptBill from '@/Components/ReceiptBill.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const autoPrint = ref(false);
const autoPrintOrder = ref(null);
const isProcessingOrder = ref(false); // Khóa chống lặp lệnh in
let printInterval = null;

// Bật/Tắt công tắc tự động in
const toggleAutoPrint = () => {
  localStorage.setItem('pos_auto_print', autoPrint.value);

  if (autoPrint.value) {
    localStorage.setItem('pos_auto_print_enabled_at', new Date().toISOString());
    startListeningOrders();
  } else {
    stopListeningOrders();
  }
};

// Hàm tự động kiểm tra đơn mới
const checkNewOrders = () => {
  // Nếu công tắc TẮT hoặc hệ thống đang xử lý 1 đơn khác -> Bỏ qua
  if (localStorage.getItem('pos_auto_print') !== 'true' || isProcessingOrder.value) return;

  const enabledAt = localStorage.getItem('pos_auto_print_enabled_at');

  axios.get('/api/check-new-orders', {
    params: { enabled_at: enabledAt }
  }).then(response => {
    if (response.data.has_new_order) {
      // 1. Khóa lại ngay lập tức và tạm dừng Polling
      isProcessingOrder.value = true;
      stopListeningOrders();

      // 2. Gán dữ liệu đơn hàng
      autoPrintOrder.value = response.data.order;

      // 3. Thực hiện in
      nextTick(() => {
        setTimeout(() => {
          // Lệnh window.print() sẽ chặn luồng JS cho đến khi người dùng tắt/in xong
          window.print();

          // 4. Giải phóng sau khi đã bấm In hoặc Hủy (Chắc chắn thoát vòng lặp)
          setTimeout(() => {
            autoPrintOrder.value = null;
            isProcessingOrder.value = false;

            // Mở lại bộ lắng nghe nếu công tắc vẫn BẬT
            if (localStorage.getItem('pos_auto_print') === 'true') {
              startListeningOrders();
            }
          }, 1500); // Trễ 1.5s an toàn trước khi quét đơn tiếp theo
        }, 300);
      });
    }
  }).catch(err => {
    console.error("Lỗi kiểm tra đơn tự động:", err);
    isProcessingOrder.value = false;
  });
};

const startListeningOrders = () => {
  if (!printInterval && !isProcessingOrder.value) {
    checkNewOrders();
    printInterval = setInterval(checkNewOrders, 3000);
  }
};

const stopListeningOrders = () => {
  if (printInterval) {
    clearInterval(printInterval);
    printInterval = null;
  }
};

const closeMobileSidebar = () => {
  document.body.classList.remove('sidebar-open');
};

const togglePushMenu = () => {
  const isMobile = window.innerWidth < 992;
  if (isMobile) {
    document.body.classList.toggle('sidebar-open');
  } else {
    document.body.classList.toggle('sidebar-collapse');
  }
};

const showNotifMenu = ref(false);
const showUserMenu = ref(false);

const notifMenuRef = ref(null);
const userMenuRef = ref(null);

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
  if (showUserMenu.value) showNotifMenu.value = false;
};

const handleClickOutside = (event) => {
  if (notifMenuRef.value && !notifMenuRef.value.contains(event.target)) {
    showNotifMenu.value = false;
  }
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    showUserMenu.value = false;
  }
};

let unregisterNavigate = null;

onMounted(() => {
  const savedAutoPrint = localStorage.getItem('pos_auto_print');
  if (savedAutoPrint !== null) {
    autoPrint.value = savedAutoPrint === 'true';
  }

  if (autoPrint.value) {
    startListeningOrders();
  }

  document.addEventListener('click', handleClickOutside);

  unregisterNavigate = router.on('navigate', () => {
    closeMobileSidebar();
  });
});

onUnmounted(() => {
  stopListeningOrders();
  document.removeEventListener('click', handleClickOutside);
  if (unregisterNavigate) unregisterNavigate();
});

const showProfileModal = ref(false);

const profileForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const openProfileModal = () => {
  showUserMenu.value = false;
  profileForm.name = user.value?.name || '';
  profileForm.email = user.value?.email || '';
  profileForm.password = '';
  profileForm.password_confirmation = '';
  profileForm.clearErrors();
  showProfileModal.value = true;
};

const closeProfileModal = () => {
  showProfileModal.value = false;
};

const updateProfile = () => {
  profileForm.put('/profile', {
    preserveScroll: true,
    onSuccess: () => {
      closeProfileModal();
      alert('Cập nhật thông tin tài khoản thành công!');
    },
  });
};

const logout = () => {
  if (confirm('Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?')) {
    router.post('/logout');
  }
};
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
.select-none {
  user-select: none;
}
</style>
