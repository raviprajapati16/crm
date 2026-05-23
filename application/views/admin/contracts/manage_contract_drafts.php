<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('contracts/sub_types/' . $main_type->id); ?>" class="btn btn-info mright5  pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="_buttons">
                            <a href="<?= admin_url('contracts/draft/' . $main_type->id . '/' . $sub_type->id) ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_contract_draft'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h5>Main Type : <span class="label label-primary"> <?php echo (isset($main_type)) ? $main_type->name : '' ?> </span>
                            &nbsp;&nbsp;Sub Type : <span class="label label-primary"> <?php echo (isset($sub_type)) ? $sub_type->name : '' ?> </span>
                        </h5>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('name'),
                            _l('contract_counts'),
                            _l('options')
                        ), 'contract-drafts'); ?>
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
                        <p class="text-muted">Select target main type and sub type to copy selected draft.</p>
                    </div>
                    <input type="hidden" name="type" value="draft_copy" />
                    <div class="col-md-12">
                        <h5>Selected Main Type : </h5>
                        <h5><span class="label label-primary"> <?php echo (isset($main_type)) ? $main_type->name : '' ?> </span></h5>
                        <input type="hidden" id="from_main_type" name="from_main_type" value="<?php echo (isset($main_type)) ? $main_type->id : '' ?>" />
                    </div>
                    <div class="col-md-12">
                        <h5>Selected Sub Type : </h5>
                        <h5><span class="label label-primary"> <?php echo (isset($sub_type)) ? $sub_type->name : '' ?> </span></h5>
                        <input type="hidden" id="from_sub_type" name="from_sub_type" value="<?php echo (isset($sub_type)) ? $sub_type->id : '' ?>" />
                    </div>
                    <div class="col-md-12">
                        <h5>Selected Draft : </h5>
                        <h5><span class="label label-primary draft_title"></span></h5>
                        <input type="hidden" id="selected_draft" name="selected_draft" value="" />
                    </div>
                    <div class="col-md-6 mtop15">
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
                    <div class="col-md-6 mtop15">
                        <label for="to_sub_type" class="control-label">Select Target Sub Type</label>
                        <select name="to_sub_type" id="to_sub_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                            <option value=""></option>
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
        initDataTable('.table-contract-drafts', window.location.href);
        appValidateForm($('#copyForm'), {
            to_main_type: 'required',
            to_sub_type: 'required',
        });

        $(document).on('click', '._copy', function() {
            $('#to_main_type').val("").selectpicker("refresh");
            $('#to_sub_type').val("").selectpicker("refresh");
            var draft_id = $(this).closest('tr').find('td:first').find('a').attr('data-id');
            var draft_title = $(this).closest('tr').find('td:first').find('a').text();
            $('#selected_draft').val(draft_id);
            $('.draft_title').text(draft_title);
            console.log(draft_title)
            $('#copy_modal').modal("show");
        });

        $(document).on('change', '#to_main_type', function() {
            $('#to_sub_type').empty();
            $('#to_sub_type').val("").selectpicker("refresh");
            var main_id = $(this).val();
            if (main_id != "" && main_id != null) {
                $.ajax({
                    url: "<?php echo admin_url('contracts/get_sub_types_list') ?>",
                    method: "POST",
                    data: {
                        main_id: main_id,
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        $('#to_sub_type').append('<option value="" selected></option>');
                        $(result.sub_types).each(function(index, item) {
                            $('#to_sub_type').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        $('#to_sub_type').selectpicker('refresh');
                    }
                });
            }
        });
    });
</script>
</body>

</html>