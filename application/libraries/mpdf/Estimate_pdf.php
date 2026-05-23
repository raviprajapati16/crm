<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Estimate_pdf
{
    public $estimate;
    protected $ci;

    public function __construct($estimate)
    {
        $this->estimate = $estimate;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $estimatepdf = new App_mpdf();
        $estimatepdf->setType('estimate');
        $estimatepdf->setHTMLContent($html_content);
        $estimatepdf->setTitle(format_estimate_number($this->estimate->id));
        $estimatepdf->setOutputMode($output_mode);
        return $estimatepdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/estimates/estimatepdf', ['estimate' => $this->estimate], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
