module.exports = {
  env: {
    browser: true,
    es6: true
  },
  extends: [
    "plugin:vue/essential",
    "airbnb-base",
    "plugin:prettier/recommended"
  ],
  globals: {
    Atomics: "readonly",
    SharedArrayBuffer: "readonly"
  },
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module",
    parser: "babel-eslint"
  },
  plugins: ["vue", "vuetify"],
  settings: {
    "import/resolver": {
      node: {
        paths: ["assets/vue"]
      }
    }
  }
};
