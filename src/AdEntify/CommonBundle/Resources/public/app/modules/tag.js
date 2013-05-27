/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "bootstrap"
], function(app) {

   var Tag = app.module();

   Tag.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_tag');
      }
   });

   Tag.Collection = Backbone.Collection.extend({
      model: Tag.Model,

      cache: true,

      parse: function(obj) {
         return obj;
      }
   });

   Tag.Views.Item = Backbone.View.extend({
      template: "myPhotos/item",

      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Tag.Views.MenuTools = Backbone.View.extend({
      template: "myPhotos/menuTools",

      close: function() {
         this.unloadTagging();
         app.trigger('global:closeMenuTools');
      },

      addTag: function() {
         $photo = $('#photos-grid .large');
         currentPhotoOverlay = $photo.find('.photo-overlay');
         this.setupTagging();
      },

      setupTagging: function() {
         currentPhotoOverlay.css({ cursor: 'crosshair'});
         currentPhotoOverlay.bind('click', this.addTagHandler);
      },

      unloadTagging: function() {
         if (currentPhotoOverlay) {
            currentPhotoOverlay.css({ cursor: 'pointer'});
            currentPhotoOverlay.unbind('click', this.addTagHandler);
         }
      },

      addTagHandler: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Add new tag
         tag = document.createElement("div");
         /*tag.innerHTML = '<i class="icon-tag icon-white"></i>';*/
         tag.setAttribute('style', 'left: ' + xPosition*100 + '%; top: ' + yPosition*100  + '%');
         tag.setAttribute('class', 'tag');
         $(tag).appendTo(currentPhotoOverlay);
         app.useLayout().setView("#menu-tools .form", new Tag.Views.AddTagForm()).render();
      },

      // Detail Form Submit
      submitPhotoDetails: function(e) {
         e.preventDefault();
         // Validate
         if ($('#photo-caption').val()) {
            var btn = $('#form-details button[type="submit"]');
            btn.button('loading');
            app.trigger('myPhotos:submitPhotoDetails');
         }
      },

      events: {
         "click .close": "close",
         "click #add-tag": "addTag",
         "click .cancel": "close",
         "click #form-details button[type='submit']": "submitPhotoDetails"
      }
   });

   Tag.Views.AddTagForm = Backbone.View.extend({
      template: "tag/addForm",

      afterRender: function() {
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
         })
      }
   });

   return Tag;
});