<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading"><?php echo _l('lead_questionnaire_group'); ?>
                        <a href="<?= admin_url('leads_questionnaire_group/lead_inquiry_form_images') ?>" class="btn btn-primary pull-right"><?php echo _l('lead_inquiry_form_images'); ?></a>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <?php render_datatable(
                            array(
                                _l('id'),
                                _l('lead_question_main_group_lbl'),
                                _l('lead_question_sub_group_lbl'),
                                _l('total_questions'),
                                _l("action"),
                            ),
                            'leads-questionnaire-group'
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="group_copy_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('leads_questionnaire_group/copy')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _l('copy') ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="text-muted">Select main group and sub group to copy all questions.</p>
                    </div>
                    <input type="hidden" name="from_main_group_id" value="" />
                    <input type="hidden" name="from_sub_group_id" value="" />
                    <input type="hidden" name="type" value="group_copy" />
                    <div class="col-md-6 main-group-section">
                        <label for="main_group_id" class="control-label">Main Group</label>
                        <select name="main_group_id" id="main_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                            <option value=""></option>
                            <?php
                            foreach ($main_group_data as $key => $item) {
                            ?>
                                <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 sub-group-section">
                        <label for="sub_group_id" class="control-label">Sub Group</label>
                        <select name="sub_group_id" id="sub_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" required>
                            <option value=""></option>
                            <?php
                            foreach ($sub_group_data as $key => $item) {
                            ?>
                                <option data-main-group-id="<?php echo $item['group_id'] ?>" value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
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
        initDataTable('.table-leads-questionnaire-group', window.location.href, '', [0], '', [1, 'asc']);

        $('form').appFormValidator({
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                var inputType = $(element).attr('type')
                if (element.hasClass('selectpicker')) {
                    var selectpickerContainer = $(element).closest('.bootstrap-select');
                    error.insertAfter(selectpickerContainer);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        $(document).on('click', '.copy-group', function() {
            $('#main_group_id').val("").selectpicker('refresh');
            $('#sub_group_id').val("").selectpicker('refresh');
            $('#sub_group_id').closest('.sub-group-section').show();
            $('#group_copy_modal').find('input[name="from_main_group_id"]').val($(this).attr('data-maingroup'));
            $('#group_copy_modal').find('input[name="from_sub_group_id"]').val($(this).attr('data-subgroup'));
            $('#group_copy_modal').modal('show');
        });

        $('#sub_group_id').find('option').hide();
        $('#main_group_id').on('change', function() {
            var from_main_group_id = $('#group_copy_modal').find('input[name="from_main_group_id"]').val();
            var from_sub_group_id = $('#group_copy_modal').find('input[name="from_sub_group_id"]').val();
            $(this).closest('.main-group-section').find('.text-danger').remove();
            var selectedMainGroup = $(this).val();
            var subDropdown = $('#sub_group_id');
            subDropdown.find('option').hide();
            subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').show();
            if ($(this).val() == from_main_group_id) {
                subDropdown.find('option').each(function() {
                    var $option = $(this);
                    var mainGroupId = $option.data('main-group-id');
                    var optionValue = $option.val();
                    if (mainGroupId == from_main_group_id && optionValue == from_sub_group_id) {
                        $option.hide();
                    }
                });
            }
            subDropdown.val('');
            var subOptionCount = subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').length;
            if (subOptionCount == 0) {
                subDropdown.removeAttr('required');
                subDropdown.closest('.sub-group-section').hide();
            } else {
                subDropdown.attr('required', true);
                subDropdown.closest('.sub-group-section').show();
            }
            subDropdown.selectpicker('refresh');
        });

        $('#sub_group_id').on('change', function() {
            $(this).closest('.sub-group-section').find('.text-danger').remove();
        });
    });
</script>
<style>
    .panel-heading {
        font-size: 17px;
    }
</style>
</body>
</html>
