jQuery(function ($) {

	var wpip_meta_boxes_order = {

		init: function () {
			$( '#woocommerce-order-items' )
				.on( 'click', 'tr.item, tr.fee, tr.shipping, tr.refund', this.select_row )
				.on( 'click', 'button.bulk-credit-note-items', this.bulk_actions.generate_credit_note );

			$( '#wpip-credit-notes')
				.on( 'click', 'a.delete-credit-note', this.credit_note.delete );

			this.reload();
		},

		select_row: function () {
			var $table = $( this ).closest( 'table' );
			var $rows = $table.find( 'tr.selected' );

			if ( $rows.length ) {
				var selected_product = false;

				$rows.each( function() {
					if ( $( this ).is( 'tr.item' ) ) {
						selected_product = true;
					}
				} );

				var selected_refund = false;

				$rows.each( function() {
					if ( $( this ).is( 'tr.refund' ) ) {
						selected_refund = true;
					}
				} );

				if ( selected_product ) {
					$( '.bulk-credit-note-items' ).hide();
				} else if ( selected_refund ) {
					$( '.bulk-credit-note-items' ).show();
				}
			}
		},

		reload: function() {

			$( document ).ajaxComplete( function( event, xhr, settings ) {

				[ 'woocommerce_refund_line_items', 'woocommerce_delete_refund' ].forEach(function( action ) {
					if ( settings.data && settings.data.indexOf( action ) !== -1 ) {
						wpip_meta_boxes_order.reload_credit_notes();
					}
				});

			});

		},

		reload_credit_notes: function() {
			var data = {
				action:    'wpip_reload_credit_notes_meta_box',
				post:      wpip_admin_meta_boxes_order.post,
				security:  wpip_admin_meta_boxes_order.credit_note_nonce
			};

			$.ajax({
				url:     wpip_admin_meta_boxes_order.ajax_url,
				data:    data,
				type:    'POST',
				complete: function( data ) {
					wpip_meta_boxes_order.block();

					if ( data.responseText ) {
						$( '#wpip-credit-notes' ).find( 'ul.credit-notes' ).first().replaceWith( data.responseText );
					}

					wpip_meta_boxes_order.unblock();

				}
			});
		},

		block: function() {
			$( '#woocommerce-order-items' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		unblock: function() {
			$( '#woocommerce-order-items' ).unblock();
		},

		credit_note: {

			delete: function( e ) {
				e.preventDefault();
				var $refund = $( this ).closest( 'li.credit-note' );
				var refund_id = $refund.attr( 'data-order_refund_id' );

				var data = {
					action     : 'wpip_delete_credit_note',
					refund_id  : refund_id,
					security   : wpip_admin_meta_boxes_order.credit_note_nonce
				};

				$.ajax({
					url:     wpip_admin_meta_boxes_order.ajax_url,
					data:    data,
					type:    'POST',
					complete: function() {
						wpip_meta_boxes_order.reload_credit_notes();
					}
				});

			}

		},

		bulk_actions: {

			generate_credit_note: function (e) {
				e.preventDefault();
				var $table = $('table.woocommerce_order_items');
				var $rows = $table.find('tr.selected');

				if ($rows.length) {

					wpip_meta_boxes_order.block();

					var refund_ids = [];
					var deferred = [];

					$.map($rows, function (row) {
						var $row = $(row);

						if ($row.is('.refund')) {
							refund_ids.push(parseInt($($row).data('order_refund_id'), 10));
						}

						return;
					});

					if (refund_ids.length) {
						deferred.push($.ajax({
							url: wpip_admin_meta_boxes_order.ajax_url,
							data: {
								action: 'generate_credit_note',
								refund_ids: refund_ids,
								security: wpip_admin_meta_boxes_order.credit_note_nonce
							},
							type: 'POST'
						}));
					}

					if (deferred) {
						$.when.apply($, deferred).done(function ( response ) {

							if ( response.success ) {
								$( '.bulk-credit-note-items' ).replaceWith( response.data.html );
							} else {
								window.alert( response.data.error );
							}

							//wc_meta_boxes_order_items.reload_items();
							wpip_meta_boxes_order.unblock();
						});
					} else {
						wpip_meta_boxes_order.unblock();
					}
				}
			}
		}

	};

	wpip_meta_boxes_order.init();

});