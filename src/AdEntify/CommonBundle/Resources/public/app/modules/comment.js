/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 14/06/2013
 * Time: 18:38
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/common"
], function(app, Common) {

   var Comment = app.module();

   Comment.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_comment');
      },

      toJSON: function() {
         return { comment: this.attributes }
      },

      initialize: function() {
         this.listenTo(this, {
            'change': this.setup,
            'add': this.setup
         });
      },

      setup: function() {
         if (this.has('author')) {
            this.set('profileLink', app.beginUrl + app.root + $.t('routing.profile/id/', { id: this.get('author')['id'] }));
            this.set("fullname", this.get("author")["firstname"] + ' ' + this.get("author")["lastname"]);
         }
         if (this.has('created_at')) {
            this.set('date', app.formatDate(this.get('created_at')));
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
      tagName: "li class='media'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Comment.Views.List = Backbone.View.extend({
      template: "comment/list",

      beforeRender: function() {
         this.options.comments.each(function(comment) {
            this.insertView(".comments-list", new Comment.Views.Item({
               model: comment
            }));
         }, this);
      },

      afterRender: function() {
         $(this.el).i18n();
         if (this.loaded) {
            if (this.options.comments.length == 0) {
               app.useLayout().setView('.alert-comments', new Common.Views.Alert({
                  cssClass: Common.alertInfo,
                  message: $.t('comment.noComments'),
                  showClose: true
               })).render();
            }
         }
      },

      initialize: function() {
         this.photoId = this.options.photoId;
         this.listenTo(this.options.comments, {
            "sync": function() {
               this.loaded = true;
               this.render();
            }
         });
         this.comments = this.options.comments;
      },

      addComment: function(e) {
         e.preventDefault();
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
                     btn.button('reset');
                     that.comments.add(comment);
                     that.render();
                     $('.comment-body').val('');
                  },
                  error: function() {
                     app.useLayout().setView('.alert-add-comment', new Common.Views.Alert({
                        cssClass: Common.alertError,
                        message: $.t('comment.errorCommentPost'),
                        showClose: true
                     })).render();
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
      },

      events: {
         "submit": "addComment"
      }
   });

   return Comment;
});