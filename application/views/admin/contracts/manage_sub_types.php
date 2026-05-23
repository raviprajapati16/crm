<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('contracts/types'); ?>" class="btn btn-info mright5  pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="_buttons">
                            <a href="#" onclick="new_type(); return false;" class="btn btn-info pull-left display-block"><?php echo _l('new_sub_type'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h5>Main Type : <span class="label label-primary selected-main-type" data-id="<?php echo (isset($main_type)) ? $main_type->id : '' ?>"> <?php echo (isset($main_type)) ? $main_type->name : '' ?> </span>
                        </h5>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('name'),
                            _l('contract_counts'),
                            _l('draft_counts'),
                            _l('options')
                        ), 'sub-contract-types'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="copy_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('contracts/copy_contract_type_data'), ['id' => "copyForm"]); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _l('copy') ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">Select target main type to copy selected sub types and its draft data.</p>
                    </div>
                    <input type="hidden" name="type" value="sub_type_copy" />
                    <div class="col-md-6">
                        <h5>Selected Main Type : </h5>
                        <h5><span class="label label-primary"> <?php echo (isset($main_type)) ? $main_type->name : '' ?> </span>
                            <input type="hidden" name="from_main_type" value="<?php echo (isset($main_type)) ? $main_type->id : '' ?>" />
                    </div>
                    <div class="col-md-6">
                        <h5>Selected Sub Type : </h5>
                        <h5><span class="label label-primary selected-sub-type"></span>
                            <input type="hidden" id="from_sub_type" name="from_sub_type" value="" />
                    </div>
                    <div class="col-md-6 mtop15 main-group-section">
                        <label for="to_main_type" class="control-label">Select Target Main Type</label>
                        <select name="to_main_type" id="to_main_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                            <option value=""></option>
                            <?php
                            foreach ($main_type_list as $key => $item) {
                            ?>
                                <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('Copy'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php $this->load->view('admin/contracts/contract_sub_type'); ?>
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-sub-contract-types', window.location.href);
        appValidateForm($('#copyForm'), {
            to_main_type: 'required',
        });

        var fromValue = $('input[name="from_main_type"]').val();
        $('#to_main_type option[value="' + fromValue + '"]').hide();
        $('#to_main_type').selectpicker('refresh');

        $(document).on('click', '._copy', function() {
            var sub_type_id = $(this).closest('tr').find('td:first').find('a').attr('data-id');
            var sub_type_name = $(this).closest('tr').find('td:first').find('a').attr('data-name');
            $('#from_sub_type').val(sub_type_id);
            $('.selected-sub-type').text(sub_type_name);
            $('#copy_modal').modal("show");
        });

        $(document).on('show.bs.modal', '#subtypemodal', function(e) {
            var input = $('#subtypemodal').find('input[name="name"]');
            input.closest('.form-group').find('#name-error').remove();
        });

        $(document).on('input', 'input[name="name"]', function() {
            $('#subtypemodal').find('button[type="submit"]').prop("disabled", false);
            var input = $('#subtypemodal').find('input[name="name"]');
            input.closest('.form-group').find('#name-error').remove();
            var id = $('#subtypemodal').find('input[name="id"]').val();
            var type = 'sub_type';
            var value = $(this).val();
            var main_id = $('.selected-main-type').attr('data-id');
            if (value != "" && value != null) {
                $.ajax({
                    url: "<?php echo admin_url('contracts/unique_check') ?>",
                    method: "POST",
                    data: {
                        type: type,
                        value: value,
                        id: id,
                        main_id: main_id
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        $('#subtypemodal').find('button[type="submit"]').prop("disabled", true);
                        input.closest('.form-group').append('<p id="name-error" class="text-danger">' + result.message + '</p>');
                    }
                });
            }
        });

    });
</script>
</body>

</html>