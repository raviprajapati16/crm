<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row product-form-section">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title product-form-title">Product Form</h3>
        </div>
        <div class="panel-body">
            <div id="wizard">
                <input type="hidden" name="lead_inquriy_form_id" value="<?= $form['id'] ?>" form="<?= $form['office_visit_form_id'] ?>" />
                <?php if (!empty($form_questions)) {
                    $question_index = 1;
                    $questionsGroup = splitQuestionsIntoGroups($form_questions);
                    foreach ($questionsGroup as $key => $group) {
                ?>
                        <h2>Step <?= ($key + 1) ?></h2>
                        <section>
                            <?php foreach ($group as $key1 => $item) {
                                $item['lead_id'] = $form['lead_id'];
                                $staffLoggedIn = (is_staff_logged_in() || is_admin()) ? false : true;
                                echo customer_inquiry_form_render($item, $question_index, $staffLoggedIn, $form['office_visit_form_id']);
                                $question_index++;
                            }
                            ?>
                        </section>
                <?php
                    }
                } ?>
            </div>
        </div>
    </div>


</div>
<script>
    $(document).off('click', '.delete-inquiry-file');
    $(document).on('click', '.delete-inquiry-file', function() {
        var formgroup = $(this).closest('.form-group')
        var questionId = formgroup.attr('data-name');
        if (confirm("Are you sure you want perform this action?")) {
            $.ajax({
                url: "<?php echo site_url('forms/delete_inquiry_file') ?>",
                method: "POST",
                data: {
                    formkey: "<?php echo $form['formkey']; ?>",
                    id: questionId,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    formgroup.find('.file-preview-section').remove();
                    fileUploadRequiredValidationCheck(formgroup);
                    fileuploadrevalidate();
                }
            });
        }
    });
</script>