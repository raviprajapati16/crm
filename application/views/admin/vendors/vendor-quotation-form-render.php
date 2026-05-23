<?php defined('BASEPATH') or exit('No direct script access allowed');
$phoneNumberArr = phonenumberSplit($leadData->phonenumber);
$formLink = site_url('forms/vqf/') . $form_data["formkey"];
$panelBorder = "border-black";
if ($form_data['form_status'] == "approved") {
    $panelBorder = " border-success ";
} else if ($form_data['form_status'] == "not-approved") {
    $panelBorder = " border-danger ";
} else if ($form_data['form_status'] == "pending" && !empty($form_data['vendor_form_submitted'])) {
    $panelBorder = " border-warning ";
}
?>
<?php
$uniqid = uniqid();
$mainFormId = 'quotationForm_' . $uniqid;
$subFormId = 'quotationItemForm_' . $uniqid;
$approval_form_id = "customerInquiryApprovalForm_" . $uniqid;
?>
<div class="panel panel-defult quotation-main-panel <?= $panelBorder ?> panel-quotation-main-form-<?= $form_data['id'] ?> mtop10" main-form-id="<?= $mainFormId ?>" item-form-id="<?= $subFormId ?>">
    <?php echo form_open_multipart(admin_url('vendors/save_quotation_form'), array('id' => $mainFormId));
    echo form_close(); ?>
    <?php echo form_open_multipart('#', array('id' => $subFormId, 'name'));
    echo form_close(); ?>
    <?php echo form_open_multipart(admin_url('vendors/quotation_approval_status_change'), array('id' => $approval_form_id, "class" => "customerInquiryApprovalForm"));
    echo form_close(); ?>
    <input type="hidden" class="vendor_quoation_forms_id" name="vendor_quoation_forms_id" form="<?= $mainFormId ?>" value="<?= $form_data['id'] ?>" />
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" class="accordian-collapse" data-parent="#vendor_accordion" href="#<?= $uniqid ?>">
                #<?= leadFormIdRender("VQF", $form_data['lead_id'], $form_data['id']) ?></a>
            <?php
            if (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])) {
                if ($form_data['form_status'] == "pending" && !empty($form_data['vendor_form_submitted'])) {
                    echo '<span class="label label-warning review-label" data-toggle="tooltip" data-title="Review Customer Form">Review Pending</span>';
                } elseif ($form_data['form_status'] == "approved") {
                    echo '<span class="label label-success review-label">Approved</span>';
                } elseif ($form_data['form_status'] == "not-approved") {
                    echo '<span class="label label-danger review-label">Not Approved</span>';
                }
            ?>
                <div class="pull-right vendor-form-btn-section">
                    <?php
                    $whatappLink = generateWhatsappLink($leadData->phonenumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                    ?>
                    <?php
                    if (!empty($phoneNumberArr)) {
                        $whatsappMessage = quotationFormMessageContent($leadData->name, $formLink);
                        foreach ($phoneNumberArr as $phoneNumber) {
                            $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                    ?>
                            <a href="<?= $whatappLink ?>" target="_blank" onclick="sendQuotationForm(<?= $form_data['lead_id'] ?>,<?= $form_data['id'] ?>,'whatsapp')" class="btn btn-default mleft5 btn-xs <?php echo ($form_data['is_whatsapp_send'] == '1') ? 'border-success' : '' ?>" data-toggle="tooltip" data-title="<?= $phoneNumber ?>"><i class="fa fa-whatsapp"></i></a>
                    <?php
                        }
                    }
                    ?>
                    <?php if (!empty($leadData->email)) { ?>
                        <a href="javascript:;" data-toggle="tooltip" data-title="<?= $leadData->email ?>" onclick="sendQuotationForm(<?= $form_data['lead_id'] ?>,<?= $form_data['id'] ?>,'email')" class="btn btn-default btn-xs mleft5 <?php echo ($form_data['is_email_send'] == '1') ? 'border-success' : '' ?>"><i class="fa fa-envelope"></i></a>
                        <?php } ?>
                    <div class="mleft5">
                        <div class="onoffswitch">
                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox quotation-formswitch" id="quotation_form_switch_<?= $form_data['id'] ?>" <?= ($form_data['is_active'] == "1") ? "checked" : "" ?>>
                            <label class="onoffswitch-label" for="quotation_form_switch_<?= $form_data['id'] ?>"></label>
                        </div>
                    </div>
                    <?php
                    if (!empty($form_data['vendor_form_submitted'])) {
                    ?>
                        <a href="<?= admin_url('vendors/pdf/' . $form_data['formkey'] . '?output_type=I') ?>" target="_blank" class="btn btn-default btn-xs mleft5" data-toggle="tooltip" data-title="View PDF"><i class="fa fa fa-file-pdf-o" aria-hidden="true"></i></a>
                    <?php
                    }
                    ?>
                    <a href="<?= site_url('forms/vqf/' . $form_data['formkey']) ?>" target="_blank" class="btn btn-default btn-xs mleft5" data-toggle="tooltip" data-title="Preview"><i class="fa fa-external-link" aria-hidden="true"></i></a>
                    <button type="button" class="btn btn-default quotation-form-save hide btn-xs mleft5 "><i class="fa fa-save"></i></button>
                    <button type="button" class="btn btn-default quotation-form-edit btn-xs mleft5"><i class="fa fa-edit"></i></button>
                    <?php
                    if (empty($form_data['vendor_form_submitted']) && has_permission('vendors', '', 'delete')) {
                    ?>
                        <button type="button" class="btn btn-default text-danger quotation-form-delete btn-xs mleft5"><i class="fa fa-trash"></i></button>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
        </h4>
    </div>
    <div id="<?= $uniqid ?>" class="panel-collapse collapse">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 mtop5 quotation-form-section hide">
                    <div class="row">
                        <?= all_type_input_render([
                            "label" => "Supplier Name",
                            "id" => "supplier_name",
                            "name" => "supplier_name",
                            "type" => "text",
                            "selected_value" => (isset($form_data['supplier_name']) && !empty($form_data['supplier_name'])) ? $form_data['supplier_name'] : '',
                            "is_required" => false,
                            "form" => $mainFormId,
                        ], 'col-md-6', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "GST IN",
                            "id" => "gst_in",
                            "name" => "gst_in",
                            "type" => "text",
                            "selected_value" => (isset($form_data['gst_in']) && !empty($form_data['gst_in'])) ? $form_data['gst_in'] : '',
                            "is_required" => false,
                            "form" => $mainFormId,
                            "className" => "gst_validation"
                        ], 'col-md-6', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Date",
                            "id" => "quotation_date",
                            "name" => "quotation_date",
                            "type" => "date_picker",
                            "selected_value" => (isset($form_data['quotation_date']) && !empty($form_data['quotation_date'])) ? date('d-m-Y', strtotime($form_data['quotation_date'])) : '',
                            "is_required" => false,
                            "form" => $mainFormId,
                        ], 'col-md-6', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Address",
                            "id" => "address",
                            "name" => "address",
                            "type" => "textarea",
                            "rows" => 4,
                            "is_required" => false,
                            "selected_value" => (isset($form_data['address']) && !empty($form_data['address'])) ? $form_data['address'] : '',
                            "form" => $mainFormId,
                        ], 'col-md-6', false);
                        ?>
                    </div>
                    <div class="row quotation-dynamic-section">
                        <input type="hidden" name="id" form="<?= $subFormId ?>" />
                        <?= all_type_input_render([
                            "label" => "Description Of Service",
                            "id" => "service_description",
                            "name" => "service_description",
                            "type" => "textarea",
                            "rows" => 7,
                            "is_required" => false,
                            "form" => $subFormId,
                        ], 'col-md-3', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "HSN / SAC",
                            "id" => "hsn_sac",
                            "name" => "hsn_sac",
                            "type" => "text",
                            "is_required" => false,
                            "form" => $subFormId,
                        ], 'col-md-3', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Quantity",
                            "id" => "qty",
                            "name" => "qty",
                            "type" => "number",
                            "is_required" => false,
                            "form" => $subFormId,
                        ], 'col-md-3', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Unit",
                            "id" => "unit",
                            "name" => "unit",
                            "type" => "text",
                            "is_required" => false,
                            "form" => $subFormId,
                        ], 'col-md-3', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Price INR",
                            "id" => "price_in_inr",
                            "name" => "price_in_inr",
                            "type" => "text",
                            "is_required" => false,
                            "form" => $subFormId,
                            "selected_value" => "0.00",
                            "className" => "decimal-value"
                        ], 'col-md-3', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Amount INR",
                            "id" => "amount_in_inr",
                            "name" => "amount_in_inr",
                            "type" => "text",
                            "is_required" => false,
                            "form" => $subFormId,
                            "selected_value" => "0.00",
                            "className" => "decimal-value",
                            "is_readonly" => true,
                        ], 'col-md-3', false);
                        ?>
                        <div class="col-md-12 mtop5">
                            <button type="button" class="btn btn-danger btn-xs quotation-update-cancel mleft5 pull-right hide" form="<?= $subFormId ?>">Cancel</button>
                            <button type="submit" class="btn btn-info btn-xs quotation-item-add pull-right" form="<?= $subFormId ?>">Add</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><b>Sr. No.</b></th>
                                        <th><b>Description Of Service</b></th>
                                        <th><b>HSN / SAC</b></th>
                                        <th><b>Quantity </b></th>
                                        <th><b>Unit </b></th>
                                        <th><b>Price INR</b></th>
                                        <th><b>Amount INR</b></th>
                                        <th><b>Action</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($form_items_data)) {
                                        foreach ($form_items_data as $k1 => $item) { ?>
                                            <tr class="quotation-item-row" data-id="<?= $item['id'] ?>">
                                                <td><?= $k1 + 1 ?></td>
                                                <td><?= $item['service_description'] ?></td>
                                                <td><?= $item['hsn_sac'] ?></td>
                                                <td><?= $item['qty'] ?></td>
                                                <td><?= $item['unit'] ?></td>
                                                <td><?= $item['price_in_inr'] ?></td>
                                                <td><?= $item['amount_in_inr'] ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs quotation-item-edit"><i class="fa fa-edit"></i></button>
                                                    <?php if (has_permission('vendors', '', 'delete')) { ?>
                                                        <button type="button" class="btn btn-danger btn-xs quotation-item-delete"><i class="fa fa-trash"></i></button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Items not available.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <?= all_type_input_render([
                            "label" => "Terms & Conditions",
                            "id" => "terms_conditions_" . $form_data['id'],
                            "name" => "terms_conditions",
                            "type" => "textarea",
                            "rows" => 7,
                            "is_required" => false,
                            "selected_value" => (isset($form_data['terms_conditions']) && !empty($form_data['terms_conditions'])) ? $form_data['terms_conditions'] : '',
                            "className" => "text-editor",
                            "form" => $mainFormId,
                        ], 'col-md-12', false);
                        ?>
                        <?= all_type_input_render([
                            "label" => "Extra Notes",
                            "id" => "notes_" . $form_data['id'],
                            "name" => "notes",
                            "type" => "textarea",
                            "rows" => 7,
                            "is_required" => false,
                            "selected_value" => (isset($form_data['notes']) && !empty($form_data['notes'])) ? $form_data['notes'] : '',
                            "className" => "text-editor",
                            "form" => $mainFormId,
                        ], 'col-md-12', false);
                        ?>

                        <?= all_type_input_render([
                            "label" => "Document Upload",
                            "id" => "file",
                            "name" => "file",
                            "type" => "fileupload",
                            "is_required" => false,
                            "selected_value" => (isset($form_data['file']) && !empty($form_data['file'])) ? $form_data['file'] : '',
                            "preview_url" => site_url('download/file/vendor_quotation_files/' . $form_data['id']),
                            "file_delete_class" => (has_permission('vendors', '', 'delete')) ? "delete-quotation-file" : '',
                            "form" => $mainFormId,
                        ], 'col-md-12', false);
                        ?>
                        <div class="col-md-12">
                            <button type="button" class="btn btn-info btn-xs quotation-form-save pull-right" form="<?= $subFormId ?>">Save</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mtop5 quotation-preview-section">

                    <div class="row">
                        <div class="col-md-4">
                            <span><b>Supplier Name </b></span> : <?= (isset($form_data['supplier_name']) && !empty($form_data['supplier_name'])) ? $form_data['supplier_name'] : '-'; ?>
                        </div>
                        <div class="col-md-4">
                            <span><b>GST IN </b></span> : <?= (isset($form_data['gst_in']) && !empty($form_data['gst_in'])) ? $form_data['gst_in'] : '-'; ?>
                        </div>
                        <div class="col-md-4">
                            <span><b>Date </b></span> : <?= (isset($form_data['quotation_date']) && !empty($form_data['quotation_date'])) ? date('d-m-Y', strtotime($form_data['quotation_date'])) : '-'; ?>
                        </div>
                        <div class="col-md-6 mtop5">
                            <span><b>Address </b></span> : <?= (isset($form_data['address']) && !empty($form_data['address'])) ? $form_data['address'] : '-'; ?>
                        </div>
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><b>Sr. No.</b></th>
                                        <th><b>Description Of Service</b></th>
                                        <th><b>HSN / SAC</b></th>
                                        <th><b>Quantity </b></th>
                                        <th><b>Unit </b></th>
                                        <th><b>Price INR</b></th>
                                        <th><b>Amount INR</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($form_items_data)) {
                                        foreach ($form_items_data as $k1 => $item) { ?>
                                            <tr class="quotation-item-row" data-id="<?= $item['id'] ?>">
                                                <td><?= $k1 + 1 ?></td>
                                                <td><?= $item['service_description'] ?></td>
                                                <td><?= $item['hsn_sac'] ?></td>
                                                <td><?= $item['qty'] ?></td>
                                                <td><?= $item['unit'] ?></td>
                                                <td><?= $item['price_in_inr'] ?></td>
                                                <td><?= $item['amount_in_inr'] ?></td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Items not available.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-12 mtop10">
                            <div><strong><u>Terms & Conditions : </u></strong></div>
                            <div class="mtop10"> <?= (isset($form_data['terms_conditions']) && !empty($form_data['terms_conditions'])) ? $form_data['terms_conditions'] : '-'; ?> </div>
                        </div>
                        <div class="col-md-12 mtop10">
                            <div><strong><u>Notes : </u></strong></div>
                            <div class="mtop10"> <?= (isset($form_data['notes']) && !empty($form_data['notes'])) ? $form_data['notes'] : '-'; ?> </div>
                        </div>
                        <?php
                        if (!empty($form_data['file'])) {
                        ?>
                            <div class="col-md-12 mtop10">
                                <div><strong><u>Document : </u></strong></div>
                                <div class="mtop10"> <a href="<?= site_url('download/file/vendor_quotation_files/' . $form_data['id']) ?>" target="_blank"> <?= $form_data['file']; ?></a></div>
                            </div>
                        <?php } ?>

                        <?php
                        if (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id']))
                            if (!empty($form_data['form_status'])) {
                        ?>
                            <div class="col-md-12 mtop5 vendor-quotation-form-approval-section">
                                <div class="panel panel-primary">
                                    <div class="panel-heading"><b>Quotation Approval</b></div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <?php if ($form_data['form_status'] == "pending") { ?>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="radio-section">
                                                            <label class="radio-inline" for="form_status_approved_<?= $uniqid ?>">
                                                                <input type="radio" id="form_status_approved_<?= $uniqid ?>" value="approved" name="form_status" form="<?= $approval_form_id ?>" checked> Approved
                                                            </label>
                                                            <label class="radio-inline" for="form_status_not_approved_<?= $uniqid ?>">
                                                                <input type="radio" id="form_status_not_approved_<?= $uniqid ?>" value="not-approved" name="form_status" form="<?= $approval_form_id ?>"> Not Approved
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 reject_reason_section hide">
                                                    <div class="form-group">
                                                        <div class="control-label">Reject Reason<span class="text-danger">* </span></div>
                                                        <textarea id="reject_reason_<?= $uniqid ?>" maxlength="500" name="reject_note" rows="4" class="form-control" placeholder="Enter Reason" form="<?= $approval_form_id ?>"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <button type="button" class="btn btn-primary btn-sm quotation-approval-form-save-btn">Submit</button>
                                                </div>
                                            <?php } elseif ($form_data['form_status'] == "approved" || $form_data['form_status'] == "not-approved") {
                                            ?>
                                                <div class="col-md-12">
                                                    <?php if ($form_data['form_status'] == "approved") { ?>
                                                        <div><b>Form Status : </b>
                                                            <span class='text-success'>Approved</span>
                                                        </div>
                                                    <?php } else if ($form_data['form_status'] == "not-approved") { ?>
                                                        <div><b>Form Status : </b>
                                                            <span class='text-danger'>Not Approved</span>
                                                        </div>
                                                        <div><b>Reject Reason : </b><?= $form_data['reject_note'] ?> </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-md-12 mtop10 text-muted">Notifiy to Vendor</div>
                                                <div class="col-md-12 mtop5">
                                                    <?php
                                                    if (!empty($phoneNumberArr)) {
                                                        $whatsappMessage = vendorQuotationApproveNotApproveMessageContent($leadData->name, $form_data['form_status'], $formLink, $form_data['reject_note']);
                                                        foreach ($phoneNumberArr as $phoneNumber) {
                                                            $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                                                    ?>
                                                            <a href="<?= $whatappLink ?>" data-type="whatsapp" target="_blank" class="btn btn-success quotation-send-approved-not-approved-notify mleft5" data-toggle="tooltip" data-title="<?= $phoneNumber ?>">Share to Whatsapp <i class="fa fa-whatsapp"></i></a>
                                                    <?php }
                                                    } ?>
                                                    <?php if (!empty($leadData->email)) { ?>
                                                        <button type="button" data-type="email" class="btn btn-default quotation-send-approved-not-approved-notify" data-toggle="tooltip" data-title="<?= $leadData->email ?>">Send Email <i class="fa fa-envelope"></i></button>
                                                    <?php } ?>
                                                </div>

                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= site_url('assets/plugins/jquery-ui/jquery-ui.js') ?>"></script>
<script>
    var deletePermissionCheck = '<?php echo has_permission('vendors', '', 'delete') ?>';
    $(document).ready(function() {

        destroyEditorsByClass('text-editor');
        init_editor('.text-editor');

        $(document).off('click', '.quotation-form-save');
        $(document).on('click', '.quotation-form-save', function(e) {
            e.preventDefault();
            var main_panel = $(this).closest('.quotation-main-panel');
            var fileInput = main_panel.find("input[type='file']");
            if (fileInput.length > 0) {
                if (!quotationValidateFileInput(fileInput[0])) {
                    return false;
                }
            }
            var formId = main_panel.find('.vendor_quoation_forms_id').val();
            var form = $('#' + main_panel.attr('main-form-id'))[0];
            var form_data = new FormData(form);
            var terms_conditions = tinymce.get('fieldterms_conditions_' + formId).getContent();
            var notes = tinymce.get('fieldnotes_' + formId).getContent();
            form_data.append("terms_conditions", terms_conditions);
            form_data.append("notes", notes);
            $.ajax({
                url: "<?php echo admin_url('vendors/save_quotation_form') ?>",
                method: "POST",
                data: form_data,
                dataType: 'json',
                processData: false,
                contentType: false,
            }).done(function(result) {
                if (result.success) {
                    getVendorQuotationFormList(result.id);
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.quotation-form-edit');
        $(document).on('click', '.quotation-form-edit', function() {
            var main_panel = $(this).closest('.quotation-main-panel');
            if (main_panel.find('.quotation-form-section').hasClass('hide')) {
                main_panel.find('.quotation-preview-section').addClass('hide');
                main_panel.find('.quotation-form-section').removeClass('hide');
                main_panel.find('.quotation-form-save').removeClass('hide');
                main_panel.find('.quotation-form-edit').addClass('hide');
                if (!main_panel.find('.panel-collapse ').hasClass('in')) {
                    main_panel.find('.accordian-collapse').trigger('click');
                }
            } else {
                main_panel.find('.quotation-preview-section').removeClass('hide');
                main_panel.find('.quotation-form-section').addClass('hide');
                main_panel.find('.quotation-form-save').addClass('hide');
                main_panel.find('.quotation-form-edit').removeClass('hide');
            }
        });

        $(document).off('click', '.quotation-item-add');
        $(document).on('click', '.quotation-item-add', function(e) {
            e.preventDefault();
            var main_panel = $(this).closest('.quotation-main-panel');
            var form = $('#' + main_panel.attr('item-form-id'))[0];
            var form_data = new FormData(form);
            var formID = main_panel.find('.vendor_quoation_forms_id').val();
            form_data.append('vendor_quotation_form_id', formID);
            $.ajax({
                url: "<?php echo admin_url('vendors/save_quotation_form_items') ?>",
                method: "POST",
                data: form_data,
                dataType: 'json',
                processData: false,
                contentType: false,
            }).done(function(result) {
                if (result.success) {
                    getVendorQuotationFormList(formID, 'edit');
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.quotation-item-edit');
        $(document).on('click', '.quotation-item-edit', function() {
            var row = $(this).closest('.quotation-item-row');
            var id = row.attr('data-id');
            var main_panel = $(this).closest('.quotation-main-panel');
            main_panel.find('textarea[name="service_description"]').val(row.find('td:nth-child(2)').text());
            main_panel.find('input[name="hsn_sac"]').val(row.find('td:nth-child(3)').text());
            main_panel.find('input[name="qty"]').val(row.find('td:nth-child(4)').text());
            main_panel.find('input[name="unit"]').val(row.find('td:nth-child(5)').text());
            main_panel.find('input[name="price_in_inr"]').val(row.find('td:nth-child(6)').text());
            main_panel.find('input[name="amount_in_inr"]').val(row.find('td:nth-child(7)').text());
            main_panel.find('.quotation-dynamic-section').find('input[name="id"]').val(id);
            main_panel.find('.quotation-item-add').text('Update');
            main_panel.find('.quotation-update-cancel').removeClass('hide');
        });

        $(document).off('click', '.quotation-update-cancel');
        $(document).on('click', '.quotation-update-cancel', function() {
            var main_panel = $(this).closest('.quotation-main-panel');
            var form = $('#' + main_panel.attr('item-form-id'))[0].reset();
            main_panel.find('.quotation-item-add').text('Add');
            main_panel.find('.quotation-update-cancel').addClass('hide');
        });

        $(document).off('click', '.quotation-item-delete');
        $(document).on('click', '.quotation-item-delete', function() {
            var main_panel = $(this).closest('.quotation-main-panel');
            var formID = main_panel.find('.vendor_quoation_forms_id').val();
            var id = $(this).closest('tr').attr('data-id');
            if (id != "" && id != null) {
                if (confirm_delete()) {
                    $.ajax({
                        url: "<?php echo admin_url('vendors/delete_quotation_item') ?>",
                        method: "POST",
                        data: {
                            id: id
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            getVendorQuotationFormList(formID, 'edit');
                        } else {
                            alert_float('danger', result.message);
                        }
                    });

                }
            }
        });

        $(document).off('change', '.quotation-formswitch');
        $(document).on('change', '.quotation-formswitch', function() {
            var formid = $(this).closest('.quotation-main-panel').find('.vendor_quoation_forms_id').val();
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('vendors/vendor_quotation_form_status_change') ?>",
                method: "POST",
                data: {
                    id: formid,
                    status: status
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.quotation-form-delete');
        $(document).on('click', '.quotation-form-delete', function() {
            var panel = $(this).closest('.quotation-main-panel');
            var formId = panel.find('.vendor_quoation_forms_id').val();
            if (confirm_delete()) {
                if (formId != "" && formId != null) {
                    $.ajax({
                        url: "<?php echo admin_url('vendors/delete_quotation_form') ?>",
                        method: "POST",
                        data: {
                            id: formId
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            panel.remove();
                        } else {
                            alert_float('danger', result.message);
                        }
                    });

                }
            }
        });

        $(document).off('input', '.decimal-value');
        $(document).on('input', '.decimal-value', function() {
            var value = $(this).val();
            var regex = /^\d*\.?\d{0,2}$/;
            if (!regex.test(value)) {
                $(this).val(value.substring(0, value.length - 1));
            }
        });

        $(document).off('change', '.quotation-main-panel input[type="file"]');
        $(document).on('change', '.quotation-main-panel input[type="file"]', function() {
            var formgroup = $(this).closest('.form-group');
            formgroup.find('.file-error').remove();
            const file = this.files[0];
            var validation = quotationValidateFileInput(this);
            if (file && validation) {
                const fileName = file.name
                const fileUrl = URL.createObjectURL(file);
                if (formgroup.find('.file-preview-section').length === 0) {
                    formgroup.append("<span class='file-preview-section'></span>");
                }
                const previewHtml = `
                    <span class='file-preview-section'>
                        Preview: <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                        <i class='fa fa-trash text-danger delete-quotation-new-file' aria-hidden='true'></i>
                    </span>
                `;
                formgroup.find('.file-preview-section').html(previewHtml);
            }
        });

        $(document).off('click', '.delete-quotation-new-file');
        $(document).on('click', '.delete-quotation-new-file', function() {
            var formgroup = $(this).closest('.form-group');
            if (confirm("Are you sure you want perform this action?")) {
                formgroup.find('.file-preview-section').remove();
                formgroup.find('input[type="file"]').val('');
            }
        });

        $(document).off('click', '.delete-quotation-file');
        $(document).on('click', '.delete-quotation-file', function() {
            var formgroup = $(this).closest('.form-group');
            var panel = $(this).closest('.quotation-main-panel');
            var formId = panel.find('.vendor_quoation_forms_id').val();
            if (confirm_delete()) {
                if (formId != "" && formId != null) {
                    $.ajax({
                        url: "<?php echo admin_url('vendors/delete_quotation_file') ?>",
                        method: "POST",
                        data: {
                            id: formId
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            formgroup.find('.file-preview-section').remove();
                        } else {
                            alert_float('danger', result.message);
                        }
                    });

                }
            }
        });

        $(document).off('change', 'input[type="radio"][name="form_status"]');
        $(document).on('change', 'input[type="radio"][name="form_status"]', function() {
            var type = $(this).val();
            if (type == "approved") {
                $(this).closest('.vendor-quotation-form-approval-section').find('textarea').attr('required', 'false');
                $(this).closest('.vendor-quotation-form-approval-section').find('.reject_reason_section').addClass('hide');
            } else {
                $(this).closest('.vendor-quotation-form-approval-section').find('textarea').attr('required', 'true');
                $(this).closest('.vendor-quotation-form-approval-section').find('.reject_reason_section').removeClass('hide');
            }
        });

        $(document).off('input', '.quotation-main-panel textarea[name="reject_note"]');
        $(document).on('input', '.quotation-main-panel textarea[name="reject_note"]', function() {
            var formgroup = $(this).closest('.form-group');
            formgroup.find('.error').remove();
            var type = $(this).val();
            if (this.value == "" || this.value == null) {
                formgroup.append("<span class='error text-danger'>Reject Reason Required...</span>")
            }
        });

        $(document).off('click', '.quotation-approval-form-save-btn');
        $(document).on('click', '.quotation-approval-form-save-btn', function() {
            var reject_note_obj = $(this).closest('.quotation-main-panel').find('textarea[name="reject_note"]');
            var reject_note = reject_note_obj.val();
            var formgroup = reject_note_obj.closest('.form-group');
            formgroup.find('.error').remove();
            var form_status = $(this).closest('.quotation-main-panel').find('input[type="radio"][name="form_status"]:checked').val();
            if (form_status == "not-approved") {
                if (reject_note == "" || reject_note == null) {
                    formgroup.append("<span class='error text-danger'>Reject Reason Required...</span>")
                    return false;
                }
            }
            var form = $(this).closest('.quotation-main-panel').find('.customerInquiryApprovalForm');
            var formId = $(this).closest('.quotation-main-panel').find('.vendor_quoation_forms_id').val();
            var formData = new FormData(form[0]);
            formData.append('form_id', formId);
            $.ajax({
                url: form.attr('action'),
                method: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                    getVendorQuotationFormList(formId);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.quotation-send-approved-not-approved-notify');
        $(document).on('click', '.quotation-send-approved-not-approved-notify', function() {
            var formId = $(this).closest('.quotation-main-panel').find('.vendor_quoation_forms_id').val();
            $.ajax({
                url: "<?php echo admin_url('vendors/send_quotation_form_approve_not_approved_notify') ?>",
                method: "POST",
                data: {
                    formId: formId,
                    type: $(this).attr('data-type')
                },
                dataType: 'json',
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('input', '.gst_validation');
        $(document).on('input', '.gst_validation', function() {
            var input = $(this).val().toUpperCase();
            var filteredInput = input.replace(/[^0-9A-Z]/g, '');
            if (filteredInput.length > 15) {
                filteredInput = filteredInput.slice(0, 15);
            }
            $(this).val(filteredInput);
        });

        $(document).off('input', "input[name='price_in_inr']");
        $(document).on('input', "input[name='qty']", function() {
            var parent = $(this).closest('.quotation-dynamic-section');
            amountCalculation(parent);
        });

        $(document).off('input', "input[name='price_in_inr']");
        $(document).on('input', "input[name='price_in_inr']", function() {
            console.log("test2")
            var parent = $(this).closest('.quotation-dynamic-section');
            amountCalculation(parent);
        });
    });

    function amountCalculation(parent) {
        var qty = Number(parent.find("input[name='qty']").val());
        var price = Number(parent.find("input[name='price_in_inr']").val());
        var amount = (price * qty).toFixed(2);
        parent.find("input[name='amount_in_inr']").val(amount);
    }

    function sendQuotationForm(lead_id, form_id, type) {
        if (lead_id != "" && form_id != "" && type != "") {
            $.ajax({
                url: "<?php echo admin_url('vendors/send_quotation_form') ?>",
                method: "POST",
                data: {
                    lead_id: lead_id,
                    form_id: form_id,
                    type: type,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    var panel = $('.panel-quotation-main-form-' + form_id);
                    if (type == "email") {
                        panel.find('.panel-heading').find('.fa-envelope').closest('a').addClass('border-success');
                    } else {
                        panel.find('.panel-heading').find('.fa-whatsapp').closest('a').addClass('border-success');
                    }
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    }

    function destroyEditorsByClass(className) {
        var elements = document.querySelectorAll('.' + className);
        elements.forEach(function(element) {
            var editor = tinymce.get(element.id);
            if (editor) {
                editor.remove();
            }
        });
    }

    function quotationValidateFileInput(fileInput) {
        var maxSize = 5 * 1024 * 1024;
        var allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
        var isValid = true;
        var errors = [];
        if (fileInput.files.length > 0) {
            var file = fileInput.files[0];
            var fileSize = file.size;
            var fileName = file.name;
            var fileExtension = fileName.split('.').pop().toLowerCase();

            if (fileSize > maxSize) {
                isValid = false;
                errors.push("File size exceeds. Allowed maximum file size allowed up to " + (maxSize / 1024 / 1024) + " MB");
            }

            if (!allowedExtensions.includes(fileExtension)) {
                isValid = false;
                errors.push("Invalid file type. Allowed extensions are: " + allowedExtensions.join(", "));
            }
        }
        $(fileInput).siblings('.file-error').remove();
        if (!isValid) {
            errors.forEach(function(error) {
                $("<div class='validation-error text-danger file-error'>").text(error).insertAfter($(fileInput));
            });
        }
        return isValid;
    }
</script>
<style>
    .vendor-form-btn-section {
        display: flex;
        margin-top: -5px;
    }

    .onoffswitch {
        top: 5px;
    }

    .border-success {
        border: 1px solid green !important;
    }

    .border-warning {
        border: 1px solid #ff6f00 !important;
    }

    .border-danger {
        border: 1px solid red !important;
    }

    .border-black {
        border: 1px solid black !important;
    }

    .quotation-main-panel {
        border-radius: 3px;
    }

    .review-label {
        position: absolute;
        margin-left: 5px;
        font-size: 10px !important;
    }
</style>