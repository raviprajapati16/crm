<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_signed_to_vendor extends App_mail_template
{
    protected $for = 'customer';

    protected $contract;

    protected $contact;

    public $slug = 'contract-signed-to-vendor';

    public $rel_type = 'contract';

    public function __construct($contract, $contact)
    {
        parent::__construct();

        $this->contract = $contract;
        $this->contact = $contact;
    }

    public function build()
    {
        $this->to($this->contact->email)
            ->set_rel_id($this->contract->id)
            ->set_merge_fields('client_merge_fields', $this->contract->client, $this->contact->id)
            ->set_merge_fields('vendors_merge_fields', $this->contract->client)
            ->set_merge_fields('contract_merge_fields', $this->contract->id);
    }
}
