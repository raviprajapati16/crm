<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content email-templates">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('pdf_settings'); ?></h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><strong>ID</strong></th>
                                                <th><strong>Module</strong></th>
                                                <th><strong>Action</strong></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($settings as $setting) { ?>
                                                <tr>
                                                    <td><?= $setting['id'] ?></td>
                                                    <td><?= ucfirst($setting['rel_type']); ?></td>
                                                    <td><a class="btn btn-xs btn-info" href="<?php echo admin_url('pdfsettings/' . $setting['id']); ?>">Manage</a></td>
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