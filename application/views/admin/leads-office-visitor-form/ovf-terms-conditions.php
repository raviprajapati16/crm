<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">
                            Office visit form : Terms and Conditions
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open_multipart(admin_url('leads_office_visitor_forms/terms_and_conditions'), array('id' => "ovf_terms_condition")); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <textarea name="ovf_terms_and_conditions" id="ovf_terms_and_conditions_editor" class="texteditor"><?= get_option('office_visit_form_terms_and_conditions'); ?></textarea>
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
<?php init_tail(); ?>
<script>
    $(function() {
        tinyMceEditor('.texteditor', 900);
    });

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