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
   var currentPhotoOverlay = null;
   var currentTag = null;
   var tags = null;

   Tag.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_tag');
      }
   });

   Tag.Collection = Backbone.Collection.extend({
      model: Tag.Model,
      cache: true
   });

   Tag.Views.Item = Backbone.View.extend({
      template: "tag/item",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Tag.Views.List = Backbone.View.extend({
      template: "tag/list",

      beforeRender: function() {
         tags.each(function(tag) {
            this.insertView(".tags", new Tag.Views.Item({
               model: tag
            }));
         }, this);
      },

      afterRender: function() {
         setTimeout(function() {
            tags.each(function(tag) {
               tag.set('class', '');
            });
         }, 500);
      },

      initialize: function() {
         this.listenTo(tags, {
            "add": this.render,
            "remove": this.render
         });
      }
   });

   Tag.Views.MenuTools = Backbone.View.extend({
      template: "tag/menuTools",

      initialize: function() {
         var that = this;
         app.on('tagMenuTools:addTag', function() {
            that.addTag();
         });
         app.on('global:closeMenuTools', function() {
            that.unloadTagging();
         });
         tags = new Tag.Collection();
      },

      cancel: function() {
         app.trigger('tagMenuTools:cancel');
      },

      addTag: function() {
         $photo = $('#photos-grid .large');
         if ($photo.length) {
            currentPhotoOverlay = $photo.find('.photo-overlay');
            this.setupTagging();
         }
      },

      setupTagging: function() {
         currentPhotoOverlay.css({ cursor: 'crosshair'});
         currentPhotoOverlay.bind('click', this.tagOverlayHandler);
         app.useLayout().insertView(currentPhotoOverlay.selector, new Tag.Views.List()).render();
      },

      unloadTagging: function() {
         if (currentPhotoOverlay) {
            currentPhotoOverlay.css({ cursor: 'pointer'});
            currentPhotoOverlay.unbind('click', this.tagOverlayHandler);
         }
      },

      tagOverlayHandler: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Remove tags arent validated
         tags.each(function(tag) {
            if (!tag.has('validated')) {
               tags.remove(tag);
            }
         });

         var tag = new Tag.Model();
         tag.set('xPosition', xPosition);
         tag.set('yPosition', yPosition);
         tag.set('class', 'new-tag');
         tags.add(tag);
         currentTag = tag;

         app.useLayout().setView("#menu-tools .tag-form", new Tag.Views.AddTagForm()).render();
         $('.tag-text').fadeOut('fast', function() {
            $('.tag-form').fadeIn();
         });
      },

      close: function() {
         this.unloadTagging();
         app.trigger('global:closeMenuTools');
      },

      events: {
         "click .cancel-add-tag": "cancel"
      }
   });

   Tag.Views.AddTagForm = Backbone.View.extend({
      template: "tag/addForm",

      cancel: function(e) {
         e.preventDefault();
         // Remove current tag
         if (currentTag)
            tags.remove(currentTag);
         // Hide form
         app.trigger('tagMenuTools:cancel');
      },

      afterRender: function() {
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
         })
      },

      events: {
         "click .cancel-add-tag": "cancel"
      }
   });

   return Tag;
});