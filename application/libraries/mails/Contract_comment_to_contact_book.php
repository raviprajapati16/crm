<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_comment_to_contact_book extends App_mail_template
{
    protected $for = 'customer';

    protected $contract;

    protected $contact;

    public $slug = 'contract-comment-to-contact-book-user';

    public $rel_type = 'contract';

    public function __construct($contract, $contact)
    {
        parent::__construct();

        $this->contract = $contract;
        $this->contact  = $contact;
        $this->set_merge_fields('contact_book_merge_fields', $this->contract->client);
        $this->set_merge_fields('contract_merge_fields', $this->contract->id);
    }

    public function build()
    {
        $this->to($this->contact['email'])
            ->set_rel_id($this->contract->id);
    }
}
