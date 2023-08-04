jQuery(document).ready(function(){
	const $menu = $(".woocart");
	jQuery(document).mouseup(e => {
		if ( !$menu.is(e.target) && $menu.has(e.target).length === 0 ) {
			$menu.removeClass("active");
		}
	});
});