<template>
<div>
    <pf-notifications ref="notif" :toast="true"></pf-notifications>
    <pf-layout :disabled="!$auth.check()" :horizontal="true" :display="display">
        <router-link slot="brand" :to="{ name: 'home' }" :exact="true" class="navbar-brand">
            <span class="navbar-brand-name">My Application</span>
        </router-link>
        <template slot="utility-menu">
          <router-link :to="{ name: 'login' }" tag="li" active-class="active">
            <a v-if="!$auth.check()">Login</a>
          </router-link>
          <li class="dropdown" v-if="$auth.check()">
            <a href="#0" class="dropdown-toggle" id="userMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
              <span title="Username" class="fa pficon-user"></span>
              {{ this.$auth.user().name }} <span class="caret"></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="userMenu">
              <li><a href="#" @click.prevent="logout"><i class="fa fa-power-off"></i> Logout</a></li>
            </ul>
          </li>
      </template>
      <template v-if="$auth.check()" slot="horizontal-menu">
        <li v-if="this.can('users-list')" class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown"><span class="fa fa-key" title="Admin Area"></span> Admin
          <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <router-link :to="{ name: 'users' }" tag="li" active-class="active">
              <a><span class="list-group-item-value">Users Management</span></a>
            </router-link>
            <router-link :to="{ name: 'roles' }" tag="li" active-class="active" v-if="this.can('roles-list')">
              <a><span class="list-group-item-value">Roles</span></a>
            </router-link>
            <router-link :to="{ name: 'permissions' }" tag="li" active-class="active" v-if="this.can('permissions-list')">
              <a><span class="list-group-item-value">Permissions</span></a>
            </router-link>
          </ul>
        </li>
    </template>
    <router-view></router-view>
</pf-layout>
</div>
</template>
<script>
import Perm from "./permission.js";
export default {
  mixins: [Perm],
  data() {
    return {
      'display': 'flex'
    }
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