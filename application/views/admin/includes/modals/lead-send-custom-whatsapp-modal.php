<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <?php echo form_open('admin/leads/leadSendCustomWhatsapp', array('id' => 'form_lead_whatsapp_send')); ?>
        <div class="modal-header">
            <button type="button" class="close close-whatsapp-send-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Whatsapp Send</h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="whatsapp_send_lead_id" value="" form="form_lead_whatsapp_send" />
            <input type="hidden" name="lead_whatsapp_number" value="" form="form_lead_whatsapp_send" />
            <div class="row">
                <?= all_type_input_render([
                    "label" => "TO",
                    "id" => "lead_whatsapp_send",
                    "type" => "text",
                    "form" => "form_lead_whatsapp_send",
                    "is_readonly" => "true",
                ], 'col-md-12', false);
                ?>
                <?= all_type_input_render([
                    "label" => "Templates",
                    "id" => "lead_whatsapp_send_template",
                    "type" => "select",
                    "form" => "form_lead_email_send",
                ], 'col-md-12', false);
                ?>
                <?= all_type_input_render([
                    "label" => "Enter Your Message",
                    "id" => "lead_whatsapp_body",
                    "name" => "lead_whatsapp_body",
                    "type" => "textarea",
                    "rows" => 9,
                    "is_required" => true,
                    "className" => "lead_whatsapp_body",
                    "form" => "form_lead_whatsapp_send",
                ], 'col-md-12', false);
                ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close-whatsapp-send-modal"><?php echo _l('close'); ?></button>
            <button type="submit" class="btn btn-info whatsapp-btn" name="submit" value="web"><i class="fa fa-whatsapp" aria-hidden="true"></i> Web Share</button>
            <button type="submit" class="btn btn-info whatsapp-btn" name="submit" value="app"><i class="fa fa-whatsapp" aria-hidden="true"></i> App Share</button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<style>
    .mce-content-body {
        overflow-y: scroll !important;
    }

    #fieldlead_whatsapp_body {
        resize: none;
    }

    #form_lead_whatsapp_send .whatsapp-btn {
        background-color: #25d366 !important;
        border-color: #25d366 !important;
        font-family: Arial, sans-serif !important;
        font-weight: bold !important;
        color: #fff !important;
    }


    #form_lead_whatsapp_send .fa-whatsapp {
        font-size: 22px;
        vertical-align: bottom;
    }

    #form_lead_whatsapp_send .whatsapp-btn:hover {
        background-color: #075e54 !important;
        border-color: #075e54 !important;
    }

    /* .mce-edit-area iframe {
        height: 100% !important;
    } */
</style>