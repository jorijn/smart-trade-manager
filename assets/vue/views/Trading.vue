<template>
  <div>
      <trade-dialog />
    <v-row>
      <trade-overview />
    </v-row>
  </div>
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
