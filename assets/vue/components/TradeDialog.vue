<template>
  <div>
    <v-card>
      <v-toolbar dense color="red lighten-1" dark>
        <v-toolbar-title>Create a new trade</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-card-text>
        <v-autocomplete
          v-model="symbol"
          label="Symbol"
          :items="symbols"
        ></v-autocomplete>
        <v-text-field
          v-model="quantity"
          label="Quantity"
          required
        >
          <template slot="append">
            <v-btn small depressed outlined rounded color="secondary" text>25%</v-btn>
            <v-btn small depressed outlined rounded color="secondary" text>50%</v-btn>
            <v-btn small depressed outlined rounded color="secondary" text>75%</v-btn>
            <v-btn small depressed outlined rounded color="secondary" text>100%</v-btn>
          </template>
        </v-text-field>
      </v-card-text>
    </v-card>

    <v-card>
      <v-toolbar dense color="primary lighten-1" dark>
        <v-toolbar-title>Stop Loss</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-switch v-model="stoplossEnabled"></v-switch>
      </v-toolbar>
      <v-card-text :disabled="stoplossEnabled">
        <v-text-field v-model="stoploss" label="Price"></v-text-field>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
export default {
  name: "TradeDialog",
  data: () => {
    return {
      valid: true,
      symbol: null,
      stoploss: null,
      stoplossEnabled: false,
      quantity: 0,
      symbols: ["USDT-BTC", "BTC-ETH", "ETH-XRP"]
    };
  }
};
</script>

<style scoped></style>
