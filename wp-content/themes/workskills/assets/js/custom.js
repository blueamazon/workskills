jQuery(document).ready(function(){
	
	if( jQuery('#course-pricing a.course_button .coming_soon').length > 0 )
		jQuery('#course-pricing a.course_button').addClass('coming_soon_wrap');
		
	if( jQuery('#course-pricing .variations_form').length > 0 ){
		jQuery('#course-pricing > .course_button').click( function(e){
			if( jQuery(this).hasClass("disabled") ){
				jQuery.alert({
					title: nextgates.alert_title,
					content: nextgates.alert_content
				});
				e.stopImmediatePropagation(); 
			}
		});
	}
		
	const $menu = jQuery(".woocart");
	jQuery(document).mouseup(e => {
		if ( !$menu.is(e.target) && $menu.has(e.target).length === 0 ) {
			$menu.removeClass("active");
		}
	});
});

