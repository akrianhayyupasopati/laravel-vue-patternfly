<template>
	<div>
    <pf-confirm :message="confirm" :showConfirm="isConfirm" :data="dataConfirm" @on:confirm="del(arguments[0])" @on:close="isConfirm=false"></pf-confirm>
    <pf-modal v-if="showModal" :title="modalTitle" @close="showModal = false">
      <form autocomplete="off" method="post">
            <input type="hidden" v-model="user.id"/>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.name }">
                <label for="name">Name</label>
                <input type="text" id="name" class="form-control" v-model="user.name" required>
                <span class="help-block" v-if="error && errors.name">{{ errors.name[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.email }">
                <label for="email">E-mail</label>
                <input type="email" id="email" class="form-control" placeholder="user@example.com" v-model="user.email" required>
                <span class="help-block" v-if="error && errors.email">{{ errors.email[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.role }">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" v-model="user.role">
                  <option disabled value="">Select role</option>
                  <option v-for="(role,i) in roles" :key="i" :value="role.id">{{role.display_name}}</option>
                </select>
                <span class="help-block" v-if="error && errors.role">{{ errors.role[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.password }">
                <label for="password">Password</label>
                <input type="password" id="password" class="form-control" v-model="user.password" required>
                <span class="help-block" v-if="error && errors.password">{{ errors.password[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.confirm_password }">
                <label for="password">Confirm Password</label>
                <input type="password" id="confirm_password" class="form-control" v-model="user.confirm_password" required>
                <span class="help-block" v-if="error && errors.confirm_password">{{ errors.confirm_password[0] }}</span>
            </div>
      </form>
      <template slot="button">
        <button class="btn btn-primary" @click="save">Save</button>
      </template>
    </pf-modal>
    <pf-modal v-if="showDisplay" title="Detail Record" @close="showDisplay = false">
      <dl class="dl dl-horizontal">
        <dt>Name</dt>
        <dd>{{user.name}}</dd>
        <dt>Email</dt>
        <dd>{{user.email}}</dd>
        <dt>Created at</dt>
        <dd>{{user.created_at}}</dd>
        <dt>Modified at</dt>
        <dd>{{user.updated_at}}</dd>
        <dt>Roles</dt>
        <dd><span class="label label-success">{{user.role}}</span></dd>
        <dt>Permissions</dt>
        <dd><li class="text text-success" v-for="(perm,i) in user.permissions" :key="i">{{perm.display_name}} </li></dd>
      </dl>
    </pf-modal>
		<legend><span v-if="loading" class="spinner spinner-inline"></span> Users Management</legend>
        <pf-toolbar :views="views" :view="view" :filter-fields="filterFields" :filters="filters" :columns="cols" :picked-columns="pickedCols" :sort-fields="sortFields" :sort-by="sortBy" :sort-direction="sortDirection" :result-count="resultCount" @update:filters="filter" @sort-by="sort" @update:pickedColumns="setVisible" @update:view="setView">
          <div class="form-group">
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-users')" @click="add" ><i class="fa fa-plus-square"></i></button>
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-users')"  @click="multiDelete"><i class="fa fa-trash"></i></button>
            <download-excel class="btn btn-default" type="button" :data="excelData" :fields="excelFields" name="users.xls"><i class="fa fa-file-excel-o"></i></download-excel>
          </div>
        </pf-toolbar>
        <pf-table v-show="this.view=='table'" :striped="true" :bordered="true" :hover="true" :selectable="true" :sortable="true" :columns="tableColumns" :scrollable="false" :rows="rows" :page="page" :totalItems="totalItems" :itemsPerPage="perPage" @update:page="updatePage" @update:itemsPerPage="updatePerPage" ref="selectionTable">
          <template slot-scope="data">
            <td v-if="tableColumns.includes('Id')">{{data.row.id}}</td>
            <td v-if="tableColumns.includes('Name')">{{data.row.name}}</td>
            <td v-if="tableColumns.includes('Email')">{{data.row.email}}</td>
            <td v-if="tableColumns.includes('Roles')"><small><span class="label label-success" v-for="(role,i) in data.row.roles" :key="i">{{role.display_name}} </span></small></td>
            <td v-if="tableColumns.includes('Created at')">{{data.row.created_at}}</td>
            <td v-if="tableColumns.includes('Modified at')">{{data.row.updated_at}}</td>
          </template>
          <template slot="action" slot-scope="data">
            <a class="btn btn-default" href="#" @click="display(data.row)">Display</a>
          </template>
          <template slot="dropdown" slot-scope="data">
            <li><a href="#" @click="edit(data.row)">Edit</a></li>
            <li><a href="#" @click="confirmDelete(data.row)">Delete</a></li>
          </template>
        </pf-table>
        <pf-list-view v-show="this.view=='list'" :selectable="true" :page="page" :totalItems="totalItems" :itemsPerPage="perPage" :rows="rows" @update:page="updatePage" @update:itemsPerPage="updatePerPage" ref="selectionList">
          <template slot-scope="data">
            <div class="list-view-pf-left">
              <span class="fa fa-user list-view-pf-icon-sm"></span>
            </div>
            <div class="list-view-pf-body list-view-pf-stacked">
              <div class="list-view-pf-description">
                <div class="list-group-item-heading">{{data.row.name}}</div>
                <div class="list-group-item-text"><i class="fa fa-envelope"></i> {{data.row.email}}</div>
                <div class="list-group-item-text" v-for="(role,i) in data.row.roles" :key="i"><i class="fa fa-key"></i> {{role.display_name}}</div>
              </div>
              <div class="list-view-pf-additional-info">
                <div class="list-view-pf-additional-info-item list-view-pf-additional-info-stacked">
                  <span class="fa fa-plus-square"></span>
                  {{data.row.created_at}}
                </div>
                <div class="list-view-pf-additional-info-item list-view-pf-additional-info-stacked">
                  <span class="fa fa-pencil-square"></span>
                  {{data.row.updated_at}}
                </div>
              </div>
            </div>
          </template>
          <template slot="action" slot-scope="data">
            <a class="btn btn-default" href="#" @click="display(data.row)">Display</a>
          </template>
          <template slot="dropdown" slot-scope="data">
            <li><a href="#" @click="edit(data.row)">Edit</a></li>
            <li><a href="#" @click="confirmDelete(data.row)">Delete</a></li>
          </template>
        </pf-list-view>
      </div>
</template>
<script>
import DownloadExcel from "vue-json-excel";
import Perms from "./../../../permission.js";
export default {
  mixins: [Perms],
  components: {
    DownloadExcel,
  },

  data() {
    return {
      confirm: "",
      isConfirm: false,
      dataConfirm: null,
      user: {
        id: "",
        name: "",
        email: "",
        role: "",
        password: "",
        confirm_password: "",
        permissions: ""
      },
      error: false,
      errors: {},
      showModal: false,
      editing: false,
      view: "table",
      views: "table,list",
      filterFields: {
        name: {
          label: "Name",
          placeholder: "filter by name"
        },
        email: {
          label: "Email",
          placeholder: "filter by email"
        }
      },
      filters: [],
      sortFields: {
        name: "Name",
        email: "Email",
        created_at: "Created at",
        updated_at: "Modified at"
      },
      sortBy: "name",
      sortDirection: "ascending",
      cols: ["Id", "Name", "Email", "Roles", "Created at", "Modified at"],
      pickedCols: ["Name", "Email", "Roles", "Created at", "Modified at"],
      resultCount: 0,
      tableColumns: [ "Id", "Name", "Email", "Roles", "Created at", "Modified at" ],
      rows: [],
      page: 0,
      pages: 0,
      perPage: 10,
      totalItems: 0,
      loading: false,
      params: {},
      selected: 10,
      excelData: [],
      excelFields: {
        "Name": "name",
        "Email": "email",
        "Created at": "created_at",
        "Modified at": "updated_at"
      },
      excelMeta: [
        [
          {
            key: "charset",
            value: "utf-8"
          }
        ]
      ],
      modalTitle: "My Modal",
      showDisplay: false,
      roles: []
    };
  },
  mounted() {
    this.params.perPage = 10;
    this.tableColumns = this.pickedCols;
    this.usersList();
    this.getRoles();
    this.showModal = false;
  },
  watch: {},
  methods: {
    getRoles: function() {
      var app = this;
      app.loading = true;
      this.axios({
        method: "get",
        url: "/roles"
      })
        .then(function(response) {
          if (app.can("manage-permissions")) {
            app.roles = response.data.roles;
          } else {
            response.data.roles = response.data.roles.filter(
              item => item.name !== "developer"
            );
            app.roles = response.data.roles;
          }
          app.loading = false;
        })
        .catch(function(error) {
          app.loading = false;
          app.$root.$refs.notif.add(
            "An error occured couldn't load roles",
            "danger"
          );
          console.log(error);
        });
    },
    usersList: function() {
      var app = this;
      app.loading = true;
      this.axios({
        method: "get",
        url: "/user",
        params: app.params
      })
        .then(function(response) {
          if (app.can("manage-permissions")) {
            app.rows = response.data.users.data;
          } else {
            response.data.users.data = response.data.users.data.filter(
              item => item.name !== "Developer"
            );
            app.rows = response.data.users.data;
          }
          app.page = response.data.users.current_page;
          app.totalItems = response.data.users.total;
          app.resultCount = response.data.users.total;
          app.perPage = parseInt(response.data.users.per_page);
          app.excelData = response.data.export;
          app.loading = false;
        })
        .catch(function(error) {
          app.loading = false;
          app.$root.$refs.notif.add(
            "An error occured couldn't load user",
            "danger"
          );
          console.log(error);
        });
    },
    updatePage: function(pageNumber) {
      this.params.page = pageNumber;
      this.usersList();
    },
    updatePerPage: function(perPage) {
      this.params.perPage = perPage;
      this.params.page = 1;
      this.usersList();
    },
    filter: function(filters) {
      this.params.search = filters;
      this.params.page = 1;
      this.usersList();
    },
    sort: function(sortBy, sortDirection) {
      this.params.sortBy = sortBy;
      this.params.sortDirection = sortDirection;
      this.usersList();
    },
    setVisible: function(columns) {
      this.tableColumns = columns;
    },
    setView: function(activeView) {
      this.view = activeView;
    },
    add: function(event) {
      event.preventDefault();
      if (this.can("manage-users")) {
        this.modalTitle = "Add User";
        this.user.id = "";
        this.user.name = "";
        this.user.email = "";
        this.user.role = "";
        this.user.password = "";
        this.user.confirm_password = "";
        this.showModal = true;
        this.editing = false;
        this.error = false;
      } else {
        this.$root.$refs.notif.add("Permission denied!", "danger");
      }
    },
    edit: function(row) {
      if (this.can("manage-users")) {
        this.modalTitle = "Edit User";
        this.user.id = row.id;
        this.user.name = row.name;
        this.user.email = row.email;
        this.user.role = row.roles[0].id;
        this.user.password = "";
        this.user.confirm_password = "";
        this.showModal = true;
        this.editing = true;
        this.error = false;
      } else {
        this.$root.$refs.notif.add("Permission denied!", "danger");
      }
    },
    create: function() {
      var app = this;
      this.axios({
        method: "post",
        url: "/user",
        data: app.user
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add("New User successfully added", "success");
            app.showModal = false;
            app.error = false;
            app.usersList();
          }
        })
        .catch(function(error) {
          app.error = true;
          app.errors = error.response.data;
        });
    },
    update: function() {
      var app = this;
      this.axios({
        method: "put",
        url: "/user/" + app.user.id,
        data: app.user
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add("User successfully changed", "success");
            app.showModal = false;
            app.error = false;
            app.usersList();
          }
        })
        .catch(function(error) {
          app.error = true;
          app.errors = error.response.data;
        });
    },
    confirmDelete: function(data) {
      this.dataConfirm = data;
      this.confirm =
        "Are you sure want to delete this user with name " +
        data.name +
        "? this cannot be undone.";
      this.isConfirm = true;
    },
    del: function(row) {
      this.isConfirm = false;
      var app = this;
      if (this.can("manage-users")) {
        this.axios({
          method: "delete",
          url: "/user/" + row.id
        })
          .then(function(response) {
            if (response.data.status == "success") {
              app.$root.$refs.notif.add("User successfully deleted", "success");
              app.showModal = false;
              app.error = false;
              app.usersList();
            }
          })
          .catch(function(error) {
            app.$root.$refs.notif.add(
              "Something wrong, cannot delete user",
              "danger"
            );
          });
      } else {
        this.$root.$refs.notif.add("Permission denied!", "danger");
      }
    },
    save: function() {
      if (!this.editing) {
        this.create();
      } else {
        this.update();
      }
    },
    display: function(row) {
      this.user.name = row.name;
      this.user.email = row.email;
      this.user.created_at = row.created_at;
      this.user.updated_at = row.updated_at;
      this.user.role = row.roles[0].display_name;
      this.user.permissions = row.roles[0].perms;
      this.showDisplay = true;
    },
    multiDelete: function(event) {
      var app = this;
      event.preventDefault();
      var selection = [];
      if (this.view == "table")
        selection = this.$refs.selectionTable.getSelected();
      else selection = this.$refs.selectionList.getSelected();
      if (selection.length > 0) {
        if (
          confirm(
            "Are you sure want to delete " +
              selection.length +
              " user(s)? this operation couldn't be undone."
          )
        ) {
          this.axios({
            method: "post",
            url: "/user/group_delete",
            data: { users: selection }
          })
            .then(function(response) {
              app.$root.$refs.notif.add(
                "Selected users has been deleted",
                "success"
              );
              if (app.view == "table")
                app.$refs.selectionTable.setAllSelected(false);
              else app.$refs.selectionList.setAllSelected(false);
              app.usersList();
            })
            .catch(function(error) {
              app.loading = false;
              app.$root.$refs.notif.add(
                "An error occured couldn't delete users",
                "danger"
              );
              console.log(error);
            });
        }
      } else {
        app.$root.$refs.notif.add("No users selected.", "warning");
      }
    }
  }
};
</script>