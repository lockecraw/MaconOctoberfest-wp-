$jaer = jQuery.noConflict();
	jQuery(document).ready(function($jaer) {
	jQuery(function(){
		//Registration form validation
		jQuery('#registration_form').validate();
	});
});
$("#personal-information").validate({
  rules: {
    email: "required",
    TEXT_38: {
      equalTo: "#email"
    }
  }
});