<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Credit_note_pdf
{
    public $credit_note;
    protected $ci;

    public function __construct($credit_note)
    {
        $this->credit_note = $credit_note;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $creditnotepdf = new App_mpdf();
        $creditnotepdf->setType('credit note');
        $creditnotepdf->setHTMLContent($html_content);
        $creditnotepdf->setTitle(format_credit_note_number($this->credit_note->id));
        $creditnotepdf->setOutputMode($output_mode);
        return $creditnotepdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/credit_note/creditnotepdf', [
        'credit_note' => $this->credit_note], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
