/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common",
   "bootstrap"
], function(app, Common) {

   var ExternalServicePhotos = app.module();

   ExternalServicePhotos.Views.Item = Backbone.View.extend({
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
         app.trigger('externalServicePhoto:imageChecked', $('.check-image .checked').length);
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   ExternalServicePhotos.Views.AlbumItem = Backbone.View.extend({
      template: "externalServicePhotos/albumItem",

      tagName: "li class='span2'",

      serialize: function() {
         return {
            model: this.model,
            categories : this.categories
         };
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.categories.length > 0) {
            var that = this;
            $(this.el).find('.selectCategories').select2();
            $(this.el).find('.selectCategories').on('change', function() {
               that.model.set('categories', $(that.el).find('.selectCategories').select2('val'));
            });
         }
         $(this.el).find('.photos-confidentiality').change(function() {
            if ($(this).val())
               that.model.set('confidentiality', $(this).val());
         });
      },

      initialize: function() {
         //this.listenTo(this.model, "change", this.render);
         this.categories = this.options.categories;
      },

      selectAlbum: function() {
         var that = this;
         $(this.el).find('.caption-select').fadeOut('fast', function() {
            $(that.el).find('.caption-selected').fadeIn();
         });
         app.trigger('externalServicePhotos:selectAlbum', this.model);
      },

      cancelSelection: function() {
         var that = this;
         $(this.el).find('.caption-selected').fadeOut('fast', function() {
            $(that.el).find('.caption-select').fadeIn();
         });
         app.trigger('externalServicePhotos:cancelSelectAlbum', this.model);
      },

      events: {
         "click .selectAlbum": "selectAlbum",
         "click .cancelSelection": "cancelSelection"
      }
   });

   ExternalServicePhotos.Views.ErrorNoRights = Backbone.View.extend({
      template: "externalServicePhotos/errors/noRights",

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   ExternalServicePhotos.Views.MenuRightPhotos = Backbone.View.extend({
      template: "externalServicePhotos/menuRightPhotos",

      serialize: function() {
         return {
            categories : this.categories
         };
      },

      imageChecked: function(count) {
         if (count > 0) {
            $('.no-photo-selected').fadeOut('fast', function() {
               $('.photos-selected').fadeIn('fast');
               $('.photo-count').html(count);
            });
         } else {
            $('.photos-selected').fadeOut('fast', function() {
               $('.no-photo-selected').fadeIn('fast');
            });
         }
      },

      initialize: function() {
         var that = this;
         this.listenTo(app, 'externalServicePhoto:imageChecked', function(count) {
            that.imageChecked(count);
         });
         this.listenTo(app, 'externalPhotos:uploadingError', function() {
            btn = $('.submit-photos');
            btn.button('reset');
            app.useLayout().setView('.alert-upload-photos', new Common.Views.Alert({
               cssClass: Common.alertError,
               message: $.t('externalServicePhotos.uploadingError'),
               showClose: true
            })).render();
         });
         app.on('externalPhotos:uploadingInProgress', function() {
            $('#uploadInProgressModal').appendTo("body").modal({
               backdrop: true,
               show: true
            });
            $('#uploadInProgressModal').on('hidden', function() {
               Backbone.history.navigate($.t('routing.my/adentify/'), true);
            });
         });
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
      },

      events: {
         "click .photos-rights": "photoRightsClick",
         "click .submit-photos": "submitPhotos"
      },

      photoRightsClick: function() {
         if ($('.photos-rights:checked').length != 1) {
            $('.submit-photos').hide();
            app.useLayout().setView("#errors", new ExternalServicePhotos.Views.ErrorNoRights()).render();
            $('.alert').alert();
         } else {
            $('.alert').alert('close');
            $('.submit-photos').fadeIn('fast');
         }
      },

      submitPhotos: function(e) {
         e.preventDefault();
         btn = $('.submit-photos');
         btn.button('loading');
         confidentiality = $('#photos-confidentiality option:selected').val() == 'private' ? 'private' : 'public';
         app.trigger('externalServicePhoto:submitPhotos', {
            confidentiality: confidentiality,
            categories: $(this.el).find('.selectCategories').select2('val')
         });
      },

      afterRender: function() {
         $(this.el).i18n();
         if (typeof this.categories !== 'undefined' && this.categories.length > 0)
            $(this.el).find('.selectCategories').select2();
      }
   });

   ExternalServicePhotos.Views.MenuRightAlbums = Backbone.View.extend({
      template: "externalServicePhotos/menuRightAlbums",

      serialize: function() {
         return {
            categories : this.categories
         };
      },

      checkAlbumsSelected: function() {
         var that = this;
         if (this.checkedAlbums.length > 0) {
            $('.no-album-selected').fadeOut('fast', function() {
               $('.albums-selected').fadeIn('fast');
               $('.album-count').html(that.checkedAlbums.length);
            });
         } else {
            $('.albums-selected').fadeOut('fast', function() {
               $('.no-album-selected').fadeIn('fast');
            });
         }
      },

      initialize: function() {
         var that = this;
         this.checkedAlbums = [];
         app.on('externalServicePhotos:selectAlbum', function(album) {
            that.checkedAlbums.push(album);
            that.checkAlbumsSelected();
         });
         app.on('externalPhotos:uploadingError', function() {
            btn = $('.submit-photos');
            btn.button('reset');
            app.useLayout().setView('.alert-upload-photos', new Common.Views.Alert({
               cssClass: Common.alertError,
               message: $.t('externalServicePhotos.uploadingError'),
               showClose: true
            })).render();
         });
         app.on('externalPhotos:uploadingInProgress', function() {
            $('#uploadInProgressModal').appendTo("body").modal({
               backdrop: true,
               show: true
            });
            $('#uploadInProgressModal').on('hidden', function() {
               Backbone.history.navigate($.t('routing.my/adentify/'), true);
            });
         });
         app.on('externalServicePhotos:cancelSelectAlbum', function(album) {
            index = _.indexOf(that.checkedAlbums, album);
            if (index > -1)
               that.checkedAlbums.splice(index, 1);
            that.checkAlbumsSelected();
         });
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            "sync": this.render
         });
      },

      events: {
         "click .photos-rights": "photoRightsClick",
         "click .submit-photos": "submitAlbums"
      },

      photoRightsClick: function() {
         if ($('.photos-rights:checked').length != 1) {
            $('.submit-photos').hide();
            app.useLayout().setView("#errors", new ExternalServicePhotos.Views.ErrorNoRights()).render();
            $('.alert').alert();
         } else {
            $('.alert').alert('close');
            $('.submit-photos').fadeIn('fast');
         }
      },

      submitAlbums: function(e) {
         e.preventDefault();
         btn = $('.submit-photos');
         btn.button('loading');
         confidentiality = $('#photos-confidentiality option:selected').val() == 'private' ? 'private' : 'public';
         app.trigger('externalServicePhoto:submitAlbums', {
            /*confidentiality: confidentiality,
            categories: $(this.el).find('.selectCategories').select2('val'),*/
            albums: this.checkedAlbums
         });
      },

      afterRender: function() {
         $(this.el).i18n();
         if (typeof this.categories !== 'undefined' && this.categories.length > 0)
            $(this.el).find('.selectCategories').select2();
      }
   });

   return ExternalServicePhotos;
});