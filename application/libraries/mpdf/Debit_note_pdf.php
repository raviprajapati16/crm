<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Debit_note_pdf
{
    public $debit_note;
    protected $ci;

    public function __construct($debit_note)
    {
        $this->debit_note = $debit_note;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $creditnotepdf = new App_mpdf();
        $creditnotepdf->setType('debit note');
        $creditnotepdf->setHTMLContent($html_content);
        $creditnotepdf->setTitle(format_debit_note_number($this->debit_note->id));
        $creditnotepdf->setOutputMode($output_mode);
        return $creditnotepdf->generatePDF();
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/debit_note/debitnotepdf', [
        'debit_note' => $this->debit_note], true);
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
