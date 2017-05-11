<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class that will hold functionality for front side
 *
 * PHP version 5
 *
 * @category   Front Side Code
 * @package    Qibla Enrollments
 * @author     Muhammad Atiq
 * @version    1.0.0
 * @since      File available since Release 1.0.0
*/

class QE_Front extends QE
{
    //Front side starting point. Will call appropriate front side hooks
    public function __construct() {
        
        do_action('qe_before_front', $this );
        //All front side code will go here
        
        //add_action( 'woocommerce_order_status_pending', array( $this, 'qe_register_user_to_course' ), 10, 1 );
        //add_action( 'woocommerce_order_status_processing', array( $this, 'qe_register_user_to_course' ), 10, 1 );
        //add_action( 'woocommerce_order_status_completed', array( $this, 'qe_register_user_to_course' ), 10, 1 );
        //add_action( 'paypal_ipn_for_wordpress_payment_status_completed', array( $this, 'qpp_paypal_payment_completed' ), 10, 1 );
        
        add_action( 'paypal_ipn_for_wordpress_valid_ipn_request', array( $this, 'qe_paypal_payment_completed' ), 10, 1 );
        add_action( 'paypal_ipn_for_wordpress_payment_status_completed', array( $this, 'qe_paypal_payment_completed' ), 10, 1 );
        
        add_action( 'wc_gateway_stripe_process_payment', array( $this, 'qe_stripe_payment_completed' ), 20, 2 );
        
        do_action('qe_after_front', $this );
    }
    
    public function qe_stripe_payment_completed( $post_data, $order ) {
        $this->qe_register_user_to_course($order->id);        
    }
    
    public function qe_paypal_payment_completed( $posted ) {
        
        $custom_str = stripcslashes($posted['custom']);
        $custom = json_decode($custom_str,true);
        $order_id = $custom['order_id'];
        $this->qe_register_user_to_course($order_id);
    }
    
    public function qe_register_user_to_course( $order_id ) {
        
        $order = new WC_Order( $order_id );
        $user_id = $order->user_id;
        
        $current_user = get_userdata( $user_id );
        //global $current_user;
        $currentUserName = $current_user->user_login;
        $currentUserEmail = $current_user->user_email;
        $post_user_id = $user_id;//get_current_user_id();
        $firstName = $current_user->first_name;//get_user_meta($post_user_id, 'first_name', TRUE);
        $lastName = $current_user->last_name;//get_user_meta($post_user_id, 'last_name', TRUE);
        if($firstName){
            $userFullName = $firstName.' '.$lastName;
        }else{
            $firstNameBill = get_user_meta($post_user_id, 'billing_first_name', TRUE);
            $lastNameBill = get_user_meta($post_user_id, 'billing_last_name', TRUE);
            $userFullName = $firstNameBill.' '.$lastNameBill;
        }
        $userEmail = $current_user->user_email;
        
        $options = get_option( 'qe_settings' );
        $api_url = $options['api_url'];
        $auth_token = $options['auth_token'];
        $user_id = null;
        $page = 1;
        do{
            unset($responseUser);
            $url = $api_url.'/accounts/1/users/?per_page=100&page='.$page.'&access_token='.$auth_token;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $response = curl_exec($ch);
            curl_close($ch);
            $responseUser = json_decode($response, true);
            if($responseUser){            
                foreach ($responseUser as $keyUser => $valueUser) {
                    if($valueUser['login_id']==$currentUserName){
                        $user_id = $valueUser['id'];
                        break;
                    }
                }
            }
            $page++;
        }while( is_array($responseUser) && sizeof($responseUser)>0 && $user_id === null );
        
        if($user_id === null ){
            //Canvas New User Create
            $url = $api_url."/accounts/1/users?access_token=".$auth_token;
            $data = array( "account_id" => 1,
                "pseudonym[unique_id]" => $currentUserName,
                "user[terms_of_use]" => true,
                "user[skip_registration]" => true,
                "pseudonym[send_confirmation]" => false,
                "pseudonym[force_self_registration]" => false,
                "communication_channel[confirmation_url]" => false,
                "communication_channel[skip_confirmation]" => true,
                "force_validations" => false,
                "enable_sis_reactivation" => false,
                "user[name]" => $userFullName,
                "communication_channel[address]" => $userEmail );
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));

            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response, true);

            $user_id = $response['id'];
        }
       
        $order_items = $order->get_items();
        foreach ($order_items as $key => $value) {
            $course_id = get_post_meta($value['product_id'], 'course_id', TRUE);
            $url = $api_url."/courses/$course_id/enrollments?access_token=".$auth_token;
            $data = array("course_id" => $course_id,"enrollment[user_id]" => $user_id,"enrollment[enrollment_state]" => 'active');                        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
            $response = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($response, true);  
            
            $this->qe_add_user_to_group( $options, $user_id, $value );
        }
        
        return $status;
    }
    
    private function qe_add_user_to_group( $options = array(), $user_id= '', $item = array() ) { 
        
        if( empty($options) || empty($item) || empty($user_id) ) {
            return false;
        }
        
        if( !isset($item['variation_id']) && !is_array($options) ) {
            return false;
        }
        $api_url = $options['api_url'];
        $auth_token = $options['auth_token'];
        
        $course_id = get_post_meta($item['product_id'], 'course_id', TRUE);
        $product_groups = $this->qe_get_selected_group_with_cats($item);
        
        $url = $api_url."courses/$course_id/group_categories/?per_page=100&access_token=".$auth_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $group_cats = json_decode($response, true);

        $url = $api_url."courses/$course_id/groups/?per_page=1000&access_token=".$auth_token;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $groups = json_decode($response, true);
        
        if(is_array($groups) && is_array($group_cats) && is_array($product_groups) ) {
            foreach($product_groups as $product_group_cat=>$product_group ) {
                foreach ( $group_cats as $group_cat ) {
                    if( $group_cat['name'] == $product_group_cat ) {
                        foreach ( $groups as $group ) {
                            if( $group['group_category_id'] == $group_cat['id'] && $group['name'] == $product_group ) {
                                $url = $api_url."/groups/".$group['id']."/memberships/?access_token=".$auth_token;
                                $data = array( "user_id" => $user_id );
                                $ch = curl_init($url);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
                                $response = curl_exec($ch);
                                curl_close($ch);
                                $response = json_decode($response, true);
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
    
    private function qe_get_selected_group_with_cats( $item ) {
        $variation_product = new WC_Product_Variation( $item['variation_id'] );
        $variation_data = $variation_product->get_variation_attributes();        
        $group_cats = array();
        foreach( $variation_data as $attr => $val ) {
            foreach( $item->get_meta_data() as $subitem ) {
                if( $subitem->key == str_replace("attribute_", "", $attr) && strtolower($subitem->value) != "choose an option" ) {
                    $group_cats[wc_attribute_label( $subitem->key, $variation_product )] = $subitem->value;
                    break;
                }
            }
        }
        return $group_cats;
    }

}

$qe_front = new QE_Front();