<template>
  <div>
    <trade-dialog :symbols="symbols" />
    <v-row>
      <trade-overview
        @tradeCreate="getActiveTrades"
        :symbols="symbols"
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
      overviewLoading: true,
      symbols: []
    };
  },
  async mounted() {
    await this.getSymbols();

    this.getActiveTrades();
  },
  methods: {
    async getSymbols() {
      const symbols = await axios.get(`/api/v1/symbol`);
      this.symbols = symbols.data;
    },
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
