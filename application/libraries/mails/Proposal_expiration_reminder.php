<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proposal_expiration_reminder extends App_mail_template
{
    protected $for = 'customer';

    protected $proposal;

    public $slug = 'proposal-expiry-reminder';

    public $rel_type = 'proposal';

    public function __construct($proposal)
    {
        parent::__construct();

        $this->proposal = $proposal;

        // For SMS
        $this->set_merge_fields('proposals_merge_fields', $this->proposal->id);
    }

    public function build()
    {
        set_time_limit(0);
        set_mailing_constant();
        if($this->proposal->download_request == "1"){
            $pdf    = proposal_mpdf($this->proposal);
            $attach = $pdf->Output(slug_it($this->proposal->subject) . '.pdf', 'S');
            $this->add_attachment([
                'attachment' => $attach,
                'filename'   => slug_it($this->proposal->subject) . '.pdf',
                'type'       => 'application/pdf',
            ]);
        }
        $this->to($this->proposal->email)
        ->set_rel_id($this->proposal->id);
    }
}
