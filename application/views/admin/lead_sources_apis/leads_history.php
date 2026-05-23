<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Leads History</h4>
                        <hr class="hr-panel-heading" />
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
                        </div>
                    </div>
                </div>
                <div class="row" id="instructions">
                    <div class="col-md-12">

                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title">Leads History</h4>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" id="import_btn" class="btn btn-info" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Importing"><?php echo _l('import'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php render_datatable(array(
                            '<input type="checkbox" id="select_all">',
                            'Name',
                            'Email',
                            'Contact No',
                            'Subject',
                            'Message',
                            'Leads Module Import Status',
                            'Received Date',
                        ), 'leads-history'); ?>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    var IMPORT_URL = "<?= admin_url("leadsource_apis/import_leads"); ?>";
    var fnServerParams = {
        "api_type": '#api_type',
    }
    var table = initDataTable('.table-leads-history', "<?= admin_url('leadsource_apis/leads_history') ?>", false, [0], fnServerParams,[7,'desc']);
    $("#api_type").on('change', function(e) {
        if (table) {
            table.draw();
        }
        $('#status').val('2').selectpicker('refresh');
        if ($(this).val() == "india-mart") {
            $('#source').val('3').selectpicker('refresh');
        } else {
            $('#source').val('15').selectpicker('refresh');
        }
    });
    $("#api_type").trigger('change');

    $("#select_all").on('click', function(e) {
        var is_checked = $(this).prop('checked');
        $(".import_id").prop('checked', is_checked);
    });

    $("#import_leads_form").on('submit', function(e) {
        e.preventDefault();
        var import_leads_count = $(".import_id").filter(':checked').length;
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
        var formData = new FormData(this);
        formData.append('api_type_result', $("#api_type").val())
        if (!has_error) {
            $.ajax({
                url: IMPORT_URL,
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(resJSON) {
                    resetButton('#import_btn');
                    if (resJSON.status) {
                        if (resJSON.imported_leads.length > 0) {
                            $.each(resJSON.imported_leads, function(i, lead_id) {
                                var tr_selector = "#lead_" + lead_id;
                                $('table').DataTable().row($(tr_selector)).remove().draw();
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

    function resetButton(selector) {
        setTimeout(function() {
            $(selector).button('reset');
        }, 500);
    }
</script>