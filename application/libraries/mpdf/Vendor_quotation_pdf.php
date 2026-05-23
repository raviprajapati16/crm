<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Vendor_quotation_pdf
{
    protected $formData;
    protected $ci;
    public function __construct($formData, $tag = '')
    {
        $this->ci = &get_instance();
        $this->formData = $formData;
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $vendorpdf = new App_mpdf();
        $vendorpdf->setType('vendor quotation');
        $vendorpdf->setHTMLContent($html_content);
        $vendorpdf->setTitle('Vendor Quotation #'.$this->formData['form_data']['lead_id']);
        $vendorpdf->setOutputMode($output_mode);
        return $vendorpdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/vendor_quotation_pdf/vendor_quotation_pdf', [
            'form_data' => $this->formData['form_data'],
            'item_data' => $this->formData['item_data'],
        ], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
