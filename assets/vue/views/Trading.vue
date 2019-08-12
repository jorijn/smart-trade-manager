<template>
  <v-layout row wrap px-2>
    <v-flex md4 pa-1 px-2>
      <trade-dialog />
    </v-flex>
    <v-flex md8 pa-1 px-2>
      <trade-overview />
    </v-flex>
  </v-layout>
</template>

<script>
import TradeDialog from "../components/TradeDialog";
import TradeOverview from "../components/TradeOverview";

export default {
  name: "Trading",
  components: { TradeOverview, TradeDialog }
};
</script>

<style scoped></style>
