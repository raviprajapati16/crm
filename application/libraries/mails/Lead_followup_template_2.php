<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_followup_template_2 extends App_mail_template
{
    protected $lead;

    public $slug = 'leads-follow-up-2';

    public $rel_type = 'leads-follow-up';

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
        ->set_merge_fields('leads_merge_fields', $this->lead)
        ->set_merge_fields('staff_merge_fields', get_staff_user_id())
        ->set_merge_fields('other_merge_fields');

    }
}
