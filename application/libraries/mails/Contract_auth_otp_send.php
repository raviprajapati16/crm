<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract_auth_otp_send extends App_mail_template
{
    protected $for = 'customer';

    protected $contract;

    protected $contact;

    protected $otp_code;

    public $slug = 'contract-auth-otp-send';

    public $rel_type = 'contract';

    public function __construct($contract, $contact, $otp_code)
    {
        parent::__construct();

        $this->contract = $contract;
        $this->contact = $contact;
        $this->otp_code = $otp_code;
    }

    public function build()
    {
        $this->to($this->contact->email)
            ->set_rel_id($this->contract->id)
            ->set_merge_fields('client_merge_fields', $this->contract->client, $this->contact->id)
            ->set_merge_fields('vendors_merge_fields', $this->contract->client)
            ->set_merge_fields('contract_merge_fields', $this->contract->id, $this->otp_code);
    }
}
