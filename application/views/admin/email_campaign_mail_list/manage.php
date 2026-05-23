<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('email_campaigns', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="javascript:;" onclick="openMailListModal()" class="btn btn-info pull-left display-block"><?php echo _l('new_mail_list'); ?></a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('List Name'),
                            _l('Created By'),
                            _l('Created At'),
                        ), 'mail-lists'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="maillist_model" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('email_campaign_mail_list/save'), array("id" => "mailListForm")); ?>
        <input type="hidden" id="maillist_id" name="id" form="mailListForm" value="" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create New Email List</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <a href="<?= site_url('uploads/demo_files/email_campaign_mail_list_demo_file.csv') ?>" class="btn btn-primary btn-sm csv-sample-btn" download="demo.csv">Download Demo CSV</a>
                        <br><br><br>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="title">List Name</label>
                            <input type="text" name="title" id="title" class="form-control" form="mailListForm" required />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="file">CSV File</label>
                            <input type="file" name="file" id="file" form="mailListForm" class="form-control" accept=".csv,.xls,.xlsx" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="mailListForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        initDataTable('.table-mail-lists', window.location.href, [0], [0]);

        $('#mailListForm').appFormValidator({
            rules: {
                title: 'required',
                file: {
                    required: function() {
                        if ($('#maillist_id').val() == "" || $('#maillist_id').val() == null) {
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

        $('#file').on('change', function() {
            var file = this.files[0];
            var formGroup = $(this).closest('.form-group');
            var errorSection = formGroup.find('.file-error-section');
            errorSection.remove();
            if (file) {
                var maxSizeInBytes = 100 * 1024 * 1024;
                if (file.size > maxSizeInBytes) {
                    $('<div>', {
                        text: 'Error: File size exceeds the maximum limit of 100 MB.',
                        class: 'file-error-section text-danger'
                    }).insertAfter('#file');
                    $(this).val('');
                    return;
                }

                var extensions = ["csv", "xls", "xlsx"];
                var fileExtension = file.name.split('.').pop().toLowerCase();
                console.log(fileExtension)
                if (!extensions.includes(fileExtension)) {
                    $('<div>', {
                        text: 'Error: Only CSV, XLSX, XLS files are allowed.',
                        class: 'file-error-section text-danger'
                    }).insertAfter('#file');
                    $(this).val('');
                    return;
                }
            }
        });
    });

    function openMailListModal(id = "") {
        var parent = $('#maillist_model');
        if (id == "") {
            $('.csv-sample-btn').closest('.col-md-12').removeClass('hide');
            $('#file').closest('.form-group').removeClass('hide');
            parent.find('#file').val("");
            parent.find('#title').val("");
            parent.find('#maillist_id').val("");
            parent.find('.modal-title').html("Create New Mail List");
            parent.modal('show');
        } else {
            $('.csv-sample-btn').closest('.col-md-12').addClass('hide');
            $('#file').closest('.form-group').addClass('hide');
            var element = $('a[data-id="' + id + '"]');
            parent.find('#title').val(element.attr('data-title'));
            parent.find('#maillist_id').val(element.attr('data-id'));
            parent.find('.modal-title').html("Edit Mail List");
            parent.modal('show');
        }
    }
</script>
</body>

</html>