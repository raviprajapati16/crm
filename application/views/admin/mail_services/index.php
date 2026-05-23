<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content email-templates">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('mailservices/service'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_service'); ?></a>
                        </div>
                        <div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('mail_services'); ?></h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><strong>ID</strong></th>
                                                <th><strong>Service Name</strong></th>
                                                <th><strong>Action</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($settings as $setting) { ?>
                                                <tr>
                                                    <td><?= $setting['id'] ?></td>
                                                    <td><?= ucfirst($setting['service_name']); ?></td>
                                                    <td><a class="btn btn-xs btn-info" href="<?php echo admin_url('mailservices/service/' . $setting['id']); ?>"><i class="fa fa-edit"></i></a>
                                                    <a class="btn btn-xs btn-danger" href="<?php echo admin_url('mailservices/delete/' . $setting['id']); ?>"><i class="fa fa-trash"></i></a></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>