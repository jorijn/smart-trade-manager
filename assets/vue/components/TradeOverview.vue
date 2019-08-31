<template>
  <v-col>
    <v-card>
      <v-toolbar color="secondary lighten-1" dark>
        <v-toolbar-title>Trades Overview</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="values"
          item-key="value"
        ></v-data-table>
      </v-card-text>
    </v-card>
  </v-col>
</template>

<script>
import axios from "axios";

export default {
  name: "TradeOverview",
  data() {
    return {
      headers: [
        { value: "id", text: "ID" },
        { value: "symbol", text: "Symbol" },
        { value: "quantity", text: "Quantity" },
        { value: "stoploss", text: "StopLoss" },
        { value: "takeprofits", text: "Take Profits" }
      ],
      values: []
    };
  },
  mounted() {
    this.getActiveTrades();
  },
  methods: {
    async getActiveTrades() {
      const result = await axios.get("/api/v1/trade");

      // TODO process
    }
  }
};
</script>

<style scoped></style>
