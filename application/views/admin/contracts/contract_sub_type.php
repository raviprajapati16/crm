<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="subtypemodal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('contracts/sub_type'), array('id'=>'contract-sub-type-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Agreement Sub Type</span>
                    <span class="add-title">New Agreement Sub Type</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <?php echo render_input('name', 'contract_type_name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="main_type" value="<?= $main_type->id  ?>"/>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
  window.addEventListener('load',function(){
      appValidateForm($('#contract-sub-type-form'),{name:'required'},manage_contract_sub_types);
      $('#subtypemodal').on('hidden.bs.modal', function(event) {
        $('#additional').html('');
        $('#subtypemodal input[name="name"]').val('');
        $('.add-title').removeClass('hide');
        $('.edit-title').removeClass('hide');
    });
  });
  function manage_contract_sub_types(form) {
    var data = $(form).serialize();
    var url = form.action;
    $.post(url, data).done(function(response) {
        response = JSON.parse(response);
        if(response.success == true){
            alert_float('success',response.message);
            if($('body').hasClass('contract') && typeof(response.id) != 'undefined') {
                var ctype = $('#contract_type');
                ctype.find('option:first').after('<option value="'+response.id+'">'+response.name+'</option>');
                ctype.selectpicker('val',response.id);
                ctype.selectpicker('refresh');
            }
        }
        if($.fn.DataTable.isDataTable('.table-sub-contract-types')){
            $('.table-sub-contract-types').DataTable().ajax.reload();
        }
        $('#subtypemodal').modal('hide');
    });
    return false;
}
function new_type(){
    $('#subtypemodal').modal('show');
    $('.edit-title').addClass('hide');
}
function edit_type(invoker,id){
    var name = $(invoker).data('name');
    $('#additional').append(hidden_input('id',id));
    $('#subtypemodal input[name="name"]').val(name);
    $('#subtypemodal').modal('show');
    $('.add-title').addClass('hide');
}
</script>
