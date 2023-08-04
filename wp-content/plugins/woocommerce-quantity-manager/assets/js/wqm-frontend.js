jQuery( function( $ ) {
	/**
	 * Frontend JS
	 */
	const wqmFrontendJS = function() {
		// Variation Change
        $( document ).on( 'found_variation', this.handleVariationQtyInput );
    };

    /**
	 * Handle variation qty input
	 */
	wqmFrontendJS.prototype.handleVariationQtyInput = function( event, variation ) {
        const $qtyInput = $( '.single_variation_wrap .quantity input[name="quantity"]' );

		console.log( { variation, $qtyInput } );

        if ( variation.input_value !== undefined ) {
            $qtyInput.val( variation.input_value );
        } else {
			$qtyInput.val( 1 );
		}

		if ( variation.step !== undefined ) {
            $qtyInput.attr( 'step', parseInt( variation.step ) );
        } else {
			$qtyInput.attr( 'step', 1 );
		}
    };

	/**
	 * Init wqmFrontendJS.
	 */
	new wqmFrontendJS();
} );