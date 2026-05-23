<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Payment_pdf
{
    public $payment;
    protected $ci;

    public function __construct($payment)
    {
        $this->payment = $payment;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $paymentpdf = new App_mpdf();
        $paymentpdf->setType('payment');
        $paymentpdf->setHTMLContent($html_content);
        $paymentpdf->setTitle(_l('payment') . ' #' . $this->payment->paymentid);
        $paymentpdf->setOutputMode($output_mode);
        return $paymentpdf->generatePDF();
    }

    protected function renderHTML()
    {
        if ($this->payment->invoiceid == "0") {
            $this->ci->load->model('proposals_model');
            $proposalData = $this->ci->proposals_model->get($this->payment->proposal_id);
            $proposalPaymentData = get_proposal_payment_data($this->payment->proposal_id);
            $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/payment/paymentpdf', [
                'payment' => $this->payment,
                'proposalData' => $proposalData,
                'proposalPaymentData' => $proposalPaymentData,
            ], true);
        } else {
            $amountDue = ($this->payment->invoice_data->status != Invoices_model::STATUS_PAID
                && $this->payment->invoice_data->status != Invoices_model::STATUS_CANCELLED ? true : false);
            $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/payment/paymentpdf', [
                'payment' => $this->payment,
                'amountDue' => $amountDue,
            ], true);
        }
        return $html_content ?: "<h1>File Not Found</h1>";
    }
}
