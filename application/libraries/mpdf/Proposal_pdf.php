<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Proposal_pdf
{
    public $proposal;
    protected $ci;

    public function __construct($proposal)
    {
        $this->proposal = $proposal;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $proposalpdf = new App_mpdf();
        $proposalpdf->setType('proposal');
        $proposalpdf->setHTMLContent($html_content);
        $proposalpdf->setTitle($this->proposal->subject);
        $proposalpdf->setOutputMode($output_mode);
        return $proposalpdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/proposal/proposalpdf', ['proposal' => $this->proposal], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
