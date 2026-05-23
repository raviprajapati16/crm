<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leads_form_brochure_download extends App_mail_template
{
    protected $email;

    public $slug = 'leads-form-brochure-download';

    public $rel_type = 'lead';

    public function __construct($email)
    {
        parent::__construct();
        $this->email = $email;
    }

    public function build()
    {
        $this->to($this->email);

    }
}
