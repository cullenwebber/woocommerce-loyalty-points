jQuery(document).ready(function ($) {
	//Page seems to be loaded via jQuery so we need to set a timeout to ensure the element is loaded in
	setTimeout(() => {
		$("#used_points").on("input", function (e) {
			var value = e.target.value;
			jQuery("body").trigger("update_checkout");
		});
	}, 1000);
});
