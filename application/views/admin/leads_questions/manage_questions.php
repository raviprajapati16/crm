<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('leads_questionnaire_group'); ?>" class="btn btn-info mright5  pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="_buttons">
                            <a href="javascript:;" onclick="openQuestionModal()" class="btn btn-info pull-left display-block"><?php echo _l('lead_new_questions'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h5>Main Group : <span class="label label-primary"> <?php echo (isset($main_group_data)) ? $main_group_data['name'] : '' ?> </span>
                            <?php if (isset($sub_group_data)) { ?>
                                &nbsp;&nbsp; Sub Group : <span class="label label-info"> <?php echo $sub_group_data['name']; ?> </span>
                            <?php } ?>
                        </h5>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="#" data-toggle="modal" data-table=".table-leads-questions" data-target="#group_copy_modal" class="hide copy-btn table-btn">Copy</a>
                                <a href="#" data-table=".table-leads-questions" class="hide multiple-delete-btn table-btn">Delete</a>
                                <?php
                                render_datatable(
                                    array(
                                        '<div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all"><label></label></div>',
                                        _l('lead_question_order'),
                                        _l('lead_question_text'),
                                        _l('lead_question_type'),
                                        _l('lead_question_add_edit_active'),
                                    ),
                                    'leads-questions',
                                    array('customizable-table'),
                                    array(
                                        'id' => 'table-leads-questions',
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="question_modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <?php echo form_open(admin_url('leads_questionnaire_group/question'), array("name" => "questionForm", "id" => "questionForm")); ?>
            <input type="hidden" id="question_id" name="id" value="" form="questionForm" />
            <input type="hidden" id="main_group_id" name="main_group_id" value="<?= $main_group_data['id']; ?>" form="questionForm" />
            <input type="hidden" id="sub_group_id" name="sub_group_id" value="<?php echo (isset($sub_group_data)) ? $sub_group_data['id'] : '' ?>" form="questionForm" />
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="additional"></div>
                            <?php echo render_input('question', 'Question', "", "text", array("form" => "questionForm")); ?>
                            <div class="select-placeholder form-group">
                                <label for="type"><?php echo _l('custom_field_add_edit_type'); ?></label>
                                <select name="type" id="type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" form="questionForm">
                                    <option value=""></option>
                                    <option value="input">Input</option>
                                    <option value="number">Number</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="radio">Radio</option>
                                    <option value="multiselect">Multi Select</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date_picker">Date Picker</option>
                                    <option value="date_picker_time">Datetime Picker</option>
                                    <option value="colorpicker">Color Picker</option>
                                    <option value="link">Hyperlink</option>
                                    <option value="fileupload">File Upload</option>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div id="options_wrapper" class="hide">
                                <span class="pull-left fa fa-question-circle" data-toggle="tooltip" title="" data-original-title="<?php echo _l('custom_field_add_edit_options_tooltip'); ?>"></span>
                                <?php echo render_textarea('type_options', 'Options', "", array('rows' => 3, "form" => "questionForm")); ?>
                            </div>
                            <div class="checkbox checkbox-primary" id="required_wrap">
                                <input type="checkbox" name="is_required" id="is_required" form="questionForm">
                                <label for="is_required"><?php echo _l('custom_field_required'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info" form="questionForm"><?php echo _l('submit'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
    <div class="modal fade" id="group_copy_modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <?php echo form_open(admin_url('leads_questionnaire_group/copy'), array("name" => "copyForm", "id" => "copyForm")); ?>
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
                        <input type="hidden" name="from_main_group_id" value="<?= $main_group_data['id']; ?>" form="copyForm" />
                        <input type="hidden" name="from_sub_group_id" value="<?php echo (isset($sub_group_data)) ? $sub_group_data['id'] : '' ?>" form="copyForm" />
                        <input type="hidden" name="type" value="questions_copy" form="copyForm" />
                        <div class="col-md-6 main-group-section">
                            <label for="copy_main_group_id" class="control-label">Main Group</label>
                            <select name="main_group_id" id="copy_main_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" form="copyForm" required>
                                <option value=""></option>
                                <?php
                                foreach ($main_group_data_arr as $key => $item) {
                                ?>
                                    <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 sub-group-section">
                            <label for="copy_sub_group_id" class="control-label">Sub Group</label>
                            <select name="sub_group_id" id="copy_sub_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" form="copyForm" required>
                                <option value=""></option>
                                <?php
                                foreach ($sub_group_data_arr as $key => $item) {
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
                    <button type="submit" class="btn btn-info" form="copyForm"><?php echo _l('Copy'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<div class="ui-widget">
    <label for="tags">Tags: </label>
    <input id="tags">
</div>
<?php init_tail(); ?>
<!-- RowReorder extension -->
<script>
    $(document).ready(function() {
        $(window).off('beforeunload');
        var table = initDataTable('.table-leads-questions', window.location.href, '', [0], '', [1, 'asc']);

        $("tbody").sortable({
            start: function(event, ui) {
                ui.item.addClass("bg-danger");
            },
            stop: function(event, ui) {
                ui.item.removeClass("bg-danger");
            },
            update: function(event, ui) {
                var order = $(this).children("tr").map(function() {
                    return $(this).find("a").attr('data-id');
                }).get();
                $.ajax({
                    url: "<?php echo admin_url('leads_questionnaire_group/question_reorder'); ?>",
                    method: "POST",
                    data: {
                        order: order
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        alert_float('success', result.message);
                        table.draw();
                    }
                });
            }
        });

        $('#sub_group_id').find('option').hide();
        $('#main_group_id').on('change', function() {
            var selectedMainGroup = $(this).val();
            var subDropdown = $('#sub_group_id');
            subDropdown.find('option').hide();
            subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').show();
            subDropdown.val('');
            subDropdown.selectpicker('refresh');
        });

        appValidateForm($('#questionForm'), {
            question: 'required',
            type: 'required',
            order_no: 'required',
            type_options: {
                required: {
                    depends: function(element) {
                        var type = $('#type').val();
                        return type == 'select' || type == 'checkbox' || type == 'multiselect' || type == 'radio';
                    }
                }

            },
        });

        $('select[name="type"]').on('change', function() {
            var type = $(this).val();
            var options_wrapper = $('#options_wrapper');
            var display_inline = $('.display-inline-checkbox')
            if (type == 'select' || type == 'checkbox' || type == 'multiselect' || type == 'radio') {
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

        $(document).on('click', '#mass_select_all', function() {
            if (this.checked) {
                $('.single-checkbox').prop('checked', true);
            } else {
                $('.single-checkbox').prop('checked', false);
            }
        });


        $('#copyForm').appFormValidator({
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

        $(document).on('click', '.multiple-delete-btn', function() {
            var idArr = [];
            $('.single-checkbox').each(function() {
                if (this.checked) {
                    idArr.push(this.value);
                }
            });
            if (idArr.length > 0) {
                if (confirm("Are you sure you want to perform this action?")) {
                    $.ajax({
                        url: "<?php echo admin_url('leads_questionnaire_group/delete_bulk'); ?>",
                        method: "POST",
                        data: {
                            ids: idArr
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            table.draw();
                        }
                    });
                }
            } else {
                alert_float('danger', "Please select question(s).");
            }
        });

        $(document).on('click', '.copy-btn', function() {
            $('#copy_main_group_id').val("").selectpicker('refresh');
            $('#copy_sub_group_id').val("").selectpicker('refresh');
            $('#copy_sub_group_id').closest('.sub-group-section').show();
            $('#group_copy_modal').modal('show');
        });

        $('#copy_sub_group_id').find('option').hide();
        $('#copy_main_group_id').on('change', function() {
            var from_main_group_id = $('#group_copy_modal').find('input[name="from_main_group_id"]').val();
            var from_sub_group_id = $('#group_copy_modal').find('input[name="from_sub_group_id"]').val();
            $(this).closest('.main-group-section').find('.text-danger').remove();
            var selectedMainGroup = $(this).val();
            var subDropdown = $('#copy_sub_group_id');
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

        $('#copy_sub_group_id').on('change', function() {
            $(this).closest('.sub-group-section').find('.text-danger').remove();
        });

        $("#question").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "<?php echo admin_url('leads_questionnaire_group/get_suggestions'); ?>",
                    type: 'POST',
                    data: {
                        text: request.term
                    },
                    success: function(data) {
                        console.log(data)
                        response(data);
                    }
                });
            },
            minLength: 4,
            select: function(event, ui) {
                console.log("Selected:", ui.item.value);
            }
        });
    });

    function openQuestionModal(id = "") {
        var parent = $('#question_modal');
        if (id == "") {
            parent.find('#question_id').val("");
            parent.find('#question').val("");
            parent.find('#type_options').val("");
            parent.find('#order_no').val("");
            parent.find('#is_required').prop('checked', false);
            parent.find('#type').val("");
            parent.find('#type').trigger('change');
            parent.find('#type').selectpicker('refresh');
            parent.find('.modal-title').html("Edit Question");
            parent.modal('show');
        } else {
            $.ajax({
                url: "<?php echo admin_url('leads_questionnaire_group/get_question'); ?>",
                method: "POST",
                data: {
                    id: id
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    parent.find('#question_id').val(result.data.id);
                    parent.find('#question').val(result.data.question);
                    parent.find('#type_options').val(result.data.type_options);
                    parent.find('#order_no').val(result.data.order_no);
                    if (result.data.is_required == "0") {
                        parent.find('#is_required').prop('checked', false);
                    } else {
                        parent.find('#is_required').prop('checked', true);
                    }
                    parent.find('#type').val(result.data.type);
                    parent.find('#type').trigger('change');
                    parent.find('#type').selectpicker('refresh');
                    parent.find('.modal-title').html("Edit Question");
                    parent.modal('show');
                }
            });
        }

    }
</script>
<style>
    .has-row-options {
        cursor: pointer;
    }

    .todo-dragger {
        margin-left: -5px;
        margin-top: -8px;
    }

    .ui-autocomplete {
        z-index: 11111;
    }
</style>
</body>

</html>