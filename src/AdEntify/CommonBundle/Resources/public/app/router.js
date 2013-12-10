define([
   // Application.
   "app",

   // Modules
   "modules/facebook",
   "modules/homepage",
   "modules/photos",
   "modules/upload",
   "modules/facebookAlbums",
   "modules/facebookPhotos",
   "modules/instagramPhotos",
   "modules/adentifyOAuth",
   "modules/flickrSets",
   "modules/flickrPhotos",
   "modules/externalServicePhotos",
   "modules/photo",
   "modules/brand",
   "modules/mySettings",
   "modules/user",
   "modules/common",
   "modules/category",
   "modules/search",
   "modules/comment",
   "modules/notifications",
   'modules/action',
   'modules/hashtag'
],

function(app, Facebook, HomePage, Photos, Upload, FacebookAlbums, FacebookPhotos, InstagramPhotos,
         AdEntifyOAuth, FlickrSets, FlickrPhotos, ExternalServicePhotos, Photo, Brand, MySettings, User,
         Common, Category, Search, Comment, Notifications, Action, Hashtag) {

   var searchSetup = false;
   var notificationsSetup = false;
   var dropdownMenusSetup = false;

   var Router = Backbone.Router.extend({
      initialize: function() {
         this.listenTo(this, {
            'route': this.routeTriggered
         });

         // Handle window event
         this.handleWindowEvent();

         // Initialize authent
         app.fb = new Facebook.Model();
         // Get AdEntify accesstoken for AdEntify API
         app.oauth = new AdEntifyOAuth.Model();
         app.oauth.loadAccessToken();

         // Facebook
         FB.init({
            appId      : facebookAppId,                                   // App ID from the app dashboard
            channelUrl : channelUrl,  // Channel file for x-domain comms
            status     : false,                                                // Check Facebook Login status
            xfbml      : true,                                               // Look for social plugins on the page
            cookie     : true,
            oauth      : true
         });
         FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
               app.fb.connected(response);
            }
         });

         // Collections init
         var collections = {
            photos: new Photos.Collection(),
            tickerPhotos: new Photos.Collection(),
            myPhotos: new Photos.Collection(),
            myTickerPhotos: new Photos.Collection(),
            fbAlbums: new FacebookAlbums.Collection(),
            fbPhotos: new FacebookPhotos.Collection(),
            istgPhotos : new InstagramPhotos.Collection(),
            flrSets: new FlickrSets.Collection(),
            flrPhotos: new FlickrPhotos.Collection(),
            brands: new Brand.Collection(),
            categories: new Category.Collection(),
            photoCategories: new Category.Collection(),
            searchPhotos: new Photos.Collection(),
            searchUsers: new User.Collection(),
            searchHashtags: new Hashtag.Collection(),
            searchBrands: new Brand.Collection(),
            comments: new Comment.Collection(),
            notifications: new Notifications.Collection(),
            users: new User.Collection(),
            actions: new Action.Collection(),
            hashtags: new Hashtag.Collection()
         };
         _.extend(this, collections);

         // Setup Search, notifications, dropdown menus...
         this.setupEnvironment();

         // Dom events
         this.listenTo(app, 'domchange:title', this.onDomChangeTitle);
         this.listenTo(app, 'domchange:description', this.onDomChangeDescription);

         // Handle url parameters
         this.checkUrlQuery();
      },

      routes: function() {
         i18nRoutes = {
            "fr": {
               "": "homepage",
               "mes/photos/": "myPhotos",
               "mes/photos/favorites/": "favoritesPhotos",
               "photo/:id/": "viewPhoto",
               "edition/photo/:id/": "editPhoto",
               "upload/": "upload",
               "upload/local/": "uploadLocal",
               "mes/parametres/": "mySettings",
               "facebook/albums/": "facebookAlbums",
               "facebook/albums/:id/photos/": "facebookAlbumsPhotos",
               "instagram/photos/": "instagramPhotos",
               "flickr/sets/": "flickrSets",
               "flickr/sets/:id/photos/": "flickrPhotos",
               "marques/": "viewBrands",
               "marque/:slug/": "viewBrand",
               "mon/profil/": "myProfile",
               "profil/:id/": "profile",
               "categorie/:slug/": "category",
               "mon/adentify/": "myAdentify",
               "recherche/": "search",
               "recherche/:keywords": "search",

               '*notFound': 'notFound'
            },
            "en" : {
               "": "homepage",
               "upload/": "upload",
               "upload/local/": "uploadLocal",
               "my/photos/": "myPhotos",
               "my/settings/": "mySettings",
               "facebook/albums/": "facebookAlbums",
               "facebook/albums/:id/photos/": "facebookAlbumsPhotos",
               "instagram/photos/": "instagramPhotos",
               "flickr/sets/": "flickrSets",
               "flickr/sets/:id/photos/": "flickrPhotos",
               "photo/:id/": "viewPhoto",
               "brands/": "viewBrands",
               "brand/:slug/": "viewBrand",
               "my/profile/": "myProfile",
               "profile/:id/": "profile",
               "category/:slug/": "category",
               "my/adentify/": "myAdentify",
               "my/photos/favorites/": "favoritesPhotos",
               "search/": "search",
               "search/:keywords": "search",

               '*notFound': 'notFound'
            }
         };
         switch (app.appState().getLocale()) {
            case 'fr':
               return i18nRoutes.fr;
            case 'en':
               return i18nRoutes.en;
            default:
               return i18nRoutes.en;
         }
      },

      homepage: function() {
         this.reset();

         var that = this;

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.photos,
               photosSuccess: function(collection) {
                  that.successCallback(collection, 'photos.noPhotos');
               },
               photosError: function() {
                  that.errorCallback('photos.errorPhotosLoading');
               },
               tagged: true,
               filters: true,
               listenToEnable: true
            }),
            "#right-pane-content": new Action.Views.List({
               actions: this.actions
            })
         }).render();

         this.photos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading');
            }
         });
         this.actions.fetch({
            url: Routing.generate('api_v1_get_actions'),
            success: function(collection) {
               that.successCallback(collection, 'action.noActions', '#right-pane-content');
            },
            error: function() {
               that.errorCallback('action.errorLoading', '#right-pane-content');
            }
         });
      },

      myPhotos: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.myPhotos,
               pageTitle: $.t('myPhotos.pageTitleMyPhotos'),
               title: $.t('myPhotos.titleMyPhotos'),
               itemClickBehavior: Photos.Common.PhotoItemClickBehaviorAddTag,
               addTag: true,
               showServices: true,
               filters: true
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         var that = this;
         this.myPhotos.fetch({
            url: Routing.generate('api_v1_get_user_photos', { id: app.appState().getCurrentUserId() }),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading');
            }
         });
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      upload: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new Upload.Views.Content(),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
            this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      uploadLocal: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new Upload.Views.LocalUpload({
               categories: this.categories
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         this.categories.fetch();
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      facebookAlbums: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new FacebookAlbums.Views.List({
               albums: this.fbAlbums,
               categories: this.categories
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         this.categories.fetch();
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      facebookAlbumsPhotos: function(id) {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new FacebookPhotos.Views.List({
               albumId: id,
               photos: this.fbPhotos,
               categories: this.categories
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         this.categories.fetch();
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      instagramPhotos: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new InstagramPhotos.Views.List({
               photos: this.istgPhotos,
               categories: this.categories
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         this.categories.fetch();
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      flickrSets: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset();

         app.useLayout().setViews({
            "#center-pane-content": new FlickrSets.Views.List({
               sets: this.flrSets,
               categories: this.categories
            }),
            "#right-pane-content": new ExternalServicePhotos.Views.MenuRightAlbums({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      flickrPhotos: function(id) {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset();

         app.useLayout().setViews({
            "#center-pane-content": new FlickrPhotos.Views.List({
               photos: this.flrPhotos,
               albumId: id
            }),
            "#right-pane-content": new ExternalServicePhotos.Views.MenuRightPhotos({
               categories: this.categories
            })
         }).render();

         this.categories.fetch();
      },

      viewPhoto: function(id) {
         this.reset();

         var photo = new Photo.Model({ 'id': id });
         app.useLayout().setViews({
            "#center-pane-content": new Photo.Views.Item({
               photo: photo,
               comments: this.comments,
               photoId: id,
               categories: this.categories,
               hashtags: this.hashtags
            }),
            "#right-pane-content": new Photo.Views.RightMenu({
               photo: photo,
               tickerPhotos: this.tickerPhotos,
               tagged: false
            })
         }).render();

         photo.fetch();
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photo_linked_photos', { id: id })
         });
         this.comments.fetch({
            url: Routing.generate('api_v1_get_photo_comments', { id: id })
         });
         this.categories.fetch({
            url: Routing.generate('api_v1_get_photo_categories', { id: id })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_photo_hashtags', { id: id })
         });
      },

      viewBrands: function() {
         this.reset(false, false);
         $('html, body').addClass('body-grey-background');

         app.useLayout().setViews({
            "#center-pane-content": new Brand.Views.List({
               brands: this.brands
            })
         }).render();

         this.brands.fetch();
      },

      viewBrand: function(slug) {
         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         // Get brand info
         var brand = new Brand.Model({
            id: slug
         });

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.photos,
               title: $.t('brand.titleViewBrand')
            }),
            "#left-pane": new Brand.Views.MenuLeft({
               model: brand,
               slug: slug,
               followers: this.users,
               photos: this.photos,
               categories: this.categories
            })
         }).render();

         var that = this;

         brand.fetch({
            url: Routing.generate('api_v1_get_brand', { slug: slug }),
            success: function() {
               app.trigger('domchange:title', $.t('brand.pageTitleViewBrand', { name: brand.get('name') }));
            }
         });

         this.categories.fetch({
            url: Routing.generate('api_v1_get_brand_categories', { slug: slug, locale: app.appState().getLocale() })
         });

         // Get brand photos
         this.photos.fetch({
            url: Routing.generate('api_v1_get_brand_photos', { slug: slug }),
            success: function(collection) {
               that.successCallback(collection, 'brand.noPhotos');
            },
            error: function() {
               that.errorCallback('brand.errorPhotosLoading');
            }
         });

         this.users.fetch({
            url: Routing.generate('api_v1_get_brand_followers', { slug: slug })
         });
      },

      mySettings: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new MySettings.Views.Detail(),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: app.appState().getCurrentUserId()
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags,
               showServices: true
            })
         }).render();

         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: app.appState().getCurrentUserId() })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: app.appState().getCurrentUserId() })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: app.appState().getCurrentUserId() })
         });
      },

      profile: function(id) {
         this.reset(true, false);
         $('html, body').addClass('body-grey-background');

         var followers = new User.Collection();
         var followings = new User.Collection();

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.photos,
               userId: id,
               tagged: true,
               title: $.t('profile.pageHeading')
            }),
            "#left-pane": new User.Views.MenuLeft({
               user: new User.Model({
                  id: id
               }),
               followings: followings,
               followers: followers,
               photos: this.photos,
               hashtags: this.hashtags
            })
         }).render();

         this.photos.fetch({
            url: Routing.generate('api_v1_get_user_photos', { id: id })
         });
         followings.fetch({
            url: Routing.generate('api_v1_get_user_followings', { id: id })
         });
         followers.fetch({
            url: Routing.generate('api_v1_get_user_followers', { id: id })
         });
         this.hashtags.fetch({
            url: Routing.generate('api_v1_get_user_hashtags', { id: id })
         });
      },

      category: function(slug) {
         this.reset();

         var category = new Category.Model();
         var that = this;

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.photos,
               photosSuccess: function(collection) {
                  that.successCallback(collection, 'category.noPhotos');
               },
               photosError: function() {
                  that.errorCallback('category.errorPhotosLoading');
               },
               category: category,
               listenToEnable: true,
               filters: true,
               photosUrl: Routing.generate('api_v1_get_category_photos', { slug: slug, tagged: true })
            }),
            "#right-pane-content": new Action.Views.List({
               actions: this.actions
            })
         }).render();

         // Get category
         if (this.categories.length > 0) {
            var foundCategory = _.first(this.categories.where({ slug: slug }));
            if (foundCategory) {
               category.set('name', foundCategory.get('name'));
               that.onDomChangeTitle($.t('category.pageTitle', { 'name': foundCategory.get('name') }));
            }
         } else {
            this.categories.fetch({
               success: function(collection) {
                  var foundCategory = _.first(collection.where({ slug: slug }));
                  if (foundCategory) {
                     category.set('name', foundCategory.get('name'));
                     that.onDomChangeTitle($.t('category.pageTitle', { 'name': foundCategory.get('name') }));
                  }
               }
            });
         }

         // Get category photos
         this.photos.fetch({
            url: Routing.generate('api_v1_get_category_photos', { slug: slug, tagged: true }),
            success: function(collection) {
               that.successCallback(collection, 'category.noPhotos');
            },
            error: function() {
               that.errorCallback('category.errorPhotosLoading');
            }
         });
         this.actions.fetch({
            url: Routing.generate('api_v1_get_actions'),
            success: function(collection) {
               that.successCallback(collection, 'action.noActions', '#right-pane-content');
            },
            error: function() {
               that.errorCallback('action.errorLoading', '#right-pane-content');
            }
         });
      },

      myAdentify: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset();

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.photos,
               pageTitle: $.t('myAdentify.pageTitle'),
               title: $.t('myAdentify.title')
            }),
            "#right-pane-content": new Photos.Views.Ticker({
               tickerPhotos: this.tickerPhotos
            })
         }).render();

         var that = this;
         this.photos.fetch({
            url: Routing.generate('api_v1_get_user_feed'),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading');
            }
         });
         this.tickerPhotos.fetch({
            url: Routing.generate('api_v1_get_photos', { tagged: false }),
            success: function(collection) {
               that.successCallback(collection, 'photos.noPhotos', '#right-pane-content');
            },
            error: function() {
               that.errorCallback('photos.errorPhotosLoading', '#right-pane-content');
            }
         });
      },

      favoritesPhotos: function() {
         if (!app.appState().isLogged()) {
            Common.Tools.notLoggedModal(true);
            return;
         }

         this.reset();

         app.useLayout().setViews({
            "#center-pane-content": new Photos.Views.Content({
               photos: this.myPhotos,
               tagged: true,
               title: $.t('myPhotos.titleFavorites')
            }),
            "#right-pane-content": new Action.Views.List({
               actions: this.actions
            })
         }).render();

         var that = this;
         this.myPhotos.fetch({
            url: Routing.generate('api_v1_get_user_favorites'),
            success: function(collection) {
               that.successCallback(collection, 'myPhotos.noPhotos');
            },
            error: function() {
               that.errorCallback('myPhotos.errorPhotosLoading');
            }
         });
         this.actions.fetch({
            url: Routing.generate('api_v1_get_actions'),
            success: function(collection) {
               that.successCallback(collection, 'action.noActions', '#right-pane-content');
            },
            error: function() {
               that.errorCallback('action.errorLoading', '#right-pane-content');
            }
         });
      },

      search: function(keywords) {
         this.reset(false, false);
         $('html, body').addClass('body-grey-background');

         app.useLayout().setViews({
            "#center-pane-content": new Search.Views.FullList({
               photos: this.searchPhotos,
               users: this.searchUsers,
               hashtags: this.searchHashtags,
               brands: this.searchBrands,
               terms: keywords
            })
         }).render();
      },

      notFound: function() {
         Common.Tools.notFound();
      },

      reset: function(activeLeftPane, activeRightPane) {
         this.activePane(activeLeftPane, activeRightPane);

         if (this.photos.length) {
            this.photos.fullReset();
         }
         if (this.tickerPhotos.length) {
            this.tickerPhotos.fullReset();
         }
         if (this.fbAlbums.length) {
            this.fbAlbums.fullReset();
         }
         if (this.fbPhotos.length) {
            this.fbPhotos.fullReset();
         }
         if (this.istgPhotos.length) {
            this.istgPhotos.fullReset();
         }
         if (this.flrSets.length) {
            this.flrSets.fullReset();
         }
         if (this.flrPhotos.length) {
            this.flrPhotos.fullReset();
         }
         if (this.myPhotos.length) {
            this.myPhotos.fullReset();
         }
         if (this.myTickerPhotos.length) {
            this.myTickerPhotos.fullReset();
         }
         if (this.brands.length) {
            this.brands.fullReset();
         }
         if (this.categories.length) {
            this.categories.fullReset();
         }
         if (this.users.length) {
            this.users.fullReset();
         }
         if (this.actions.length) {
            this.actions.fullReset();
         }
         if (this.comments.length) {
            this.comments.fullReset();
         }
         if (this.hashtags.length) {
            this.hashtags.fullReset();
         }
         if ($('html, body').hasClass('body-grey-background')) {
            $('html, body').removeClass('body-grey-background');
         }
      },

      setupEnvironment: function() {
         // Add search form if not already set
         if (!searchSetup) {
            searchSetup = true;
            app.useLayout().setView('#search-bar', new Search.Views.Form({
               photos: this.searchPhotos,
               users: this.searchUsers,
               hashtags: this.searchHashtags,
               brands: this.searchBrands
            })).render();
         }
         if (!notificationsSetup) {
            notificationsSetup = true;
            app.useLayout().setView('#notifications', new Notifications.Views.List({
               notifications: this.notifications
            })).render();
            $('.profile-picture-wrapper .profile-picture').click(function(e) {
               app.trigger('notifications:click', e);
            });
         }
         if (!dropdownMenusSetup) {
            dropdownMenusSetup = true;
            User.Dropdown.listenClick();
         }
      },

      // Shortcut for building a url.
      go: function() {
         return this.navigate(_.toArray(arguments).join("/"), true);
      },

      activePane: function(leftActive, rightActive) {
         leftActive = typeof leftActive !== 'undefined' ? leftActive : false;
         rightActive = typeof rightActive !== 'undefined' ? rightActive : true;
         if (leftActive || rightActive) {
            if (!$('#center-pane').hasClass('col-sm-12 col-md-9'))
               $('#center-pane').removeClass().addClass('col-sm-12 col-md-9');
         } else {
            if (!$('#center-pane').hasClass('col-sm-12 col-md-12'))
               $('#center-pane').removeClass().addClass('col-sm-12 col-md-12');
         }

         if (leftActive && !rightActive) {
            this.changeVisiblityRightPane(false);
            this.changeVisiblityLeftPane(true);
         } else if (rightActive && !leftActive) {
            this.changeVisiblityLeftPane(false);
            this.changeVisiblityRightPane(true);
         } else if (leftActive && rightActive) {
            this.changeVisiblityLeftPane(true);
            this.changeVisiblityRightPane(true);
         } else {
            this.changeVisiblityLeftPane(false);
            this.changeVisiblityRightPane(false);
         }
      },

      changeVisiblityLeftPane: function(show) {
         if (show) {
            if ($('#left-pane').hasClass('hide'))
               $('#left-pane').removeClass('hide');
            $('#center-pane').removeClass('col-sm-12').addClass('col-sm-9');
         } else {
            if ($('#center-pane').hasClass('col-sm-9'))
               $('#center-pane').removeClass('col-sm-9').addClass('col-sm-12');
            if (!$('#left-pane').hasClass('hide'))
               $('#left-pane').addClass('hide');
         }
      },

      changeVisiblityRightPane: function(show) {
         if (show) {
            if ($('#right-pane').hasClass('hide'))
               $('#right-pane').removeClass('hide');
         } else {
            if (!$('#right-pane').hasClass('hide'))
               $('#right-pane').addClass('hide');
         }
      },

      // Change title of window
      onDomChangeTitle: function(title) {
         if (typeof title !== 'undefined' && title != '') {
            $(document).attr('title', title);
         }
      },

      onDomChangeDescription: function(description) {
         if (typeof description !== 'undefined' && description != '') {
            $('meta[name=description]').attr('content', description);
         }
      },

      routeTriggered: function(e) {
         app.stopLoading();
         // Analytics
         var url = Backbone.history.root + Backbone.history.getFragment();
         ga('send', 'pageview', url);
         // Scroll to top
         $("html, body").animate({ scrollTop: 0 }, 'fast');
      },

      successCallback: function(collection, translationKey, target) {
         target = (typeof target === "undefined") ? "#center-pane-content" : target;
         this.deleteOldAlerts();

         // Check if collection is empty
         if (collection.length == 0) {
            this.successView = new Common.Views.Alert({
               cssClass: Common.alertInfo,
               message: $.t(translationKey)
            });
            app.useLayout().setView(target, this.successView, true).render();
         }
      },

      errorCallback: function(translationKey, target) {
         target = (typeof target === "undefined") ? "#center-pane-content" : target;
         this.deleteOldAlerts();

         this.errorView = new Common.Views.Alert({
            cssClass: Common.alertError,
            message: $.t(translationKey),
            showClose: true
         });
         app.useLayout().setView(target, this.errorView, true).render();
      },

      deleteOldAlerts: function() {
         if (this.successView) {
            app.useLayout().removeView(this.successView);
            this.successView = null;
         }
         if (this.errorView) {
            app.useLayout().removeView(this.errorView);
            this.errorView = null;
         }
      },

      handleWindowEvent: function() {
         if (accountEnabled == 0) {
            $('#accountDisabled').modal('show');
         }
         window.onpopstate = function() {
            if (Common.Tools.hideCurrentModalIfOpened(null, false)) {
               return;
            }
         };
      },

      checkUrlQuery: function() {
         if (Common.Tools.getParameterByName('showLinkedAccount') && app.appState().isLogged()) {
            app.useLayout().setView('#modal-container', new Common.Views.Modal({
               title: 'profile.myLinkedServices',
               view: new MySettings.Views.ServiceList({
                  showLabel: true
               }),
               redirect: true,
               showConfirmButton: false,
               modalDialogClasses: 'linkedaccount-dialog'
            })).render();
         }
      }
   });

   return Router;
});
