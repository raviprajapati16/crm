<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading">Assigned Leads</h4>
    <?php
    $table_data = array("Lead ID", "Name", "Company", "Phone", "Email", "Address", "Status", "Last Updated By");
    echo render_datatable($table_data, 'customer-assigned-leads'); ?>
<?php } ?>
<div class="modal fade" id="leadDataModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row" id="leadDataContent">
                    <!-- JS will inject lead fields here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                </div>
            </div>

        </div>
    </div>
</div>