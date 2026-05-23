<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Expense_report_pdf
{
    public $report;
    protected $ci;

    public function __construct($report)
    {
        $this->report = $report;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->post('action_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $pdf = new App_mpdf();
        $pdf->setType('expense report');
        $pdf->setHTMLContent($html_content);
        $pdf->setTitle('Expense Report');
        $pdf->setOutputMode($output_mode);
        return $pdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/expense_report/pdf_report_view', ['data' => $this->report], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
