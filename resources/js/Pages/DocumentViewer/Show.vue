<template>
  <div class="vh-100 d-flex flex-column bg-dark position-relative overflow-hidden">
    <!-- Header Thanh Công Cụ Xem File -->
    <div class="bg-secondary text-white px-3 py-2 d-flex justify-content-between align-items-center shadow-sm" style="z-index: 100;">
      <div class="d-flex align-items-center text-truncate mr-3">
        <i class="fas fa-file-alt fa-lg text-warning mr-3"></i>
        <div class="text-truncate">
          <h6 class="m-0 font-weight-bold text-truncate" style="max-width: 500px;">
            {{ file.file_name }}
          </h6>
          <small class="text-light opacity-75" v-if="file.document_code">
            Mã VB: <strong>{{ file.document_code }}</strong>
          </small>
        </div>
      </div>

      <!-- Cụm Nút Thao Tác Trên Thanh ToolBar -->
      <div class="d-flex align-items-center gap-2">
        <!-- Công cụ riêng cho Hình Ảnh -->
        <div v-if="isImage" class="btn-group mr-2">
          <button class="btn btn-outline-light btn-sm" @click="zoomIn" title="Phóng to"><i class="fas fa-search-plus"></i></button>
          <button class="btn btn-outline-light btn-sm" @click="zoomOut" title="Thu nhỏ"><i class="fas fa-search-minus"></i></button>
          <button class="btn btn-outline-light btn-sm" @click="rotate" title="Xoay hình"><i class="fas fa-redo"></i></button>
          <button class="btn btn-outline-light btn-sm" @click="resetImage" title="Đặt lại"><i class="fas fa-sync-alt"></i></button>
        </div>

        <!-- Bật/Tắt Sidebar Trích Yếu -->
        <button
          class="btn btn-sm"
          :class="showSidebar ? 'btn-warning' : 'btn-outline-light'"
          @click="showSidebar = !showSidebar"
          title="Thông tin văn bản"
        >
          <i class="fas fa-info-circle mr-1"></i> Thông tin
        </button>

        <!-- <button class="btn btn-outline-light btn-sm" @click="printFile" title="In văn bản">
          <i class="fas fa-print mr-1"></i> In
        </button>

        <button class="btn btn-outline-light btn-sm" @click="copyLink" title="Sao chép liên kết">
          <i class="fas fa-link"></i>
        </button> -->

        <!-- Toàn màn hình -->
        <button class="btn btn-outline-light btn-sm" @click="toggleFullscreen" title="Toàn màn hình">
          <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
        </button>

        <!-- Tải về máy -->
        <a :href="file.url" download class="btn btn-success btn-sm">
          <i class="fas fa-download mr-1"></i> Tải về
        </a>

        <!-- Đóng Tab -->
        <button class="btn btn-danger btn-sm ml-2" @click="closeTab">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Màn hình chính bao gồm Khung hiển thị & Sidebar -->
    <div class="flex-grow-1 d-flex overflow-hidden position-relative">

      <!-- Khung Hiển Thị Nội Dung File -->
      <div class="flex-grow-1 bg-secondary overflow-auto d-flex justify-content-center align-items-center p-3">

        <!-- Loading -->
        <div v-if="loading && !hasError" class="text-white text-center position-absolute" style="z-index: 10;">
          <div class="spinner-border text-light mb-2" role="status"></div>
          <div>Đang nạp dữ liệu tệp...</div>
        </div>

        <!-- Báo Lỗi Nạp File -->
        <div v-if="hasError" class="text-center text-white py-5">
          <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
          <h5>Không thể tải nội dung tệp này!</h5>
          <p class="text-muted">Tệp có thể bị hỏng, bị xóa hoặc không hỗ trợ định dạng này.</p>
          <a :href="file.url" download class="btn btn-outline-light btn-sm mt-2">Tải về thử lại</a>
        </div>

        <!-- 1. Word -->
        <VueOfficeDocx
          v-if="['docx'].includes(file.extension) && !hasError"
          :src="file.url"
          class="w-100 h-100 bg-white rounded shadow"
          @rendered="loading = false"
          @error="onRenderError"
        />

        <!-- 2. Excel -->
        <VueOfficeExcel
          v-else-if="['xlsx', 'xls'].includes(file.extension) && !hasError"
          :src="file.url"
          class="w-100 h-100 bg-white rounded shadow"
          @rendered="loading = false"
          @error="onRenderError"
        />

        <!-- 3. PDF -->
        <VueOfficePdf
          v-else-if="file.extension === 'pdf' && !hasError"
          :src="file.url"
          class="w-100 h-100 bg-white rounded shadow"
          @rendered="loading = false"
          @error="onRenderError"
        />

        <!-- 4. Hình Ảnh (Hỗ trợ Scale & Rotate) -->
        <div v-else-if="isImage && !hasError" class="text-center overflow-auto w-100 h-100 d-flex justify-content-center align-items-center">
          <img
            :src="file.url"
            class="img-fluid rounded shadow transition-all"
            :style="{
              transform: `scale(${imageScale}) rotate(${imageRotation}deg)`,
              maxHeight: imageScale === 1 ? '85vh' : 'none',
              transition: 'transform 0.2s ease-in-out'
            }"
            @load="loading = false"
            @error="onRenderError"
          />
        </div>

        <!-- 5. Tệp Không Hỗ Trợ -->
        <div v-else-if="!hasError" class="text-center text-white py-5">
          <i class="fas fa-file-download fa-4x mb-3 text-warning"></i>
          <h5>Định dạng (.{{ file.extension }}) chưa hỗ trợ xem trực tuyến.</h5>
          <a :href="file.url" download class="btn btn-primary mt-2">Tải tệp về máy</a>
        </div>
      </div>

      <!-- Sidebar Thông tin Văn bản (Slide Drawer) -->
      <div
        v-if="showSidebar"
        class="bg-white border-left p-3 shadow-lg overflow-auto"
        style="width: 320px; min-width: 320px; z-index: 90;"
      >
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
          <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-1"></i> Thông Tin Chi Tiết</h6>
          <button class="close" @click="showSidebar = false">&times;</button>
        </div>

        <div class="mb-3">
          <label class="text-muted small m-0">Tên tệp đính kèm:</label>
          <div class="font-weight-bold text-break">{{ file.file_name }}</div>
        </div>

        <div class="mb-3" v-if="file.document_code">
          <label class="text-muted small m-0">Mã văn bản:</label>
          <div class="font-weight-bold text-primary">{{ file.document_code }}</div>
        </div>

        <div class="mb-3" v-if="file.summary">
          <label class="text-muted small m-0">Trích yếu nội dung:</label>
          <div class="bg-light p-2 rounded text-dark small" style="white-space: pre-line;">
            {{ file.summary }}
          </div>
        </div>

        <div class="mb-3">
          <label class="text-muted small m-0">Định dạng tệp:</label>
          <div><span class="badge badge-info text-uppercase">{{ file.extension }}</span></div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import VueOfficeDocx from '@vue-office/docx';
import VueOfficeExcel from '@vue-office/excel';
import VueOfficePdf from '@vue-office/pdf';

import '@vue-office/docx/lib/index.css';
import '@vue-office/excel/lib/index.css';

const props = defineProps({
  file: Object
});

// States
const loading = ref(true);
const hasError = ref(false);
const showSidebar = ref(false);
const isFullscreen = ref(false);

// Image manipulation states
const imageScale = ref(1);
const imageRotation = ref(0);

const isImage = computed(() => {
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(props.file.extension?.toLowerCase());
});

// Event Handlers
const onRenderError = () => {
  loading.value = false;
  hasError.value = true;
};

const zoomIn = () => { imageScale.value = Math.min(imageScale.value + 0.25, 3); };
const zoomOut = () => { imageScale.value = Math.max(imageScale.value - 0.25, 0.5); };
const rotate = () => { imageRotation.value = (imageRotation.value + 90) % 360; };
const resetImage = () => { imageScale.value = 1; imageRotation.value = 0; };

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
    isFullscreen.value = true;
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
      isFullscreen.value = false;
    }
  }
};

const copyLink = () => {
  navigator.clipboard.writeText(window.location.href);
  alert('Đã sao chép đường dẫn xem tệp!');
};

const printFile = () => {
  if (isImage.value || props.file.extension === 'pdf') {
    const printWin = window.open(props.file.url, '_blank');
    printWin.focus();
    printWin.print();
  } else {
    alert('Tính năng in nhanh hiện hỗ trợ file PDF và Hình ảnh. Với Word/Excel vui lòng tải về để in.');
  }
};

const closeTab = () => {
  window.close();
};
</script>
