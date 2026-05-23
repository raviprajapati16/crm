<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Invoice_pdf
{
    public $invoice;
    protected $ci;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $invoicepdf = new App_mpdf();
        $invoicepdf->setType('invoice');
        $invoicepdf->setHTMLContent($html_content);
        $invoicepdf->setTitle(format_invoice_number($this->invoice->id));
        $invoicepdf->setOutputMode($output_mode);
        return $invoicepdf->generatePDF();
    }

    private function get_payment_modes()
    {
        $this->ci->load->model('payment_modes_model');
        $payment_modes = $this->ci->payment_modes_model->get();

        // In case user want to include {invoice_number} or {client_id} in PDF offline mode description
        foreach ($payment_modes as $key => $mode) {
            if (isset($mode['description'])) {
                $payment_modes[$key]['description'] = str_replace('{invoice_number}', $this->invoice_number, $mode['description']);
                $payment_modes[$key]['description'] = str_replace('{client_id}', $this->invoice->clientid, $mode['description']);
            }
        }

        return $payment_modes;
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/invoice/invoicepdf', [
            'invoice' => $this->invoice,
        ], true);
        return $html_content ?: "<h1>Content Not Found</h1>";
    }
}
