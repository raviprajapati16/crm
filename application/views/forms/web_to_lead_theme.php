<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title><?php echo $form->name; ?></title>
    <?php app_external_form_header($form); ?>
    <?php hooks()->do_action('app_web_to_lead_form_head'); ?>
</head>
<style>
    body {
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
        background-size: 200% 200%;
        animation: colorSwap 5s ease-in-out infinite;
        height: 100vh;
        margin: 0;
    }

    @keyframes colorSwap {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .btn-primary,
    .btn-info {
        padding: 10px;
        background: rgb(13, 87, 46);
    }


    .panel {
        position: relative;
        padding: 15px;
        border-radius: 25px;
        background-color: white;
        z-index: 1;
    }

    @keyframes moveBorder {
        0% {
            background-position: 0% 50%;
        }

        100% {
            background-position: 100% 50%;
        }
    }

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
        padding: 20px;
        border-radius: 20px;
    }

    @media (max-width: 768px) {
        .logo {
            width: 60%;
            margin: auto;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
        }
    }

    .form-container .panel {
        border-width: "10px";
        border-color: "#fff";
        background-color: "#fff";
    }

    .panel-primary>.panel-heading {
        border-color: #fff !important;
        border-radius: 10px;
    }

    .panel-heading {
        padding: 25px;
        background: rgb(13, 87, 46);
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
        margin: <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "10px" : "none" ?>;
    }

    .panel-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
    }

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
    }

    @media only screen and (max-width: 768px) {
        .logo {
            width: 60%;
            margin: auto;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
        }

    }
</style>

<body class="web-to-lead <?php echo $form->form_key; ?>" <?php if (is_rtl(true)) {
                                                                echo ' dir="rtl"';
                                                            } ?>>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div id="logo">
                    <?php get_white_company_logo() ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <?php if (!empty($form->theme_form_title)) { ?>
                        <div class="panel-heading">
                            <h3 class="panel-title text-center"><?= $form->theme_form_title; ?></h3>
                        </div>
                    <?php } ?>
                    <div class="panel-body">
                        <div class="row">
                            <div class="<?php if ($this->input->get('col')) {
                                            echo $this->input->get('col');
                                        } else {
                                            echo 'col-md-12';
                                        } ?>">
                                <div id="response"></div>
                                <?php echo form_open_multipart($this->uri->uri_string(), array('id' => $form->form_key, 'class' => 'disable-on-submit')); ?>
                                <?php hooks()->do_action('web_to_lead_form_start'); ?>
                                <?php echo form_hidden('key', $form->form_key); ?>
                                <div class="row">
                                    <?php foreach ($form_fields as $field) {
                                        render_form_builder_field($field);
                                    } ?>
                                    <?php if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) { ?>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_site_key'); ?>"></div>
                                                <div id="recaptcha_response_field" class="text-danger"></div>
                                            </div>
                                        <?php } ?>
                                        <?php if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_lead_form') == 1) { ?>
                                            <div class="col-md-12">
                                                <div class="checkbox chk">
                                                    <input type="checkbox" name="accept_terms_and_conditions" required="true" id="accept_terms_and_conditions" <?php echo set_checkbox('accept_terms_and_conditions', 'on'); ?>>
                                                    <label for="accept_terms_and_conditions">
                                                        <?php echo _l('gdpr_terms_agree', terms_url()); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="text-left col-md-12 submit-btn-wrapper">
                                            <button class="btn btn-info" id="form_submit" type="submit"><?php echo $form->submit_btn_name; ?></button>
                                        </div>
                                        </div>

                                        <?php hooks()->do_action('web_to_lead_form_end'); ?>
                                        <?php echo form_close(); ?>
                                </div>
                                <?php app_external_form_footer($form); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <script>
        var form_id = '#<?php echo $form->form_key; ?>';
        $(function() {
            $(form_id).appFormValidator({

                onSubmit: function(form) {
                    $("input[type=file]").each(function() {
                        if ($(this).val() === "") {
                            $(this).prop('disabled', true);
                        }
                    });
                    var formURL = $(form).attr("action");
                    var formData = new FormData($(form)[0]);
                    $('#form_submit').prop('disabled', true);
                    $('#form_submit').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
                    $.ajax({
                        type: $(form).attr('method'),
                        data: formData,
                        mimeType: $(form).attr('enctype'),
                        contentType: false,
                        cache: false,
                        processData: false,
                        url: formURL
                    }).always(function() {
                        $('#form_submit').prop('disabled', false);
                        $('#form_submit').html('Submit');
                    }).done(function(response) {
                        response = JSON.parse(response);
                        // In case action hook is used to redirect
                        if (response.redirect_url) {
                            window.top.location.href = response.redirect_url;
                            return;
                        }
                        window.parent.location.href = 'https://advancebiofuel.in/thank-you/';
                        if (response.success == false) {
                            $('#recaptcha_response_field').html(response.message); // error message
                        } else if (response.success == true) {
                            $(form_id).remove();
                            $('#response').html('<div class="alert alert-success">' + response.message + '</div>');
                            $('html,body').animate({
                                scrollTop: $("#online_payment_form").offset().top
                            }, 'slow');
                        } else {
                            $('#response').html('Something went wrong...');
                        }
                        if (typeof(grecaptcha) != 'undefined') {
                            grecaptcha.reset();
                        }
                    }).fail(function(data) {
                        if (typeof(grecaptcha) != 'undefined') {
                            grecaptcha.reset();
                        }
                        if (data.status == 422) {
                            $('#response').html('<div class="alert alert-danger">Some fields that are required are not filled properly.</div>');
                        } else {
                            $('#response').html(data.responseText);
                        }
                    });
                    return false;
                }
            });
        });
    </script>
    <?php hooks()->do_action('app_web_to_lead_form_footer'); ?>
</body>

</html>