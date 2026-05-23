<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="new_type(); return false;" class="btn btn-info pull-left display-block">New Agreement Type</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('name'),
                            _l('contract_counts'),
                            _l('sub_category_counts'),
                            _l('options')
                        ), 'contract-types'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/contracts/contract_type'); ?>

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
                        <p class="text-muted">Select from main type and target main type to copy all sub types and its draft data.</p>
                    </div>
                    <input type="hidden" name="type" value="main_type_copy" />
                    <div class="col-md-6 main-group-section">
                        <label for="from_main_type" class="control-label">From Main Type</label>
                        <select name="from_main_type" id="from_main_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
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
                    <div class="col-md-6 main-group-section">
                        <label for="to_main_type" class="control-label">To Main Type</label>
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
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-contract-types', window.location.href);
        appValidateForm($('#copyForm'), {
            from_main_type: 'required',
            to_main_type: 'required',
        });
        $(document).on('click', '._copy', function() {
            var main_type_id = $(this).closest('tr').find('td:first').find('a').attr('data-id');
            $('#from_main_type').val(main_type_id);
            $('#from_main_type').trigger('change').selectpicker('refresh');
            $('#copy_modal').modal("show");
        });

        $('#to_main_type option').hide();
        $('#to_main_type').selectpicker('refresh');
        $(document).on('change', '#from_main_type', function() {
            var fromValue = $(this).val();
            $('#to_main_type').val("");
            $('#to_main_type option').show();
            if (fromValue) {
                $('#to_main_type option[value="' + fromValue + '"]').hide();
            }
            $('#to_main_type').selectpicker('refresh');
        });

        $(document).on('show.bs.modal', '#type', function(e) {
            var input = $('#type').find('input[name="name"]');
            input.closest('.form-group').find('#name-error').remove();
        });

        $(document).on('input', 'input[name="name"]', function() {
            $('#type').find('button[type="submit"]').prop("disabled", false);
            var input = $('#type').find('input[name="name"]');
            input.closest('.form-group').find('#name-error').remove();
            var id = $('#type').find('input[name="id"]').val();
            var type = 'main_type';
            var value = $(this).val();
            if (value != "" && value != null) {
                $.ajax({
                    url: "<?php echo admin_url('contracts/unique_check') ?>",
                    method: "POST",
                    data: {
                        type: type,
                        value: value,
                        id: id,
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        $('#type').find('button[type="submit"]').prop("disabled", true);
                        input.closest('.form-group').append('<p id="name-error" class="text-danger">' + result.message + '</p>');
                    }
                });
            }
        });
    });
</script>
</body>

</html>