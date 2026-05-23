<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals_failed_to_achieve extends App_mail_template
{
    protected $goal;

    public $staff_id;

    public $slug = 'goals-failed-to-achieve';

    public $rel_type = 'goals';

    public function __construct($goal, $staff_id)
    {
        parent::__construct();
        $this->goal = $goal;
        $this->staff_id = $staff_id;
    }

    public function build()
    {
        $staffData = get_staff(get_staff_user_id());
        $this->reply_to = $staffData->email;
        $assignedStaff = get_staff($this->staff_id);
        $this->to($assignedStaff->email)
            ->set_rel_id($this->goal->id)
            ->set_merge_fields('goals_merge_fields', $this->goal, $this->staff_id)
            ->set_merge_fields('staff_merge_fields', $this->staff_id);
    }
}
