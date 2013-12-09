// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  waitSeconds: 5,

  paths: {
     facebook: [
        "//connect.facebook.net/fr_FR/all",
        "../vendor/lib/fr-fb"
     ],
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore.min",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "../vendor/js/bootstrap.min",
     "hmacsha1": "../vendor/js/hmac-sha1",
     "pinterest": [
        "//assets.pinterest.com/js/pinit",
        "../vendor/lib/pinit"
     ],
     "select2": "../vendor/js/select2/select2",
     "select2fr": "../vendor/js/select2/select2_locale_fr",
     "jquery.fileupload": "../vendor/js/jquery.fileupload",
     "jquery.iframe-transport": "../vendor/js/jquery.iframe-transport",
     "jquery.ui.widget": "../vendor/js/jquery.ui.widget",
     "moment": "../vendor/js/moment.min",
     "typeahead": "../vendor/js/bootstrap3-typeahead.min"
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
     }
  }

   //,urlArgs: "bust=" + Number(new Date())

});
