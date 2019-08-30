<template>
  <div>
    <v-row align="stretch">
      <v-col sm="12" md="4">
        <v-card class="fill-height" :loading="loading">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Symbol & Quantity</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <balance-information
            v-if="symbolObject !== null"
            :free="quoteBalanceFree"
            :locked="quoteBalanceLocked"
            :quote-label="symbolObject.quoteAsset"
            account-value="0"
            account-value-quote-label="USDT"
          ></balance-information>
          <v-card-text>
            <v-autocomplete
              v-model="symbol"
              label="Symbol"
              :items="symbols"
            ></v-autocomplete>
            <v-text-field v-model="quantity" label="Quantity" required>
            </v-text-field>
            <v-switch
              v-model="respectMaximumLoss"
              label="Respect maximum loss setting"
            ></v-switch>
            <div>
              <v-btn small color="accent">25%</v-btn>
              <v-btn small color="accent">50%</v-btn>
              <v-btn small color="accent">75%</v-btn>
              <v-btn small color="accent">100%</v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col sm="12" md="4"
        ><v-card class="fill-height" :loading="loading">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Stop Loss</v-toolbar-title>
            <v-spacer></v-spacer>
            <v-switch
              v-model="stoplossEnabled"
              hide-details
              label=""
            ></v-switch>
          </v-toolbar>
          <v-card-text>
            <v-text-field
              :disabled="!stoplossEnabled"
              v-model="stoploss"
              label="Stop Loss Price"
              required
            >
            </v-text-field>
          </v-card-text> </v-card
      ></v-col>
      <v-col sm="12" md="4"
        ><v-card class="fill-height" :loading="loading">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Take Profit</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text>
            <p>
              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci
              asperiores aspernatur cumque deleniti, eos eum illo magnam
              nesciunt optio placeat praesentium quaerat quos reprehenderit
              repudiandae tempora tempore vitae voluptas voluptate.
            </p>
          </v-card-text>
        </v-card></v-col
      >
    </v-row>
    <v-row align="center" justify="center" class="py-4">
      <v-btn
        color="primary"
        x-large
        rounded
        @click="
          () => {
            this.loading = true;
          }
        "
      >
        <v-icon left>fas fa-plus</v-icon>
        Create Trade
      </v-btn>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";
import BalanceInformation from "./BalanceInformation";

export default {
  name: "TradeDialog",
  components: { BalanceInformation },
  data: () => {
    return {
      respectMaximumLoss: true,
      symbol: null,
      stoploss: null,
      stoplossEnabled: false,
      quantity: 0,
      quoteBalanceFree: 0,
      quoteBalanceLocked: 0,
      accountValue: 0,
      symbolObject: null,
      symbols: ["BTCUSDT", "XRPUSDT", "XRPETH"],
      loading: false
    };
  },
  watch: {
    async symbol() {
      const response = await axios.get(`/api/v1/symbol/${this.symbol}`);

      this.symbolObject = response.data.symbol;
      this.quoteBalanceFree = response.data.balance_free;
      this.quoteBalanceLocked = response.data.balance_locked;
    }
  }
};
</script>

<style scoped></style>
