<?php
/**
 * Invoice class.
 *
 * Handling invoice specific functionality.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices_Premium/Class
 * @version     0.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BEWPIP_Invoice.
 */
class BEWPIP_Invoice_UBL {

	/**
	 * @var BEWPI_Invoice
	 */
	private $invoice;

	/**
	 * @var WC_Order
	 */
	private $order;

	/**
	 * @var string
	 */
	private $currency;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var DateTime
	 */
	private $issue_date;

	/**
	 * @var string
	 */
	private $invoice_type_code;

	/**
	 * @var CleverIt\UBL\Invoice\Party
	 */
	private $accounting_supplier;

	/**
	 * @var CleverIt\UBL\Invoice\Party
	 */
	private $accounting_customer;

	/**
	 * @var CleverIt\UBL\Invoice\TaxTotal
	 */
	private $tax_total;

	/**
	 * @var $line_items []
	 */
	private $invoice_lines;

	/**
	 * @var string
	 */
	private $company_vat_id;

	/**
	 * @var $allowance_charges []
	 */
	private $allowance_charges;

	/**
	 * @var $filename
	 */
	private $full_path;

	/**
	 * Initialize hooks.
	 */
	public static function init_hooks() {
		add_action( 'admin_footer-edit.php', array( __CLASS__, 'add_bulk_generate_ubl_action' ) );
		add_action( 'load-edit.php', array( __CLASS__, 'bulk_generate_ubl_action' ) );
		add_filter( 'woocommerce_email_attachments', array( __CLASS__, 'attach_ubl_invoice_to_email' ), 99, 3 );
	}

	/**
	 * Adds bulk export actions to export PDF documents to zip file.
	 */
	public static function add_bulk_generate_ubl_action() {
		global $post_type;

		if ( 'shop_order' === $post_type ) {
			?>
			<script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('<option>').val('bulk_generate_ubl').text('<?php _e( 'Bulk Generate UBL', 'woocommerce-pdf-invoices' )?>').appendTo("select[name='action'], select[name='action2']");
                });
			</script>
			<?php
		}
	}

	/**
	 * Callback to bulk export all invoices to zip.
	 */
	public static function bulk_generate_ubl_action() {
		global $typenow;
		$post_type = $typenow;

		// Are we on order page?
		if ( 'shop_order' !== $post_type ) {
			return;
		}

		// Get the action.
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();
		if ( 'bulk_generate_ubl' !== $action ) {
			return;
		}

		// Security check.
		check_admin_referer( 'bulk-posts' );

		$post_ids = array();
		if ( isset( $_REQUEST['post'] ) ) {
			$post_ids = array_map( 'intval', $_REQUEST['post'] );
		}

		$invoice     = WPI()->get_invoice( $post_ids[0] );
		$ubl_invoice = new BEWPIP_Invoice_UBL( $invoice );
		$ubl_invoice->generate();
	}

	/**
	 * Attach the Credit Note to the Refunded or Cancelled email.
	 *
	 * @param array  $attachments attachments.
	 * @param string $status      name of email.
	 * @param object $order       order.
	 *
	 * @return array
	 */
	public static function attach_ubl_invoice_to_email( $attachments, $status, $order ) {
		$attachments = array();

		// Only attach to emails with WC_Order object.
		if ( ! $order instanceof WC_Order ) {
			return $attachments;
		}

		if ( ! BEWPI_Abstract_Invoice::exists( $order->get_id() ) ) {
			return $attachments;
		}

		$invoice     = WPI()->get_invoice( $order->get_id() );
		$ubl_invoice = new BEWPIP_Invoice_UBL( $invoice );
		$full_path   = $ubl_invoice->generate();

		$attachments[] = $full_path;

		return $attachments;
	}

	/**
	 * Get the accounting code.
	 *
	 * @param WC_Order $order order object.
	 *
	 * @return string
	 */
	public static function get_accounting_cost_code( $order ) {
		if ( 'yes' === $order->get_meta( 'is_vat_exempt' ) ) {
			return '8100'; // Intra-community.
		}

		if ( in_array( $order->get_billing_country(), WC()->countries->get_european_union_countries(), true ) ) {
			return '8000'; // EU.
		} else {
			return '8110'; // Non-EU.
		}
	}

	/**
	 * Get tax category code standard from UN/EDIFACT by tax class.
	 *
	 * @param string $tax_class tax class.
	 *
	 * @return string
	 */
	public static function get_tax_category_id( $tax_class = '' ) {
		switch ( $tax_class ) {
			case 'inherit':
				return 'S';
			case 'reduced_rate':
				// Lower rate.
				return 'AA';
			case 'zero_rate':
				return 'Z';
			default:
				return 'S';
		}
	}

	/**
	 * BEWPIP_Invoice_UBL Constructor.
	 *
	 * @param BEWPI_Invoice $invoice           invoice object.
	 * @param string        $invoice_type_code invoice type.
	 */
	public function __construct( BEWPI_Invoice $invoice, $invoice_type_code = '380' ) {
		$this->invoice           = $invoice;
		$this->order             = $invoice->order;
		$this->currency          = WPI()->get_currency( $this->order );
		$this->id                = $this->order->get_id();
		$this->issue_date        = $invoice->get_date();
		$this->invoice_type_code = $invoice_type_code;
		$this->company_vat_id    = WPI()->get_option( 'template', 'company_vat_id' );

		$accounting_supplier = new CleverIt\UBL\Invoice\Party();
		$this->initialize_accounting_supplier( $accounting_supplier );
		$this->accounting_supplier = $accounting_supplier;

		$accounting_customer = new CleverIt\UBL\Invoice\Party();
		$this->initialize_accounting_customer( $accounting_customer, $invoice );
		$this->accounting_customer = $accounting_customer;

		$this->add_invoice_lines();
		$this->add_tax_totals();
	}

	/**
	 * Initialize an Accounting Supplier object.
	 *
	 * @param \CleverIt\UBL\Invoice\Party $accounting_supplier accounting supplier object.
	 */
	private function initialize_accounting_supplier( &$accounting_supplier ) {
		// Set accounting supplier.
		$accounting_supplier->setName( WPI()->get_option( 'template', 'company_name' ) );

		$address = new \CleverIt\UBL\Invoice\Address();
		$address->setStreetName( get_option( 'woocommerce_store_address' ) );
		$address->setCityName( get_option( 'woocommerce_store_city' ) );
		$address->setPostalZone( get_option( 'woocommerce_store_postcode' ) );

		$country = new CleverIt\UBL\Invoice\Country();
		$country->setIdentificationCode( get_option( 'woocommerce_default_country' ) );
		$address->setCountry( $country );

		$accounting_supplier->setPostalAddress( $address );

		// PartyTaxScheme.
		$party_tax_scheme = new CleverIt\UBL\Invoice\PartyTaxScheme();
		$party_tax_scheme->setCompanyId( $this->company_vat_id );

		$tax_scheme = new CleverIt\UBL\Invoice\TaxScheme();
		// http://www.unece.org/trade/untdid/d01b/tred/tred5153.htm
		$tax_scheme->setId( 'VAT' );
		$party_tax_scheme->setTaxScheme( $tax_scheme );

		$accounting_supplier->setPartyTaxScheme( $party_tax_scheme );

		// PartyLegalEntity.
		$party_legal_entity = new CleverIt\UBL\Invoice\PartyLegalEntity();
		$party_legal_entity->setRegistrationName( WPI()->get_option( 'template', 'company_name' ) );
		$party_legal_entity->setCompanyId( $this->company_vat_id );
		$accounting_supplier->setPartyLegalEntity( $party_legal_entity );

		// Contact.
		$contact = new CleverIt\UBL\Invoice\Contact();
		$contact->setTelephone( WPI()->get_option( 'template', 'company_phone' ) );
		$contact->setElectronicMail( get_option( 'new_admin_email' ) );
		$accounting_supplier->setContact( $contact );
	}

	/**
	 * Initialize an Accounting Customer object.
	 *
	 * @param \CleverIt\UBL\Invoice\Party $accounting_customer accounting customer object.
	 * @param BEWPI_Abstract_Invoice      $invoice             invoice object.
	 */
	private function initialize_accounting_customer( &$accounting_customer, $invoice ) {
		$order = $invoice->order;
		$name  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

		// Set accounting supplier.
		$accounting_customer->setName( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );

		$address = new \CleverIt\UBL\Invoice\Address();
		$address->setStreetName( sprintf( '%1$s %2$s', $order->get_billing_address_1(), $order->get_billing_address_2() ) );
		$address->setCityName( $order->get_billing_city() );
		$address->setPostalZone( $order->get_billing_postcode() );
		$country = new CleverIt\UBL\Invoice\Country();
		$country->setIdentificationCode( $order->get_billing_country() );
		$address->setCountry( $country );
		$accounting_customer->setPostalAddress( $address );

		// PartyTaxScheme.
		$party_tax_scheme = new CleverIt\UBL\Invoice\PartyTaxScheme();
		$company_vat_id   = (string) WPI()->get_meta( $order, '_vat_number' );
		$party_tax_scheme->setCompanyId( $company_vat_id );

		$tax_scheme = new CleverIt\UBL\Invoice\TaxScheme();
		$tax_scheme->setId( 'VAT' );
		$party_tax_scheme->setTaxScheme( $tax_scheme );

		$accounting_customer->setPartyTaxScheme( $party_tax_scheme );

		// PartyLegalEntity.
		$legal_entity = new CleverIt\UBL\Invoice\PartyLegalEntity();
		$legal_entity->setRegistrationName( $order->get_billing_company() );
		$legal_entity->setCompanyId( $company_vat_id );
		$accounting_customer->setPartyLegalEntity( $legal_entity );

		// Contact.
		$contact = new CleverIt\UBL\Invoice\Contact();
		$contact->setName( $name );
		$contact->setTelephone( $order->get_billing_phone() );
		$contact->setElectronicMail( $order->get_billing_email() );
		$accounting_customer->setContact( $contact );
	}

	/**
	 * Add line items.
	 */
	private function add_invoice_lines() {
		$accounting_cost_code = self::get_accounting_cost_code( $this->order );

		/**
		 * Product line item.
		 *
		 * @var WC_Order_Item_Product $line_item
		 */
		foreach ( $this->order->get_items() as $line_item ) {
			$invoice_line = new CleverIt\UBL\Invoice\InvoiceLine();
			$invoice_line->setId( $line_item->get_id() );
			$invoice_line->setInvoicedQuantity( $line_item->get_quantity() );
			// Line total after discount.
			$invoice_line->setLineExtensionAmount( $this->order->get_line_total( $line_item, false ) );

			// Product.
			$item = new CleverIt\UBL\Invoice\Item();
			$item->setName( $line_item->get_name() );

			$product = $line_item->get_product();
			if ( false !== $product ) {
				$item->setDescription( $product->get_short_description() );
				$item->setSellersItemIdentification( $product->get_sku() );
			}

			$invoice_line->setItem( $item );

			/*$item_discount = 0;
			if ( $line_item->get_subtotal() !== $line_item->get_total() ) {
				$item_discount = $this->order->get_item_subtotal( $line_item, false, false ) - $this->order->get_item_total( $line_item, false, false );
			}*/

			$price = new CleverIt\UBL\Invoice\Price();
			$price->setBaseQuantity( $line_item->get_quantity() );
			// Price including tax.
			$price->setPriceAmount( $this->order->get_item_total( $line_item, true ) );
			$invoice_line->setPrice( $price );

			$tax_total = new CleverIt\UBL\Invoice\TaxTotal();
			$tax_total->setTaxAmount( $line_item->get_total_tax() );

			$taxes = $line_item->get_taxes();
			foreach ( $taxes['total'] as $rate_id => $amount ) {
				// Skip zero tax.
				if ( 0.00 === (float) $amount ) {
					continue;
				}

				$tax_subtotal = new CleverIt\UBL\Invoice\TaxSubTotal();
				$tax_subtotal->setTaxableAmount( ( (float) $amount / floatval( WC_Tax::get_rate_percent( $rate_id ) ) ) * 100 );
				$tax_subtotal->setTaxAmount( (float) $amount );

				// Tax category.
				$tax_category = new CleverIt\UBL\Invoice\TaxCategory();

				if ( 'yes' === $this->order->get_meta( 'is_vat_exempt' ) ) {
					$tax_code = 'E';
				} else {
					$tax_code = self::get_tax_category_code( $line_item->get_tax_class() );
				}

				$tax_category->setId( $tax_code );
				$tax_category->setPercent( floatval( WC_Tax::get_rate_percent( $rate_id ) ) );

				// Tax scheme.
				$tax_scheme = new CleverIt\UBL\Invoice\TaxScheme();
				$tax_scheme->setId( 'VAT' );
				$tax_category->setTaxScheme( $tax_scheme );

				$tax_subtotal->setTaxCategory( $tax_category );

				$tax_total->addTaxSubTotal( $tax_subtotal );
			}

			$invoice_line->setTaxTotal( $tax_total );
			$invoice_line->setAccountingCostCode( $accounting_cost_code );

			$this->invoice_lines[] = $invoice_line;
		}
	}

	/**
	 * Add tax lines.
	 */
	private function add_tax_totals() {
		/**
		 * Add tax lines.
		 *
		 * @var WC_Order_Item_Tax $tax
		 */
		foreach ( $this->order->get_taxes() as $id => $tax ) {
			// Tax total.
			$tax_total = new CleverIt\UBL\Invoice\TaxTotal();
			$tax_total->setTaxAmount( $this->order->get_total_tax() );

			// Tax subtotal.
			$tax_subtotal = new CleverIt\UBL\Invoice\TaxSubTotal();
			$tax_percent  = floatval( WC_Tax::get_rate_percent( $tax->get_rate_id() ) );
			$tax_subtotal->setTaxableAmount( ( $tax->get_tax_total() / $tax_percent ) * 100 );
			$tax_subtotal->setTaxAmount( $tax->get_tax_total() );

			// Tax category.
			$tax_category = new CleverIt\UBL\Invoice\TaxCategory();
			// https://www.unece.org/trade/untdid/d07b/tred/tred5305.htm.
			// http://blogs.twinfield.nl/hubfs/Overzicht_btw-codes_en_ubl-codes.pdf?t=1511774971274&utm_campaign=Sam%20&utm_source=hs_automation&utm_medium=email&utm_content=55110857&_hsenc=p2ANqtz--st7zTI-SkHNNv27u2VLsQURw7Cn45czDoVUMWmqNdgW9Gz_17GGC2pFMVC_eObuw38KTy-DhXk4qNfA8iyxVYW0g1aA&_hsmi=55110857.


			if ( 'yes' === $this->order->get_meta( 'is_vat_exempt' ) ) {
				$tax_code = 'E';
			} else {
				$tax_code = self::get_tax_category_code( $tax->get_tax_class() );
			}

			$tax_category->setId( $tax_code );
			$tax_category->setPercent( $tax_percent );

			// Tax scheme.
			$tax_scheme = new CleverIt\UBL\Invoice\TaxScheme();
			$tax_scheme->setId( 'VAT' );
			$tax_category->setTaxScheme( $tax_scheme );

			$tax_subtotal->setTaxCategory( $tax_category );

			$tax_total->addTaxSubTotal( $tax_subtotal );

			$this->tax_total = $tax_total;
		}
	}

	/**
	 * Add discount.
	 *
	 * @param WC_Order_Item_Tax $tax        tax.
	 * @param float             $tax_amount amount.
	 */
	private function add_discount( $tax, $tax_amount ) {
		$allowance_charge = new CleverIt\UBL\Invoice\AllowanceCharge();
		$allowance_charge->setChargeIndicator( 'false' );
		$allowance_charge->setAllowanceChargeReason( __( 'Discount', 'woocommerce-pdf-invoices' ) );
		$allowance_charge->setAmount( $tax_amount );

		$tax_category = new CleverIt\UBL\Invoice\TaxCategory();

		if ( 'yes' === $this->order->get_meta( 'is_vat_exempt' ) ) {
			$tax_code = 'E';
		} else {
			$tax_code = self::get_tax_category_code( $tax->get_tax_class() );
		}

		$tax_category->setId( $tax_code );
		$tax_category->setPercent( floatval( WC_Tax::get_rate_percent( $tax->get_rate_id() ) ) );

		$tax_scheme = new \CleverIt\UBL\Invoice\TaxScheme();
		$tax_scheme->setId( 'VAT' );
		$tax_category->setTaxScheme( $tax_scheme );

		$allowance_charge->setTaxCategory( $tax_category );
		$this->allowance_charges[] = $allowance_charge;
	}

	/**
	 * Add shipping as AllowanceCharge.
	 *
	 * @param WC_Order_Item_Shipping $shipping    shipping.
	 * @param int                    $tax_rate_id rate id.
	 * @param float                  $tax_amount  $tax_amount tax amount.
	 */
	private function add_shipping( $shipping, $tax_rate_id, $tax_amount ) {
		$allowance_charge = new CleverIt\UBL\Invoice\AllowanceCharge();
		$allowance_charge->setChargeIndicator( 'true' );
		$allowance_charge->setAllowanceChargeReason( $shipping->get_name() );
		$allowance_charge->setAmount( $tax_amount );

		$tax_category = new CleverIt\UBL\Invoice\TaxCategory();

		if ( 'yes' === $this->order->get_meta( 'is_vat_exempt' ) ) {
			$tax_code = 'E';
		} else {
			$tax_code = self::get_tax_category_code( $shipping->get_tax_class() );
		}

		$tax_category->setId( $tax_code );

		$tax_percent = floatval( WC_Tax::get_rate_percent( $tax_rate_id ) );
		$tax_category->setPercent( $tax_percent );

		$tax_scheme = new \CleverIt\UBL\Invoice\TaxScheme();
		$tax_scheme->setId( 'VAT' );
		$tax_category->setTaxScheme( $tax_scheme );

		$allowance_charge->setTaxCategory( $tax_category );
		$this->allowance_charges[] = $allowance_charge;
	}

	/**
	 * Add fee as AllowanceCharge.
	 *
	 * @param WC_Order_Item_Fee $fee         fee.
	 * @param int               $tax_rate_id rate id.
	 * @param float             $tax_amount  amount.
	 */
	private function add_fee( $fee, $tax_rate_id, $tax_amount ) {
		$allowance_charge = new CleverIt\UBL\Invoice\AllowanceCharge();
		$allowance_charge->setChargeIndicator( 'true' );
		$allowance_charge->setAllowanceChargeReason( $fee->get_name() );
		$allowance_charge->setAmount( (float) $tax_amount );

		$tax_category = new CleverIt\UBL\Invoice\TaxCategory();

		if ( 'yes' === $this->order->get_meta( 'is_vat_exempt' ) ) {
			$tax_code = 'E';
		} else {
			$tax_code = self::get_tax_category_code( $fee->get_tax_class() );
		}

		$tax_category->setId( $tax_code );
		$tax_percent = floatval( WC_Tax::get_rate_percent( $tax_rate_id ) );
		$tax_category->setPercent( $tax_percent );

		$tax_scheme = new \CleverIt\UBL\Invoice\TaxScheme();
		$tax_scheme->setId( 'VAT' );
		$tax_category->setTaxScheme( $tax_scheme );

		$allowance_charge->setTaxCategory( $tax_category );
		$this->allowance_charges[] = $allowance_charge;
	}

	/**
	 * Generate UBL file.
	 */
	public function generate() {
		// Create invoice object.
		$ubl_invoice = new CleverIt\UBL\Invoice\Invoice();

		// General invoice details.
		$ubl_invoice->setId( $this->order->get_id() );
		$ubl_invoice->setIssueDate( $this->invoice->get_date() );
		$ubl_invoice->setInvoiceTypeCode( $this->invoice_type_code );
		$ubl_invoice->setDocumentCurrencyCode( $this->currency );
		$ubl_invoice->setBuyerReference( $this->order->get_id() );

		// References.
		$order_reference = new CleverIt\UBL\Invoice\OrderReference();
		$order_reference->setId( $this->order->get_id() );
		$order_reference->setSalesOrderID( $this->order->get_id() );
		$order_reference->setIssueDate( $this->order->get_date_created() );
		$ubl_invoice->setOrderReference( $order_reference );

		// Additional document reference.
		$additional_document_reference = new CleverIt\UBL\Invoice\AdditionalDocumentReference();
		$additional_document_reference->setId( $this->invoice->get_formatted_number() );
		$additional_document_reference->setFilename( $this->invoice->get_filename() );
		$pdf_file = file_get_contents( $this->invoice->get_full_path() );
		$additional_document_reference->setAttachment( base64_encode( $pdf_file ) );
		$ubl_invoice->setAdditionalDocumentReference( $additional_document_reference );

		$ubl_invoice->setAccountingSupplierParty( $this->accounting_supplier );
		$ubl_invoice->setAccountingCustomerParty( $this->accounting_customer );

		// AllowanceCharge.
		/**
		 * Annotation.
		 *
		 * @var WC_Order_Item_Tax $tax
		 */
		foreach ( $this->order->get_taxes() as $id => $tax ) {
			$discount_tax_amount = BEWPIP_Invoice::get_discount_tax_by_rate_id( $this->order, $tax->get_rate_id() );
			$this->add_discount( $tax, (float) $discount_tax_amount );
		}

		/**
		 * Annotation.
		 *
		 * @var WC_Order_Item_Shipping $shipping
		 */
		foreach ( $this->order->get_items( 'shipping' ) as $shipping ) {
			$shipping_taxes = $shipping->get_taxes();

			if ( isset( $shipping_taxes['total'] ) ) {
				foreach ( $shipping_taxes['total'] as $rate_id => $amount ) {
					$this->add_shipping( $shipping, $rate_id, (float) $amount );
				}
			}
		}

		/**
		 * Annotation.
		 *
		 * @var WC_Order_Item_Fee $fee
		 */
		foreach ( $this->order->get_fees() as $fee ) {
			$fee_taxes = $fee->get_taxes();

			if ( isset( $fee_taxes['total'] ) ) {
				foreach ( $fee_taxes['total'] as $rate_id => $amount ) {
					$this->add_fee( $fee, $rate_id, (float) $amount );
				}
			}
		}

		$ubl_invoice->setAllowanceCharges( $this->allowance_charges );
		$ubl_invoice->setTaxTotal( $this->tax_total );
		$ubl_invoice->setInvoiceLines( $this->invoice_lines );

		$legal_monetary_total = new CleverIt\UBL\Invoice\LegalMonetaryTotal();
		$legal_monetary_total->setLineExtensionAmount( $this->order->get_subtotal() );
		$legal_monetary_total->setTaxExclusiveAmount( BEWPIP_Invoice::calculate_subtotal( $this->order ) );
		$legal_monetary_total->setTaxInclusiveAmount( $this->order->get_total() );
		$legal_monetary_total->setAllowanceTotalAmount( $this->order->get_total_discount() );
		$legal_monetary_total->setChargeTotalAmount( $this->order->get_shipping_total() );
		$legal_monetary_total->setPayableAmount( $this->order->get_total() );
		$ubl_invoice->setLegalMonetaryTotal( $legal_monetary_total );

		$xml             = CleverIt\UBL\Invoice\Generator::invoice( $ubl_invoice, $this->currency );
		$ubl_path        = $this->invoice->get_rel_pdf_path() . '/' . $this->invoice->get_formatted_number() . '.xml';
		$this->full_path = WPI_ATTACHMENTS_DIR . '/' . $ubl_path;

		update_post_meta( $this->order->get_id(), '_bewpi_ubl_xml_path', $ubl_path );

		$success = file_put_contents( $this->full_path, $xml );

		return (bool) $success ? $this->full_path : false;
	}

	/**
	 * Get the full path of the UBL xml.
	 *
	 * @return string
	 */
	public function get_full_path() {
		return $this->full_path;
	}
}
