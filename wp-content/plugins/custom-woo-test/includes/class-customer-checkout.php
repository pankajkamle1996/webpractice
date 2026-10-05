<?php
Class CustomerCheckout{
    public function __construct(){
        add_action('woocommerce_after_order_notes',array($this,'add_customer_reference_field'));

    }
    public function add_customer_reference_field($checkout){
        echo '<div class="customer_reference-custom-field">';
        woocommerce_form_field(
            'customer_reference',
            array(
                'type' => 'text',
                'label' => 'Customer Reference',
                'required' => true,
                'class' => array('form-row-wide'),  
            ),
            $checkout->get_value('customer_reference')
        );
        echo '</div>';

    }

}