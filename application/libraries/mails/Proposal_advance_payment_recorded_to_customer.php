<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proposal_advance_payment_recorded_to_customer extends App_mail_template
{
    protected $for = 'staff';

    protected $proposal;

    protected $email;

    public $slug = 'proposal-advance-payment-recorded';

    public $rel_type = 'proposal';

    public function __construct($email, $proposal, $reply_to = '')
    {
        parent::__construct();
        $this->proposal = $proposal;
        $this->email = $email;
        $this->reply_to = $reply_to;
    }

    public function build()
    {
        $this->to($this->email)
        ->set_rel_id($this->proposal->id)
        ->set_merge_fields('proposals_merge_fields', $this->proposal->id, $this->proposal->paymentid);
    }
}
