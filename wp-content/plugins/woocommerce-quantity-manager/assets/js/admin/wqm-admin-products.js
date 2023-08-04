jQuery( function( $ ) {
    /**
	 * Admin Product JS
	 */
	var wqmAdminProductSettings = function() {
        $( 'input[name=_sold_individually]' ).on( 'change', this.handleSoldIndividually );
        $( '#woocommerce-product-data' ).on( 'woocommerce_variations_added woocommerce_variations_loaded', this.handleVariationsInit );
    };

    /**
     * Handle variations disabling in case Sold Individually is changed before init.
     */
    wqmAdminProductSettings.prototype.handleVariationsInit = function( event ) {
        const $wqmVariaitonProductFields = $( '#woocommerce-product-data .wqm-product-field' );

        if ( $( 'input[name=_sold_individually]' ).is( ':checked' ) ) {
            $wqmVariaitonProductFields.prop( 'disabled', true );
        } else {
            $wqmVariaitonProductFields.prop( 'disabled', false );
        }
    };

    /**
	 * Handle Sold Individually Checkbox
	 */
	wqmAdminProductSettings.prototype.handleSoldIndividually = function( event ) {
        const $wqmProductFields = $( '.wqm-product-field' );

        if ( $( this ).is( ':checked' ) ) {
            $wqmProductFields.prop( 'disabled', true );
        } else {
            $wqmProductFields.prop( 'disabled', false );
        }
    };

	/**
	 * Init wqmAdminProductSettings.
	 */
	new wqmAdminProductSettings();
} );