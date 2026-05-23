<style>
    td {
        vertical-align: middle !important;
    }

    .daterangepicker {
        z-index: 11111 !important;
    }
</style>
<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?= $staff->firstname ?> <?= $staff->lastname ?></h4>
        </div>
        <div class="modal-body goals_details">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <?php
                        if ($goal->goal_duration_type == 1) {
                        ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date-range">Date Range</label>
                                    <input type="text" name="daterange" id="date-range" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                        <?php
                        if (in_array($goal->goal_duration_type, [2, 3, 4, 5])) {
                        ?>
                            <div class="col-md-2">
                                <label for="years_filter">Year</label>
                                <div class="form-group">
                                    <select id="years_filter" name="years_filter" class="selectpicker" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                                        <?php
                                        foreach (get_year_list(date("Y-m-d", strtotime($goal->created_at))) as $year) {
                                        ?>
                                            <option value="<?= $year['title'] ?>" ($year['title']=="current" ? "selected" : "" )><?= $year['title'] ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <?php
                    if (has_permission('goals', '', 'edit')) {
                        render_datatable(array(
                            "Goal Period (" . get_goal_duration_title_by_key($goal->goal_duration_type) . ")",
                            "Achieved / Target ",
                            "Progress",
                            "Status",
                            "Action",
                        ), 'staff-goals-listing');
                    } else {
                        render_datatable(array(
                            "Goal Period (" . get_goal_duration_title_by_key($goal->goal_duration_type) . ")",
                            "Achieved / Target ",
                            "Progress",
                            "Status",
                        ), 'staff-goals-listing');
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="<?= site_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
<script src="<?= site_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script>
    $('#date-range').daterangepicker({
        locale: {
            format: 'DD-MM-YYYY'
        },
        opens: 'left',
        showCustomRangeLabel: true,
        alwaysShowCalendars: true,
        startDate: moment().startOf('month'),
        endDate: moment(),
        minDate: moment("<?= date("Y-m-d", strtotime($goal->created_at))  ?>", 'YYYY-MM-DD'),
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

    var payloadData = [];
    payloadData['year'] = "#years_filter";
    payloadData['date'] = "#date-range";
    app.options.tables_pagination_limit = 10;
    var goalDetailstable = initDataTableCustom('.table-staff-goals-listing', '<?= admin_url("goals/staff_details_table/$goal_id/$staff_id") ?>', [], [0, 1, 2, 3], payloadData)
    $('.table-staff-goals-listing').DataTable().on('draw', function() {
        var rows = $('.table-staff-goals-listing').find('tr');
        $.each(rows, function() {
            var td = $(this).find('td').eq(2);
            var percent = $(td).find('input[name="percent"]').val();
            $(td).find('.goal-progress').circleProgress({
                value: percent,
                size: 45,
                animation: false,
                fill: {
                    gradient: ["#28b8da", "#059DC1"]
                }
            })
        })
    })

    $(document).on('change', '#date-range', function() {
        if (goalDetailstable) {
            goalDetailstable.draw();
        }
    });

    $(document).on('change', '#years_filter', function() {
        if (goalDetailstable) {
            goalDetailstable.draw();
        }
    });

    $(document).on('click', '.notify-btn', function() {
        var data = {};
        data.status = $(this).attr("data-status");
        data.goal_id = "<?= $goal_id ?>";
        data.staff_id = "<?= $staff_id ?>";
        data.start_date = $(this).attr("data-start-date");
        data.end_date = $(this).attr("data-end-date");
        data.goal_duration_title = $(this).attr("data-duration-title");

        $.ajax({
            url: "<?php echo admin_url('goals/notify') ?>",
            method: "POST",
            data: data,
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert_float('success', result.message);
            } else {
                alert_float('danger', result.message);
            }
        });

    });

    function initDataTableCustom(selector, url, notsearchable, notsortable, fnserverparams, defaultorder) {
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
            dom: "<'row'><'row'<'col-md-7'lB><'col-md-5'>>rt<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
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