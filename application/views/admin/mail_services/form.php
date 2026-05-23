<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content email-templates">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <?= ucfirst($title) ?>
                    </div>
                    <div class="panel-body">
                        <?php echo form_open_multipart(admin_url('mailservices/service/' . $service->id), array('id' => 'service-form')); ?>


                        <div class="row">
                            <?= all_type_input_render([
                                "label" => "Service Name",
                                "id" => "service_name",
                                "name" => "service_name",
                                "type" => "text",
                                "is_required" => true,
                                "selected_value" => (isset($service->service_name)) ? $service->service_name : '',
                                "form" => 'service-form'
                            ], 'col-md-4', true); ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="checkbox checkbox-inline mbot25">
                                    <input type="checkbox" value="1" id="only_send" name="only_send" <?= (isset($service->only_send) && $service->only_send == "1") ? 'checked' : '' ?>>
                                    <label for="only_send">Only Send Emails</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>SMTP Settings</h4>
                                    </div>
                                    <?= all_type_input_render([
                                        "label" => "SMTP Encryption",
                                        "id" => "smtp_encryption",
                                        "name" => "smtp_encryption",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->smtp_encryption) ? $service->smtp_encryption : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>

                                    <?= all_type_input_render([
                                        "label" => "SMTP Host",
                                        "id" => "smtp_host",
                                        "name" => "smtp_host",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->smtp_host) ? $service->smtp_host : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>

                                    <?= all_type_input_render([
                                        "label" => "SMTP Port",
                                        "id" => "smtp_port",
                                        "name" => "smtp_port",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->smtp_port) ? $service->smtp_port : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>

                                    <?= all_type_input_render([
                                        "label" => "Email Charset",
                                        "id" => "email_charset",
                                        "name" => "email_charset",
                                        "type" => "text",
                                        "is_required" => false,
                                        "selected_value" => isset($service->email_charset) ? $service->email_charset : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>
                                </div>
                            </div>

                            <!-- IMAP Settings -->
                            <div class="col-md-6 imap-section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>IMAP Settings</h4>
                                    </div>

                                    <?= all_type_input_render([
                                        "label" => "IMAP Encryption",
                                        "id" => "imap_encryption",
                                        "name" => "imap_encryption",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->imap_encryption) ? $service->imap_encryption : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>

                                    <?= all_type_input_render([
                                        "label" => "IMAP Host",
                                        "id" => "imap_host",
                                        "name" => "imap_host",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->imap_host) ? $service->imap_host : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>

                                    <?= all_type_input_render([
                                        "label" => "IMAP Port",
                                        "id" => "imap_port",
                                        "name" => "imap_port",
                                        "type" => "text",
                                        "is_required" => true,
                                        "selected_value" => isset($service->imap_port) ? $service->imap_port : '',
                                        "form" => 'service-form'
                                    ], 'col-md-12'); ?>
                                </div>
                            </div>


                            <!-- Submit Button -->
                            <div class="col-md-12">
                                <div class="col-md-12 mtop5">
                                    <button type="submit" class="btn btn-info btn-xs pull-right" form="service-form">Save</button>
                                </div>
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
        // Apply form validation rules
        $('#service-form').appFormValidator({
            rules: {
                service_name: 'required',
                smtp_host: 'required',
                smtp_port: 'required',
                smtp_encryption: 'required',
                imap_host: {
                    required: function(element) {
                        if ($('.imap-section').hasClass('hide')) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                },
                imap_port: {
                    required: function(element) {
                        if ($('.imap-section').hasClass('hide')) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                },
                imap_encryption: {
                    required: function(element) {
                        if ($('.imap-section').hasClass('hide')) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                },
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                formGroup.append(error);
            },
        });

        $('#only_send').on('change', function() {
            if (this.checked) {
                $('.imap-section').addClass('hide');
                $('.imap-section input').val('');
            } else {
                $('.imap-section').removeClass('hide');
            }
        });
        $('#only_send').trigger('change');

    });
</script>
<style>
    .panel-heading {
        font-size: 18px;
    }
</style>
</body>

</html>