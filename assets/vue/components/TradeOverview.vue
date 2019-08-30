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
      values: [
        {
          id: 14,
          symbol: "USDT-BTC",
          quantity: 1.2,
          stoploss: 12111,
          takeprofits: 3
        },
        {
          id: 15,
          symbol: "USDT-ETH",
          quantity: 24,
          stoploss: 219.45,
          takeprofits: 2
        }
      ]
    };
  }
};
</script>

<style scoped></style>
