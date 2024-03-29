<template>
	<div>
    <pf-modal v-if="isConfirm" title="Please confirm" @close="isConfirm=false" @cancel="isConfirm=false" @confirm="del">
      {{ confirm }}
    </pf-modal>
    <pf-modal v-if="showModal" :title="modalTitle" @close="showModal=false" @cancel="showModal=false" @confirm="save">
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
    </pf-modal>
    <pf-modal v-if="showDisplay" title="Detail Record" @close="showDisplay = false" cancelButton="">
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
		<legend>Users Management</legend>
        <pf-spinner :loading="loading" size="lg">
        <pf-toolbar :views="views" :view="view" :filterFields="filterFields" :filters="filters" :columns="cols" :pickedColumns="pickedCols" :sortFields="sortFields" :sortBy="sortBy" :sortDirection="sortDirection" :resultCount="resultCount" @update:filters="updateFilters" @sort-by="sort" @update:pickedColumns="setVisible" @update:view="setView" @update:sortBy="updateSortBy" @update:sortDirection="updateSortDirection">
          <div class="form-group">
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-users')" @click="add" ><i class="fa fa-plus-square"></i></button>
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-users')"  @click="confirmDelete(null, true)"><i class="fa fa-trash"></i></button>
            <download-excel class="btn btn-default" type="button" :data="excelData" :fields="excelFields" name="users.xls"><i class="fa fa-file-excel-o"></i></download-excel>
          </div>
        </pf-toolbar>
        <pf-table v-show="this.view=='table'" :striped="true" :bordered="true" :hover="true" :selectable="true" :sortable="false" :columns="tableColumns" :scrollable="false" :rows="rows" :page="page" :totalItems="totalItems" :itemsPerPage="perPage" @update:page="updatePage" @update:itemsPerPage="updatePerPage" ref="selectionTable">
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
            <li><a href="#" @click="confirmDelete(data.row,false)">Delete</a></li>
          </template>
        </pf-table>
        <pf-list-view v-show="this.view=='list'" :expandable="true" :stacked="true" :selectable="true" :page="page" :totalItems="totalItems" :itemsPerPage="perPage" :rows="rows" @update:page="updatePage" @update:itemsPerPage="updatePerPage" ref="selectionList">
          <template slot-scope="data">
            <pf-list-item icon="fa-user" iconVariant="info" iconSize="sm">
              <template slot="heading">
                {{data.row.name}}
              </template>
              <template slot="description">
                <pf-icon name="fa-envelope"></pf-icon> {{data.row.email}} <br/>
                <pf-icon name="fa-lock"></pf-icon><span v-for="(role,i) in data.row.roles" :key="i"> {{role.display_name}}</span>
              </template>
              <template slot="additional-info" v-if="data.row.roles.length > 0">
                <pf-list-item-additional-info :stacked="true" v-for="(perm,i) in data.row.roles[0].perms" :key="i"><pf-icon name="pficon-key" class="fa"></pf-icon>
                  {{perm.display_name}}
                </pf-list-item-additional-info>
              </template>
            </pf-list-item>
          </template>
          <template slot="action" slot-scope="data">
            <a class="btn btn-default" href="#" @click="display(data.row)">Display</a>
          </template>
          <template slot="dropdown" slot-scope="data">
            <li><a href="#" @click="edit(data.row)">Edit</a></li>
            <li><a href="#" @click="confirmDelete(data.row,false)">Delete</a></li>
          </template>
          <template slot="expansion" slot-scope="data">
            <dl class="dl-horizontal">
              <dt>Created</dt>
              <dd>{{data.row.created_at}}</dd>
              <dt>Last modified</dt>
              <dd>{{data.row.updated_at}}</dd>
            </dl>
          </template>
        </pf-list-view>
        </pf-spinner>
      </div>
</template>
<script>
import DownloadExcel from "vue-json-excel";
import Perms from "./../../../permission.js";
export default {
  mixins: [Perms],
  components: {
    DownloadExcel
  },
  data() {
    return {
      confirm: "",
      isConfirm: false,
      multiDelete: false,
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
        id: "Id",
        name: "Name",
        email: "Email",
        created_at: "Created at",
        updated_at: "Modified at"
      },
      sortBy: "id",
      sortDirection: "ascending",
      cols: ["Id", "Name", "Email", "Roles", "Created at", "Modified at"],
      pickedCols: ["Name", "Email", "Roles", "Created at", "Modified at"],
      resultCount: 0,
      tableColumns: [
        "Id",
        "Name",
        "Email",
        "Roles",
        "Created at",
        "Modified at"
      ],
      rows: [],
      page: 0,
      perPage: 10,
      totalItems: 0,
      loading: false,
      params: {},
      selected: 10,
      excelData: [],
      excelFields: {
        Name: "name",
        Email: "email",
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
    updateFilters: function(activeFilters) {
      this.filters = activeFilters;
      this.params.search = this.filters;
      this.params.page = 1;
      this.usersList();
    },
    updateSortBy: function(sortBy) {
      this.sortBy = sortBy
    },
    updateSortDirection: function(sortDirection) {
      this.sortDirection = sortDirection
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
        if (row.roles.length > 0) {
          this.user.role = row.roles[0].id;
        }
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
    confirmDelete: function(data, multiDelete) {
      this.multiDelete = multiDelete;
      this.dataConfirm = data;
      if (multiDelete) {
        var selection = [];
        if (this.view == "table") {
          selection = this.$refs.selectionTable.getSelected();
        } else {
          selection = this.$refs.selectionList.getSelected();
        }
        if (selection.length > 0) {
          this.confirm =
            "Are you sure want to delete " +
            selection.length +
            " user(s)? this operation couldn't be undone.";
          this.isConfirm = true;
        } else {
          this.$root.$refs.notif.add("No users selected.", "warning");
        }
      } else {
        this.confirm =
          "Are you sure want to delete this user with name " +
          data.name +
          "? this cannot be undone.";
        this.isConfirm = true;
      }
    },
    del: function() {
      this.loading = true;
      this.isConfirm = false;
      var app = this;
      if (!app.multiDelete) {
        if (this.can("manage-users")) {
          this.axios({
            method: "delete",
            url: "/user/" + app.dataConfirm.id
          })
            .then(function(response) {
              if (response.data.status == "success") {
                app.$root.$refs.notif.add(
                  "User successfully deleted",
                  "success"
                );
                app.showModal = false;
                app.error = false;
                app.usersList();
                app.loading = false;
              }
            })
            .catch(function(error) {
              app.$root.$refs.notif.add(
                "Something wrong, cannot delete user",
                "danger"
              );
              app.loading = false;
            });
        } else {
          this.$root.$refs.notif.add("Permission denied!", "danger");
          app.loading = false;
        }
      } else {
        var selection = [];
        if (this.view == "table") {
          selection = this.$refs.selectionTable.getSelected();
        } else {
          selection = this.$refs.selectionList.getSelected();
        }
        if (selection.length > 0) {
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
              app.loading = false;
            })
            .catch(function(error) {
              app.loading = false;
              app.$root.$refs.notif.add(
                "An error occured couldn't delete users",
                "danger"
              );
              app.loading = false;
            });
        } else {
          app.$root.$refs.notif.add("No users selected.", "warning");
          app.loading = false;
        }
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
      if (row.roles.length > 0) {
        this.user.role = row.roles[0].display_name;
        this.user.permissions = row.roles[0].perms;
      }
      this.showDisplay = true;
    }
  }
};
</script>