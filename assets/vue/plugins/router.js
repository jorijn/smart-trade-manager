import Vue from "vue";
import Router from "vue-router";
import Trading from "../views/Trading.vue";

Vue.use(Router);

export default new Router({
  mode: "history",
  base: process.env.BASE_URL,
  routes: [
    {
      path: "/",
      redirect: { name: "trading" }
    },
    {
      path: "/trading",
      name: "trading",
      component: Trading
    },
    {
      path: "/about",
      name: "about",
      component: () => import("../views/About.vue")
    }
  ]
});
