<template>
  <v-data-table
    :footer-props="{ 'items-per-page-options': [-1] }"
    :hide-default-footer="true"
    :headers="headers"
    :items="orders"
    group-by="side"
  >
    <template v-slot:item.updatedAt="{ item }">
      {{ item.updatedAt | fromNow }}
    </template>
    <template v-slot:item.filledQuantity="{ item }">
      {{ item.filledQuantity | roundStep(symbol) }}
      {{ symbol.baseAsset }} ({{
        item.filledQuoteQuantity | roundStep(symbol)
      }}
      {{ symbol.quoteAsset }})
    </template>
    <template v-slot:item.price="{ item }">
      {{ item.price | roundTicks(symbol) }} {{ symbol.quoteAsset }}
    </template>
    <template v-slot:item.stopPrice="{ item }">
      <span v-if="item.stopPrice"
        >{{ item.stopPrice | roundTicks(symbol) }} {{ symbol.quoteAsset }}</span
      >
    </template>
  </v-data-table>
</template>

<script>
import moment from "moment";

export default {
  name: "TradeDetails",
  props: {
    orders: {
      type: Array,
      default: () => []
    },
    symbol: {
      type: Object,
      required: true
    }
  },
  computed: {
    headers() {
      return [
        { value: "orderId", text: "Order ID", align: "right" },
        {
          value: "filledQuantity",
          text: "Filled",
          align: "right"
        },
        { value: "price", text: "Price", align: "right" },
        { value: "side", text: "Side", align: "right" },
        { value: "status", text: "Status", align: "right" },
        { value: "stopPrice", text: "Stop Price", align: "right" },
        { value: "updatedAt", text: "Updated At" }
      ];
    }
  },
  filters: {
    fromNow(value) {
      return moment(parseFloat(value)).fromNow();
    }
  }
};
</script>

<style scoped></style>
