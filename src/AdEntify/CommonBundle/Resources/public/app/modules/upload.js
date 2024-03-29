/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/photos',
   'modules/externalServicePhotos',
   'modules/common',
   'modules/mySettings'
], function(app, Photos, ExternalServicePhotos, Common, MySettings) {

   var Upload = app.module();

   Upload.Views.Content = Backbone.View.extend({
      template: "upload/content",

      serialize: function() {
         return {
            model: {
               appRoot: app.rootUrl,
               localUpload: app.beginUrl + app.root + $.t('routing.upload/local/'),
               instagramUrl: Upload.Common.getInstagramUrl()
            }
         }
      },

      initialize: function() {
         app.trigger('domchange:title', $.t('upload.pageTitle'));
      },

      beforeRender: function() {
         if (!this.getView('.services-container')) {
            var upload = require('modules/upload');
            this.setView('.services-container', new upload.Views.ServiceButtons());
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      events: {
         "click #flickrUploadButton": "flickrUpload"
      },

      flickrUpload: function() {
         window.location.href = Routing.generate('flickr_request_token');
      }
   });

   Upload.Views.LocalUpload = Backbone.View.extend({
      template: 'upload/localUpload',

      initialize: function() {
         this.photos = new ExternalServicePhotos.Collection();
         this.listenTo(app, 'externalServicePhoto:submitPhotos', this.submitPhotos);
      },

      beforeRender: function() {
         this.insertView('.upload-photos-container', new Upload.Views.PhotosList({
            photos: this.photos,
            categories: this.options.categories
         }));
      },

      afterRender: function() {
         var that = this;
         $('#fileupload').attr("data-url", Routing.generate('upload_local_photo'));
         $('#fileupload').fileupload({
            dataType: 'json',
            start: function() {
               $('#loading-photos').stop().fadeIn();
               if (that.getView('.alert-upload'))
                  that.removeView('.alert-upload');
            },
            fail: function(e, data) {
               that.setView('.alert-upload', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('upload.errorLocalUpload'),
                  showClose: true
               })).render();
            },
            always: function() {
               $('#loading-photos').stop().fadeOut();
            },
            done: function (e, data) {
               if (data.result) {
                  var photo = new ExternalServicePhotos.Model();
                  photo.set('thumbUrl', data.result['photo-medium'].filename);

                  photo.set('smallSource', data.result['photo-small'].filename);
                  photo.set('smallWidth', data.result['photo-small'].width);
                  photo.set('smallHeight', data.result['photo-small'].height);

                  if (data.result['photo-medium']) {
                     photo.set('mediumSource', data.result['photo-medium'].filename);
                     photo.set('mediumWidth', data.result['photo-medium'].width);
                     photo.set('mediumHeight', data.result['photo-medium'].height);
                  }

                  if (data.result['photo-retina']) {
                     photo.set('retinaSource', data.result['photo-retina'].filename);
                     photo.set('retinaWidth', data.result['photo-retina'].width);
                     photo.set('retinaHeight', data.result['photo-retina'].height);
                  }

                  if (data.result['photo-large']) {
                     photo.set('largeSource', data.result['photo-large'].filename);
                     photo.set('largeWidth', data.result['photo-large'].width);
                     photo.set('largeHeight', data.result['photo-large'].height);
                  }

                  photo.set('originalSource', data.result.original.filename);
                  photo.set('originalWidth', data.result.original.width);
                  photo.set('originalHeight', data.result.original.height);
                  that.photos.add(photo);
               } else {
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('upload.errorProductImageUpload'),
                     showClose: true
                  })).render();
               }
            }
         });
         $(this.el).i18n();
      },

      submitPhotos: function(options) {
         // Show loader
         $('#photos-container').fadeOut('fast', function() {
            $('#loading-upload').fadeIn('fast');
         });

         if (this.photos.length > 0) {
            this.photos.each(function(image) {
               image.set('confidentiality', options.confidentiality);
            });

            // POST images to database
            $.ajax({
               url : Routing.generate('upload_load_external_photos'),
               type: 'POST',
               data: { 'images': this.photos.toJSON(), 'source': 'local' },
               success: function() {
                  Upload.Common.showUploadInProgressModal(true);
               },
               error: function() {
                  // Hide loader
                  $('#loading-upload').fadeOut('fast', function() {
                     $('#photos-container').fadeIn('fast');
                  });
                  app.trigger('externalPhotos:uploadingError');
               }
            });
         }
      }
   });

   Upload.Views.PhotosList = Backbone.View.extend({
      template: 'externalServicePhotos/list',
      confidentiality: 'public',
      albumName: '',

      serialize: function() {
         return {
            album: this.albumName,
            title: 'localPhotosUploaded'
         };
      },

      initialize: function() {
         this.listenTo(this.options.photos, 'add', this.render);
         this.categories = this.options.categories;
         this.listenTo(this.options.categories, {
            'sync': this.render
         });
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            this.insertView('#photos-list', new ExternalServicePhotos.Views.Item({
               model: photo,
               enableCheck: false,
               categories: this.categories
            }));
         }, this);

         if (!this.getView('.upload-counter-view')) {
            var counterView = new ExternalServicePhotos.Views.Counter({
               counterType: 'photos'
            });
            var that = this;
            counterView.on('checkedPhoto', function(count) {
               var submitButton = $(that.el).find('.submit-photos-button');
               if (count > 0) {
                  if ($(that.el).find('.submit-photos-button:visible').length === 0)
                     submitButton.fadeIn('fast');
               } else {
                  if ($(that.el).find('.submit-photos-button:hidden').length === 0)
                     submitButton.fadeOut('fast');
               }
            });
            this.setView('.upload-counter-view', counterView);
         }
      },

      afterRender: function() {
         $('#loading-photos').hide();
         $(this.el).i18n();
         if (this.options.photos.length > 0) {
            $('#loading-photos').hide();
         }
         var that = this;
         $(this.el).find('.photos-confidentiality').change(function() {
            if ($(this).val())
               that.confidentiality = $(this).val();
         });
      },

      submitPhotos: function() {
         var that = this;
         app.trigger('externalServicePhoto:submitPhotos', {
            confidentiality: that.confidentiality
         });
      },

      events: {
         'click .submit-photos-button': 'submitPhotos'
      }
   });

   Upload.Views.ServiceButtons = Backbone.View.extend({
      template: 'upload/serviceButtons',

      serialize: function() {
         return {
            rootUrl: app.beginUrl + app.root
         };
      },

      beforeRender: function() {
         this.services.each(function(service) {
            this.insertView(".services", new Upload.Views.ServiceButton({
               model: service
            }), 0);
         }, this);
      },

      afterRender: function() {
         this.$('.services').append('<li><a href="' + app.beginUrl + app.root + $.t('routing.upload/local/') + '" class="service-button local-service-button active"><i class="local-circle-white-icon icon"></i> ' + $.t('upload.myComputer') + '</a></li>');
      },

      initialize: function() {
         this.services = new MySettings.ServicesCollection();
         this.services.fetch();
         this.listenTo(this.services, 'sync', this.render);
      }
   });

   Upload.Views.ServiceButton = Backbone.View.extend({
      template: 'upload/serviceButton',
      tagName: 'li',

      serialize: function() {
         return {
            model: this.model,
            rootUrl: app.beginUrl + app.root
         };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      showServicePhotos: function(evt) {
         evt.preventDefault();
         Upload.Common.goToServiceUploadPage(this.model);
      },

      events: {
         'click .service-button': 'showServicePhotos'
      }
   });

   Upload.Views.UploadInProgress = Backbone.View.extend({
      template: 'upload/uploadInProgress',

      beforeRender: function() {
         var that = this;
         if (!this.getView('.progress-bar-container')) {
            var progressBarView = Upload.Common.getProgressBarView();
            progressBarView.on('completed', function() {
               that.removeView('.progress-bar-container');
               Common.Tools.hideCurrentModalIfOpened(function() {
                  Backbone.history.navigate($.t('routing.my/photos/'), true);
               });
            });
            this.setView('.progress-bar-container', progressBarView);
         }
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   Upload.Common = {
      getInstagramUrl: function(connected) {
         connected = typeof connected !== 'undefined' ? connected : false;
         return connected ? $.t('routing.instagram/photos/') : 'https://api.instagram.com/oauth/authorize/?client_id=' + instagramClientId + '&redirect_uri=' + app.rootUrl + 'instagram/authentication&response_type=code';
      },

      getFacebookUrl: function(connected) {
         connected = typeof connected !== 'undefined' ? connected : false;
         return connected ? $.t('facebook/albums/') : 'https://www.facebook.com/dialog/oauth?client_id=' + facebookAppId + '&redirect_uri=' + Routing.generate('_security_check_facebook', null, true) + '&scope=' + facebookPermissions + '&response_type=code';
      },

      getFlickrUrl: function(connected) {
         connected = typeof connected !== 'undefined' ? connected : false;
         return connected ? $.t('routing.flickr/sets/') : Routing.generate('flickr_request_token', { 'locale': currentLocale });
      },

      getProgressBarView: function() {
         return new Common.Views.ProgressBar();
      },

      showUploadInProgressModal: function(redirectOnClose) {
         redirectOnClose = redirectOnClose || false;
         // Top progressbar
         var progressBar = new Common.Views.ProgressBar();
         app.useLayout().setView('.top-progress-bar', progressBar).render();

         // Modal upload in progress
         var uploadInProgressView = new Upload.Views.UploadInProgress();

         var modal = new Common.Views.Modal({
            view: uploadInProgressView,
            showFooter: false,
            showHeader: false,
            modalDialogClasses: 'upload-dialog'
         });
         if (redirectOnClose) {
            modal.on('hide', function() {
               Backbone.history.navigate($.t('routing.my/photos/'), true);
            });
         }
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      },

      goToServiceUploadPage: function(model) {
         switch(model.get('service_name')) {
            case 'Facebook':
               var url = Upload.Common.getFacebookUrl(app.fb.isConnected());
               app.fb.isConnected() ? Backbone.history.navigate(url, { trigger: true }) : window.location.href = url;
               break;
            case 'instagram':
               var url = Upload.Common.getInstagramUrl(model.get('linked'));
               model.get('linked') ? Backbone.history.navigate(url, { trigger: true }) : window.location.href = url;
               break;
            case 'Flickr':
               var url = Upload.Common.getFlickrUrl(model.get('linked'));
               model.get('linked') ? Backbone.history.navigate(url, { trigger: true }) : window.location.href = url;
               break;
         }
      }
   };

   return Upload;
});