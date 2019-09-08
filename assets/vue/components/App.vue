<template>
  <v-app>
    <v-navigation-drawer v-model="drawer" app>
      <v-list>
        <v-list-item to="/trading">
          <v-list-item-action>
            <v-icon>fas fa-home</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Trading</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item to="/logging">
          <v-list-item-action>
            <v-icon>fas fa-paper-plane</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>Logging</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-list-item to="/about">
          <v-list-item-action>
            <v-icon>fas fa-address-card</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>About</v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>
    <v-app-bar
      app
      prominent
      color="primary darken-4"
      :src="require('../../img/nav-bar-background.jpg')"
      dark
      shrink-on-scroll
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Smart Trade Manager</v-toolbar-title>
    </v-app-bar>

    <v-content>
      <v-container>
        <router-view></router-view>
      </v-container>
    </v-content>
  </v-app>
</template>

<script>
export default {
  name: "app",
  data: () => ({
    drawer: null
  })
};
</script>

<style scoped></style>
