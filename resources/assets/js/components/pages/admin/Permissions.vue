<template>
	<div>
    <pf-modal v-if="showModal" :title="modalTitle" @close="showModal = false">
      <form autocomplete="off" method="post">
            <input type="hidden" v-model="permission.id"/>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.name }">
                <label for="name">Name</label>
                <input type="text" id="name" class="form-control" v-model="permission.name" required>
                <span class="help-block" v-if="error && errors.name">{{ errors.name[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.display_name }">
                <label for="display_name">Display name</label>
                <input type="text" id="display_name" class="form-control" v-model="permission.display_name" required>
                <span class="help-block" v-if="error && errors.display_name">{{ errors.display_name[0] }}</span>
            </div>
            <div class="form-group" v-bind:class="{ 'has-error': error && errors.description }">
                <label for="description">Decription</label>
                <input type="text" id="description" class="form-control" v-model="permission.description" required>
                <span class="help-block" v-if="error && errors.description">{{ errors.description[0] }}</span>
            </div>
      </form>
      <template slot="button">
        <button class="btn btn-primary" @click="save">Save</button>
      </template>
    </pf-modal>
    <pf-modal v-if="showDisplay" title="Detail Record" @close="showDisplay = false">
      <dl class="dl dl-horizontal">
        <dt>Name</dt>
        <dd>{{permission.name}}</dd>
        <dt>Display name</dt>
        <dd>{{permission.display_name}}</dd>
        <dt>Description</dt>
        <dd>{{permission.description}}</dd>
        <dt>Created at</dt>
        <dd>{{permission.created_at}}</dd>
        <dt>Modified at</dt>
        <dd>{{permission.updated_at}}</dd>
      </dl>
    </pf-modal>
		<legend><span v-if="loading" class="spinner spinner-inline"></span> Permissions Management</legend>
        <pf-toolbar :views="views" :view="view" :filter-fields="filterFields" :filters="filters" :columns="cols" :picked-columns="pickedCols" :sort-fields="sortFields" :sort-by="sortBy" :sort-direction="sortDirection" :result-count="resultCount" @update:filters="filter" @sort-by="sort" @update:pickedColumns="setVisible" @update:view="setView">
          <div class="form-group">
            <button class="btn btn-default" type="button" :disabled="!this.can('manage-permissions')" @click="add" ><i class="fa fa-plus-square"></i></button>
            <button class="btn btn-default" type="button"  :disabled="!this.can('manage-permissions')"  @click="multiDelete"><i class="fa fa-trash"></i></button>
            <download-excel class="btn btn-default" type="button" :data="excelData" :fields="excelFields" name="permissions.xls"><i class="fa fa-file-excel-o"></i></download-excel>
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
            <li><a href="#" @click="del(data.row)">Delete</a></li>
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
      permission: {
        id: "",
        name: "",
        display_name: "",
        description: ""
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
      showDisplay: false
    };
  },
  mounted() {
    this.params.perPage = this.perPage;
    this.tableColumns = this.pickedCols;
    this.permissionsList();
    this.showModal = false;
  },
  watch: {},
  methods: {
    permissionsList: function() {
      var app = this;
      app.loading = true;
      this.axios({
        method: "get",
        url: "/permissions",
        params: app.params
      })
        .then(function(response) {
          app.rows = response.data.permissions.data;
          app.page = response.data.permissions.current_page;
          app.resultCount = parseInt(response.data.permissions.total);
          app.perPage = parseInt(response.data.permissions.per_page);
          app.excelData = response.data.export;
          app.loading = false;
        })
        .catch(function(error) {
          app.loading = false;
          app.$root.$refs.notif.add(
            "An error occured couldn't load permission",
            "danger"
          );
          console.log(error);
        });
    },
    updatePage: function(pageNumber) {
      this.params.page = pageNumber;
      this.permissionsList();
    },
    updatePerPage: function(perPage) {
      this.params.perPage = perPage;
      this.params.page = 1;
      this.permissionsList();
    },
    filter: function(filters) {
      this.params.search = filters;
      this.params.page = 1;
      this.permissionsList();
    },
    sort: function(sortBy, sortDirection) {
      this.params.sortBy = sortBy;
      this.params.sortDirection = sortDirection;
      this.permissionsList();
    },
    setVisible: function(columns) {
      this.tableColumns = columns;
    },
    setView: function(activeView) {
      this.view = activeView;
    },
    add: function(event) {
      event.preventDefault();
      if (this.can("manage-permissions")) {
        this.modalTitle = "Add permission";
        this.permission.id = "";
        this.permission.name = "";
        this.permission.display_name = "";
        this.permission.description = "";
        this.showModal = true;
        this.editing = false;
        this.error = false;
      } else {
        this.$root.$refs.notif.add("Permission denied!", "danger");
      }
    },
    edit: function(row) {
      if (this.can("manage-permissions")) {
        this.modalTitle = "Edit permission";
        this.permission.id = row.id;
        this.permission.name = row.name;
        this.permission.display_name = row.display_name;
        this.permission.description = row.description;
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
        url: "/permissions",
        data: app.permission
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add(
              "New permission successfully added",
              "success"
            );
            app.showModal = false;
            app.error = false;
            app.permissionsList();
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
        url: "/permissions/" + app.permission.id,
        data: app.permission
      })
        .then(function(response) {
          if (response.data.status == "success") {
            app.$root.$refs.notif.add(
              "permission successfully changed",
              "success"
            );
            app.showModal = false;
            app.error = false;
            app.permissionsList();
          }
        })
        .catch(function(error) {
          app.error = true;
          app.errors = error.response.data;
        });
    },
    del: function(row) {
      var app = this;
      if (this.can("manage-permissions")) {
        if (
          confirm(
            "Are you sure want to delete this permission with name " +
              row.display_name +
              "? this cannot be undone."
          )
        ) {
          this.axios({
            method: "delete",
            url: "/permission/" + row.id
          })
            .then(function(response) {
              if (response.data.status == "success") {
                app.$root.$refs.notif.add(
                  "permission successfully deleted",
                  "success"
                );
                app.showModal = false;
                app.error = false;
                app.permissionsList();
              }
            })
            .catch(function(error) {
              app.$root.$refs.notif.add(
                "Something wrong, cannot delete permission",
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
      this.permission.name = row.name;
      this.permission.display_name = row.display_name;
      this.permission.description = row.description;
      this.permission.created_at = row.created_at;
      this.permission.updated_at = row.updated_at;
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
              " permission(s)? this operation couldn't be undone."
          )
        ) {
          this.axios({
            method: "post",
            url: "/permissions/group_delete",
            data: { permissions: selection }
          })
            .then(function(response) {
              app.$root.$refs.notif.add(
                "Selected permissions has been deleted",
                "success"
              );
              if (app.view == "table")
                app.$refs.selectionTable.setAllSelected(false);
              else app.$refs.selectionList.setAllSelected(false);
              app.permissionsList();
            })
            .catch(function(error) {
              app.loading = false;
              app.$root.$refs.notif.add(
                "An error occured couldn't delete permissions",
                "danger"
              );
              console.log(error);
            });
        }
      } else {
        app.$root.$refs.notif.add("No permissions selected.", "warning");
      }
    }
  }
};
</script>