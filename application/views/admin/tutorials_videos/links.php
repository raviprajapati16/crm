<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    input[type="color"] {
        width: 40%;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('Type'),
                            _l('Button Text'),
                            _l('Active'),
                        ), 'tutorials-links'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="tutorials_links_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open_multipart(admin_url('tutorials_videos/link_save'), array("id" => "tutorialLinkForm")); ?>
        <input type="hidden" id="tutorial_link_id" name="id" form="tutorialLinkForm" value="" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add New Videos</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="title-text"></h4>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="button_text">Button Text</label>
                            <input type="text" name="button_text" id="button_text" class="form-control" form="tutorialLinkForm" maxlength="45" required />
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="select-placeholder form-group">
                            <label for="video_id">Tutorial Video</label>
                            <select name="video_id" id="video_id" class="selectpicker" data-width="100%" data-none-selected-text="Select Tutorial Video" form="tutorialLinkForm" required>
                                <option value=""></option>
                                <?php
                                foreach ($tutorialVideos as $key => $video) {
                                ?>
                                    <option value="<?= $video['id'] ?>"><?= $video['title'] ?></option>
                                <?php
                                }
                                ?>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_color">Button Color</label>
                            <input type="color" name="button_color" id="button_color" class="form-control" form="tutorialLinkForm" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_hover_color">Button Hove Color</label>
                            <input type="color" name="button_hover_color" id="button_hover_color" class="form-control" form="tutorialLinkForm" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_hover_color">Button Text Color</label>
                            <input type="color" name="button_text_color" id="button_text_color" class="form-control" form="tutorialLinkForm" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="button_hover_color">Button Hover Text Color</label>
                            <input type="color" name="button_hover_text_color" id="button_hover_text_color" class="form-control" form="tutorialLinkForm" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="button_hover_color">Active</label>
                        <div class="onoffswitch onoffswitch-main">
                            <input type="checkbox" name="active" class="onoffswitch-checkbox onoffswitch" id="activeSwitch">
                            <label class="onoffswitch-label" for="activeSwitch"></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="tutorialLinkForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table;
    $(function() {
        table = initDataTable('.table-tutorials-links', window.location.href, [0], [0]);

        $('#tutorialLinkForm').appFormValidator({
            rules: {
                title: 'required',
                link: 'required',
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

    function openTutorialModal(id = "") {
        var parent = $('#tutorials_links_modal');
        $.ajax({
            url: "<?php echo admin_url('tutorials_videos/get_links_data'); ?>",
            method: "POST",
            data: {
                id: id
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                parent.find('.title-text').text(result.data.type);
                parent.find('#tutorial_link_id').val(result.data.id);
                parent.find('#button_text').val(result.data.button_text);
                parent.find('#button_color').val(result.data.button_color);
                parent.find('#button_hover_color').val(result.data.button_hover_color);
                parent.find('#button_hover_color').val(result.data.button_hover_color);
                parent.find('#button_text_color').val(result.data.button_text_color);
                parent.find('#button_hover_text_color').val(result.data.button_hover_text_color);
                parent.find('#video_id').val(result.data.video_id).selectpicker('refresh');
                if (result.data.active == '1') {
                    parent.find('#activeSwitch').prop("checked", true);
                } else {
                    parent.find('#activeSwitch').prop("checked", false);
                }
                parent.find('.modal-title').html("Edit Button Link");
                parent.modal('show');
            } else {
                alert_float("danger", "Something went wrong.")
            }
        });

    }
</script>
</body>

</html>