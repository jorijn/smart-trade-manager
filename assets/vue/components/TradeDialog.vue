<template>
  <div>
    <v-row align="stretch">
      <v-col cols="12" md="4" lg="4" xl="4">
        <v-card class="fill-height">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Symbol & Quantity</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text>
            <v-autocomplete
              v-model="symbol"
              label="Symbol"
              :items="symbols.map(item => item.symbol)"
              :loading="symbolsLoading"
            ></v-autocomplete>
            <v-text-field
              :label="ladderMode ? 'Range Low Price' : 'Limit Price'"
              append-icon="fas fa-arrows-alt-v"
              @click:append="toggleLadderMode"
              type="number"
              v-model="rangeLow"
              required
              :rules="[rules.required]"
              :suffix="this.isValidSymbol ? symbolObject.quoteAsset : null"
            >
            </v-text-field>
            <v-text-field
              v-if="ladderMode"
              type="number"
              label="Range High Price"
              v-model="rangeHigh"
              :rules="[rules.required]"
              required
              :suffix="this.isValidSymbol ? symbolObject.quoteAsset : null"
            >
            </v-text-field>
            <v-text-field
              v-model="quantity"
              type="number"
              label="Quantity"
              required
              :rules="[rules.required]"
              persistent-hint
              :suffix="this.isValidSymbol ? symbolObject.quoteAsset : null"
              :hint="
                this.isValidSymbol
                  ? `Available: ${quoteBalanceFree} ${symbolObject.quoteAsset}`
                  : null
              "
            >
            </v-text-field>
            <div class="mt-3">
              <v-btn small color="primary" @click="setQuantity(0.25)"
                >25%</v-btn
              >
              <v-btn small color="primary" @click="setQuantity(0.5)">50%</v-btn>
              <v-btn small color="primary" @click="setQuantity(0.75)"
                >75%</v-btn
              >
              <v-btn small color="primary" @click="setQuantity(1)">100%</v-btn>
            </div>
            <v-switch
              v-model="respectMaximumLoss"
              label="Maximum Loss Protection"
              :hint="
                `This will prevent the creation of a trade if it puts your portfolio at more risk than the configured ${portfolioLossThreshold}%.`
              "
              persistent-hint
            ></v-switch>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="4" xl="4">
        <v-card class="fill-height">
          <v-toolbar color="primary" dark>
            <v-toolbar-title>Take Profit</v-toolbar-title>
            <v-spacer></v-spacer>
          </v-toolbar>
          <v-card-text v-if="isValidSymbol && takeProfits.length > 0">
            <v-chip
              class="mr-2 mb-2"
              close
              :key="price"
              v-for="{ percentage, price } in takeProfits"
              @click:close="removeTakeProfit(percentage, price)"
            >
              <span
                >Sell <strong>{{ percentage }}%</strong> at
                <strong>{{ price }}</strong> {{ symbolObject.quoteAsset }}</span
              >
            </v-chip>
          </v-card-text>
          <v-divider
            v-if="
              takeProfits.length > 0 && this.totalPercentageTakeProfit < 100
            "
          ></v-divider>
          <div v-if="this.totalPercentageTakeProfit < 100">
            <v-card-text>
              <v-text-field
                label="Price"
                type="number"
                v-model="takeProfitPrice"
                :rules="[this.validatePriceAboveEntry]"
                :suffix="this.isValidSymbol ? symbolObject.quoteAsset : null"
              ></v-text-field>
              <v-text-field
                label="Percentage"
                type="number"
                v-model="takeProfitPercentage"
                :rules="[rules.percentage, this.validatePercentageTakeProfit]"
              ></v-text-field>
              <p>
                <v-btn
                  color="primary"
                  block
                  @click="addTakeProfit"
                  :disabled="!addTakeProfitEnabled || !isValidSymbol"
                  >Add Take Profit
                </v-btn>
              </p>
            </v-card-text>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="4" lg="4" xl="4">
        <v-card class="fill-height">
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
              type="number"
              :disabled="!stoplossEnabled"
              v-model="stoplossPrice"
              label="Stop Loss Price"
              required
              :rules="[rules.required, this.validatePriceBelowEntry]"
              :suffix="this.isValidSymbol ? symbolObject.quoteAsset : null"
            >
            </v-text-field>
            <v-text-field
              type="number"
              :disabled="true"
              :value="this.stoplossPercentage"
              label="Risk %"
              required
              suffix="%"
            >
            </v-text-field>
            <v-text-field
              type="number"
              :disabled="true"
              :value="this.portfolioRiskPercentage"
              label="Portfolio Risk %"
              required
              suffix="%"
            ></v-text-field>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <v-row align="center" justify="center" class="py-4">
      <v-btn
        :color="this.confirmation ? 'warning' : 'primary'"
        x-large
        rounded
        @click="startTrade"
        :disabled="!createTradeEnabled"
        :loading="loading"
      >
        <v-icon left v-if="!this.confirmation">fas fa-plus</v-icon>
        <v-icon left v-else>fas fa-exclamation-triangle</v-icon>
        <span v-if="!this.confirmation">
          Create Trade
        </span>
        <span v-else>
          I am sure, create trade
        </span>
      </v-btn>
    </v-row>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "TradeDialog",
  data: () => {
    return {
      confirmation: false,
      accountValue: {},
      respectMaximumLoss: true,
      symbol: null,
      stoplossPrice: null,
      stoplossEnabled: false,
      symbolsLoading: false,
      quantity: null,
      takeProfitPrice: null,
      takeProfitPercentage: null,
      quoteBalanceFree: 0,
      quoteBalanceLocked: 0,
      currentPrice: null,
      symbolObject: {},
      loading: false,
      rangeLow: null,
      rangeHigh: null,
      ladderMode: false,
      takeProfits: [],
      portfolioLossThreshold: 0,
      rules: {
        required: value => !!value || "Required.",
        percentage: value => {
          if (value === null || value.length === 0) {
            return true;
          }

          return value > 0 && value <= 100
            ? true
            : "Percentage should be between 0 and 100%";
        }
      }
    };
  },
  methods: {
    async startTrade() {
      if (!this.confirmation) {
        this.$nextTick(() => {
          this.confirmation = true;
        });
        return;
      }

      this.confirmation = false;

      this.loading = true;
      const trade = this.compileTrade();

      try {
        await axios.post("/api/v1/trade", trade);
        this.loading = false;

        this.$emit("trade-created");
      } catch (error) {
        this.loading = false;
      }
    },
    getMaxBalance() {
      if (this.respectMaximumLoss) {
        const entryPrice = this.getBaseEntryPrice();
        if (
          this.stoplossPrice === null ||
          this.stoplossPrice.length === 0 ||
          entryPrice === null
        ) {
          return null;
        }

        let maxLossPercentage =
          parseFloat(this.portfolioLossThreshold) /
          100 /
          (1 - parseFloat(this.stoplossPrice) / entryPrice);

        if (maxLossPercentage > 1) {
          maxLossPercentage = 1; // can't risk more than 100% of the portfolio
        }

        const maxLossAmount =
          parseFloat(this.accountValue[this.symbolObject.quoteAsset]) *
          maxLossPercentage;

        return maxLossAmount > parseFloat(this.quoteBalanceFree)
          ? parseFloat(this.quoteBalanceFree)
          : maxLossAmount;
      }

      return this.quoteBalanceFree;
    },
    setQuantity(percentage) {
      const maxBalance = this.getMaxBalance();
      if (maxBalance === null) {
        this.quantity = null;
      }

      this.quantity = (maxBalance * percentage).toFixed(8);
    },
    validatePercentageTakeProfit(value) {
      if (value === null || value.length === 0) {
        return true;
      }

      return this.totalPercentageTakeProfit + parseFloat(value) <= 100
        ? true
        : "Total percentage for Take Profit cannot exceed 100%";
    },
    validatePriceAboveEntry(value) {
      const floatValue = parseFloat(value);

      return value === null ||
        value.length === 0 ||
        floatValue > this.getBaseEntryPrice()
        ? true
        : "Price should be above (average) entry price";
    },
    validatePriceBelowEntry(value) {
      const floatValue = parseFloat(value);

      return value === null ||
        value.length === 0 ||
        floatValue < this.getBaseEntryPrice()
        ? true
        : "Price should be below (average) entry price";
    },
    toggleLadderMode() {
      this.ladderMode = !this.ladderMode;
    },
    addTakeProfit() {
      const existingIndex = this.takeProfits.findIndex(
        o => o.price === parseFloat(this.takeProfitPrice)
      );

      if (existingIndex >= 0) {
        this.takeProfits[existingIndex].percentage += parseFloat(
          this.takeProfitPercentage
        );
      } else {
        this.takeProfits.push({
          percentage: parseFloat(this.takeProfitPercentage),
          price: parseFloat(this.takeProfitPrice)
        });
      }

      this.takeProfits.sort((a, b) =>
        Math.sign(parseFloat(b.price) - parseFloat(a.price))
      );

      this.takeProfitPercentage = null;
      this.takeProfitPrice = null;
    },
    removeTakeProfit(percentage, price) {
      const index = this.takeProfits.findIndex(
        o => o.percentage === percentage && o.price === price
      );

      if (index !== -1) {
        this.takeProfits.splice(index, 1);
      }
    },
    getBaseEntryPrice() {
      if (!this.ladderMode) {
        return this.rangeLow === null || this.rangeLow.length === 0
          ? null
          : parseFloat(this.rangeLow);
      }

      if (
        this.rangeHigh === null ||
        this.rangeHigh.length === 0 ||
        this.rangeLow === null ||
        this.rangeLow.length === 0
      ) {
        return null;
      }

      return (parseFloat(this.rangeLow) + parseFloat(this.rangeHigh)) / 2;
    },
    compileTrade() {
      const trade = {
        entryLow: this.rangeLow,
        symbol: this.symbol,
        quantity: this.quantity
      };

      if (this.ladderMode) {
        trade.entryHigh = this.rangeHigh;
      }

      if (this.stoplossEnabled) {
        trade.stoploss = this.stoplossPrice;
      }

      if (this.takeProfits.length > 0) {
        trade.takeProfits = this.takeProfits;
      }

      return { trade };
    }
  },
  computed: {
    totalPercentageTakeProfit() {
      return this.takeProfits.reduce(
        (accumulator, currentValue) => accumulator + currentValue.percentage,
        0
      );
    },
    isValidSymbol() {
      return "quoteAsset" in this.symbolObject;
    },
    addTakeProfitEnabled() {
      return this.takeProfitPercentage && this.takeProfitPrice;
    },
    createTradeEnabled() {
      const baseCriteria =
        parseFloat(this.quantity) > 0 &&
        this.isValidSymbol &&
        parseFloat(this.rangeLow) > 0;

      let additionalCriteria = true;
      if (this.respectMaximumLoss) {
        additionalCriteria =
          this.portfolioRiskPercentage !== null &&
          this.portfolioRiskPercentage.length > 0 &&
          parseFloat(this.portfolioRiskPercentage) <=
            parseFloat(this.portfolioLossThreshold);
      }

      return baseCriteria && additionalCriteria;
    },
    stoplossPercentage() {
      const entryPrice = this.getBaseEntryPrice();

      if (
        this.stoplossPrice === null ||
        this.stoplossPrice.length === 0 ||
        entryPrice === null
      ) {
        return null;
      }

      return ((1 - parseFloat(this.stoplossPrice) / entryPrice) * 100).toFixed(
        5
      );
    },
    portfolioRiskPercentage() {
      const entryPrice = this.getBaseEntryPrice();

      if (
        this.stoplossPrice === null ||
        this.stoplossPrice.length === 0 ||
        entryPrice === null ||
        this.quantity === null ||
        this.quantity.length === 0
      ) {
        return null;
      }

      return (
        ((parseFloat(this.quantity) -
          parseFloat(this.quantity) *
            (parseFloat(this.stoplossPrice) / entryPrice)) /
          parseFloat(this.accountValue[this.symbolObject.quoteAsset])) *
        100
      ).toFixed(5);
    }
  },
  async mounted() {
    const accountValue = await axios.get(`/api/v1/account/value`);
    this.accountValue = accountValue.data;

    if (
      typeof window.options !== "undefined" &&
      "portfolio_loss_threshold" in window.options
    ) {
      this.portfolioLossThreshold = window.options.portfolio_loss_threshold;
    }
  },
  props: {
    symbols: {
      type: Array,
      default: () => [],
      required: true
    }
  },
  watch: {
    async symbol() {
      this.symbolsLoading = true;
      const response = await axios.get(`/api/v1/symbol/${this.symbol}`);
      this.symbolsLoading = false;

      this.symbolObject = response.data.symbol;
      this.quoteBalanceFree = response.data.balance_free;
      this.quoteBalanceLocked = response.data.balance_locked;
      this.currentPrice = parseFloat(response.data.price);

      this.rangeLow = this.currentPrice;
    }
  }
};
</script>

<style scoped></style>
