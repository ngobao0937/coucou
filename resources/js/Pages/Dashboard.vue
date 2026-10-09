<template>
  <AdminLayout>
    <Head title="Dashboard Thống Kê Doanh Số" />

    <!-- CONTENT HEADER & BỘ LỌC THỜI GIAN -->
    <div class="content-header p-0 mb-3">
      <div class="container-fluid p-0 d-flex justify-content-between align-items-center flex-wrap">
        <div>
          <h3 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-chart-line mr-2 text-primary"></i>Tổng Quan Doanh Số
          </h3>
          <small class="text-muted">Báo cáo tình hình kinh doanh của quán</small>
        </div>


      </div>
    </div>
    <!-- BỘ LỌC TÌM KIẾM THEO THỜI GIAN -->
    <div class="card card-outline card-primary mb-3 mt-2 mt-md-0 shadow-sm" style="min-width: 320px;">
        <div class="card-body p-2">
        <form @submit.prevent="applyFilter" class="form-inline d-flex flex-wrap gap-2 justify-content-end">
            <select v-model="filterForm.filter_type" @change="handleTypeChange" class="form-control form-control-sm mr-2 mb-1 mb-sm-0">
            <option value="today">Hôm nay</option>
            <option value="this_week">Tuần này</option>
            <option value="this_month">Tháng này</option>
            <option value="this_year">Năm nay</option>
            <option value="custom">Tùy chọn ngày</option>
            </select>

            <template v-if="filterForm.filter_type === 'custom'">
            <input type="date" v-model="filterForm.from_date" class="form-control form-control-sm mr-1 mb-1 mb-sm-0" />
            <span class="mr-1">-</span>
            <input type="date" v-model="filterForm.to_date" class="form-control form-control-sm mr-2 mb-1 mb-sm-0" />
            </template>

            <button type="submit" class="btn btn-sm btn-primary">
            <i class="fas fa-filter mr-1"></i> Lọc
            </button>
        </form>
        </div>
    </div>
    <div class="container-fluid p-0">
      <!-- 1. CÁC THẺ WIDGET KPI -->
      <div class="row">
        <!-- Tổng Doanh Thu -->
        <div class="col-12 col-sm-6 col-md-3 mb-3">
          <div class="info-box bg-white shadow-sm rounded-lg border-0">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-coins"></i></span>
            <div class="info-box-content">
              <span class="info-box-text text-muted font-weight-bold">TỔNG DOANH THU</span>
              <span class="info-box-number text-success h4 font-weight-bold mb-0">
                {{ formatPrice(kpi.total_revenue) }}đ
              </span>
            </div>
          </div>
        </div>

        <!-- Số Đơn Hàng -->
        <div class="col-12 col-sm-6 col-md-3 mb-3">
          <div class="info-box bg-white shadow-sm rounded-lg border-0">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text text-muted font-weight-bold">SỐ ĐƠN HOÀN THÀNH</span>
              <span class="info-box-number text-info h4 font-weight-bold mb-0">
                {{ formatNumber(kpi.total_orders) }} đơn
              </span>
            </div>
          </div>
        </div>

        <!-- Tổng Số Ly/Sản Phẩm -->
        <div class="col-12 col-sm-6 col-md-3 mb-3">
          <div class="info-box bg-white shadow-sm rounded-lg border-0">
            <span class="info-box-icon bg-warning text-white elevation-1"><i class="fas fa-mug-hot"></i></span>
            <div class="info-box-content">
              <span class="info-box-text text-muted font-weight-bold">TỔNG MÓN ĐÃ BÁN</span>
              <span class="info-box-number text-warning h4 font-weight-bold mb-0">
                {{ formatNumber(kpi.total_items_sold) }} món
              </span>
            </div>
          </div>
        </div>

        <!-- Giá Trị Trung Bình / Đơn -->
        <div class="col-12 col-sm-6 col-md-3 mb-3">
          <div class="info-box bg-white shadow-sm rounded-lg border-0">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-calculator"></i></span>
            <div class="info-box-content">
              <span class="info-box-text text-muted font-weight-bold">GIÁ TRỊ TB / ĐƠN</span>
              <span class="info-box-number text-danger h4 font-weight-bold mb-0">
                {{ formatPrice(kpi.average_order_value) }}đ
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. KHU VỰC BIỂU ĐỒ CHÍNH & TOP SẢN PHẨM -->
      <div class="row">
        <!-- Biểu đồ doanh thu -->
        <div class="col-12 col-lg-8 mb-4">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 pt-3">
              <h5 class="card-title font-weight-bold text-dark">
                <i class="fas fa-chart-area mr-2 text-primary"></i>Biểu Đồ Biến Động Doanh Thu
              </h5>
            </div>
            <div class="card-body">
              <div style="height: 320px; position: relative;">
                <canvas ref="chartCanvas"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Bảng Top Sản Phẩm Bán Chạy -->
        <div class="col-12 col-lg-4 mb-4">
          <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom-0 pt-3">
              <h5 class="card-title font-weight-bold text-dark">
                <i class="fas fa-crown mr-2 text-warning"></i>Top Món Bán Chạy
              </h5>
            </div>
            <div class="card-body p-0 table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted">
                  <tr>
                    <th>Món nước</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-right">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in topProducts" :key="index">
                    <td class="font-weight-bold text-sm">
                      <span class="badge mr-1" :class="index === 0 ? 'badge-warning' : 'badge-secondary'">{{ index + 1 }}</span>
                      {{ item.product?.name || 'Món đã xóa' }}
                    </td>
                    <td class="text-center font-weight-bold text-primary">{{ item.total_quantity }}</td>
                    <td class="text-right font-weight-bold text-success">{{ formatPrice(item.total_sales) }}đ</td>
                  </tr>
                  <tr v-if="topProducts.length === 0">
                    <td colspan="3" class="text-center text-muted py-4">Chưa có dữ liệu trong kỳ</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. BẢNG DANH SÁCH ĐƠN HÀNG GẦN ĐÂY -->
      <div class="row">
        <div class="col-12 mb-4">
          <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header ">
                <div class="bg-white border-bottom-0 py-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-history mr-2 text-info"></i>Đơn Hàng Gần Đây
                    </h5>
                    <Link href="/don-hang" class="btn btn-sm btn-outline-primary rounded-pill">
                        Xem tất cả đơn hàng <i class="fas fa-arrow-right ml-1"></i>
                    </Link>
                </div>

            </div>
            <div class="card-body p-0 table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                  <tr>
                    <th style="min-width: 150px;">Mã Đơn</th>
                    <th style="min-width: 150px;">Bàn</th>
                    <th style="min-width: 150px;">Người Tạo</th>
                    <th style="min-width: 150px;">Tổng Tiền</th>
                    <th style="min-width: 150px;">Trạng Thái</th>
                    <th style="min-width: 150px;">Thời Gian</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="order in recentOrders" :key="order.id">
                    <td class="font-weight-bold text-primary">{{ order.code || ('#ORD-' + order.id) }}</td>
                    <td><span class="badge badge-info">{{ order.table?.name || 'N/A' }}</span></td>
                    <td>{{ order.user?.name || 'Hệ thống' }}</td>
                    <td class="font-weight-bold text-danger">{{ formatPrice(order.total_amount) }}đ</td>
                    <td>
                      <span class="badge" :class="{
                        'badge-warning': order.status === 'pending',
                        'badge-success': order.status === 'completed',
                        'badge-danger': order.status === 'cancelled'
                      }">
                        {{ order.status === 'pending' ? 'Đang xử lý' : (order.status === 'completed' ? 'Hoàn thành' : 'Đã hủy') }}
                      </span>
                    </td>
                    <td class="text-muted text-sm">{{ formatDate(order.created_at) }}</td>
                  </tr>
                  <tr v-if="recentOrders.length === 0">
                    <td colspan="6" class="text-center text-muted py-4">Chưa có đơn hàng nào phát sinh</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  filters: Object,
  kpi: Object,
  chartData: Array,
  topProducts: Array,
  recentOrders: Array
});

// Form bộ lọc
const filterForm = reactive({
  filter_type: props.filters.filter_type || 'this_month',
  from_date: props.filters.from_date || '',
  to_date: props.filters.to_date || '',
});

const chartCanvas = ref(null);
let chartInstance = null;

// Hàm định dạng tiền tệ & số
const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);
const formatNumber = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString('vi-VN');
};

// Xử lý submit bộ lọc
const applyFilter = () => {
  router.get('/dashboard', filterForm, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleTypeChange = () => {
  if (filterForm.filter_type !== 'custom') {
    applyFilter();
  }
};

// Hàm khởi tạo và vẽ Biểu đồ ChartJS
const renderChart = () => {
  if (!chartCanvas.value) return;

  if (chartInstance) {
    chartInstance.destroy();
  }

  const labels = props.chartData.map(item => {
    const d = new Date(item.date);
    return `${d.getDate()}/${d.getMonth() + 1}`;
  });

  const revenues = props.chartData.map(item => item.total_revenue);

  chartInstance = new Chart(chartCanvas.value, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Doanh Thu (VNĐ)',
          data: revenues,
          borderColor: '#007bff',
          backgroundColor: 'rgba(0, 123, 255, 0.1)',
          fill: true,
          tension: 0.3,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#007bff'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return ' Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) + 'đ';
            }
          }
        }
      }
    }
  });
};

onMounted(() => {
  renderChart();
});

watch(() => props.chartData, () => {
  renderChart();
}, { deep: true });
</script>

<style scoped>
.info-box {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-radius: 12px;
}
.info-box-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  margin-right: 1rem;
}
</style>
