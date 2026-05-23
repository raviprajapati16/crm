<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4>mPDF Settings</h4>
<div class="tab-content mtop30">
    <?php $signature = get_option('signature_image'); ?>
    <?php if ($signature != '') { ?>
        <div class="form-group">
            <label><strong>Signature and stamp</strong></label>
            <div class="row">
                <div class="col-md-9">
                    <img width="30%" src="<?php echo base_url('uploads/company/' . $signature); ?>" class="img img-responsive">
                </div>
                <?php if (has_permission('settings', '', 'delete')) { ?>
                    <div class="col-md-3 text-right">
                        <a href="<?php echo admin_url('settings/remove_signature_image_mpdf'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } else { ?>
        <div class="form-group">
            <label for="signature_image" class="control-label"><?php echo _l('signature_image'); ?></label>
            <input type="file" name="signature_image" class="form-control" accept=".jpg,.jpeg,.png">
        </div>
    <?php } ?>
</div>