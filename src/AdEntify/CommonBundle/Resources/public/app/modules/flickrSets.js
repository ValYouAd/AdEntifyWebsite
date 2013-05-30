/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "hmacsha1"
], function(app) {

   var FlickrSets = app.module();
   var error = '';
   var loaded = false;

   FlickrSets.Model = Backbone.Model.extend({
      title: '',
      description: '',
      id: '',

      initialize: function() {
         this.set('title', this.attributes.title._content);
         this.set('description', this.attributes.description._content);
         this.set('id', this.attributes.id);
      }
   });

   FlickrSets.Collection = Backbone.Collection.extend({
      model: FlickrSets.Model,
      cache: true
   });

   FlickrSets.Views.Item = Backbone.View.extend({
      template: "flickrSets/item",

      tagName: "li class='span2'",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   FlickrSets.Views.List = Backbone.View.extend({
      template: "flickrSets/list",

      beforeRender: function() {
         this.options.sets.each(function(album) {
            this.insertView("#sets-list", new FlickrSets.Views.Item({
               model: album
            }));
         }, this);
      },

      afterRender: function() {
         if (loaded) {
            $('#loading-sets').hide();
         }
      },

      initialize: function() {
         var that = this;

         // Get flickr token
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  url: Routing.generate('api_v1_get_oauthuserinfos'),
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  success: function(data) {
                     if (!data || data.error) {
                        error = data.error;
                     } else {
                        var flickrOAuthInfos = _.find(data, function(service) {
                           if (service.service_name == 'flickr') {
                              return true;
                           } else { return false; }
                        });
                        // Connect to Flickr API
                        if (flickrOAuthInfos) {
                           $.ajax({
                              url: 'http://api.flickr.com/services/rest/?method=flickr.photosets.getList&format=json&api_key=370e2e2f28c0ca81fd6a5a336a6e2c89'
                                 + '&user_id='+ flickrOAuthInfos.service_user_id + '&jsoncallback=?',
                              dataType: 'jsonp',
                              success: function(response) {
                                 var sets = [];
                                 for (var i= 0, l=response.photosets.photoset.length; i<l; i++) {
                                    sets[i] = response.photosets.photoset[i];
                                 }
                                 that.options.sets.add(sets);
                                 loaded = true;
                              },
                              error : function() {
                                 // TODO : error
                                 console.log('impossible de récupérer les albums Flickr');
                              }
                           });
                        } else {
                           // TODO : Redirect to error page
                        }
                     }
                  },
                  error: function() {
                     error = 'Can\'t get instagram token.';
                  }
               });
            }
         });

         this.listenTo(this.options.sets, {
            "add": this.render
         });
      }
   });

   return FlickrSets;
});