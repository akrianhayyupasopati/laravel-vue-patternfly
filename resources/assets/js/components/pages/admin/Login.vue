<template>
  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <div id="brand">
          <h1><strong>PATTERNFLY ENTERPRISE</strong></h1>
        </div><!--/#brand-->
      </div><!--/.col-*-->
      <div class="col-sm-7 col-md-6 col-lg-5 login">
        <form class="form-horizontal" role="form" @submit.prevent="login">
          <div class="form-group">
            <label for="email" class="col-sm-2 col-md-2 control-label">Email</label>
            <div class="col-sm-10 col-md-10">
              <input type="email" class="form-control" name="email" placeholder="username@example.com" tabindex="1" v-model="email">
            </div>
          </div>          
          <div class="form-group">
            <label for="password" class="col-sm-2 col-md-2 control-label">Password</label>
            <div class="col-sm-10 col-md-10">
              <input type="password" class="form-control" id="password" name="password" placeholder="" tabindex="2" v-model="password">
            </div>            
          </div>
          <div class="form-group">
            <div class="col-xs-8 col-sm-offset-2 col-sm-6 col-md-offset-2 col-md-6">
              <div class="checkbox">
                <label>
                  <input type="checkbox" tabindex="3" name="remember" v-model="remember"> Remember username
                </label>
              </div>
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4 submit">
              <button type="submit" class="btn btn-primary btn-lg" tabindex="4"><span class="spinner spinner-xs spinner-inline" v-if="loading"></span> Log In</button>
            </div>
          </div>
        </form>
      </div><!--/.col-*-->
      <div class="col-sm-5 col-md-6 col-lg-7 details">
        <p><strong>Welcome to Laravel-Vue-PatternFly demo application!</strong></p>
        <p>Please log in to access application.</p>
        <p>Default login as Developer, email: <strong>developer@developer.com</strong> password: <strong>p@ssw0rd</strong></p>
        <p>Default login as Super Administrator, email: <strong>superadmin@admin.com</strong> password: <strong>superadmin</strong></p>
        <p class="small">copyright &copy; 2017</p>
      </div><!--/.col-*-->
    </div><!--/.row-->
  </div><!--/.container-->
</template>
<script>
export default {
  data() {
    return {
      email: null,
      password: null,
      loading: false,
      remember: true
    };
  },
  mounted() {
    var tag = document.getElementsByTagName("html")[0];
    tag.classList.add("login-pf");
  },
  methods: {
    login() {
      var app = this;
      app.loading = true;
      this.$auth.login({
        params: {
          email: app.email,
          password: app.password
        },
        success: function(success) {
          this.$root.$refs.notif.add(
            "<strong>Login accepted</strong>",
            "success"
          );
          app.loading = false;
          var tag = document.getElementsByTagName("html")[0];
          tag.classList.remove("login-pf");
        },
        error: function(error) {
          app.loading = false;
          this.$root.$refs.notif.add(
            "<strong>Login failed</strong>, " + error.response.data.msg,
            "danger"
          );
        },
        rememberMe: app.remember,
        redirect: "/dashboard",
        fetchUser: true
      });
    }
  }
};
</script>