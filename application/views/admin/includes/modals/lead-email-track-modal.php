<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close close-email-track-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Lead Emails Tracking</h4>
        </div>
        <div class="modal-body">
            <?php
            if (isset($lead->id)) {
                $this->load->view(
                    'admin/includes/emails_tracking',
                    array(
                        'tracked_emails' =>
                        get_tracked_emails($lead->id, 'lead')
                    )
                );
            }
            ?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close-email-track-modal"><?php echo _l('close'); ?></button>
        </div>
    </div>
</div>
<style>
    .mce-content-body {
        overflow-y: scroll !important;
    }

    #fieldlead_whatsapp_body {
        resize: none;
    }

    /* .mce-edit-area iframe {
        height: 100% !important;
    } */
</style>