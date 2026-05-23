<?php

use Mpdf\Tag\Tr;

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .panel-title {
        padding-bottom: 5px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Visitor Types
                            <button type="button" onclick="openVisitorTypeModal('',this)" class="btn btn-primary pull-right">Create</button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <input type="hidden" id="pvf-visitor-types" value="1" />
                        <?php render_datatable(
                            array(
                                _l('id'),
                                _l('title'),
                                _l("action"),
                            ),
                            'pvf-visitor-types'
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Relation Types
                            <button type="button" onclick="openRelationTypeModal('',this)" class="btn btn-primary pull-right">Create</button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <input type="hidden" id="pvf-relation-types" value="1" />
                        <?php render_datatable(
                            array(
                                _l('id'),
                                _l('title'),
                                _l("action"),
                            ),
                            'pvf-relation-types'
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">Terms and Conditions</div>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open_multipart(admin_url('leads_plant_visit_forms/terms_and_conditions_update'), array('id' => "ovf_terms_condition")); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <textarea name="pvf_terms_and_conditions" id="pvf_terms_and_conditions_editor" class="texteditor"><?= get_option('plant_visit_form_terms_and_conditions'); ?></textarea>
                            </div>
                            <div class="col-md-12 mtop10">
                                <button type="submit" class="btn btn-info pull-right">Save</button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="visitorTypeModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('leads_plant_visit_forms/save_visitor_type_data'), array("name" => "visitorTypeForm", "id" => "visitorTypeForm")); ?>
        <input type="hidden" id="id" name="id" value="" form="visitorTypeForm" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="id" value="" form="visitorTypeForm" />
                    <?= all_type_input_render([
                        "label" => "Title",
                        "id" => "title",
                        "name" => "title",
                        "type" => "text",
                        "is_required" => true,
                        "form" => "visitorTypeForm"
                    ], 'col-md-12', true);
                    ?>
                    <?= all_type_input_render([
                        "label" => "Allowed Members",
                        "id" => "allowed_members",
                        "name" => "allowed_members",
                        "type" => "text",
                        "is_required" => true,
                        "form" => "visitorTypeForm"
                    ], 'col-md-12', true);
                    ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="control-label">Visit Charge Type <span class="text-danger">* </span> </div>
                            <div class="radio-section">
                                <label class="radio-inline" for="radio_charge_type_1">
                                    <input form="visitorTypeForm" required="true" type="radio" id="radio_charge_type_1" value="per person" name="charge_type" checked>Per Person
                                </label>
                                <label class="radio-inline" for="radio_charge_type_0">
                                    <input form="visitorTypeForm" required="true" type="radio" id="radio_charge_type_0" value="fixed" name="charge_type">Fixed
                                </label>
                            </div>
                        </div>
                    </div>
                    <?= all_type_input_render([
                        "label" => "Visit Charge (Amount)",
                        "id" => "amount",
                        "name" => "amount",
                        "type" => "text",
                        "is_required" => true,
                        "form" => "visitorTypeForm"
                    ], 'col-md-12', true);
                    ?>
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="control-label">Free Visit Allowed <span class="text-danger">* </span> </div>
                            <div class="radio-section">
                                <label class="radio-inline" for="free_visit_allowed_1">
                                    <input form="visitorTypeForm" required="true" type="radio" id="free_visit_allowed_1" value="1" name="free_visit_allowed" checked>Yes
                                </label>
                                <label class="radio-inline" for="free_visit_allowed_0">
                                    <input form="visitorTypeForm" required="true" type="radio" id="free_visit_allowed_0" value="0" name="free_visit_allowed">No
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="select-placeholder form-group">
                            <label for="free_visit_day">Select Free Visit Day <span class="text-danger">*</span></label>
                            <select name="free_visit_day" id="free_visit_day" class="selectpicker" data-width="100%" data-none-selected-text="Select Day" data-hide-disabled="true" form="visitorTypeForm">
                                <option value=""></option>
                                <?php foreach (getDays() as $key => $item) { ?>
                                    <option value="<?= $item ?>"><?= $item ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="visitorTypeForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div class="modal fade" id="relationTypeModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('leads_plant_visit_forms/save_relation_type_data'), array("name" => "relationTypeForm", "id" => "relationTypeForm")); ?>
        <input type="hidden" id="id" name="id" value="" form="relationTypeForm" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="id" value="" form="relationTypeForm" />
                    <?= all_type_input_render([
                        "label" => "Title",
                        "id" => "title",
                        "name" => "title",
                        "type" => "text",
                        "is_required" => true,
                        "form" => "relationTypeForm"
                    ], 'col-md-12', true);
                    ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="relationTypeForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        tinyMceEditor('.texteditor', 900);

        app.options.tables_pagination_limit = 10;

        var table_visitor_types = initDataTable('.table-pvf-visitor-types', window.location.href, '', [], {
            'pvf-visitor-types': '#pvf-visitor-types'
        }, [1, 'asc']);

        var table_relation_types = initDataTable('.table-pvf-relation-types', window.location.href, '', [], {
            'pvf-relation-types': '#pvf-relation-types'
        }, [1, 'asc']);

        $('#visitorTypeForm').validate({
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        $('#relationTypeForm').validate({
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                form.submit();
            }
        });

        $('select').on('change', function() {
            $(this).closest('.form-group').find('p.text-danger').remove();
            $(this).closest('.form-group').removeClass('has-error');
        });

        $(document).on('change', '.formswitch-visior-type', function() {
            var id = $(this).attr('data-id');
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/visitor_type_active_inactive_status_change') ?>",
                method: "POST",
                data: {
                    id: id,
                    status: status,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    table_visitor_types.draw();
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).on('change', '.formswitch-relation-type', function() {
            var id = $(this).attr('data-id');
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/relation_type_active_inactive_status_change') ?>",
                method: "POST",
                data: {
                    id: id,
                    status: status,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    table_visitor_types.draw();
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).on('input', '#fieldamount', function() {
            let value = $(this).val();
            if (!/^\d*\.?\d{0,2}$/.test(value)) {
                $(this).val(value.slice(0, -1));
            }
            if (parseFloat(value) === 0) {
                $(this).val('');
            }
        });

        $(document).on('input', '#fieldallowed_members', function() {
            let value = $(this).val();
            if (!/^\d*$/.test(value) || parseInt(value) > 100) {
                $(this).val(value.slice(0, -1));
            }
            if (parseInt(value) === 0) {
                $(this).val('');
            }
        });

        $(document).on('change', 'input[type="radio"][name="charge_type"]', function() {
            var selected = $('input[type="radio"][name="charge_type"]:checked').val();
            if (selected == "per person") {
                $('#fieldamount').closest('.form-group').find(".control-label").html("Visit Charge (Amount Per Person ) <span class='text-danger'>* </span>");
            } else {
                $('#fieldamount').closest('.form-group').find(".control-label").html("Visit Charge (Fixed Amount) <span class='text-danger'>* </span>");
            }
        });

        $(document).on('change', 'input[type="radio"][name="free_visit_allowed"]', function() {
            var selected = $('input[type="radio"][name="free_visit_allowed"]:checked').val();
            if (selected == "0") {
                $('#free_visit_day').closest('[class^="col-md-"]').addClass('hide');
                $('#free_visit_day').prop('required', false);
            } else {
                $('#free_visit_day').closest('[class^="col-md-"]').removeClass('hide');
                $('#free_visit_day').prop('required', true);
            }
        });
    });

    function openVisitorTypeModal(id = '', element) {
        var modal = $('#visitorTypeModal');
        modal.find('error').remove();
        modal.find('.file-upload-preview').remove();
        if (id == "") {
            $('input[type="text"]').val("");
            $('input[type="hidden"][name="id"]').val("");
            $('input[type="radio"][name="free_visit_allowed"][value="0"]').prop("checked", true).trigger("change");
            $('input[type="radio"][name="charge_type"][value="per person"]').prop("checked", true).trigger("change");
            $('select[name="free_visit_day"]').val("").selectpicker("refresh");
            modal.find('.modal-title').text("Add New Visitor Type");
            modal.modal('show');
        } else {
            $('input[type="text"]').val("");
            $('input[type="hidden"][name="id"]').val("");
            $('input[type="radio"][name="free_visit_allowed"][value="0"]').prop("checked", true).trigger("change");
            $('input[type="radio"][name="charge_type"][value="per person"]').prop("checked", true).trigger("change");
            $('select[name="free_visit_day"]').val("").selectpicker("refresh");

            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/get_visitor_type') ?>",
                method: "POST",
                data: {
                    id: id,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('input[name="id"]').val(result.data.id);
                    $('input[name="title"]').val(result.data.title);
                    $('input[name="allowed_members"]').val(result.data.allowed_members);
                    $('input[name="amount"]').val(result.data.amount);
                    $('input[type="radio"][name="free_visit_allowed"][value="' + result.data.free_visit_allowed + '"]').prop("checked", true).trigger("change");
                    $('input[type="radio"][name="charge_type"][value="' + result.data.charge_type + '"]').prop("checked", true).trigger("change");
                    $('select[name="free_visit_day"]').val(result.data.free_visit_day).selectpicker("refresh");
                    modal.find('.modal-title').text("Update Visitor Type");
                    modal.modal('show');
                } else {
                    alert_float('danger', result.message);
                }
            });
        }

    }

    function openRelationTypeModal(id = '', element) {
        var modal = $('#relationTypeModal');
        modal.find('error').remove();
        modal.find('.file-upload-preview').remove();
        if (id == "") {
            $('input[type="text"]').val("");
            $('input[type="hidden"][name="id"]').val("");
            modal.find('.modal-title').text("Add New Relation Type");
            modal.modal('show');
        } else {
            $('input[type="text"]').val("");
            $('input[type="hidden"][name="id"]').val("");

            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/get_relation_type') ?>",
                method: "POST",
                data: {
                    id: id,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('input[name="id"]').val(result.data.id);
                    $('input[name="title"]').val(result.data.title);
                    modal.find('.modal-title').text("Update Relation Type");
                    modal.modal('show');
                } else {
                    alert_float('danger', result.message);
                }
            });
        }

    }

    function tinyMceEditor(selector, height) {
        tinymce.init({
            selector: selector,
            height: height,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table contextmenu paste code help wordcount'
            ],
            mobile: {
                theme: 'mobile'
            },
            toolbar: 'insert | undo redo |  formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_css: [
                '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
                '//www.tiny.cloud/css/codepen.min.css'
            ],
        });
    }
</script>
</body>

</html>