require("patternfly/node_modules/c3/c3.min");
require("patternfly/node_modules/d3/d3.min");
import Vue from "vue";
import VuePatternfly from "vue-patternfly";
import VueRouter from "vue-router";
import axios from "axios";
import VueAxios from "vue-axios";
import App from "./App.vue";
import Dashboard from "./components/pages/Dashboard.vue";
import Home from "./components/pages/Home.vue";
import Register from "./components/pages/Register.vue";
import Login from "./components/pages/admin/Login.vue";
import User from "./components/pages/admin/User.vue";
import Roles from "./components/pages/admin/Roles.vue";
import Permissions from "./components/pages/admin/Permissions.vue";

import "patternfly/dist/css/patternfly.min.css";
import "patternfly/dist/css/patternfly-additions.min.css";
import "vue-patternfly/dist/vue-patternfly.css";

Vue.use(VuePatternfly);
Vue.use(VueRouter);
Vue.use(VueAxios, axios);
axios.defaults.baseURL = `http://${window.location.hostname}:8000/api`;
const router = new VueRouter({
  routes: [
    {
      path: "/",
      name: "home",
      component: Home,
      meta: {
        auth: true
      }
    },
    {
      path: "/register",
      name: "register",
      component: Register,
      meta: {
        auth: false
      }
    },
    {
      path: "/login",
      name: "login",
      component: Login,
      meta: {
        auth: false
      }
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: Dashboard,
      meta: {
        auth: true
      }
    },
    {
      path: "/admin",
      name: "admin",
      redirect: "/admin/users",
      meta: {
        auth: true
      }
    },
    {
      path: "/admin/users",
      name: "users",
      component: User,
      meta: {
        auth: true
      }
    },
    {
      path: "/admin/roles",
      name: "roles",
      component: Roles,
      meta: {
        auth: true
      }
    },
    {
      path: "/admin/permissions",
      name: "permissions",
      component: Permissions,
      meta: {
        auth: true
      }
    }
  ]
});
Vue.router = router;
Vue.use(require("@websanova/vue-auth"), {
  auth: require("@websanova/vue-auth/drivers/auth/bearer.js"),
  http: require("@websanova/vue-auth/drivers/http/axios.1.x.js"),
  router: require("@websanova/vue-auth/drivers/router/vue-router.2.x.js")
});
App.router = Vue.router;
new Vue(App).$mount("#app");
