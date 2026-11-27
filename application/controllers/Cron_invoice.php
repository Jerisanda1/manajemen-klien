<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_invoice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_cli() && php_sapi_name() !== 'cli' && empty($_SERVER['argv'])) {
            show_404();
        }

        $this->load->library('invoice_reminder');
    }

    // php index.php cron_invoice pengingat_email [dry]
    public function pengingat_email($mode = '')
    {
        $this->invoice_reminder->run($mode === 'dry');
    }
}
