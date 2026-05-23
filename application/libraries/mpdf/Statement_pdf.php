<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Statement_pdf
{
    public $statement;
    protected $ci;

    public function __construct($statement)
    {
        $this->statement = $statement;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $statementpdf = new App_mpdf();
        $statementpdf->setType('customer statement');
        $statementpdf->setHTMLContent($html_content);
        $statementpdf->setTitle(_l('account_summary'));
        $statementpdf->setOutputMode($output_mode);
        return $statementpdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/statement/statementpdf', [
            'statement' => $this->statement,
        ], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
