/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/06/2013
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
define([
   "app"
], function(app) {

   var Common = app.module();
   Common.alertError = 'alert-danger';
   Common.alertInfo = 'alert-info';
   Common.alertSuccess = 'alert-success';
   Common.alertWarning = 'alert-block';


   Common.AlertModel = Backbone.Model.extend({
      defaults: {
         showClose: false
      },

      initialize: function(options) {
         switch(options.cssClass) {
            case Common.alertError:
               this.set('cssClass', Common.alertError);
            break;
            case Common.alertInfo:
               this.set('cssClass', Common.alertInfo);
            break;
            case Common.alertSuccess:
               this.set('cssClass', Common.alertSuccess);
            break;
            default:
               this.set('cssClass', Common.alertWarning);
            break;
         }
         this.set('message', options.message);
         if (typeof options.showClose !== 'undefined') {
            this.set('showClose', options.showClose)
         }
      }
   });

   Common.Views.Alert = Backbone.View.extend({
      template: "common/alert",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         if (typeof this.options.showClose === 'undefined')
            this.options.showClose = false;

         this.model = new Common.AlertModel({
            cssClass: this.options.cssClass,
            message: this.options.message,
            showClose: this.options.showClose
         });
         this.listenTo(this.model, "change", this.render);
      }
   });

   Common.ModalModel = Backbone.Model.extend({});

   Common.Views.Modal = Backbone.View.extend({
      template: "common/modal",
      showFooter: true,
      showHeader: true,
      showConfirmButton: false,

      serialize: function() {
         return {
            title: this.title,
            content: this.content,
            showFooter: this.showFooter,
            showHeader: this.showHeader,
            modalContentClasses: this.modalContentClasses,
            modalDialogClasses: this.modalDialogClasses,
            showConfirmButton: this.showConfirmButton,
            confirmButton: this.confirmButton
         };
      },

      beforeRender: function() {
         if (typeof this.options.view !== 'undefined')
            this.setView('.modal-body', this.options.view);
      },

      initialize: function() {
         this.showFooter = typeof this.options.showFooter !== 'undefined' ? this.options.showFooter : this.showFooter;
         this.showHeader = typeof this.options.showHeader !== 'undefined' ? this.options.showHeader : this.showHeader;
         this.showConfirmButton = typeof this.options.showConfirmButton !== 'undefined' ? this.options.showConfirmButton : this.showConfirmButton;
         this.confirmButton = typeof this.options.confirmButton !== 'undefined' ? this.options.confirmButton : 'common.confirmButton';
         this.title = typeof this.options.title !== 'undefined' ? this.options.title : null;
         this.modalContentClasses = typeof this.options.modalContentClasses !== 'undefined' ? this.options.modalContentClasses : null;
         this.modalDialogClasses = typeof this.options.modalDialogClasses !== 'undefined' ? this.options.modalDialogClasses : null;
         this.content = typeof this.options.content !== 'undefined' ? this.options.content : null;
      },

      close: function() {
         this.$('#commonModal').modal('hide');
      },

      afterRender: function() {
         $(this.el).i18n();
         var that = this;
         this.$('#commonModal').on('show.bs.modal', function() {
            that.trigger('show');
         });
         this.$('#commonModal').on('hide.bs.modal', function() {
            that.trigger('hide');
         });
         this.$('#commonModal').on('hidden.bs.modal', function() {
            if (that.options.redirect) {
               Backbone.history.navigate('', true);
            }
            that.remove();
            app.trigger('modal:hidden');
            app.trigger('modal:removed');
         });
         this.$('#commonModal').modal({
            backdrop: true,
            show: true
         });
      },

      confirmClick: function() {
         this.trigger('confirm');
      },

      events: {
         'click [data-action="confirm"]': 'confirmClick'
      }
   });

   Common.Views.ProgressBar = Backbone.View.extend({
      template: 'common/progressBar',
      progress: 0,

      update: function(progress) {
         if (progress <= 100 && this.progress != progress) {
            this.progress = progress;
            this.$('.progress-value').html(this.progress);
            this.$('.progress-bar').css({width: this.progress + '%'}).attr('aria-valuenow', this.progress);
         }
      }
   });

   Common.Tools = {
      showUploadProgressBar: function() {
         var progressBar = new Common.Views.ProgressBar();
         app.oauth.loadAccessToken({
            success: function() {
               var progressInterval = setInterval(function() {
                  $.ajax({
                     type: 'GET',
                     url: Routing.generate('api_v1_task_user_progress'),
                     headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                     success: function(progress) {
                        if (progress != null)
                           progressBar.update(progress);
                        else {
                           progressBar.remove();
                           clearInterval(progressInterval);
                        }
                     }
                  });
               }, 2000);
            }
         });
         app.useLayout().setView('.top-progress-bar', progressBar);
      },

      hideCurrentModalIfOpened: function(callback, changeHistoryOnClose) {
         changeHistoryOnClose = typeof changeHistoryOnClose !== 'undefined' ? changeHistoryOnClose : true;

         var currentModal = app.useLayout().getView('#modal-container');
         if (currentModal) {
            currentModal.changeHistoryOnClose = changeHistoryOnClose;
            app.once('modal:hidden', function() {
               if (callback)
                  callback();
            });
            currentModal.close();
            return true;
         } else {
            if (callback)
               callback();
            return false;
         }
      },

      notLoggedModal: function(redirect, content) {
         redirect = typeof redirect !== 'undefined' ? redirect : false;
         content = typeof content !== 'undefined' ? content : 'common.contentNotLogged';
         app.useLayout().setView('#center-pane-content', new Common.Views.Modal({
            title: 'common.titleNotLogged',
            content: content,
            redirect: redirect,
            showConfirmButton: false,
            modalDialogClasses: 'notlogged-dialog'
         }), true).render();
      },

      notFound: function() {
         app.useLayout().setView('#center-pane-content', new Common.Views.Modal({
            title: 'common.titlePageNotFound',
            content: 'common.contentPageNotFound',
            redirect: true,
            showConfirmButton: false
         }), true).render();
      },

      getParameterByName: function (name) {
         name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
         var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
         return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
      },

      getHtmlErrors: function(json) {
         var errors = null;
         if (json instanceof Array)
            errors = json;
         else if (typeof json.errors !== 'undefined')
            errors = json.errors;

         if (errors && errors instanceof Array && errors.length > 0) {
            if (errors.length > 1) {
               var markup = '<ul>';
               for (var i = 0; i<errors.length; i++) {
                  markup += '<li>';
                  if (typeof errors[i].message !== 'undefined')
                     markup += $.t(errors[i].message);
                  else
                     markup += $.t(errors[i]);
                  markup += '</li>';
               }
               markup += '</ul>';
               return markup;
            } else {
               var error = errors.pop();
               if (error.message !== 'undefined')
                  return $.t(error.message);
               else
                  return $.t(error);
            }
         } else if (typeof json.message !== 'undefined') {
            return $.t(json.message);
         }
         else {
            return $.t('error.generic');
         }
      }
   }

   return Common;
});