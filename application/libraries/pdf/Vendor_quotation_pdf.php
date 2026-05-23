<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_pdf.php');

class Vendor_quotation_pdf extends App_pdf
{
    protected $formData;
    public function __construct($formData, $tag = '')
    {
        parent::__construct();
        $this->SetTitle($this->formData['formkey']);
        $this->formData = $formData;
    }

    public function prepare()
    {

        $this->set_view_vars($this->formData);

        return $this->build();
    }

    protected function type()
    {
        return 'vendor_quotation_pdf';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_vendor_quotation_pdf.php';
        $actualPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/vendor_quotation_pdf.php';
        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }
        return $actualPath;
    }
}
