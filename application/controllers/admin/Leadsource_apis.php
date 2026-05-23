<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Leadsource_apis extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('indiamartnew_model');
        $this->load->model('Leadsnew_model');
        $this->load->model('leads_model');
        $this->load->model('TradeIndia_model');
    }

    public function settings()
    {
        $data['indiamart'] = $this->indiamartnew_model->get_settings();
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $type = $post_data['type'];
            if (isset($post_data['type'])) {
                unset($post_data['type']);
            }
            if ($type == "indiamart") {
                if ($this->indiamartnew_model->save_settings()) {
                    set_alert('success', "IndiaMart API setting successfully updated");
                } else {
                    set_alert('danger', "Error : IndiaMart API setting not updated");
                }
            }
            if ($type == "tradeindia") {
                if ($this->TradeIndia_model->update_setting()) {
                    set_alert('success', "TradeIndia API setting successfully updated");
                } else {
                    set_alert('danger', "Error : TradeIndia API API setting not updated");
                }
            }
            redirect(admin_url("leadsource_apis/settings"));
        }
        $this->load->view('admin/lead_sources_apis/settings', $data);
    }

    public function fetch_leads()
    {
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        $this->load->view('admin/lead_sources_apis/fetch_leads', $data);
    }

    public function get_leads()
    {
        $post = $this->input->post(null, TRUE);
        $results['status'] = false;

        $result_arr = [];
        if ($post['api_type'] == "india-mart") {
            $response = $this->indiamartnew_model->indiamart_curl_request($post);
            $response = json_decode($response['result'], true);
            if (isset($response['STATUS']) && $response['STATUS'] == "FAILURE") {
                $results['message'] = $response['MESSAGE'];
                $results['status'] = false;
                echo json_encode($results);
                exit;
            } else {
                $result_arr = $response['RESPONSE'];
            }
        } else {
            $response = $this->TradeIndia_model->tradeindia_curl_request($post);
            if (isset($response['result']) && is_string($response['result'])) {
                $decoded_result = json_decode($response['result'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result_arr = $decoded_result;
                } else {
                    $results['message'] = $response['result'];
                    $results['status'] = false;
                    echo json_encode($results);
                    exit;
                }
            }
        }

        if (!empty($result_arr)) {
            $results['status'] = true;
            $table = ['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []];
            $table_rows = '';
            foreach ($result_arr as $key => $record) {
                if ($post['api_type'] == "india-mart") {
                    $query_id = $record['UNIQUE_QUERY_ID'];
                    $lead_data = $record;
                    $leadRes = $this->indiamartnew_model->add_lead($query_id, $lead_data);
                    $id = $leadRes['id'];
                    $lead_data['lead_id'] = $id;
                    $leadRow = false;
                    if ($leadRes['exists']) {
                        $leadRow = $leadRes['exists'];
                    }
                    $table_rows .= $this->getRowHTMLIndiaMart($lead_data, $leadRow);
                } else {
                    $query_id = $record['rfi_id'];
                    $lead_data = $record;
                    $leadRes = $this->TradeIndia_model->add_lead($query_id, $lead_data);
                    $id = $leadRes['id'];
                    $lead_data['lead_id'] = $id;
                    $leadRow = false;
                    if ($leadRes['exists']) {
                        $leadRow = $leadRes['exists'];
                    }
                    $table_rows .= $this->getRowHTMLTradeIndia($lead_data, $leadRow);
                }
            }
            $results['table_rows'] = $table_rows;
        }
        echo json_encode($results);
    }

    public function import_leads()
    {
        $post = $this->input->post(null, TRUE);
        $response = ['status' => FALSE, 'message' => 'Something Went Wrong,Please try again!', 'message_type' => 'danger'];
        $leads = $post['lead_ids'];
        $status = $post['status'];
        $source = $post['source'];
        if (is_array($leads) && !empty($leads)) {
            $imported_leads = [];
            foreach ($leads as $l_k => $lead_id) {
                if ($post['api_type_result'] == "india-mart") {
                    $leadRow = $this->indiamartnew_model->get_lead($lead_id);
                    if ($leadRow) {
                        $leadData = json_decode($leadRow->lead_data, true);
                        $qproduct = str_replace('Requirement for ', '', $leadData['SUBJECT']);
                        $prod = $this->leads_model->getleadproducts($qproduct);

                        if ($prod > 0) {
                            $assigned =  $this->leads_model->getstaffassignment_byproduct($prod);
                        } else {
                            $assigned = get_staff_user_id();
                        }

                        $phone = $leadData['SENDER_MOBILE'];
                        if (!empty($leadData['SENDER_COUNTRY_ISO'])) {
                            $this->db->where('iso2', $leadData['SENDER_COUNTRY_ISO']);
                            $this->db->or_where('short_name', $leadData['SENDER_COUNTRY_ISO']);
                            $this->db->or_where('long_name', $leadData['SENDER_COUNTRY_ISO']);
                            $country = $this->db->get(db_prefix() . 'countries')->row();
                            if ($country) {
                                $phone = "+" . convert_phonenumer_by_country($phone, $country->iso2);
                            }
                        }

                        $insert_data = [
                            'name' => isset($leadData['SENDER_NAME']) ? $leadData['SENDER_NAME'] : '',
                            'source' => $source,
                            'status' => $status,
                            'email' => isset($leadData['SENDER_EMAIL']) ? $leadData['SENDER_EMAIL'] : '',
                            'phonenumber' => $phone,
                            'title' => '',
                            'tags' => $leadData['SUBJECT'],
                            'company' => isset($leadData['SENDER_COMPANY']) ? $leadData['SENDER_COMPANY'] : '',
                            'website' => '',
                            'address' => isset($leadData['SENDER_ADDRESS']) ? $leadData['SENDER_ADDRESS'] : '',
                            'city' => isset($leadData['SENDER_CITY']) ? $leadData['SENDER_CITY'] : '',
                            'zip' => isset($leadData['SENDER_PINCODE']) ? $leadData['SENDER_PINCODE'] : '',
                            'state' => isset($leadData['SENDER_STATE']) ? $leadData['SENDER_STATE'] : '',
                            'country' => isset($leadData['SENDER_COUNTRY_ISO']) ? get_country_id_by_iso2($leadData['SENDER_COUNTRY_ISO']) : null,
                            'description' => $leadData['QUERY_MESSAGE'],
                            'assigned' => $assigned
                            /*'custom_contact_date' => $this->Api_model->value($this->input->post('custom_contact_date', TRUE)),
                            'is_public' => $this->Api_model->value($this->input->post('is_public', TRUE)),*/
                        ];
                        $output = $this->leads_model->add($insert_data);
                        if ($output) {
                            $this->indiamartnew_model->update_lead($lead_id, ['is_imported' => 1]);
                            $imported_leads[] = $lead_id;
                        }
                    }
                } else {
                    $leadRow = $this->TradeIndia_model->get_lead($lead_id);
                    if ($leadRow) {
                        $leadData = json_decode($leadRow->lead_data, true);
                        $lead_id = $leadRow->id;
                        $mobileNumbers = isset($leadData['sender_other_mobiles']) ? $leadData['sender_other_mobiles'] : '';
                        $mobileNumbersArray = !empty($mobileNumbers) ? explode(', ', $mobileNumbers) : [];
                        $emaildata = isset($leadData['sender_email']) ? $leadData['sender_email'] : '';
                        $emaildataArray = !empty($emaildata) ? explode(', ', $emaildata) : [];

                        $qproduct = str_replace('New Inquiry for ', '', $leadData['subject']);
                        $qproduct = substr($qproduct, 0, strpos($qproduct, 'from'));

                        $products = $this->leads_model->getleadproducts($qproduct);

                        if (!empty($products)) {
                            $assigned = $this->leads_model->getstaffassignment_byproduct($products);
                        } else {
                            // If no products found, you can assign a default staff ID like "1".
                            $assigned = "1";
                        }
                        if (!empty($leadData['sender_country'])) {
                            $this->db->where('iso2', $leadData['sender_country']);
                            $this->db->or_where('short_name', $leadData['sender_country']);
                            $this->db->or_where('long_name', $leadData['sender_country']);
                            $country = $this->db->get(db_prefix() . 'countries')->row();
                            if ($country) {
                                $value = $country->country_id;
                                if (!empty($mobileNumbersArray)) {
                                    foreach ($mobileNumbersArray as $key => $mobno) {
                                        $mobileNumbersArray[$key] = "+" . convert_phonenumer_by_country($mobno, $country->iso2);
                                    }
                                }
                            } else {
                                $value = 0;
                            }
                        }
                        $insert_data = [
                            'name' => isset($leadData['sender_name']) ? $leadData['sender_name'] : '',
                            'source' => $source,
                            'status' => $status, //New Lead
                            'email' => implode(',', $emaildataArray),
                            'phonenumber' => implode(',', $mobileNumbersArray),
                            'title' => '',
                            'tags' => $qproduct,
                            'company' => isset($leadData['sender_co']) ? $leadData['sender_co'] : '',
                            'website' => '',
                            'address' => isset($leadData['address']) ? $leadData['address'] : '',
                            'city' => isset($leadData['sender_city']) ? $leadData['sender_city'] : '',
                            'zip' => '',
                            'state' => isset($leadData['sender_state']) ? $leadData['sender_state'] : '',
                            'description' => $leadData['message'],
                            'country' => $country->country_id ?? '',
                            'assigned' => $assigned,
                            /*'custom_contact_date' => $this->Api_model->value($this->input->post('custom_contact_date', TRUE)),
                            'is_public' => $this->Api_model->value($this->input->post('is_public', TRUE)),*/
                        ];
                        $output = $this->leads_model->add($insert_data);

                        if ($output) {
                            $this->TradeIndia_model->update_lead($lead_id, ['is_imported' => 1]);
                            $imported_leads[] = $lead_id;
                        }
                    }
                }
            } /* Leads Loop */
            $response['status'] = TRUE;
            $response['imported_leads'] = $imported_leads;
            $response['message'] = count($imported_leads) . " Lead(s) Imported Successfully";
            $response['message_type'] = "success";
        }
        echo json_encode($response);
    }

    public function leads_history()
    {
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources']  = $this->leads_model->get_source();
        if ($this->input->is_ajax_request()) {
            $post = $this->input->post();
            if ($post['api_type'] == "india-mart") {
                $this->app->get_table_data('leads_history_indiamart');
            } else {
                $this->app->get_table_data('leads_history_tradeindia');
            }
        }
        $this->load->view('admin/lead_sources_apis/leads_history', $data);
    }

    private function checkDates($start_date, $end_date)
    {
        $response = ['status' => TRUE, 'message' => ''];
        if ($start_date != '' && $end_date != '') {
            if ($end_date < $start_date) {
                $response['status'] = FALSE;
                $response['message'] = "Invalid End Date";
            }
            $response['start_date'] = date('Y-m-d', strtotime($start_date));
            $response['end_date'] = date('Y-m-d', strtotime($end_date));
            $response['has_dates'] = TRUE;
        } else {
            if ($start_date != '' || $end_date != '') {
                $response['status'] = FALSE;
                $response['message'] = "Please Provide Both Dates";
            }
        }
        return $response;
    }

    private function getRowHTMLIndiaMart($data, $lead_row)
    {
        $imported_span = '';
        $checkbox = "<input type='checkbox' name='lead_ids[]' class='import_id' value='{$data['lead_id']}'>";
        $row_html = '';
        $row_html .= "<tr id='lead_{$data['lead_id']}'>";
        $row_html .= "<td>{$checkbox}</td>";
        $row_html .= "<td>{$data['SENDER_NAME']} {$imported_span}</td>";
        $row_html .= "<td>{$data['SENDER_EMAIL']}</td>";
        $row_html .= "<td>{$data['SENDER_MOBILE']}</td>";
        $row_html .= "<td>{$data['SUBJECT']}</td>";
        $row_html .= "<td>{$data['QUERY_MESSAGE']}</td>";
        if ($lead_row) {
            $row_html .= "<td>" . ($lead_row->is_imported == 1 ? "<span class='label label-success'>Imported</span>" : "<span class='label label-danger'>Not Imported</span>") . "</td>";
        } else {
            $row_html .= "<td><span class='label label-danger'>Not Imported</span></td>";
        }
        $row_html .= "</tr>";
        return $row_html;
    }

    private function getRowHTMLTradeIndia($data, $lead_row)
    {
        $imported_span = '';
        $checkbox = "<input type='checkbox' name='lead_ids[]' class='import_id' value='{$data['lead_id']}'>";
        $row_html = '';
        $row_html .= "<tr id='lead_{$data['lead_id']}'>";
        $row_html .= "<td>{$checkbox}</td>";
        $row_html .= "<td>{$data['sender_name']} {$imported_span}</td>";
        $row_html .= "<td>{$data['sender_email']}</td>";
        $row_html .= "<td>{$data['sender_mobile']}</td>";
        $row_html .= "<td>{$data['subject']}</td>";
        $row_html .= "<td>{$data['message']}</td>";
        if ($lead_row) {
            $row_html .= "<td>" . ($lead_row->is_imported == 1 ? "<span class='label label-success'>Imported</span>" : "<span class='label label-danger'>Not Imported</span>") . "</td>";
        } else {
            $row_html .= "<td><span class='label label-danger'>Not Imported</span></td>";
        }
        $row_html .= "</tr>";
        return $row_html;
    }
}
