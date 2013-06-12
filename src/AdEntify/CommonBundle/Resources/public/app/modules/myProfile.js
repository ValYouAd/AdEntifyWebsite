/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 07/06/2013
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var MyProfile = app.module();

   MyProfile.Model = Backbone.Model.extend({

   });

   MyProfile.Views.Detail = Backbone.View.extend({
      template: "myProfile/detail",

      afterRender: function() {
         $(this.el).find('option[value="' + app.appState().getLocale() + '"]').attr("selected", "selected");
         $(this.el).i18n();
      },

      initialize: function() {
         app.trigger('domchange:title', $.t('myProfile.pageTitle'));
      },

      submit: function(e) {
         e.preventDefault();
         window.location.href = Routing.generate('change_lang', {'locale': $('#lang').val()});
      },

      events: {
         "submit form": "submit"
      }
   });

   MyProfile.Views.MenuRight = Backbone.View.extend({
      template: "myProfile/menuRight"
   });


   return MyProfile;
});