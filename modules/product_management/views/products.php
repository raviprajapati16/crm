<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->view('include_top'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 col-6">
                                <span class="h4" id="head_title"><?= $title ?></span>
                            </div>
                            <div class="col-md-6 col-6">
                                <div class="_buttons pull-right">
                                    <a href="<?php echo admin_url('product_management/product'); ?>" class="btn mright5 btn-info pull-left display-block">New Product</a>
                                </div>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <table id="proposal_templates" width="100%" class="table customizable-table dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Assinment Priority</th>
                                    <th>Name</th>
                                    <th>Search Terms</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product) {
                                ?>
                                    <tr id="template_<?= $product->productid ?>">
                                        <td>
                                            <a href="product_management/product/<?= $product->productid ?>"> Edit </a>
                                            <!-- <a onClick="editproduct(<?= $product->productid ?>)"> Edit </a> -->
                                        </td>
                                        <td><?= $product->order ?> </td>
                                        <td><?= $product->name ?></td>
                                        <td><?= $product->searchterms ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('include_bottom'); ?>