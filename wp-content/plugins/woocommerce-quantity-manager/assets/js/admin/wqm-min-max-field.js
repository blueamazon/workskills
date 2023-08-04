jQuery( function( $ ) {
	/**
	 * Admin Min Max Field JS
	 */
	const wqmMinMaxField = function() {
        $( '.wqm-min-max-input-container' ).on( 'keyup', '.wqm-quantity-input', this.validateMinMax );
        $( '.wqm-min-max-input-container' ).on( 'focusout', '.wqm-quantity-input', this.handleFocusOff );

        $( document.body ).on( 'wqm_add_error_tip', this.addErrorTip );
        $( document.body ).on( 'wqm_remove_error_tip', this.removeErrorTip );

        $( '#woocommerce-product-data' ).on( 'woocommerce_variations_added woocommerce_variations_loaded', this.handleVariationsInit );
    };

    wqmMinMaxField.prototype.handleVariationsInit = function( event ) {
        $( '.wqm-min-max-input-container' ).on( 'keyup', '.wqm-quantity-input', wqmMinMaxField.prototype.validateMinMax );
        $( '.wqm-min-max-input-container' ).on( 'focusout', '.wqm-quantity-input', wqmMinMaxField.prototype.handleFocusOff );
    };

    /**
	 * Handle Min / Max Validation
	 */
	wqmMinMaxField.prototype.validateMinMax = function( event ) {
        const $this = $( this );
        const minValue = $this.hasClass('min') ? $this.val() : $this.parents( '.wqm-min-max-input-container' ).first().find( '.wqm-quantity-input.min' ).val();
        const maxValue = $this.hasClass('max') ? $this.val() : $this.parents( '.wqm-min-max-input-container' ).first().find( '.wqm-quantity-input.max' ).val();
        const errorCode = $this.hasClass('min') ? 'min_max' : 'max_min';

        if ( [ maxValue, minValue ].includes( '' ) ) {
            $( document.body ).triggerHandler( 'wqm_remove_error_tip', [ $( this ), errorCode ] );
            return;
        }

        if ( parseFloat( minValue ) <= parseFloat( maxValue ) ) {
            $( document.body ).triggerHandler( 'wqm_remove_error_tip', [ $( this ), errorCode ] );
        }
    };

    /**
	 * Handle Focus Out Input
	 */
	wqmMinMaxField.prototype.handleFocusOff = function( event ) {
        const $this = $( this );
        const minValue = $this.hasClass('min') ? $this.val() : $this.parents( '.wqm-min-max-input-container' ).first().find( '.wqm-quantity-input.min' ).val();
        const maxValue = $this.hasClass('max') ? $this.val() : $this.parents( '.wqm-min-max-input-container' ).first().find( '.wqm-quantity-input.max' ).val();
        const errorCode = $this.hasClass('min') ? 'min_max' : 'max_min';

        if ( [ maxValue, minValue ].includes( '' ) ) {
            $( document.body ).triggerHandler( 'wqm_remove_error_tip', [ $( this ), errorCode ] );
            return;
        }

        if ( parseFloat( minValue ) <= parseFloat( maxValue ) ) {
            $( document.body ).triggerHandler( 'wqm_remove_error_tip', [ $( this ), errorCode ] );
            return;
        } 

        $( document.body ).triggerHandler( 'wqm_add_error_tip', [ $( this ), errorCode ] );
    };

    wqmMinMaxField.prototype.addErrorTip = function( event, element, error_type ) {
        const offset = element.position();

        if ( element.parents( '.wqm-min-max-input-container' ).first().find( '.wqm_error_tip' ).length === 0 ) {
            element.after( '<div class="wqm_error_tip ' + error_type + '">' + wqm_field_validation[error_type] + '</div>' );
            element.parent().find( '.wqm_error_tip' )
                .css( 'left', offset.left + element.width() - ( element.width() / 2 ) - ( $( '.wqm_error_tip' ).width() / 2 ) )
                .css( 'top', offset.top + element.height() )
                .fadeIn( '100' );
        }
    };

    wqmMinMaxField.prototype.removeErrorTip = function( event, element, error_type ) {
        element.parents( '.wqm-min-max-input-container' ).first().find( '.wqm_error_tip' ).fadeOut( '100', function() { $( this ).remove(); } );
    };

	/**
	 * Init wqmMinMaxField.
	 */
	new wqmMinMaxField();
} );