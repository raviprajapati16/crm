<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$panelClass = "";
$formLink = "";
$phoneNumberArr = phonenumberSplit($leadData->phonenumber);
if (isset($form_data)) {
    $panelClass .= "panel-saved-form panel-main-form-" . $form_data['id'];
    if ($form_data['form_status'] == "approved") {
        $panelClass .= " border-success ";
    } else if ($form_data['form_status'] == "not-approved") {
        $panelClass .= " border-danger ";
    } else if ($form_data['form_status'] == "pending" && !empty($form_data['customer_form_submitted'])) {
        $panelClass .= " border-warning ";
    } else {
        $panelClass .= " border-black ";
    }
    $formLink = site_url('forms/cif/') . $form_data["formkey"];
}
?>
<div class="panel panel-default <?= $panelClass; ?> main-panel mtop10">
    <?php
    $uniqid = uniqid();
    $main_form_id = "customerInquiryForm_" . $uniqid;
    $approval_form_id = "customerInquiryApprovalForm_" . $uniqid;
    ?>
    <?php echo form_open_multipart(admin_url('leads/save_inquiry_form'), array('id' => $main_form_id, "class" => "customerInquiryForm"));
    echo form_close(); ?>
    <?php echo form_open_multipart(admin_url('leads/inquiry_form_approval_status_change'), array('id' => $approval_form_id, "class" => "customerInquiryApprovalForm"));
    echo form_close(); ?>
    <input type="hidden" id="inquiryFormId" name="id" value="<?= (isset($form_data)) ? $form_data['id'] : '' ?>" form="<?= $main_form_id; ?>" />
    <input type="hidden" name="sub_group_id" value="<?= (isset($sub_group_data)) ? $sub_group_data['id'] : '' ?>" form="<?= $main_form_id; ?>" />
    <input type="hidden" name="main_group_id" value="<?= (isset($main_group_data)) ? $main_group_data['id'] : '' ?>" form="<?= $main_form_id; ?>" />
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" class="accordian-collapse" data-parent="#accordion<?= (isset($form_data)) ? 'old' : 'new' ?>" href="#<?= $uniqid ?>">
                <?= (isset($form_data)) ? 'Inquiry Form #' . leadFormIdRender("LIF", $form_data['lead_id'], $form_data['id']) : 'New Form' ?>
                <?php if (isset($form_data)) {
                    if ($form_data['form_status'] == "pending" && !empty($form_data['customer_form_submitted'])) {
                        echo '<span class="label label-warning review-label" data-toggle="tooltip" data-title="Review Customer Form">Review Pending</span>';
                    } elseif ($form_data['form_status'] == "approved") {
                        echo '<span class="label label-success review-label">Approved</span>';
                    } elseif ($form_data['form_status'] == "not-approved") {
                        echo '<span class="label label-danger review-label">Not Approved</span>';
                    } else if(empty($form_data['form_status']) && ($form_data['is_whatsapp_send'] == "1" || $form_data['is_email_send'] == "1")){
                        echo '<span class="label label-primary review-label">Sent</span>';
                    }
                ?>
                <?php
                }
                ?>
                <br>
                <small>
                    Main Group : <span style="color: black;"><?php echo (isset($main_group_data)) ? $main_group_data['name'] : '' ?></span>
                    <?php if ($sub_group_data) { ?>
                        | Sub Group : <span style="color: black;"><?php echo $sub_group_data['name'];  ?></span>
                    <?php } ?>
                </small>
            </a>
            <div class="pull-right inquiry-form-btn-section">
                <?php if (isset($form_data)) {
                    if (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])) {
                        if (empty($form_data['office_visit_form_id'])) {
                            if (!empty($phoneNumberArr)) {
                                $whatsappMessage = inquiryFormMessageContent($leadData->name, $productname, $formLink);
                                foreach ($phoneNumberArr as $phoneNumber) {
                                    $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                ?>
                                    <a href="<?= $whatappLink ?>" target="_blank" onclick="sendInquiryForm(<?= $form_data['lead_id'] ?>,<?= $form_data['id'] ?>,'whatsapp')" class="btn btn-default mleft5 btn-xs <?php echo ($form_data['is_whatsapp_send'] == '1') ? 'border-success' : '' ?>" data-toggle="tooltip" data-title="<?= $phoneNumber ?>"><i class="fa fa-whatsapp"></i></a>
                            <?php }
                            } ?>
                            <?php if (!empty($leadData->email)) { ?>
                                <a href="javascript:;" onclick="sendInquiryForm(<?= $form_data['lead_id'] ?>,<?= $form_data['id'] ?>,'email')" class="btn btn-default btn-xs mleft5 <?php echo ($form_data['is_email_send'] == '1') ? 'border-success' : '' ?>" data-toggle="tooltip" data-title="<?= $leadData->email ?>"><i class="fa fa-envelope"></i></a>
                            <?php } ?>
                            <div class="mleft5">
                                <div class="onoffswitch">
                                    <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox formswitch" id="form_switch_<?= $form_data['id'] ?>" <?= ($form_data['is_active'] == "1") ? "checked" : "" ?>>
                                    <label class="onoffswitch-label" for="form_switch_<?= $form_data['id'] ?>"></label>
                                </div>
                            </div>
                            <button type="button" class="btn btn-default inquiry-form-save btn-xs hide mleft5 "><i class="fa fa-save"></i> Save</button>
                            <button type="button" class="btn btn-default inquiry-form-edit btn-xs mleft5"><i class="fa fa-edit"></i></button>
                            <?php if ($form_data['form_status'] != "approved") { ?>
                                <button type="button" data-id="" class="btn btn-default text-danger inquiry-form-delete btn-xs mleft5"><i class="fa fa-trash"></i></button>
                            <?php }
                        } else {
                            ?>
                            <div class="ovf-linked-section">
                                <div>#<?= leadFormIdRender("OVF", $form_data['lead_id'], $form_data['office_visit_form_id']) ?></div>
                                <div><small>This form is auto generated by office visit form.</small></div>
                            </div>
                        <?php
                        } ?>
                    <?php } ?>
                <?php } else { ?>
                    <button type="button" class="btn btn-default inquiry-form-save btn-xs mleft5"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-default inquiry-form-edit btn-xs mleft5"><i class="fa fa-edit"></i></button>
                    <button type="button" data-id="" class="btn btn-default text-danger inquiry-form-delete btn-xs mleft5"><i class="fa fa-trash"></i></button>
                <?php } ?>
            </div>
            <?php if (!empty($form_data['customer_form_submitted'])) { ?>
                <br>
                <small>
                    Customer Form Submitted On <b><?= _d($form_data['customer_form_submitted']) ?></b>
                </small>
            <?php } ?>
        </h4>
    </div>
    <div id="<?= $uniqid ?>" class="panel-collapse collapse <?= (!isset($form_data)) ? 'in' : '' ?>">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12 mtop5 question-form-section hide">
                    <?php if (!empty($questions_data)) {
                        foreach ($questions_data as $key => $item) {
                            $item['lead_id'] = $form_data['lead_id'];
                            echo customer_inquiry_form_render($item, ($key + 1), false, $main_form_id);
                        }
                    } ?>
                    <div class="col-md-12 mtop5">
                        <button type="button" class="btn btn-primary form-save-btn">Save</button>
                    </div>
                </div>
            </div>
            <div class="row question-view-section">
                <div class="col-md-12 mtop5">
                    <?php if (!empty($questions_data)) {
                        foreach ($questions_data as $key => $item) {
                            if (isset($item['answer']) && !empty($item['answer'])) {
                                $answer = $item['answer'];
                                if ($item['type'] == "fileupload") {
                                    $file_url = site_url('download/file/lead_inquiry_form_files/' . $item['id']);
                                    $answer = "<a href='" . $file_url . "' target='_blank'>" . $item['answer'] . "</a>";
                                }
                                echo '<p class="text-muted lead-field-heading no-mtop">' . ($key + 1) . ') ' . ucfirst($item['question']) . '</p>
                                <p class="bold font-medium-xs">' . $answer . '</p>';
                            } else {
                                echo '<p class="text-muted lead-field-heading no-mtop">' . ($key + 1) . ') ' . ucfirst($item['question']) . '</p>
                                <p class="bold font-medium-xs">-</p>';
                            }
                        }
                    }
                    ?>
                </div>
                <?php
                if (isset($form_data) && (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])))
                    if (!empty($form_data['form_status'])) {
                ?>
                    <div class="col-md-12 mtop5 inquiry-form-approval-section">
                        <div class="panel panel-primary">
                            <div class="panel-heading"><b>Customer Inquiry Form Approval</b></div>
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
                                            <button type="button" class="btn btn-primary btn-sm approval-form-save-btn">Submit</button>
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
                                        <div class="col-md-12 mtop10 text-muted">Notifiy to Customer</div>
                                        <div class="col-md-12 mtop5">
                                            <?php
                                            if (!empty($phoneNumberArr)) {
                                                $whatsappMessage = inquiryFormApproveNotApproveMessageContent($leadData->name, $form_data['form_status'], $formLink, $form_data['reject_note']);
                                                foreach ($phoneNumberArr as $phoneNumber) {
                                                    $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                                            ?>
                                                    <a href="<?= $whatappLink ?>" data-type="whatsapp" target="_blank" class="btn btn-success send-approved-not-approved-notify mleft5" data-toggle="tooltip" data-title="<?= $phoneNumber ?>">Share to Whatsapp <i class="fa fa-whatsapp"></i></a>
                                            <?php }
                                            } ?>
                                            <?php if (!empty($leadData->email)) { ?>
                                                <button type="button" data-type="email" class="btn btn-default send-approved-not-approved-notify" data-toggle="tooltip" data-title="<?= $leadData->email ?>">Send Email <i class="fa fa-envelope"></i></button>
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
    <?php echo form_close(); ?>
</div>
<script src="<?= site_url('assets/plugins/jquery-ui/jquery-ui.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('#lead_inquiry_forms .question-form-section').sortable({
            start: function(event, ui) {
                ui.item.addClass("bg-danger");
            },
            stop: function(event, ui) {
                ui.item.removeClass("bg-danger");
            },
            update: function(event, ui) {
                $(this).closest('.question-form-section').find(".form-group").map(function(index) {
                    var id = $(this).attr('data-name');
                    $(this).find('span.question_index').html((index + 1) + ") ");
                });
            }
        });

        $(document).off('click', '.inquiry-form-edit');
        $(document).on('click', '.inquiry-form-edit', function() {
            var parent = $(this).closest('.panel');
            if (parent.find('.question-form-section').hasClass('hide')) {
                parent.find('.question-form-section').removeClass('hide');
                parent.find('.question-view-section').addClass('hide');
                parent.find('.inquiry-form-save').removeClass('hide');
            } else {
                parent.find('.question-form-section').addClass('hide');
                parent.find('.question-view-section').removeClass('hide');
                parent.find('.inquiry-form-save').addClass('hide');
            }
            if (!parent.find('.question-form-section').hasClass('in')) {
                parent.find('.collapse').collapse('show');

            }
        });

        $(document).off('click', '.inquiry-form-delete');
        $(document).on('click', '.inquiry-form-delete', function() {
            var panel = $(this).closest('.panel');
            var formId = panel.find("#inquiryFormId").val();
            if (confirm_delete()) {
                if (formId != "" && formId != null) {
                    $.ajax({
                        url: "<?php echo admin_url('leads/delete_inquiry_form') ?>",
                        method: "POST",
                        data: {
                            formId: formId
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

                } else {
                    $('#main_group_id').val("").selectpicker('refresh');
                    $('#sub_group_id').val("").selectpicker('refresh');
                    $('.inquiry-new-form-section').empty();
                    alert_float('success', "Form deleted successfully");
                }
            }

        });

        $(document).off('click', '.form-save-btn');
        $(document).on('click', '.form-save-btn', function() {
            $(this).closest('.panel').find('.inquiry-form-save').trigger('click');
        });

        $(document).off('click', '.inquiry-form-save');
        $(document).on('click', '.inquiry-form-save', function() {
            var form = $(this).closest('.panel').find('.customerInquiryForm');
            var formData = new FormData(form[0]);
            formData.append('leadid', $('input[name="leadid"]').val());
            var form = $(this).closest('.panel').find('form');
            var fileInput = form.find("input[type='file']");
            if (fileInput.length > 0) {
                if (!validateFileInput(fileInput[0])) {
                    return false;
                }
            }
            // Question Order
            $(this).closest('.main-panel').find('.question-form-section').find(".form-group").map(function(index) {
                var id = $(this).attr('data-name');
                formData.append('order[' + id + ']', (index + 1));
            }).get();
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
                    $('.inquiry-new-form-section').empty();
                    getInquiryFormLists();
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('change', '.formswitch');
        $(document).on('change', '.formswitch', function() {
            var formid = $(this).closest('.panel').find('#inquiryFormId').val();
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads/customer_inquiry_form_status_change') ?>",
                method: "POST",
                data: {
                    formid: formid,
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
    });

    $(document).off('change', '.question-form-section input[type="file"]');
    $(document).on('change', '.question-form-section input[type="file"]', function() {
        var formgroup = $(this).closest('.form-group');
        formgroup.find('.file-error').remove();
        const file = this.files[0];
        var validation = validateFileInput(this);
        if (file && validation) {
            const fileName = file.name
            const fileUrl = URL.createObjectURL(file);
            if (formgroup.find('.file-preview-section').length === 0) {
                formgroup.append("<span class='file-preview-section'></span>");
            }
            const previewHtml = `
                    <span class='file-preview-section'>
                        Preview: <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                        <i class='fa fa-trash text-danger delete-inquiry-new-file' aria-hidden='true'></i>
                    </span>
                `;
            formgroup.find('.file-preview-section').html(previewHtml);
        }
    });

    $(document).off('click', '.delete-inquiry-file');
    $(document).on('click', '.delete-inquiry-file', function() {
        var formgroup = $(this).closest('.form-group');
        var formid = $(this).closest('.panel').find('#inquiryFormId').val();
        var questionId = formgroup.attr('data-name');
        if (confirm("Are you sure you want perform this action?")) {
            $.ajax({
                url: "<?php echo admin_url('leads/delete_inquiry_file') ?>",
                method: "POST",
                data: {
                    formid: formid,
                    id: questionId,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    formgroup.find('.file-preview-section').remove();
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        }

    });

    $(document).off('click', '.delete-inquiry-new-file');
    $(document).on('click', '.delete-inquiry-new-file', function() {
        var formgroup = $(this).closest('.form-group');
        if (confirm("Are you sure you want perform this action?")) {
            formgroup.find('.file-preview-section').remove();
            formgroup.find('input[type="file"]').val('');
        }
    });

    $(document).off('click', '.review-label');
    $(document).on('click', '.review-label', function() {
        $(this).parent().find('.accordian-collapse').trigger('click');
    });

    $(document).off('change', 'input[type="radio"][name="form_status"]');
    $(document).on('change', 'input[type="radio"][name="form_status"]', function() {
        var type = $(this).val();
        if (type == "approved") {
            $(this).closest('.inquiry-form-approval-section').find('textarea').attr('required', 'false');
            $(this).closest('.inquiry-form-approval-section').find('.reject_reason_section').addClass('hide');
        } else {
            $(this).closest('.inquiry-form-approval-section').find('textarea').attr('required', 'true');
            $(this).closest('.inquiry-form-approval-section').find('.reject_reason_section').removeClass('hide');
        }
    });

    $(document).off('input', 'textarea[name="reject_note"]');
    $(document).on('input', 'textarea[name="reject_note"]', function() {
        var formgroup = $(this).closest('.form-group');
        formgroup.find('.error').remove();
        var type = $(this).val();
        if (this.value == "" || this.value == null) {
            formgroup.append("<span class='error text-danger'>Reject Reason Required...</span>")
        }
    });

    $(document).off('click', '.approval-form-save-btn');
    $(document).on('click', '.approval-form-save-btn', function() {
        var reject_note_obj = $(this).closest('.panel-saved-form').find('textarea[name="reject_note"]');
        var reject_note = reject_note_obj.val();
        var formgroup = reject_note_obj.closest('.form-group');
        formgroup.find('.error').remove();
        var form_status = $(this).closest('.panel-saved-form').find('input[type="radio"][name="form_status"]:checked').val();
        if (form_status == "not-approved") {
            if (reject_note == "" || reject_note == null) {
                formgroup.append("<span class='error text-danger'>Reject Reason Required...</span>")
                return false;
            }
        }
        var form = $(this).closest('.panel-saved-form').find('.customerInquiryApprovalForm');
        var formId = $(this).closest('.panel-saved-form').find('#inquiryFormId').val();
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
                getInquiryFormLists(formId);
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    $(document).off('click', '.send-approved-not-approved-notify');
    $(document).on('click', '.send-approved-not-approved-notify', function() {
        var formId = $(this).closest('.panel-saved-form').find('#inquiryFormId').val();

        $.ajax({
            url: "<?php echo admin_url('leads/send_inquiry_form_for_approve_not_approved_notify') ?>",
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



    function sendInquiryForm(lead_id, form_id, type) {
        if (lead_id != "" && form_id != "" && type != "") {
            $.ajax({
                url: "<?php echo admin_url('leads/send_inquiry_form') ?>",
                method: "POST",
                data: {
                    lead_id: lead_id,
                    form_id: form_id,
                    type: type,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    var panel = $('input[name="id"][value="' + form_id + '"]').closest('.panel-saved-form');
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

    function validateFileInput(fileInput) {
        var maxSize = 5 * 1024 * 1024; // 5 MB
        var allowedExtensions = ["jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx", "csv"];
        var isValid = true;
        var errors = [];
        if (fileInput.files.length > 0) {
            var file = fileInput.files[0];
            var fileSize = file.size; // Size in bytes
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
    .inquiry-form-btn-section {
        display: flex;
        margin-top: -5px;
    }

    .onoffswitch {
        top: 5px;
    }

    .form-group {
        cursor: pointer;
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

    .review-label {
        position: absolute;
        margin-left: 5px;
        font-size: 10px !important;
    }

    .panel-saved-form {
        padding: 1px;
    }

    .todo-dragger {
        margin-top: -8px;
    }

    .question_index {
        margin-left: 17px;
    }

    .inquiry-form-section .ovf-linked-section {
        position: relative;
        top: -9px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .inquiry-form-section .panel-default>.panel-heading {
            height: 170px;
        }

        .inquiry-form-section .review-label {
            position: relative;
            top: 38px;
            left: 200px;
        }

        .inquiry-form-section .inquiry-form-btn-section {
            margin-top: 13px;
            margin-right: 48px;
        }
    }
</style>