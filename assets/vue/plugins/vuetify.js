import "@fortawesome/fontawesome-free/css/all.css";
import Vue from "vue";
import Vuetify from "vuetify/lib";

Vue.use(Vuetify);

let applicationDarkMode;
try {
  applicationDarkMode = localStorage.getItem("dark-mode") === "true";
} catch (err) {
  applicationDarkMode = false;
}

export default new Vuetify({
  icons: {
    iconfont: "fa"
  },
  theme: {
    dark: applicationDarkMode
  }
});
