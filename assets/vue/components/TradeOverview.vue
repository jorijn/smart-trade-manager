<template>
  <v-col>
    <v-card :loading="loading">
      <v-toolbar color="secondary lighten-1" dark>
        <v-toolbar-title>Trades Overview</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-toolbar>
      <v-card-text>
        <v-data-table
          :headers="headers"
          :items="tableData"
          show-expand
          single-expand
          item-key="id"
          sort-by="id"
          :sort-desc="true"
        >
          <template v-slot:expanded-item="{ headers }">
            <td :colspan="headers.length">Peek-a-boo!</td>
          </template>
          <template v-slot:item.quantity="{ item }">
            {{ item.quantity }} {{ item.symbol.quoteAsset }}
          </template>
          <template v-slot:item.buy_orders="{ item }">
            {{ item.buy_orders_filled }} / {{ item.buy_orders_quantity }}
            {{ item.symbol.baseAsset }} ({{
              (
                (item.buy_orders_filled / item.buy_orders_quantity) *
                100
              ).toFixed(0)
            }}%)
          </template>
          <template v-slot:item.takeprofits="{ item }">
            <v-chip
              class=""
              :key="price"
              v-for="{ percentage, price } in item.takeProfits"
            >
              <span
                >Sell <strong>{{ percentage }}%</strong> at
                <strong>{{ price }}</strong> {{ item.symbol.quoteAsset }}</span
              >
            </v-chip>
          </template>
          <template v-slot:item.stoploss="{ item }">
            <span v-if="!isNaN(item.stoploss)">
              {{ item.stoploss }} {{ item.symbol.quoteAsset }}
            </span>
          </template>
          <template v-slot:item.entry="{ item }">
            <span v-if="!isNaN(item.entryHigh)">
              {{ item.entryLow }} &mdash; {{ item.entryHigh }}
              {{ item.symbol.quoteAsset }}
            </span>
            <span v-else>
              {{ item.entryLow }} {{ item.symbol.quoteAsset }}
            </span>
          </template>
          <template v-slot:item.symbol="{ item }">
            <span v-if="'symbol' in item.symbol">
              {{ item.symbol.symbol }}
            </span>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>
  </v-col>
</template>

<script>
import {
  getStepSize,
  getTickSize,
  roundStep,
  roundTicks
} from "../exchange-helpers";

export default {
  name: "TradeOverview",
  data() {
    return {
      headers: [
        { value: "id", text: "ID" },
        { value: "symbol", text: "Symbol" },
        { value: "quantity", text: "Quantity" },
        { value: "stoploss", text: "StopLoss" },
        { value: "entry", text: "Entry (Range)" },
        { value: "buy_orders", text: "Buy Orders Filled" },
        { value: "takeprofits", text: "Take Profit" }
      ],
      values: []
    };
  },
  props: {
    trades: Array,
    loading: {
      type: Boolean,
      default: false
    },
    symbols: {
      type: Array,
      default: () => [],
      required: true
    }
  },
  computed: {
    tableData() {
      return this.trades.map(item => {
        const symbol = this.symbols.find(i => i.symbol === item.symbol);

        return {
          id: item.id,
          symbol,
          quantity: roundStep(parseFloat(item.quantity), getStepSize(symbol)),
          stoploss: roundTicks(parseFloat(item.stoploss), getTickSize(symbol)),
          entryLow: roundTicks(parseFloat(item.entryLow), getTickSize(symbol)),
          entryHigh: roundTicks(
            parseFloat(item.entryHigh),
            getTickSize(symbol)
          ),
          takeProfits: item.takeProfits.map(tp => {
            return {
              percentage: tp.percentage,
              price: roundTicks(parseFloat(tp.price), getTickSize(symbol))
            };
          }),
          buy_orders_filled: item.orders.reduce((total, i) => {
            if (i.side !== "BUY") {
              return total;
            }

            return total + parseFloat(i.filledQuantity);
          }, 0.0),
          buy_orders_quantity: item.orders.reduce((total, i) => {
            if (i.side !== "BUY") {
              return total;
            }

            return total + parseFloat(i.quantity);
          }, 0.0)
        };
      });
    }
  }
};
</script>

<style scoped></style>
