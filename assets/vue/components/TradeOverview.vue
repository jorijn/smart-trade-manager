<template>
  <v-col>
    <v-card :loading="loading">
      <v-toolbar color="secondary lighten-1" dark>
        <v-toolbar-title>Trades Overview</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon right @click="$emit('refresh-trade-list')">
          <v-icon>fas fa-sync-alt</v-icon>
        </v-btn>
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
          <template v-slot:expanded-item="{ headers, item }">
            <td class="pa-0 mr-0" :colspan="headers.length">
              <trade-details
                :orders="item.orders"
                :asset="item.symbol.baseAsset"
                :quoteAsset="item.symbol.quoteAsset"
                :symbol="item.symbol"
              ></trade-details>
            </td>
          </template>
          <template v-slot:item.quantity="{ item }">
            {{ item.quantity | roundStep(item.symbol) }}
            {{ item.symbol.quoteAsset }}
          </template>
          <template v-slot:item.buy_orders="{ item }">
            <span v-if="item.buy_orders_quantity > 0">
              {{ item.buy_orders_filled }} / {{ item.buy_orders_quantity }}
              {{ item.symbol.baseAsset }} ({{
                (
                  (item.buy_orders_filled / item.buy_orders_quantity) *
                  100
                ).toFixed(0)
              }}%)
            </span>
            <span v-else>-</span>
          </template>
          <template v-slot:item.takeprofits="{ item }">
            <div v-if="item.takeProfits.length > 0">
              <div
                class=""
                :key="price"
                v-for="{ percentage, price } in item.takeProfits"
              >
                <span
                  >Sell <strong>{{ percentage }}%</strong> at
                  <strong>{{ price | roundTicks(item.symbol) }}</strong>
                  {{ item.symbol.quoteAsset }}</span
                >
              </div>
            </div>
            <span v-else>-</span>
          </template>
          <template v-slot:item.stoploss="{ item }">
            <span v-if="item.stoploss">
              {{ item.stoploss | roundTicks(item.symbol) }}
              {{ item.symbol.quoteAsset }}
            </span>
            <span v-else>-</span>
          </template>
          <template v-slot:item.entry="{ item }">
            <span v-if="item.entryHigh">
              {{ item.entryLow | roundTicks(item.symbol) }} &mdash;
              {{ item.entryHigh | roundTicks(item.symbol) }}
              {{ item.symbol.quoteAsset }}
            </span>
            <span v-else>
              {{ item.entryLow | roundTicks(item.symbol) }}
              {{ item.symbol.quoteAsset }}
            </span>
          </template>
          <template v-slot:item.symbol="{ item }">
            <span v-if="'symbol' in item.symbol">
              {{ item.symbol.symbol }}
            </span>
          </template>
          <template v-slot:item.actions="{ item }">
            <v-menu bottom left>
              <template v-slot:activator="{ on }">
                <v-btn icon v-on="on">
                  <v-icon>fas fa-ellipsis-v</v-icon>
                </v-btn>
              </template>

              <v-list>
                <v-list-item>
                  <v-list-item-title>Update Stop Loss</v-list-item-title>
                </v-list-item>
                <v-divider></v-divider>
                <v-list-item>
                  <v-list-item-title>Close Trade</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
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
import TradeDetails from "./TradeDetails";

export default {
  name: "TradeOverview",
  components: { TradeDetails },
  data() {
    return {
      headers: [
        { value: "id", text: "ID" },
        { value: "symbol", text: "Symbol", sortable: false, align: "right" },
        {
          value: "quantity",
          text: "Quantity",
          sortable: false,
          align: "right"
        },
        {
          value: "stoploss",
          text: "StopLoss",
          sortable: false,
          align: "right"
        },
        {
          value: "entry",
          text: "Entry (Range)",
          sortable: false,
          align: "right"
        },
        {
          value: "buy_orders",
          text: "Buy Orders Filled",
          sortable: false,
          align: "right"
        },
        {
          value: "takeprofits",
          text: "Take Profit",
          sortable: false,
          align: "right"
        },
        { value: "actions", text: null, sortable: false }
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
          ...item,
          symbol,
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
