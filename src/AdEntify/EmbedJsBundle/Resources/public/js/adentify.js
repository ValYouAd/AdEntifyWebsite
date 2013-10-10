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

   AdEntify = {
      hoverTimeout: null,
      rootUrl: "//local.adentify.com/",
      showTags: false,
      showLikes: false,

      init: function() {
         var that = this;

         // Load options
         AdEntify.showTags = typeof jQuery(this.getValue('selector')).attr('data-adentify-tags') !== 'undefined' ? true : false;
         AdEntify.showLikes = typeof jQuery(this.getValue('selector')).attr('data-adentify-likes') !== 'undefined' ? true : false;

         // Load CSS
         if (jQuery('meta[property="adentitfy-loaded"]').length == 0) {
            $head = jQuery('head');
            $head.append('<style type="text/css">' +
               '.adentify-pastille {opacity: 1;background: url("'+ AdEntify.rootUrl +'img/pastille.png") no-repeat;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;width: 25px;height: 25px;position: absolute;top: 10px;right: 10px;z-index: 2;cursor: pointer;}' +
               '.adentify-photo-container:hover .adentify-pastille { opacity: 1; }' +
               (AdEntify.showTags === true ? '.tags {display: block;}' : '.tags {display: none;}') +
               '.tags li {margin: 0;padding: 0;}' +
               '.tag {position: absolute;background: rgba(0,0,0,0.9);width: 25px;height: 25px;border-radius: 12.5px;}' +
               '.popover {position: absolute;display: none;padding: 4px 6px;-webkit-transition: opacity 0.3s ease-out;-moz-transition: opacity 0.3s ease-out;-ms-transition: opacity 0.3s ease-out;-o-transition: opacity 0.3s ease-out;transition: opacity 0.3s ease-out;}' +
               '.popover-product {min-width: 250px;}' +
               '.popover-product .product-image {max-width: 250px;}' +
               '.popover-product .brand-logo {max-height: 50px;}' +
               '.tag .map {width: 270px;height: 260px;}' +
               '.popover {position:absolute;top:0;left:0;z-index:1010;max-width:276px;padding:1px;text-align:left;background-color:#ffffff;-webkit-background-clip:padding-box;-moz-background-clip:padding;background-clip:padding-box;border:1px solid #ccc;border:1px solid rgba(0, 0, 0, 0.2);-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);-moz-box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);box-shadow:0 5px 10px rgba(0, 0, 0, 0.2);white-space:normal;}.popover.top{margin-top:-10px;}' +
               '.popover.right {margin-left:10px;}' +
               '.popover.bottom {margin-top:10px;}' +
               '.popover.left {margin-left:-10px;}' +
               '.popover-title {margin:0;padding:8px 14px;font-size:14px;font-weight:normal;line-height:18px;background-color:#f7f7f7;border-bottom:1px solid #ebebeb;-webkit-border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;border-radius:5px 5px 0 0;}.popover-title:empty{display:none;}' +
               '.popover-content {padding:9px 14px;}' +
               '.popover .arrow,.popover .arrow:after {position:absolute;display:block;width:0;height:0;border-color:transparent;border-style:solid;}' +
               '.popover .arrow {border-width:11px;}' +
               '.popover .arrow:after {border-width:10px;content:"";}' +
               '.popover.top .arrow {left:50%;margin-left:-11px;border-bottom-width:0;border-top-color:#999;border-top-color:rgba(0, 0, 0, 0.25);bottom:-11px;}.popover.top .arrow:after{bottom:1px;margin-left:-10px;border-bottom-width:0;border-top-color:#ffffff;}' +
               '.popover.right .arrow {top:50%;left:-11px;margin-top:-11px;border-left-width:0;border-right-color:#999;border-right-color:rgba(0, 0, 0, 0.25);}.popover.right .arrow:after{left:1px;bottom:-10px;border-left-width:0;border-right-color:#ffffff;}' +
               '.popover.bottom .arrow {left:50%;margin-left:-11px;border-top-width:0;border-bottom-color:#999;border-bottom-color:rgba(0, 0, 0, 0.25);top:-11px;}.popover.bottom .arrow:after{top:1px;margin-left:-10px;border-top-width:0;border-bottom-color:#ffffff;}' +
               '.popover.left .arrow {top:50%;right:-11px;margin-top:-11px;border-right-width:0;border-left-color:#999;border-left-color:rgba(0, 0, 0, 0.25);}.popover.left .arrow:after{right:1px;border-right-width:0;border-left-color:#ffffff;bottom:-10px;}' +
               '[class^="icon-"],[class*=" icon-"]{display:inline-block;width:14px;height:14px;*margin-right:.3em;line-height:14px;vertical-align:text-top;background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings.png");background-position:14px 14px;background-repeat:no-repeat;margin-top:1px;}' +
               '.icon-white,.nav-pills>.active>a>[class^="icon-"],.nav-pills>.active>a>[class*=" icon-"],.nav-list>.active>a>[class^="icon-"],.nav-list>.active>a>[class*=" icon-"],.navbar-inverse .nav>.active>a>[class^="icon-"],.navbar-inverse .nav>.active>a>[class*=" icon-"],.dropdown-menu>li>a:hover>[class^="icon-"],.dropdown-menu>li>a:focus>[class^="icon-"],.dropdown-menu>li>a:hover>[class*=" icon-"],.dropdown-menu>li>a:focus>[class*=" icon-"],.dropdown-menu>.active>a>[class^="icon-"],.dropdown-menu>.active>a>[class*=" icon-"],.dropdown-submenu:hover>a>[class^="icon-"],.dropdown-submenu:focus>a>[class^="icon-"],.dropdown-submenu:hover>a>[class*=" icon-"],.dropdown-submenu:focus>a>[class*=" icon-"]{background-image:url("'+ AdEntify.rootUrl + 'img/glyphicons-halflings-white.png");}' +
               '.icon-heart {background-position: -96px 0;}' +
               '.adentify-photo-likes {position: absolute; bottom: 5px; right: 5px; color: white;text-shadow: 0 0 3px rgba(0,0,0,0.8); text-rendering: optimizelegibility;}' +
               '</style>');
            $head.append('<meta property="adentify-loaded" content="true">');
         }

         jQuery(this.getValue('selector')).wrap('<div class="adentify-photo-container" style="position: relative;display: inline-block;" />');
         jQuery('<div class="adentify-photo-overlay" style="position: absolute;left: 0px;top: 0px;width: 100%;height: 100%;" />').insertBefore(this.getValue('selector'));
         $tags = jQuery('<ul class="tags" data-state="hidden" data-always-visible="no" style="list-style-type: none;margin: 0;padding: 0;" />').insertBefore(this.getValue('selector'));
         $pastille = jQuery('<div class="adentify-pastille" />').insertBefore(this.getValue('selector'));
         $pastille.on('click', function() {
            if ($tags.data('always-visible') == 'no') {
               $tags.data('always-visible', 'yes');
            } else {
               $tags.data('always-visible', 'no');
               $tags.stop().fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         });
         $pastille.on('mouseenter', function() {
            $tags.stop().fadeIn('fast');
            $tags.data('state', 'visible');
         });
         $pastille.on('mouseleave', function() {
            if ($tags.data('always-visible') == 'no') {
               $tags.stop().fadeOut('fast');
               $tags.data('state', 'hidden');
            }
         });
         jQuery.ajax({
            url: AdEntify.rootUrl + 'public-api/v1/photos/' + jQuery(this.getValue('selector')).data('adentify-photo-id'),
            dataType: "jsonp",
            success: function(photo) {
               if (typeof photo !== 'undefined' && typeof photo.tags !== 'undefined') {
                  if (AdEntify.showLikes === true) {
                     jQuery('<div class="adentify-photo-likes">' + photo.likes_count + ' <i class="icon-heart icon-white"></i></div>').insertBefore(that.getValue('selector'));
                  }
                  var tags = photo.tags;
                  if (typeof tags !== 'undefined' && tags.length > 0) {
                     var i = 0;
                     for (i; i <tags.length; i++) {
                        var tag = tags[i];
                        if (tag.type == 'place') {
                           jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover"><span class="title">'+ (tag.link ? '<a href="'+ tag.link +'" target="_blank">'+ tag.title +'</a>' : tag.title) +'</span>'
                              + (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              '<div id="map' + tag.id + '" class="map" data-lng="' + tag.venue.lng + '" data-lat="' + tag.venue.lat + '"></div></div></div>');
                        } else if (tag.type == 'person') {
                           jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover"><div class="text-center"><img src="https://graph.facebook.com/' + tag.person.facebook_id + '/picture?type=square" /></div><span class="title"><a href="' + tag.link + '" target="_blank">'+ tag.title +'</a></span>' +
                              (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              '</div></div>');
                        } else if (tag.type == 'product') {
                           jQuery($tags).append('<div class="tag" data-x="'+tag.x_position+'" data-y="'+tag.y_position+'" data-tag-id="'+ tag.id +'" style="left: '+ (tag.x_position*100) +'%; top: '+ (tag.y_position*100) +'%"><div class="popover popover-product"><span class="title"><a href="'+ tag.link +'" target="_blank">' + tag.title + (tag.product.brand ? ' - ' + tag.product.brand.name : '') + '</a></span><img class="pull-left product-image" src="'+tag.product.small_url+'">' +
                              (tag.description ? '<p>' + tag.description + '</p>' : '') +
                              (tag.product && tag.product.brand ? '<div class="brand pull-right"><img src="' + tag.product.brand.small_logo_url + '" alt="' + tag.product.brand.name + '" class="brand-logo" /></div>' : '') +
                              '<a href="' + tag.product.purchase_url + '" class="btn btn-small btn-primary"><i class="icon-shopping-cart icon-white"></i> Acheter</a></div></div>');
                        } else {
                           jQuery($tags).append('');
                        }
                     }
                  }
               }
            },
            error: function() {
            }
         });
         $tags = jQuery('.tags');
         $tags.on('mouseenter', '.tag', function() {
            var popover = jQuery(this).find('.popover');
            popover.css({top: jQuery(this).data('y') > 0.5 ? '-'+popover.height()+'px' : '30px'});
            popover.css({left: jQuery(this).data('x') > 0.5 ? '-'+popover.width()+'px' : '30px'});

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

            popover.fadeIn();

            var xhr = AdEntify.createCORSRequest('POST', AdEntify.rootUrl + 'public-api/v1/tag/stat');
            if (xhr) {
               xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
               xhr.send('tagId=' + jQuery(this).data('tag-id') + '&statType=hover');
            }
         });
         $tags.on('mouseleave', '.tag', function() {
            jQuery(this).find('.popover').fadeOut();
         });
         $tags.on('click', 'a[href]', function() {
            var xhr = AdEntify.createCORSRequest('POST', AdEntify.rootUrl + 'public-api/v1/tag/stat');
            if (xhr) {
               xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
               xhr.send('tagId=' + jQuery(this).data('tag-id') + '&statType=click');
            }
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
      }
   };

})(); // We call our anonymous function immediately