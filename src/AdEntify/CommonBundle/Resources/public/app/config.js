// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  paths: {
     facebook: "//connect.facebook.net/fr_FR/all",
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore.min",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "../vendor/js/bootstrap.min",
     "hmacsha1": "../vendor/js/hmac-sha1",
     "pinterest": "//assets.pinterest.com/js/pinit",
     "select2": "../vendor/js/select2/select2",
     "select2fr": "../vendor/js/select2/select2_locale_fr"
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
           "bootstrap",
           "i18next2"
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
