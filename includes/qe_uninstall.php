<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class that will hold functionality for plugin deactivation
 *
 * PHP version 5
 *
 * @category   Uninstall
 * @package    Qibla Enrollments
 * @author     Muhammad Atiq
 * @version    1.0.0
 * @since      File available since Release 1.0.0
*/

class QE_Uninstall extends QE
{
    public function __construct() {
        
        do_action('qe_before_uninstall', $this );
        
        do_action('qe_after_uninstall', $this );
    }    
}

$qe_uninstall = new QE_Uninstall();