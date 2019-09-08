import Vue from "vue";
import vuetify from "../vue/plugins/vuetify";
import router from "../vue/plugins/router";
import App from "../vue/components/App.vue";

import {
  getStepSize,
  getTickSize,
  roundStep,
  roundTicks
} from "../vue/exchange-helpers";

require("../css/app.css");

Vue.filter("roundTicks", (value, symbol) => {
  return roundTicks(value, getTickSize(symbol));
});

Vue.filter("roundStep", (value, symbol) => {
  return roundStep(value, getStepSize(symbol));
});

// eslint-disable-next-line no-new
new Vue({
  vuetify,
  router,
  el: "#app",
  components: { App }
});
