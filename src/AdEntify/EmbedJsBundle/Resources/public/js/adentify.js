(function() {
   var jQuery;
   if (window.jQuery === undefined || window.jQuery.fn.jquery !== '1.4.2') {
      var script_tag = document.createElement('script');
      script_tag.setAttribute("type","text/javascript");
      script_tag.setAttribute("src",
         "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js");
      if (script_tag.readyState) {
         script_tag.onreadystatechange = function () { // For old versions of IE
            if (this.readyState == 'complete' || this.readyState == 'loaded') {
               scriptLoadHandler();
            }
         };
      } else { // Other browsers
         script_tag.onload = scriptLoadHandler;
      }
      (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
   } else {
      jQuery = window.jQuery;
      AdEntify.init();
   }

   function scriptLoadHandler() {
      jQuery = window.jQuery.noConflict(true);
      AdEntify.init();
   }

   var AdEntify = {
      hoverTimeout: null,
      rootUrl: "//adentify.com/",
      showTags: false,
      showLikes: false,

      init: function() {
         var that = this;

         // Load options
         AdEntify.showTags = typeof jQuery(this.getValue('selector')).attr('data-adentify-tags') !== 'undefined';
         AdEntify.showLikes = typeof jQuery(this.getValue('selector')).attr('data-adentify-likes') !== 'undefined';
         AdEntify.cover = typeof jQuery(this.getValue('selector')).attr('data-cover') === 'undefined';
         AdEntify.hideCopyright = typeof jQuery(this.getValue('selector')).attr('data-hide-copyright') === 'undefined';

         // Load CSS
         if (jQuery('meta[property="adentitfy-loaded"]').length == 0) {
            $head = jQuery('head');
            $head.append('<style type="text/css">' +
               '@font-face {font-family: "asapregular";src: url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/asap-regular-webfont.svg#asapregular") format("svg");font-weight: normal;font-style: normal;}' +
               '@font-face {font-family: "robotobold";src: url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/Roboto-Bold-webfont.svg#robotobold") format("svg");font-weight: normal;font-style: normal;}' +
               '@font-face {font-family: "asapbold";src: url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.eot");src: url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.eot?#iefix") format("embedded-opentype"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.woff") format("woff"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.ttf") format("truetype"),url("'+ AdEntify.rootUrl +'fonts/asap-bold-webfont.svg#asapbold") format("svg");font-weight: normal;font-style: normal;}' +
               '#embed-photo {max-height: ' + window.innerHeight + 'px; max-width: ' + window.innerWidth + 'px}' +
               '.adentify-pastille {background: url("'+ AdEntify.rootUrl +'img/adentify-pastille.png") no-repeat;}' +
               (AdEntify.showTags === true ? '.adentify-photo-container .tags {opacity: 1;}' : '.adentify-photo-container .tags {opacity: 0;}') +
               '.tag {background-image: url("'+ AdEntify.rootUrl +'/img/sprites.png");}' +
               '.tag .popover {width: ' + window.innerWidth * 0.85 + 'px;}' +
               '.tag .popover p {max-width: 400px;}' +
               '[class^="icon-"],[class*=" icon-"]{background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings.png");}' +
               '.icon-white,.nav-pills>.active>a>[class^="icon-"],.nav-pills>.active>a>[class*=" icon-"],.nav-list>.active>a>[class^="icon-"],.nav-list>.active>a>[class*=" icon-"],.navbar-inverse .nav>.active>a>[class^="icon-"],.navbar-inverse .nav>.active>a>[class*=" icon-"],.dropdown-menu>li>a:hover>[class^="icon-"],.dropdown-menu>li>a:focus>[class^="icon-"],.dropdown-menu>li>a:hover>[class*=" icon-"],.dropdown-menu>li>a:focus>[class*=" icon-"],.dropdown-menu>.active>a>[class^="icon-"],.dropdown-menu>.active>a>[class*=" icon-"],.dropdown-submenu:hover>a>[class^="icon-"],.dropdown-submenu:focus>a>[class^="icon-"],.dropdown-submenu:hover>a>[class*=" icon-"],.dropdown-submenu:focus>a>[class*=" icon-"]{background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings-white.png");}' +
               '.tag-buttons {background: url("'+ AdEntify.rootUrl +'img/dark-grey-tag-background.jpg") repeat;}' +
               // sprites
               '.arrow-top-adentify-pastille-hover, .add-tag-icon, .like-icon, .share-icon, .favorite-icon, .tag-place-icon, .tag-user-icon, .tag-popover-arrow-bottom, .tag-popover-arrow-left, .tag-popover-arrow-right, .tag-popover-arrow-top { background-image: url("'+ AdEntify.rootUrl +'/img/sprites.png");}' +
               '</style>');
            $head.append('<meta property="adentify-loaded" content="true">');
         }

         jQuery(this.getValue('selector')).wrap('<div class="adentify-photo-container" style="position: relative;display: inline-block;" />');
         jQuery('<div class="adentify-photo-overlay" style="position: absolute;left: 0px;top: 0px;width: 100%;height: 100%;" />').insertBefore(this.getValue('selector'));
         $tags = jQuery('<ul class="tags" data-state="hidden" data-always-visible="no" style="list-style-type: none;margin: 0;padding: 0;" />').insertBefore(this.getValue('selector'));

         if (!AdEntify.hideCopyright) {
            $pastilleWrapper = jQuery('<div class="adentify-pastille-wrapper" />').insertBefore(this.getValue('selector'));
            $pastille = jQuery($pastilleWrapper).append('<div class="adentify-pastille" />');
            $pastillePopover = jQuery($pastilleWrapper).append('\
            <div class="popover">\
               <div class="arrow-top-adentify-pastille-hover"></div>\
               <ul class="popover-pastille-buttons list-unstyled">\
                  <li><button class="btn-icon share-icon share-button"></button></li>\
               </ul>\
            </div>');
            $pastillePopover.find('.like-icon').click(function() {
               window.open(that.photoUrl());
               return false;
            });
            $pastillePopover.find('.add-tag-icon').click(function() {
               window.open(that.photoUrl());
               return false;
            });
            $pastillePopover.find('.favorite-icon').click(function() {
               window.open(that.photoUrl());
               return false;
            });
            $pastillePopover.find('.share-icon').click(function() {
               if (jQuery(that.getValue('selector')).siblings('.share-overlay:visible').length > 0) {
                  jQuery(that.getValue('selector')).siblings('.share-overlay').fadeOut();
               } else {
                  jQuery(that.getValue('selector')).siblings('.share-overlay').fadeIn();
               }
               return false;
            });
         }

         that = this;
         jQuery.ajax({
            url: AdEntify.rootUrl + 'public-api/v1/photos/' + jQuery(this.getValue('selector')).data('adentify-photo-id'),
            dataType: 'jsonp',
            success: function(photo) {
               if (typeof photo !== 'undefined' && typeof photo.tags !== 'undefined') {
                  var photoContainer = jQuery(that.getValue('selector')).parent('.adentify-photo-container');
                  if (photoContainer.length > 0) {
                     photoContainer.css({maxHeight: photo.large_height, maxWidth: photo.large_width});
                  }
                  jQuery('<div class="share-overlay fadeOut"><div class="share-overlay-wrapper"><div class="share-overlay-cell">\
                              <div class="share-overlay-inner">\
                              <iframe allowtransparency="true" frameborder="0" scrolling="no"\
                              src="https://platform.twitter.com/widgets/tweet_button.html?text=' + that.photoUrl() + '&via=AdEntify&lang=' + that.currentLang() + '" style="width:130px; height:20px;"></iframe>\
                              <div class="g-plusone" data-size="medium" data-href="' + that.photoUrl() + '"></div>\
                              <script type="text/javascript">\
                              window.___gcfg = {lang: "' + that.currentLang() + '"};\
                              (function() {\
                                 var po = document.createElement("script"); po.type = "text/javascript"; po.async = true;\
                                 po.src = "https://apis.google.com/js/plusone.js";\
                                 var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(po, s);\
                                 })();\
                              </script>\
                              <div class="fblike"><div class="fb-like" data-href="' + that.photoUrl() + '" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div></div>\
                              <div class="pinterest"><a target="_blank" href="//pinterest.com/pin/create/button/?url=' + encodeURIComponent(that.photoUrl()) + '&media=' + encodeURIComponent(photo.large_url) + '&description=' + encodeURIComponent(photo.caption) + '" data-pin-do="buttonPin" data-pin-config="beside"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" /></a></div>\
                              <textarea class="embedCode form-control input-block-level selectOnFocus" rows="3">&lt;iframe src="' + AdEntify.rootUrl + 'iframe/photo-' + jQuery(that.getValue('selector')).data('adentify-photo-id') +'.html" scrolling="no" frameborder="0" style="border:none; overflow:hidden;width:' + photo.large_width + 'px; height:' + photo.large_height + 'px;" allowTransparency="true"&gt;&lt;/iframe&gt;</textarea>\
                      </div>\
                  </div></div></div>').insertBefore(that.getValue('selector'));
                  FB.XFBML.parse();
                  if (AdEntify.showLikes === true) {
                     jQuery('<div class="adentify-photo-likes">' + photo.likes_count + ' <i class="icon-heart icon-white"></i></div>').insertBefore(that.getValue('selector'));
                  }
                  that.addTag(photo);
               }
            },
            error: function() {
            }
         });
         $tags = jQuery('.tags');
         $tags.on('mouseenter', '.tag', function() {

            // Load map if found
            var map = jQuery(this).find('.map');
            if (map.length > 0 && map.data('loaded') != '1') {
               map.data('loaded', 1);
               var latLng = new google.maps.LatLng(map.data('lat'), map.data('lng'));
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
               var gMap = new google.maps.Map(document.getElementById('map'+jQuery(this).data('tag-id')), mapOptions);
               new google.maps.Marker({
                  position: latLng,
                  map: gMap
               });
            }
         });

         var photoEnterTime = {};
         var tagEnterTime = {};
         var $photos = jQuery(this.getValue('selector'));

         // Photos hover in/out
         $photos.hover(function() {
            photoEnterTime[$photos.data('adentify-photo-id')] = Date.now();
            that.postAnalytic('hover', 'photo', null, $photos.data('adentify-photo-id'));
         }, function() {
            if (photoEnterTime[$photos.data('adentify-photo-id')]) {
               var interactionTime = Date.now() - photoEnterTime[$photos.data('adentify-photo-id')];
               if (interactionTime > 200)
                  that.postAnalytic('interaction', 'photo', null, $photos.data('adentify-photo-id'), null, interactionTime)
            }
         });

         // Tags hover in/out
         jQuery('.tags .tag').hover(function() {
            tagEnterTime[jQuery(this).data('tag-id')] = Date.now();
            that.postAnalytic('hover', 'tag', jQuery(this).data('tag-id'), null);
         }, function() {
            if (tagEnterTime[jQuery(this).data('tag-id')]) {
               var interactionTime = Date.now() - tagEnterTime[jQuery(this).data('tag-id')];
               if (interactionTime > 200)
                  that.postAnalytic('interaction', 'tag', jQuery(this).data('tag-id'), null, null, interactionTime)
            }
         });

         // Tags click
         $tags.on('click', '.tag a[href]', function() {
            that.postAnalytic('click', 'tag', jQuery(this).parents('.tag').data('tag-id'), null, jQuery(this).attr('href'));
         });

         // Post photo view
         that.postAnalytic('view', 'photo', null, $photos.data('adentify-photo-id'));
      },

      postAnalytic: function(action, element, tag, photo, link, actionValue) {
         var analytic = {
            'platform': 'embed',
            'element': element,
            'action': action,
            'sourceUrl': this.getParentUrl()
         };
         if (tag)
            analytic.tag = tag;
         if (photo)
            analytic.photo = photo;
         if (link)
            analytic.link = link;
         if (actionValue)
            analytic.actionValue = actionValue;

         jQuery.ajax({
            type: 'POST',
            url: AdEntify.rootUrl + 'api/v1/analytics',
            data: {
               'analytic': analytic
            }
         });
      },

      getParentUrl: function() {
         var isInIframe = (parent !== window),
            parentUrl = null;

         if (isInIframe) {
            parentUrl = document.referrer;
         }

         return parentUrl;
      },

      addTag: function(photo) {
         var tags = photo.tags;
         var that = this;
         if (typeof tags !== 'undefined' && tags.length > 0) {
            var i = 0;
            for (i; i <tags.length; i++) {
               var tag = tags[i];
               var $tag = null;
               if (tag.type == 'place') {
                  $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="tag-place-icon tag-icon"></div><div class="popover"><div class="tag-popover-arrow"></div><div class="popover-inner"><span class="title">'+ (tag.link ? '<a href="'+ tag.link +'" target="_blank">'+ tag.title +'</a>' : tag.title) +'</span>'
                  + (tag.description ? '<p>' + tag.description + '</p>' : '') +
                  '<div id="map' + tag.id + '" class="map" data-lng="' + tag.venue.lng + '" data-lat="' + tag.venue.lat + '"></div>\
                              <div class="popover-details">\
                                 <address>\
                                 <strong>' + tag.title + '</strong><br>\
                                   ' + (tag.venue.address ? tag.venue.address + '<br>' : '') +
                  (tag.venue.postal_code ? tag.venue.postal_code + ' ' : '') + (tag.venue.city ? tag.venue.city + ' ' : '') + (tag.venue.country ? tag.venue.country + ' ' : '') +
                  '</address></div></div></div></div>');
               } else if (tag.type == 'person') {
                  $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%">\
                              <div class="tag-user-icon tag-icon"></div><div class="popover"><div class="tag-popover-arrow"></div>\
                              <div class="popover-inner"><div class="text-center"><img src="https://graph.facebook.com/' + tag.person.facebook_id + '/picture?type=square" /></div><span class="person-title"><a href="' + tag.link + '" target="_blank">'+ tag.title +'</a></span>' +
                  '</div></div></div>');
               } else if (tag.type == 'product') {
                  $tag = jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="tag-brand-icon glyphicon glyphicon-tag tag-icon"></div><div class="popover popover-product"><div class="tag-popover-arrow"></div><div class="popover-inner"><span class="title">' +
                  (typeof tag.link !== 'undefined' ? '<a href="'+ tag.link +'" target="_blank">' + tag.title + '</a></span>' : tag.title + '</span>') + (tag.product && typeof tag.product.small_url !== 'undefined' ? '<img class="pull-left product-image" src="'+tag.product.small_url+'">' : '') +
                  (tag.description ? '<p>' + tag.description + '</p>' : '') +
                  (tag.brand ? typeof tag.brand.small_logo_url !== 'undefined' ? '<div class="brand"><img src="' + tag.brand.small_logo_url + '" alt="' + tag.brand.name + '" class="brand-logo" /></div>' : '' : '') +
                  '</div></div></div>');
               } else {
                  jQuery($tags).append('');
               }
               if ($tag) {
                  var popoverArrow = $tag.find('[data-tag-id="'+ tag.id + '"] .tag-popover-arrow');
                  popoverArrow.addClass('tag-popover-arrow-top');
                  popoverArrow.css({top: '30px'});
                  popoverArrow.css({left: '12px'});
               }

               if (typeof popover !== 'undefined') {
                  popover.css({top: this.model.get('y_position') > 0.5 ? '-'+(popover.height() + 18)+'px' : '46px'});
                  popover.css({left: this.model.get('x_position') > 0.5 ? '-'+(popover.width() - 31)+'px' : '-8px'});
                  popover.fadeIn(100);
               }
            }
            jQuery('.tag').each(function() {
               that.positionTagPopover(this);
            });
         }
      },

      positionTagPopover: function(tag) {
         var deferreds = [];
         var i = 0;

         // Create a deferred for all images
         jQuery(tag).find('img').each(function() {
            deferreds.push(new jQuery.Deferred());
         });

         // When image is loaded, resolve the next deferred
         jQuery(tag).find('img').load(function() {
            if (typeof deferreds[i] !== 'undefined')
               deferreds[i].resolve();
            i++;
         }).each(function() {
            if(this.complete)
               jQuery(this).load();
         });

         // When all deferreds are done (all images loaded) do some stuff
         jQuery.when.apply(null, deferreds).done(function() {
            var popover = jQuery(tag).find('.popover');
            var popoverInner = jQuery(popover).find('.popover-inner');

            if (!popover.is(':visible'))
               popover.show();

            var popoverInnerOffset = jQuery(popoverInner).offset();
            var left = popoverInnerOffset.left;
            var top = popoverInnerOffset.top + 40;
            var failPosition = ''; // r: right, b: bottom, l: left, t: top, d: droite
            failPosition += ((popoverInnerOffset.left + popoverInner.outerWidth(true)) > jQuery('.adentify-photo-container').width()) ? 'r' : '';
            failPosition += ((popoverInnerOffset.top + popoverInner.outerHeight(true) + 40) > jQuery('.adentify-photo-container').height()) ? 'b' : '';
            failPosition = ((failPosition.indexOf('r') > -1) && (popoverInnerOffset.left - popoverInner.outerWidth(true) + 35  < 0)) ? failPosition.replace('r', 'l') : failPosition;
            failPosition = ((failPosition.indexOf('b') > -1) && (popoverInnerOffset.top - popoverInner.outerHeight(true) - 5 < 0)) ? failPosition.replace('b', 't') : failPosition;

            failPosition += (failPosition.indexOf('t') > -1) && ((popoverInnerOffset.left + popoverInner.outerWidth(true) + 40) > jQuery('.adentify-photo-container').width()) ? 'd' : '';
            failPosition = ((failPosition.indexOf('d') > -1) && (popoverInnerOffset.left - popoverInner.outerWidth(true) + 35  < 0)) ? failPosition.replace('d', '') : failPosition;


            if (failPosition.indexOf('r') > -1)
               left = popoverInnerOffset.left - popoverInner.outerWidth(true) + 35;
            if (failPosition.indexOf('b') > -1) {
               top = popoverInnerOffset.top - popoverInner.outerHeight(true) - 5;
               jQuery(popover).find('.tag-popover-arrow').removeClass('tag-popover-arrow-top').addClass('tag-popover-arrow-bottom').css({top: '-5px'});
            }
            if (failPosition.indexOf('l') > -1)
               left = jQuery('.adentify-photo-container').width() * 0.5 - popoverInner.outerWidth(true) / 2;
            if (failPosition.indexOf('t') > -1) {
               top = jQuery('.adentify-photo-container').height() * 0.5 - popoverInner.outerHeight(true) / 2;
               left = popoverInnerOffset.left + 40;
               jQuery(popover).find('.tag-popover-arrow').removeClass('tag-popover-arrow-top').addClass('tag-popover-arrow-left').css({top: '14px', left: '30px'});
            }

            if (failPosition.indexOf('d') > -1) {
               left = popoverInnerOffset.left - popoverInner.outerWidth(true) - 5;
               jQuery(popover).find('.tag-popover-arrow').removeClass('tag-popover-arrow-top').addClass('tag-popover-arrow-right').css({top: '14px', left: '-6px'});
            }

            jQuery(popoverInner).offset({ top: top, left: left});

            if (popover.is(':visible'))
               popover.hide();
         });
      },

      getValue: function(key) {
         var i = 0;
         for (i; i<_adentify.length;i++) {
            if (_adentify[i][0] == key)
               return _adentify[i][1];
         }
         return false;
      },

      createCORSRequest: function(method, url) {
         var xhr = new XMLHttpRequest();
         if ("withCredentials" in xhr) {
            // XHR for Chrome/Firefox/Opera/Safari.
            xhr.open(method, url, true);
         } else if (typeof XDomainRequest != "undefined") {
            // XDomainRequest for IE.
            xhr = new XDomainRequest();
            xhr.open(method, url);
         } else {
            // CORS not supported.
            xhr = null;
         }
         xhr.setRequestHeader('X-Custom-Auth', 'value');
         return xhr;
      },

      photoUrl: function(https) {
         https = typeof https !== 'undefined' ? https : true;
         return (https ? 'https:' : '') + AdEntify.rootUrl + this.currentLang() + '/app/photo/' + jQuery(this.getValue('selector')).data('adentify-photo-id') + '/';
      },

      currentLang: function() {
         var language = window.navigator.userLanguage || window.navigator.language;
         if (language.indexOf('fr') !== -1) {
            return 'fr';
         }
         else {
            return 'en';
         }
      }
   };

})(); // We call our anonymous function immediately