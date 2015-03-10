// Set the require.js configuration for your application.
require.config({

  // Initialize the application with the main application file and the JamJS
  // generated configuration file.
  deps: ["../vendor/jam/require.config", "main"],

  waitSeconds: 30,

  paths: {
     "jquery": "../vendor/js/jquery-1.11.2.min",
     "jquery.ui.widget": "../vendor/js/jquery-ui-1.10.4.custom.min",
     "facebook": "../vendor/lib/en-fb",
     "lodash": "../vendor/jam/lodash/dist/lodash.underscore.min",
     "infinitescroll": "../vendor/js/jquery.infinitescroll.min",
     "bootstrap": "../vendor/js/bootstrap.min",
     "hmacsha1": "../vendor/js/hmac-sha1",
     "pinterest": "../vendor/js/pinit",/*"//assets.pinterest.com/js/pinit"*/
     "select2": "../vendor/js/select2.min",
     "select2fr": "../vendor/js/select2_locale_fr",
     "jquery.fileupload": "../vendor/js/jquery.fileupload",
     "jquery.iframe-transport": "../vendor/js/jquery.iframe-transport",
     "moment": "../vendor/js/moment-with-langs.min",
     "typeahead": "../vendor/js/bootstrap3-typeahead.min",
     "bday-picker": "../vendor/js/bday-picker.min",
     "jquery.serializeJSON": "../vendor/js/jquery.serializeJSON.min",
     "Chart": "../vendor/js/Chart.min",
     "daterangepicker": "../vendor/js/daterangepicker",
     "introjs": "../vendor/js/intro.min",
     "i18next": "../vendor/js/i18next.amd.withJQuery.min"
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
         "jquery",
         "jquery.ui.widget"
      ]
     },
     "Backbone": {
      "deps": [
	 "jquery",
	 "facebook",
	 "bootstrap"
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
  }

   //urlArgs: "v=" + appVersion
});
