<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reminder_customer_meeting_email_send extends App_mail_template
{
    protected $for = 'staff';

    protected $customer_email;

    protected $reminder;

    protected $lead;

    public $slug = 'reminder-customer-meeting-email-send';

    public $rel_type = 'staff';

    public function __construct($customer_email, $reminder, $lead)
    {
        parent::__construct();

        $this->customer_email = $customer_email;
        $this->reminder = $reminder;
        $this->lead = $lead;

        $this->ci->load->library('merge_fields/staff_merge_fields');

        $this->set_merge_fields('staff_merge_fields', get_staff_user_id());
        $this->set_merge_fields($this->ci->staff_merge_fields->meeting_email_to_customer($this->reminder, $this->lead));
    }

    public function build()
    {

        $user = get_staff(get_staff_user_id());
        $this->reply_to = $user->email;
        $this->to($this->customer_email)
            ->set_rel_id(get_staff_user_id());
    }
}
