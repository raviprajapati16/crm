<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title">Manage Google Sheets</h4>
                        <hr class="hr-panel-heading" />
                        <div class="_buttons">
                            <a href="<?php echo admin_url('google_sheets/create'); ?>" class="btn btn-info pull-left display-block">Create</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable(array(
                            'No.',
                            'Sheet Title',
                            'Created At'
                        ), 'google-sheets'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    var table = initDataTable('.table-google-sheets', "<?= admin_url('google_sheets/index') ?>", false, [0, 1, 2]);
</script>