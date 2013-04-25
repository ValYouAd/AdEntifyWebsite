function facebookConnect() {

   $('#authent-container').fadeOut('fast', function() {
      $('#loading').fadeIn('fast');
   })

   FB.getLoginStatus(function(response) {
      if (response.status === 'connected') {
         window.location.href = Routing.generate('_security_check_facebook');
      } else if (response.status === 'not_authorized') {
         FB.login(function(response) {
            if (response.authResponse) {
               window.location.href = Routing.generate('_security_check_facebook');
            } else {
               // cancelled
            }
         });
      } else {
         FB.login(function(response) {
            if (response.authResponse) {
               window.location.href = Routing.generate('_security_check_facebook');
            } else {
               // cancelled
            }
         });
      }
   });
}