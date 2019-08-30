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
    sourceType: "module"
  },
  plugins: ["vue", "vuetify"],
  rules: {
    "vuetify/no-deprecated-classes": "error",
    "vuetify/no-legacy-grid": "error"
  },
  "settings": {
    "import/resolver": {
      "node": {
        "paths": ["assets"]
      }
    }
  }
};
