import Vue from "vue";
import vuetify from "../vue/plugins/vuetify";
import App from "../vue/components/App";

require("../css/app.css");

// eslint-disable-next-line no-new
new Vue({
  vuetify,
  el: "#app",
  components: { App }
});
