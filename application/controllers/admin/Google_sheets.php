<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Google_sheets extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_sheets_model');
        $this->load->model('leads_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('google_sheets');
        }
        $this->load->view('admin/google_sheets/manage_sheets');
    }

    public function create()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();

            $required_fields = ['sheet_title', 'sheet_url', 'status', 'source', 'assignee'];
            foreach ($required_fields as $field) {
                if (empty($post_data[$field])) {
                    set_alert('danger', 'All required fields must be filled');
                    redirect(admin_url('google_sheets/create'));
                    return;
                }
            }

            if (empty($post_data['map']['name']) || (empty($post_data['map']['email']) && empty($post_data['map']['phone_no']))) {
                set_alert('danger', 'Name field and either Email or Phone field must be mapped');
                redirect(admin_url('google_sheets/create'));
                return;
            }

            $sheet_data = [
                'sheet_title' => $this->input->post('sheet_title'),
                'sheet_url' => $this->input->post('sheet_url'),
                'status' => $this->input->post('status'),
                'source' => $this->input->post('source'),
                'assignee' => $this->input->post('assignee'),
                'column_mapping' => json_encode($this->input->post('map')),
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sheet_id = $this->google_sheets_model->add_sheet($sheet_data);

            if ($sheet_id) {
                $url = $this->input->post('sheet_url');
                $all_columns = json_decode($this->input->post('all_columns'), true);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);

                if ($response) {
                    $lines = explode("\n", $response);

                    for ($i = 1; $i < count($lines); $i++) {
                        $row = str_getcsv($lines[$i]);

                        if (empty($row) || count($row) != count($all_columns)) {
                            continue;
                        }

                        $row_data = [];
                        foreach ($all_columns as $index => $column_name) {
                            if (isset($row[$index])) {
                                $row_data[$column_name] = $row[$index];
                            }
                        }

                        if (empty($row_data)) {
                            continue;
                        }

                        $sheet_record = [
                            'sheet_id' => $sheet_id,
                            'sheet_record_id' => $row_data['id'],
                            'record_data' => json_encode($row_data),
                            'is_imported' => 0,
                            'lead_id' => null
                        ];

                        $this->google_sheets_model->add_sheet_record($sheet_record);
                    }

                    set_alert('success', 'Google Sheet added successfully with ' . (count($lines) - 1) . ' records');
                } else {
                    set_alert('warning', 'Google Sheet added but failed to fetch data');
                }

                redirect(admin_url('google_sheets/index'));
            } else {
                set_alert('danger', 'Failed to add Google Sheet');
                redirect(admin_url('google_sheets/create'));
            }
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('admin/google_sheets/create', $data);
    }

    public function edit($id)
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $required_fields = ['sheet_title', 'status', 'source', 'assignee'];
            foreach ($required_fields as $field) {
                if (empty($post_data[$field])) {
                    set_alert('danger', 'All required fields must be filled');
                    redirect(admin_url('google_sheets/edit/' . $id));
                    return;
                }
            }
            $update_data = [
                'sheet_title' => $this->input->post('sheet_title'),
                'status' => $this->input->post('status'),
                'source' => $this->input->post('source'),
                'assignee' => $this->input->post('assignee')
            ];
            $success = $this->google_sheets_model->update_sheet($id, $update_data);
            if ($success) {
                log_activity('New Google Sheet Updated [ID: ' . $id . ', Title: ' . $this->input->post('sheet_title') . ']');
                set_alert('success', _l('google_sheet_updated'));
                redirect(admin_url('google_sheets/index'));
            } else {
                set_alert('danger', _l('failed_to_update_google_sheet'));
                redirect(admin_url('google_sheets/edit/' . $id));
            }
        }

        $data['sheet'] = $this->google_sheets_model->get_sheets($id);

        if (!$data['sheet']) {
            set_alert('warning', _l('google_sheet_not_found'));
            redirect(admin_url('google_sheets'));
        }

        $data['mapping'] = json_decode($data['sheet']->column_mapping, true);

        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);

        $data['columns'] = [];
        if (!empty($data['sheet']->sheet_url)) {
            $sample_record = $this->google_sheets_model->get_sample_record($id);
            if ($sample_record) {
                $record_data = json_decode($sample_record->record_data, true);
                $data['columns'] = array_keys($record_data);
            }
        }
        $data['title'] = "Edit Google Sheet";
        $this->load->view('admin/google_sheets/edit', $data);
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('google_sheets'));
        }

        $sheet = $this->google_sheets_model->get_sheets($id);

        if (!$sheet) {
            set_alert('warning', _l('google_sheet_not_found'));
            redirect(admin_url('google_sheets'));
        }

        $success = $this->google_sheets_model->delete_sheet_records($id);

        $success = $this->google_sheets_model->delete_sheet($id) && $success;

        if ($success) {
            set_alert('success', "Google sheet successfully deleted.");
            log_activity('Google Sheet Deleted [ID: ' . $id . ', Title: ' . $sheet->sheet_title . ']');
        } else {
            set_alert('danger', "Error : Google sheet not deleted.");
        }

        redirect(admin_url('google_sheets'));
    }

    public function fecth_sheet()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $url = $this->input->post('url');

        if (empty($url)) {
            echo json_encode(['success' => false, 'message' => 'URL is required']);
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) || !strpos($url, 'docs.google.com/spreadsheets') || !strpos($url, 'pub?output=csv')) {
            echo json_encode(['success' => false, 'message' => 'Invalid Google Sheet URL']);
            return;
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch) || $httpCode != 200) {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch data from Google Sheet: ' . curl_error($ch)]);
                curl_close($ch);
                return;
            }

            curl_close($ch);

            $lines = explode("\n", $response);
            if (count($lines) == 0) {
                echo json_encode(['success' => false, 'message' => 'No data found in the sheet']);
                return;
            }

            $headerRow = str_getcsv($lines[0]);

            if (count($headerRow) == 0 || empty($headerRow[0])) {
                echo json_encode(['success' => false, 'message' => 'No columns found in the sheet']);
                return;
            }

            echo json_encode(['success' => true, 'columns' => $headerRow]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function view_sheets_records($id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('google_sheets_records',['sheet_id' => $id]);
        }
        $data['sheet'] = $this->google_sheets_model->get_sheets($id);
        $this->load->view('admin/google_sheets/view_sheets_records',$data);
    }
}
