<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Purchase_pdf
{
    public $purchase;
    protected $ci;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $purchasepdf = new App_mpdf();
        $purchasepdf->setType('purchase');
        $purchasepdf->setHTMLContent($html_content);
        $purchasepdf->setTitle($this->purchase->subject);
        $purchasepdf->setOutputMode($output_mode);
        return $purchasepdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/purchase/purchasepdf', ['purchase' => $this->purchase], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
