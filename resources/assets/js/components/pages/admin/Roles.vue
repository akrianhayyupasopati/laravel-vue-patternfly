<template>
	<div>
    <pf-modal v-if="showModal" :title="modalTitle" @close="showModal = false">
      <form autocomplete="off" method="post">
            <input type="hidden" v-model="role.id"/>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.name }">
                <label for="name">Name</label>
                <input type="text" id="name" class="form-control" v-model="role.name" required>
                <span class="help-block" v-if="error && errors.name">{{ errors.name[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.display_name }">
                <label for="display_name">Display name</label>
                <input type="text" id="display_name" class="form-control" v-model="role.display_name" required>
                <span class="help-block" v-if="error && errors.display_name">{{ errors.display_name[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.description }">
                <label for="description">Decription</label>
                <input type="text" id="description" class="form-control" v-model="role.description" required>
                <span class="help-block" v-if="error && errors.description">{{ errors.description[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.permission }">
                <label for="password">Permission</label>
                <br/>
                <v-select multiple :options="permissions" v-model="role.permissions"></v-select>
                <span class="help-block" v-if="error && errors.permission">{{ errors.permission[0] }}</span>
            </div>
      </form>
      <template slot="button">
        <button class="btn btn-primary" @click="save">Save</button>
      </template>
    </pf-modal>
    <pf-modal v-if="showDisplay" title="Detail Record" @close="showDisplay = false">
      <dl class="dl dl-horizontal">
        <dt>Name</dt>
        <dd>{{role.name}}</dd>
        <dt>Display name</dt>
        <dd>{{role.display_name}}</dd>
        <dt>Description</dt>
        <dd>{{role.description}}</dd>
        <dt>Created at</dt>
        <dd>{{role.created_at}}</dd>
        <dt>Modified at</dt>
        <dd>{{role.updated_at}}</dd>
        <dt>Permissions</dt>
        <dd><li class="text text-success" v-for="(perm,i) in role.permissions" :key="i">{{perm.display_name}} </li></dd>
      </dl>
    </pf-modal>
		<legend><span v-if="loading" class="spinner spinner-inline"></span> Roles Management</legend>
        <pf-toolbar :views="views" :view="view" :filter-fields="filterFields" :filters="filters" :columns="cols" :picked-columns="pickedCols" :sort-fields="sortFields" :sort-by="sortBy" :sort-direction="sortDirection" :result-count="resultCount" @update:filters="filter" @sort-by="sort" @update:pickedColumns="setVisible" @update:view="setView">
          <div class="form-group">
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-roles')" @click="add" ><i class="fa fa-plus-square"></i></button>
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-roles')" @click="multiDelete"><i class="fa fa-trash"></i></button>
            <download-excel class="btn btn-default" type="button" :data="excelData" :fields="excelFields" name="roles.xls"><i class="fa fa-file-excel-o"></i></download-excel>
          </div>
        </pf-toolbar>
        <pf-table v-show="this.view=='table'" :striped="true" :bordered="true" :hover="true" :selectable="true" :sortable="true" :columns="tableColumns" :rows="rows" :page="page" :totalItems="resultCount" :itemsPerPage="perPage" @update:page="updatePage" @update:itemsPerPage="updatePerPage" ref="selectionTable">
          <template slot-scope="data">
            <td v-if="tableColumns.includes('Id')">{{data.row.id}}</td>
            <td v-if="tableColumns.includes('Name')">{{data.row.name}}</td>
            <td v-if="tableColumns.includes('Display name')">{{data.row.display_name}}</td>
            <td v-if="tableColumns.includes('Description')">{{data.row.description}}</td>
            <td v-if="tableColumns.includes('Created at')">{{data.row.created_at}}</td>
            <td v-if="tableColumns.includes('Modified at')">{{data.row.updated_at}}</td>
          </template>
          <template slot="action" slot-scope="data">
            <a class="btn btn-default" href="#" @click="display(data.row)">Display</a>
          </template>
          <template slot="dropdown" slot-scope="data">
            <li><a href="#" @click="edit(data.row)">Edit</a></li>
            <li><a href="#" @click="del(data.row)">Delete</a></li>
          </template>
        </pf-table>
        <pf-list-view v-show="this.view=='list'" :selectable="true" :page="page" :totalItems="resultCount" :itemsPerPage="perPage" @update:page="updatePage" @update:itemsPerPage="updatePerPage" :rows="rows" ref="selectionList">
          <template slot-scope="data">
            <div class="list-view-pf-left">
              <span class="fa fa-lock list-view-pf-icon-sm"></span>
            </div>
            <div class="list-view-pf-body list-view-pf-stacked">
              <div class="list-view-pf-description">
                <div class="list-group-item-heading">{{data.row.display_name}}</div>
                <div class="list-group-item-text"><dt>Name</dt><dd>{{data.row.name}}</dd></div>
                <div class="list-group-item-text"><dt>Description</dt><dd>{{data.row.description}}</dd></div>
                <div class="list-group-item-text"><dt>Created at</dt><dd>{{data.row.created_at}}</dd></div>
                <div class="list-group-item-text"><dt>Last Modified</dt><dd>{{data.row.updated_at}}</dd></div>
              </div>
              <div class="list-view-pf-additional-info">
                <div class="list-view-pf-additional-info-item list-view-pf-additional-info-stacked"  v-for="(perm,i) in data.row.perms" :key="i">
                  <span class="fa fa-key"></span>
                  {{perm.display_name}}
                </div>
              </div>
            </div>
          </template>
          <template slot="action" slot-scope="data">
            <a class="btn btn-default" href="#" @click="display(data.row)">Display</a>
          </template>
          <template slot="dropdown" slot-scope="data">
            <li><a href="#" @click="edit(data.row)">Edit</a></li>
            <li><a href="#" @click="del(data.row)">Delete</a></li>
          </template>
        </pf-list-view>
      </div>
</template>
<script>
import VSelect from "vue-select";
import DownloadExcel from "vue-json-excel";
import Perms from "./../../../permission.js";
export default {
  mixins: [Perms],
  components: {
    DownloadExcel,
    VSelect
  },

  data() {
    return {
      role: {
        id: "",
        name: "",
        display_name: "",
        description: "",
        permissions: []
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
        display_name: {
          label: "Display name",
          placeholder: "filter by display name"
        },
        description: {
          label: "Description",
          placeholder: "filter by description"
        }
      },
      filters: [],
      sortFields: {
        name: "Name",
        display_name: "Display name",
        description: "Description",
        created_at: "Created at",
        updated_at: "Modified at"
      },
      sortBy: "name",
      sortDirection: "ascending",
      cols: [
        "Id",
        "Name",
        "Display name",
        "Description",
        "Created at",
        "Modified at"
      ],
      pickedCols: [
        "Name",
        "Display name",
        "Description",
        "Created at",
        "Modified at"
      ],
      resultCount: 0,
      tableColumns: [
        "Id",
        "Name",
        "Display name",
        "Description",
        "Created at",
        "Modified at"
      ],
      rows: [],
      page: 0,
      perPage: 10,
      loading: false,
      params: {},
      excelData: [],
      excelFields: {
        Name: "name",
        "Display name": "display_name",
        Description: "description",
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
      permissions: []
    };
  },
  mounted() {
    this.params.perPage = this.perPage;
    this.tableColumns = this.pickedCols;
    this.rolesList();
    this.getPermissions();
    this.showModal = false;
  },
  watch: {},
  methods: {
    getPermissions: function() {
      var app = this;
      app.loading = true;
      this.axios({
        method: "get",
        url: "/permissions"
      })
        .then(function(response) {
          if (app.can("manage-permissions")) {
            var options = [];
            response.data.permissions.forEach(element => {
              options.push({ label: element.display_name, value: element.id });
            });
            app.permissions = options;
          } else {
            response.data.permissions = response.data.permissions.filter(
              item => item.name !== "manage-permissions"
            );
            var options = [];
            response.data.permissions.forEach(element => {
              options.push({ label: element.display_name, value: element.id });
            });
            app.permissions = options;
          }
          app.loading = false;
        })
        .catch(function(error) {
          app.loading = false;
          app.$root.$refs.notif.add(
            "An error occured couldn't load permissions",
            "danger"
          );
          console.log(error);
        });
    },
    rolesList: function() {
      var app = this;
      app.loading = true;
      this.axios({
        method: "get",
        url: "/roles",
        params: app.params
      })
        .then(function(response) {
          if (app.can("manage-permissions")) {
            app.rows = response.data.roles.data;
          } else {
            response.data.roles.data = response.data.roles.data.filter(
              item => item.name !== "developer"
            );
            app.rows = response.data.roles.data;
          }
          app.page = response.data.roles.current_page;
          app.resultCount = parseInt(response.data.roles.total);
          app.perPage = parseInt(response.data.roles.per_page);
          app.excelData = response.data.export;
          app.loading = false;
        })
        .catch(function(error) {
          app.loading = false;
          app.$root.$refs.notif.add(
            "An error occured couldn't load role",
            "danger"
          );
          console.log(error);
        });
    },
    updatePage: function(pageNumber) {
      this.params.page = pageNumber;
      this.rolesList();
    },
    updatePerPage: function(perPage) {
      this.params.perPage = perPage;
      this.params.page = 1;
      this.rolesList();
    },
    filter: function(filters) {
      this.params.search = filters;
      this.params.page = 1;
      this.rolesList();
    },
    sort: function(sortBy, sortDirection) {
      this.params.sortBy = sortBy;
      this.params.sortDirection = sortDirection;
      this.rolesList();
    },
    setVisible: function(columns) {
      this.tableColumns = columns;
    },
    setView: function(activeView) {
      this.view = activeView;
    },
    add: function(event) {
      if (this.can("manage-roles")) {
        event.preventDefault();
        this.modalTitle = "Add role";
        this.role.id = "";
        this.role.name = "";
        this.role.display_name = "";
        this.role.description = "";
        this.role.permissions = [];
        this.showModal = true;
        this.editing = false;
        this.error = false;
      } else {
        this.$root.$refs.notif.add("Permission denied!", "danger");
      }
    },
    edit: function(row) {
      if (this.can("manage-roles")) {
        this.modalTitle = "Add role";
        this.role.id = row.id;
        this.role.name = row.name;
        this.role.display_name = row.display_name;
        this.role.description = row.description;
        var permissions = [];
        for (var i = 0; i < row.perms.length; i++) {
          permissions.push({
            value: row.perms[i].id,
            label: row.perms[i].display_name
          });
        }
        this.role.permissions = permissions;
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
        url: "/roles",
        data: app.role
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add("New role successfully added", "success");
            app.showModal = false;
            app.error = false;
            app.rolesList();
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
        url: "/roles/" + app.role.id,
        data: app.role
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add("role successfully changed", "success");
            app.showModal = false;
            app.error = false;
            app.rolesList();
          }
        })
        .catch(function(error) {
          app.error = true;
          app.errors = error.response.data;
        });
    },
    del: function(row) {
      var app = this;
      if (this.can("manage-roles")) {
        if (
          confirm(
            "Are you sure want to delete this role with name " +
              row.display_name +
              "? this cannot be undone."
          )
        ) {
          this.axios({
            method: "delete",
            url: "/role/" + row.id
          })
            .then(function(response) {
              if (response.data.status == "success") {
                app.$root.$refs.notif.add(
                  "role successfully deleted",
                  "success"
                );
                app.showModal = false;
                app.error = false;
                app.rolesList();
              }
            })
            .catch(function(error) {
              app.$root.$refs.notif.add(
                "Something wrong, cannot delete role",
                "danger"
              );
            });
        }
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
      this.role.name = row.name;
      this.role.display_name = row.display_name;
      this.role.description = row.description;
      this.role.created_at = row.created_at;
      this.role.updated_at = row.updated_at;
      this.role.permissions = row.perms;
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
              " role(s)? this operation couldn't be undone."
          )
        ) {
          this.axios({
            method: "post",
            url: "/roles/group_delete",
            data: { roles: selection }
          })
            .then(function(response) {
              app.$root.$refs.notif.add(
                "Selected roles has been deleted",
                "success"
              );
              if (app.view == "table")
                app.$refs.selectionTable.setAllSelected(false);
              else app.$refs.selectionList.setAllSelected(false);
              app.rolesList();
            })
            .catch(function(error) {
              app.loading = false;
              app.$root.$refs.notif.add(
                "An error occured couldn't delete roles",
                "danger"
              );
              console.log(error);
            });
        }
      } else {
        app.$root.$refs.notif.add("No roles selected.", "warning");
      }
    }
  }
};
</script>