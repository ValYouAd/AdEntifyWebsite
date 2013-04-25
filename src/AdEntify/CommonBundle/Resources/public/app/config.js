// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  paths: {
     facebook: "http://connect.facebook.net/fr_FR/all",
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "../vendor/js/bootstrap.min"
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
           "jquery"
        ]
     },
     "Backbone": {
        "deps": [
           "facebook",
           "bootstrap"
        ]
     },
     "jquery-ui": {
        "deps": [
           "jquery"
        ]
     }
  }

});
