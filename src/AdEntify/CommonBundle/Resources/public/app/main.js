require([
  // Application.
  "app",

  // Main Router.
  "router",

   "i18next2",

   // App State
   "modules/appState",
   "modules/tagStats",
   "modules/photoActions",
   "modules/common"
],

function(app, Router, i18n, AppState, TagStats, PhotoActions) {

   // Extend App
   _.extend(app, {
      appState: function() {
         if (typeof this.appstate === 'undefined')
            this.appstate = new AppState.Model();
         return this.appstate;
      },
      tagStats: function() {
         if (typeof this.tagstats === 'undefined')
            this.tagstats = new TagStats.Model();
         return this.tagstats;
      },
      photoActions: function() {
         if (typeof this.photoActionsObj === 'undefined')
            this.photoActionsObj = new PhotoActions.Model();
         return this.photoActionsObj;
      },
      formatDate: function(stringDate) {
         var date = new Date(stringDate);
         return $.t('common.formatDate', { 'day': date.getDate(), 'month' : date.getMonth(), 'year': date.getFullYear(), 'hours': date.getHours(), 'minutes': date.getMinutes() });
      }
   });

   // Extend Backbone Model
   _.extend(Backbone.Model.prototype, {
      sync: function(method, model, options) {
         if (!options.headers) {
            app.oauth.loadAccessToken({
               success: function() {
                     options.headers = { 'Authorization': app.oauth.getAuthorizationHeader() };
                     return Backbone.sync(method, model, options);
                  },
               error: function() {
                  window.location.href = Routing.generate('home_logoff', { '_locale': app.appState().getLocale() });
               }
            });
         } else {
            return Backbone.sync(method, model, options);
         }
      },

      getToken: function(intention, callback) {
         var that = this;
         app.oauth.loadAccessToken({
            success: function() {
               $.ajax({
                  headers : {
                     "Authorization": app.oauth.getAuthorizationHeader()
                  },
                  url: Routing.generate('api_v1_get_csrftoken', { intention: intention}),
                  success: function(data) {
                        that.set('_token', data);
                     if (callback)
                        callback();
                  }
               });
            },
            error: function () {
               window.location.href = Routing.generate('home_logoff', { '_locale': app.appState().getLocale() });
            }
         });
      }
   });

   // Extend Backbone Collection
   _.extend(Backbone.Collection.prototype, {
      sync: function(method, collection, options) {
         if (!options.headers) {
            app.oauth.loadAccessToken({
               success: function() {
                  options.headers = { 'Authorization': app.oauth.getAuthorizationHeader() };
                  return Backbone.sync(method, collection, options);
               },
               error: function() {
                  window.location.href = Routing.generate('home_logoff', { '_locale': app.appState().getLocale() });
               }
            });
         } else {
            return Backbone.sync(method, collection, options);
         }
      },
      parse: function(obj) {
         // Check if there is pagination
         if (typeof obj !== 'undefined' && typeof obj.data !== 'undefined') {
            if (typeof obj.paging !== 'undefined') {
               if (typeof obj.paging.next !== 'undefined')
                  this.next = obj.paging.next
               if (typeof obj.paging.previous !== 'undefined')
                  this.previous = obj.paging.previous
            }
            return obj.data;
         }
         else return obj;
      },
      hasNextPage: function() {
         return ((typeof this.next !== 'undefined') && this.next);
      },
      nextPage: function(success, error) {
         if (!this.hasNextPage())
            return false;

         return this.fetch({
            url: this.next,
            remove: false,
            success: success,
            error: error
         });
      },
      hasPreviousPage: function() {
         return ((typeof this.previous !== 'undefined') && this.previous);
      },
      previousPage: function(success, error) {
         if (!this.hasPreviousPage())
            return false;

         return this.fetch({
            url: this.previous,
            remove: false,
            success: success,
            error: error
         });
      },
      fullReset: function() {
         // Delete previous pagination
         delete this.next;
         delete this.previous;
         return Backbone.Collection.prototype.reset.apply(this, arguments);
      }
   });

   // Extend Backbone View
   _.extend(Backbone.View.prototype, {
      stopLoading: function() {
         app.stopLoading();
      },
      startLoading: function() {
         app.startLoading();
      }
   });

   i18n.init({
      lng: app.appState().getLocale(),
      resGetPath: app.rootUrl + "bundles/adentifycommon/app/locales/__lng__/__ns__.json",
      ns: { namespaces: ['adentify'], defaultNs: 'adentify'},
      useDataAttrOptions: true
   }).done(function() {
      // Define your master router on the application namespace and trigger all
      // navigation from this instance.
      app.router = new Router();

      // Trigger the initial route and enable HTML5 History API support, set the
      // root folder to '/' by default.  Change in app.js.
      Backbone.history.start({ pushState: true, root: app.root });
   });

  // All navigation that is relative should be passed through the navigate
  // method, to be processed by the router. If the link has a `data-bypass`
  // attribute, bypass the delegation completely.
  $(document).on("click", "a[href]:not([data-bypass])", function(evt) {
    // Get the absolute anchor href.
    var href = { prop: $(this).prop("href"), attr: $(this).attr("href") };
    // Get the absolute root.
    var root = location.protocol + "//" + location.host + app.root;

    // Ensure the root is part of the anchor href, meaning it's relative.
    if (href.prop.slice(0, root.length) === root) {
       // Stop the default event to ensure the link will not cause a page
       // refresh.
       evt.preventDefault();
       app.appState().setLastClickedAhref(evt);

       if (href.attr.replace(root, '') != window.location.href.replace(root, '')) {
          app.startLoading(function() {
             // `Backbone.history.navigate` is sufficient for all Routers and will
             // trigger the correct events. The Router's internal `navigate` method
             // calls this anyways.  The fragment is sliced from the root.
             Backbone.history.navigate(href.attr.replace(root, ''), { trigger: true });
          });
       } else {
          app.useLayout().setView('#content', new Common.Views.Modal({
             title: 'common.titleSamePage',
             content: 'common.contentSamePage'
          }), true).render();
       }
    }
  });

});
