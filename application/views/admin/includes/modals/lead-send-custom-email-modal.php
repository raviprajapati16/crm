<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.tagify__dropdown{
    z-index: 9999999;
}
.lead-emails-input{
    width: 100%;
}
</style>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <?php echo form_open_multipart('admin/leads/leadSendCustomEmail', array('id' => 'form_lead_email_send')); ?>
        <div class="modal-header">
            <button type="button" class="close close-email-send-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Email Send</h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="email_send_lead_id" value="" form="form_lead_email_send" />
            <div class="row">
                <div class="form-group col-md-12">
                    <label for="fieldlead_email_send_to">To:</label>
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="fieldlead_email_send_to" name="to" class="lead-emails-input" value="" form="form_lead_email_send" required>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label for="fieldlead_email_send_cc">CC:</label>
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="fieldlead_email_send_cc" name="cc" class="lead-emails-input" value="" form="form_lead_email_send">
                    </div>
                </div>

                <?= all_type_input_render([
                    "label" => "Templates",
                    "id" => "lead_email_send_template",
                    "type" => "select",
                    "is_required" => true,
                    "form" => "form_lead_email_send",
                ], 'col-md-12', false);
                ?>

                <?= all_type_input_render([
                    "label" => "Enter Subject",
                    "id" => "lead_email_subject",
                    "name" => "lead_email_subject",
                    "type" => "text",
                    "is_required" => true,
                    "form" => "form_lead_email_send",
                ], 'col-md-12', false);
                ?>
                <?= all_type_input_render([
                    "label" => "Enter Mail Content",
                    "id" => "lead_email_body",
                    "name" => "lead_email_body",
                    "type" => "textarea",
                    "rows" => 5,
                    "is_required" => true,
                    "className" => "lead_email_body",
                    "form" => "form_lead_email_send",
                ], 'col-md-12', false);
                ?>
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="control-label">Attachments</div>
                        <input type="file" name="email_attachments[]" id="email_attachments" class="form-control" value="" form="form_lead_email_send" multiple>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close-email-send-modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info">Send</button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<style>
    .mce-content-body {
        overflow-y: scroll !important;
    }

    /* .mce-edit-area iframe {
        height: 100% !important;
    } */
</style>