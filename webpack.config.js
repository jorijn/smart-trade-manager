const Encore = require("@symfony/webpack-encore");
const VuetifyLoaderPlugin = require("vuetify-loader/lib/plugin");

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore.setOutputPath("public/build/")
  .setPublicPath("/build")
  .addEntry("app", "./assets/js/app.js")
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .configureBabel(() => {}, {
    useBuiltIns: "usage",
    corejs: 3
  })
  .enableIntegrityHashes(Encore.isProduction())
  .enableVueLoader()
  .addPlugin(new VuetifyLoaderPlugin())
  .enableSassLoader(options => {
    options.implementation = require("sass");
    options.fiber = require("fibers");
  });

module.exports = Encore.getWebpackConfig();
