<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content email-templates">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <?= ucfirst($setting->rel_type) ?> <?= _l('pdf_settings'); ?>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open_multipart(admin_url('pdfsettings/' . $setting->id), array('id' => 'pdf-form')); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?= all_type_input_render([
                                    "label" => "<b>Header</b>",
                                    "id" => "header",
                                    "name" => "header",
                                    "type" => "textarea",
                                    "rows" => 7,
                                    "is_required" => false,
                                    "selected_value" => (isset($setting->header)) ? $setting->header : '',
                                    "className" => "text-editor",
                                    "form" => 'pdf-form'
                                ], 'col-md-12', false);
                                ?>

                                <div class="col-md-12">
                                    <label><b>Header repeat on all page ?</b></label>
                                    <div class="form-group">
                                        <div class="radio-section">
                                            <label class="radio-inline" for="header_repeat_1">
                                                <input type="radio" id="header_repeat_1" value="1" name="header_repeat" form="pdf-form" <?= ($setting->header_repeat == "1") ? 'checked' : '' ?>> Yes
                                            </label>
                                            <label class="radio-inline" for="header_repeat_0">
                                                <input type="radio" id="header_repeat_0" value="0" name="header_repeat" form="pdf-form" <?= ($setting->header_repeat == "0") ? 'checked' : '' ?>> No
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <?= all_type_input_render([
                                    "label" => "<b>Footer Text (Right Side)</b>",
                                    "id" => "footer",
                                    "name" => "footer",
                                    "type" => "text",
                                    "is_required" => false,
                                    "selected_value" => (isset($setting->footer)) ? $setting->footer : '',
                                    "form" => 'pdf-form'
                                ], 'col-md-12', false);
                                ?>

                                <div class="col-md-12">
                                    <label><b>Watermark Type</b> <span class="text-danger">*</span></label>
                                    <div class="form-group">
                                        <div class="radio-section">
                                            <label class="radio-inline" for="watermark_0">
                                                <input type="radio" id="watermark_0" value="no" name="watermark_type" form="pdf-form" <?= ($setting->watermark_type == "no") ? 'checked' : '' ?>> No Watermark
                                            </label>
                                            <label class="radio-inline" for="watermark_1">
                                                <input type="radio" id="watermark_1" value="image" name="watermark_type" form="pdf-form" <?= ($setting->watermark_type == "image") ? 'checked' : '' ?>> Image
                                            </label>
                                            <label class="radio-inline" for="watermark_2">
                                                <input type="radio" id="watermark_2" value="text" name="watermark_type" form="pdf-form" <?= ($setting->watermark_type == "text") ? 'checked' : '' ?>> Text
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?= all_type_input_render([
                                    "label" => "<b>Watermark Text</b>",
                                    "id" => "watermark_text",
                                    "name" => "watermark",
                                    "type" => "text",
                                    "is_required" => true,
                                    "selected_value" => (isset($setting->watermark)) ? $setting->watermark : '',
                                    "form" => 'pdf-form'
                                ], 'col-md-12 text-section', true);
                                ?>
                                <?php
                                $watermark_url = "";
                                if (isset($setting) && ($setting->watermark_type == "image")  && !empty($setting->watermark) && file_exists(protected_file_url_by_path('uploads/pdf_settings/' . $setting->id . '/' . $setting->watermark))) {
                                    $watermark_url = site_url('download/preview_image?path=' . protected_file_url_by_path('uploads/pdf_settings/' . $setting->id . '/' . $setting->watermark));
                                }
                                ?>
                                <?= all_type_input_render([
                                    "label" => "<b>Watermark Image</b>",
                                    "id" => "watermark_image",
                                    "name" => "file",
                                    "type" => "fileupload",
                                    "is_required" => true,
                                    "form" => 'pdf-form'
                                ], 'col-md-3 image-section', true);
                                ?>
                                <div class="col-md-4 image-section preview-section">
                                    <?php
                                    if (isset($setting) && ($setting->watermark_type == "image")  && !empty($setting->watermark)) {
                                    ?>
                                        <img class="preview-image" src="<?= $watermark_url ?>" />
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div class="col-md-12 mtop5">
                                    <button type="submit" class="btn btn-info btn-xs pull-right" form="pdf-form" onclick="tinyMCE.triggerSave(true,true);">Save</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var editor = init_editor('.text-editor');
        var cur_type = "<?= isset($setting->watermark_type) ? $setting->watermark_type : '' ?>";
        $('#pdf-form').appFormValidator({
            rules: {
                watermark_type: 'required',
                file: {
                    required: function(element) {
                        var type = $('input[name="watermark_type"]:checked').val();
                        if (type == "image") {
                            if ($('.preview-image').length > 0) {
                                return false;
                            } else {
                                return true;
                            }
                        }
                        return false;
                    }
                },
                watermark: {
                    required: function(element) {
                        var type = $('input[name="watermark_type"]:checked').val();
                        if (type == "text") {
                            return true;
                        }
                        return false;
                    },
                }
            },
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
        });

        $('input[name="watermark_type"]').on('change', function() {
            if (cur_type == "image" || cur_type == "no") {
                $('#fieldwatermark_text').val("");
            }
            var type = $('input[name="watermark_type"]:checked').val();
            if (type == "text") {
                $('.text-section').removeClass('hide');
                $('.image-section').addClass('hide');
                $('#fileupload_watermark_image').prop('required', false);
                $('#fieldwatermark_text').prop('required', true);
            } else if (type == "image") {
                if ($('.preview-image').length > 0) {
                    $('#fileupload_watermark_image').attr('required', false);
                } else {
                    $('#fileupload_watermark_image').attr('required', true);
                }
                $('#fieldwatermark_text').prop('required', false);
                $('.text-section').addClass('hide');
                $('.image-section').removeClass('hide');
            } else {
                $('#fieldwatermark_text').prop('required', false);
                $('#fileupload_watermark_image').prop('required', false);
                $('.text-section').addClass('hide');
                $('.image-section').addClass('hide');
            }
        });
        $('input[name="watermark_type"]').trigger('change');

        $('#fileupload_watermark_image').on('change', function() {
            var file = this.files[0];
            if (file) {
                var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert_float('danger', 'Only JPG, JPEG, and PNG files are allowed.');
                    $(this).val('');
                } else if (file.size > 500 * 1024) {
                    alert_float('danger', 'Image size should not be more than 500 KB');
                    $(this).val('');
                } else {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('.preview-section').find('.preview-image').remove();
                        $('.preview-section').html('<img class="preview-image" src="' + e.target.result + '" />');
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    });
</script>
<style>
    .panel-heading {
        font-size: 18px;
    }

    .preview-image {
        width: 150px;
        height: 100px;
    }
</style>
</body>

</html>