<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .onoffswitch-main .onoffswitch-label:before {
        height: 20px;
    }

    .onoffswitch-main {
        top: -20px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Purchase Status</h4>
                                <hr>
                            </div>
                        </div>
                        <div class="_buttons">
                            <a href="#" onclick="new_status(); return false;" class="btn btn-info pull-left display-block">Add Status</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php if (count($statuses) > 0) { ?>
                            <table class="table dt-table scroll-responsive" data-order-col="0" data-order-type="asc">
                                <thead>
                                    <th>Order No.</th>
                                    <th>Status Name</th>
                                    <th><?php echo _l('options'); ?></th>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 0;
                                    foreach ($statuses as $status) { ?>
                                        <tr class="<?= ($status['static'] == 1 ? 'no-drag' : '') ?>" data-id="<?php echo $status['id']; ?>">
                                            <td>
                                                <?= ($status['static'] == 0 ? '<i class="fa fa-bars" style="margin-right: 10px;"></i>' : '') ?>
                                                <?php echo  $no += 1; ?></td>
                                            <td><a href="#" onclick="edit_status(this,<?php echo $status['id']; ?>);return false;" data-color="<?php echo $status['color']; ?>" data-name="<?php echo $status['name']; ?>" data-isdefault="<?= $status['isdefault'] ?>"><?php echo $status['name']; ?></a>
                                            </td>
                                            <td>
                                                <?php if ($status['static'] != 1) { ?>
                                                    <a href="#" onclick="edit_status(this,<?php echo $status['id']; ?>);return false;" data-color="<?php echo $status['color']; ?>" data-name="<?php echo $status['name']; ?>" data-isdefault="<?= $status['isdefault'] ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                                    <?php if ($status['isdefault'] == 0) { ?>
                                                        <a href="<?php echo admin_url('purchase_settings/delete_status/' . $status['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } else { ?>
                            <p class="no-margin">Statues not found.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(
                            admin_url('purchase_settings/save_terms'),
                            array(
                                'id' => 'purchaseTermsForm'
                            )
                        ) ?>

                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Purchase Settings</h4>
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
                                            <p class="text-muted">Click on any variable below to insert it into the Purchase Number Prefix at your cursor position:</p>
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
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
                                                                    data-target="purchase_number_prefix">
                                                                    <i class="fa fa-plus"></i> Insert
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle"></i>
                                                <strong>Example:</strong> Using prefix "PO-{financial_year_short}" would generate:
                                                <strong>PO-<?php echo $financial_year_short; ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="purchase_number_prefix">Purchase Number Prefix</label>
                                    <input type="text" class="form-control" id="purchase_number_prefix" name="purchase_number_prefix" value="<?= get_option('purchase_number_prefix') ?>" required>
                                    <small class="text-muted">Use variables above to create dynamic prefixes. Example: PO-{current_full_year}-</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="purchase_terms_and_condition">Terms and Conditions</label>
                                    <textarea class="form-control" id="purchase_terms_and_condition" name="purchase_terms_and_condition" rows="5" required placeholder="Enter terms and conditions"><?= get_option('purchase_terms_and_condition') ?></textarea>
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
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="status" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('purchase_settings/status'), array('id' => 'purchase-status-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Add Purchase Status</span>
                    <span class="add-title">Edit Purchase Status</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'leads_status_add_edit_name'); ?>
                        <?php echo render_color_picker('color', _l('leads_status_color')); ?>
                        <!-- <div>Is Default ?</div>
                        <br>
                        <div class="pull-left form-group mtop5">
                            <div class="onoffswitch onoffswitch-main pull-right">
                                <input type="checkbox" name="isdefault" class="onoffswitch-checkbox onoffswitch onoffswitch-bg-image" id="isdefault">
                                <label class="onoffswitch-label" for="isdefault"></label>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        
        init_editor('textarea[name="purchase_terms_and_condition"]');

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

        $('#purchaseTermsForm').appFormValidator({
            rules: {
                purchase_number_prefix: 'required',
                purchase_terms_and_condition: 'required'
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                formGroup.append(error);
            }
        });

        $("tbody").sortable({
            items: "tr:not(.no-drag)",
            start: function(event, ui) {

            },
            stop: function(event, ui) {

            },
            update: function(event, ui) {
                var order = $(this).children("tr").map(function() {
                    return $(this).attr('data-id');
                }).get();
                $.ajax({
                    url: "<?php echo admin_url('purchase_settings/status_reorder'); ?>",
                    method: "POST",
                    data: {
                        order: order
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        alert_float('success', result.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                });
            }
        });
    });
    
    window.addEventListener('load', function() {
        $('#status .colorpicker-input').colorpicker('setValue', '#000000');

        appValidateForm($("body").find('#purchase-status-form'), {
            name: 'required'
        }, submit_handler);
        $('#status').on("hidden.bs.modal", function(event) {
            $('#additional').html('');
            $('#status input[name="name"]').val('');
            $('#status input[name="color"]').val('');
            $('#status .colorpicker-input').colorpicker('setValue', '#000000');
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
            $('#isdefault').prop('checked', false);
        });
    });

    function new_status() {
        $('#status').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_status(invoker, id) {
        $('#additional').append(hidden_input('id', id));
        $('#status input[name="name"]').val($(invoker).data('name'));
        $('#status .colorpicker-input').colorpicker('setValue', $(invoker).data('color'));
        if ($(invoker).data('isdefault') == 1) {
            $('#isdefault').prop('checked', true);
        } else {
            $('#isdefault').prop('checked', false);
        }
        $('#status').modal('show');
        $('.add-title').addClass('hide');
    }

    function submit_handler(form) {
        form.submit();
    }
</script>
</body>
</html>