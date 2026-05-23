<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_merchant(); return false;" class="btn btn-info pull-left display-block">New Merchant</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array("#","Name", "Details", _l('options')), 'expenses-merchants'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="expense-merchant-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('expenses_merchants/save'), array('id' => 'expense-merchant-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Merchant</span>
                    <span class="add-title">Add Merchant</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'Merchant Name'); ?>
                        <?php echo render_textarea('details', 'Merchant Details'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-expenses-merchants', window.location.href, [2], [2], undefined, [0, 'desc']);
        window.addEventListener('load', function() {
            appValidateForm($('#expense-merchant-form'), {
                name: 'required'
            }, manage_merchants);
            $('#expense-merchant-modal').on('hidden.bs.modal', function(event) {
                $('#additional').html('');
                $('#expense-merchant-modal input[name="name"]').val('');
                $('#expense-merchant-modal textarea').val('');
                $('.add-title').removeClass('hide');
                $('.edit-title').removeClass('hide');
            });
        });
    });

    function manage_merchants(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);

            if (response.success == true) {
                alert_float('success', response.message);
                if ($('body').hasClass('expense') && typeof(response.id) != 'undefined') {
                    var merchant = $('#merchant');
                    merchant.find('option:first').after('<option value="' + response.id + '">' + response.name + '</option>');
                    merchant.selectpicker('val', response.id);
                    merchant.selectpicker('refresh');
                }
            }

            if ($.fn.DataTable.isDataTable('.table-expenses-merchants')) {
                $('.table-expenses-merchants').DataTable().ajax.reload();
            }

            $('#expense-merchant-modal').modal('hide');
        });
        return false;
    }

    function new_merchant() {
        $('#expense-merchant-modal').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_merchant(invoker, id) {
        var name = $(invoker).data('name');
        var details = $(invoker).data('details');
        $('#additional').append(hidden_input('id', id));
        $('#expense-merchant-modal input[name="name"]').val(name);
        $('#expense-merchant-modal textarea').val(details);
        $('#expense-merchant-modal').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
</body>

</html>