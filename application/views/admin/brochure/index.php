<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php
                        if (has_permission('brochure', '', 'create')) {
                        ?>
                            <div class="_buttons">
                                <a href="javascript:;" onclick="openBrochureModal()" class="btn btn-info pull-left display-block">Create</a>
                            </div>
                        <?php
                        }
                        ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                render_datatable(
                                    array(
                                        'Sr. No.',
                                        'Brochure',
                                        _l('action'),
                                    ),
                                    'brochure-presentation',
                                    array(
                                        'id' => 'brochure-presentation',
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="brochure_modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog">
            <?php echo form_open_multipart(admin_url('brochure/save'), array("id" => "brochureForm")); ?>
            <input type="hidden" id="brochure_id" name="id" form="brochureForm" value="" />
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create New Brochure</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" id="title" class="form-control" form="brochureForm" required />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="file">File</label>
                                <input type="file" name="file" id="file" form="brochureForm" class="form-control" accept=".pdf" required />
                                <div class="file-preview-section mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    <button type="submit" class="btn btn-info" form="brochureForm"><?php echo _l('submit'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    $(document).ready(function() {
        $(window).off('beforeunload');
        var table = initDataTable('.table-brochure-presentation', window.location.href, [0], [0,1,2], []);
        $('#brochureForm').appFormValidator({
            rules: {
                title: 'required',
                file: {
                    required: function() {
                        if ($('#brochure_modal').find('.filepreviewlink').length == 0) {
                            return true;
                        }
                        return false;

                    }
                },
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                formGroup.append(error);
            },
            submitHandler: function(form) {
                var $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true);
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                form.submit();
            }
        });

        $(document).on('click', '.copybtn', function() {
            var textToCopy = $(this).attr('data-url');
            var tempElement = $("<textarea>");
            $("body").append(tempElement);
            tempElement.val(textToCopy).select();
            document.execCommand("copy");
            tempElement.remove();
            alert_float('success', "Presentation Public URL successfully copied.");
        });

        $('#file').on('change', function() {
            var file = this.files[0];
            var formGroup = $(this).closest('.form-group');
            var previewSection = formGroup.find('.file-preview-section');
            var errorSection = formGroup.find('.file-error-section');

            previewSection.empty();
            errorSection.remove();

            if (file) {
                var maxSizeInBytes = 100 * 1024 * 1024;
                if (file.size > maxSizeInBytes) {
                    $('<div>', {
                        text: 'Error: File size exceeds the maximum limit of 100 MB.',
                        class: 'file-error-section text-danger'
                    }).insertAfter(previewSection);
                    $(this).val('');
                    return;
                }

                var fileExtension = file.name.split('.').pop().toLowerCase();
                if (fileExtension !== 'pdf') {
                    $('<div>', {
                        text: 'Error: Only PDF files are allowed.',
                        class: 'file-error-section text-danger'
                    }).insertAfter(previewSection);
                    $(this).val('');
                    return;
                }

                var objectUrl = URL.createObjectURL(file);
                var previewLink = $('<a>', {
                    href: objectUrl,
                    text: 'Preview: ' + file.name,
                    target: '_blank',
                    class: 'text-info filepreviewlink'
                });
                previewSection.append(previewLink);
            }
        });
    });

    function openBrochureModal(id = "") {
        var parent = $('#brochure_modal');
        if (id == "") {
            parent.find('#file').val("");
            parent.find('#title').val("");
            parent.find('#brochure_id').val("");
            parent.find('.modal-title').html("Add New Brochure");
            parent.modal('show');
        } else {
            parent.find('.file-preview-section').empty();
            $.ajax({
                url: "<?php echo admin_url('brochure/get_data'); ?>",
                method: "POST",
                data: {
                    id: id
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    parent.find('#title').val(result.data.title);
                    parent.find('#brochure_id').val(result.data.id);
                    if (result.data.previewLink) {
                        parent.find('.file-preview-section').append(`<a href="${result.data.previewLink}" target="_blank" class="text-info filepreviewlink">Preview: ${result.data.file_name}</a>`);
                        parent.find('#file').prop('checked', false);
                    } else {
                        parent.find('#file').prop('checked', true);
                    }
                    parent.find('.modal-title').html("Edit Brochure");
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