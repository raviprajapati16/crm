<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= site_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
<style>
    .select-container {
        display: inline-block;
    }

    .select-container select {
        width: 11em;
    }


    .header-filter-section {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
    }

    .select-container {
        flex: 1 1 auto;
        /* Allows elements to shrink and grow as needed */
        min-width: 150px;
        /* Sets a minimum width for each dropdown */
    }

    .dataTables_filter {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .dt-info-text {
        margin-right: 10px;
        white-space: nowrap;
        font-size: 15px;
    }


    /* Add these styles to your stylesheet */
    .stats-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .mtop20 {
        margin-top: 20px;
    }

    .mtop30 {
        margin-top: 30px;
    }

    .mright10 {
        margin-right: 10px;
    }

    .stat-box {
        border-radius: 4px;
        padding: 20px;
        position: relative;
        margin-bottom: 20px;
        min-height: 120px;
        display: flex;
        align-items: center;
        color: #fff;
    }

    .stat-icon {
        padding-right: 15px;
        border-right: 1px solid rgba(255, 255, 255, 0.2);
        margin-right: 15px;
    }

    .stat-content {
        flex-grow: 1;
    }

    .stat-number {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        color: #fff;
    }

    .stat-text {
        margin: 5px 0;
        opacity: 0.9;
        font-size: 16px;
        color: #fff;
    }

    .stat-percentage {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 14px;
        color: #fff;
        font-weight: 500;
    }

    .bg-primary {
        background-color: #337ab7;
    }

    .bg-success {
        background-color: #5cb85c;
    }

    .bg-info {
        background-color: #5bc0de;
    }

    .bg-warning {
        background-color: #f0ad4e;
    }

    .bg-danger {
        background-color: #d9534f;
    }

    /* Bootstrap 3.4.0 specific overrides */
    .panel-default {
        border-color: #ddd;
    }

    .panel-default>.panel-heading {
        background-color: #f5f5f5;
        border-color: #ddd;
    }

    .btn-group>.btn-sm {
        padding: 5px 10px;
        font-size: 12px;
        line-height: 1.5;
        border-radius: 3px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row mtop30">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="stats-header">
                            <div class="_buttons mright10">
                                <a href="<?= admin_url('email_campaigns') ?>" class="btn btn-info pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
                            </div>
                            <h4 class="no-margin">Email Campaign Analytics</h4>
                        </div>
                        <hr class="hr-panel-heading" />
                        <!-- Main Stats Cards -->
                        <div class="row">
                            <div class="col-md-2">
                                <div class="stat-box bg-primary">
                                    <div class="stat-icon">
                                        <i class="fa fa-envelope fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['total_emails'] ?></h3>
                                        <p class="stat-text">Total Emails</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box bg-warning">
                                    <div class="stat-icon">
                                        <i class="fa fa-hourglass-start fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['queue_count'] ?></h3>
                                        <p class="stat-text">In Queue</p>
                                        <div class="stat-percentage">
                                            <span><?= $stats['queue_percentage'] ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box bg-success">
                                    <div class="stat-icon">
                                        <i class="fa fa-paper-plane fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['sent_count'] ?></h3>
                                        <p class="stat-text">Emails Sent</p>
                                        <div class="stat-percentage">
                                            <span><?= $stats['sent_percentage'] ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box bg-danger">
                                    <div class="stat-icon">
                                        <i class="fa fa-times-circle fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['failed_count'] ?></h3>
                                        <p class="stat-text">Failed To Send</p>
                                        <div class="stat-percentage">
                                            <span><?= $stats['failed_percentage'] ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box bg-success">
                                    <div class="stat-icon">
                                        <i class="fa fa-envelope-open fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['opened_count'] ?></h3>
                                        <p class="stat-text">Opened Emails</p>
                                        <div class="stat-percentage">
                                            <span><?= $stats['opened_percentage'] ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="stat-box bg-danger">
                                    <div class="stat-icon">
                                        <i class="fa fa-envelope fa-2x"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 class="stat-number"><?= $stats['not_opened_count'] ?></h3>
                                        <p class="stat-text">Not Opened</p>
                                        <div class="stat-percentage">
                                            <span><?= $stats['not_opened_percentage'] ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modify the existing Chart Section to include all three charts -->
                        <div class="row mtop20">
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Email Performance Trend</h3>
                                    </div>
                                    <div class="panel-body" style="height:347px;">
                                        <canvas id="deliveryChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Delivery Status</h3>
                                    </div>
                                    <div class="panel-body">
                                        <canvas id="deliveryStatusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Open Status</h3>
                                    </div>
                                    <div class="panel-body">
                                        <canvas id="openStatusChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Email Statistics By Sender</h3>
                                    </div>
                                    <div class="panel-body">
                                        <canvas id="emailSenderChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="d-flex justify-content-between align-items-center header-filter-section">
                            <!-- Title on the left -->
                            <h4 class="no-margin">Campaign Results</h4>

                            <!-- Filters on the right -->
                            <div class="d-flex pull-right">
                                <div class="select-container ml-3">
                                    <select id="status-filter" class="form-control">
                                        <option value="" selected>Select Status</option>
                                        <option value="queue">Queue</option>
                                        <option value="sent">Sent</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </div>
                                <div class="select-container ml-3">
                                    <select id="rel-type-filter" class="form-control">
                                        <option value="" selected>Select Relation</option>
                                        <option value="lead">Lead</option>
                                        <option value="client_contact">Customer</option>
                                        <option value="staff">Staff</option>
                                        <option value="email_list">Email List</option>
                                    </select>
                                </div>
                                <div class="select-container ml-3">
                                    <select id="email-status-filter" class="form-control">
                                        <option value="" selected>Select Email Status</option>
                                        <option value="open">Open</option>
                                        <option value="not-open">Not Open</option>
                                    </select>
                                </div>
                                <div class="select-container ml-3">
                                    <input type="hidden" id="sent_from_type" value="" />
                                    <select name="sent_from" id="sent_from" class="form-control">
                                        <option value="" selected>Select Sent From</option>
                                        <?php if (!empty($mails)): ?>
                                            <?php foreach ($mails as $mail) {

                                            ?>
                                                <option data-type="staff" value="<?= $mail->staffid; ?>"><?= $mail->webmail_email; ?> (Staff)</option>
                                            <?php } ?>
                                        <?php endif; ?>
                                        <?php if (!empty($custom_mails)): ?>
                                            <?php foreach ($custom_mails as $mail): ?>
                                                <option data-type="custom_email" value="<?= $mail['id']; ?>"><?= $mail['email']; ?> (Custom)</option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="select-container ml-3" id="datepicker-wrapper">
                                    <input type="text" name="daterange" id="date-range" class="form-control" placeholder="Select Send Date" autocomplete="off">
                                </div>

                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('Name'),
                            _l('Email'),
                            _l('Status'),
                            _l('Send From'),
                            _l('Email Send Date & Time'),
                            _l('Email Open Date & Time'),
                        ), 'email-campaigns-result'); ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="<?= site_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script>
    $(function() {
        var fnServerParams = {
            "status": '#status-filter',
            "rel_type": '#rel-type-filter',
            "sent_from": '#sent_from',
            "sent_from_type": '#sent_from_type',
            "email_status": '#email-status-filter',
            "date_range": '#date-range',
        }
        table = initDataTableNew('.table-email-campaigns-result', window.location.href, [0], [0], fnServerParams);
        $('#status-filter,#rel-type-filter').on('change', function() {
            if (table) {
                table.draw();
            }
        });
        $('#sent_from').on('change', function() {
            var type = $('#sent_from option:selected').attr('data-type');
            $('#sent_from_type').val(type);
            if (table) {
                table.draw();
            }
        });
        $('#email-status-filter').on('change', function() {
            if (table) {
                table.draw();
            }
        });

        $('#date-range').daterangepicker({
            parentEl: '#datepicker-wrapper',
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear'
            },
            autoUpdateInput: false,
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


        $(window).on('scroll', function() {
            const picker = $input.data('daterangepicker');
            if (picker && picker.container.is(':visible')) {
                picker.move(); // <-- this repositions the calendar
            }
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            if (table) {
                table.draw();
            }
        });

        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            if (table) {
                table.draw();
            }
        });
    });

    function initDataTableNew(selector, url, notsearchable, notsortable, fnserverparams, defaultorder) {
        var table = typeof(selector) == 'string' ? $("body").find('table' + selector) : selector;

        if (table.length === 0) {
            return false;
        }

        fnserverparams = (fnserverparams == 'undefined' || typeof(fnserverparams) == 'undefined') ? [] : fnserverparams;

        // If not order is passed order by the first column
        if (typeof(defaultorder) == 'undefined') {
            defaultorder = [
                [0, 'asc']
            ];
        } else {
            if (defaultorder.length === 1) {
                defaultorder = [defaultorder];
            }
        }

        var user_table_default_order = table.attr('data-default-order');

        if (!empty(user_table_default_order)) {
            var tmp_new_default_order = JSON.parse(user_table_default_order);
            var new_defaultorder = [];
            for (var i in tmp_new_default_order) {
                // If the order index do not exists will throw errors
                if (table.find('thead th:eq(' + tmp_new_default_order[i][0] + ')').length > 0) {
                    new_defaultorder.push(tmp_new_default_order[i]);
                }
            }
            if (new_defaultorder.length > 0) {
                defaultorder = new_defaultorder;
            }
        }

        var length_options = [10, 25, 50, 100];
        var length_options_names = [10, 25, 50, 100];

        app.options.tables_pagination_limit = parseFloat(app.options.tables_pagination_limit);

        if ($.inArray(app.options.tables_pagination_limit, length_options) == -1) {
            length_options.push(app.options.tables_pagination_limit);
            length_options_names.push(app.options.tables_pagination_limit);
        }

        length_options.sort(function(a, b) {
            return a - b;
        });
        length_options_names.sort(function(a, b) {
            return a - b;
        });

        length_options.push(-1);
        length_options_names.push(app.lang.dt_length_menu_all);

        var dtSettings = {
            "language": app.lang.datatables,
            "processing": true,
            "retrieve": true,
            "serverSide": true,
            'paginate': true,
            'searchDelay': 750,
            "bDeferRender": true,
            "responsive": true,
            "autoWidth": false,
            dom: "<'row'<'col-md-7'lB><'col-md-5'f>>rt<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
            "pageLength": app.options.tables_pagination_limit,
            "lengthMenu": [length_options, length_options_names],
            "columnDefs": [{
                "searchable": false,
                "targets": notsearchable,
            }, {
                "sortable": false,
                "targets": notsortable
            }],
            "fnDrawCallback": function(oSettings) {
                _table_jump_to_page(this, oSettings);
                if (oSettings.aoData.length === 0) {
                    $(oSettings.nTableWrapper).addClass('app_dt_empty');
                } else {
                    $(oSettings.nTableWrapper).removeClass('app_dt_empty');
                }

                // Update header info
                var start = oSettings._iDisplayStart + 1;
                var end = oSettings._iDisplayStart + oSettings._iDisplayLength;
                var total = oSettings.fnRecordsTotal();
                end = end > total ? total : end;

                var headerInfo = 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';

                // Update the info text in the filter label
                var $filterDiv = $(this).closest('.dataTables_wrapper').find('.dataTables_filter');
                var $infoSpan = $filterDiv.find('.dt-info-text');
                if ($infoSpan.length === 0) {
                    $filterDiv.prepend('<span class="dt-info-text">' + headerInfo + '</span>');
                } else {
                    $infoSpan.html(headerInfo);
                }
            },
            "fnCreatedRow": function(nRow, aData, iDataIndex) {
                // If tooltips found
                $(nRow).attr('data-title', aData.Data_Title);
                $(nRow).attr('data-toggle', aData.Data_Toggle);
            },
            "initComplete": function(settings, json) {
                var t = this;
                var $btnReload = $('.btn-dt-reload');
                $btnReload.attr('data-toggle', 'tooltip');
                $btnReload.attr('title', app.lang.dt_button_reload);

                var $btnColVis = $('.dt-column-visibility');
                $btnColVis.attr('data-toggle', 'tooltip');
                $btnColVis.attr('title', app.lang.dt_button_column_visibility);

                if (t.hasClass('scroll-responsive') || app.options.scroll_responsive_tables == 1) {
                    t.wrap('<div class="table-responsive"></div>');
                }

                var dtEmpty = t.find('.dataTables_empty');
                if (dtEmpty.length) {
                    dtEmpty.attr('colspan', t.find('thead th').length);
                }

                // Hide mass selection because causing issue on small devices
                if (is_mobile() && $(window).width() < 400 && t.find('tbody td:first-child input[type="checkbox"]').length > 0) {
                    t.DataTable().column(0).visible(false, false).columns.adjust();
                    $("a[data-target*='bulk_actions']").addClass('hide');
                }

                t.parents('.table-loading').removeClass('table-loading');
                t.removeClass('dt-table-loading');
                var th_last_child = t.find('thead th:last-child');
                var th_first_child = t.find('thead th:first-child');
                if (th_last_child.text().trim() == app.lang.options) {
                    th_last_child.addClass('not-export');
                }
                if (th_first_child.find('input[type="checkbox"]').length > 0) {
                    th_first_child.addClass('not-export');
                }
                mainWrapperHeightFix();
            },
            "order": defaultorder,
            "ajax": {
                "url": url,
                "type": "POST",
                "data": function(d) {
                    if (typeof(csrfData) !== 'undefined') {
                        d[csrfData['token_name']] = csrfData['hash'];
                    }
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                    if (table.attr('data-last-order-identifier')) {
                        d['last_order_identifier'] = table.attr('data-last-order-identifier');
                    }
                }
            },
            buttons: get_datatable_buttons(table),
        };

        if (table.hasClass('scroll-responsive') || app.options.scroll_responsive_tables == 1) {
            dtSettings.responsive = false;
        }

        table = table.dataTable(dtSettings);
        var tableApi = table.DataTable();

        var hiddenHeadings = table.find('th.not_visible');
        var hiddenIndexes = [];

        $.each(hiddenHeadings, function() {
            hiddenIndexes.push(this.cellIndex);
        });

        setTimeout(function() {
            for (var i in hiddenIndexes) {
                tableApi.columns(hiddenIndexes[i]).visible(false, false).columns.adjust();
            }
        }, 10);

        if (table.hasClass('customizable-table')) {
            var tableToggleAbleHeadings = table.find('th.toggleable');
            var invisible = $('#hidden-columns-' + table.attr('id'));
            try {
                invisible = JSON.parse(invisible.text());
            } catch (err) {
                invisible = [];
            }

            $.each(tableToggleAbleHeadings, function() {
                var cID = $(this).attr('id');
                if ($.inArray(cID, invisible) > -1) {
                    tableApi.column('#' + cID).visible(false);
                }
            });
        }

        // Fix for hidden tables colspan not correct if the table is empty
        if (table.is(':hidden')) {
            table.find('.dataTables_empty').attr('colspan', table.find('thead th').length);
        }

        table.on('preXhr.dt', function(e, settings, data) {
            if (settings.jqXHR) settings.jqXHR.abort();
        });

        return tableApi;
    }
</script>
<script>
    $(function() {
        // Delivery Chart (Line Chart)
        var datewiseData = <?= json_encode($stats_by_date) ?>;

        if (!datewiseData || datewiseData.length === 0) {
            $('#deliveryChart').parent().html('<div class="text-center text-muted mt-4">No data available for the selected period</div>');
            return;
        }

        var labels = datewiseData.map(item => {
            var date = new Date(item.date);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        });

        var totalData = datewiseData.map(item => parseInt(item.sent_count));
        var openedData = datewiseData.map(item => parseInt(item.opened_count));

        var totalSum = totalData.reduce((a, b) => a + b, 0);
        var openedSum = openedData.reduce((a, b) => a + b, 0);

        var ctx = document.getElementById('deliveryChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: `Total Sent (${totalSum})`,
                        data: totalData,
                        borderColor: '#337ab7',
                        backgroundColor: 'rgba(51, 122, 183, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#337ab7',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: `Opened (${openedSum})`,
                        data: openedData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label.split(' (')[0]}: ${parseInt(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return parseInt(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });


        // Status wise chart
        var statusWiseData = <?= json_encode($stats_by_status) ?>;

        if (!statusWiseData || (!statusWiseData.sent_count && !statusWiseData.failed_count)) {
            $('#deliveryStatusChart').parent().html('<div class="text-center text-muted mt-4">No delivery status data available</div>');
            return;
        }

        var sentCount = parseInt(statusWiseData.sent_count);
        var failedCount = parseInt(statusWiseData.failed_count);
        var total = sentCount + failedCount;

        var deliveryCtx = document.getElementById('deliveryStatusChart').getContext('2d');
        var deliveryStatusChart = new Chart(deliveryCtx, {
            type: 'pie',
            data: {
                labels: [`Sent (${sentCount})`, `Failed (${failedCount})`],
                datasets: [{
                    data: [sentCount, failedCount],
                    backgroundColor: ['#5cb85c', '#d9534f'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: `${label} (${((data.datasets[0].data[i] / total) * 100).toFixed(2)}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = parseInt(context.raw);
                                const percentage = ((value / total) * 100).toFixed(2);
                                return `${context.label.split(' (')[0]}: ${value} (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        formatter: function(value, context) {
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${value} (${percentage}%)`;
                        },
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        align: 'center',
                        anchor: 'center'
                    }
                }
            },
            plugins: [ChartDataLabels]
        });


        // Open Status Chart (Pie)
        var openStatusWiseData = <?= json_encode($stats_by_open_status) ?>;

        if (!openStatusWiseData || (!openStatusWiseData.opened_count && !openStatusWiseData.not_opened_count)) {
            $('#openStatusChart').parent().html('<div class="text-center text-muted mt-4">No open status data available</div>');
            return;
        }

        var openedCount = parseInt(openStatusWiseData.opened_count);
        var notOpenedCount = parseInt(openStatusWiseData.not_opened_count);
        var openTotal = openedCount + notOpenedCount;

        var openCtx = document.getElementById('openStatusChart').getContext('2d');
        var openStatusChart = new Chart(openCtx, {
            type: 'pie',
            data: {
                labels: [`Opened (${openedCount})`, `Not Opened (${notOpenedCount})`],
                datasets: [{
                    data: [openedCount, notOpenedCount],
                    backgroundColor: ['#5bc0de', '#f0ad4e'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: `${label} (${((data.datasets[0].data[i] / openTotal) * 100).toFixed(2)}%)`,
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = parseInt(context.raw);
                                const percentage = ((value / openTotal) * 100).toFixed(2);
                                return `${context.label.split(' (')[0]}: ${value} (${percentage}%)`;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        formatter: function(value, context) {
                            const percentage = ((value / openTotal) * 100).toFixed(2);
                            return `${value} (${percentage}%)`;
                        },
                        font: {
                            weight: 'bold',
                            size: 14
                        },
                        align: 'center',
                        anchor: 'center'
                    }
                }
            },
            plugins: [ChartDataLabels]
        });


        // Sender chart
        var senderWiseData = <?= json_encode($stats_by_sender) ?>;
        if (!senderWiseData || senderWiseData.length === 0) {
            $('#emailSenderChart').parent().html('<div class="text-center text-muted mt-4">No data available for the selected period</div>');
            return;
        }

        var labels = senderWiseData.map(item => {
            return item.email;
        });

        var sentData = senderWiseData.map(item => {
            return parseInt(item.sent_count);
        });

        var failedData = senderWiseData.map(item => {
            return parseInt(item.failed_count);
        });

        var totalSent = sentData.reduce((a, b) => a + b, 0);
        var totalFailed = failedData.reduce((a, b) => a + b, 0);

        var ctx = document.getElementById('emailSenderChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                        label: `Total Sent (${totalSent})`,
                        data: sentData,
                        borderColor: '#337ab7',
                        backgroundColor: 'rgba(51, 122, 183, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#337ab7',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: `Total Failed (${totalFailed})`,
                        data: failedData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label.split(' (')[0]}: ${parseInt(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return parseInt(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

    });
</script>
</body>

</html>