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

   var FlickrPhotos = app.module();
   var error = '';

   FlickrPhotos.Model = Backbone.Model.extend({
      smallPicture: null,

      initialize: function() {
         this.set('smallPicture', this.get('url_s'));
      }
   });

   FlickrPhotos.Collection = Backbone.Collection.extend({
      model: FlickrPhotos.Model,
      cache: true
   });

   FlickrPhotos.Views.Item = Backbone.View.extend({
      template: "externalServicePhotos/item",

      tagName: "li span2",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      events: {
         "click .check-image" : "toggleCheckedImage"
      },

      toggleCheckedImage: function(e) {
         var container = $(e.currentTarget).find('.check-image-container');
         if (container.length > 0) {
            container.toggleClass('checked');
         }
         this.checkActionButtons();
      },

      afertRender: function() {
         this.checkActionButtons();
      },

      checkActionButtons: function() {
         if ($('.check-image .checked').length > 0) {
            $('.action-buttons').fadeIn('fast');
         } else {
            $('.action-buttons').fadeOut('fast');
         }
      }
   });

   FlickrPhotos.Views.List = Backbone.View.extend({
      template: "externalServicePhotos/list",

      initialize: function() {
         this.loadPhotos();

         this.listenTo(this.options.photos, {
            "add": this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView("#photos-list", new FlickrPhotos.Views.Item({
               model: photo
            }));
         }, this);
      },

      afterRender: function() {
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
      },

      loadPhotos: function() {
         var that = this;

         // Get flickr set photos
         $.ajax({
            url: Routing.generate('flickr_sets_photos', { 'id': this.options.albumId }),
            dataType: 'json',
            success: function(response) {
               var photos = [];
               for (var i= 0, l=response.length; i<l; i++) {
                  photos[i] = response[i];
               }
               that.options.photos.add(photos);
            },
            error : function(e) {
               // TODO : error
               console.log('impossible de récupérer les photos flickr');
            }
         });
      },

      events: {
         "click .submit-photos": "submitPhotos",
         "click .photos-rights": "photoRightsClick"
      },

      submitPhotos: function(e) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         // Get checked images
         checkedImages = $('.checked img');
         if (checkedImages.length > 0) {
            var images = [];
            _.each(checkedImages, function(image, index) {
               images[index] = {
                  'source' : $(image).data('source-url'),
                  'width' : $(image).data('source-width'),
                  'height' : $(image).data('source-height')
               };
            });

            // POST images to database
         }
      },

      photoRightsClick: function() {
         if ($('.photos-rights:checked').length != 2) {
            $('.submit-photos').hide();
            app.useLayout().setView("#errors", new FlickrPhotos.Views.ErrorNoRights()).render();
            $('.alert').alert();
         } else {
            $('.submit-photos').fadeIn('fast');
         }
      }
   });

   FlickrPhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "externalServicePhotos/errors/noRights"
   });

   return FlickrPhotos;
});