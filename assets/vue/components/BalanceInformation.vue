<template>
  <v-tooltip bottom>
    <template v-slot:activator="{ on }">
      <v-subheader v-on="on">
        <strong>Available:</strong>&nbsp;<span>{{ free }} {{ quoteLabel }}</span>
      </v-subheader>
    </template>
    <span
      >Free: {{ free }} {{ quoteLabel }}, locked: {{ locked }}
      {{ quoteLabel }} (Account: {{ accountValue }}
      {{ accountValueQuoteLabel }})</span
    >
  </v-tooltip>
</template>

<script>
export default {
  name: "BalanceInformation",
  props: {
    free: String,
    locked: String,
    accountValue: String,
    accountValueQuoteLabel: String,
    quoteLabel: String
  }
};
</script>

<style scoped></style>
