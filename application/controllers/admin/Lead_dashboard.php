<?php

class Lead_dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leadsnew_model');
        $this->load->model('leads_model');
        $this->load->model('staff_model');
        $this->load->model('leadsdashboard_model');
    }

    public function index()
    {
        if (!has_permission('lead_dashboard', '', 'view') && !has_permission('lead_dashboard', '', 'view_own')) {
            access_denied('leads dashboard');
        }
        $data = [];
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('admin/leads_dashboard/dashboard', $data);
    }

    public function get_chart_data()
    {
        $data = $this->input->post();
        $dateRange = explode(" - ", $data['daterange']);
        $data['startdate'] = to_sql_date($dateRange[0]);
        $data['enddate'] = to_sql_date($dateRange[1]);
        $chart_data = [];

        $colorsArr = chartColors();

        if (empty($data['assignee'])) {
            if (has_permission('lead_dashboard', '', 'view_own') && manager_employee_data_access_permission_check("leads_dashboard")) {
                $data['assignee'] = "(" . implode(",", get_manager_assigned_staff_ids()) . ")";
            }
        } else {
            $data['assignee'] = "(" . $data['assignee'] . ")";
        }

        // // Today leads follow up
        // $todayFollowupData = $this->leadsdashboard_model->get_today_lead_followup_data($data);
        // if (!empty($todayFollowupData)) {
        //     $chart_data['today_lead_followup_data'] = [
        //         'labels' => array_column($todayFollowupData, 'assignee_name'),
        //         'datasets' => [
        //             array("name" => "Pending", "color" => "#4687db", "data" => array_column($todayFollowupData, 'pending_count')),
        //             array("name" => "Followup", "color" => "#5eeb8a", "data" => array_column($todayFollowupData, 'attend_count')),
        //         ]
        //     ];
        // }

        // // Today Leads data - views / update
        // $todayReceivedData = $this->leadsdashboard_model->get_today_lead_data($data);
        // if (!empty($todayReceivedData)) {
        //     $chart_data['today_lead_data'] = [
        //         'labels' => array_column($todayReceivedData, 'assignee_name'),
        //         'datasets' => [
        //             array("name" => ($data['todayLeadsType'] == "leads-view") ? "Viewes" : "Updated", "color" => "#4687db", "data" => array_column($todayReceivedData, 'leads')),
        //         ]
        //     ];
        // }

        // Lead Attend Data
        $leadAttendData = $this->leadsdashboard_model->get_lead_attend_data($data);
        if (!empty($leadAttendData)) {
            $averageTimes = [];
            $groupedData = [];
            foreach ($leadAttendData as $entry) {
                $assignee = $entry['assignee_name'];
                if (!isset($groupedData[$assignee])) {
                    $groupedData[$assignee] = [
                        'total_time' => 0,
                        'count' => 0
                    ];
                }
                $groupedData[$assignee]['total_time'] += $entry['attend_time'];
                $groupedData[$assignee]['count']++;
            }
            foreach ($groupedData as $assignee => $stats) {
                $averageTime = round($stats['total_time'] / $stats['count']);
                $averageTimes[] = array("assignee_name" => $assignee, "total_leads" => $stats['count'], "avg_time" => $averageTime);
            }
            $chart_data['lead_attend_data'] = [
                'labels' => array_column($averageTimes, 'assignee_name'),
                'total_leads' => array_column($averageTimes, 'total_leads'),
                'datasets' => [
                    array("name" => "Avg. Time", "color" => "#4687db", "data" => array_column($averageTimes, 'avg_time')),
                ]
            ];
        }

        // Lead followup duration Data
        $leadFollowupDuration = $this->leadsdashboard_model->get_lead_followup_duration_data($data);
        if (!empty($leadFollowupDuration)) {
            $chart_data['lead_followup_duration_data'] = [
                'labels' => array_column($leadFollowupDuration, 'assignee_name'),
                'datasets' => [
                    array("name" => "Call Duration", "color" => "#2fbd08", "data" => array_column($leadFollowupDuration, 'call_duration')),
                    array("name" => "Online Meetings Duration", "color" => "#4687db", "data" => array_column($leadFollowupDuration, 'meeting_duration')),
                ]
            ];
        }

        $chart_data['leads_summary'] = get_leads_summary($data);

        // Lapsed Lead Data
        $lapsedLeadData = $this->leadsdashboard_model->get_lapslead_data($data);
        if (!empty($lapsedLeadData)) {
            $chart_data['lapslead_data'] = [
                'labels' => array_column($lapsedLeadData, 'assignee_name'),
                'datasets' => [
                    array("name" => "Lapsed Leads", "color" => "#eb4034", "data" => array_column($lapsedLeadData, 'count')),
                ]
            ];
        }

        //leads received data
        $rawData = $this->leadsdashboard_model->get_received_leads_data($data);
        if (!empty($rawData)) {
            $aggregatedData = [];
            foreach ($rawData as $entry) {
                $date = $entry['date'];
                $source = $entry['source_name'];
                $leads = $entry['leads'];
                if (!isset($aggregatedData[$date])) {
                    $aggregatedData[$date] = [];
                }
                if (!isset($aggregatedData[$date][$source])) {
                    $aggregatedData[$date][$source] = 0;
                }
                $aggregatedData[$date][$source] += $leads;
            }

            $dates = array_keys($aggregatedData);
            $sources = [];
            $dataset = [];

            foreach ($aggregatedData as $sourceData) {
                foreach ($sourceData as $source => $leads) {
                    if (!in_array($source, $sources)) {
                        $sources[] = $source;
                    }
                }
            }

            $colorIndex = 0;
            foreach ($sources as $source) {
                $dataPoints = [];
                foreach ($dates as $date) {
                    $dataPoints[] = isset($aggregatedData[$date][$source]) ? $aggregatedData[$date][$source] : 0;
                }
                $dataset[] = [
                    'name' => $source,
                    'data' => $dataPoints,
                    'color' => $colorsArr[$colorIndex]
                ];
                $colorIndex = ($colorIndex + 1) % count($colorsArr);
            }

            $chart_data['received_lead_data'] = [
                'labels' => $dates,
                'datasets' => $dataset
            ];
        }

        //Leads view data
        $rawData = $this->leadsdashboard_model->get_leads_view_data($data);
        if (!empty($rawData)) {
            $chart_data['lead_view_data']['dates'] = array_column($rawData, 'date');
            $chart_data['lead_view_data']['leads'] = array_column($rawData, 'leads');
        }

        //leads email send data
        $rawData = $this->leadsdashboard_model->get_leads_send_data($data);
        if (!empty($rawData)) {
            if ($data['leadSendType'] == "email") {
                $chart_data['lead_send_data'] = [
                    'labels' => array_column($rawData, 'date'),
                    'datasets' => [
                        array("name" => "Emails Opened", "color" => "#5eeb8a", "data" => array_column($rawData, 'emails_opened')),
                        array("name" => "Emails Not open", "color" => "#4687db", "data" => array_column($rawData, 'emails_sent')),
                    ]
                ];
            } else {
                $chart_data['lead_send_data'] = [
                    'labels' => array_column($rawData, 'date'),
                    'datasets' => [
                        array("name" => "Whatsapp Share", "color" => "#5eeb8a", "data" => array_column($rawData, 'whatsapp_shared')),
                    ]
                ];
            }
        }

        //Leads allocation data
        $rawData = $this->leadsdashboard_model->get_assignee_data($data);
        if (!empty($rawData)) {
            $chart_data['lead_allocation_data'] = [
                'labels' => array_column($rawData, 'assignee_name'),
                'series' => array_map('intval', array_column($rawData, 'leads')),
            ];
        }

        //Leads customer conversion data
        $rawData = $this->leadsdashboard_model->get_customer_conversion_data($data);
        if (!empty($rawData)) {
            $chart_data['cutomer_conversion_data']['dates'] = array_column($rawData, 'date');
            $chart_data['cutomer_conversion_data']['customers'] = array_column($rawData, 'clients');
        }

        //Leads vendor conversion data
        $rawData = $this->leadsdashboard_model->get_vendor_conversion_data($data);
        if (!empty($rawData)) {
            $chart_data['vendor_conversion_data']['dates'] = array_column($rawData, 'date');
            $chart_data['vendor_conversion_data']['vendors'] = array_column($rawData, 'vendors');
        }

        // Leads transfer Data
        if (isset($data['assignee']) && !empty($data['assignee'])) {
            $rawData = $this->leadsdashboard_model->get_leads_transfer_to_other_data($data);
            if (!empty($rawData)) {
                $chart_data['lead_transfer_to_other_data'] = [
                    'labels' => array_column($rawData, 'name'),
                    'series' => array_map('intval', array_column($rawData, 'leads')),
                ];
            }

            $rawData = $this->leadsdashboard_model->get_leads_transfer_to_self_data($data);
            if (!empty($rawData)) {
                $chart_data['lead_transfer_to_self_data'] = [
                    'labels' => array_column($rawData, 'name'),
                    'series' => array_map('intval', array_column($rawData, 'leads')),
                ];
            }
        }

        //leads Inquiry form data
        $rawData = [];
        if ($data['formType'] == "lead-inquiry-form") {
            $rawData = $this->leadsdashboard_model->get_inquiry_form_data($data);
        } else {
            $rawData = $this->leadsdashboard_model->get_vendor_form_data($data);
        }

        if (!empty($rawData)) {
            $formattedData = [];
            foreach ($rawData as $row) {
                $date = $row['date'];
                $form_status = $row['form_status'];
                $count = $row['count'];
                if (!isset($formattedData[$date])) {
                    $formattedData[$date] = [
                        'date' => $date,
                        'pending' => 0,
                        'sent' => 0,
                        'approved' => 0,
                        'not-approved' => 0,
                        'draft' => 0
                    ];
                }
                if (array_key_exists($form_status, $formattedData[$date])) {
                    $formattedData[$date][$form_status] += $count;
                }
            }
            $chart_data['form_data'] = [
                'labels' => array_column($formattedData, 'date'),
                'datasets' => [
                    array("name" => "Approved", "color" => "#2ca02c", "data" => array_column($formattedData, 'approved')),
                    array("name" => "Not Approved", "color" => "#d62728", "data" => array_column($formattedData, 'not-approved')),
                    array("name" => "Review Pending", "color" => "#0ee4f0", "data" => array_column($formattedData, 'pending')),
                    array("name" => "Sent", "color" => "#ff8b17", "data" => array_column($formattedData, 'sent')),
                    array("name" => "Drafts", "color" => "#2c6e91", "data" => array_column($formattedData, 'draft')),
                ]
            ];
        }

        // Proposal Chart Data
        $rawData = $this->leadsdashboard_model->get_proposal_data($data);
        if (!empty($rawData)) {
            $chart_data['proposal_data'] = [
                'labels' => array_column($rawData, 'date'),
                'datasets' => [
                    array("name" => "Accepted", "color" => "#5eeb8a", "data" => array_column($rawData, 'accepted')),
                    array("name" => "Declined", "color" => "#d62728", "data" => array_column($rawData, 'declined')),
                    array("name" => "Open", "color" => "#2d0d42", "data" => array_column($rawData, 'open')),
                    array("name" => "Sent", "color" => "#ff8b17", "data" => array_column($rawData, 'sent')),
                    array("name" => "Revised", "color" => "#a33603", "data" => array_column($rawData, 'revised')),
                    array("name" => "Draft", "color" => "#2c6e91", "data" => array_column($rawData, 'draft')),
                ]
            ];
        }

        // Contract Chart Data
        $rawData = $this->leadsdashboard_model->get_contract_data($data);
        if (!empty($rawData)) {
            $chart_data['contract_data'] = [
                'labels' => array_column($rawData, 'date'),
                'datasets' => [
                    array("name" => "Verified", "color" => "#5eeb8a", "data" => array_column($rawData, 'verified')),
                    array("name" => "In Review", "color" => "#d62728", "data" => array_column($rawData, 'in review')),
                    array("name" => "Sent", "color" => "#ff8b17", "data" => array_column($rawData, 'sent')),
                    array("name" => "Draft", "color" => "#2c6e91", "data" => array_column($rawData, 'draft')),
                ]
            ];
        }

        // Avg Order.
        $getStaff = $this->staff_model->get();
        if (!empty($getStaff)) {
            $staffUser = array();
            $amountArr = array();
            $datasetTitle = "Sales Amount";
            foreach ($getStaff as $staff) {
                if (!empty($data['assignee']) && $staff['staffid'] != $data['assignee']) {
                    continue;
                }
                $totalData = calculate_volume_of_business(null, $staff['staffid'], $data['startdate'], $data['enddate']);
                if ($totalData['total_amount'] != 0) {
                    $staffUser[] = $staff['firstname'] . " " . $staff['lastname'];
                    if ($data['salesChartType'] == "total-sales") {
                        $datasetTitle = "Sales Amount";
                        $amountArr[] = $totalData['total_amount'];
                    } else {
                        $datasetTitle = "Average Sales Amount";
                        $amountArr[] = $totalData['total_amount'] / $totalData['total_transaction'];
                    }
                }
            }
            if (!empty($staffUser)) {
                $chart_data['sales_chart_data'] = [
                    'labels' => $staffUser,
                    'datasets' => [
                        array("name" => $datasetTitle, "color" => "#5eeb8a", "data" => $amountArr),
                    ]
                ];
            }
        }

        //Leads Follow Up Data
        $categories = ['Attend', 'Not Attend', 'Declined', 'Busy', 'Not Reachable', 'Visited', 'Not Visited', 'Present', 'Absent'];
        $categoryColors = [
            'Attend' => '#2ca02c',
            'Not Attend' => '#ff7f0e',
            'Declined' => '#d62728',
            'Busy' => '#1f77b4',
            'Not Reachable' => '#9467bd',
            'Visited' => '#2ca02c',
            'Not Visited' => '#d62728',
            'Present' => '#2ca02c',
            'Absent' => '#d62728'
        ];
        $rawData = $this->leadsdashboard_model->get_leads_followup_data($data);
        if (!empty($rawData)) {
            $chart_data['leads_followup_data']['labels'] = array_column($rawData, 'date');
            foreach ($categories as $category) {
                $data = [];
                foreach ($rawData as $item) {
                    if (isset($item[$category])) {
                        $data[] = (int)$item[$category];
                    }
                }
                if (!empty($data)) {
                    $chart_data['leads_followup_data']['datasets'][] = [
                        'name' => $category,
                        'data' => $data,
                        'color' => $categoryColors[$category]
                    ];
                }
            }
        }

        $result['success'] = true;
        $result['data'] = $chart_data;
        echo json_encode($result);
    }

    public function leads_transferred()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('lead_dashboard_leads_transferred');
        }
    }

    public function leads_transferred_to_other()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('lead_dashboard_leads_transferred_to_other');
        }
    }
}
