/**
 * Created by pierrickmartos on 23/12/14.
 */
jQuery(document).ready(function() {
   $('.change-account-btn').click(function() {
      if ($('.accounts-selector').val()) {
         window.location.href = $('.accounts-selector').val();
      }
      return false;
   });
});