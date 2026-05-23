<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading"><?= _l('customer_media') ?></h4>
    <?php if (has_permission('customer_media', '', 'create')) {
        ?>
        <div class="inline-block new-contact-wrapper">
            <a href="#" onclick="customerMediaFormPopup(<?php echo $client->userid; ?>); return false;"
                class="btn btn-info new-media mbot25">Create</a>
        </div>
    <?php } ?>
    <?php
    $table_data = array("#", "Title", "Media Type", "View Media");
    echo render_datatable($table_data, 'customer-media'); ?>
<?php } ?>
<div id="customer-media"></div>
<div class="modal fade" id="customerMediaModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('customer_media/save'), array("name" => "customerMediaForm", "id" => "customerMediaForm")); ?>
        <input type="hidden" id="media_id" name="id" value="" form="customerMediaForm" />
        <input type="hidden" id="customer_id" name="customer_id" value="<?php echo $client->userid; ?>"
            form="customerMediaForm" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="rel_type" class="control-label">Media Type</label>
                            <select name="rel_type" id="rel_type" onchange="changeMediaType()" class="selectpicker"
                                data-width="100%" data-none-selected-text="Select Media" form="customerMediaForm"
                                required>
                                <option value="">Select Media Type</option>
                                <option value="product_presentation">Presentation</option>
                                <option value="brochure">Brochure</option>
                                <option value="tutorial">Video</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="rel_id" class="control-label">Media</label>
                            <select name="rel_id" id="rel_id" class="selectpicker" data-width="100%"
                                data-none-selected-text="Select Media" form="customerMediaForm" required>
                                <option value="">Select Media</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="customerMediaForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>