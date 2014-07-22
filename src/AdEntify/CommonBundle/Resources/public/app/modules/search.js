define([
   'app',
   'modules/common',
   'modules/photos',
   'modules/hashtag',
   'modules/brand'
], function(app, Common, Photos, Hashtag, Brand) {

   var Search = app.module();

   Search.Views.Item = Backbone.View.extend({
      template: "search/item",
      tagName: 'li',

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      },

      showPhoto: function(evt) {
         Photos.Common.showPhoto(evt, this.model);
      },

      events: {
         'click a[data-photo-link]': 'showPhoto'
      }
   });

   Search.Views.UserItem = Backbone.View.extend({
      template: "search/userItem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, 'change', this.render);
      }
   });

   Search.Views.List = Backbone.View.extend({
      template: "search/list",

      serialize: function() {
         return {
            searchUrl : app.beginUrl + app.root + $.t('routing.search/')
         };
      },

      beforeRender: function() {
         this.options.photos.each(function(photo) {
            photo.setup();
            this.insertView(".search-tags-results", new Search.Views.Item({
               model: photo
            }));
         }, this);
         this.options.users.each(function(user) {
            this.insertView(".search-users-results", new Search.Views.UserItem({
               model: user
            }));
         }, this);
         Search.Common.setupResults(this);
      },

      afterRender: function() {
         $(this.el).i18n();
         Search.Common.hideContainerWithNoResults(this);
      },

      initialize: function() {
         this.listenTo(this.options.photos, 'sync', this.render);
         this.listenTo(this.options.users, 'sync', this.render);
         this.listenTo(this.options.hashtags, 'sync', this.render);
         this.listenTo(this.options.brands, 'sync', this.render);
         this.listenTo(app, 'search:starting', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-bar .dropdown-menu').fadeIn();
               $('.search-loading').fadeIn();
               $('.alert-search-tags-results').html();
               $('.alert-search-users-results').html();
            }
         });
         this.listenTo(app, 'search:completed', function() {
            if (!this.isFullscreenSearch()) {
               $('.search-loading').stop().fadeOut();

               if (this.options.photos.length === 0 && this.options.users.length === 0 && this.options.hashtags.length === 0 && this.options.brands.length === 0) {
                  this.setView('.alert-search-results', new Common.Views.Alert({
                     cssClass: Common.alertInfo,
                     message: $.t('search.noResults'),
                     showClose: true
                  })).render();
                  $('.view-more-results').hide();
               }
               else {
                  var view = this.getView('.alert-search-results');
                  if (view) view.remove();
                  this.$('.view-more-results').show();
               }
            }
         });
         this.listenTo(app, 'search:close', function() {
            $('.search-bar .dropdown-menu').stop().fadeOut();
         });
         this.listenTo(app, 'search:show', function() {
            if (!this.isFullscreenSearch() && (this.options.photos.length > 0 || this.options.users.length > 0 || this.options.hashtags.length > 0 || this.options.brands.length > 0))
               $('.search-bar .dropdown-menu').stop().fadeIn();
         });
      },

      isFullscreenSearch: function() {
         return Backbone.history.fragment.indexOf($.t('routing.search/')) !== -1;
      }
   });

   Search.Views.Form = Backbone.View.extend({
      template: "search/searchBar",
      searchTimeout: null,
      terms: null,

      serialize: function() {
         return { terms: this.terms };
      },

      initialize: function() {
         this.requests = [];
         this.photos = this.options.photos;
         this.users = this.options.users;
         this.hashtags = this.options.hashtags;
         this.brands = this.options.brands;
         this.setView('.search-results-container', new Search.Views.List({
            photos: this.photos,
            users: this.users,
            hashtags: this.hashtags,
            brands: this.brands
         })).render();
         this.listenTo(app, 'search:start', function(terms) {
            if (terms) {
               this.terms = terms;
               this.startSearch(terms);
            }
         });
      },

      events : {
         'keydown .search-query': 'keyDown',
         'keyup .search-query': 'search',
         'click .search-button': 'search',
         'blur .search-query': 'closeSearchResults',
         'focus .search-query': 'showSearchResults'
      },

      keyDown: function(e) {
         if (e.keyCode == 13)
            e.preventDefault();
      },

      search: function(e) {
         this.terms = this.$('.search-query').val();

         if (e.keyCode == 13 || $(e.currentTarget).hasClass('search-button')) {
            Backbone.history.navigate($.t('routing.search/keywords', { 'keywords': encodeURI(this.terms) }), { trigger: true });
         } else {
            if (this.searchTimeout)
               clearTimeout(this.searchTimeout);
            var that = this;
            this.searchTimeout = setTimeout(function() {
               that.startSearch(that.terms);
            }, 500);
         }
      },

      startSearch: function(terms) {
         if (terms) {
            app.trigger('search:starting', terms);
            var that = this;
            this.requests = [];
            this.requests.push(new $.Deferred());
            this.photos.fetch({
               url: Routing.generate('api_v1_get_photo_search'),
               data: { 'query': terms },
               complete: function() {
                  var deferred = that.requests.pop();
                  if (deferred)
                     deferred.resolve();
               },
               error: function() {
                  that.setView('.alert-search-tags-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
            this.requests.push(new $.Deferred());
            this.hashtags.fetch({
               url: Routing.generate('api_v1_get_hashtag_search'),
               data: { 'query': terms },
               complete: function() {
                  var deferred = that.requests.pop();
                  if (deferred)
                     deferred.resolve();
               },
               error: function() {
                  that.setView('.alert-search-feeds-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
            this.requests.push(new $.Deferred());
            this.users.fetch({
               url: Routing.generate('api_v1_get_user_search'),
               data: { 'query': terms },
               complete: function() {
                  var deferred = that.requests.pop();
                  if (deferred)
                     deferred.resolve();
               },
               error: function() {
                  that.setView('.alert-search-users-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
            this.requests.push(new $.Deferred());
            this.brands.fetch({
               url: Routing.generate('api_v1_get_brand_search', { query: terms }),
               complete: function() {
                  var deferred = that.requests.pop();
                  if (deferred)
                     deferred.resolve();
               },
               error: function() {
                  that.setView('.alert-search-brands-results', new Common.Views.Alert({
                     cssClass: Common.alertError,
                     message: $.t('search.error'),
                     showClose: true
                  })).render();
               }
            });
            $.when.apply(null, this.requests).done(function() {
               app.trigger('search:completed');
            });
         }
      },

      closeSearchResults: function() {
         app.trigger('search:close');
      },

      showSearchResults: function() {
         app.trigger('search:show');
      },

      afterRender: function() {
         $(this.el).i18n();
      }
   });

   Search.Views.FullItem =  Backbone.View.extend({
      template: "search/fullitem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Search.Views.FullUserItem =  Backbone.View.extend({
      template: "search/fullUserItem",
      tagName: "li",

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      }
   });

   Search.Views.FullList =  Backbone.View.extend({
      template: "search/fulllist",
      tagName: 'div class="search-fulllist"',
      resultsCount: 0,

      serialize: function() {
         return {
            terms : this.terms,
            resultsCount: this.resultsCount
         };
      },

      initialize: function() {
         this.terms = typeof this.options.terms !== 'undefined' ? this.options.terms : null;
         this.listenTo(this.options.photos, 'sync', this.render);
         this.listenTo(this.options.users, 'sync', this.render);
         this.listenTo(this.options.hashtags, 'sync', this.render);
         this.listenTo(this.options.brands, 'sync', this.render);
         this.listenTo(app, 'search:starting', function(terms) {
            $('.search-loading').fadeIn();
            $('.alert-search-tags-results').html();
            $('.alert-search-users-results').html();
            this.terms = terms;
         });
         this.listenTo(app, 'search:completed', function() {
            if (this.terms.indexOf('%23') != -1) {
               $('.feeds-container').hide();
            }
         });
         if (this.terms) {
            app.trigger('search:start', this.terms);
         } else
            this.terms = $('.search-query').val();
      },

      getFullSearchUrl: function() {
         return app.beginUrl + app.root + $.t('routing.search/') + encodeURIComponent(this.terms);
      },

      beforeRender: function() {
         // Check if its a feed
         var hashtags = [];
         if (this.terms)
            hashtags = this.terms.match(/(?:^|\s)(\#\w+)/g);
         this.isFeed = hashtags && hashtags.length == 1;
         if (this.isFeed) {
            this.resultsCount = this.options.photos.length;
         } else {
            this.resultsCount = this.options.photos.length + this.options.users.length + this.options.hashtags.length + this.options.brands.length;
         }

         if (this.options.photos.length === 0) {
            this.setView('.alert-search-tags-results', new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t('search.noPhoto'),
               showClose: true
            })).render();
         } else {
            if (this.getView('.alert-search-tags-results')) {
               this.removeView('.alert-search-tags-results');
            }
         }

         if (!this.getView('.search-photos-results')) {
            var Photos = require('modules/photos');
            this.setView('.search-photos-results', new Photos.Views.Content({
               photos: this.options.photos,
               listenToEnable: true,
               pageTitle: $.t('search.pageTitle', { title: this.terms }),
               showPhotoInfo: true
            }));
         }
         if (!this.getView('.search-users-results')) {
            this.options.users.each(function(result) {
               this.insertView(".search-users-results", new Search.Views.FullUserItem({
                  model: result
               }));
            }, this);
         }
         if (!this.getView('.search-filters')) {
            var filters = new Search.Views.Filters({
               photos: this.options.photos
            });
            this.setView('.search-filters', filters);
         }
         Search.Common.setupResults(this);
      },

      afterRender: function() {
         $(this.el).i18n();

         Search.Common.hideContainerWithNoResults(this, this.isFeed);
         this.$('.search-button').popover({
            html: true,
            content: '<div class="fb-share"><div class="fb-share-button" data-href="' + this.getFullSearchUrl() + '" data-type="button"></div></div> <div class="tweet"><a href="https://twitter.com/share" class="twitter-share-button" data-via="AdEntify" data-url="' + this.getFullSearchUrl() + '" data-lang="' + currentLocale + '" data-count="none">Tweet</a></div>' +
               '<div class="g-plus"><div class="g-plusone" data-size="medium" data-annotation="none" data-href="' + this.getFullSearchUrl() + '"></div><script type="text/javascript">window.___gcfg = {lang: \'fr\'};(function() {var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;po.src = \'https://apis.google.com/js/plusone.js\';var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);})();</script></div>'
         }).on('shown.bs.popover', function() {
               FB.XFBML.parse();
               twttr.widgets.load();
            });
      }
   });

   Search.Views.FullSearch = Backbone.View.extend({
      template: "search/fullsearch"
   });

   Search.Views.Filters = Backbone.View.extend({
      template: 'search/filters',
      currentFilter: null,
      tagName: 'div class="filters-wrapper"',

      initialize: function() {
         this.photosUrl = this.options.photosUrl;
         this.photosSuccess = this.options.photosSuccess;
         this.photosError = this.options.photosError;
      },

      afterRender: function() {
         $(this.el).i18n();
      },

      activateFilter: function(newFilter) {
         if (newFilter.hasClass('active')) {
            this.activeFilter = null;
            newFilter.removeClass('active');
            return false;
         }

         if (this.activeFilter) {
            this.activeFilter.removeClass('active');
         }
         this.activeFilter = newFilter;
         this.activeFilter.addClass('active');
         return true;
      },

      mostOldestFilter: function(e) {
         var activate = this.activateFilter($(e.currentTarget).parent());
         this.loadPhotos(activate ? '&orderBy=date&way=asc' : '');
      },

      taggedPhotosFilter: function(e) {
         var activate = this.activateFilter($(e.currentTarget).parent());
         this.loadPhotos(activate ? '&tagged=1' : '');
      },

      mostRecentFilter: function(e) {
         var activate = this.activateFilter($(e.currentTarget).parent());
         this.loadPhotos(activate ? '&orderBy=date' : '');
      },

      mostLikedFilter: function(e) {
         var activate = this.activateFilter($(e.currentTarget).parent());
         this.loadPhotos(activate ? '&orderBy=mostLiked' : '');
      },

      loadPhotos: function(query) {
         this.options.photos.fetch({
            url: this.getOriginalPhotosUrl() + query,
            reset: true,
            success: this.photosSuccess,
            error: this.photosError
         });
      },

      getOriginalPhotosUrl: function() {
         return Routing.generate('api_v1_get_photo_search', {query: $('.search-query').val() });
      },

      events: {
         'click .mostOldest-filter': 'mostOldestFilter',
         'click .taggedPhotos-filter': 'taggedPhotosFilter',
         'click .mostRecent-filter': 'mostRecentFilter',
         'click .mostLiked-filter': 'mostLikedFilter'
      }
   });

   Search.Common = {
      setupResults: function(context) {
         if (!context.getView('.search-feeds-results')) {
            context.options.hashtags.each(function(result) {
               context.insertView(".search-feeds-results", new Hashtag.Views.Item({
                  model: result
               }));
            }, context);
         }
         if (!context.getView('.search-brands-results')) {
            context.options.brands.each(function(result) {
               context.insertView(".search-brands-results", new Brand.Views.Item({
                  model: result
               }));
            }, context);
         }
      },
      
      hideContainerWithNoResults: function(context, feed) {
         if (context.options.users.length === 0 || feed) {
            context.$('.users-container').fadeOut();
         } else {
            if (context.$('.users-container:visible').length === 0)
               context.$('.users-container').stop().fadeIn();
         }
         if (context.options.hashtags.length === 0 || feed) {
            context.$('.feeds-container').fadeOut();
         } else {
            if (context.$('.feeds-container:visible').length === 0)
               context.$('.feeds-container').stop().fadeIn();
         }
         if (context.options.photos.length === 0) {
            context.$('.search-filters').fadeOut();
         } else {
            if (context.$('.search-filters:visible').length === 0)
               context.$('.search-filters').stop().fadeIn();
         }
         if (context.options.brands.length === 0 || feed) {
            context.$('.brands-container').fadeOut();
         } else {
            if (context.$('.brands-container:visible').length === 0)
               context.$('.brands-container').stop().fadeIn();
         }
         if (feed) {
            context.$('.search-title').hide();
         } else {
            context.$('.search-title').show();
         }
      }
   };

   return Search;
});