<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->view('include_top'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open('admin/product_management/product/' . $product[0]->productid, array('id' => 'AddEditProduct')); ?>
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title"><?= $title ?></h4>
                        <hr class="hr-panel-heading" />
                        <input type="hidden" id="productid" name="productid" class="form-control" value="<?= $product[0]->productid ?>">
                        <div class="col-md-6">
                            <div class="form-group" app-field-wrapper="order"><label for="order" class="control-label"> <small class="req text-danger">* </small>Assinment Priority</label><input type="text" id="order" name="order" class="form-control" value="<?= $product[0]->order ?>"></div>
                            <div class="form-group" app-field-wrapper="name"><label for="name" class="control-label"> <small class="req text-danger">* </small>Product Name</label><input type="text" id="name" name="name" class="form-control" value="<?= $product[0]->name ?>"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" app-field-wrapper="searchterms"><label for="searchterms" class="control-label"><small class="req text-danger">* </small>Search Terms</label><textarea id="searchterms" name="searchterms" class="form-control" rows="3" style="height: 70px; font-size: 100%; padding-top: 9px;" aria-invalid="false"><?= $product[0]->searchterms ?></textarea></div>
                        </div>
                        <div class="col-md-12">
                            <hr />
                            <button type="submit" class="btn btn-info pull-right lead-save-btn" id="lead-form-submit"><?php echo _l('submit'); ?></button>
                            <button type="button" class="btn btn-default pull-right mright5" data-dismiss="modal" onclick="window.location='/admin/product_management/products'"><?php echo _l('close'); ?></button>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php $this->load->view('include_bottom'); ?>