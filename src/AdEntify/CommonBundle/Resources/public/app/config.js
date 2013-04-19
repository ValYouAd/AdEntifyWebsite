// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  paths: {
     facebook: "http://connect.facebook.net/fr_FR/all",
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore"
  },

   map: {
      // Ensure Lo-Dash is used instead of underscore.
      "*": { "underscore": "lodash" }
   },

  shim: {
    // Put shims here.
  }

});
