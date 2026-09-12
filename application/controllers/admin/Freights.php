<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Freights extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('freights_model');
        $this->load->model('freight_master_model');
    }

    /* List all freights */
    public function index()
    {
        if (!has_permission('freights', '', 'view') && !has_permission('freights', '', 'view_own')) {
            access_denied('Freights');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('freights');
        }

        $data['title'] = 'Freight Master';
        $this->load->view('admin/freights/manage', $data);
    }

    /* Add or update freight */
    public function freight($id = '')
    {
        if ($id == '') {
            if (!has_permission('freights', '', 'create')) {
                access_denied('Freights');
            }
        } else {
            if (!has_permission('freights', '', 'edit')) {
                access_denied('Freights');
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                $id = $this->freights_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', 'Freight Record'));
                    redirect(admin_url('freights'));
                }
            } else {
                $success = $this->freights_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', 'Freight Record'));
                }
                redirect(admin_url('freights'));
            }
        }

        if ($id == '') {
            $data['title'] = 'Add New Freight';
        } else {
            $data['freight'] = $this->freights_model->get($id);
            $data['activity_log'] = $this->freights_model->get_activity_log($id);
            $data['title']   = 'Edit Freight';
        }

        $data['countries'] = get_all_countries();
        $data['groups'] = $this->freight_master_model->get_groups();

        $this->load->view('admin/freights/freight', $data);
    }

    /* Delete freight */
    public function delete($id)
    {
        if (!has_permission('freights', '', 'delete')) {
            access_denied('Freights');
        }
        if (!$id) {
            redirect(admin_url('freights'));
        }
        $response = $this->freights_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', 'Freight Record'));
        } else {
            set_alert('warning', _l('problem_deleting', 'Freight Record'));
        }
        redirect(admin_url('freights'));
    }

    /* Get sizes by group ID (AJAX) */
    public function get_sizes_by_group($group_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->where('group_id', $group_id);
            $this->db->order_by('name', 'asc');
            $sizes = $this->db->get(db_prefix() . 'freight_sizes')->result_array();
            echo json_encode($sizes);
        }
    }

    /* Get freight details (AJAX) */
    public function get_freight_details($id)
    {
        if ($this->input->is_ajax_request()) {
            $freight = $this->freights_model->get($id);
            if (!$freight) {
                echo 'Freight record not found.';
                return;
            }

            $from_country_name = get_country_short_name($freight->from_country);
            $to_country_name = get_country_short_name($freight->to_country);

            $html = '<table class="table table-striped">';
            $html .= '<tbody>';

            // FROM and TO headings
            $html .= '<tr>';
            $html .= '<td width="50%"><strong>FROM</strong></td>';
            $html .= '<td width="50%"><strong>TO</strong></td>';
            $html .= '</tr>';

            // Country
            $html .= '<tr>';
            $html .= '<td>Country: ' . $from_country_name . '</td>';
            $html .= '<td>Country: ' . $to_country_name . '</td>';
            $html .= '</tr>';

            // State
            $html .= '<tr>';
            $html .= '<td>State: ' . $freight->from_state . '</td>';
            $html .= '<td>State: ' . $freight->to_state . '</td>';
            $html .= '</tr>';

            // City/Port
            $html .= '<tr>';
            $html .= '<td>City/Port: ' . $freight->from_city . '</td>';
            $html .= '<td>City/Port: ' . $freight->to_city . '</td>';
            $html .= '</tr>';

            // Pin Code
            $html .= '<tr>';
            $html .= '<td>Pin Code: ' . $freight->from_pin . '</td>';
            $html .= '<td>Pin Code: ' . $freight->to_pin . '</td>';
            $html .= '</tr>';

            // Details heading
            $html .= '<tr>';
            $html .= '<td colspan="2"><strong>DETAILS</strong></td>';
            $html .= '</tr>';

            $html .= '<tr><td>Truck/Container</td><td>' . $freight->group_name . '</td></tr>';
            $html .= '<tr><td>Size</td><td>' . $freight->size_name . '</td></tr>';
            $html .= '<tr><td>Carrier</td><td>' . $freight->carrier . '</td></tr>';
            $html .= '<tr><td>Freight Cost</td><td>' . $freight->freight_cost . '</td></tr>';
            $html .= '<tr><td>Transit Time</td><td>' . $freight->transit_time . '</td></tr>';
            $html .= '<tr><td>Notes</td><td>' . nl2br($freight->notes) . '</td></tr>';

            $html .= '</tbody>';
            $html .= '</table>';

            echo $html;
        }
    }
}
