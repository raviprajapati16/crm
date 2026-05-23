<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('google_sheets/index'); ?>" class="btn btn-info mright5 pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h4 class="no-margin" id="head_title">Google Sheets : <b> <?= $sheet->sheet_title ?></b></h4>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            'ID',
                            'Name',
                            'Email',
                            'Phone No.',
                            'Leads Import Status',
                            'View Record Details',
                        ), 'google-sheets-records'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="recordDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Record Details</h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    var table = initDataTable('.table-google-sheets-records', "<?= admin_url('google_sheets/view_sheets_records/' . $sheet->id) ?>", false, [0, 1, 2, 3, 4]);
    $('body').on('click', '.view-record', function(e) {
        e.preventDefault();
        var recordId = $(this).data('id');
        var recordDataString = $(this).attr('data-record');
        try {
            var  recordData = JSON.parse(JSON.parse(recordDataString));
            var modalContent = '<div class="table-responsive"><table class="table table-bordered">';
            for (var key in recordData) {
                if (recordData.hasOwnProperty(key)) {
                    modalContent += '<tr><th>' + key.replace(/_/g, ' ').toUpperCase() + '</th><td>' + recordData[key] + '</td></tr>';
                }
            }
            modalContent += '</table></div>';
            $('#recordDetailsModal .modal-body').html(modalContent);
            $('#recordDetailsModal').modal('show');
        } catch (e) {
            console.error("Error processing JSON data:", e);
            console.log("Raw data string:", recordDataString);
            alert("Error loading record details. See console for more information.");
        }
    });
</script>