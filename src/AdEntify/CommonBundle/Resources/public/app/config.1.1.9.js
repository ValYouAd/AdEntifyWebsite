// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  waitSeconds: 30,

  paths: {
     facebook: [
        "//connect.facebook.net/fr_FR/all",
        "../vendor/lib/fr-fb"
     ],
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore.min",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min",
     "hmacsha1": "../vendor/js/hmac-sha1",
     "pinterest": [
        "//assets.pinterest.com/js/pinit",
        "../vendor/lib/pinit"
     ],
     "select2": "//cdn.jsdelivr.net/select2/3.4.6/select2.min",
     "select2fr": "//cdn.jsdelivr.net/select2/3.4.6/select2_locale_fr",
     "jquery.fileupload": "../vendor/js/jquery.fileupload",
     "jquery.iframe-transport": "../vendor/js/jquery.iframe-transport",
     "jquery.ui.widget": "../vendor/js/jquery.ui.widget",
     "moment": "//cdn.jsdelivr.net/momentjs/2.5.1/moment-with-langs.min",
     "typeahead": "../vendor/js/bootstrap3-typeahead.min",
     "bday-picker": "../vendor/js/bday-picker.min",
     "jquery.serializeJSON": "../vendor/js/jquery.serializeJSON.min",
     "Chart": "//cdn.jsdelivr.net/chart.js/0.2/Chart.min",
     "daterangepicker": "../vendor/js/daterangepicker",
     "introjs": "../vendor/js/intro.min",
     "i18next": "//cdn.jsdelivr.net/i18next/1.7.1/i18next.amd.withJQuery.min"
  },

   map: {
      // Ensure Lo-Dash is used instead of underscore.
      "*": { "underscore": "lodash" }
   },

  shim: {
     "infinitescroll": {
        "deps": [
           "jquery"
        ]
     },
     "typeahead" : {
        "deps": [
           "jquery"
        ]
     },
     "bootstrap": {
        "deps": [
           "jquery-ui",
           "jquery"
        ]
     },
     "Backbone": {
        "deps": [
           "jquery",
           "facebook",
           "bootstrap"
        ]
     },
     "jquery-ui": {
        "deps": [
           "jquery"
        ]
     },
     "select2": {
        "deps": [
           "jquery"
        ]
     },
     "select2fr": {
        "deps": [
           "jquery"
        ]
     },
     "bday-picker": {
        "deps": [
           "jquery"
        ]
     },
     "jquery.serializeJSON": {
        "deps": [
            "jquery"
        ]
     },
     "Chart": {
        "deps": ["jquery"]
     },
     "daterangepicker": {
        "deps": ["jquery", "moment"]
     }
  },

   urlArgs: "v=1.1.9"

});
