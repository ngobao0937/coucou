<template>
  <Head title="Đăng nhập" />
  <div class="login-page">
    <div class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <h3>CUCU</h3>
        </div>
        <div class="card-body">
          <p class="login-box-msg">Đăng nhập tài khoản</p>
          <form @submit.prevent="submit">
            <!-- Đổi Email thành Username -->
            <div class="input-group mb-3">
              <input
                v-model="form.username"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.username }"
                placeholder="Tên đăng nhập"
                required
                autofocus
              />
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-user"></span></div>
              </div>
            </div>
            <div v-if="form.errors.username" class="text-danger small mb-2">{{ form.errors.username }}</div>

            <div class="input-group mb-3">
              <input
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': form.errors.password }"
                placeholder="Mật khẩu"
                required
              />
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
              </div>
            </div>
            <div v-if="form.errors.password" class="text-danger small mb-2">{{ form.errors.password }}</div>

            <button type="submit" class="btn btn-primary btn-block" :disabled="form.processing">
              <i v-if="form.processing" class="fas fa-spinner fa-spin mr-1"></i>
              Đăng Nhập
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
  username: '',
  password: '',
});

const submit = () => {
  form.post('/login');
};
</script>

<style scoped>
.login-page {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #e9ecef;
}
</style>
