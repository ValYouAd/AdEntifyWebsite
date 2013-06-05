/**
 * Created with JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/04/2013
 * Time: 11:33
 * To change this template use File | Settings | File Templates.
 */
define([
   "app",
   "modules/venue",
   "modules/person",
   "bootstrap",
   "modernizer"
], function(app, Venue, Person) {

   var Tag = app.module();
   var currentPhotoOverlay = null;
   var currentTag = null;
   var tags = null;
   var currentVenues = {};
   var currentVenue = null;
   var currentPerson = null;
   var venuesSearchTimeout = null;

   Tag.Model = Backbone.Model.extend({
      urlRoot: function() {
         return Routing.generate('api_v1_get_tag');
      },

      toJSON: function() {
         var jsonAttributes = this.attributes;
         delete jsonAttributes.class;
         return { tag: jsonAttributes }
      }
   });

   Tag.Collection = Backbone.Collection.extend({
      model: Tag.Model,
      cache: true
   });

   Tag.Views.Item = Backbone.View.extend({
      template: "tag/types/item",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         clearTimeout(this.hoverTimeout);
         $(this.el).find('.popover').show();
         app.tagStats().tagHover(this.model);
      },

      hoverOut: function() {
         var that = this;
         this.hoverTimeout = setTimeout(function() {
            $(that.el).find('.popover').hide();
         }, 200);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut"
      }
   });

   Tag.Views.PersonItem = Backbone.View.extend({
      template: "tag/types/person",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         clearTimeout(this.hoverTimeout);
         $(this.el).find('.popover').show();
         app.tagStats().tagHover(this.model);
      },

      hoverOut: function() {
         var that = this;
         this.hoverTimeout = setTimeout(function() {
            $(that.el).find('.popover').hide();
         }, 200);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut"
      }
   });

   Tag.Views.VenueItem = Backbone.View.extend({
      template: "tag/types/venue",
      tagName: "li",
      hoverTimeout: null,

      serialize: function() {
         return { model: this.model };
      },

      initialize: function() {
         this.listenTo(this.model, "change", this.render);
      },

      hoverIn: function() {
         clearTimeout(this.hoverTimeout);
         $(this.el).find('.popover').show();
         if (!this.model.has('map_loaded')) {
            $map = $(this.el).find('.map');
            var latLng = new google.maps.LatLng(this.model.get('venue').lat, this.model.get('venue').lng);
            var mapOptions = {
               zoom:  14,
               center: latLng,
               scrollwheel: false,
               navigationControl: false,
               mapTypeControl: false,
               scaleControl: false,
               draggable: false,
               mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map($map.get(0), mapOptions);
            new google.maps.Marker({
               position: latLng,
               map: map,
               animation: google.maps.Animation.DROP,
               title: this.model.get('venue')['name']
            });
            this.model.set('map', map);
            this.model.set('map_loaded', true);
         } else {
            google.maps.event.trigger(this.model.get('map'), 'resize')
         }
         app.tagStats().tagHover(this.model);
      },

      hoverOut: function() {
         var that = this;
         this.hoverTimeout = setTimeout(function() {
            $(that.el).find('.popover').hide();
         }, 200);
      },

      events: {
         "mouseenter .tag": "hoverIn",
         "mouseleave .tag": "hoverOut"
      }
   });

   Tag.Views.List = Backbone.View.extend({
      template: "tag/list",

      beforeRender: function() {
         tags.each(function(tag) {
            this.insertView(".tags", new Tag.Views.Item({
               model: tag
            }));
         }, this);
      },

      afterRender: function() {
         setTimeout(function() {
            tags.each(function(tag) {
               tag.set('class', '');
            });
         }, 500);
      },

      initialize: function() {
         this.listenTo(tags, {
            "add": this.render,
            "remove": this.render
         });
      }
   });

   Tag.Views.MenuTools = Backbone.View.extend({
      template: "tag/menuTools",

      initialize: function() {
         var that = this;
         app.on('tagMenuTools:addTag', function() {
            that.addTag();
         });
         app.on('global:closeMenuTools', function() {
            that.unloadTagging();
         });
         app.on('tagMenuTools:tagAdded', function() {
            that.unloadTagging();
         });
         tags = new Tag.Collection();
      },

      cancel: function() {
         this.unloadTagging();
         app.trigger('tagMenuTools:cancel');
      },

      addTag: function() {
         $photo = $('#photos-grid .large');
         if ($photo.length) {
            currentPhotoOverlay = $photo.find('.photo-overlay');
            this.setupTagging();
         }
      },

      setupTagging: function() {
         currentPhotoOverlay.css({ cursor: 'crosshair'});
         currentPhotoOverlay.bind('click', this.tagOverlayHandler);
         app.useLayout().insertView(currentPhotoOverlay.selector, new Tag.Views.List()).render();
      },

      unloadTagging: function() {
         if (currentPhotoOverlay) {
            currentPhotoOverlay.css({ cursor: 'pointer'});
            currentPhotoOverlay.unbind('click', this.tagOverlayHandler);
         }
      },

      tagOverlayHandler: function(e) {
         var tagRadius = 12.5;
         var xPosition = (e.offsetX - tagRadius) / e.currentTarget.clientWidth;
         var yPosition = (e.offsetY - tagRadius) / e.currentTarget.clientHeight;

         // Remove tags arent persisted
         tags.each(function(tag) {
            if (!tag.has('persisted')) {
               tags.remove(tag);
            }
         });

         var tag = new Tag.Model();
         tag.set('x_position', xPosition);
         tag.set('y_position', yPosition);
         tag.set('class', 'new-tag');
         tags.add(tag);
         currentTag = tag;

         app.useLayout().setView("#menu-tools .tag-form", new Tag.Views.AddTagForm()).render();
         $('.tag-text').fadeOut('fast', function() {
            $('.tag-form').fadeIn();
         });
      },

      close: function() {
         this.unloadTagging();
         app.trigger('global:closeMenuTools');
      },

      events: {
         "click .cancel-add-tag": "cancel"
      }
   });

   Tag.Views.AddTagForm = Backbone.View.extend({
      template: "tag/addForm",

      afterRender: function() {
         // Tabs
         $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
         });

         // Venue
         if (!Modernizr.geolocation) {
            $('#support-geolocation').fadeOut('fast', function() {
               $('#not-support-geolocation').fadeIn('fast');
            })
         }
         $('#venue-text').typeahead({
            source: function(query, process) {
               if (venuesSearchTimeout)
                  clearTimeout(venuesSearchTimeout);
               venuesSearchTimeout = setTimeout(function() {
                  $('#loading-venue').css({'display': 'inline-block'});
                  app.oauth.loadAccessToken({
                     success: function() {
                        var url = Routing.generate('api_v1_get_venue_search', { query: query });
                        if (app.appState().getCurrentPosition()) {
                           url += '?ll=' + app.appState().getCurrentPosition().coords.latitude + ',' + app.appState().getCurrentPosition().coords.longitude;
                        }
                        $.ajax({
                           url: url,
                           headers: { 'Authorization': app.oauth.getAuthorizationHeader() },
                           data: {
                              radius: 3000
                           },
                           success: function(data) {
                              if (typeof data !== undefined && data.length > 0) {
                                 var venues = []
                                 currentVenues = {};
                                 _.each(data, function(venue) {
                                    venues.push(venue.name);
                                    currentVenues[venue.name] = venue;
                                 });
                                 process(venues);
                              }
                              $('#loading-venue').fadeOut(200);
                           }
                        });
                     }
                  });
               }, 500);
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               currentVenue = currentVenues[selectedItem];
               $('#venue-link').val(currentVenue.link ? currentVenue.link : currentVenue.foursquare_short_link);
               return selectedItem;
            }
         });

         // Person
         if (!app.fb.isConnected()) {
            $('.tab-pane .fb-loggedin').fadeOut('fast');
            $('.tab-pane .fb-loggedout').fadeIn('fast');
         } else {
            $('.tab-pane .fb-loggedout').fadeOut('fast');
            $('.tab-pane .fb-loggedin').fadeIn('fast');
         }
         $('#person-text').typeahead({
            source: function(query, process) {
               $('#loading-person').css({'display': 'inline-block'});
               app.fb.loadFriends({
                  success: function(friends) {
                     var friendsNames = [];
                     _.each(friends, function(friend) {
                        friendsNames.push(friend.name);
                     });
                     process(friendsNames);
                     $('#loading-person').fadeOut(200);
                  }
               });
            },
            minLength: 2,
            items: 10,
            updater: function(selectedItem) {
               app.fb.loadFriends({
                  success: function(friends) {
                     currentPerson = _.find(friends, function(friend) {
                        if (friend.name == selectedItem)
                           return friend;
                     });
                  }
               });
               return selectedItem;
            }
         })
      },

      geolocation: function(e) {
         e.preventDefault();
         if (Modernizr.geolocation) {
            var btn = $('.btn-geolocation');
            btn.button('loading');
            navigator.geolocation.getCurrentPosition(function(position) {
               app.appState().set('currentPosition', position);
               btn.button('reset');
               $('#support-geolocation').html('<div class="alert fade in alert-success"><small>Nous venons de vous <strong>géolocaliser avec succès.</strong></small></div>');
            });
         }
      },

      cancel: function(e) {
         e.preventDefault();
         // Remove current tag
         if (currentTag)
            tags.remove(currentTag);
         app.appState().set('currentPosition', '');
         // Hide form
         app.trigger('tagMenuTools:cancel');
      },

      submit: function(e) {
         e.preventDefault();
         $activePane = $('.tab-content .active');

         switch ($activePane.attr('id')) {
            case 'product':
               break;
            case 'venue':
               if (currentVenue && currentTag && $('#venue-link').val()) {
                  $submit = $('#submit-venue');
                  $submit.button('loading');
                  // Set venue info
                  currentVenue.link = $('#venue-link').val();
                  currentVenue.description = $('#venue-description').val();
                  // POST currentVenue
                  venue = new Venue.Model();
                  venue.entityToModel(currentVenue);
                  venue.url = Routing.generate('api_v1_post_venue');
                  venue.getToken('venue_item', function() {
                     venue.save(null, {
                        success: function() {
                           // Link tag to photo
                           currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
                           // Set tag info
                           currentTag.set('type', 'place');
                           currentTag.set('venue', venue.get('id'));
                           currentTag.set('title', currentVenue.name);
                           currentTag.set('description', currentVenue.description);
                           currentTag.set('link', currentVenue.link);
                           currentTag.url = Routing.generate('api_v1_post_tag');
                           currentTag.getToken('tag_item', function() {
                              currentTag.save(null, {
                                 success: function() {
                                    currentTag.set('persisted', '');
                                    app.trigger('tagMenuTools:tagAdded');
                                 },
                                 error: function() {
                                    // TODO: show alert
                                 }
                              });
                           });
                        },
                        error: function() {
                           // TODO: show alert
                        }
                     });
                  });
               }
               break;
            case 'person':
               if (currentPerson) {
                  $submit = $('#submit-person');
                  $submit.button('loading');
                  person = new Person.Model();
                  person.entityToModel(currentPerson);
                  person.url = Routing.generate('api_v1_post_person');
                  person.getToken('person_item', function() {
                     person.save(null, {
                        success: function() {
                           // Link tag to photo
                           currentTag.set('photo', app.appState().getCurrentPhotoModel().get('id'));
                           // Set tag info
                           currentTag.set('type', 'person');
                           currentTag.set('person', person.get('id'));
                           currentTag.set('title', currentPerson.name);
                           currentTag.set('link', 'https://www.facebook.com/' + currentPerson.id);
                           currentTag.url = Routing.generate('api_v1_post_tag');
                           currentTag.getToken('tag_item', function() {
                              currentTag.save(null, {
                                 success: function() {
                                    currentTag.set('persisted', '');
                                    app.trigger('tagMenuTools:tagAdded');
                                 },
                                 error: function() {
                                    // TODO: show alert
                                 }
                              });
                           });
                        },
                        error: function() {
                           // TODO: show alert
                        }
                     });
                  });
               }
               break;
         }
      },

      events: {
         "click .cancel-add-tag": "cancel",
         "click .btn-geolocation": "geolocation",
         "submit": "submit"
      }
   });

   return Tag;
});