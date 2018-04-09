<template>
<div>
    <pf-notifications ref="notif" :toast="true"></pf-notifications>
    <pf-layout :disabled="!$auth.check()" :horizontal="true" :display="display">
        <router-link slot="brand" :to="{ name: 'home' }" :exact="true" class="navbar-brand">
            <span class="navbar-brand-name">P A T T E R N F L Y - <span class="text-small">Enterprise Pattern</span></span>
        </router-link>
        <template slot="utility-menu">
          <router-link :to="{ name: 'login' }" tag="li" active-class="active">
            <a v-if="!$auth.check()">Login</a>
          </router-link>
          <li>
            <a><pf-icon name="fa-bell"/></a>
          </li>
          <li>
            <bs-dropdown type="link" class="dropdown pull-right">
              <template slot="button" type="link">
                <pf-icon name="fa-user"></pf-icon> {{this.$auth.user().name}}
              </template>
              <ul slot="dropdown-menu" class="dropdown-menu">
              <li>
                <a href="#" @click.prevent="logout"><i class="fa fa-power-off"></i> Logout</a>
              </li>
              </ul>
            </bs-dropdown>
          </li>
      </template>
      <template v-if="$auth.check()" slot="horizontal-menu">
        <router-link :to="{ name: 'dashboard' }" tag="li" active-class="active">
          <a><pf-icon name="fa-dashboard"/> Dashboard</a>
          <ul class="nav navbar-nav navbar-persistent">
            <router-link :to="{ name: 'dashboard' }" tag="li" active-class="active">
              <a>Main Dashboard</a>
            </router-link>
          </ul>
        </router-link>
        <router-link :to="{ name: 'admin' }" tag="li" active-class="active" v-if="this.can('users-list')">
          <a><pf-icon name="pficon-key"/> Admin</a>
          <ul class="nav navbar-nav navbar-persistent">
            <router-link :to="{ name: 'users' }" tag="li" active-class="active" v-if="this.can('users-list')">
              <a>Users</a>
            </router-link>
            <router-link :to="{ name: 'roles' }" tag="li" active-class="active" v-if="this.can('roles-list')">
              <a>Roles</a>
            </router-link>
            <router-link :to="{ name: 'permissions' }" tag="li" active-class="active" v-if="this.can('permissions-list')">
              <a>Permissions</a>
            </router-link>
          </ul>
        </router-link>
    </template>
    <router-view></router-view>
</pf-layout>
</div>
</template>
<style lang="css">
@media (min-width: 768px) {
  .navbar-pf .navbar-brand {
    padding: 5px 0 0px;
  }
  .navbar-pf .navbar-utility > li > div > button {
    border-left: 1px solid #2b2b2b;
    color: #d1d1d1 !important;
    text-decoration: none;
  }
  .navbar-pf .navbar-utility > li > div > button:hover {
    border-left: 1px solid #2b2b2b;
    color: #d1d1d1 !important;
    text-decoration: none;
    background-color: #303030;
  }
}
</style>
<script>
import Perm from "./permission.js";
import VueStrap from "vue-strap";
export default {
  mixins: [Perm],
  components: {
    BsDropdown: VueStrap.dropdown
  },
  data() {
    return {
      display: "flex"
    };
  },
  methods: {
    logout() {
      var app = this;
      this.$auth.logout({
        success: function(resp) {
          this.$root.$refs.notif.add(
            "<strong>Successfully log out</strong>",
            "success"
          );
        },
        error: function() {
          this.$root.$refs.notif.add(
            "<strong>Logout failed</strong>, something gone wrong.",
            "danger"
          );
        },
        redirect: "/login"
      });
    }
  }
};
</script>
<style lang="scss">
.navbar-utility-dropdown .btn-link {
  color: lightgray;
}

.navbar-utility-dropdown > :hover {
  color: rgb(30, 223, 213);
}
</style>
