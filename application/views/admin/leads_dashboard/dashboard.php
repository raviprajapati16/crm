<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= site_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    /* <!-- 
</style>

<style>
    -->*/
    /* #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    } */

    .charts-section .panel-heading {
        display: flex;
        justify-content: space-between;
        padding: 0px 10px 0px 10px;
        align-items: center;
    }

    .charts-section .panel-heading {
        display: flex;
        justify-content: space-between;
        padding: 5px 10px 5px 10px;
        align-items: center;
    }

    .charts-section .d-flex {
        display: flex;
    }

    .charts-section .header-filter-section select {
        margin-left: 5px;
        width: 170px;
    }

    .chart-div {
        height: 350px;
        min-height: 350px !important;
    }

    .select-container {
        display: inline-block;
    }

    .header-filter-section {
        display: flex;
        gap: 10px;
    }

    .report-btn {
        margin-top: 25px;
    }

    .report-btn .dropdown-menu button {
        background: none;
        border: none;

        text-align: left;
        cursor: pointer;
        padding: 0;
        font-size: inherit;
        padding: 10px;
    }

    .report-btn .dropdown-menu button:hover {
        background: #d6d8d7;
        width: 100%;
    }

    .report-btn .dropdown-menu button:focus {
        outline: none;
        background: #d6d8d7;
        width: 100%;
    }

    .jc-space-between {
        justify-content: space-between;
    }

    .chart-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .chart-title {
        text-align: center;
        margin-bottom: 10px;
        font-size: 15px;
        font-weight: bold;
    }

    /* Responsive styles for mobile */
    @media (max-width: 768px) {
        .panel-heading {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .panel-title {
            margin-bottom: 10px;
        }

        .select-container {
            width: 100%;
        }

        .select-container select {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-filter-section {
            flex-direction: column;
            width: 100%;
        }

        .header-filter-section select {
            width: 100% !important;
            margin-bottom: 10px;
        }
    }

    #lead-transfer-to-self-chart,
    #lead-transfer-to-other-chart {
        min-height: 450px !important;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">Lead Dashboard</div>
                    </div>
                    <div class="panel-body">
                        <div class="row filter-section">
                            <?php if (has_permission('lead_dashboard', '', 'view') || (has_permission('lead_dashboard', '', 'view_own') && manager_employee_data_access_permission_check("leads_dashboard"))) { ?>
                                <div class="col-md-3">
                                    <label for="view_assigned">Staff</label>
                                    <div class="form-group">
                                        <select id="view_assigned" name="view_assigned" class="selectpicker" data-live-search="true" data-none-selected-text="Select Staff" data-width="100%">
                                            <option value="" selected></option>
                                            <?php if (!empty($staff)) {
                                                $selected = false;
                                                foreach ($staff as $user) {
                                                    if (is_manager()) {
                                                        if (!in_array($user['staffid'], get_manager_assigned_staff_ids()) && $user['staffid'] != get_staff_user_id()) {
                                                            continue;
                                                        }
                                                    }
                                                    if (!is_staff_in_sales_department($user['staffid'])) {
                                                        continue;
                                                    }
                                            ?>
                                                    <option value="<?= $user['staffid'] ?>" <?= ($selected) ? "selected" : "" ?>><?= $user['firstname'] ?> <?= $user['lastname'] ?></option>
                                            <?php
                                                }
                                            } ?>
                                        </select>
                                    </div>


                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="assignee" id="view_assigned" value="<?= get_staff_user_id() ?>" />
                            <?php }  ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date-range">Date Range</label>
                                    <input type="text" name="daterange" id="date-range" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-default report-btn hide" onclick="reportPDF()"><i class="fa fa-file-pdf-o"></i> Report</button>
                            </div>
                            <div class="col-md-3 pull-right">
                                <?= tutorialLinkButtonRender('lead-dashboard-btn', 'right', "margin-left:5px;margin-top:0px;"); ?>
                                <button type="button" class="btn btn-info pull-right" onclick="getData()" data-toggle="tooltip" data-title="Refresh"><i class="fa fa-refresh" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <!-- <hr /> -->
                        <!-- <div class="row charts-section">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Today's Lead Follow Up</div>
                                        <div class="pull-right d-flex header-filter-section select-container">
                                            <select id="today-followup-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="today-leads-followup-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Today's Leads Views</div>
                                        <div class="pull-right d-flex header-filter-section select-container">
                                            <select id="today-leads-type" class="form-control">
                                                <option value="leads-view" selected>Leads Views</option>
                                                <option value="leads-updated">Leads Updated</option>
                                            </select>
                                            <select id="today-leads-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="today-leads-chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <hr />
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Leads Summary</div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row" id="lead-summary-section">

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row charts-section">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Received</div>
                                        <div class="pull-right select-container">
                                            <select id="lead-received-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-received-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Leads Views</div>
                                        <div class="pull-right d-flex header-filter-section select-container">
                                            <select id="view-types" class="form-control">
                                                <option value="leads-view" selected>Leads Views</option>
                                                <option value="leads-updated">Leads Updated</option>
                                            </select>
                                            <select id="lead-view-types" class="form-control">
                                                <option value="area" selected>Area Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-view-chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row charts-section">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Allocation</div>
                                        <div class="pull-right">
                                            <select id="lead-allocation-types" class="form-control">
                                                <option value="pie" selected>Pie Chart</option>
                                                <option value="donut">Donut Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-allocation-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Whatsapp Sent</div>
                                        <div class="pull-right d-flex header-filter-section select-container">
                                            <select id="lead-send-type" class="form-control">
                                                <option value="email">Email</option>
                                                <option value="whatsapp" selected>Whatsapp</option>
                                            </select>
                                            <select id="lead-chart-sent-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-send-chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row charts-section">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Customer Conversion</div>
                                        <div class="pull-right">
                                            <select id="customer-conversion-chart-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="customer-conversion-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Leads Follow up</div>
                                        <div class="pull-right d-flex header-filter-section">
                                            <select id="followup-types" class="form-control">
                                                <option value="Call" selected>Calls</option>
                                                <option value="Online Meeting">Online Meetings</option>
                                                <option value="Face To Face">Face to Face Meetings</option>
                                                <option value="Plant Visit">Plant Visit</option>
                                            </select>
                                            <select id="leads-followup-chart-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="leads-followup-chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Commented out Lead Attend Time to Total Sales boxes as requested
                        <div class="row charts-section">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Attend Time</div>
                                        <div class="pull-right">
                                            <select id="lead-attend-chart-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-attend-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Followup Duration</div>
                                        <div class="pull-right">
                                            <select id="lead-followup-duration-chart-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lead-followup-duration-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lapsed Lead</div>
                                        <div class="pull-right">
                                            <select id="lapslead-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="lapslead-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Vendor Conversion</div>
                                        <div class="pull-right">
                                            <select id="vendor-conversion-chart-types" class="form-control">
                                                <option value="line" selected>Line Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="vendor-conversion-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Inquiry Forms</div>
                                        <div class="pull-right d-flex header-filter-section">
                                            <select id="form-types" class="form-control">
                                                <option value="lead-inquiry-form" selected>Lead Inquiry Forms</option>
                                                <option value="vendor-quotation-form">Vendor Quotation Forms</option>
                                            </select>
                                            <select id="forms-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="forms-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Proposals</div>
                                        <div class="pull-right">
                                            <select id="proposal-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="proposal-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Agreements</div>
                                        <div class="pull-right">
                                            <select id="contract-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="contract-chart"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Total Sales</div>
                                        <div class="pull-right d-flex header-filter-section">
                                            <select id="sales-type" class="form-control">
                                                <option value="total-sales" selected>Total Sales</option>
                                                <option value="avg-sales">Average Sales</option>
                                            </select>
                                            <select id="sales-chart-types" class="form-control">
                                                <option value="bar" selected>Bar Chart</option>
                                                <option value="line">Line Chart</option>
                                                <option value="area">Area Chart</option>
                                                <option value="radar">Radar Chart</option>
                                                <option value="scatter">Scatter Chart</option>
                                                <option value="heatmap">Heatmap Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="chart-div" id="sales-chart"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->
                        <div class="row charts-section hide">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Lead Transfer</div>
                                        <div class="pull-right d-flex header-filter-section">
                                            <select id="lead-transfer-chart-types" class="form-control">
                                                <option value="pie" selected>Pie Chart</option>
                                                <option value="donut">Donut Chart</option>
                                                <option value="bar">Bar Chart</option>
                                                <option value="area">Area Chart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h3 class="chart-title">Lead Transfer To Others</h3>
                                                <div class="chart-div" id="lead-transfer-to-other-chart"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <h3 class="chart-title">Lead Transferred To Me</h3>
                                                <div class="chart-div" id="lead-transfer-to-self-chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row leads-transfer-table hide">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Leads transferred to me</div>
                                    </div>
                                    <div class="panel-body">
                                        <?php render_datatable(array(
                                            'Lead ID',
                                            'Lead name',
                                            'Assigned By',
                                            'Assigned Date & Time',
                                        ), 'leads-transferred'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row leads-transfer-table hide">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="panel-title">Leads transferred to Other</div>
                                    </div>
                                    <div class="panel-body">
                                        <?php render_datatable(array(
                                            'Lead ID',
                                            'Lead name',
                                            'Transferred To',
                                            'Transferred Date & Time',
                                        ), 'leads-transferred-to-other'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?= site_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script src="<?= site_url('assets/plugins/apex-charts/apexcharts.js') ?>"></script>
<script src="<?= site_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>
<script src="<?= site_url('assets/plugins/js-pdf/jspdf.umd.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $(window).off('beforeunload');
        if ($('#view_assigned').val() != "" && $('#view_assigned').val() != null) {
            $('#lead-transfer-to-other-chart').closest('.charts-section').removeClass('hide');
            $('.leads-transfer-table').removeClass('hide');

        } else {
            $('#lead-transfer-to-other-chart').closest('.charts-section').addClass('hide');
            $('.leads-transfer-table').addClass('hide');
        }

        $('.selectpicker').selectpicker();
        $('#date-range').daterangepicker({
            locale: {
                format: 'DD-MM-YYYY'
            },
            opens: 'left',
            showCustomRangeLabel: true,
            alwaysShowCalendars: true,
            startDate: moment().startOf('month'),
            endDate: moment(),
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment()],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            }
        });

        var fnServerParams = {
            "assignee": '#view_assigned',
            "daterange": '#date-range'
        }
        app.options.tables_pagination_limit = 10;
        var leadsTransferredTable = initDataTable('.table-leads-transferred', "<?= admin_url('lead_dashboard/leads_transferred') ?>", false, [0, 1, 2, 3], fnServerParams);
        var leadsTransferredToOtherTable = initDataTable('.table-leads-transferred-to-other', "<?= admin_url('lead_dashboard/leads_transferred_to_other') ?>", false, [0, 1, 2, 3], fnServerParams);


        getData();

        $(document).on('change', '#view_assigned', function() {
            if ($('#view_assigned').val() != "" && $('#view_assigned').val() != null) {
                $('#lead-transfer-to-other-chart').closest('.charts-section').removeClass('hide');
                $('.leads-transfer-table').removeClass('hide');

            } else {
                $('#lead-transfer-to-other-chart').closest('.charts-section').addClass('hide');
                $('.leads-transfer-table').addClass('hide');
            }
            getData();
            if (leadsTransferredTable) {
                leadsTransferredTable.draw();
            }
            if (leadsTransferredToOtherTable) {
                leadsTransferredToOtherTable.draw();
            }
        });
        $(document).on('change', '#date-range', function() {
            getData();
            if (leadsTransferredTable) {
                leadsTransferredTable.draw();
            }
            if (leadsTransferredToOtherTable) {
                leadsTransferredToOtherTable.draw();
            }
        });
        $(document).on('change', '#lead-received-types', function() {
            leadReceivedChart();
        });
        $(document).on('change', '#lead-view-types', function() {
            leadViewChart();
        });
        $(document).on('change', '#lead-chart-sent-types', function() {
            leadSendChart();
        });
        $(document).on('change', '#lead-allocation-types', function() {
            leadAllocationChart();
        });
        $(document).on('change', '#customer-conversion-chart-types', function() {
            leadCustomerConversionChart();
        });
        $(document).on('change', '#leads-followup-chart-types', function() {
            leadsFollowUpChart();
        });
        $(document).on('change', '#vendor-conversion-chart-types', function() {
            leadVendorConversionChart();
        });
        $(document).on('change', '#forms-chart-types', function() {
            FormsChart();
        });
        $(document).on('change', '#proposal-chart-types', function() {
            ProposalChart();
        });
        $(document).on('change', '#contract-chart-types', function() {
            ContractChart();
        });
        $(document).on('change', '#lead-transfer-chart-types', function() {
            LeadTransferToOtherChart();
            LeadTransferToSelfChart();
        });
        $(document).on('change', '#contract-chart-types', function() {
            ContractChart();
        });
        $(document).on('change', '#contract-chart-types', function() {
            ContractChart();
        });
        // $(document).on('change', '#today-leads-chart-types', function() {
        //     todayLeadsChart();
        // });
        $(document).on('change', '#lead-attend-chart-types', function() {
            LeadAttendChart();
        });
        $(document).on('change', '#lead-followup-duration-chart-types', function() {
            LeadFollowupDurationChart();
        });
        // $(document).on('change', '#today-leads-type', function() {
        //     $(this).closest('.panel').find('.panel-title').html("Today's " + $('#today-leads-type :selected').text());
        //     getData('today-leads-chart');
        // });
        $(document).on('change', '#followup-types', function() {
            getData('leads-followup-chart');
        });
        // $(document).on('change', '#today-followup-chart-types', function() {
        //     todayLeadFollowupChart();
        // });
        $(document).on('change', '#view-types', function() {
            $(this).closest('.panel').find('.panel-title').html($('#view-types :selected').text());
            getData('lead-view-chart');
        });
        $(document).on('change', '#form-types', function() {
            $(this).closest('.panel').find('.panel-title').html($('#form-types :selected').text());
            getData('forms-chart');
        });
        $(document).on('change', '#lead-send-type', function() {
            $(this).closest('.panel').find('.panel-title').html("Lead " + $('#lead-send-type :selected').text() + " Sent");
            getData('lead-send-chart');
        });
        $(document).on('change', '#lapslead-chart-types', function() {
            lapsedLeadChart();
        });
        $(document).on('change', '#sales-type', function() {
            $(this).closest('.panel').find('.panel-title').html($('#sales-type :selected').text());
            getData('sales-chart');
        });
        $(document).on('change', '#sales-chart-types', function() {
            salesChart();
        });
    });

    function getData(target = "") {
        if (target == "") {
            $('.chart-div').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
        } else {
            $('#' + target).html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
        }
        $('.report-btn').addClass('hide');
        $.ajax({
            url: "<?php echo admin_url('lead_dashboard/get_chart_data') ?>",
            method: "POST",
            data: {
                assignee: $('#view_assigned').val(),
                daterange: $('#date-range').val(),
                viewType: $('#view-types').val(),
                followupType: $('#followup-types').val(),
                formType: $('#form-types').val(),
                todayLeadsType: $('#today-leads-type').val(),
                leadSendType: $('#lead-send-type').val(),
                salesChartType: $('#sales-type').val(),
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                localStorage.setItem('chart_data', JSON.stringify(result.data));
                if (target == "") {
                    leadViewChart();
                    leadReceivedChart();
                    leadSendChart();
                    leadAllocationChart();
                    leadCustomerConversionChart();
                    leadsFollowUpChart();
                    // leadVendorConversionChart();
                    // FormsChart();
                    // ProposalChart();
                    // ContractChart();
                    LeadTransferToSelfChart();
                    LeadTransferToOtherChart();
                    // todayLeadFollowupChart();
                    // todayLeadsChart();
                    // LeadAttendChart();
                    // LeadFollowupDurationChart();
                    // lapsedLeadChart();
                    leadsSummary();
                    // salesChart();
                } else {
                    if (target == "lead-view-chart") {
                        leadViewChart();
                    }
                    if (target == "leads-followup-chart") {
                        leadsFollowUpChart();
                    }
                    if (target == "forms-chart") {
                        FormsChart();
                    }
                    if (target == "today-leads-chart") {
                        todayLeadsChart();
                    }
                    if (target == "lead-send-chart") {
                        leadSendChart();
                    }
                    if (target == "sales-chart") {
                        salesChart();
                    }
                }
                $('.report-btn').removeClass('hide');
            }
        });
    }

    function leadReceivedChart() {
        var chartElement = document.querySelector("#lead-received-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        var type = $('#lead-received-types').val();
        if (data.received_lead_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.received_lead_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += value;
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Received Leads : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.received_lead_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadViewChart() {
        var type = $('#lead-view-types').val();
        var chartElement = document.querySelector("#lead-view-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        }
        var title = $('#view-types :selected').text();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.lead_view_data) {
            chartElement.innerHTML = "";
            var totalCount = data.lead_view_data.leads.reduce((a, b) => Number(a) + Number(b), 0);
            var options = {
                series: [{
                    name: "Leads",
                    data: data.lead_view_data.leads
                }],
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    zoom: {
                        enabled: false
                    },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total ' + title + ' : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    colors: ['#0c542b']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: data.lead_view_data.dates,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                colors: ['#0c542b'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: getResponsiveFontSize(12) + 'px'
                }
            };

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadSendChart() {
        var chartElement = document.querySelector("#lead-send-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#lead-chart-sent-types').val();
        if (data.lead_send_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.lead_send_data.datasets.filter(dataset =>
                dataset.data.some(value => !isNaN(value) && value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data = dataset.data.map(value => isNaN(value) ? 0 : value);
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            function getResponsiveFontSize(baseSize) {
                const width = window.innerWidth;
                if (width <= 480) return baseSize * 0.7;
                if (width <= 768) return baseSize * 0.9;
                return baseSize;
            }

            function shouldShowControls() {
                return window.innerWidth > 768;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Leads ' + $('#lead-send-type :selected').text() + ' Sent : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.lead_send_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    },
                    heatmap: {
                        shadeIntensity: 1,
                        colorScale: {
                            ranges: [{
                                    from: 0,
                                    to: 10,
                                    color: '#00A100',
                                    name: '0-10'
                                },
                                {
                                    from: 11,
                                    to: 20,
                                    color: '#128FD9',
                                    name: '11-20'
                                },
                                {
                                    from: 21,
                                    to: 30,
                                    color: '#FFB200',
                                    name: '21-30'
                                },
                                {
                                    from: 31,
                                    to: 40,
                                    color: '#FF0000',
                                    name: '31-40'
                                }
                            ]
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const count = filteredDatasets[opts.seriesIndex].data.reduce((a, b) => Number(a) + Number(b), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                        tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                        tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total Send: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                        tooltipHtml += `</div></div></div>`;
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    var chart;

    function leadAllocationChart() {
        var chartElement = document.querySelector("#lead-allocation-chart");
        var data = localStorage.getItem('chart_data');

        if (chart) {
            chart.destroy();
        }

        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#lead-allocation-types').val();
        if (data.lead_allocation_data) {
            chartElement.innerHTML = "";

            if (!Array.isArray(data.lead_allocation_data.series) || !Array.isArray(data.lead_allocation_data.labels)) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Invalid data format</div>';
                return;
            }

            var series = data.lead_allocation_data.series;
            var labels = data.lead_allocation_data.labels;

            if (type !== 'pie' && type !== 'donut') {
                series = [{
                    name: 'Lead Allocation',
                    data: series
                }];
            }

            function getResponsiveFontSize(baseSize) {
                const width = window.innerWidth;
                if (width <= 480) return baseSize * 0.7;
                if (width <= 768) return baseSize * 0.9;
                return baseSize;
            }

            var options = {
                series: series,
                chart: {
                    type: type,
                    height: '100%',
                    width: '100%'
                },
                labels: labels,
                colors: [
                    '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0',
                    '#03A9F4', '#4CAF50', '#F9CE1D', '#FF9800',
                    '#9C27B0', '#673AB7', '#E91E63', '#795548', '#607D8B'
                ],
                responsive: [{
                    breakpoint: 1200,
                    options: {
                        legend: {
                            fontSize: getResponsiveFontSize(14) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(14) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 350
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: getResponsiveFontSize(12) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: getResponsiveFontSize(10) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(8) + 'px',
                            },
                        },
                    }
                }],
                legend: {
                    position: (type === 'pie' || type === 'donut') ? 'top' : 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    offsetY: 0,
                    offsetX: 0,
                    fontSize: getResponsiveFontSize(15) + 'px',
                    formatter: function(seriesName, opts) {
                        const count = series[opts.seriesIndex];
                        return seriesName + " (" + count + ")";
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        if (type === 'pie' || type === 'donut') {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                        return val;
                    },
                    style: {
                        fontSize: getResponsiveFontSize(12) + 'px',
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                tooltip: {
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var index = (type === 'pie' || type === 'donut') ? seriesIndex : dataPointIndex;
                        var label = labels[index];
                        var value = (type === 'pie' || type === 'donut') ? series[index] : series[0].data[index];
                        var color = (type === 'pie' || type === 'donut') ? w.globals.colors[index] : "";
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="display: flex;">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                        tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${label}: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${value}</span>`;
                        tooltipHtml += `</div>`;
                        tooltipHtml += '</div></div></div>';
                        return tooltipHtml;
                    }
                },
            };

            if (type !== 'pie' && type !== 'donut') {
                options.xaxis = {
                    categories: labels,
                    labels: {
                        rotate: -45,
                        rotateAlways: false,
                        hideOverlappingLabels: true,
                        trim: true,
                        style: {
                            fontSize: getResponsiveFontSize(10) + 'px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                        },
                    }
                };
                options.yaxis = {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px',
                        }
                    },
                    forceNiceScale: true
                };
            }

            try {
                chart = new ApexCharts(chartElement, options);
                chart.render();

                // Make chart responsive to window resizes
                window.addEventListener('resize', function() {
                    chart.updateOptions({
                        chart: {
                            height: chartElement.offsetHeight,
                            width: chartElement.offsetWidth
                        },
                        legend: {
                            fontSize: getResponsiveFontSize(15) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(10) + 'px',
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(12) + 'px',
                                },
                            },
                        },
                    });
                });
            } catch (e) {
                console.error("Error rendering chart:", e);
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Error rendering chart</div>';
            }
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadCustomerConversionChart() {
        var type = $('#customer-conversion-chart-types').val();
        var chartElement = document.querySelector("#customer-conversion-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        }
        var title = $('#customer-conversion-chart-types :selected').text();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.cutomer_conversion_data) {
            chartElement.innerHTML = "";
            var totalCount = data.cutomer_conversion_data.customers.reduce((a, b) => Number(a) + Number(b), 0);
            var options = {
                series: [{
                    name: "Customers",
                    data: data.cutomer_conversion_data.customers
                }],
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    zoom: {
                        enabled: false
                    },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Customer Conversion : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    colors: ['#0c542b']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: data.cutomer_conversion_data.dates,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                colors: ['#0c542b'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: getResponsiveFontSize(12) + 'px'
                }
            };
            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadsFollowUpChart() {
        var chartElement = document.querySelector("#leads-followup-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }
        var type = $('#leads-followup-chart-types').val();
        var title = $('#followup-types :selected').text();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.leads_followup_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.leads_followup_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += value;
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            filteredDatasets.forEach(dataset => {
                const datasetTotal = dataset.data.reduce((acc, curr) => Number(acc) + Number(curr), 0);
                dataset.nameWithCount = `${dataset.name} (${datasetTotal})`;
            });

            var options = {
                series: filteredDatasets.map(dataset => ({
                    ...dataset,
                    name: dataset.nameWithCount
                })),
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total ' + title + ' : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.leads_followup_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px'
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name.split(' (')[0]}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadVendorConversionChart() {
        var type = $('#vendor-conversion-chart-types').val();
        var chartElement = document.querySelector("#vendor-conversion-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        }
        var title = $('#vendor-conversion-chart-types :selected').text();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768;
        }

        if (data.vendor_conversion_data) {
            chartElement.innerHTML = "";
            var totalCount = data.vendor_conversion_data.vendors.reduce((a, b) => Number(a) + Number(b), 0);
            var options = {
                series: [{
                    name: "Vendors",
                    data: data.vendor_conversion_data.vendors
                }],
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    zoom: {
                        enabled: false
                    },
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Vendor Conversion : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    colors: ['#0c542b']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: data.vendor_conversion_data.dates,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                colors: ['#0c542b'],
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    fontSize: getResponsiveFontSize(12) + 'px'
                }
            };
            var chart = new ApexCharts(chartElement, options);
            chart.render();

            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function getResponsiveFontSize(baseSize) {
        var windowWidth = window.innerWidth;
        if (windowWidth <= 767) {
            return baseSize * 0.8;
        } else if (windowWidth <= 1199) {
            return baseSize * 0.9;
        } else {
            return baseSize;
        }
    }

    function FormsChart() {
        var chartElement = document.querySelector("#forms-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#forms-chart-types').val();
        var title = $('#form-types :selected').text();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.form_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.form_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += value;
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total ' + title + ' : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.form_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function ProposalChart() {
        var chartElement = document.querySelector("#proposal-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#proposal-chart-types').val();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.proposal_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.proposal_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Proposals : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.proposal_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function ContractChart() {
        var chartElement = document.querySelector("#contract-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#contract-chart-types').val();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.contract_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.contract_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Agreements : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.contract_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function salesChart() {
        var chartElement = document.querySelector("#sales-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#sales-chart-types').val();

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768; // Show controls only on screens wider than 768px
        }

        if (data.sales_chart_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.sales_chart_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Total Amount : ' + totalCount,
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.sales_chart_data.labels,
                    labels: {
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                            tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                            tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                            tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                            tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
                            tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
                            tooltipHtml += `</div></div></div>`;
                        }
                        return tooltipHtml;
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    },
                    xaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px'
                            }
                        }
                    }
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    var chartLeadTransfer;

    function LeadTransferToOtherChart() {
        var chartElement = document.querySelector("#lead-transfer-to-other-chart");
        var data = localStorage.getItem('chart_data');

        if (chartLeadTransfer) {
            chartLeadTransfer.destroy();
        }

        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#lead-transfer-chart-types').val();
        if (data.lead_transfer_to_other_data) {
            chartElement.innerHTML = "";

            if (!Array.isArray(data.lead_transfer_to_other_data.series) || !Array.isArray(data.lead_transfer_to_other_data.labels)) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Invalid data format</div>';
                return;
            }

            var series = data.lead_transfer_to_other_data.series;
            var labels = data.lead_transfer_to_other_data.labels;

            if (type !== 'pie' && type !== 'donut') {
                series = [{
                    name: 'Lead Allocation',
                    data: series
                }];
            }

            function getResponsiveFontSize(baseSize) {
                const width = window.innerWidth;
                if (width <= 480) return baseSize * 0.7;
                if (width <= 768) return baseSize * 0.9;
                return baseSize;
            }

            var options = {
                series: series,
                chart: {
                    type: type,
                    height: 400,
                    width: '100%'
                },
                labels: labels,
                colors: [
                    '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0',
                    '#03A9F4', '#4CAF50', '#F9CE1D', '#FF9800',
                    '#9C27B0', '#673AB7', '#E91E63', '#795548', '#607D8B'
                ],
                responsive: [{
                    breakpoint: 1200,
                    options: {
                        legend: {
                            fontSize: getResponsiveFontSize(14) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(14) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 350
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: getResponsiveFontSize(12) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: getResponsiveFontSize(10) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(8) + 'px',
                            },
                        },
                    }
                }],
                legend: {
                    position: (type === 'pie' || type === 'donut') ? 'top' : 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    offsetY: 0,
                    offsetX: 0,
                    fontSize: getResponsiveFontSize(15) + 'px',
                    formatter: function(seriesName, opts) {
                        const count = series[opts.seriesIndex];
                        return seriesName + " (" + count + ")";
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        if (type === 'pie' || type === 'donut') {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                        return val;
                    },
                    style: {
                        fontSize: getResponsiveFontSize(12) + 'px',
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                tooltip: {
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var index = (type === 'pie' || type === 'donut') ? seriesIndex : dataPointIndex;
                        var label = labels[index];
                        var value = (type === 'pie' || type === 'donut') ? series[index] : series[0].data[index];
                        var color = (type === 'pie' || type === 'donut') ? w.globals.colors[index] : "";
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="display: flex;">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                        tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${label}: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${value}</span>`;
                        tooltipHtml += `</div>`;
                        tooltipHtml += '</div></div></div>';
                        return tooltipHtml;
                    }
                },
            };

            if (type !== 'pie' && type !== 'donut') {
                options.xaxis = {
                    categories: labels,
                    labels: {
                        rotate: -45,
                        rotateAlways: false,
                        hideOverlappingLabels: true,
                        trim: true,
                        style: {
                            fontSize: getResponsiveFontSize(10) + 'px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                        },
                    }
                };
                options.yaxis = {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px',
                        }
                    },
                    forceNiceScale: true
                };
            }

            try {
                chartLeadTransfer = new ApexCharts(chartElement, options);
                chartLeadTransfer.render();
                window.addEventListener('resize', function() {
                    chartLeadTransfer.updateOptions({
                        chart: {
                            height: 400,
                            width: '100%'
                        },
                        legend: {
                            fontSize: getResponsiveFontSize(15) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(10) + 'px',
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(12) + 'px',
                                },
                            },
                        },
                    });
                });
            } catch (e) {
                console.error("Error rendering chart:", e);
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Error rendering chart</div>';
            }
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    var chartLeadSelfTransfer;

    function LeadTransferToSelfChart() {
        var chartElementLeadTransferSelf = document.querySelector("#lead-transfer-to-self-chart");
        var data = localStorage.getItem('chart_data');

        if (chartLeadSelfTransfer) {
            chartLeadSelfTransfer.destroy();
        }

        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElementLeadTransferSelf.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        var type = $('#lead-transfer-chart-types').val();
        if (data.lead_transfer_to_self_data) {
            chartElementLeadTransferSelf.innerHTML = "";

            if (!Array.isArray(data.lead_transfer_to_self_data.series) || !Array.isArray(data.lead_transfer_to_self_data.labels)) {
                chartElementLeadTransferSelf.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Invalid data format</div>';
                return;
            }

            var series = data.lead_transfer_to_self_data.series;
            var labels = data.lead_transfer_to_self_data.labels;

            if (type !== 'pie' && type !== 'donut') {
                series = [{
                    name: 'Lead Allocation',
                    data: series
                }];
            }

            function getResponsiveFontSize(baseSize) {
                const width = window.innerWidth;
                if (width <= 480) return baseSize * 0.7;
                if (width <= 768) return baseSize * 0.9;
                return baseSize;
            }

            var options = {
                series: series,
                chart: {
                    type: type,
                    height: 400, // Fixed height for the chart
                    width: '100%'
                },
                labels: labels,
                colors: [
                    '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0',
                    '#03A9F4', '#4CAF50', '#F9CE1D', '#FF9800',
                    '#9C27B0', '#673AB7', '#E91E63', '#795548', '#607D8B'
                ],
                responsive: [{
                    breakpoint: 1200,
                    options: {
                        legend: {
                            fontSize: getResponsiveFontSize(14) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(14) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 350
                        },
                        legend: {
                            position: 'top', // Set legend to top for smaller screens
                            fontSize: getResponsiveFontSize(12) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'top', // Set legend to top for smaller screens
                            fontSize: getResponsiveFontSize(10) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(8) + 'px',
                            },
                        },
                    }
                }],
                legend: {
                    position: 'top', // Set legend to top for all types
                    horizontalAlign: 'center',
                    floating: false,
                    offsetY: 0,
                    offsetX: 0,
                    fontSize: getResponsiveFontSize(15) + 'px',
                    formatter: function(seriesName, opts) {
                        const count = series[opts.seriesIndex];
                        return seriesName + " (" + count + ")";
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        if (type === 'pie' || type === 'donut') {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                        return val;
                    },
                    style: {
                        fontSize: getResponsiveFontSize(12) + 'px',
                    },
                    dropShadow: {
                        enabled: false
                    }
                },
                tooltip: {
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var index = (type === 'pie' || type === 'donut') ? seriesIndex : dataPointIndex;
                        var label = labels[index];
                        var value = (type === 'pie' || type === 'donut') ? series[index] : series[0].data[index];
                        var color = (type === 'pie' || type === 'donut') ? w.globals.colors[index] : "";
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="display: flex;">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                        tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${label}: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${value}</span>`;
                        tooltipHtml += `</div>`;
                        tooltipHtml += '</div></div></div>';
                        return tooltipHtml;
                    }
                },
            };

            if (type !== 'pie' && type !== 'donut') {
                options.xaxis = {
                    categories: labels,
                    labels: {
                        rotate: -45,
                        rotateAlways: false,
                        hideOverlappingLabels: true,
                        trim: true,
                        style: {
                            fontSize: getResponsiveFontSize(10) + 'px',
                            fontFamily: 'Helvetica, Arial, sans-serif',
                        },
                    }
                };
                options.yaxis = {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return Math.floor(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: getResponsiveFontSize(12) + 'px',
                        }
                    },
                    forceNiceScale: true
                };
            }

            try {
                chartLeadSelfTransfer = new ApexCharts(chartElementLeadTransferSelf, options);
                chartLeadSelfTransfer.render();
                window.addEventListener('resize', function() {
                    chartLeadSelfTransfer.updateOptions({
                        chart: {
                            height: 400, // Keep the fixed height on resize
                            width: '100%'
                        },
                        legend: {
                            fontSize: getResponsiveFontSize(15) + 'px',
                        },
                        dataLabels: {
                            style: {
                                fontSize: getResponsiveFontSize(12) + 'px',
                            },
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(10) + 'px',
                                },
                            },
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: getResponsiveFontSize(12) + 'px',
                                },
                            },
                        },
                    });
                });
            } catch (e) {
                console.error("Error rendering chart:", e);
                chartElementLeadTransferSelf.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">Error rendering chart</div>';
            }
        } else {
            chartElementLeadTransferSelf.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    // function todayLeadFollowupChart() {
    //     var chartElement = document.querySelector("#today-leads-followup-chart");
    //     var data = localStorage.getItem('chart_data');
    //     if (data != null && data != "") {
    //         data = JSON.parse(data);
    //     } else {
    //         chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //         return;
    //     }

    //     function getResponsiveFontSize(baseSize) {
    //         const width = window.innerWidth;
    //         if (width <= 480) return baseSize * 0.7;
    //         if (width <= 768) return baseSize * 0.9;
    //         return baseSize;
    //     }

    //     function shouldShowControls() {
    //         return window.innerWidth > 768;
    //     }

    //     var type = $('#today-followup-chart-types').val();
    //     if (data.today_lead_followup_data) {
    //         chartElement.innerHTML = "";
    //         var totalCount = 0;

    //         const filteredDatasets = data.today_lead_followup_data.datasets.filter(dataset =>
    //             dataset.data.some(value => value > 0)
    //         );

    //         filteredDatasets.forEach(dataset => {
    //             dataset.data.forEach(value => {
    //                 totalCount += Number(value);
    //             });
    //         });

    //         if (filteredDatasets.length === 0) {
    //             chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //             return;
    //         }

    //         var options = {
    //             series: filteredDatasets,
    //             chart: {
    //                 height: '100%',
    //                 width: '100%',
    //                 type: type,
    //                 stacked: type === 'bar',
    //                 events: {
    //                     dataPointSelection: function(event, chartContext, config) {
    //                         updateChartToDateWise(config.dataPointIndex);
    //                     }
    //                 },
    //                 animations: {
    //                     enabled: true,
    //                     easing: 'easeinout',
    //                     speed: 800,
    //                     animateGradually: {
    //                         enabled: true,
    //                         delay: 150
    //                     },
    //                     dynamicAnimation: {
    //                         enabled: true,
    //                         speed: 350
    //                     }
    //                 },
    //                 toolbar: {
    //                     show: shouldShowControls(),
    //                     tools: {
    //                         download: true,
    //                         selection: true,
    //                         zoom: true,
    //                         zoomin: true,
    //                         zoomout: true,
    //                         pan: true,
    //                         reset: true
    //                     },
    //                     autoSelected: 'zoom'
    //                 }
    //             },
    //             title: {
    //                 text: 'Total Received Leads : ' + totalCount,
    //                 align: 'center',
    //                 style: {
    //                     fontSize: getResponsiveFontSize(15) + 'px',
    //                     fontWeight: 'bold',
    //                     fontFamily: undefined,
    //                     color: '#263238'
    //                 }
    //             },
    //             responsive: [{
    //                 breakpoint: 480,
    //                 options: {
    //                     chart: {
    //                         width: '100%'
    //                     },
    //                     legend: {
    //                         position: 'bottom'
    //                     }
    //                 }
    //             }],
    //             dataLabels: {
    //                 enabled: false
    //             },
    //             stroke: {
    //                 show: true,
    //                 width: type === 'line' ? 2 : 0,
    //                 colors: ['transparent']
    //             },
    //             grid: {
    //                 row: {
    //                     colors: ['#f3f3f3', 'transparent'],
    //                     opacity: 0.5
    //                 }
    //             },
    //             xaxis: {
    //                 categories: data.today_lead_followup_data.labels,
    //                 labels: {
    //                     style: {
    //                         fontSize: getResponsiveFontSize(12) + 'px'
    //                     }
    //                 }
    //             },
    //             yaxis: {
    //                 min: 1,
    //                 labels: {
    //                     formatter: function(value) {
    //                         if (type != "heatmap") {
    //                             return Math.floor(value);
    //                         }
    //                         return value;
    //                     },
    //                     style: {
    //                         fontSize: getResponsiveFontSize(12) + 'px'
    //                     }
    //                 },
    //                 forceNiceScale: true
    //             },
    //             plotOptions: {
    //                 bar: {
    //                     columnWidth: '50%',
    //                     distributed: false,
    //                     endingShape: 'rounded'
    //                 }
    //             },
    //             legend: {
    //                 position: 'top',
    //                 horizontalAlign: 'left',
    //                 offsetX: 40,
    //                 fontSize: getResponsiveFontSize(12) + 'px',
    //                 formatter: function(seriesName, opts) {
    //                     const seriesIndex = opts.seriesIndex;
    //                     const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
    //                     return seriesName + " (" + count + ")";
    //                 }
    //             },
    //             tooltip: {
    //                 shared: true,
    //                 intersect: false,
    //                 custom: function({
    //                     series,
    //                     seriesIndex,
    //                     dataPointIndex,
    //                     w
    //                 }) {
    //                     var lbl = "";
    //                     if (w.globals.categoryLabels.length > 0) {
    //                         lbl = w.globals.categoryLabels[dataPointIndex];
    //                     } else {
    //                         lbl = w.globals.labels[dataPointIndex];
    //                     }
    //                     let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
    //                     tooltipHtml += lbl;
    //                     tooltipHtml += '</div>';
    //                     let totalValue = 0;
    //                     w.config.series.forEach((s, index) => {
    //                         if (s.data[dataPointIndex] > 0) {
    //                             const color = w.globals.colors[index];
    //                             tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
    //                             tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
    //                             tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
    //                             tooltipHtml += `</div>`;
    //                             tooltipHtml += '</div></div>';
    //                             totalValue += Number(s.data[dataPointIndex]);
    //                         }
    //                     });
    //                     if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
    //                         tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
    //                         tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
    //                         tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
    //                         tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
    //                         tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
    //                         tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
    //                         tooltipHtml += `</div></div></div>`;
    //                     }
    //                     return tooltipHtml;
    //                 }
    //             }
    //         };

    //         switch (type) {
    //             case 'line':
    //                 delete options.plotOptions.bar;
    //                 options.stroke.width = 2;
    //                 delete options.stroke.colors;
    //                 options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
    //                 options.markers = {
    //                     size: 4,
    //                     hover: {
    //                         size: 6
    //                     }
    //                 };
    //                 options.stroke.curve = 'smooth';
    //                 break;
    //             case 'bar':
    //             case 'column':
    //                 options.plotOptions.bar.columnWidth = '50%';
    //                 break;
    //             case 'area':
    //                 options.stroke.curve = 'smooth';
    //                 options.fill = {
    //                     type: 'gradient',
    //                     gradient: {
    //                         shadeIntensity: 1,
    //                         opacityFrom: 0.5,
    //                         opacityTo: 0.5,
    //                         stops: [0, 100]
    //                     }
    //                 };
    //                 break;
    //             case 'radar':
    //                 delete options.plotOptions.bar;
    //                 options.stroke.width = 2;
    //                 break;
    //             case 'scatter':
    //                 options.plotOptions.scatter = {
    //                     markers: {
    //                         size: 5
    //                     }
    //                 };
    //                 break;
    //             case 'heatmap':
    //                 options.dataLabels = {
    //                     enabled: true
    //                 };
    //                 break;
    //         }

    //         var chart = new ApexCharts(chartElement, options);
    //         chart.render();

    //         // Add resize event listener
    //         window.addEventListener('resize', function() {
    //             chart.updateOptions({
    //                 chart: {
    //                     height: chartElement.offsetHeight,
    //                     width: chartElement.offsetWidth,
    //                     toolbar: {
    //                         show: shouldShowControls()
    //                     }
    //                 },
    //                 title: {
    //                     style: {
    //                         fontSize: getResponsiveFontSize(15) + 'px'
    //                     }
    //                 },
    //                 legend: {
    //                     fontSize: getResponsiveFontSize(12) + 'px'
    //                 },
    //                 xaxis: {
    //                     labels: {
    //                         style: {
    //                             fontSize: getResponsiveFontSize(12) + 'px'
    //                         }
    //                     }
    //                 },
    //                 yaxis: {
    //                     labels: {
    //                         style: {
    //                             fontSize: getResponsiveFontSize(12) + 'px'
    //                         }
    //                     }
    //                 }
    //             });
    //         });
    //     } else {
    //         chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //     }
    // }

    // function todayLeadsChart() {
    //     var chartElement = document.querySelector("#today-leads-chart");
    //     var data = localStorage.getItem('chart_data');
    //     if (data != null && data != "") {
    //         data = JSON.parse(data);
    //     } else {
    //         chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //         return;
    //     }

    //     function getResponsiveFontSize(baseSize) {
    //         const width = window.innerWidth;
    //         if (width <= 480) return baseSize * 0.7;
    //         if (width <= 768) return baseSize * 0.9;
    //         return baseSize;
    //     }

    //     function shouldShowControls() {
    //         return window.innerWidth > 768;
    //     }

    //     // var type = $('#today-leads-chart-types :selected').val();
    //     // var typeTxt = $('#today-leads-type :selected').text()
    //     if (data.today_lead_data) {
    //         chartElement.innerHTML = "";
    //         var totalCount = 0;

    //         const filteredDatasets = data.today_lead_data.datasets.filter(dataset =>
    //             dataset.data.some(value => value > 0)
    //         );

    //         filteredDatasets.forEach(dataset => {
    //             dataset.data.forEach(value => {
    //                 totalCount += Number(value);
    //             });
    //         });

    //         if (filteredDatasets.length === 0) {
    //             chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //             return;
    //         }

    //         var options = {
    //             series: filteredDatasets,
    //             chart: {
    //                 height: '100%',
    //                 width: '100%',
    //                 type: type,
    //                 stacked: type === 'bar',
    //                 events: {
    //                     dataPointSelection: function(event, chartContext, config) {
    //                         updateChartToDateWise(config.dataPointIndex);
    //                     }
    //                 },
    //                 animations: {
    //                     enabled: true,
    //                     easing: 'easeinout',
    //                     speed: 800,
    //                     animateGradually: {
    //                         enabled: true,
    //                         delay: 150
    //                     },
    //                     dynamicAnimation: {
    //                         enabled: true,
    //                         speed: 350
    //                     }
    //                 },
    //                 toolbar: {
    //                     show: shouldShowControls(),
    //                     tools: {
    //                         download: true,
    //                         selection: true,
    //                         zoom: true,
    //                         zoomin: true,
    //                         zoomout: true,
    //                         pan: true,
    //                         reset: true
    //                     },
    //                     autoSelected: 'zoom'
    //                 }
    //             },
    //             title: {
    //                 text: typeTxt + ' : ' + totalCount,
    //                 align: 'center',
    //                 style: {
    //                     fontSize: getResponsiveFontSize(15) + 'px',
    //                     fontWeight: 'bold',
    //                     fontFamily: undefined,
    //                     color: '#263238'
    //                 }
    //             },
    //             responsive: [{
    //                 breakpoint: 480,
    //                 options: {
    //                     chart: {
    //                         width: '100%'
    //                     },
    //                     legend: {
    //                         position: 'bottom'
    //                     }
    //                 }
    //             }],
    //             dataLabels: {
    //                 enabled: false
    //             },
    //             stroke: {
    //                 show: true,
    //                 width: type === 'line' ? 2 : 0,
    //                 colors: ['transparent']
    //             },
    //             grid: {
    //                 row: {
    //                     colors: ['#f3f3f3', 'transparent'],
    //                     opacity: 0.5
    //                 }
    //             },
    //             xaxis: {
    //                 categories: data.today_lead_data.labels,
    //                 labels: {
    //                     style: {
    //                         fontSize: getResponsiveFontSize(12) + 'px'
    //                     }
    //                 }
    //             },
    //             yaxis: {
    //                 min: 1,
    //                 labels: {
    //                     formatter: function(value) {
    //                         if (type != "heatmap") {
    //                             return Math.floor(value);
    //                         }
    //                         return value;
    //                     },
    //                     style: {
    //                         fontSize: getResponsiveFontSize(12) + 'px'
    //                     }
    //                 },
    //                 forceNiceScale: true
    //             },
    //             plotOptions: {
    //                 bar: {
    //                     columnWidth: '50%',
    //                     distributed: false,
    //                     endingShape: 'rounded'
    //                 }
    //             },
    //             legend: {
    //                 position: 'top',
    //                 horizontalAlign: 'left',
    //                 offsetX: 40,
    //                 fontSize: getResponsiveFontSize(12) + 'px',
    //                 formatter: function(seriesName, opts) {
    //                     const seriesIndex = opts.seriesIndex;
    //                     const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
    //                     return seriesName + " (" + count + ")";
    //                 }
    //             },
    //             tooltip: {
    //                 shared: true,
    //                 intersect: false,
    //                 custom: function({
    //                     series,
    //                     seriesIndex,
    //                     dataPointIndex,
    //                     w
    //                 }) {
    //                     var lbl = "";
    //                     if (w.globals.categoryLabels.length > 0) {
    //                         lbl = w.globals.categoryLabels[dataPointIndex];
    //                     } else {
    //                         lbl = w.globals.labels[dataPointIndex];
    //                     }
    //                     let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
    //                     tooltipHtml += lbl;
    //                     tooltipHtml += '</div>';
    //                     let totalValue = 0;
    //                     w.config.series.forEach((s, index) => {
    //                         if (s.data[dataPointIndex] > 0) {
    //                             const color = w.globals.colors[index];
    //                             tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
    //                             tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
    //                             tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
    //                             tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
    //                             tooltipHtml += `</div>`;
    //                             tooltipHtml += '</div></div>';
    //                             totalValue += Number(s.data[dataPointIndex]);
    //                         }
    //                     });
    //                     if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
    //                         tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
    //                         tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
    //                         tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
    //                         tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
    //                         tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total: </span>`;
    //                         tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${totalValue}</span>`;
    //                         tooltipHtml += `</div></div></div>`;
    //                     }
    //                     return tooltipHtml;
    //                 }
    //             }
    //         };

    //         switch (type) {
    //             case 'line':
    //                 delete options.plotOptions.bar;
    //                 options.stroke.width = 2;
    //                 delete options.stroke.colors;
    //                 options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
    //                 options.markers = {
    //                     size: 4,
    //                     hover: {
    //                         size: 6
    //                     }
    //                 };
    //                 options.stroke.curve = 'smooth';
    //                 break;
    //             case 'bar':
    //             case 'column':
    //                 options.plotOptions.bar.columnWidth = '50%';
    //                 break;
    //             case 'area':
    //                 options.stroke.curve = 'smooth';
    //                 options.fill = {
    //                     type: 'gradient',
    //                     gradient: {
    //                         shadeIntensity: 1,
    //                         opacityFrom: 0.5,
    //                         opacityTo: 0.5,
    //                         stops: [0, 100]
    //                     }
    //                 };
    //                 break;
    //             case 'radar':
    //                 delete options.plotOptions.bar;
    //                 options.stroke.width = 2;
    //                 break;
    //             case 'scatter':
    //                 options.plotOptions.scatter = {
    //                     markers: {
    //                         size: 5
    //                     }
    //                 };
    //                 break;
    //             case 'heatmap':
    //                 options.dataLabels = {
    //                     enabled: true
    //                 };
    //                 break;
    //         }

    //         var chart = new ApexCharts(chartElement, options);
    //         chart.render();

    //         // Add resize event listener
    //         window.addEventListener('resize', function() {
    //             chart.updateOptions({
    //                 chart: {
    //                     height: chartElement.offsetHeight,
    //                     width: chartElement.offsetWidth,
    //                     toolbar: {
    //                         show: shouldShowControls()
    //                     }
    //                 },
    //                 title: {
    //                     style: {
    //                         fontSize: getResponsiveFontSize(15) + 'px'
    //                     }
    //                 },
    //                 legend: {
    //                     fontSize: getResponsiveFontSize(12) + 'px'
    //                 },
    //                 xaxis: {
    //                     labels: {
    //                         style: {
    //                             fontSize: getResponsiveFontSize(12) + 'px'
    //                         }
    //                     }
    //                 },
    //                 yaxis: {
    //                     labels: {
    //                         style: {
    //                             fontSize: getResponsiveFontSize(12) + 'px'
    //                         }
    //                     }
    //                 }
    //             });
    //         });
    //     } else {
    //         chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
    //     }
    // }

    function LeadAttendChart() {
        var chartElement = document.querySelector("#lead-attend-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768;
        }

        function formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return [
                h > 0 ? h + 'h' : '',
                m > 0 ? m + 'm' : '',
                s > 0 || (h === 0 && m === 0) ? s + 's' : ''
            ].filter(Boolean).join(' ');
        }

        var type = $('#lead-attend-chart-types :selected').val();
        if (data.lead_attend_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.lead_attend_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Avg. Lead Attend Time',
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.lead_attend_data.labels,
                    labels: {
                        style: {
                            fontSize: '12px' // Fixed font size
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return formatTime(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + formatTime(count) + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${formatTime(s.data[dataPointIndex])}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        //if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                        tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                        tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total Leads Attend: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${ data.lead_attend_data.total_leads[dataPointIndex] }</span>`;
                        tooltipHtml += `</div></div></div>`;
                        //}
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    }
                    // Removed x and y axis label updates
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function LeadFollowupDurationChart() {
        var chartElement = document.querySelector("#lead-followup-duration-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768;
        }

        function formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return [
                h > 0 ? h + 'h' : '',
                m > 0 ? m + 'm' : '',
                s > 0 || (h === 0 && m === 0) ? s + 's' : ''
            ].filter(Boolean).join(' ');
        }

        var type = $('#lead-followup-duration-chart-types :selected').val();
        if (data.lead_followup_duration_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.lead_followup_duration_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Follow-up Duration',
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.lead_followup_duration_data.labels,
                    labels: {
                        style: {
                            fontSize: '12px' // Fixed font size
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return formatTime(value);
                            }
                            return value;
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + formatTime(count) + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${formatTime(s.data[dataPointIndex])}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        //if (w.config.series.filter(s => s.data[dataPointIndex] > 0).length > 1) {
                        tooltipHtml += `<div class="apexcharts-tooltip-series-group" style="order: ${w.config.series.length + 1}; display: flex;">`;
                        tooltipHtml += '<span class="apexcharts-tooltip-marker" style="background-color: #888888;"></span>';
                        tooltipHtml += `<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">`;
                        tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">Total Duration: </span>`;
                        tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${formatTime(totalValue)}</span>`;
                        tooltipHtml += `</div></div></div>`;
                        //}
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    }
                    // Removed x and y axis label updates
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function lapsedLeadChart() {
        var chartElement = document.querySelector("#lapslead-chart");
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
            return;
        }

        function getResponsiveFontSize(baseSize) {
            const width = window.innerWidth;
            if (width <= 480) return baseSize * 0.7;
            if (width <= 768) return baseSize * 0.9;
            return baseSize;
        }

        function shouldShowControls() {
            return window.innerWidth > 768;
        }

        function formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = Math.floor(seconds % 60);
            return [
                h > 0 ? h + 'h' : '',
                m > 0 ? m + 'm' : '',
                s > 0 || (h === 0 && m === 0) ? s + 's' : ''
            ].filter(Boolean).join(' ');
        }

        var type = $('#lapslead-chart-types :selected').val();
        if (data.lapslead_data) {
            chartElement.innerHTML = "";
            var totalCount = 0;

            const filteredDatasets = data.lapslead_data.datasets.filter(dataset =>
                dataset.data.some(value => value > 0)
            );

            filteredDatasets.forEach(dataset => {
                dataset.data.forEach(value => {
                    totalCount += Number(value);
                });
            });

            if (filteredDatasets.length === 0) {
                chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
                return;
            }

            var options = {
                series: filteredDatasets,
                chart: {
                    height: '100%',
                    width: '100%',
                    type: type,
                    stacked: type === 'bar',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            updateChartToDateWise(config.dataPointIndex);
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        },
                        dynamicAnimation: {
                            enabled: true,
                            speed: 350
                        }
                    },
                    toolbar: {
                        show: shouldShowControls(),
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                            reset: true
                        },
                        autoSelected: 'zoom'
                    }
                },
                title: {
                    text: 'Lapsed Leads',
                    align: 'center',
                    style: {
                        fontSize: getResponsiveFontSize(15) + 'px',
                        fontWeight: 'bold',
                        fontFamily: undefined,
                        color: '#263238'
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: type === 'line' ? 2 : 0,
                    colors: ['transparent']
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    }
                },
                xaxis: {
                    categories: data.lapslead_data.labels,
                    labels: {
                        style: {
                            fontSize: '12px' // Fixed font size
                        }
                    }
                },
                yaxis: {
                    min: 1,
                    labels: {
                        formatter: function(value) {
                            if (type != "heatmap") {
                                return value;
                            }
                            return value;
                        },
                        style: {
                            fontSize: '12px'
                        }
                    },
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        distributed: false,
                        endingShape: 'rounded'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40,
                    fontSize: getResponsiveFontSize(12) + 'px',
                    formatter: function(seriesName, opts) {
                        const seriesIndex = opts.seriesIndex;
                        const count = filteredDatasets[seriesIndex].data.reduce((acc, val) => Number(acc) + Number(val), 0);
                        return seriesName + " (" + count + ")";
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        var lbl = "";
                        if (w.globals.categoryLabels.length > 0) {
                            lbl = w.globals.categoryLabels[dataPointIndex];
                        } else {
                            lbl = w.globals.labels[dataPointIndex];
                        }
                        let tooltipHtml = '<div class="apexcharts-tooltip-title custom-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                        tooltipHtml += lbl;
                        tooltipHtml += '</div>';
                        let totalValue = 0;
                        w.config.series.forEach((s, index) => {
                            if (s.data[dataPointIndex] > 0) {
                                const color = w.globals.colors[index];
                                tooltipHtml += `<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: ${index + 1}; display: flex;">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-marker" style="background-color: ${color};"></span>`;
                                tooltipHtml += '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">';
                                tooltipHtml += `<div class="apexcharts-tooltip-y-group">`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-label">${s.name}: </span>`;
                                tooltipHtml += `<span class="apexcharts-tooltip-text-y-value">${s.data[dataPointIndex]}</span>`;
                                tooltipHtml += `</div>`;
                                tooltipHtml += '</div></div>';
                                totalValue += Number(s.data[dataPointIndex]);
                            }
                        });
                        return tooltipHtml;
                    }
                }
            };

            switch (type) {
                case 'line':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    delete options.stroke.colors;
                    options.colors = filteredDatasets.map(dataset => dataset.backgroundColor);
                    options.markers = {
                        size: 4,
                        hover: {
                            size: 6
                        }
                    };
                    options.stroke.curve = 'smooth';
                    break;
                case 'bar':
                case 'column':
                    options.plotOptions.bar.columnWidth = '50%';
                    break;
                case 'area':
                    options.stroke.curve = 'smooth';
                    options.fill = {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.5,
                            opacityTo: 0.5,
                            stops: [0, 100]
                        }
                    };
                    break;
                case 'radar':
                    delete options.plotOptions.bar;
                    options.stroke.width = 2;
                    break;
                case 'scatter':
                    options.plotOptions.scatter = {
                        markers: {
                            size: 5
                        }
                    };
                    break;
                case 'heatmap':
                    options.dataLabels = {
                        enabled: true
                    };
                    break;
            }

            var chart = new ApexCharts(chartElement, options);
            chart.render();

            // Add resize event listener
            window.addEventListener('resize', function() {
                chart.updateOptions({
                    chart: {
                        height: chartElement.offsetHeight,
                        width: chartElement.offsetWidth,
                        toolbar: {
                            show: shouldShowControls()
                        }
                    },
                    title: {
                        style: {
                            fontSize: getResponsiveFontSize(15) + 'px'
                        }
                    },
                    legend: {
                        fontSize: getResponsiveFontSize(12) + 'px'
                    }
                    // Removed x and y axis label updates
                });
            });
        } else {
            chartElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>';
        }
    }

    function leadsSummary() {
        var data = localStorage.getItem('chart_data');
        if (data != null && data != "") {
            data = JSON.parse(data);
            console.log(data);

            var html = '';

            $(data.leads_summary).each(function(index, item) {
                html += '<div class="col-md-2 col-sm-4 col-xs-6 border-right" style="margin-bottom: 15px;">';
                html += '<h3 class="bold" style="margin-top: 0;">' + item.total;
                if (item.percent !== undefined) {
                    html += '<span style="font-size: 14px;"> (' + item.percent + '%)' + '</span>';
                }
                html += '</h3>';
                var textColorClass = (item.junk !== undefined || item.lost !== undefined) ? 'text-danger' : '';
                html += '<span style="color:' + item.color + ';" class="' + textColorClass + '">' + item.name + '</span>';
                html += '</div>';
            });

            $('#lead-summary-section').html(html);
        } else {
            $('#lead-summary-section').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;">No data available</div>');
            return;
        }
    }
</script>
<script>
    <?php
    $setting = get_pdf_settings('lead dashboard report');
    if (isset($setting) && ($setting->watermark_type == "image")  && !empty($setting->watermark) && file_exists(protected_file_url_by_path('uploads/pdf_settings/' . $setting->id . '/' . $setting->watermark))) {
        $watermark = site_url('download/preview_image?path=' . protected_file_url_by_path('uploads/pdf_settings/' . $setting->id . '/' . $setting->watermark));
    } else {
        $watermark = $setting->watermark;
    }
    ?>
    var headerCode = `<?= get_pdf_settings('lead dashboard report')->header ?>`;
    var is_headerRepeat = `<?= get_pdf_settings('lead dashboard report')->header_repeat ?>`;
    var footer_text = `<?= get_pdf_settings('lead dashboard report')->footer ?>`;
    var watermark_type = `<?= get_pdf_settings('lead dashboard report')->watermark_type ?>`;
    var watermark = `<?= isset($watermark) ? $watermark : "" ?>`;
    var is_admin = `<?= (is_admin()) ? 1 : 0 ?>`;

    async function captureHtml(htmlCode) {
        const tempContainer = document.createElement('div');
        tempContainer.style.position = 'absolute';
        tempContainer.style.left = '-9999px';
        tempContainer.innerHTML = htmlCode;
        document.body.appendChild(tempContainer);
        const canvas = await html2canvas(tempContainer, {
            scale: 3,
            useCORS: false
        });
        document.body.removeChild(tempContainer);
        return canvas;
    }

    async function addHeader(pdf, pageNumber, htmlCode) {
        const canvas = await captureHtml(htmlCode);
        const imgData = canvas.toDataURL('image/jpeg');
        const imgWidth = pdf.internal.pageSize.getWidth() - 20;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'JPEG', 10, 10, imgWidth, imgHeight);
        const lineY = 10 + imgHeight + 5;
        pdf.setLineWidth(0.5);
        pdf.line(10, lineY, pdf.internal.pageSize.getWidth() - 10, lineY);
        pdf.setFontSize(10);
        pdf.setTextColor(0, 0, 0);
    }

    function addFooter(pdf, pageNumber, footerText) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;
        const lineY = pageHeight - margin - 5;
        pdf.setFont("helvetica", "normal");
        pdf.setLineWidth(0.5);
        pdf.line(margin, lineY, pageWidth - margin, lineY);
        pdf.setFontSize(10);
        pdf.setTextColor(0, 0, 0);
        pdf.text(footerText, margin, pageHeight - margin);
        pdf.text("Page " + pageNumber, pageWidth - margin - pdf.getStringUnitWidth("Page " + pageNumber) * 5, pageHeight - margin);
    }

    function addWatermark(pdf) {
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        pdf.setGState(new pdf.GState({
            opacity: 0.3
        }));
        if (watermark_type === "image") {
            const imgWidth = 100;
            const imgHeight = 30;
            pdf.addImage(watermark, 'JPEG', (pageWidth - imgWidth) / 2, (pageHeight - imgHeight) / 2, imgWidth, imgHeight);
        } else if (watermark_type === "text") {
            pdf.setFontSize(60);
            pdf.setTextColor(150, 150, 150);
            const textX = pageWidth / 3.3;
            const textY = pageHeight / 1.4;
            pdf.text(watermark, textX, textY, {
                angle: 45
            });
        }
        pdf.setGState(new pdf.GState({
            opacity: 1
        }));
    }

    async function captureElement(selector) {
        const element = document.querySelector(selector);
        const canvas = await html2canvas(element, {
            scale: 3,
            useCORS: false
        });
        return canvas;
    }

    async function reportPDF() {
        $('body').append('<div id="loadingOverlay"><div style="display: flex; justify-content: center; align-items: center; margin-top: 30vh; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div></div>');

        const {
            jsPDF
        } = window.jspdf;
        const pdf = new jsPDF();
        let pageNumber = 1;

        const addSection = async (title, chartId, yOffset) => {
            pdf.setFontSize(14);
            pdf.setFont("helvetica", "bold");
            pdf.text(title, 10, yOffset);
            const chartCanvas = await captureElement(chartId);
            const chartImgData = chartCanvas.toDataURL('image/jpeg');
            const chartWidth = pdf.internal.pageSize.getWidth() - 30;
            const chartHeight = (chartCanvas.height * chartWidth) / chartCanvas.width;
            pdf.addImage(chartImgData, 'JPEG', 20, yOffset + 8, chartWidth, chartHeight);
        };

        async function addPageWithHeaderAndFooter(is_first_page = false) {
            if (!is_first_page) {
                pdf.addPage();
                pageNumber++;
            }
            if (is_headerRepeat == "1") {
                await addHeader(pdf, pageNumber, headerCode);
            } else if (pageNumber == 1) {
                await addHeader(pdf, pageNumber, headerCode);
            }
            addFooter(pdf, pageNumber, footer_text);
            if (watermark_type != "no") {
                addWatermark(pdf);
            }
        }


        // Page 1 -------------------------------------------
        await addPageWithHeaderAndFooter(true);

        var pageWidth = pdf.internal.pageSize.getWidth();
        let startTop = 0;

        // Report Title
        var title = "Leads Report";
        pdf.setFontSize(16);
        pdf.setFont("helvetica", "bold");
        pdf.text(title, 10, startTop = 50);


        // Date title
        var dateRange = document.querySelector('#date-range').value || "";
        pdf.setFontSize(12);
        pdf.setFont("helvetica", "normal");
        pdf.text("Date : " + dateRange, pageWidth - 70, startTop = 50);

        var username = "";
        if (is_admin == "1") {
            username = $('#view_assigned :selected').text();
        } else {
            username = `<?= get_staff_full_name() ?>`;
        }

        if (username != "" && username != null) {
            pdf.setFontSize(12);
            pdf.setFont("helvetica", "normal");
            pdf.text("User : " + username, pageWidth - 70, startTop += 5);
        }


        // Page 1 -------------------------------------------
        await addSection("1. Lead Summary", '#lead-summary-section', startTop + 15);
        await addSection("2. Lead Received", '#lead-received-chart', startTop + 123);

        // Page 2 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection(`3. ${$('#view-types :selected').text()}`, '#lead-view-chart', 58);
        await addSection("4. Lead Allocation", '#lead-allocation-chart', 166);

        // Page 3 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection("5. Lead " + $('#lead-send-type :selected').text() + " Sent", '#lead-send-chart', 58);
        await addSection("6. Customer Conversion", '#customer-conversion-chart', 166);

        // Page 4 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection("7. Leads Follow up : " + $('#followup-types :selected').text(), '#leads-followup-chart', 58);
        await addSection("8. Lead Attend Time", '#lead-attend-chart', 166);

        // Page 5 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection("9. Lead Followup Duration", '#lead-followup-duration-chart', 58);
        await addSection("10. Lapsed Lead", '#lapslead-chart', 166);

        // Page 6 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection("11. Vendor Conversion", '#vendor-conversion-chart', 58);
        await addSection("12. " + $('#form-types :selected').text(), '#forms-chart', 166);

        // Page 7 -------------------------------------------
        await addPageWithHeaderAndFooter();
        await addSection("13. Proposals", '#proposal-chart', 58);
        await addSection("14. Agreements", '#contract-chart', 166);

        //Page 8
        await addPageWithHeaderAndFooter();
        await addSection("15. " + $('#sales-type :selected').text(), '#sales-chart', 58);

        if (!$('#lead-transfer-to-other-chart').closest('.charts-section').hasClass('hide')) {
            await addSection("16. Lead Transfer To Others", '#lead-transfer-to-other-chart', 166);

            //Page 9
            await addPageWithHeaderAndFooter();
            await addSection("17. Lead Transferred To Me", '#lead-transfer-to-self-chart', 58);
        }


        var filename = "Lead Report - " + dateRange + ".pdf";
        if (username != "" && username != null) {
            filename = "Lead Report - " + username + " - " + dateRange + ".pdf";
        }
        pdf.save(filename);
        $('#loadingOverlay').remove();
    }
</script>




</body>

</html>