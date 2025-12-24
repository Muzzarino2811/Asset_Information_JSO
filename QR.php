<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QR extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function scan($qr_value)
    {
        // Check if the user is on a mobile device
        $is_mobile = $this->agent->is_mobile(); 

        // Fetch code data from the database using the scanned QR code
        $code = $this->general->get_data("mst_code", array(
            "code" => array(
                SQL_CONDITION_OPERATOR => SQL_WHERE_EQUAL,
                SQL_CONDITION_VALUE => $qr_value
            )
        ))->row();

        if ($code) {
            // Prepare data to send to the mobile view
            $data['qr_value'] = $qr_value;
            $data['code'] = $code;

            // Load the mobile view for QR scanning (inside mobile/assets)
            $this->load->view('mobile/assets/qr_scan', $data);
        } else {
            // If code is not found, show a 404 or error page
            show_404();
        }
    }
    
}
