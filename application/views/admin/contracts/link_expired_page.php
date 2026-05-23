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
    <title><?= $contract->subject ?></title>
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
    <div class="container form-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div id="logo">
                    <?php get_company_logo('/') ?>
                </div>
            </div>
            <?php if (isset($success)) { ?>
                <div class="col-md-8 col-md-offset-2">
                    <div class="alert alert-success text-center">
                        You have successfully signed the agreement. The agreement is currently under review. Once the
                        verification is completed, you will receive a confirmation email on your registered email ID.<br>
                    </div>
                </div>
            <?php } else { ?>
                <div class="col-md-8 col-md-offset-2">
                    <table class="table table-bordered">
                        <tr>
                            <th colspan="2" class="text-center"><strong>Agreement Sign Status</strong></th>
                        </tr>
                        <tr>
                            <th><strong>Name</strong></th>
                            <th><strong>Status</strong></th>
                        </tr>
                        <?php if (count($contract_contacts) > 0) { ?>
                            <?php foreach ($contract_contacts as $contact) {
                                $contact_name = $contact['name'];
                                ?>
                                <tr>
                                    <td><?= $contact_name ?></td>
                                    <td><?= ($contact['signed'] == "1") ? "<i class='fa fa-check text-success signicon'></i> Signed" : "<i class='fa fa-times signicon text-danger'></i> Not Signed";
                            } ?></td>
                            </tr>
                        <?php } ?>
                    </table>
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
                                    Your Agreement ID :<?= $contract->prefix . $contract->number; ?>

                                </div>
                                <div class="col-md-12 mtop20">
                                    <b>Please contact us on below details with your contract ID.</b>
                                </div>
                                <div class="col-md-12 mtop20">
                                    <?php
                                    $staffData = get_staff($contract->addedfrom);
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
            <?php } ?>
        </div>
    </div>
    </div>
    <?php app_external_form_footer(); ?>
    <script src="<?= site_url('assets/plugins/jquery-steps/jquery.steps.js') ?>"></script>
    <script>
        var form_id = '#<?php echo $form['formkey']; ?>';
    </script>
</body>

</html>