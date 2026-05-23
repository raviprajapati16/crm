<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(
                            admin_url('proposal_settings/save'),
                            array(
                                'id' => 'proposalSettingForm'
                            )
                        ) ?>

                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Proposal Settings</h4>
                                <hr>
                            </div>
                        </div>

                        <!-- Dynamic Variables Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" href="#prefixVariables" aria-expanded="false" aria-controls="prefixVariables">
                                                <i class="fa fa-plus-circle"></i> Available Variables for Prefix
                                            </a>
                                        </h4>
                                    </div>
                                    <?php
                                    $current_year = date('Y');
                                    $current_year_short = date('y');
                                    $next_year = date('Y', strtotime('+1 year'));
                                    $next_year_short = date('y', strtotime('+1 year'));
                                    $current_month = date('m');
                                    $current_month_name = strtoupper(date('F'));
                                    $current_month_short = strtoupper(date('M'));
                                    $current_month_num = date('n');
                                    if ($current_month_num >= 4) {
                                        $fy_start = $current_year;
                                        $fy_end = $current_year + 1;
                                    } else {
                                        $fy_start = $current_year - 1;
                                        $fy_end = $current_year;
                                    }
                                    $financial_year_full = $fy_start . $fy_end;
                                    $financial_year = $fy_start . substr($fy_end, 2);
                                    $financial_year_short = substr($fy_start, 2) . substr($fy_end, 2);
                                    $current_date = date('d');
                                    ?>

                                    <div id="prefixVariables" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <p class="text-muted">Click on any variable below to insert it into the Proposal Number Prefix at your cursor position:</p>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Variable</th>
                                                            <th>Example Output</th>
                                                            <th>Description</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code>{current_full_year}</code></td>
                                                            <td><strong><?php echo $current_year; ?></strong></td>
                                                            <td>Current year</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_full_year}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{current_year_short}</code></td>
                                                            <td><strong><?php echo $current_year_short; ?></strong></td>
                                                            <td>Current year (Short)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_year_short}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{next_full_year}</code></td>
                                                            <td><strong><?php echo $next_year; ?></strong></td>
                                                            <td>Next year</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{next_full_year}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{next_year_short}</code></td>
                                                            <td><strong><?php echo $next_year_short; ?></strong></td>
                                                            <td>Next year (Short)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{next_year_short}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{current_month}</code></td>
                                                            <td><strong><?php echo $current_month; ?></strong></td>
                                                            <td>Current month (2 digits)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_month}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{current_month_name}</code></td>
                                                            <td><strong><?php echo $current_month_name; ?></strong></td>
                                                            <td>Current month name</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_month_name}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{current_month_short}</code></td>
                                                            <td><strong><?php echo $current_month_short; ?></strong></td>
                                                            <td>Current month (3 letters)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_month_short}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{current_date}</code></td>
                                                            <td><strong><?php echo $current_date; ?></strong></td>
                                                            <td>Current Date</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{current_date}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{financial_year_full}</code></td>
                                                            <td><strong><?php echo $financial_year_full; ?></strong></td>
                                                            <td>Financial year (Apr-Mar)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{financial_year_full}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{financial_year}</code></td>
                                                            <td><strong><?php echo $financial_year; ?></strong></td>
                                                            <td>Financial year (Apr-Mar)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{financial_year}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><code>{financial_year_short}</code></td>
                                                            <td><strong><?php echo $financial_year_short; ?></strong></td>
                                                            <td>Financial year (Apr-Mar)</td>
                                                            <td>
                                                                <button type="button" class="btn btn-xs btn-primary variable-insert"
                                                                    data-variable="{financial_year_short}"
                                                                    data-target="proposal_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i>
                                                <strong>Example:</strong> Using prefix "PROP-{financial_year_short}" would generate:
                                                <strong>PROP-<?php echo $financial_year; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_number_prefix">Proposal Number Prefix</label>
                                    <input type="text" class="form-control" id="proposal_number_prefix" name="proposal_number_prefix" value="<?= get_option('proposal_number_prefix') ?>" required>
                                    <small class="text-muted">Use variables above to create dynamic prefixes. Example: PROP-{current_full_year}-</small>
                                </div>
                            </div>
                        </div>

                        <!-- Domestic Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Domestic Details</h4>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_account_name">Account Name </label>
                                    <input type="text" class="form-control" id="proposal_domestic_account_name" name="proposal_domestic_account_name" value="<?= get_option('proposal_domestic_account_name') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_account_no">Account Number </label>
                                    <input type="number" class="form-control" id="proposal_domestic_account_no" name="proposal_domestic_account_no" value="<?= get_option('proposal_domestic_account_no') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_bank_name">Bank Name </label>
                                    <input type="text" class="form-control" id="proposal_domestic_bank_name" name="proposal_domestic_bank_name" value="<?= get_option('proposal_domestic_bank_name') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_ifsc_code">IFSC Code </label>
                                    <input type="text" class="form-control" id="proposal_domestic_ifsc_code" name="proposal_domestic_ifsc_code" value="<?= get_option('proposal_domestic_ifsc_code') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_swift_code">Swift Code</label>
                                    <input type="text" class="form-control" id="proposal_domestic_swift_code" name="proposal_domestic_swift_code" value="<?= get_option('proposal_domestic_swift_code') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_domestic_address">Bank Address </label>
                                    <textarea class="form-control" id="proposal_domestic_address" name="proposal_domestic_address" rows="3" required><?= get_option('proposal_domestic_address') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Mergeable Fields for Domestic Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" href="#domesticMergeableFields" aria-expanded="false" aria-controls="domesticMergeableFields">
                                                <i class="fa fa-plus-circle"></i> Mergeable Fields
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="domesticMergeableFields" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <p class="text-muted">Click on any field below to insert it into the Terms and Conditions at your cursor position:</p>
                                            <div class="row">
                                                <?php foreach ($merge_fields as $merge_field) { ?>
                                                    <div class="col-md-3">
                                                        <div><?= ucfirst($merge_field['name']) ?></div>
                                                        <a href="javascript:;" class="btn btn-sm merge-field" data-field="<?= $merge_field['key'] ?>" data-target="proposal_domestic_terms">
                                                            <?= $merge_field['key'] ?>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="proposal_domestic_terms">Terms and Conditions</label>
                                    <textarea class="form-control" id="proposal_domestic_terms" name="proposal_domestic_terms" rows="5" required placeholder="Enter terms and conditions for domestic transactions..."><?= get_option('proposal_domestic_terms') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <br>

                        <!-- International Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">International Details</h4>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_account_name">Account Name </label>
                                    <input type="text" class="form-control" id="proposal_international_account_name" name="proposal_international_account_name" value="<?= get_option('proposal_international_account_name') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_account_no">Account Number </label>
                                    <input type="number" class="form-control" id="proposal_international_account_no" name="proposal_international_account_no" value="<?= get_option('proposal_international_account_no') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_bank_name">Bank Name </label>
                                    <input type="text" class="form-control" id="proposal_international_bank_name" name="proposal_international_bank_name" value="<?= get_option('proposal_international_bank_name') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_ifsc_code">IFSC Code</label>
                                    <input type="text" class="form-control" id="proposal_international_ifsc_code" name="proposal_international_ifsc_code" value="<?= get_option('proposal_international_ifsc_code') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_swift_code">Swift Code </label>
                                    <input type="text" class="form-control" id="proposal_international_swift_code" name="proposal_international_swift_code" value="<?= get_option('proposal_international_swift_code') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proposal_international_address">Bank Address </label>
                                    <textarea class="form-control" id="proposal_international_address" name="proposal_international_address" rows="3" required><?= get_option('proposal_international_address') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Mergeable Fields for International Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" href="#internationalMergeableFields" aria-expanded="false" aria-controls="internationalMergeableFields">
                                                <i class="fa fa-plus-circle"></i> Mergeable Fields
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="internationalMergeableFields" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <p class="text-muted">Click on any field below to insert it into the Terms and Conditions at your cursor position:</p>
                                            <div class="row">
                                                <?php foreach ($merge_fields as $merge_field) { ?>
                                                    <div class="col-md-3">
                                                        <div><?= ucfirst($merge_field['name']) ?></div>
                                                        <a href="javascript:;" class="btn btn-sm merge-field" data-field="<?= $merge_field['key'] ?>" data-target="proposal_international_terms">
                                                            <?= $merge_field['key'] ?>
                                                        </a>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="proposal_international_terms">Terms and Conditions</label>
                                        <textarea class="form-control" id="proposal_international_terms" name="proposal_international_terms" rows="5" required placeholder="Enter terms and conditions for international transactions..."><?= get_option('proposal_international_terms') ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <button type="submit" class="btn btn-primary">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script>
        $(function() {
            $(window).off('beforeunload');

            init_editor('textarea[name="proposal_international_terms"]');
            init_editor('textarea[name="proposal_domestic_terms"]');

            // Handle variable insertion for prefix
            $(document).on('click', '.variable-insert', function() {
                var variable = $(this).data('variable');
                var target = $(this).data('target');
                var inputField = document.getElementById(target);

                if (inputField) {
                    var cursorPos = inputField.selectionStart;
                    var textBefore = inputField.value.substring(0, cursorPos);
                    var textAfter = inputField.value.substring(cursorPos);

                    inputField.value = textBefore + variable + textAfter;
                    inputField.focus();
                    inputField.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);

                    // Show success message
                    $(this).removeClass('btn-primary').addClass('btn-success');
                    $(this).html('<i class="fa fa-check"></i> Inserted');

                    // Reset button after 1 second
                    setTimeout(() => {
                        $(this).removeClass('btn-success').addClass('btn-primary');
                        $(this).html('<i class="fa fa-plus"></i> Insert');
                    }, 1000);
                }
            });

            // Handle merge field insertion
            $(document).on('click', '.merge-field', function() {
                var field = $(this).data('field');
                var target = $(this).data('target');

                // Insert into TinyMCE editor if it exists
                if (typeof tinyMCE !== 'undefined') {
                    var editor = tinyMCE.get(target);
                    if (editor) {
                        editor.execCommand('mceInsertContent', false, field);
                        editor.focus();
                        return;
                    }
                }

                // Fallback to textarea insertion
                var textarea = document.getElementById(target);
                if (textarea) {
                    var cursorPos = textarea.selectionStart;
                    var textBefore = textarea.value.substring(0, cursorPos);
                    var textAfter = textarea.value.substring(cursorPos);

                    textarea.value = textBefore + field + textAfter;
                    textarea.focus();
                    textarea.setSelectionRange(cursorPos + field.length, cursorPos + field.length);
                }
            });

            // Toggle collapse icon
            $('[data-toggle="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                } else {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
            });

            $('#proposalSettingForm').appFormValidator({
                rules: {
                    // Prefix validation
                    proposal_number_prefix: 'required',

                    // Domestic section validation rules
                    proposal_domestic_account_name: 'required',
                    proposal_domestic_account_no: 'required',
                    proposal_domestic_bank_name: 'required',
                    proposal_domestic_ifsc_code: 'required',
                    proposal_domestic_swift_code: 'required',
                    proposal_domestic_address: 'required',
                    proposal_domestic_terms: 'required',

                    // International section validation rules
                    proposal_international_account_name: 'required',
                    proposal_international_account_no: 'required',
                    proposal_international_bank_name: 'required',
                    proposal_international_swift_code: 'required',
                    proposal_international_ifsc_code: 'required',
                    proposal_international_address: 'required',
                    proposal_international_terms: 'required'
                },
                errorPlacement: function(error, element) {
                    var formGroup = $(element).closest('.form-group');
                    formGroup.append(error);
                }
            });
        });
    </script>
    </body>

    </html>