<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(
                            admin_url('invoice_settings/save'),
                            array(
                                'id' => 'invoiceSettingForm'
                            )
                        ) ?>

                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Invoice Settings</h4>
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
                                            <p class="text-muted">Click on any variable below to insert it into the Invoice Number Prefix at your cursor position:</p>
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
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
                                                                    data-target="invoice_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i>
                                                <strong>Example:</strong> Using prefix "INV-{financial_year_short}" would generate:
                                                <strong>INV-<?php echo $financial_year_short; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="invoice_prefix">Invoice Number Prefix</label>
                                    <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" value="<?= get_option('invoice_prefix') ?>" required>
                                    <small class="text-muted">Use variables above to create dynamic prefixes. Example: INV-{current_full_year}-</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="invoice_terms_and_condition">Terms and Conditions</label>
                                    <textarea class="form-control" id="invoice_terms_and_condition" name="invoice_terms_and_condition" rows="5" required placeholder="Enter terms and conditions"><?= get_option('invoice_terms_and_condition') ?></textarea>
                                </div>
                            </div>
                        </div>

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
    <?php init_tail(); ?>
    <script>
        $(function() {
            $(window).off('beforeunload');
            
            init_editor('textarea[name="invoice_terms_and_condition"]');

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

            // Toggle collapse icon
            $('[data-toggle="collapse"]').on('click', function() {
                var icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                } else {
                    icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
            });

            $('#invoiceSettingForm').appFormValidator({
                rules: {
                    invoice_prefix: 'required',
                    invoice_terms_and_condition: 'required'
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