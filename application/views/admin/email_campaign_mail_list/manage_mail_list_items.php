<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
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
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?= admin_url('email_campaign_mail_list') ?>" class="btn btn-info pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
                        </div>
                        <?php if (has_permission('email_campaigns', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="javascript:;" onclick="openMailListItemModal()" class="btn btn-info pull-right display-block">New Email</a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('Name'),
                            _l('Email'),
                        ), 'mail-lists-items'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="maillist_model" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('email_campaign_mail_list/save_item'), array("id" => "mailListItemForm")); ?>
        <input type="hidden" id="maillistitem_id" name="id" form="mailListItemForm" value="" />
        <input type="hidden" id="list_id" name="list_id" form="mailListItemForm" value="<?= $list_id ?>" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create New Email</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" form="mailListItemForm" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" form="mailListItemForm" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="mailListItemForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        initDataTableNew('.table-mail-lists-items', window.location.href, [0], [0]);

        $('#mailListItemForm').appFormValidator({
            rules: {
                email: 'required',
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                formGroup.append(error);
            },
            submitHandler: function(form) {
                var $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true);
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                form.submit();
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

    function openMailListItemModal(id = "") {
        var parent = $('#maillist_model');
        if (id == "") {
            parent.find('#name').val("");
            parent.find('#title').val("");
            parent.find('#maillistitem_id').val("");
            parent.find('.modal-title').html("Create New Email");
            parent.modal('show');
        } else {
            var element = $('a[data-id="' + id + '"]');
            parent.find('#name').val(element.attr('data-name'));
            parent.find('#email').val(element.attr('data-email'));
            parent.find('#maillistitem_id').val(element.attr('data-id'));
            parent.find('.modal-title').html("Edit Email");
            parent.modal('show');
        }
    }
</script>
</body>

</html>