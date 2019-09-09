<template>
  <v-col>
    <v-card :loading="loading">
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="logs"
          :options.sync="options"
          :server-items-length="totalLogs"
          item-key="id"
          :footer-props="{ 'items-per-page-options': this.rowsPerPageItems }"
          sort-by="id"
          single-expand
          show-expand
          :sort-desc="true"
        >
          <template v-slot:expanded-item="{ headers, item }">
            <td :colspan="headers.length">
              <div class="pa-4">
                <pre>{{ item.context }}</pre>
                <pre>{{ item.extra }}</pre>
              </div>
            </td>
          </template>
          <template v-slot:item.message="{ item }">
            <span v-html="$options.filters.formatMessage(item)"></span>
          </template>
          <template v-slot:item.levelName="{ item }">
            <v-chip label :color="getColor(item.level)" dark>{{
              item.levelName
            }}</v-chip>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-col>
</template>

<script>
import axios from "axios";

export default {
  name: "Logging",
  data() {
    return {
      loading: false,
      totalLogs: 0,
      lastId: 0,
      lastPage: 0,
      lastItemsPerPage: 0,
      options: {},
      rowsPerPageItems: [20, 50, 100, 200],
      headers: [
        { value: "createdAt", text: "Date / Time (UTC)", sortable: false },
        { value: "levelName", text: "Level", sortable: false },
        { value: "message", text: "Message", sortable: false }
      ],
      logs: []
    };
  },
  watch: {
    serverOptions() {
      if (
        this.serverOptions.page !== this.lastPage ||
        this.serverOptions.itemsPerPage !== this.lastItemsPerPage
      ) {
        this.getDataFromApi();
      }
    }
  },
  computed: {
    serverOptions() {
      return {
        page: this.options.page,
        itemsPerPage: this.options.itemsPerPage
      };
    }
  },
  mounted() {
    this.getDataFromApi();
  },
  methods: {
    getDataFromApi() {
      this.lastPage = this.serverOptions.page;
      this.lastItemsPerPage = this.serverOptions.itemsPerPage;
      this.loading = true;

      return axios
        .post("/api/v1/logs", this.serverOptions)
        .then(({ data }) => {
          this.logs = data.items;
          this.totalLogs = data.total;
        })
        .catch(err => console.log(err.response.data))
        .finally(() => {
          this.loading = false;
        });
    },
    getColor(level) {
      if (level >= 400) {
        return "red";
      }

      if (level >= 250) {
        return "orange";
      }

      if (level >= 200) {
        return "green";
      }

      return "grey";
    }
  },
  filters: {
    formatMessage(item) {
      return item.message.replace(/\{(.*?)\}/g, (variable, value) => {
        if (value in item.context) {
          return `<strong>${item.context[value]}</strong>`;
        }

        if (value in item.extra) {
          return `<strong>${item.extra[value]}</strong>`;
        }

        return variable;
      });
    }
  }
};
</script>

<style scoped></style>
