// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  paths: {
     facebook: "http://connect.facebook.net/fr_FR/all"
  },

  shim: {
    // Put shims here.
  }

});
