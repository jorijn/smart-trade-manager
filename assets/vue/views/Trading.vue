<template>
  <div>
    <trade-dialog v-on:trade-created="getActiveTrades" :symbols="symbols" />
    <v-row>
      <trade-overview
        @tradeCreate="getActiveTrades"
        v-on:refresh-trade-list="getActiveTrades"
        v-on:close-trade="closeTrade"
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
      overviewLoading: false,
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
        const result = await axios.get("/api/v1/trade");
        this.overviewLoading = false;
        this.trades = result.data
          .sort((a, b) => Math.sign(parseFloat(b.id) - parseFloat(a.id)))
          .map(order => {
            order.orders = order.orders.sort((a, b) =>
              Math.sign(parseFloat(b.orderId) - parseFloat(a.orderId))
            );
            return order;
          });
      } catch (error) {
        this.overviewLoading = false;
      }
    },
    async closeTrade(id) {
      try {
        this.overviewLoading = true;
        await axios.get(`/api/v1/trade/${id}/close`);

        const index = this.trades.findIndex(t => t.id === id);
        if (index !== -1) {
          this.trades.splice(index, 1);
        }

        this.overviewLoading = false;
      } catch (error) {
        this.overviewLoading = false;
      }
    }
  }
};
</script>

<style scoped></style>
