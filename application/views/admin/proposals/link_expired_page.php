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
    <title><?= $proposal->subject ?></title>
    <?php app_external_form_header(); ?>
    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/jquery-steps/css/jquery.steps.css') ?>">
</head>
 <?php
    theme_style_clients_area_head();
    ?>
<style>
    body {
        user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
    }

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
        padding: 20px;
        background: #F7F7F7;
        border-radius: 20px;
    }

    .table,
    .panel-body {
        background: #fff;
    }

    @media (max-width: 767px) {
        .logo {
            width: 80%;
            padding: 10px;
        }
    }
</style>

<body class="expired-page <?php echo $form['formkey']; ?>">
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
                <div class="alert alert-danger text-center">
                    The link you tried to access has expired. For assistance, please contact support!<br>
                </div>
            </div>
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-primary">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                Your Proposal ID : <?= sprintf("%06d", $proposal->id);  ?>
                            </div>
                            <div class="col-md-12 mtop20">
                                <b>Please contact us on below details with your proposal ID.</b>
                            </div>
                            <div class="col-md-12 mtop20">
                                <?php
                                $staffData = get_staff($proposal->addedfrom);
                                $signatureCode = $staffData->email_signature;
                                if (empty($staffData) || empty(trim($signatureCode))) {
                                    $staffData = get_staff(1);
                                    $signatureCode = $staffData->email_signature;
                                }
                                $lines = explode("\n", $signatureCode);
                                if (isset($lines[0]) && (stripos($lines[0], 'thanks') !== false || stripos($lines[0], 'thank you') !== false)) {
                                    unset($lines[0]);
                                }
                                echo $contact_details = implode("\n", $lines);
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
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
            setInterval(nextSlide, 3000);
        });
    </script>
    <style>
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
            opacity: 0.6;
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

        .panel {
            background-color: transparent;
        }

        .logo {
            width: 40%;
            margin: auto;
            margin-bottom: 10px;
        }

        .panel-body {
            font-weight: bold;
        }
    </style>
</body>

</html>