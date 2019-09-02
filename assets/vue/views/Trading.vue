<template>
  <div>
    <trade-dialog />
    <v-row>
      <trade-overview
        @tradeCreate="getActiveTrades"
        :loading="overviewLoading"
        :trades="trades"
      />
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
import TradeDialog from "../components/TradeDialog";
import TradeOverview from "../components/TradeOverview";

export default {
  name: "Trading",
  components: { TradeOverview, TradeDialog },
  data() {
    return {
      trades: [],
      overviewLoading: false
    };
  },
  mounted() {
    this.getActiveTrades();
  },
  methods: {
    async getActiveTrades() {
      this.overviewLoading = true;

      try {
        this.overviewLoading = false;
        const result = await axios.get("/api/v1/trade");
        this.trades = result.data;
      } catch (error) {
        this.overviewLoading = false;
      }
    }
  }
};
</script>

<style scoped></style>
