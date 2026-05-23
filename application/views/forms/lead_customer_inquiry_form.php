<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$logo_url = base_url('uploads/company/' . get_option('company_logo'));
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title>Customer inquiry Form</title>
    <?php app_external_form_header(); ?>
    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/jquery-steps/css/jquery.steps.css') ?>">
</head>
<?php
$company_logo = base_url('uploads/company/' . get_option('company_logo'));
?>

<?php
theme_style_clients_area_head();
?>
<style>
    .btn-primary,
    .btn-info {
        padding: 10px;
    }

    .wizard>.actions a,
    .wizard>.actions a:hover,
    .wizard>.actions a:active {
        display: block;
        padding: 0.5em 1em;
        text-decoration: none;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
    }


    .dynamic-section .panel,
    .product-form-section .panel {
        position: relative;
        padding: 15px;
        border-radius: 25px;
        background-color: white;
        z-index: 1;
    }

    .dynamic-section .panel::before,
    .product-form-section .panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 25px;
        padding: 4px;
        -webkit-mask:
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        mask:
            linear-gradient(#fff 0 0) content-box,
            linear-gradient(#fff 0 0);
        mask-composite: exclude;
        -webkit-mask-composite: destination-out;
        z-index: -1;
    }

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
        padding: 20px;
        background: #F7F7F7;
        border-radius: 20px;
    }

    @media (max-width: 768px) {
        .logo {
            width: 60%;
            margin: auto;
            margin-bottom: 10px;
            padding: 10px;
            background: #F7F7F7;
            border-radius: 10px;
        }
    }

    .dynamic-section {
        margin-top: 15px;
    }

    .form-container .panel {
        border-width: <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "3px" : "10px" ?>;
        border-color: <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "#115516" : "#fff" ?> !important;
        background-color: <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "transparent" : "#fff" ?>;
    }

    .panel-primary>.panel-heading {
        border-color: #fff !important;
        border-radius: 10px;
    }

    .panel-heading {
        padding: 25px;
        margin: <?= (get_option('lead_forms_background_image_slider_active') == "1") ? "10px" : "none" ?>;
    }

    .panel-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
    }

    .error {
        color: red;
    }

    #fieldadditional_info {
        resize: none;
    }

    .mtop10 {
        margin-top: 10px;
    }

    .mtop5 {
        margin-top: 5px;
    }

    .mtop20 {
        margin-top: 20px;
    }

    .disable-select.btn-default {
        background: #eee !important;
        pointer-events: none !important;
    }

    .steps {
        display: none !important;
    }

    .wizard>.content {
        overflow-y: auto;
        background-color: transparent;
    }

    .dt-loader {
        transform: translateZ(1px);
        display: flex;
        flex-direction: column;
        align-items: center
    }

    .dt-loader:after {
        content: '';
        display: inline-block;
        width: 48px;
        height: 48px;
        background: url('<?= get_favicon_link(); ?>') no-repeat center center;
        background-size: cover;
        box-sizing: border-box;
        box-shadow: 2px 2px 2px 1px rgb(0 0 0 / .1);
        animation: logo-flip 1s linear infinite
    }

    .dt-loader span {
        margin-top: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #333
    }

    @keyframes logo-flip {
        0% {
            transform: rotateY(0deg)
        }

        100% {
            transform: rotateY(360deg)
        }
    }

    .slider {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        overflow: hidden;
        z-index: -1;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #fff !important;
        opacity: 0.89;
    }

    .slide {
        position: absolute;
        top: 0;
        left: 100%;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: left 1s ease-in-out;
    }

    .active {
        left: 0;
    }

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
    }

    .steps {
        display: none !important;
    }

    .wizard>.content {
        background-color: transparent;
        overflow-y: auto;
    }

    .full-screen-modal {
        width: 100%;
        height: 100%;
        padding: 0;
        backdrop-filter: blur(15px);
    }

    .full-screen-modal .modal-content {
        height: 100%;
        position: relative;
        padding: 20px;
        border: none;
    }

    .full-screen-modal .modal-body {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0;
    }

    .full-screen-modal .modal-body img {
        max-width: 90%;
        max-height: 100%;
    }

    .full-screen-modal .modal-dialog {
        width: 50%;
        max-width: 90%;
        margin: 30px auto;
    }

    .full-screen-modal .modal-image {
        width: 100%;
        height: auto;
        max-width: 100%;
        border-radius: 10px;
        padding: 10px;
    }

    .full-screen-modal .close-btn {
        position: absolute;
        top: 0;
        right: 10px;
        color: #0D572E;
        z-index: 1000;
        border-radius: 100%;
        font-weight: bold;
        font-size: 24px;
        cursor: pointer;
        padding: 5px;
        opacity: 1;
    }

    .full-screen-modal .modal-body {
        padding: unset;
    }

    @media only screen and (max-width: 768px) {
        .full-screen-modal .modal-dialog {
            width: 100%;
            max-width: 100%;
            top: 30%;
        }

        .full-screen-modal .modal-image {
            padding: 3px;
        }

        .full-screen-modal .close-btn {
            font-size: 18px;
        }

        .panel-heading {
            padding: 15px;
        }

        .panel-heading .panel-title {
            font-size: 15px;
        }
    }
</style>

<body class="lead-inquiry-form <?php echo $form['formkey']; ?>">
    <?php if (!empty($background_slider_image) && get_option('lead_forms_background_image_slider_active') == "1") { ?>
        <div class="slider" id="slider">
            <?php $i = 0;
            foreach ($background_slider_image as $key => $item) {
                if (file_exists('uploads/lead_inquiry_form_images/' . $item['value'])) {
                    $imgurl = base_url('uploads/lead_inquiry_form_images/' . $item['value']);
            ?>
                    <div class="slide <?= ($i == 0) ? 'active' : '' ?>" style="background-image: url('<?= $imgurl ?>');"></div>
            <?php $i++;
                }
            }
            ?>
            <div class="overlay"></div>
        </div>
    <?php } ?>
    <div class="container form-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div id="logo">
                    <?php get_company_logo('/') ?>
                </div>
            </div>
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title text-center">Customer Inquiry Form</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($form['is_active']) { ?>
                                    <?php echo form_open_multipart($this->uri->uri_string(), array('id' => $form['formkey'], 'class' => 'disable-on-submit')); ?>
                                    <?php echo form_hidden('key', $form['formkey']); ?>
                                    <div class="row">
                                        <div id="wizard">
                                            <?php if (!empty($form_questions)) {
                                                $question_index = 1;
                                                $questionsGroup = splitQuestionsIntoGroups($form_questions);
                                                foreach ($questionsGroup as $key => $group) {
                                            ?>
                                                    <h2>Step <?= ($key + 1) ?></h2>
                                                    <section>
                                                        <?php foreach ($group as $key1 => $item) {
                                                            $item['lead_id'] = $form['lead_id'];
                                                            echo customer_inquiry_form_render($item, $question_index, true, $form['formkey']);
                                                            $question_index++;
                                                        }
                                                        ?>
                                                    </section>
                                            <?php
                                                }
                                            } ?>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                <?php } else {
                                    $message = "";
                                    if ($form['form_status'] == "pending" && !empty($form['customer_form_submitted'])) {
                                        $message = "Thankyou ! Your form is successfully submitted. Form Approval is Pending...";
                                    } else if ($form['form_status'] == "approved") {
                                        $message = "Thankyou ! Your form has been approved.";
                                    } else {
                                        $message = "Thankyou ! Your form successfully submitted.";
                                    }
                                ?>
                                    <div class="alert alert-success text-center"><?= $message; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($popup_image)) {
        if (file_exists('uploads/lead_inquiry_form_images/' . $popup_image['value'])) {
    ?>
            <div class="modal fade full-screen-modal" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <img src="<?= base_url('uploads/lead_inquiry_form_images/' . $popup_image['value']) ?>" class="img-responsive modal-image" alt="Sample Image">
                        </div>
                        <button type="button" class="close close-btn" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                </div>
            </div>

    <?php }
    } ?>
    <?php app_external_form_footer(); ?>
    <script src="<?= site_url('assets/plugins/jquery-steps/jquery.steps.js') ?>"></script>
    <script>
        var form_id = '#<?php echo $form['formkey']; ?>';
        $(function() {
            var $slider = $("#slider");
            var $slides = $slider.children(".slide");
            var currentSlideIndex = 0;

            function nextSlide() {
                $slides.eq(currentSlideIndex).removeClass("active");
                currentSlideIndex = (currentSlideIndex + 1) % $slides.length;
                $slides.eq(currentSlideIndex).addClass("active");
            }
            setInterval(nextSlide, 4000);

            $('#imageModal').modal('show');
            $('select').selectpicker('destroy');
            $('.render-input-disabled').on('keydown', false);
            var validator = $(form_id).validate({
                errorPlacement: function(error, element) {
                    var inputType = $(element).attr('type')
                    var formGroup = $(element).closest('.form-group');
                    $(formGroup).append(error);
                },
                submitHandler: function(form) {
                    if ($("input[type='file']").length > 0) {
                        var fileInput = form.querySelector("input[type='file']");
                        if (!validateFileInput(fileInput)) {
                            return false;
                        }
                    }
                    form.submit();
                }
            });

            $("#wizard").steps({
                headerTag: "h2",
                bodyTag: "section",
                transitionEffect: "slideLeft",
                onInit: function(event, currentIndex) {
                    stepFormInit();
                },
                onStepChanging: function(event, currentIndex, newIndex) {
                    if (newIndex > currentIndex) {
                        return validator.form();
                    }
                    return true;
                },
                onFinishing: function(event, currentIndex) {
                    return validator.form();
                },
                onFinished: function(event, currentIndex) {
                    if (validator.form()) {
                        $('form').submit();
                    }
                }
            });

            function stepFormInit() {
                $('select').selectpicker();

                $('select').on('change', function() {
                    $(this).closest('.form-group').find('span.error').remove();
                });

                $('input[type="file"]').each(function() {
                    var formgroup = $(this).closest('.form-group');
                    fileUploadRequiredValidationCheck(formgroup);
                });

                $(form_id).find('.delete-inquiry-file').on('click', function() {
                    var formgroup = $(this).closest('.form-group')
                    var formKey = $('input[name="key"]').val();
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
                            }
                        });
                    }
                });

                $(document).on('click', '.delete-inquiry-new-file', function() {
                    var formgroup = $(this).closest('.form-group');
                    if (confirm("Are you sure you want perform this action?")) {
                        formgroup.find('.file-preview-section').remove();
                        formgroup.find('input[type="file"]').val('');
                        fileUploadRequiredValidationCheck(formgroup);
                    }
                });

                $(form_id).find('input[type="file"]').on('change', function() {
                    var formgroup = $(this).closest('.form-group');
                    formgroup.find('span.error').remove();
                    fileUploadRequiredValidationCheck(formgroup);
                    const file = this.files[0];
                    var validation = validateFileInput(this);
                    if (file && validation) {
                        const fileName = file.name
                        const fileUrl = URL.createObjectURL(file);
                        formgroup.find('.file-preview-section').remove();
                        const previewHtml = `
                            <span class='file-preview-section'>
                                Preview: <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                                <i class='fa fa-trash text-danger delete-inquiry-new-file' aria-hidden='true'></i>
                            </span>
                        `;
                        formgroup.append(previewHtml);
                    }
                });
                $('.datepicker').datetimepicker({
                    format: 'd-m-Y',
                    timepicker: false,
                });
                $('.datetimepicker').datetimepicker({
                    format: 'd-m-Y H:i:s',
                    timepicker: true,
                });
            }

        });



        function getBrowserInfo() {
            const userAgent = navigator.userAgent;
            let browserName = "Unknown";
            let browserVersion = "Unknown";
            if (/Firefox\//.test(userAgent)) {
                browserName = "Mozilla Firefox";
                browserVersion = userAgent.match(/Firefox\/([\d.]+)/)[1];
            } else if (/Edg\//.test(userAgent)) {
                browserName = "Microsoft Edge";
                browserVersion = userAgent.match(/Edg\/([\d.]+)/)[1];
            } else if (/Chrome\//.test(userAgent)) {
                browserName = "Google Chrome";
                browserVersion = userAgent.match(/Chrome\/([\d.]+)/)[1];
            } else if (/Safari\//.test(userAgent) && !/Chrome\//.test(userAgent)) {
                browserName = "Apple Safari";
                browserVersion = userAgent.match(/Version\/([\d.]+)/)[1];
            } else if (/Opera\//.test(userAgent) || /OPR\//.test(userAgent)) {
                browserName = "Opera";
                browserVersion = userAgent.match(/(?:Opera\/|OPR\/)([\d.]+)/)[1];
            } else if (/Trident\//.test(userAgent)) {
                browserName = "Internet Explorer";
                browserVersion = userAgent.match(/Trident\/[\d.]+; rv:([\d.]+)/)[1];
            }
            return browserName + ' ' + browserVersion;
        }

        function getOSInfo() {
            const userAgent = navigator.userAgent;
            let osName = "Unknown";
            let osVersion = "Unknown";
            if (/Windows NT/.test(userAgent)) {
                osName = "Windows";
                osVersion = userAgent.match(/Windows NT (\d+\.\d+)/)[1];
                if (osVersion === "6.1") {
                    osVersion = "7";
                } else if (osVersion === "6.2") {
                    osVersion = "8";
                } else if (osVersion === "6.3") {
                    osVersion = "8.1";
                } else if (osVersion === "10.0") {
                    osVersion = "10";
                }
            } else if (/Mac OS X/.test(userAgent)) {
                osName = "macOS";
                osVersion = userAgent.match(/Mac OS X (\d+[._]\d+([._]\d+)?)/)[1].replace(/_/g, ".");
            } else if (/Android/.test(userAgent)) {
                osName = "Android";
                osVersion = userAgent.match(/Android\s([\d.]+)/)[1];
            } else if (/iPhone|iPad|iPod/.test(userAgent)) {
                osName = "iOS";
                osVersion = userAgent.match(/OS\s([\d_]+)/)[1].replace(/_/g, ".");
            } else if (/Linux/.test(userAgent)) {
                osName = "Linux";
            }
            return osName + ' ' + osVersion;
        }

        function getUserInfo(callback) {
            fetch('https://api.ipify.org?format=json')
                .then(response => response.json())
                .then(data => {
                    const ipAddress = data.ip;
                    const browserName = getBrowserInfo();
                    const browserVersion = navigator.appVersion;
                    const osInfo = navigator.platform;
                    const deviceType = /Mobile/.test(navigator.userAgent) ? 'Mobile' : 'Desktop';
                    callback({
                        ipAddress: ipAddress,
                        browser: {
                            name: browserName,
                            version: browserVersion
                        },
                        os: getOSInfo(),
                        deviceType: deviceType
                    });
                })
                .catch(error => {
                    console.error('Error fetching IP address:', error);
                    const browserName = getBrowserInfo();
                    const browserVersion = navigator.appVersion;
                    const osInfo = navigator.platform;
                    const deviceType = /Mobile/.test(navigator.userAgent) ? 'Mobile' : 'Desktop';
                    callback({
                        ipAddress: 'Unknown',
                        browser: {
                            name: browserName,
                            version: browserVersion
                        },
                        os: getOSInfo(),
                        deviceType: deviceType
                    });
                });
        }

        getUserInfo(function(userInfo) {
            $.ajax({
                url: "<?php echo site_url('forms/inquiry_analysis_log') ?>",
                method: "POST",
                data: {
                    formkey: "<?php echo $form['formkey']; ?>",
                    ip_address: userInfo.ipAddress,
                    browser_agent: userInfo.browser.name,
                    os_info: userInfo.os,
                    device_type: userInfo.deviceType,
                },
                dataType: 'json'
            }).done(function(result) {});
        });

        function validateFileInput(fileInput) {
            var maxSize = 5 * 1024 * 1024; // 5 MB
            var allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
            var isValid = true;
            var errors = [];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                var fileSize = file.size; // Size in bytes
                var fileName = file.name;
                var fileExtension = fileName.split('.').pop().toLowerCase();

                if (fileSize > maxSize) {
                    isValid = false;
                    errors.push("File size exceeds. Allowed maximum file size allowed up to " + (maxSize / 1024 / 1024) + " MB");
                }

                if (!allowedExtensions.includes(fileExtension)) {
                    isValid = false;
                    errors.push("Invalid file type. Allowed extensions are: " + allowedExtensions.join(", "));
                }
            }
            $(fileInput).siblings('.error').remove();
            if (!isValid) {
                fileInput.value = '';
                $(fileInput).siblings('.file-preview-section').remove();
                errors.forEach(function(error) {
                    $("<div class='validation-error text-danger error'>").text(error).insertAfter($(fileInput));
                });
            }
            return isValid;
        }

        function fileUploadRequiredValidationCheck(formgroup) {
            var fileInput = formgroup.find("input[type='file']");
            var form = formgroup.closest("form");
            var validator = form.data("validator");
            var hasFilePreview = formgroup.find('.file-preview-section').length > 0;
            if (validator) {
                if (hasFilePreview) {
                    fileInput.removeAttr('required');
                    formgroup.removeAttr('data-required');
                } else {
                    fileInput.attr('required', true);
                    formgroup.attr('data-required', 1);
                }
            }
        }
    </script>
</body>

</html>