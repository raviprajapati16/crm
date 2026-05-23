<?php

class Leadsnew extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leadsnew_model');
        $this->load->model('leads_model');
    }

    public function index($id = '')
    {
        if (!has_permission('leads', '', 'view') && !has_permission('leads', '', 'view_own')) {
            access_denied('leads');
        }
        $this->load->model('Leadsnew_model');
        $data['module_type'] = "leads";
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['id'] = get_staff_user_id();
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $data['summary'] = get_leads_summary();
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();
        $data['lead_countries'] = $this->leads_model->get_lead_countries();
        $data['lead_states'] = $this->leads_model->get_lead_states();
        $data['lead_cities'] = $this->leads_model->get_lead_cities();
        $data['products'] = get_tags_unique();
        $data['all_countries'] = $this->leads_model->get_all_countries();

        $data['title'] = _l('leads');
        // in case accesed the url leads/index/ directly with id - used in search
        $data['leadid'] = $id;
        $this->load->view('admin/leadsnew/manage_leadsnew', $data);
    }

    public function get_lead()
    {
        $post_data = $this->input->post();
        $leads = $this->Leadsnew_model->get_leads($post_data);
        $totalLeadsCount = $this->Leadsnew_model->get_leads($post_data, true);

        // Sort the leads array by the lapselead column in descending order
        // usort($leads, function ($a, $b) {
        //     return $b->lapselead <=> $a->lapselead;
        // });

        foreach ($leads as &$lead) {
            if ($lead->assigned != 0) {
                $id = $lead->assigned;
                $full_name = $lead->assigned_firstname . ' ' . $lead->assigned_lastname;
                $lead->assigned_output = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $lead->assigned) . '">' . staff_profile_image($lead->assigned, ['staff-profile-image-small']) . '</a>';
                // For exporting
                $lead->assigned_output .= '<span class="hide">' . $full_name . '</span>';
                $lead->assigned_output .= '<span class="hide">' . $id . '</span>';
                // Set row class based on the value of $lead->lapselead
            } else {
                $lead->assigned_output = ''; // Or whatever default value you want to set
            }

            if ($lead->short_name) {
                $lead->country_name = $lead->short_name;
            } else {
                $lead->country_name = '';
            }
            if ($lead->dateadded) {
                $lead->dateadded = '<span data-toggle="tooltip" data-title="' . _dt($lead->dateadded) . '" class="text-has-action is-date">' . time_ago($lead->dateadded) . '</span>';
            } else {
                $lead->dateadded = '';
            }
            if ($lead->lastcontact) {
                $lead->lastcontact  = '<span data-toggle="tooltip" data-title="' . _dt($lead->lastcontact) . '" class="text-has-action is-date">' . time_ago($lead->lastcontact) . '</span>';
            } else {
                $lead->lastcontact = '';
            }

            $locked = false;
            $lockAfterConvert = get_option('lead_lock_after_convert_to_customer');

            if ($lead->is_converted > 0) {
                $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
            }

            // Pass the locked status to the view
            $lead->edit_locked = $locked;
        }
        $response = array(
            "draw" => $post_data['draw'],
            "recordsTotal" => $totalLeadsCount,
            "recordsFiltered" => $totalLeadsCount,
            "data" => $leads
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function export_all_csv()
    {
        $this->load->helper('download');
        $leads = $this->Leadsnew_model->get_all_leads();
        if (!empty($leads)) {
            $headers = array_keys($leads[0]);
            $csv_data = implode(',', $headers) . "\n";
            foreach ($leads as $row) {
                $csv_row = array();
                foreach ($row as $key => $value) {
                    // If the value contains a comma, wrap it in double quotes
                    if (strpos($value, ',') !== false) {
                        $value = '"' . $value . '"';
                    }
                    $csv_row[] = $value;
                }
                $csv_data .= implode(',', $csv_row) . "\n";
            }
            //Add date time to file name
            $file_name = 'AllLeadsExport_' . date('d_m_Y-H-i') . '.csv';
            force_download($file_name, $csv_data);
        }
    }
}
