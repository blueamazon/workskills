<?php
/**
 * Class that automatically loads fonts on runtime.
 *
 * The fonts need to be in uploads/woocommerce-pdf-invoices/fonts folder.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

if ( ! class_exists( 'BEWPIP_Font' ) ) {
	/**
	 * Class BEWPIP_Font.
	 */
	class BEWPIP_Font {

		/**
		 * Font styles/types.
		 *
		 * @var array.
		 */
		private static $font_styles = array( 'R', 'B', 'I', 'BI' );

		/**
		 * Initialize hooks.
		 */
		public static function init_hooks() {
			add_filter( 'bewpi_mpdf', array( __CLASS__, 'load_custom_fonts' ), 10, 2 );
		}

		/**
		 * Convert the mPDF fonts path to a relative path that points to our custom uploads directory using '..' parent directory command.
		 *
		 * @param string $fonts_path Path to the mPDF default fonts.
		 *
		 * @return string relative path to the custom fonts directory.
		 */
		private static function make_fonts_path_relative( $fonts_path ) {
			$wp_upload_dir = wp_upload_dir();

			$upload_dirs = array_filter( explode( DIRECTORY_SEPARATOR, $wp_upload_dir['basedir'] ) );
			$ttfont_dirs = array_filter( explode( DIRECTORY_SEPARATOR, $fonts_path ) );
			$same_dirs   = array_intersect( $upload_dirs, $ttfont_dirs );

			foreach ( $same_dirs as $dir ) {
				if ( false !== ( $key = array_search( $dir, $ttfont_dirs, true ) ) ) {
					unset( $ttfont_dirs[ $key ] );
				}
			}

			$fonts_path_relative = '';
			$dirs_up_count       = count( $ttfont_dirs );
			for ( $i = 0; $i < $dirs_up_count; $i ++ ) {
				$fonts_path_relative .= '../';
			}

			$custom_uploads_part = join( DIRECTORY_SEPARATOR, array_diff( $upload_dirs, $same_dirs ) );

			return $fonts_path_relative . $custom_uploads_part . '/woocommerce-pdf-invoices/fonts/';
		}

		/**
		 * Adds all the fonts to mPDF lib.
		 *
		 * @param mPDF  $mpdf mPDF object.
		 * @param array $font_files all fonts to add.
		 *
		 * @return bool
		 */
		private static function add_fonts( &$mpdf, $font_files ) {
			$fonts_path_relative = self::make_fonts_path_relative( _MPDF_TTFONTPATH );
			if ( empty( $fonts_path_relative ) ) {
				error_log( 'WooCommerce PDF Invoices: `$fonts_path_relative` is empty.' );

				return false;
			}

			sort( $font_files );

			// add font files to font data array.
			foreach ( $font_files as $font_path ) {

				$font       = pathinfo( $font_path );
				$font_names = explode( '-', $font['filename'] );
				if ( count( $font_names ) > 1 ) {
					$name       = $font_names[0];
					$font_style = $font_names[1];
				} else {
					// regular font.
					$name       = $font['filename'];
					$font_style = '';
				}

				if ( ! in_array( $font_style, self::$font_styles, true ) ) {
					error_log( 'WooCommerce PDF Invoices: `$font_style` not in array.' );
					continue;
				}

				$fontname = str_replace( ' ', '', strtolower( $name ) );

				// first create key to prevent undefined index notices.
				if ( ! isset( $mpdf->fontdata[ $fontname ] ) ) {
					$mpdf->fontdata[ $fontname ] = array( $font_style => $fonts_path_relative . basename( $font_path ) );
					continue;
				}

				$mpdf->fontdata[ $fontname ][ $font_style ] = $fonts_path_relative . basename( $font_path );
			}

			return true;
		}

		/**
		 * Loads all the fonts from uploads/woocommerce-pdf-invoices/fonts folder.
		 *
		 * @param mPDF $mpdf mPDF library object.
		 *
		 * @return mPDF $mpdf.
		 */
		public static function load_custom_fonts( $mpdf ) {
			$font_files = array_merge( glob( WPI_UPLOADS_DIR . '/fonts/*.ttf' ), glob( WPI_UPLOADS_DIR . '/fonts/*.otf' ) );
			if ( count( $font_files ) === 0 ) {
				return $mpdf;
			}

			if ( ! defined( '_MPDF_TTFONTPATH' ) ) {
				error_log( 'WooCommerce PDF Invoices: `_MPDF_TTFONTPATH` not defined.' );

				return $mpdf;
			}

			if ( ! self::add_fonts( $mpdf, $font_files ) ) {
				return $mpdf;
			}

			// can be used to add data if fonts are arabic, hebrew etc.
			$mpdf->fontdata = apply_filters( 'bewpi_fontdata', $mpdf->fontdata );

			// add fonts to available unifonts array.
			foreach ( $mpdf->fontdata as $font_style => $font ) {
				$mpdf->fontdata[ $font_style ] = $font;

				// add to available fonts array.
				foreach ( self::$font_styles as $style ) {
					if ( isset( $font[ $style ] ) && $font[ $style ] ) {
						// no suffix for regular style.
						$mpdf->available_unifonts[] = $font_style . trim( $style, 'R' );
					}
				}
			}

			$mpdf->default_available_fonts = $mpdf->available_unifonts;

			return $mpdf;
		}
	}
}
