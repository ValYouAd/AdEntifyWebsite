// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  paths: {
     facebook: "//connect.facebook.net/fr_FR/all",
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "../vendor/js/bootstrap.min",
     "hmacsha1": "../vendor/js/hmac-sha1",
     "pinterest": "//assets.pinterest.com/js/pinit",
     "i18next": "i18next.amd.withJQuery-1.6.3.min"
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
     "i18next": {
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
           "i18next"
        ]
     },
     "jquery-ui": {
        "deps": [
           "jquery"
        ]
     }
  }

});
