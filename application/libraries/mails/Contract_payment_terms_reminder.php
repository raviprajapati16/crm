<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_payment_terms_reminder extends App_mail_template
{
    protected $for = 'customer';

    protected $contract;

    protected $contact;

    protected $contact_term_id;

    public $slug = 'payment-terms-reminder';

    public $rel_type = 'contract';

    public function __construct($contract, $contact, $contact_term_id)
    {
        parent::__construct();
        $this->contract = $contract;
        $this->contact = $contact;
        $this->contact_term_id = $contact_term_id;
    }

    public function build()
    {
        $staffData = get_staff($this->contract->addedfrom);
        $this->reply_to = $staffData->email;

        $this->to($this->contact->email)
            ->set_rel_id($this->contract->id)
            ->set_merge_fields('contract_merge_fields', $this->contract->id, "", $this->contact_term_id)
            ->set_merge_fields('client_merge_fields', $this->contract->client);
    }
}
