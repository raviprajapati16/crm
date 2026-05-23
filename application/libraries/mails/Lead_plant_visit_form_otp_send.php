<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_plant_visit_form_otp_send extends App_mail_template
{
    protected $lead;

    public $slug = 'lead-plant-visit-form-otp-send';

    public $rel_type = 'lead';

    protected $otp_code;

    public function __construct($lead, $otp_code)
    {
        parent::__construct();
        $this->lead = $lead;
        $this->otp_code = $otp_code;
    }

    public function build()
    {
        $staffData = get_staff(get_staff_user_id());
        $this->reply_to = $staffData->email;

        $this->to($this->lead->email)
        ->set_rel_id($this->lead->id)
        ->set_merge_fields('leads_merge_fields', $this->lead, $this->otp_code);
    }
}
