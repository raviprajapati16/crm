<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Fetch Leads</h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $form_attributes = ['id' => 'fetch_api_form'];
                        echo form_open($this->uri->uri_string(), $form_attributes); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="api_type">API Source</label>
                                <div class="form-group">
                                    <select id="api_type" name="api_type" class="selectpicker" data-live-search="true" data-none-selected-text="Select API Soruce" data-width="100%">
                                        <option value="india-mart" selected>Indiamart</option>
                                        <option value="trade-india">Tradeindia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('start_date', 'start_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_date_input('end_date', 'end_date'); ?>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div class="input-group">
                                        <input type="reset" name="reset" class="btn btn-dafault" value="Reset"> &nbsp;
                                        <button type="submit" id="submit_btn" class="btn btn-info" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading">Fetch Leads</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <div class="text-danger" id="error"></div>
                    </div>
                </div>
                <div class="row" id="instructions">
                    <div class="col-md-12">

                    </div>
                </div>

                <div class="panel_s hide" id="response">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title">Search Leads</h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $form_attributes = ['id' => 'import_leads_form'];
                        echo form_open($this->uri->uri_string(), $form_attributes); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                echo render_leads_status_select($statuses, '', 'lead_add_edit_status');
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                echo render_leads_source_select($sources, [], 'lead_add_edit_source');
                                ?>
                            </div>
                            <input type="hidden" name="api_type_result" id="api_type_result" value="" />
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" id="import_btn" class="btn btn-info" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Importing"><?php echo _l('import'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table id="leads_table" width="100%" class="table table-responsive">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact No</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Import Lead to CRM Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    var FETCH_URL = "<?= admin_url("leadsource_apis/get_leads"); ?>";
    var IMPORT_URL = "<?= admin_url("leadsource_apis/import_leads"); ?>";

    $("#fetch_api_form").on('submit', function(e) {
        $("#response,#instructions").addClass('hide');
        $("#error").html('');
        $('#api_type_result').val('');
        $("#submit_btn").button('loading');
        e.preventDefault();
        $.ajax({
            url: FETCH_URL,
            type: 'POST',
            data: new FormData(this),
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(resJSON) {
                $("#submit_btn").button('reset');
                if (resJSON.status) {
                    $("#head_title").html(resJSON.head_title);
                    $('#leads_table').DataTable().destroy();
                    var api_type = $('#api_type').val()
                    $('#api_type_result').val(api_type);
                    $('#status').val('2').selectpicker('refresh');
                    if (api_type == "india-mart") {
                        $('#source').val('3').selectpicker('refresh');
                    } else {
                        $('#source').val('15').selectpicker('refresh');
                    }
                    $("#leads_table tbody").html(resJSON.table_rows);
                    initDataTableInline('#leads_table');
                    $("#response").removeClass('hide');
                } else {
                    $('#error').html(resJSON.message);
                }
            }
        });
    });

    $("#select_all").on('click', function(e) {
        var is_checked = $(this).prop('checked');
        $("#leads_table .import_id").prop('checked', is_checked);
    });

    $("#import_leads_form").on('submit', function(e) {
        e.preventDefault();
        var import_leads_count = $("#leads_table .import_id").filter(':checked').length;
        var has_error = false;
        if (!$(this).valid()) {
            $("#import_btn").button('reset');
            has_error = true;
            return false;
        }
        if (import_leads_count <= 0) {
            has_error = true;
            alert_float('danger', 'Please select leads to import!', 5000);
            resetButton('#import_btn');
        }
        if (!has_error) {
            $.ajax({
                url: IMPORT_URL,
                type: 'POST',
                data: new FormData(this),
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(resJSON) {
                    resetButton('#import_btn');
                    if (resJSON.status) {
                        if (resJSON.imported_leads.length > 0) {
                            $.each(resJSON.imported_leads, function(i, lead_id) {
                                var tr_selector = "#lead_" + lead_id;
                                $('#leads_table').DataTable().row($(tr_selector)).remove().draw();
                            });
                        }
                        alert_float('success', resJSON.message);
                    } else {
                        $('#error').html(resJSON.message);
                    }
                }
            });
        }
    });

    var validationObject = {
        source: 'required',
        status: 'required',
    };
    appValidateForm($('#import_leads_form'), validationObject);
    initDataTableInline('#leads_table');

    function resetButton(selector) {
        setTimeout(function() {
            $(selector).button('reset');
        }, 500);
    }
</script>