<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                            <?php if (isset($question_data)) { ?>
                                <a href="<?php echo admin_url('leads_questions/question'); ?>" class="btn btn-success pull-right"><?php echo _l('lead_new_questions'); ?></a>
                                <div class="clearfix"></div>
                            <?php } ?>
                        </h4>
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <hr class="hr-panel-heading" />
                        <div class="select-placeholder form-group">
                            <label for="main_group_id">Question belong to Main Product Group</label>
                            <select name="main_group_id" id="main_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php
                                foreach ($main_group as $key => $item) {
                                ?>
                                    <option value="<?php echo $item['id']; ?>" <?php if (isset($question_data) && $question_data->main_group_id == $item['id']) {
                                                                                    echo 'selected';
                                                                                } ?>><?php echo $item['name']; ?></option>
                                <?php

                                }
                                ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div class="select-placeholder form-group">
                            <label for="sub_group_id">Sub Product Group</label>
                            <select name="sub_group_id" id="sub_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php
                                foreach ($sub_group as $key => $item) {
                                ?>
                                    <option data-main-group-id="<?php echo $item['group_id'] ?>" value="<?php echo $item['id'] ?>" <?php if (isset($question_data) && $question_data->sub_group_id == $item['id']) {
                                                                                                                                        echo 'selected';
                                                                                                                                    } ?>><?php echo $item['name']; ?></option>
                                <?php

                                }
                                ?>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <?php $value = (isset($question_data) ? $question_data->question : ''); ?>
                        <?php echo render_input('question', 'Question', $value); ?>
                        <div class="select-placeholder form-group">
                            <label for="type"><?php echo _l('custom_field_add_edit_type'); ?></label>
                            <select name="type" id="type" class="selectpicker" <?php if (isset($question_data)) {echo ' disabled'; } ?> data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true">
                                <option value=""></option>
                                <option value="input" <?php if (isset($question_data) && $question_data->type == 'input') {
                                                            echo 'selected';
                                                        } ?>>Input</option>
                                <option value="number" <?php if (isset($question_data) && $question_data->type == 'number') {
                                                            echo 'selected';
                                                        } ?>>Number</option>
                                <option value="textarea" <?php if (isset($question_data) && $question_data->type == 'textarea') {
                                                                echo 'selected';
                                                            } ?>>Textarea</option>
                                <option value="select" <?php if (isset($question_data) && $question_data->type == 'select') {
                                                            echo 'selected';
                                                        } ?>>Select</option>
                                <option value="multiselect" <?php if (isset($question_data) && $question_data->type == 'multiselect') {
                                                                echo 'selected';
                                                            } ?>>Multi Select</option>
                                <option value="checkbox" <?php if (isset($question_data) && $question_data->type == 'checkbox') {
                                                                echo 'selected';
                                                            } ?>>Checkbox</option>
                                <option value="date_picker" <?php if (isset($question_data) && $question_data->type == 'date_picker') {
                                                                echo 'selected';
                                                            } ?>>Date Picker</option>
                                <option value="date_picker_time" <?php if (isset($question_data) && $question_data->type == 'date_picker_time') {
                                                                        echo 'selected';
                                                                    } ?>>Datetime Picker</option>
                                <option value="colorpicker" <?php if (isset($question_data) && $question_data->type == 'colorpicker') {
                                                                echo 'selected';
                                                            } ?>>Color Picker</option>
                                <option value="link" <?php if (isset($question_data) && $question_data->type == 'link') {
                                                            echo 'selected';
                                                        } ?><?php if (isset($question_data) && $question_data->fieldto == 'items') {
                                                                echo 'disabled';
                                                            } ?>>Hyperlink</option>
                            </select>
                        </div>
                        <div class="clearfix"></div>
                        <div id="options_wrapper" class="<?php if (!isset($question_data) || isset($question_data) && $question_data->type != 'select' && $question_data->type != 'checkbox' && $question_data->type != 'multiselect') {
                                                                echo 'hide';
                                                            } ?>">
                            <span class="pull-left fa fa-question-circle" data-toggle="tooltip" title="<?php echo _l('custom_field_add_edit_options_tooltip'); ?>"></span>
                            <?php $value = (isset($question_data) ? $question_data->type_options : ''); ?>
                            <?php echo render_textarea('type_options', 'Options', $value, array('rows' => 3)); ?>
                        </div>
                        <?php $value = (isset($question_data) ? $question_data->order_no : ''); ?>
                        <?php echo render_input('order_no', 'Order', $value, 'number'); ?>


                        <div class="checkbox checkbox-primary" id="required_wrap">
                            <input type="checkbox" name="is_required" id="is_required" <?php if (isset($question_data) && $question_data->is_required == 1) {
                                                                                            echo 'checked';
                                                                                        } ?>>
                            <label for="required"><?php echo _l('custom_field_required'); ?></label>
                        </div>

                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(document).ready(function() {
        $('#sub_group_id').find('option').hide();
        $('#main_group_id').on('change', function() {
            var selectedMainGroup = $(this).val();
            var subDropdown = $('#sub_group_id');
            subDropdown.find('option').hide();
            subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').show();
            subDropdown.val('');
            subDropdown.selectpicker('refresh');
        });
        appValidateForm($('form'), {
            main_group_id: 'required',
            sub_group_id: 'required',
            type: 'required',
            type_options: {
                required: {
                    depends: function(element) {
                        var type = $('#type').val();
                        return type == 'select' || type == 'checkbox' || type == 'multiselect';
                    }
                }
            }
        });

        $('select[name="type"]').on('change', function() {
            var type = $(this).val();
            var options_wrapper = $('#options_wrapper');
            var display_inline = $('.display-inline-checkbox')
            if (type == 'select' || type == 'checkbox' || type == 'multiselect') {
                options_wrapper.removeClass('hide');
                if (type == 'checkbox') {
                    display_inline.removeClass('hide');
                } else {
                    display_inline.addClass('hide');
                    display_inline.find('input').prop('checked', false);
                }
            } else {
                options_wrapper.addClass('hide');
                display_inline.addClass('hide');
                display_inline.find('input').prop('checked', false);
            }
        });

    });
</script>
</body>

</html>