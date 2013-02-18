jQuery(document).ready(function() {

	jQuery(".numeric-only").numeric({ negative: false }, function() { alert("No negative values"); this.value = ""; this.focus(); });

});