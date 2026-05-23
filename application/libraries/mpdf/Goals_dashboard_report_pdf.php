<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Goals_dashboard_report_pdf
{
    public $result;
    protected $ci;

    public function __construct($result)
    {
        $this->result = $result;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->post('action_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $pdf = new App_mpdf();
        $pdf->setType('goals dashboard report');
        $pdf->setHTMLContent($html_content);
        $pdf->setTitle('Goals Report');
        $pdf->setOutputMode($output_mode);
        return $pdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/goals_dashboard_report_pdf/goals_dashboard_report_pdf', ['data' => $this->result], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
