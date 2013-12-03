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

   ExternalServicePhotos.Model = Backbone.Model.extend({ });

   ExternalServicePhotos.Collection = Backbone.Collection.extend({
      model: ExternalServicePhotos.Model
   });

   ExternalServicePhotos.Views.Item = Backbone.View.extend({
      template: "externalServicePhotos/item",
      tagName: 'div class="col-sm-4 photo-item"',
      enableCheck: true,

      serialize: function() {
         return {
            model: this.model,
            enableCheck: this.enableCheck,
            categories : this.categories
         };
      },

      initialize: function() {
         this.enableCheck = typeof this.options.enableCheck === 'undefined' ? this.enableCheck : this.options.enableCheck;
         this.categories = this.options.categories;
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
         $(this.el).find('.selectHashtags').select2({
            minimumInputLength: 1,
            multiple: true,
            ajax: {
               url: Routing.generate('api_v1_get_hashtag_search'),
               dataType: 'json',
               data: function(term, page) {
                  return {
                     query: term,
                     page: page
                  }
               },
               results: function(data, page) {
                  return {
                     results : $.map(data.data, function(item) {
                        return {
                           id : item.name,
                           text : item.name
                        };
                     })
                  }
               },
               dropdownCssClass: "bigdrop"
            }
         }).on("change", function(e) {
               that.model.set('hashtags', e.val);
            });
      },

      selectPhoto: function(e) {

         var container = $(this.el).find('.photo-inner');
         if (container.length > 0) {
            container.addClass('checked');
         }
         $(this.el).find('.checked-overlay').fadeIn('fast');
         this.model.set('checked', true);
         app.trigger('externalServicePhotos:selectPhoto', this.model);
      },

      unselectPhoto: function() {
         var container = $(this.el).find('.photo-inner');
         if (container.length > 0) {
            container.removeClass('checked');
         }
         $(this.el).find('.checked-overlay').fadeOut('fast');
         this.model.set('checked', false);
         app.trigger('externalServicePhotos:unselectPhoto', this.model);
      },

      events: {
         'click .hover-overlay': 'selectPhoto',
         'click .unselect-button': 'unselectPhoto'
      }
   });

   ExternalServicePhotos.Views.AlbumItem = Backbone.View.extend({
      template: "externalServicePhotos/albumItem",

      tagName: 'div class="col-sm-4 album-item"',

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
         $(this.el).find('.selectHashtags').select2({
            minimumInputLength: 1,
            multiple: true,
            ajax: {
               url: Routing.generate('api_v1_get_hashtag_search'),
               dataType: 'json',
               data: function(term, page) {
                  return {
                     query: term,
                     page: page
                  }
               },
               results: function(data, page) {
                  return {
                     results : $.map(data.data, function(item) {
                        return {
                           id : item.name,
                           text : item.name
                        };
                     })
                  }
               },
               dropdownCssClass: "bigdrop"
            }
         }).on("change", function(e) {
               that.model.set('hashtags', e.val);
            });
      },

      initialize: function() {
         this.listenTo(this.model, 'change:picture', this.render);
         this.categories = this.options.categories;
      },

      selectAlbum: function() {
         var that = this;
         $(this.el).find('.checked-overlay').fadeIn('fast');
         $(this.el).find('.selectAlbumWrapper').fadeOut('fast', function() {
            $(that.el).find('.unselectAlbumWrapper').fadeIn();
         });
         app.trigger('externalServicePhotos:selectAlbum', this.model);
      },

      cancelSelection: function() {
         var that = this;
         $(this.el).find('.checked-overlay').fadeOut('fast');
         $(this.el).find('.unselectAlbumWrapper').fadeOut('fast', function() {
            $(that.el).find('.selectAlbumWrapper').fadeIn();
         });
         app.trigger('externalServicePhotos:cancelSelectAlbum', this.model);
      },

      events: {
         'click .selectAlbum': 'selectAlbum',
         'click .unselect-button': 'cancelSelection',
         'click .unselectAlbum': 'cancelSelection'
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
      checkedImagesCount: 0,

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
         this.listenTo(app, 'externalServicePhoto:imageChecked', function(value) {
            that.checkedImagesCount = that.checkedImagesCount + value;
            that.imageChecked(that.checkedImagesCount);
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
         this.listenTo(app, 'externalServicePhotos:selectAlbum', function(album) {
            this.checkedAlbums.push(album);
            this.checkAlbumsSelected();
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
         this.listenTo(app, 'externalPhotos:uploadingInProgress', function() {
            $('#uploadInProgressModal').appendTo("body").modal({
               backdrop: true,
               show: true
            });
            $('#uploadInProgressModal').on('hidden', function() {
               Backbone.history.navigate($.t('routing.my/adentify/'), true);
            });
         });
         this.listenTo(app, 'externalServicePhotos:cancelSelectAlbum', function(album) {
            index = _.indexOf(this.checkedAlbums, album);
            if (index > -1)
               that.checkedAlbums.splice(index, 1);
            this.checkAlbumsSelected();
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

   ExternalServicePhotos.Views.Counter = Backbone.View.extend({
      template: 'externalServicePhotos/counter',

      serialize: function() {
         return {
            count: this.counterType == 'album' ? this.checkedAlbums.length : this.checkedPhotos.length,
            translationKey: this.counterType == 'album' ? 'countSelectedAlbum' : 'countSelectedPhoto'
         };
      },

      initialize: function() {
         this.counterType = this.options.counterType;
         if (this.counterType == 'album') {
            this.checkedAlbums = [];
            this.listenTo(app, 'externalServicePhotos:selectAlbum', function(album) {
               this.checkedAlbums.push(album);
               this.trigger('checkedAlbum', this.checkedAlbums.length);
               this.render();
            });
            this.listenTo(app, 'externalServicePhotos:cancelSelectAlbum', function(album) {
               index = _.indexOf(this.checkedAlbums, album);
               if (index > -1)
                  this.checkedAlbums.splice(index, 1);
               this.trigger('checkedAlbum', this.checkedAlbums.length);
               this.render();
            });
         } else {
            this.checkedPhotos = [];
            this.listenTo(app, 'externalServicePhotos:selectPhoto', function(photo) {
               this.checkedPhotos.push(photo);
               this.trigger('checkedPhoto', this.checkedPhotos.length);
               this.render();
            });
            this.listenTo(app, 'externalServicePhotos:unselectPhoto', function(photo) {
               index = _.indexOf(this.checkedPhotos, photo);
               if (index > -1)
                  this.checkedPhotos.splice(index, 1);
               this.trigger('checkedPhoto', this.checkedPhotos.length);
               this.render();
            });
         }
      }
   });

   ExternalServicePhotos.Common = {
      showUploadInProgressModal: function() {
         Backbone.history.navigate($.t('routing.my/photos/'), true);
         /*$('#uploadInProgressModal').appendTo("body").modal({
            backdrop: true,
            show: true
         });
         $('#uploadInProgressModal').on('hidden.bs.modal', function() {
            Backbone.history.navigate($.t('routing.my/photos/'), true);
         });*/
      }
   }

   return ExternalServicePhotos;
});