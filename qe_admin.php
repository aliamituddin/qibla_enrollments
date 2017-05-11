<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class that will hold functionality for admin side
 *
 * PHP version 5
 *
 * @category   Admin Side Code
 * @package    Qibla Enrollments
 * @author     Muhammad Atiq
 * @version    1.0.0
 * @since      File available since Release 1.0.0
*/

class QE_Admin extends QE
{
    //Admin side starting point. Will call appropriate admin side hooks
    public function __construct() {
        
        do_action('qe_before_admin', $this );
        //All admin side code will go here
        
        add_action( 'admin_menu', array( $this, 'qe_admin_menus' ) );    
        
        do_action('qe_after_admin', $this );            
    }

    public function qe_admin_menus(){
        
        add_menu_page( QE_PLUGIN_NAME, QE_PLUGIN_NAME, 'manage_options', 'qe_settings', array( $this, 'qe_settings' ) );
        //add_submenu_page( 'qe_settings', QE_PLUGIN_NAME.' Settings', 'Enrollments', 'manage_options', 'qe_settings', array( $this, 'qe_settings' ) );
    }    
    
    public function qe_settings() {
        
        if( isset($_POST['btnsave']) && $_POST['btnsave'] != "" ) {
            
            $exclude = array('btnsave');
            $options = array();
            
            foreach( $_POST as $k => $v ) {
                if( !in_array( $k, $exclude )) {
                    $options[$k] = $v;
                }
            }
            
            update_option( 'qe_settings', $options );
            $message = 'Settings Saved Successfully!';
        }
        
        $options = get_option( 'qe_settings' );
        
        require_once QE_PLUGIN_PATH.'templates/admin/settings.php';
        $this->load_wp_media_uploader();
    }
        
    public function qe_reports() {
        
    }
    
    private function load_wp_media_uploader() {
        
        wp_enqueue_script('media-upload');
    	wp_enqueue_script('thickbox');
    	wp_enqueue_style('thickbox');
        
        $this->load_javascript();
    }
    
    private function load_javascript() {
        $html = '';
        ob_start();
        require_once QE_PLUGIN_PATH.'templates/admin/load_media_upload_js.php';
        $html = ob_get_contents();
        ob_end_clean();
        echo $html;
    }
}

$qe_admin = new QE_Admin();