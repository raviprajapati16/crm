<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .edit-profile-body .radio-inline input[type=radio] {
        margin-left: -35px !important;
        margin-top: 1px !important;
    }

    #previewContent {
        border: 1px solid;
        padding: 10px;
        border-radius: 14px;
        width: 100%;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="row">
                <div class="col-md-12">
                    <?= tutorialLinkButtonRender('manage-profile', 'right','margin-bottom:15px;'); ?>
                    <?= tutorialLinkButtonRender('login-to-hrms', 'right','margin-bottom:15px;margin-right:5px;'); ?>
                </div>
            </div>
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body edit-profile-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'staff_profile_table', 'autocomplete' => 'off')); ?>
                        <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'two-factor-authentication', 'active' => 0)) == 0) { ?>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" value="1" name="two_factor_auth_enabled" id="two_factor_auth_enabled" <?php if ($current_user->two_factor_auth_enabled == 1) {
                                                                                                                                    echo ' checked';
                                                                                                                                } ?>>
                                <label for="two_factor_auth_enabled"><i class="fa fa-question-circle" data-placement="right" data-toggle="tooltip" data-title="<?php echo _l('two_factor_authentication_info'); ?>"></i>
                                    <?php echo _l('enable_two_factor_authentication'); ?></label>
                            </div>
                            <hr />
                        <?php } ?>
                        <?php if ($current_user->profile_image == NULL) { ?>
                            <div class="form-group">
                                <label for="profile_image" class="profile-image"><?php echo _l('staff_edit_profile_image'); ?></label>
                                <input type="file" name="profile_image" class="form-control" id="profile_image" accept=".jpg, .jpeg, .png">
                            </div>
                        <?php } ?>
                        <?php if ($current_user->profile_image != NULL) { ?>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-9">
                                        <?php echo staff_profile_image($current_user->staffid, array('img', 'img-responsive', 'staff-profile-image-thumb'), 'thumb'); ?>
                                    </div>
                                    <div class="col-md-3 text-right">
                                        <a href="<?php echo admin_url('staff/remove_staff_profile_image'); ?>"><i class="fa fa-remove"></i></a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="firstname" class="control-label"><?php echo _l('staff_add_edit_firstname'); ?></label>
                            <input type="text" class="form-control" name="firstname" value="<?php if (isset($member)) {
                                                                                                echo $member->firstname;
                                                                                            } ?>">
                        </div>
                        <div class="form-group">
                            <label for="lastname" class="control-label"><?php echo _l('staff_add_edit_lastname'); ?></label>
                            <input type="text" class="form-control" name="lastname" value="<?php if (isset($member)) {
                                                                                                echo $member->lastname;
                                                                                            } ?>">
                        </div>
                        <div class="form-group">
                            <label for="email" class="control-label"><?php echo _l('staff_add_edit_email'); ?></label>
                            <input type="email" <?php if (has_permission('staff', '', 'edit')) { ?> name="email" <?php } else { ?> disabled="true" <?php } ?> class="form-control" value="<?php echo $member->email; ?>" id="email">
                        </div>
                        <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                        <?php echo render_input('phonenumber', 'staff_add_edit_phonenumber', $value); ?>
                        <?php if (get_option('disable_language') == 0) { ?>
                            <div class="form-group select-placeholder">
                                <label for="default_language" class="control-label"><?php echo _l('localization_default_language'); ?></label>
                                <select name="default_language" data-live-search="true" id="default_language" class="form-control selectpicker" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""><?php echo _l('system_default_string'); ?></option>
                                    <?php foreach ($this->app->get_available_languages() as $availableLanguage) {
                                        $selected = '';
                                        if (isset($member)) {
                                            if ($member->default_language == $availableLanguage) {
                                                $selected = 'selected';
                                            }
                                        }
                                    ?>
                                        <option value="<?php echo $availableLanguage; ?>" <?php echo $selected; ?>><?php echo ucfirst($availableLanguage); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                        <div class="form-group select-placeholder">
                            <label for="direction"><?php echo _l('document_direction'); ?></label>
                            <select class="selectpicker" data-none-selected-text="<?php echo _l('system_default_string'); ?>" data-width="100%" name="direction" id="direction">
                                <option value="" <?php if (isset($member) && empty($member->direction)) {
                                                        echo 'selected';
                                                    } ?>></option>
                                <option value="ltr" <?php if (isset($member) && $member->direction == 'ltr') {
                                                        echo 'selected';
                                                    } ?>>LTR</option>
                                <option value="rtl" <?php if (isset($member) && $member->direction == 'rtl') {
                                                        echo 'selected';
                                                    } ?>>RTL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="facebook" class="control-label"><i class="fa fa-facebook"></i> <?php echo _l('staff_add_edit_facebook'); ?></label>
                            <input type="text" class="form-control" name="facebook" value="<?php if (isset($member)) {
                                                                                                echo $member->facebook;
                                                                                            } ?>">
                        </div>
                        <div class="form-group">
                            <label for="linkedin" class="control-label"><i class="fa fa-linkedin"></i> <?php echo _l('staff_add_edit_linkedin'); ?></label>
                            <input type="text" class="form-control" name="linkedin" value="<?php if (isset($member)) {
                                                                                                echo $member->linkedin;
                                                                                            } ?>">
                        </div>
                        <div class="form-group">
                            <label for="skype" class="control-label"><i class="fa fa-skype"></i> <?php echo _l('staff_add_edit_skype'); ?></label>
                            <input type="text" class="form-control" name="skype" value="<?php if (isset($member)) {
                                                                                            echo $member->skype;
                                                                                        } ?>">
                        </div>

                        <div class="form-group">
                            <label for="previewSignature" class="control-label">Email Signature <i class="fa fa-cog email-sign-setting-btn" style="font-size: 15px;" data-toggle="tooltip" data-title="Email Signature Setting" aria-hidden="true"></i></label>
                            <iframe style="width: 100%;" id="previewSignature" srcdoc="<?php echo htmlspecialchars(get_webmail_signature(), ENT_QUOTES, 'UTF-8'); ?>"></iframe>
                        </div>

                        <?php $value = (isset($member) ? $member->whatsapp_signature : ''); ?>
                        <?php echo render_textarea('whatsapp_signature', 'Whatsapp Signature', $value); ?>

                        <div class="form-group">
                            <div class="control-label">Notification Sound</div>
                            <div class="radio-section">
                                <label class="radio-inline" for="notification_sound_on">On
                                    <input type="radio" id="notification_sound_on" name="notification_sound" value="1" <?= (isset($member->notification_sound) && $member->notification_sound == "1") ? "checked" : ""  ?> />
                                </label>
                                <label class="radio-inline" for="notification_sound_off">Off
                                    <input type="radio" id="notification_sound_off" name="notification_sound" value="0" <?= (isset($member->notification_sound) && $member->notification_sound == "0") ? "checked" : ""  ?> />
                                </label>
                            </div>
                        </div>
                        <?php if (count($staff_departments) > 0) { ?>
                            <div class="form-group">
                                <label for="departments"><?php echo _l('staff_edit_profile_your_departments'); ?></label>
                                <div class="clearfix"></div>
                                <?php
                                foreach ($departments as $department) { ?>
                                    <?php
                                    foreach ($staff_departments as $staff_department) {
                                        if ($staff_department['departmentid'] == $department['departmentid']) { ?>
                                            <div class="chip-circle mtop20"><?php echo $staff_department['name']; ?></div>
                                    <?php }
                                    }

                                    ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo _l('staff_edit_profile_change_your_password'); ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open('admin/staff/change_password_profile', array('id' => 'staff_password_change_form')); ?>
                        <div class="form-group">
                            <label for="oldpassword" class="control-label"><?php echo _l('staff_edit_profile_change_old_password'); ?></label>
                            <input type="password" class="form-control" name="oldpassword" id="oldpassword">
                        </div>
                        <div class="form-group">
                            <label for="newpassword" class="control-label"><?php echo _l('staff_edit_profile_change_new_password'); ?></label>
                            <input type="password" class="form-control" id="newpassword" name="newpassword">
                        </div>
                        <div class="form-group">
                            <label for="newpasswordr" class="control-label"><?php echo _l('staff_edit_profile_change_repeat_new_password'); ?></label>
                            <input type="password" class="form-control" id="newpasswordr" name="newpasswordr">
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                    <?php if ($member->last_password_change != NULL) { ?>
                        <div class="panel-footer">
                            <?php echo _l('staff_add_edit_password_last_changed'); ?>:
                            <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($member->last_password_change); ?>">
                                <?php echo time_ago($member->last_password_change); ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Email Service</h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open_multipart('admin/staff/mail_service/' . $member->staffid, array('id' => 'mail_service_form')); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="mail_services_select"><?php echo _l('mail_service'); ?></label>
                                    <select class="form-control" data-width="100%" name="mail_service" id="mail_services_select">
                                        <option value="">Select Mail Service</option>
                                        <?php
                                        if (!empty($mail_services)) {
                                            foreach ($mail_services as $key => $service) {
                                        ?>
                                                <option value="<?= $service['id'] ?>" <?= (isset($member) && $service['id'] == $member->mail_service) ? "selected" : "" ?>><?= $service['service_name'] ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <?php $value = (isset($member) ? $member->webmail_email : ''); ?>
                                <?php echo render_input('webmail_email', 'Email', $value, 'text', $attrs); ?>
                            </div>
                            <div class="col-md-12">
                                <?php $value = (isset($member) ? $member->webmail_password : ''); ?>
                                <?php echo render_input('webmail_password', 'Password', $value, 'password', []); ?>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="setting-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <?php echo form_open(admin_url('webmails/update_webmail_signature'), array("name" => "signatureForm", "id" => "signatureForm")); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Email Signature Builder</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user" aria-hidden="true"></i> First Name',
                        "id" => "firstName",
                        "name" => "firstName",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('firstName'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user" aria-hidden="true"></i> Last Name',
                        "id" => "lastName",
                        "name" => "lastName",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('lastName'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user-circle" aria-hidden="true"></i> Designation',
                        "id" => "title",
                        "name" => "title",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('title'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-building" aria-hidden="true"></i> Company',
                        "id" => "company",
                        "name" => "company",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('company'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-envelope" aria-hidden="true"></i> Email Address',
                        "id" => "email",
                        "name" => "email",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('email'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-mobile" aria-hidden="true"></i> Mobile Number',
                        "id" => "mobile",
                        "name" => "mobile",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('mobile'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-phone" aria-hidden="true"></i> Telephone Number',
                        "id" => "phone",
                        "name" => "phone",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('phone'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-link" aria-hidden="true"></i> Website',
                        "id" => "website",
                        "name" => "website",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('website'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-facebook-square" aria-hidden="true"></i> Facebook Page Link',
                        "id" => "facebook",
                        "name" => "facebook",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('facebook'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-twitter" aria-hidden="true"></i> X (twitter) Account Link',
                        "id" => "twitter",
                        "name" => "twitter",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('twitter'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-instagram" aria-hidden="true"></i> Instagram Account Link',
                        "id" => "instagram",
                        "name" => "instagram",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('instagram'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-linkedin-square" aria-hidden="true"></i> Linkedin Account Link',
                        "id" => "linkedin",
                        "name" => "linkedin",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('linkedin'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-youtube-square" aria-hidden="true"></i> Youtube Channel Link',
                        "id" => "youtube",
                        "name" => "youtube",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('youtube'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-pinterest-square" aria-hidden="true"></i> Pinterest Account Link',
                        "id" => "pinterest",
                        "name" => "pinterest",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('pinterest'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-address-book" aria-hidden="true"></i> Address Line 1',
                        "id" => "address1",
                        "name" => "address1",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('address1'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-address-book" aria-hidden="true"></i> Address Line 2',
                        "id" => "address2",
                        "name" => "address2",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('address2'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <?= all_type_input_render([
                                "label" => '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Disclaimer Text',
                                "id" => "disclaimer",
                                "name" => "disclaimer",
                                "type" => "textarea",
                                "rows" => 5,
                                "selected_value" => get_webmail_signature_data('disclaimer'),
                                "is_required" => false,
                                "form" => "signatureForm",
                            ], 'col-md-12', false);
                            ?>
                            <?php
                            $templateArr = array(
                                "template-1" => "Template 1",
                                "template-2" => "Template 2",
                                "template-3" => "Template 3",
                                "template-4" => "Template 4",
                                "template-5" => "Template 5",
                                "template-6" => "Template 6",
                                "template-7" => "Template 7",
                                "template-8" => "Template 8",
                                "template-9" => "Template 9",
                            );
                            ?>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="control-label"><i class="fa fa-th-list" aria-hidden="true"></i> Template</div>
                                    <select name="template" class="selectpicker" id="template" data-width="100%" form="signatureForm">
                                        <option value="" <?= (empty(get_webmail_signature_data('template'))) ? 'selected' : '' ?>>No Signature</option>
                                        <?php
                                        foreach ($templateArr as $key => $item) {
                                        ?>
                                            <option value="<?= $key ?>" <?= (get_webmail_signature_data('template') == $key) ? 'selected' : '' ?>><?= $item ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="preview-section">
                            <h4><strong>Preview : </strong></h4>
                            <iframe id="previewContent"></iframe>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="signatureForm"><?php echo _l('Save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {

        renderSignatureTemplate();
        $(document).on('click', '.email-sign-setting-btn', function() {
            $('#setting-modal').modal('show');
        });

        $('#signatureForm').on('change input paste', 'input, select, textarea', function() {
            renderSignatureTemplate();
        });

        $('#signatureForm').submit(function(e) {
            e.preventDefault();
            var form_data = new FormData($('#signatureForm')[0]);
            $.ajax({
                type: 'POST',
                url: $('#signatureForm').attr('action'),
                data: form_data,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        $('#setting-modal').modal('hide');
                        alert_float('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });

        function renderSignatureTemplate() {
            var form_data = new FormData($('#signatureForm')[0]);
            $.ajax({
                type: 'POST',
                url: "<?php echo admin_url('webmails/email_signature_preview') ?>",
                data: form_data,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function(response) {
                    var iframe = document.getElementById('previewContent');
                    if (response.success) {
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(response.html);
                        iframe.contentWindow.document.close();
                    } else {
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write('<div style="text-align: center;margin-top: 13%;">No Preview available</div>');
                        iframe.contentWindow.document.close();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        appValidateForm($('#staff_profile_table'), {
            firstname: 'required',
            lastname: 'required',
            email: 'required'
        });
        appValidateForm($('#staff_password_change_form'), {
            oldpassword: 'required',
            newpassword: 'required',
            newpasswordr: {
                equalTo: "#newpassword"
            }
        });
    });
</script>
</body>

</html>