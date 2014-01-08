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
            createSearchChoice: function(term, data) {
               if ($(data).filter(function() {
                  return this.text.localeCompare(term)===0;
               }).length===0) {
                  return {id:term, text:term};
               }
            },
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
                           text : item.name,
                           usedCount: item.used_count
                        };
                     })
                  }
               }
            },
            dropdownCssClass: "bigdrop",
            formatResult: function (hashtag) {
               var markup = "<table class='hashtag-result'><tr>";
               if (hashtag.text !== undefined) {
                  markup += "<td class='hashtag-name'>" + hashtag.text + "</td>";
               }
               if (hashtag.usedCount !== undefined) {
                   markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: hashtag.usedCount}) + "</td>";
               } else {
                  markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: 0}) + "</td>";
               }
               markup += "</tr></table>"
               return markup;
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
            createSearchChoice: function(term, data) {
               if ($(data).filter(function() {
                  return this.text.localeCompare(term)===0;
               }).length===0) {
                  return {id:term, text:term};
               }
            },
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
               }
            },
            dropdownCssClass: "bigdrop",
            formatResult: function (hashtag) {
               var markup = "<table class='hashtag-result'><tr>";
               if (hashtag.text !== undefined) {
                  markup += "<td class='hashtag-name'>" + hashtag.text + "</td>";
               }
               if (hashtag.usedCount !== undefined) {
                  markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: hashtag.usedCount}) + "</td>";
               } else {
                  markup += "<td class='hashtag-usecount'>" + $.t('hashtag.usedCount', {count: 0}) + "</td>";
               }
               markup += "</tr></table>"
               return markup;
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
         Common.Tools.showUploadProgressBar();
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