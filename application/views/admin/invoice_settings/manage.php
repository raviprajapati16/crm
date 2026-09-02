<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open_multipart(
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

                        <!-- Branch / Prefix / GST Multi-row Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="section-subtitle" style="margin-bottom:10px; font-weight:600; color:#555;">
                                    <i class="fa fa-building-o"></i> Branch Invoice Settings
                                </h5>
                                <p class="text-muted" style="margin-bottom:15px; font-size:13px;">
                                    Add one entry per branch. Each branch can have its own invoice prefix and GST number.
                                </p>
                            </div>
                        </div>

                        <div id="branch-rows-container">

                            <?php
                            // Load ALL branches from the single JSON option
                            $all_branches = get_option('branch_rows');
                            $all_branches = $all_branches ? json_decode($all_branches, true) : [];
                            if (!is_array($all_branches)) {
                                $all_branches = [];
                            }
                            $active_branches = array_filter($all_branches, function ($branch) {
                                return empty($branch['deleted']);
                            });
                            $active_branches = array_values($active_branches);
                            if (empty($active_branches)) {
                                // Default: one empty primary row if nothing saved yet
                                $active_branches = [['branch_name' => '', 'invoice_prefix' => '', 'gst_number' => '', 'id' => '']];
                            }

                            foreach ($active_branches as $idx => $branch):
                                $is_primary = ($idx === 0);
                            ?>
                                <div class="branch-row" data-index="<?= $idx ?>"
                                    style="background:#f9f9f9; border:1px solid #e3e3e3; border-radius:6px; padding:15px 15px 5px; margin-bottom:12px; position:relative;">
                                    <input type="hidden" name="branch_id[]" value="<?= htmlspecialchars($branch['id'] ?? '') ?>">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Branch Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control branch-name-input"
                                                    name="branch_name[]"
                                                    placeholder="<?= $is_primary ? 'e.g. Head Office' : 'e.g. Branch Office' ?>"
                                                    value="<?= htmlspecialchars($branch['branch_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Invoice Prefix <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control invoice-prefix-input"
                                                    name="invoice_prefix[]"
                                                    placeholder="e.g. INV-{financial_year_short}-"
                                                    value="<?= htmlspecialchars($branch['invoice_prefix'] ?? '') ?>" required>
                                                <small class="text-muted" style="font-size:11px;">Click a variable above then click inside this field to insert.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>GST Number</label>
                                                <input type="text" class="form-control gst-number-input"
                                                    name="gst_number[]"
                                                    placeholder="e.g. 22AAAAA0000A1Z5"
                                                    value="<?= htmlspecialchars($branch['gst_number'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-1" style="padding-top:25px; text-align:center;">
                                            <?php if ($is_primary): ?>
                                                <span class="label label-default" style="font-size:11px; padding:4px 7px;"
                                                    title="Primary row cannot be removed">Primary</span>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-danger btn-sm remove-branch-row"
                                                    title="Remove this row">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div><!-- /#branch-rows-container -->

                        <!-- Add More Button -->
                        <div class="row" style="margin-bottom:20px;">
                            <div class="col-md-12">
                                <button type="button" id="add-branch-row" class="btn btn-success btn-sm">
                                    <i class="fa fa-plus-circle"></i> Add More Branch
                                </button>
                            </div>
                        </div>

                        <!-- Hidden template for JS cloning (not submitted) -->
                        <template id="branch-row-template">
                            <div class="branch-row" style="background:#f9f9f9; border:1px solid #e3e3e3; border-radius:6px; padding:15px 15px 5px; margin-bottom:12px; position:relative;">
                                <input type="hidden" name="branch_id[]" value="">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Branch Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control branch-name-input"
                                                name="branch_name[]" placeholder="e.g. Branch Office" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Invoice Prefix <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control invoice-prefix-input"
                                                name="invoice_prefix[]" placeholder="e.g. BR-{financial_year_short}-" required>
                                            <small class="text-muted" style="font-size:11px;">Click a variable above then click inside this field to insert.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>GST Number</label>
                                            <input type="text" class="form-control gst-number-input"
                                                name="gst_number[]" placeholder="e.g. 22AAAAA0000A1Z5">
                                        </div>
                                    </div>
                                    <div class="col-md-1" style="padding-top:25px; text-align:center;">
                                        <button type="button" class="btn btn-danger btn-sm remove-branch-row"
                                            title="Remove this row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

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
                                <h5 class="section-subtitle" style="margin-bottom:10px; font-weight:600; color:#555;">
                                    <i class="fa fa-file-text-o"></i> Annexure Details
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_shipping_bill_no">Shipping Bill No.</label>
                                    <input type="text" class="form-control" id="annexure_shipping_bill_no" name="annexure_shipping_bill_no" value="<?= get_option('annexure_shipping_bill_no') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_shipping_bill_date">Shipping Bill Date</label>
                                    <input type="date" class="form-control" id="annexure_shipping_bill_date" name="annexure_shipping_bill_date" value="<?= get_option('annexure_shipping_bill_date') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_range_of_customs">Range of Customs</label>
                                    <input type="text" class="form-control" id="annexure_range_of_customs" name="annexure_range_of_customs" value="<?= get_option('annexure_range_of_customs') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_commissionerate_of_customs">Commissionerate of Customs</label>
                                    <input type="text" class="form-control" id="annexure_commissionerate_of_customs" name="annexure_commissionerate_of_customs" value="<?= get_option('annexure_commissionerate_of_customs') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_division_of_customs">Division of Customs</label>
                                    <input type="text" class="form-control" id="annexure_division_of_customs" name="annexure_division_of_customs" value="<?= get_option('annexure_division_of_customs') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_exporter_name">Name of Exporter</label>
                                    <input type="text" class="form-control" id="annexure_exporter_name" name="annexure_exporter_name" value="<?= get_option('annexure_exporter_name') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_exporter_address">Address of Exporter</label>
                                    <!-- <textarea class="form-control" id="annexure_exporter_address" name="annexure_exporter_address" rows="4"><?= get_option('annexure_exporter_address') ?></textarea> -->
                                    <input type="text" class="form-control" id="annexure_exporter_address" name="annexure_exporter_address" value="<?= get_option('annexure_exporter_address') ?>">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_iec_number">IEC Number</label>
                                    <input type="text" class="form-control" id="annexure_iec_number" name="annexure_iec_number" value="<?= get_option('annexure_iec_number') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_gstin">GSTIN</label>
                                    <input type="text" class="form-control" id="annexure_gstin" name="annexure_gstin" value="<?= get_option('annexure_gstin') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_pan_number">PAN Number</label>
                                    <input type="text" class="form-control" id="annexure_pan_number" name="annexure_pan_number" value="<?= get_option('annexure_pan_number') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_cin_number">CIN Number</label>
                                    <input type="text" class="form-control" id="annexure_cin_number" name="annexure_cin_number" value="<?= get_option('annexure_cin_number') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_stuffing_address">Stuffing Address</label>
                                    <!-- <textarea class="form-control" id="annexure_stuffing_address" name="annexure_stuffing_address" rows="4"><?= get_option('annexure_stuffing_address') ?></textarea> -->
                                    <input type="text" class="form-control" id="annexure_stuffing_address" name="annexure_stuffing_address" value="<?= get_option('annexure_stuffing_address') ?>">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_aeo_certificate_number">AEO Certificate Number</label>
                                    <input type="text" class="form-control" id="annexure_aeo_certificate_number" name="annexure_aeo_certificate_number" value="<?= get_option('annexure_aeo_certificate_number') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_permission_order_no">Permission Order No.</label>
                                    <input type="text" class="form-control" id="annexure_permission_order_no" name="annexure_permission_order_no" value="<?= get_option('annexure_permission_order_no') ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_authorised_signatory_name">Name of Authorised Signatory</label>
                                    <input type="text" class="form-control" id="annexure_authorised_signatory_name" name="annexure_authorised_signatory_name" value="<?= get_option('annexure_authorised_signatory_name') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_authorised_signatory_designation">Designation of Authorised Signatory</label>
                                    <input type="text" class="form-control" id="annexure_authorised_signatory_designation" name="annexure_authorised_signatory_designation" value="<?= get_option('annexure_authorised_signatory_designation') ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="annexure_examination_agency_name">Examination Agency/Officer Name</label>
                                    <input type="text" class="form-control" id="annexure_examination_agency_name" name="annexure_examination_agency_name" value="<?= get_option('annexure_examination_agency_name') ?>">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="annexure_declaration">Declaration</label>
                                    <textarea class="form-control" id="annexure_declaration" name="annexure_declaration" rows="5"><?= get_option('annexure_declaration') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="section-subtitle" style="margin-bottom:10px; font-weight:600; color:#555;">
                                    <i class="fa fa-sticky-note"></i> Invoice Notes
                                </h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes_gst_invoice">GST Invoice Notes</label>
                                    <textarea class="form-control" id="notes_gst_invoice" name="notes_gst_invoice" rows="4"><?= get_option('notes_gst_invoice') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes_custom_invoice">Custom Invoice Notes</label>
                                    <textarea class="form-control" id="notes_custom_invoice" name="notes_custom_invoice" rows="4"><?= get_option('notes_custom_invoice') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes_packing_weight_list">Packing / Weight List Notes</label>
                                    <textarea class="form-control" id="notes_packing_weight_list" name="notes_packing_weight_list" rows="4"><?= get_option('notes_packing_weight_list') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes_commercial_invoice">Commercial Invoice Notes</label>
                                    <textarea class="form-control" id="notes_commercial_invoice" name="notes_commercial_invoice" rows="4"><?= get_option('notes_commercial_invoice') ?></textarea>
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
            init_editor('textarea[name="notes_gst_invoice"]');
            init_editor('textarea[name="notes_custom_invoice"]');
            init_editor('textarea[name="notes_packing_weight_list"]');
            init_editor('textarea[name="notes_commercial_invoice"]');
            init_editor('textarea[name="annexure_declaration"]');
            // Track the last focused invoice prefix input
            var $lastFocusedPrefix = null;
            $(document).on('focus', '.invoice-prefix-input', function() {
                $lastFocusedPrefix = $(this);
            });

            // Handle variable insertion into the last focused prefix input (or fallback to first)
            $(document).on('click', '.variable-insert', function() {
                var variable = $(this).data('variable');

                // Use last focused prefix, or fall back to the first prefix input
                var $target = ($lastFocusedPrefix && $lastFocusedPrefix.length) ?
                    $lastFocusedPrefix :
                    $('.invoice-prefix-input').first();

                if ($target && $target.length) {
                    var inputField = $target[0];
                    var cursorPos = inputField.selectionStart;
                    var textBefore = inputField.value.substring(0, cursorPos);
                    var textAfter = inputField.value.substring(cursorPos);

                    inputField.value = textBefore + variable + textAfter;
                    inputField.focus();
                    inputField.setSelectionRange(cursorPos + variable.length, cursorPos + variable.length);

                    // Highlight the target row briefly
                    $target.closest('.branch-row').css('border-color', '#5cb85c');
                    setTimeout(function() {
                        $target.closest('.branch-row').css('border-color', '#e3e3e3');
                    }, 800);
                }

                // Visual feedback on insert button
                $(this).removeClass('btn-primary').addClass('btn-success');
                $(this).html('<i class="fa fa-check"></i> Inserted');
                setTimeout(() => {
                    $(this).removeClass('btn-success').addClass('btn-primary');
                    $(this).html('<i class="fa fa-plus"></i> Insert');
                }, 1000);
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

            // ── Add More Branch Row ──────────────────────────────────────
            $('#add-branch-row').on('click', function() {
                var template = document.getElementById('branch-row-template');
                var clone = document.importNode(template.content, true);
                var $clone = $(clone);

                // Animate entry
                $clone.find('.branch-row').css({
                    opacity: 0,
                    marginTop: '-10px'
                });
                $('#branch-rows-container').append($clone);
                $('#branch-rows-container .branch-row').last()
                    .animate({
                        opacity: 1,
                        marginTop: '0px'
                    }, 300);
            });

            // ── Remove Branch Row ────────────────────────────────────────
            $(document).on('click', '.remove-branch-row', function() {
                var $row = $(this).closest('.branch-row');
                $row.fadeOut(250, function() {
                    $(this).remove();
                });
            });

            // ── Form Validator ───────────────────────────────────────────
            $('#invoiceSettingForm').appFormValidator({
                rules: {
                    invoice_terms_and_condition: 'required'
                },
                errorPlacement: function(error, element) {
                    $(element).closest('.form-group').append(error);
                }
            });
        });
    </script>
    </body>

    </html>