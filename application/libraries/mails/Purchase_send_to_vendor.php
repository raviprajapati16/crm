<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_send_to_vendor extends App_mail_template
{
    protected $for = 'vendor';

    protected $purchase;

    protected $attach_pdf;

    public $slug = 'purchase-email-send-to-vendor';

    public $rel_type = 'purchase';

    public function __construct($purchase, $attach_pdf, $cc = '', $reply_to = '')
    {
        parent::__construct();

        $this->purchase = $purchase;
        $this->attach_pdf = $attach_pdf;
        $this->cc = $cc;
        $this->reply_to = $reply_to;
    }

    public function build()
    {
        if ($this->attach_pdf) {
            set_time_limit(0);
            set_mailing_constant();
            $pdf = purchase_mpdf($this->purchase);
            $attach = $pdf->Output(slug_it(format_purchase_number($this->purchase->id)) . '.pdf', 'S');
            $this->add_attachment([
                'attachment' => $attach,
                'filename' => slug_it(format_purchase_number($this->purchase->id)) . '.pdf',
                'type' => 'application/pdf',
            ]);
        }

        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->purchase_model->get_attachments($this->purchase->id, $attachment);
                $this->add_attachment([
                    'attachment' => get_upload_path_by_type('purchase') . $this->purchase->id . '/' . $_attachment->file_name,
                    'filename' => $_attachment->file_name,
                    'type' => $_attachment->filetype,
                    'read' => true,
                ]);
            }
        }

        $this->to($this->purchase->email)
            ->set_rel_id($this->purchase->id)
            ->set_merge_fields('purchase_merge_fields', $this->purchase->id);
    }
}
