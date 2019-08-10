import Vue from "vue";
import vuetify from "../vue/plugins/vuetify";
import router from "../vue/plugins/router";
import App from "../vue/components/App";

require("../css/app.css");

// eslint-disable-next-line no-new
new Vue({
  vuetify,
  router,
  el: "#app",
  components: { App }
});
