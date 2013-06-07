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

     /* serialize: function() {
         return { model: this.model };
      },
*/
      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         //this.listenTo(this.model, "change", this.render);
         app.trigger('domchange:title', 'Mon profil');
      },

      submit: function(e) {
         e.preventDefault();

         $.i18n.setLng($('#lang').val(), function() {
            $('main').i18n();
         });
      },

      events: {
         "submit form": "submit"
      }
   });

   MyProfile.Views.Content = Backbone.View.extend({
      template: "myProfile/content",

      serialize: function() {
         return { collection: this.options.photos };
      },

      beforeRender: function() {
         /*this.options.photos.each(function(photo) {
            this.insertView("#photos-grid", new MyProfile.Views.Item({
               model: photo
            }));
         }, this);*/
      },

      initialize: function() {
         /*this.listenTo(this.options.photos, {
            "sync": this.render
         });*/
         app.trigger('domchange:title', 'Mon profil');
      }
   });


   return MyProfile;
});