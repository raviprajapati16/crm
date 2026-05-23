<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="sync_data_purchase_data" data-rel-type="lead" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-purchase-sync-modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('sync_data'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $lang_key = 'purchase';
                ?>
                <p><?php echo _l('purchase_sync_1_info', array(_l($lang_key), _l($lang_key))); ?></p>
                <p><?php echo _l('purchase_sync_2_info', _l($lang_key)); ?></p>
                <?php echo render_textarea('address', 'proposal_address', $related->address); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_input('city', 'billing_city', $related->city); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('state', 'billing_state', $related->state); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $countries = get_all_countries(); ?>
                        <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $related->country); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_input('zip', 'billing_zip', $related->zip); ?>
                    </div>
                </div>
                <?php echo render_input('phone', 'proposal_phone', $related->phonenumber); ?>
            </div>
            <div class="modal-footer">

                <button type="button" class="btn btn-info" onclick="sync_purchase_data(<?php echo $vendor_id; ?>);"><?php echo _l('sync_now'); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->