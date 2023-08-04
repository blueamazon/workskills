jQuery( function( $ ) {
	/**
	 * Admin JS
	 */
	var wqmAdminSettings = function() {
        $( document.body ).ready( this.handleSelectWoo );
    };

    /**
	 * Init selectWoo
	 */
	wqmAdminSettings.prototype.handleSelectWoo = function( event ) {
        $( '#quantity_manager_roles' ).selectWoo({ width: '600px' });
    };

	/**
	 * Init wqmAdminSettings.
	 */
	new wqmAdminSettings();
} );