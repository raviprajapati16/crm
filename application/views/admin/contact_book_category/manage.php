<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="javascript:;" onclick="openCategoryModal()" class="btn btn-info pull-left display-block">Create</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('Name'),
                        ), 'category-list'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="category_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('contact_book_category/save'), array("id" => "categoryForm")); ?>
        <input type="hidden" id="category_id" name="id" form="categoryForm" value="" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create Category</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control" maxlength="100" form="categoryForm" required />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="categoryForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        initDataTable('.table-category-list', window.location.href, [0], [0]);

        $('#categoryForm').appFormValidator({
            rules: {
                name: 'required',
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
    });

    function openCategoryModal(id = "") {
        var parent = $('#category_modal');
        if (id == "") {
            parent.find('#name').val("");
            parent.find('#category_id').val("");
            parent.find('.modal-title').html("Create Category");
            parent.modal('show');
        } else {
            $('#file').closest('.form-group').addClass('hide');
            var element = $('a[data-id="' + id + '"]');
            parent.find('#name').val(element.attr('data-name'));
            parent.find('#category_id').val(element.attr('data-id'));
            parent.find('.modal-title').html("Edit Category");
            parent.modal('show');
        }
    }
</script>
</body>

</html>