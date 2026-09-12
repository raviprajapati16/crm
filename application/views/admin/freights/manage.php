<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('freights', '', 'create')) { ?>
                                <a href="<?php echo admin_url('freights/freight'); ?>" class="btn btn-info pull-left display-block">
                                    Add New Freight
                                </a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable([
                            'ID',
                            'From City/Port',
                            'To Country',
                            'To City/Port',
                            'Truck/Container',
                            'Size',
                            'Carrier',
                            'Freight',
                            'Transit Time',
                            'Action',
                        ], 'freights'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Freight Modal -->
<div class="modal fade" id="view_freight_modal" tabindex="-1" role="dialog" aria-labelledby="view_freight_modal_label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="view_freight_modal_label">Freight Details</h4>
            </div>
            <div class="modal-body" id="freight_details_content">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-freights', window.location.href, [9], [9]);
    });

    function view_freight(id) {
        requestGet('freights/get_freight_details/' + id).done(function(response) {
            $('#freight_details_content').html(response);
            $('#view_freight_modal').modal('show');
        });
    }
</script>
</body>

</html>