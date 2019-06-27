var Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    //.cleanupOutputBeforeBuild()
    //.enableSourceMaps(!Encore.isProduction())
    // uncomment to create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // uncomment to define the assets of the project
    .addEntry('global', './assets/js/global.js')
    .addEntry('like', './assets/js/likepost.js')
    .addEntry('comment', './assets/js/loadcomment.js')
    .addEntry('loginjs', './assets/js/login.js')
    .addEntry('postjs', './assets/js/post.js')
    .addEntry('questionjs', './assets/js/question.js')
    .addEntry('admin', './assets/js/admin.js')


    .addStyleEntry('login', './assets/css/login.css')
    .addStyleEntry('post', './assets/css/post.css')
    //.addStyleEntry('admin', './assets/css/admin.css')

        //for img
    .addPlugin(new CopyWebpackPlugin([
        // copies to {output}/static
        { from: './assets/static', to: 'static' }
    ]))

    // uncomment if you use Sass/SCSS files
    // .enableSassLoader()
    .enableVueLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()
    .enableBuildNotifications()
;

module.exports = Encore.getWebpackConfig();
