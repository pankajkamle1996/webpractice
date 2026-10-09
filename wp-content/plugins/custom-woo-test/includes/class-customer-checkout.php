<?php
Class CustomerCheckout{
    public function __construct(){
        add_action('woocommerce_after_order_notes',array($this,'add_customer_reference_field'));
        add_action('woocommerce_checkout_process',array($this,'validate_customer_reference'));
        add_action('woocommerce_checkout_create_order',array( $this, 'save_customer_reference'));
    }
    public function add_customer_reference_field($checkout){
        
        echo '<div class="customer_reference-custom-field">';
            $classes = array( 'form-row-wide' );

        // Add WooCommerce error classes after failed validation.
        if ( isset( $_POST['customer_reference'] ) && empty( $_POST['customer_reference'] ) ) {
            $classes[] = 'woocommerce-invalid';
            $classes[] = 'woocommerce-invalid-required-field';
        }
        woocommerce_form_field(
            'customer_reference',
            array(
                'type' => 'text',
                'label' => 'Customer Reference',
                'required' => true,
                'class' => $classes,  
            ),
            $checkout->get_value('customer_reference')
        );
        echo '</div>';

    }
    public function validate_customer_reference(){
        if( empty($_POST['customer_reference']) ){
            wc_add_notice(
                '<a href="#customer_reference"><strong>Customer Reference</strong> is a required field.</a>',
                'error'
            );
        }
    }
    public function save_customer_reference( $order ) {

        if ( isset( $_POST['customer_reference'] ) ) {

            $value = sanitize_text_field(
                wp_unslash( $_POST['customer_reference'] )
            );

            $order->update_meta_data(
                'customer_reference',
                $value
            );
        }
    }
}