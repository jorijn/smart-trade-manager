/**
 * Count decimal places
 * @param {float} float - get the price precision point
 * @return {int} - number of place
 */
export function getPrecision(float) {
  if (!float || Number.isInteger(float)) return 0;
  return float.toString().split(".")[1].length || 0;
}

/**
 * rounds number with given step
 * @param {float} qty - quantity to round
 * @param {float} stepSize - stepSize as specified by exchangeInfo
 * @return {float} - number
 */
export function roundStep(qty, stepSize) {
  // Integers do not require rounding
  if (Number.isInteger(qty)) return qty;
  if (typeof qty === "string") qty = parseFloat(qty);
  const qtyString = qty.toFixed(16);
  const desiredDecimals = Math.max(stepSize.indexOf("1") - 1, 0);
  const decimalIndex = qtyString.indexOf(".");
  return parseFloat(qtyString.slice(0, decimalIndex + desiredDecimals + 1));
}

/**
 * rounds price to required precision
 * @param {number} price - price to round
 * @param {float} tickSize - tickSize as specified by exchangeInfo
 * @return {float} - number
 */
export function roundTicks(price, tickSize) {
  const formatter = new Intl.NumberFormat("en-US", {
    style: "decimal",
    minimumFractionDigits: 0,
    maximumFractionDigits: 8
  });
  const precision = formatter.format(tickSize).split(".")[1].length || 0;
  if (typeof price === "string") price = parseFloat(price);
  return price.toFixed(precision);
}

/**
 * Gets the sum of an array of numbers
 * @param {array} array - the number to add
 * @return {float} - sum
 */
export function sum(array) {
  return array.reduce((a, b) => a + b, 0);
}

export function getFilter(symbol, filter) {
  return symbol.filters.find(f => f.filterType === filter);
}

export function getStepSize(symbol) {
  return getFilter(symbol, "LOT_SIZE").parameters.stepSize;
}

export function getTickSize(symbol) {
  return getFilter(symbol, "PRICE_FILTER").parameters.tickSize;
}
