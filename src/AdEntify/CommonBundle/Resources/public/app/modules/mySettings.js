/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 07/06/2013
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common",
   'modules/upload',
   'jquery.fileupload',
   'bday-picker'
], function(app, Common, Upload) {

   var MySettings = app.module();

   MySettings.ChangePasswordModel = Backbone.Model.extend({
      url: Routing.generate('api_v1_post_user_change_password', { id: currentUserId }),

      toJSON: function() {
         return { fos_user_change_password: this.attributes };
      }
   });

   MySettings.ServicesCollection = Backbone.Collection.extend({
      url: Routing.generate('api_v1_get_user_services')
   });

   MySettings.Views.ServiceItem = Backbone.View.extend({
      template: "mySettings/serviceItem",
      tagName: 'li class="service-item"',

      serialize: function() {
         return {
            model: this.model,
            showLabel: this.showLabel
         };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
         this.showLabel = this.options.showLabel;
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      deleteLink: function(e) {
         e.preventDefault();
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  url: Routing.generate('api_v1_delete_oauthuserinfo', {id: that.model.get('id')}),
                  type: 'DELETE',
                  success: function() {
                     app.trigger('mysettings:serviceDeleted', that.model);
                  },
                  error: function() {
                     app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('mySettings.cantDeleteServiceLink'),
                        showClose: true
                     })).render();
                  }
               });
            },
            error: function () {
               window.location.href = Routing.generate('home_logoff', { '_locale': app.appState().getLocale() });
            }
         });
      },

      connectService: function() {
         require('modules/upload').Common.goToServiceUploadPage(this.model);
      },

      events: {
         "click .deletelink": "deleteLink",
         'click .connect-button': 'connectService',
         'click .service-icon': 'connectService'
      }
   });

   MySettings.Views.ServiceList = Backbone.View.extend({
      template: 'mySettings/serviceList',
      showLabel: false,

      beforeRender: function() {
         this.services.each(function(service) {
            this.insertView(".services-list", new MySettings.Views.ServiceItem({
               model: service,
               showLabel: this.showLabel
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).find('.service-icon-tooltip').tooltip();
      },

      initialize: function() {
         this.services = new MySettings.ServicesCollection();
         this.showLabel = typeof this.options.showLabel !== 'undefined' ? this.options.showLabel : this.showLabel;
         var that = this;
         this.services.fetch({
            success: function() {
               that.render();
            },
            error: function() {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('mySettings.errorConnectedServices'),
                  showClose: true
               })).render();
            }
         });
         this.listenTo(this.services, "remove", this.render);
         this.listenTo(app, 'mysettings:serviceDeleted', function(service) {
            this.services.remove(service);
            if (this.services.length === 0) {
               app.useLayout().setView('.alert-connected-services', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('mySettings.noConnectedServices'),
                  showClose: true
               })).render();
            }
         });
      }
   });

   MySettings.Views.Detail = Backbone.View.extend({
      template: "mySettings/detail",

      beforeRender: function() {
         this.setView(".services", new MySettings.Views.ServiceList());
      },

      afterRender: function() {
         $(this.el).find('option[value="' + app.appState().getLocale() + '"]').attr("selected", "selected");
         $(this.el).i18n();

         $(this.el).find('#profilepictureupload').attr("data-url", Routing.generate('upload_profile_picture'));
         $(this.el).find('#profilepictureupload').fileupload({
            dataType: 'json',
            done: function (e, data) {
               if (data.result) {

               } else {
                  app.useLayout().setView('.alert-product', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('tag.errorProductImageUpload'),
                     showClose: true
                  })).render();
               }
            }
         });

         this.$('.birthdate').birthdaypicker(options={
            defaultDate: currentUserBirthday
         });
      },

      initialize: function() {
         var that = this;
         // Get current user
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_user', { id: currentUserId }),
                  headers: {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  success: function(user) {
                     if (user && typeof user.facebook_id === 'undefined' && typeof user.twitter_id === 'undefined') {
                       $('.changePasswordForm').fadeIn('fast');
                     }
                     if (user) {
                        if (!user.share_data_with_advertisers) {
                           that.$('#shareDataAdvertisers').attr('checked', false);
                        }
                        that.$('#partnersNewsletters').attr('checked', user.partners_newsletters);
                     }
                  }
               });
            }
         });
         app.trigger('domchange:title', $.t('mySettings.pageTitle'));
      },

      submitSettings: function(e) {
         var redirect = function() {
            window.location.href = Routing.generate('change_lang', { 'locale' : $('#lang').val() });
         };

         e.preventDefault();
         var deferreds = [];
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               if (that.$('#birthdate').val()) {
                  deferreds.push(new $.Deferred());
                  $.ajax({
                     url: Routing.generate('api_v1_post_user_birthday'),
                     type: 'POST',
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     data: {
                        'birthday': that.$('#birthdate').val()
                     },
                     complete: function() {
                        var deferred = deferreds.pop();
                        if (deferred)
                           deferred.resolve();
                     }
                  });
               }
               deferreds.push(new $.Deferred());
               $.ajax({
                  url: Routing.generate('api_v1_post_settings'),
                  type: 'POST',
                  headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                  data: that.$('.settingsForm').serialize(),
                  complete: function() {
                     var deferred = deferreds.pop();
                     if (deferred)
                        deferred.resolve();
                  }
               });
            }
         });

         $.when.apply(null, deferreds).done(function() {
            redirect();
         });
      },

      submitChangePassword: function(e) {
         e.preventDefault();

         if ($('.currentPassword').val() && $('.newPasswordFirst').val() && $('.newPasswordSecond').val()) {
            if ($('.newPasswordFirst').val() == $('.newPasswordSecond').val()) {
               var changePassword = new MySettings.ChangePasswordModel();
               changePassword.set('current_password', $('.currentPassword').val());
               changePassword.set('plainPassword', $('.newPasswordFirst').val());
               changePassword.getToken('change_password', function() {
                  changePassword.save(null, {
                     success: function() {
                        app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                           cssClass: Common.alertSuccess,
                           message: $.t('mySettings.passwordChanged'),
                           showClose: true
                        })).render();
                        $('input[type="password"]').val('');
                     },
                     error: function() {
                        app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                           cssClass: Common.alertSuccess,
                           message: $.t('mySettings.currentPasswordError'),
                           showClose: true
                        })).render();
                     }
                  });
               });
            } else {
               app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('mySettings.notSamePassword'),
                  showClose: true
               })).render();
            }
         } else {
            app.useLayout().setView('.alert-changePassword', new Common.Views.Alert({
               cssClass: Common.alertError,
                  message: $.t('mySettings.emptyFieldsChangePassword'),
               showClose: true
            })).render();
         }
      },

      deleteAccount: function() {
         var modal = new Common.Views.Modal({
            content: 'mySettings.modalDeleteAccountText',
            showFooter: true,
            showHeader: true,
            title: 'mySettings.modalDeleteAccountTitle',
            modalDialogClasses: 'deleteAccountDialog',
            showConfirmButton: true,
            confirmButton: 'mySettings.confirmDeleteAccountButton'
         });
         modal.on('confirm', function() {
            app.oauth.loadAccessToken({
               success: function() {
                  $.ajax({
                     url: Routing.generate('api_v1_delete_user', { id: currentUserId }),
                     type: 'DELETE',
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function() {
                        window.location.href = Routing.generate('fos_user_security_logout');
                     }
                  });
               }
            });
            modal.close();
         });
         Common.Tools.hideCurrentModalIfOpened(function() {
            app.useLayout().setView('#modal-container', modal).render();
         });
      },

      events: {
         "submit .settingsForm": "submitSettings",
         "submit .changePasswordForm": "submitChangePassword",
         'click .delete-account-button': 'deleteAccount'
      }
   });

   MySettings.Views.MenuRight = Backbone.View.extend({
      template: "mySettings/menuRight"
   });


   return MySettings;
});