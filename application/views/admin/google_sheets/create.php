<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('google_sheets/index'); ?>" class="btn btn-info mright5 pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h4 class="no-margin" id="head_title">Create Google Sheets</h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('google_sheets/create'), array('id' => 'sheet_form')); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_input('sheet_title', 'Sheet Title', "", 'text', []); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('sheet_url', 'Google Sheet URL', "", 'text', []); ?>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="fetch_sheet" class="btn btn-primary" style="margin-top: 25px;">Fetch Columns</button>
                                <button type="button" id="reset_sheet" class="btn btn-danger hide" style="margin-top: 25px;">Reset</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="sheet_error" class="alert alert-danger" style="display: none;"></div>
                            </div>
                        </div>

                        <div id="column_mapping_container" style="display: none;">
                            <hr class="hr-panel-heading" />
                            <h4>Default Settings</h4>
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
                                    <?php
                                    $staff_users = [];
                                    foreach ($staff as $user) {
                                        if (is_staff_in_sales_department($user['id'])) {
                                            $staff_users[] = $user;
                                        }
                                    }
                                    echo render_select('assignee', $staff_users, array('staffid', array('firstname', 'lastname')), 'Assignee', '', array('data-width' => '100%', 'data-size' => 6, 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot'); ?>
                                </div>
                            </div>
                            <hr class="hr-panel-heading" />
                            <h4>Column Mapping</h4>
                            <p>Map your Google Sheet columns to lead fields</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Lead Fields</th>
                                                    <th>Google Sheet Columns</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="name" readonly></td>
                                                    <td>
                                                        <select name="map[name]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="email" readonly></td>
                                                    <td>
                                                        <select name="map[email]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="phonenumber" readonly></td>
                                                    <td>
                                                        <select name="map[phonenumber]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="company" readonly></td>
                                                    <td>
                                                        <select name="map[company]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="address" readonly></td>
                                                    <td>
                                                        <select name="map[address]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="country" readonly></td>
                                                    <td>
                                                        <select name="map[country]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="state" readonly></td>
                                                    <td>
                                                        <select name="map[state]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Lead Fields</th>
                                                    <th>Google Sheet Columns</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="city" readonly></td>
                                                    <td>
                                                        <select name="map[city]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="zipcode" readonly></td>
                                                    <td>
                                                        <select name="map[zipcode]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="product" readonly></td>
                                                    <td>
                                                        <select name="map[product]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="website" readonly></td>
                                                    <td>
                                                        <select name="map[website]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" class="form-control" value="description" readonly></td>
                                                    <td>
                                                        <select name="map[description]" class="form-control column-select">
                                                            <option value="">-- Select Column --</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <hr class="hr-panel-heading" />
                                    <button type="submit" class="btn btn-success pull-right">Save</button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script type="text/javascript">
        var table = initDataTable('.table-google-sheets', "<?= admin_url('google_sheets/index') ?>", false, [0, 1, 2]);

        $('#fetch_sheet').on('click', function() {
            var sheetUrl = $('#sheet_url').val();
            $('#sheet_error').hide();
            $('#column_mapping_container').hide();
            $('.column-select').html('<option value="">-- Select Column --</option>');

            if (!sheetUrl) {
                $('#sheet_error').html('Please enter a Google Sheet URL').show();
                return;
            }
            if (!sheetUrl.includes('docs.google.com/spreadsheets') || !sheetUrl.includes('pub?output=csv')) {
                $('#sheet_error').html('Invalid Google Sheet URL. URL must be a published CSV format.').show();
                return;
            }

            $.ajax({
                url: "<?= admin_url('google_sheets/fecth_sheet') ?>",
                type: 'POST',
                data: {
                    url: sheetUrl,
                    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#fetch_sheet').html('<i class="fa fa-spinner fa-spin"></i> Fetching...').prop('disabled', true);
                },
                success: function(response) {
                    $('#fetch_sheet').html('Fetch Columns').prop('disabled', false);
                    if (response.success) {
                        var columns = response.columns;
                        var optionsHtml = '<option value="">-- Select Column --</option>';
                        for (var i = 0; i < columns.length; i++) {
                            optionsHtml += '<option value="' + columns[i] + '">' + columns[i] + '</option>';
                        }
                        $('.column-select').html(optionsHtml);
                        $('#column_mapping_container').show();
                        $('#sheet_url').prop('readonly', true);
                        $('#fetch_sheet').addClass('hide');
                        $('#reset_sheet').removeClass('hide');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'all_columns',
                            value: JSON.stringify(columns)
                        }).appendTo('#sheet_form');
                    } else {
                        $('#sheet_error').html(response.message).show();
                    }
                },
                error: function() {
                    $('#fetch_sheet').html('Fetch Columns').prop('disabled', false);
                    $('#sheet_error').html('Failed to connect to the server. Please try again.').show();
                }
            });
        });

        $(document).on('click', '#reset_sheet', function() {
            $('#sheet_url').prop('readonly', false).val('');
            $('#column_mapping_container').hide();
            $('#column_mapping_container .column-select').val('');
            $('#column_mapping_container #status, #column_mapping_container #source, #column_mapping_container #assignee')
                .find('option:selected')
                .prop('selected', false)
                .end()
                .selectpicker('refresh');
            $('input[name="all_columns"]').remove();
            $('#fetch_sheet').removeClass('hide');
            $('#reset_sheet').addClass('hide');
        });

        $("#sheet_form").appFormValidator({
            rules: {
                sheet_title: 'required',
                sheet_url: 'required',
                status: 'required',
                source: 'required',
                assignee: 'required',
                'map[name]': 'required'
            },
            messages: {
                'map[name]': 'Name field mapping is required'
            },
            submitHandler: function(form) {
                $('.table-responsive').find('.error').remove();
                var emailMapped = $('select[name="map[email]"]').val();
                var phoneMapped = $('select[name="map[phonenumber]"]').val();
                if (!emailMapped && !phoneMapped) {
                    $('.table-responsive').find('.error').remove();
                    $('.table-responsive').first().append('<span class="text-danger error">Either Email or Phone field must be mapped</span>');
                    return false;
                }
                var $submitBtn = $(form).find('button[type="submit"]');
                $submitBtn.prop('disabled', true);
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                form.submit();
            }
        });
    </script>