<table class="company two-column">
    <tr>
	    <td class="logo" width="50%">
		    <?php
		    if ( WPI()->get_option( 'template', 'company_logo' ) ) {
			    printf( '<img src="var:company_logo" style="max-height:100px;"/>' );
		    } else {
			    printf( '<h2>%s</h2>', esc_html( WPI()->get_option( 'template', 'company_name' ) ) );
		    }
		    ?>
	    </td>
	    <td class="info" width="50%">
		    <?php echo nl2br( $this->template_options['bewpi_company_address'] ); ?><br/>
		    <?php echo nl2br( $this->template_options['bewpi_company_details'] ); ?>
	    </td>
    </tr>
</table>
