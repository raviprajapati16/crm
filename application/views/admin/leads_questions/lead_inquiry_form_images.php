<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="javascript:;" onclick="addImageModal('',this)" class="btn btn-info pull-left display-block">Add New Image</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />

                        <div class="panel_s">
                            <div class="panel-heading">Popup Images</div>
                            <input type="hidden" id="popup_images" value="1" />
                            <div class="panel-body">
                                <?php render_datatable(
                                    array(
                                        _l('id'),
                                        _l('title'),
                                        _l('images'),
                                        _l("action"),
                                    ),
                                    'leads-forms-popup-images'
                                ); ?>
                            </div>
                        </div>
                        <div class="panel_s">
                            <div class="panel-heading">
                                <div class="panel-title">
                                    Background Slider Images
                                    <div class="mleft5">
                                        <div class="onoffswitch onoffswitch-main pull-right" data-toggle="tooltip" data-title="Background Slider Slider ON / OFF">
                                            <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox onoffswitch onoffswitch-bg-image" id="bg_image_main_switch" <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "checked" : "" ?>>
                                            <label class="onoffswitch-label" for="bg_image_main_switch"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="slider_images" value="1" />
                            <div class="panel-body">
                                <?php render_datatable(
                                    array(
                                        _l('id'),
                                        _l('title'),
                                        _l('images'),
                                        _l("action"),
                                    ),
                                    'leads-forms-slider-images'
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="imageAddModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('leads_questionnaire_group/add_image'), array("name" => "imageAddForm", "id" => "imageAddForm")); ?>
        <input type="hidden" id="id" name="id" value="" form="imageAddForm" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" form="imageAddForm" required />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="select-placeholder form-group">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" form="imageAddForm" required>
                                <option value=""></option>
                                <option value="popup-image">Popup Image</option>
                                <option value="background-image-slider">Images Slider</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="image">Image <span class="text-danger">*</span></label>
                            <input type="file" name="image" id="image" form="imageAddForm" class="form-control" accept=".jpeg,.jpg,.png,.gif" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="imageAddForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div class="modal fade" id="imageFullModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Preview</h4>
            </div>
            <div class="modal-body">
                <img src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        app.options.tables_pagination_limit = 10;

        var table_popup = initDataTable('.table-leads-forms-popup-images', window.location.href, '', [], {
            'popup_images': '#popup_images'
        }, [1, 'asc']);

        var table_slider = initDataTable('.table-leads-forms-slider-images', window.location.href, '', [], {
            'slider_images': '#slider_images'
        }, [1, 'asc']);

        $('#imageAddForm').validate({
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                var fileInput = form.querySelector("input[type='file']");
                if (!validateFileInput(fileInput)) {
                    return false;
                }
                form.submit();
            }
        });

        $('select').on('change', function() {
            $(this).closest('.form-group').find('.error').remove();
        });

        $(document).on('change', '.formswitch', function() {
            var type = $(this).attr('data-type');
            var id = $(this).attr('data-id');
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            if (type == "popup_images" && status) {
                if (!confirm('In a popup image, only one image can be active at a time. activating a this image will deactivate the current popup active image. Are you sure you want to proceed with this action?')) {
                    $(this).prop('checked', false)
                    return false;
                }
            }
            $.ajax({
                url: "<?php echo admin_url('leads_questionnaire_group/image_status_change') ?>",
                method: "POST",
                data: {
                    id: id,
                    status: status,
                    type: type
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    if (type == "popup_images") {
                        table_popup.draw();
                    }
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).on('change', '.onoffswitch-bg-image', function() {
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads_questionnaire_group/background_slider_active_inactive') ?>",
                method: "POST",
                data: {
                    status: status,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).on('click', '.img-thumbnail', function() {
            $('#imageFullModal').find('.img-fluid').attr('src', $(this).attr('src'));
            $('#imageFullModal').modal('show');
        });

        $(document).on('change', '#image', function() {
            var fileInput = $(this)[0];
            var validation = validateFileInput(this);
            if (fileInput.files && fileInput.files[0] && validation) {
                var file = fileInput.files[0];
                var url = URL.createObjectURL(file);
                var modal = $('#imageAddModal');
                modal.find('.file-upload-preview').remove();
                modal.find('#image').after('<div class="file-upload-preview mtop5"><img width="200px" src="' + url + '" class="img-thumbnail"></div>');
            }
        });
    });

    function addImageModal(id = '', element) {
        var modal = $('#imageAddModal');
        modal.find('error').remove();
        modal.find('.file-upload-preview').remove();
        if (id == "") {
            modal.find('#id').val("");
            modal.find('#title').val("");
            modal.find('#type').val("").selectpicker('refresh');
            modal.find('#image').val("").attr("required", true);
            modal.find('.modal-title').text("Add New Image");
        } else {
            modal.find('#id').val(id);
            modal.find('#title').val($(element).closest('tr').find('.id-col').text());
            modal.find('#type').val($(element).closest('tr').find('.id-col').attr('data-type')).selectpicker('refresh');
            modal.find('#image').val("").removeAttr("required");
            var image = $(element).closest('tr').find('.img-thumbnail').attr('src');
            modal.find('#image').after('<div class="file-upload-preview mtop5"><img width="200px" src="' + image + '" class="img-thumbnail"></div>');
            modal.find('.modal-title').text("Edit Image");
        }
        modal.modal('show');
    }

    function validateFileInput(fileInput) {
        var maxSize = 5 * 1024 * 1024; // 5 MB
        var allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        var isValid = true;
        var errors = [];
        if (fileInput.files.length > 0) {
            var file = fileInput.files[0];
            var fileSize = file.size; // Size in bytes
            var fileName = file.name;
            var fileExtension = fileName.split('.').pop().toLowerCase();

            if (fileSize > maxSize) {
                isValid = false;
                errors.push("File size exceeds. Allowed maximum file size allowed up to " + (maxSize / 1024 / 1024) + " MB");
            }

            if (!allowedExtensions.includes(fileExtension)) {
                isValid = false;
                errors.push("Invalid file type. Allowed extensions are: " + allowedExtensions.join(", "));
            }
        }
        $(fileInput).siblings('.error').remove();
        if (!isValid) {
            $(fileInput).siblings('.file-preview-section').remove();
            errors.forEach(function(error) {
                $("<div class='validation-error text-danger error'>").text(error).insertAfter($(fileInput));
            });
        }
        return isValid;
    }
</script>
<style>
    .panel-heading {
        font-size: 17px;
    }

    .img-fluid {
        width: 100%;
    }

    .error {
        color: red;
    }

    .buttons-collection {
        display: none !important;
    }

    .onoffswitch-main .onoffswitch-label:before {
        height: 20px;
    }

    .onoffswitch-main {
        top: -20px;
    }
</style>
</body>

</html>