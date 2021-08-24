<?php
/**
 * Plugin Name: Woo Product Purchase to Post Converter
 * Description: Create a post from a product purchase after the order is completed.
 * Author: Vangelis Sapountzis
 * Author URI: https://sapountzis.gr
 * Version: 1.0.0
 * Text Domain: product-order-cpt
 */

 if( !defined('ABSPATH') ):
    exit;
 endif;

 class ProductOrderPost {

    public function __construct()
    {
        //add_action('init', array($this, 'product_to_post'));
      add_filter( 'woocommerce_checkout_fields' , array($this, 'custom_override_checkout_fields' ));
      add_action( 'woocommerce_checkout_update_order_meta', array($this, 'my_custom_checkout_field_update_order_meta' ));
      add_action( 'woocommerce_thankyou', array($this, 'create_event_clever_purchase'));
      //add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'my_custom_checkout_field_display_admin_order_meta', 10, 1 ));

    }

    public function custom_override_checkout_fields( $fields ){
      foreach ( WC()->cart->get_cart() as $cart_item ) {
         if ( $cart_item['data']->get_id() == 13 ) {
       $fields['billing']['clever_topic_title'] = array(
         'label'     => __('Topic Title', 'woocommerce'),
         'placeholder'   => _x('Enter topic title', 'placeholder', 'woocommerce'),
         'required'  => true,
         'class'     => array('form-row-wide'),
         'clear'     => true
      );
    
    $fields['billing']['clever_topic_description'] = array(
         'label'     => __('Brief Topic Description', 'woocommerce'),
         'placeholder'   => _x('Tell us about your topic', 'placeholder', 'woocommerce'),
         'required'  => false,
         'class'     => array('form-row-wide'),
         'clear'     => true,
         'type'		=> 'textarea'
      );
      
         }
     }
         return $fields;  
    }

    public function my_custom_checkout_field_update_order_meta( $order_id ) {
	
      $order = wc_get_order( $order_id );
      $items = $order->get_items(); 
      foreach ( $items as $item_id => $item ) {
         $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
         
         if ( $product_id === 13 ) {
      
            update_post_meta( $order_id, 'clever_topic_title', sanitize_text_field( $_POST['clever_topic_title'] ) );
            update_post_meta( $order_id, 'clever_topic_description', sanitize_text_field( $_POST['clever_topic_description'] ) );

         }
         
      }
   }

   public function create_event_clever_purchase( $order_id ) {
	
      $order = wc_get_order( $order_id );
      $items = $order->get_items(); 
      foreach ( $items as $item_id => $item ) {
         $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
         //$product_id = $item->get_product_id();
         
         $clever_topic_title = get_post_meta($order_id, 'clever_topic_title', true);
         $clever_topic_description = get_post_meta($order_id, 'clever_topic_description', true);
      
         if ( $product_id === 13 ) {
      
         $args = array(
            'post_title' => $clever_topic_title,
            'post_content' => $clever_topic_description,
            'post_status' => 'draft'
         );
         
         //tribe_create_event( $args );
         wp_insert_post( $args );
      
         }
      }
   }

   /*public function my_custom_checkout_field_display_admin_order_meta( $order ){
       echo '<p><strong>'.__('Topic Title').':</strong> ' . get_post_meta( $order->id, 'Topic Title', true ) . '</p>';
   }*/

 }

 new ProductOrderPost;