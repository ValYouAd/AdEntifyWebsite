/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 14/06/2013
 * Time: 18:38
 * To change this template use File | Settings | File Templates.
 */
define([
   'app',
   'modules/common',
   'modules/user',
   'moment'
], function(app, Common, User, moment) {

   var Comment = app.module();

   Comment.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_comment');
      },

      toJSON: function() {
         return { comment: this.attributes };
      },

      initialize: function() {
         this.listenTo(this, {
            //'change': this.setup,
            'add': this.setup
         });
      },

      isAuthor: function() {
         return this && this.has('author') ? currentUserId === this.get('author').id : false;
      },

      setup: function() {
         if (this.has('author')) {
            this.set('authorModel', new User.Model(this.get('author')));
         }
         if (this.has('created_at')) {
            this.set('date', moment(this.get('created_at')).fromNow());
         }
      }
   });

   Comment.Collection = Backbone.Collection.extend({
      model: Comment.Model,
      cache: true,
      url: Routing.generate('api_v1_get_comments')
   });

   Comment.Views.Item = Backbone.View.extend({
      template: "comment/item",
      tagName: "li class='media comment'",
      hasRoleTeam: false,

      serialize: function() {
         return {
             model: this.model,
             has_role_team: function() {
                return Comment.Common.hasRoleTeam();
             }
         };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
         this.listenTo(this.model, "destroy", this.remove);
      },

      delete: function() {
         var that = this;
         this.model.destroy({
            url: Routing.generate('api_v1_delete_comment', { id: this.model.get('id') }),
            success: function() {
               that.remove();
            }
         });
      },

      events: {
         'click .close': 'delete'
      }
   });

   Comment.Views.List = Backbone.View.extend({
      template: "comment/list",

      serialize: function() {
          return {
              has_role_team: function() {
                 return Comment.Common.hasRoleTeam();
              }
          };
      },

      beforeRender: function() {
         if (this.options.comments.length === 0) {
            this.setView('.alert-comments', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('comment.noComments'),
               showClose: false
            }));
         } else {
            this.removeView('.alert-comments');
         }

         this.options.comments.each(function(comment) {
            this.insertView(".comments-list", new Comment.Views.Item({
               model: comment
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      initialize: function() {
         moment.lang(app.appState().getLocale());
         this.photoId = this.options.photoId;
         this.listenTo(this.options.comments, {
            'sync': function() {
               this.render();
            }
         });
         this.comments = this.options.comments;
      },

      addComment: function(e) {
         e.preventDefault();
         if (app.appState().isLogged()) {
            var that = this;
            if ($('.comment-body').val()) {
               var btn = $('.add-comment-button');
               btn.button('loading');

               var comment = new Comment.Model();
               comment.set('body', $('.comment-body').val());
               comment.set('photo', this.photoId);
               comment.url = Routing.generate('api_v1_post_comment');
               comment.getToken('comment_item', function() {
                  comment.save(null, {
                     success: function(comment) {
                        that.comments.add(comment);
                        that.render();
                        that.trigger('comment:new');
                        $('.comment-body').val('');
                     },
                     error: function() {
                        app.useLayout().setView('.alert-add-comment', new Common.Views.Alert({
                           cssClass: Common.alertError,
                           message: $.t('comment.errorCommentPost'),
                           showClose: true
                        })).render();
                     },
                     complete: function() {
                        btn.button('reset');
                     }
                  });
               });
            } else {
               app.useLayout().setView('.alert-add-comment', new Common.Views.Alert({
                  cssClass: Common.alertError,
                  message: $.t('comment.noBody'),
                  showClose: true
               })).render();
            }
         } else {
            Common.Tools.notLoggedModal(false, 'notLogged.comment');
         }
      },

      deleteAllComments: function(e) {
          e.preventDefault();

          var model;

          while (model = this.options.comments.first()) {
              model.destroy({
                  url: Routing.generate('api_v1_delete_comment', { id: model.get('id') })
              });
          }
      },

      events: {
         'click .add-comment-button': 'addComment',
         'click .delete-comment-button': 'deleteAllComments'
      }
   });

   Comment.Common = {
      hasRoleTeam: function() {
         var currentUser = app.appState().getCurrentUser();
         var roles = ['ROLE_TEAM', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN'];
         var found = false;
         _.each(roles, function(role) {
            if (!found)
               found = $.inArray(role, currentUser.roles) ? true : false;
         });
         return found;
      }
   };

   return Comment;
});