<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class that will hold functionality for plugin activation
 *
 * PHP version 5
 *
 * @category   Install
 * @package    Qibla Enrollments
 * @author     Muhammad Atiq
 * @version    1.0.0
 * @since      File available since Release 1.0.0
*/

class QE_Install extends QE
{
    public function __construct() {
        
        do_action('qe_before_install', $this );
        
        do_action('qe_after_install', $this );
    }    
}

$qe_install = new QE_Install();