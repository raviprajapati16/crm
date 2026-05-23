<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_campaigns extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        ini_set("memory_limit", -1);
        $this->load->model('email_campaign_templates_model');
        $this->load->model('leads_model');
        $this->load->model('clients_model');
        $this->load->model('email_campaigns_model');
        $this->load->model('staff_model');
        $this->load->model('email_campaigns_emails_model');
        $this->load->model('email_campaign_mail_list_model');
    }

    public function index()
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns');
        }
        $data['templates'] = $this->email_campaign_templates_model->get_all_templates();
        $data['stats'] = $this->email_campaigns_model->get_email_campaign_stats();
        $data['campaign_stats'] = $this->email_campaigns_model->get_email_all_campaign_stats();

        if ($this->input->post()) {
            $this->app->get_table_data('email_campaigns');
        }
        $this->load->view('admin/email_campaigns/index', $data);
    }


    public function results($id)
    {
        if (!has_permission('email_campaigns', '', 'view') && !has_permission('email_campaigns', '', 'view_own')) {
            access_denied('email_campaigns');
        }

        if ($this->input->post()) {
            $this->app->get_table_data('email_campaigns_results', ["id" => $id]);
        }

        $data['mails'] = $this->staff_model->get_all_webmails(false);
        $data['custom_mails'] = $this->email_campaigns_emails_model->get_all();
        $data['send_from'] = $this->email_campaigns_model->get_email_campaign_send_from($id);
        $custom = [];
        $staff = [];
        if (!empty($data['send_from'])) {
            foreach ($data['send_from'] as $item) {
                if ($item['send_from'] == 'custom_email') {
                    $custom[] = $item['mail_send_from_id'];
                } elseif ($item['send_from'] == 'staff') {
                    $staff[] = $item['mail_send_from_id'];
                }
            }
        }

        if (!empty($data['mails'])) {
            foreach ($data['mails'] as $key => $item) {
                if (!in_array($item->staffid, $staff)) {
                    unset($data['mails'][$key]);
                }
            }
        }

        if (!empty($data['custom_mails'])) {
            foreach ($data['custom_mails'] as $key => $item) {
                if (!in_array($item['id'], $custom)) {
                    unset($data['custom_mails'][$key]);
                }
            }
        }

        $data['stats'] = $this->email_campaigns_model->get_email_campaign_stats($id);
        $data['stats_by_date'] = $this->email_campaigns_model->get_email_campaign_stats_by_date($id);
        $data['stats_by_status'] = $this->email_campaigns_model->get_email_campaign_status_count($id);
        $data['stats_by_open_status'] = $this->email_campaigns_model->get_email_campaign_open_status_count($id);
        $data['stats_by_sender'] = $this->email_campaigns_model->get_email_campaign_stats_by_sender($id);
        $this->load->view('admin/email_campaigns/results', $data);
    }

    public function create()
    {
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }
        $data['templates'] = $this->email_campaign_templates_model->get_all_templates_except_demo();
        $data['leads_statuses']   = $this->leads_model->get_status();
        $data['leads_sources']   = $this->leads_model->get_source();
        $data['customers_groups'] = $this->clients_model->get_groups();
        $data['mails'] = $this->staff_model->get_all_webmails(true);
        $data['custom_mails'] = $this->email_campaigns_emails_model->get_all();
        $data['email_lists'] = $this->email_campaign_mail_list_model->get_lists();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['lead_countries'] = $this->leads_model->get_lead_countries();
        $this->load->view('admin/email_campaigns/create', $data);
    }

    public function save()
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');
        if (!has_permission('email_campaigns', '', 'create')) {
            access_denied('email_campaigns');
        }

        $data = $this->input->post();
        $replyTo = null;
        if (isset($data['reply_to']) && !empty($data['reply_to'])) {
            $replyTo = implode(",", array_values(array_column(json_decode($data['reply_to']), 'value')));
        }

        if (!isset($data['mail_id']) || (isset($data['mail_id']) && empty($data['mail_id']))) {
            set_alert('danger', "Please select send from emails.");
            redirect(admin_url('email_campaigns/create'));
        }

        if (isset($data['send_to'])) {
            $_all_emails = [];
            $lists = $data['send_to'];

            $leadAssignedWhere = "";
            $customerAssignedWhere = "";

            $staff_ids = (isset($data['staff']) && !empty($data['staff'])) ? array_filter($data['staff']) : [];
            if (!empty($staff_ids)) {
                $staff_ids =  implode(",", $staff_ids);
                $leadAssignedWhere .= " assigned IN (" . $staff_ids . ")";
                $customerAssignedWhere .= " userid In (" . $staff_ids . ") ";
            } else if (!has_permission('email_campaigns', '', 'view')) {
                if (is_manager()) {
                    $leadAssignedWhere = "assigned IN (" . get_manager_assigned_staff_ids("", true) . ")";
                } else {
                    $leadAssignedWhere = "assigned = " . get_staff_user_id();
                }
                $customerAssignedWhere = " userid In (" . implode(",", get_assigned_customers_ids_by_staff()) . ") ";
            }

            $countryIds = (isset($data['countries']) && !empty($data['countries'])) ? array_filter($data['countries']) : [];
            if (!empty($countryIds)) {
                $countryIds =  implode(",", $countryIds);
                $leadAssignedWhere .= (!empty($leadAssignedWhere)) ? " AND " : "";
                $leadAssignedWhere .= "country IN (" . $countryIds . ")";
                $customerAssignedWhere .= (!empty($customerAssignedWhere)) ? " AND " : "";
                $customerAssignedWhere .= 'userid IN (select userid from ' . db_prefix() . 'clients where country IN (' . $countryIds . '))';
            }
            foreach ($lists as $key => $val) {
                if ($key == 'staff') {
                    $where = 'active = 1';
                    if ($this->input->post('staff_all')) {
                        $staff = $this->staff_model->get('', $where);
                        foreach ($staff as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['firstname'] . " " . $email['lastname'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['staffid'],
                                'rel_type'   => "staff",
                                'status' => 'queue'
                            ]);
                        }
                    } else if ($this->input->post('specific_staff') && $this->input->post('staff_ids') && !empty($this->input->post('staff_ids'))) {
                        $where .= " AND staffid IN (" . implode(",", $this->input->post('staff_ids')) . ")";
                        $staff = $this->staff_model->get('', $where);
                        foreach ($staff as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['firstname'] . " " . $email['lastname'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['staffid'],
                                'rel_type'   => "staff",
                                'status' => 'queue'
                            ]);
                        }
                    }
                } elseif ($key == 'clients') {
                    $where = 'active=1';
                    if ($this->input->post('ml_customers_all')) {
                        if (!empty($customerAssignedWhere)) {
                            $where .= ' AND ' . $customerAssignedWhere;
                        }
                        $where .= " AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                        $clients = $this->clients_model->get_contacts('', $where);
                        foreach ($clients as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['firstname'] . " " . $email['lastname'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['id'],
                                'rel_type'   => "client_contact",
                                'status' => 'queue'
                            ]);
                        }
                    } else if ($this->input->post('specific_customers') && $this->input->post('customers') && !empty($this->input->post('customers'))) {
                        $where .= " AND userid IN (" . implode(",", $this->input->post('customers')) . ")";
                        $where .= " AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                        $clients = $this->clients_model->get_contacts('', $where);
                        foreach ($clients as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['firstname'] . " " . $email['lastname'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['id'],
                                'rel_type'   => "client_contact",
                                'status' => 'queue'
                            ]);
                        }
                    } else if ($this->input->post('customer_group')) {
                        foreach ($this->input->post('customer_group') as $group_id => $val) {
                            $where = "";
                            if (!empty($customerAssignedWhere)) {
                                $where = ' AND ' . $customerAssignedWhere;
                            }
                            $where .= " AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                            $clients = $this->clients_model->get_contacts('', 'active=1 ' . $where . ' AND userid IN (select customer_id from ' . db_prefix() . 'customer_groups where groupid =' . $group_id . ')');
                            foreach ($clients as $email) {
                                array_push($_all_emails, [
                                    'hash' => app_generate_hash(),
                                    'name'  => $email['firstname'] . " " . $email['lastname'],
                                    'email'   => $email['email'],
                                    'rel_id'   => $email['id'],
                                    'rel_type'   => "client_contact",
                                    'status' => 'queue'
                                ]);
                            }
                        }
                    }
                } elseif ($key == 'leads') {
                    $this->load->model('leads_model');
                    if ($this->input->post('leads_status') || $this->input->post('leads_source')) {
                        $where = "";
                        if ($this->input->post('leads_status')) {
                            $statuses = [];
                            foreach ($this->input->post('leads_status') as $status_id => $val) {
                                array_push($statuses, $status_id);
                                $where = 'status IN (' . implode(',', $statuses) . ')';
                            }
                        }
                        if ($this->input->post('leads_source')) {
                            $sources = [];
                            foreach ($this->input->post('leads_source') as $source_id => $val) {
                                array_push($sources, $source_id);
                                $where = 'source IN (' . implode(',', $sources) . ')';
                            }
                        }
                        if (!empty($where)) {
                            $where .= " AND lost = 0 AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                            if (!empty($leadAssignedWhere)) {
                                $where .= ' AND ' . $leadAssignedWhere;
                            }
                            $leads = $this->leads_model->get('', $where);
                            foreach ($leads as $email) {
                                if (!empty($email['email'])) {
                                    array_push($_all_emails, [
                                        'hash' => app_generate_hash(),
                                        'name'  => $email['name'],
                                        'email'   => $email['email'],
                                        'rel_id'   => $email['id'],
                                        'rel_type'   => "lead",
                                        'status' => 'queue'
                                    ]);
                                }
                            }
                        }
                    } elseif ($this->input->post('leads_all')) {
                        $where = "lost = 0 AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                        if (!empty($leadAssignedWhere)) {
                            $where .= ' AND ' . $leadAssignedWhere;
                        }

                        $leads = $this->leads_model->get('', $where);
                        foreach ($leads as $email) {
                            if (!empty($email['email'])) {
                                array_push($_all_emails, [
                                    'hash' => app_generate_hash(),
                                    'name'  => $email['name'],
                                    'email'   => $email['email'],
                                    'rel_id'   => $email['id'],
                                    'rel_type'   => "lead",
                                    'status' => 'queue'
                                ]);
                            }
                        }
                    } elseif ($this->input->post('specific_leads') && $this->input->post('lead_ids') && !empty($this->input->post('lead_ids'))) {
                        $where = db_prefix() . 'leads.id IN (' . implode(",", $this->input->post('lead_ids')) . ')';
                        $where .= " AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
                        $leads = $this->leads_model->get('', $where);
                        foreach ($leads as $email) {
                            if (!empty($email['email'])) {
                                array_push($_all_emails, [
                                    'hash' => app_generate_hash(),
                                    'name'  => $email['name'],
                                    'email'   => $email['email'],
                                    'rel_id'   => $email['id'],
                                    'rel_type'   => "lead",
                                    'status' => 'queue'
                                ]);
                            }
                        }
                    }
                } elseif ($key == 'list') {
                    if ($this->input->post('all_list')) {
                        $list_items = $this->email_campaign_mail_list_model->get_list_items();
                        foreach ($list_items as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['name'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['id'],
                                'rel_type'   => "email_list",
                                'status' => 'queue'
                            ]);
                        }
                    }
                    if ($this->input->post('specific_list') && $this->input->post('email_list') && !empty($this->input->post('email_list'))) {
                        $list_items = $this->email_campaign_mail_list_model->get_list_items(array_keys($this->input->post('email_list')));
                        foreach ($list_items as $email) {
                            array_push($_all_emails, [
                                'hash' => app_generate_hash(),
                                'name'  => $email['name'],
                                'email'   => $email['email'],
                                'rel_id'   => $email['id'],
                                'rel_type'   => "email_list",
                                'status' => 'queue'
                            ]);
                        }
                    }
                }
            }
            if (!empty($_all_emails)) {
                $campaignArr = array(
                    "title" => $data['title'],
                    "start_date" => to_sql_date($data['start_date'], true),
                    "status" => "Scheduled",
                    "template_id" => $data['template_id'],
                    "max_send_limit" => $data['max_send_limit'],
                    "reply_to" => $replyTo
                );
                $campaign_id = $this->email_campaigns_model->add($campaignArr);
                if ($campaign_id) {
                    $uniqueEmails = [];
                    $filteredArray = array_filter($_all_emails, function ($item) use (&$uniqueEmails) {
                        if (in_array(strtolower($item['email']), $uniqueEmails)) {
                            return false;
                        }
                        $uniqueEmails[] = strtolower($item['email']);
                        return true;
                    });
                    $_all_emails = array_values($filteredArray);

                    $maxlimit = $data['max_send_limit'];
                    $mail_ids = $data['mail_id'];
                    $current_sender_index = 0;
                    $current_block = 0;
                    foreach ($_all_emails as $index => &$email) {
                        $current_sender_index = floor($current_block / $maxlimit) % count($mail_ids);
                        $email['mail_send_from_id'] = $mail_ids[$current_sender_index];
                        $email['send_from'] = $data['mail_send_from'];
                        $email['campaign_id'] = $campaign_id;
                        $current_block++;
                        if ($current_sender_index >= count($mail_ids)) {
                            $current_sender_index = 0;
                        }
                    }
                    $this->email_campaigns_model->queue_add($_all_emails);
                    set_alert('success', "Campaign successfully created.");
                    redirect(admin_url('email_campaigns'));
                } else {
                    set_alert('danger', "Error : Campaign not created.");
                    redirect(admin_url('email_campaigns/create'));
                }
            } else {
                set_alert('danger', "Email recipients not available");
                redirect(admin_url('email_campaigns/create'));
            }
        } else {
            set_alert('danger', "Please select email recipients.");
            redirect(admin_url('email_campaigns/create'));
        }
    }

    public function delete($id)
    {
        if (!has_permission('email_campaigns', '', 'delete')) {
            access_denied('email_campaigns');
        }
        if ($id) {
            $delete = $this->email_campaigns_model->delete_campaign($id);
            if ($delete) {
                set_alert('success', "Campaign successfully deleted.");
                redirect(admin_url('email_campaigns'));
            } else {
                set_alert('danger', "Error : something went wrong.");
                redirect(admin_url('email_campaigns'));
            }
        } else {
            set_alert('danger', "Error : Invalid campaign");
            redirect(admin_url('email_campaigns/create'));
        }
    }

    public function status_update()
    {
        if (!has_permission('email_campaigns', '', 'edit')) {
            ajax_access_denied();
        }
        $this->load->model('emails_model');
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status'])) {
            if ($data['status'] == "requeue") {
                $this->email_campaigns_model->updateQueueByCampaignId(["campaign_id" => $data['id'], "status" => "failed"], ["status" => "queue", "status_message" => "In Queue", "email_sent_at" => null, "email_open_at" => null]);
                $check = $this->email_campaigns_model->updateCampaign($data['id'], ["status" => "In Queue", "status_message" => "In Queue"]);
            } else if ($data['status'] == "stop") {
                $check = $this->email_campaigns_model->updateCampaign($data['id'], ["status" => "Stopped", "status_message" => "Stopped"]);
            } else if ($data['status'] == "resume") {
                $check = $this->email_campaigns_model->updateCampaign($data['id'], ["status" => "In Queue", "status_message" => "In Queue"]);
            }

            if ($check) {
                $result['success'] = true;
                $result['message'] = "Campaign Successfully " . $data['status'];
            } else {
                $result['success'] = false;
                $result['message'] = "Error : Campaign not " . $data['status'];
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }

    public function countEmails()
    {
        $this->load->model('emails_model');
        $data = $this->input->post();

        $staffArr = $data['staff'];
        $statusArr = $data['status'];
        $sourceArr = $data['source'];
        $customerGroupArr = $data['customer_group'];
        $countriesArr = $data['countries'];

        $result = [];
        $result['success'] = true;
        $response = array();
        $response['all_leads'] = campaign_email_count(['type' => 'all_leads', 'staff_ids' => $staffArr, 'countries' => $countriesArr]);
        $response['all_customer'] = campaign_email_count(['type' => 'all_customer', 'staff_ids' => $staffArr, 'countries' => $countriesArr]);
        if (!empty($sourceArr)) {
            $response['source'] = array();
            foreach ($sourceArr as $key => $id) {
                $count = campaign_email_count(['type' => 'leads_source', 'source_id' => $id, 'staff_ids' => $staffArr, 'countries' => $countriesArr]);
                $response['source'][$key] = array("id" => $id, "count" => $count);
            }
        }
        if (!empty($statusArr)) {
            foreach ($statusArr as $key => $id) {
                $count = campaign_email_count(['type' => 'leads_status', 'status_id' => $id, 'staff_ids' => $staffArr, 'countries' => $countriesArr]);
                $response['status'][$key] = array("id" => $id, "count" => $count);
            }
        }
        if (!empty($customerGroupArr)) {
            foreach ($customerGroupArr as $key => $id) {
                $count = campaign_email_count(['type' => 'customer_group', 'group_id' => $id, 'staff_ids' => $staffArr, 'countries' => $countriesArr]);
                $response['customer_group'][$key] = array("id" => $id, "count" => $count);
            }
        }
        $result['data'] = $response;
        echo json_encode($result);
    }

    public function get_relation_data()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $result = $this->email_campaigns_model->search_relation_data($post_data);
            echo json_encode($result);
            die;
        }
    }
}
