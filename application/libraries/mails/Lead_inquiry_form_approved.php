<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_inquiry_form_approved extends App_mail_template
{
    protected $lead;

    public $slug = 'lead-customer-inquiry-form-approved';

    public $rel_type = 'lead';

    public function __construct($lead)
    {
        parent::__construct();
        $this->lead = $lead;
    }

    public function build()
    {
        $staffData = get_staff(get_staff_user_id());
        $this->reply_to = $staffData->email;

        $this->to($this->lead->email)
        ->set_rel_id($this->lead->id)
        ->set_merge_fields('leads_merge_fields', $this->lead);

    }
}
