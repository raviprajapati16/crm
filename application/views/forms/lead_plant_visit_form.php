<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$logo_url = base_url('uploads/company/' . get_option('company_logo'));
$showForm = false;
if (is_staff_logged_in() || is_admin()) {
    $showForm = true;
} else if ($form['active'] == "1") {
    $showForm = true;
}
$staffLoggedIn = (is_staff_logged_in() || is_admin()) ? true : false;
$requiredEnable = (is_staff_logged_in() || is_admin()) ? false : true;
$updatedBystaff = (is_numeric($form['updated_by'])) ? true : false;
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title>Customer Plant Visit Form</title>
    <?php app_external_form_header(); ?>
    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/jquery-steps/css/jquery.steps.css') ?>">
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
            color: #fff;
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

        .panel {
            border-color: #fff !important;
            border-width: 10px;
        }

        .panel-primary>.panel-heading {
            border-color: #fff !important;
            border-radius: 10px;
        }


        .partner-section .panel-heading {
            padding: 15px;
        }


        .panel-heading {
            padding: 25px;
        }

        .table-visit-charges {
            border: 2px solid #0D572E;
        }

        .table-visit-charges tr:first-child {
            font-weight: 500;
        }

        #signature {
            border-radius: 25px;
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

        .term-conditions-section {
            border: 1px solid black;
            border-radius: 20px;
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
    </style>
</head>

<body class="plant-visit-form">
    <div id="loading-spinner" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="dt-loader"><span></span></div>
        </div>
    </div>
    <div class="container form-container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div id="logo">
                    <?php get_company_logo('/') ?>
                </div>
            </div>
            <?php if ($showForm) { ?>
                <div class="col-md-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title text-center">Customer Plant Visit Form</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo form_open_multipart($this->uri->uri_string(), array('id' => $form['id'], 'class' => 'disable-on-submit')); ?>
                                    <?php echo form_hidden('id', $form['id']); ?>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => "Name Of Applicant",
                                            "id" => "name",
                                            "name" => "name",
                                            "type" => "text",
                                            "selected_value" => (isset($form['name']) && !empty($form['name'])) ? $form['name'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Professional Field",
                                            "id" => "professional_field",
                                            "name" => "professional_field",
                                            "type" => "text",
                                            "selected_value" => (isset($form['professional_field']) && !empty($form['professional_field'])) ? $form['professional_field'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Occupation",
                                            "id" => "occupation",
                                            "name" => "occupation",
                                            "type" => "text",
                                            "selected_value" => (isset($form['occupation']) && !empty($form['occupation'])) ? $form['occupation'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => " Email",
                                            "id" => "email",
                                            "name" => "email",
                                            "type" => "email",
                                            "selected_value" => (isset($form['email']) && !empty($form['email'])) ? $form['email'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Phone Number",
                                            "id" => "phone",
                                            "name" => "phone",
                                            "type" => "text",
                                            "selected_value" => (isset($form['phone']) && !empty($form['phone'])) ? $form['phone'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Age",
                                            "id" => "age",
                                            "name" => "age",
                                            "type" => "text",
                                            "className" => "age",
                                            "selected_value" => (isset($form['age']) && !empty($form['age'])) ? $form['age'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => "Aadhar Number",
                                            "id" => "aadhar_no",
                                            "name" => "aadhar_no",
                                            "type" => "number",
                                            "className" => "aadharno",
                                            "selected_value" => (isset($form['aadhar_no']) && !empty($form['aadhar_no'])) ? $form['aadhar_no'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "PAN Number",
                                            "id" => "pan_no",
                                            "name" => "pan_no",
                                            "type" => "text",
                                            "className" => "panno",
                                            "selected_value" => (isset($form['pan_no']) && !empty($form['pan_no'])) ? $form['pan_no'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?php
                                        $applicant_photo_url = NULL;
                                        if (!empty($form['photo'])) {
                                            $file_path = get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['photo'];
                                            if (file_exists($file_path)) {
                                                $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['photo']);
                                                $applicant_photo_url = site_url('download/file_download?path=' . $protected_path);
                                            }
                                        }
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Applicant's Passport Size Photo",
                                            "id" => "photo",
                                            "name" => "photo",
                                            "type" => "fileupload",
                                            "selected_value" => $form['photo'],
                                            "preview_url" => $applicant_photo_url,
                                            "file_delete_class" => "delete-doc-file",
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?php
                                        $applicant_aadhar_card_url = NULL;
                                        if (!empty($form['aadhar_card'])) {
                                            $file_path = get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['aadhar_card'];
                                            if (file_exists($file_path)) {
                                                $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['aadhar_card']);
                                                $applicant_aadhar_card_url = site_url('download/file_download?path=' . $protected_path);
                                            }
                                        }
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Applicant's Aadhar Card",
                                            "id" => "aadhar_card",
                                            "name" => "aadhar_card",
                                            "type" => "fileupload",
                                            "selected_value" => $form['aadhar_card'],
                                            "preview_url" => $applicant_aadhar_card_url,
                                            "file_delete_class" => "delete-doc-file",
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?php
                                        $applicant_pan_card_url = NULL;
                                        if (!empty($form['pan_card'])) {
                                            $file_path = get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['pan_card'];
                                            if (file_exists($file_path)) {
                                                $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['pan_card']);
                                                $applicant_pan_card_url = site_url('download/file_download?path=' . $protected_path);
                                            }
                                        }
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Applicant's PAN Card",
                                            "id" => "pan_card",
                                            "name" => "pan_card",
                                            "type" => "fileupload",
                                            "selected_value" => $form['pan_card'],
                                            "preview_url" => $applicant_pan_card_url,
                                            "file_delete_class" => "delete-doc-file",
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?php
                                        $applicant_signature_url = NULL;
                                        if (!empty($form['signature'])) {
                                            $file_path = get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['signature'];
                                            if (file_exists($file_path)) {
                                                $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form['lead_id'] . '/' . $form['signature']);
                                                $applicant_signature_url = site_url('download/file_download?path=' . $protected_path);
                                            }
                                        }
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Applicant's Signature",
                                            "id" => "signature",
                                            "name" => "signature",
                                            "type" => "fileupload",
                                            "selected_value" => $form['signature'],
                                            "preview_url" => $applicant_signature_url,
                                            "file_delete_class" => "delete-doc-file",
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => "Organization / Company",
                                            "id" => "organization",
                                            "name" => "organization",
                                            "type" => "text",
                                            "selected_value" => (isset($form['organization']) && !empty($form['organization'])) ? $form['organization'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Visit Purpose",
                                            "id" => "visit_purpose",
                                            "name" => "visit_purpose",
                                            "type" => "text",
                                            "is_required" => true,
                                            "selected_value" => (isset($form['visit_purpose']) && !empty($form['visit_purpose'])) ? $form['visit_purpose'] : '',
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <div class="col-md-4">
                                            <div class="select-placeholder form-group">
                                                <label for="visitor_type">Visitor Type <span class="text-danger">*</span></label>
                                                <select name="visitor_type" id="visitor_type" class="selectpicker" data-width="100%" data-none-selected-text="Select Day" data-hide-disabled="true">
                                                    <option value=""></option>
                                                    <?php if (!empty($visitor_types_data)) {
                                                        foreach ($visitor_types_data as $key => $item) {
                                                            if ($item['active'] == "1" || $form['visitor_type'] == $item['id']) {
                                                                if ($form['visitor_type'] == $item['id']) {
                                                                    $item['allowed_members'] = $form['max_allowed_members'];
                                                                    $item['charge_type'] = $form['charge_type'];
                                                                    $item['amount'] = $form['visit_amount'];
                                                                    $item['free_visit_allowed'] = $form['is_free_visit'];
                                                                    $item['free_visit_day'] = $form['free_visit_day'];
                                                                }
                                                    ?>
                                                                <option
                                                                    data-max-allowed-members="<?= $item['allowed_members'] ?>"
                                                                    data-charge-type="<?= $item['charge_type'] ?>"
                                                                    data-amount="<?= $item['amount'] ?>"
                                                                    data-free-visit-allowed="<?= $item['free_visit_allowed'] ?>"
                                                                    data-free-visit-day="<?= $item['free_visit_day'] ?>"
                                                                    value="<?= $item['id'] ?>"
                                                                    <?= ($form['visitor_type'] == $item['id']) ? "selected" : "" ?>><?= $item['title'] ?></option>
                                                    <?php
                                                            }
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="plant_visit" class="control-label">Product <span class="text-danger">*</span></label>
                                                <select name="plant_visit" id="plant_visit" class="selectpicker" data-width="100%" data-none-selected-text="Select Option" <?= ($requiredEnable) ? "required" : ""  ?>>
                                                    <option value=""></option>
                                                    <?php
                                                    foreach ($product_type as $key => $item) {
                                                    ?>
                                                        <option value="<?php echo $item['id'] ?>" <?= (isset($form['plant_visit']) && $form['plant_visit'] == $item['id']) ? 'selected' : '' ?>><?php echo $item['name']; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?= all_type_input_render([
                                            "label" => "Expected Visit Date & Time",
                                            "id" => "visit_date_time",
                                            "name" => "visit_date_time",
                                            "type" => "date_picker_time",
                                            "selected_value" => (isset($form['visit_date_time']) && !empty($form['visit_date_time'])) ? date('d-m-Y H:i', strtotime($form['visit_date_time'])) : '',
                                            "is_required" => true,
                                            "instructions" => "<small>* Visit date & time will be change as per availability.</small>",
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Any special requests for needs for the visit",
                                            "id" => "special_request",
                                            "name" => "special_request",
                                            "type" => "text",
                                            "selected_value" => (isset($form['special_request']) && !empty($form['special_request'])) ? $form['special_request'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => "Please provide any additional information that may be relevant to the visit",
                                            "id" => "additional_info",
                                            "name" => "additional_info",
                                            "type" => "textarea",
                                            "rows" => 4,
                                            "is_required" => true,
                                            "selected_value" => (isset($form['additional_info']) && !empty($form['additional_info'])) ? $form['additional_info'] : '',
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <button type="button" id="add-member" class="btn btn-primary mt-3">Add New Group Member <i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                    <div class="row dynamic-section">

                                    </div>
                                    <div class="row mtop20">
                                        <div class="col-md-12 dynamic-lead-inquiry-form-section">
                                        </div>
                                    </div>
                                    <div class="row mtop20">
                                        <div class="col-md-12"><b>Terms & Conditions :</b></div>
                                        <?php if (is_staff_logged_in() || is_admin()) { ?>
                                            <div class="col-md-12 mtop5">
                                                <textarea name="terms_and_conditions" id="terms_and_conditions_editor" class="texteditor">
                                                    <?= (isset($form['terms_and_conditions']) && !empty($form['terms_and_conditions'])) ? $form['terms_and_conditions'] : '' ?>
                                                </textarea>
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-md-12 mtop5">
                                                <iframe class="term-conditions-section" style="width: 100%; height: 40vh;" srcdoc="<?= htmlspecialchars($form['terms_and_conditions'], ENT_QUOTES) ?>"></iframe>
                                            </div>
                                            <div class="col-md-12 mtop5">
                                                <div class="form-group">
                                                    <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required>
                                                    <label for="agree_terms">
                                                        I agree to the terms and conditions
                                                    </label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row mtop20">
                                        <?php if (is_staff_logged_in() || is_admin()) { ?>
                                            <div class="col-md-4">
                                                <div class="select-placeholder form-group">
                                                    <label for="tax_type">Tax <span class="text-danger">*</span></label>
                                                    <select name="tax_type" id="tax_type" class="selectpicker" data-width="100%" data-none-selected-text="Select Tax" data-hide-disabled="true">
                                                        <option value=""></option>
                                                        <?php if (!empty($taxes_data)) {
                                                            foreach ($taxes_data as $key => $item) {
                                                                $selected = "";
                                                                if (!empty($form['tax_rate'])) {
                                                                    if ($form['tax_rate'] == $item['taxrate'] && $form['tax_name'] == $item['name']) {
                                                                        $selected = "selected";
                                                                    }
                                                                } else if ($item['taxrate'] == 18 && $item['taxrate'] == "GST") {
                                                                    $selected = "selected";
                                                                }
                                                        ?>
                                                                <option
                                                                    data-name="<?= $item['name'] ?>"
                                                                    data-taxrate="<?= $item['taxrate'] ?>"
                                                                    value="<?= $item['id'] ?>" <?= $selected ?>><?= $item['name'] ?> <?= $item['taxrate'] ?></option>
                                                        <?php
                                                            }
                                                        } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="clearfix"></div>
                                        <div class="col-md-6">
                                            <table class="table table-visit-charges">
                                                <tr>
                                                    <td colspan="2" class="text-center">Visit Charges</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Visitor Type</strong></td>
                                                    <td class="visitor-type-text"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Visit Date & Time </strong></td>
                                                    <td class="visit-date-time"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Members</strong></td>
                                                    <td class="visitor-total-members"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Visit Charge</strong></td>
                                                    <td class="visitor-charge"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Amount </strong></td>
                                                    <td class="visitor-total-amount"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tax<span class="tax-name"></span></strong></td>
                                                    <td class="tax-amount"></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total Payable Amount </strong></td>
                                                    <td class="visitor-total-payable-amount"></td>
                                                </tr>
                                                <input type="hidden" name="tax_name" value="<?= (isset($form['tax_name']) && !empty($form['tax_name'])) ? $form['tax_name'] : ''  ?>" />
                                                <input type="hidden" name="tax_rate" value="<?= (isset($form['tax_rate']) && !empty($form['tax_rate'])) ? $form['tax_rate'] : ''  ?>" />
                                                <input type="hidden" name="visit_amount" value="<?= (isset($form['visit_amount']) && !empty($form['visit_amount'])) ? $form['visit_amount'] : ''  ?>" />
                                                <input type="hidden" name="charge_type" value="<?= (isset($form['charge_type']) && !empty($form['charge_type'])) ? $form['charge_type'] : ''  ?>" />
                                                <input type="hidden" name="tax_amount" value="<?= (isset($form['tax_amount']) && !empty($form['tax_amount'])) ? $form['tax_amount'] : ''  ?>" />
                                                <input type="hidden" name="total_amount" value="<?= (isset($form['total_amount']) && !empty($form['total_amount'])) ? $form['total_amount'] : ''  ?>" />
                                                <input type="hidden" name="total_pay_amount" value="<?= (isset($form['total_pay_amount']) && !empty($form['total_pay_amount'])) ? $form['total_pay_amount'] : ''  ?>" />
                                                <input type="hidden" name="max_allowed_members" value="<?= (isset($form['max_allowed_members']) && !empty($form['max_allowed_members'])) ? $form['max_allowed_members'] : ''  ?>" />
                                                <input type="hidden" name="total_members" value="<?= (isset($form['total_members']) && !empty($form['total_members'])) ? $form['total_members'] : ''  ?>" />
                                                <input type="hidden" name="is_free_visit" value="<?= (isset($form['is_free_visit']) && !empty($form['is_free_visit'])) ? $form['is_free_visit'] : ''  ?>" />
                                                <input type="hidden" name="free_visit_day" value="<?= (isset($form['free_visit_day']) && !empty($form['free_visit_day'])) ? $form['free_visit_day'] : ''  ?>" />
                                            </table>
                                        </div>
                                        <?php if (!$staffLoggedIn) { ?>
                                            <div class="col-md-6" style="text-align: right;">
                                                <p class="bold" id="signatureLabel">Applicant Signature</p>
                                                <div class="signature-pad--body">
                                                    <canvas id="signature" height="130"></canvas>
                                                    <div class="dispay-block">
                                                        <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear"><?php echo _l('clear'); ?></button>
                                                        <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo"><?php echo _l('undo'); ?></button>
                                                    </div>
                                                </div>
                                                <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" name="digital_signature" id="signatureInput">
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row mtop20">
                                        <div class="col-md-12 text-center">
                                            <button type="submit" class="btn btn-info"><?= (is_staff_logged_in() || is_admin()) ? "Save" : "Submit" ?> <i class="fa fa-check-circle" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="col-md-12">
                    <?php
                    if ($form['approval_staus'] == "Pending") {
                    ?>
                        <div class="col-md-8 col-md-offset-2">
                            <div class="alert alert-success text-center">
                                Thank you! Your form has been successfully submitted and is currently under review. Once the review process is complete, you will be notified via Email or WhatsApp. </div>
                        </div>
                    <?php
                    } else if ($form['approval_staus'] == "Approved") {
                    ?>
                        <div class="col-md-8 col-md-offset-2">
                            <div class="alert alert-success text-center">
                                Thank you! Your form has been successfully submitted and apporval process has been completed.
                            </div>
                        </div>
                    <?php
                    } else { ?>
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
                                            <b>Form ID : </b> #<?= leadFormIdRender("PVF", $form['lead_id'], $form['id']) ?>
                                        </div>
                                        <div class="col-md-12 mtop20">
                                            <b>Please contact us on below details with above Form ID.</b>
                                        </div>
                                        <div class="col-md-12 mtop20">
                                            <?php
                                            $staffData = get_staff($form['created_by']);
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
            <?php } ?>
        </div>
    </div>
    <?php app_external_form_footer(); ?>
    <script src="<?= site_url('assets/plugins/jquery-input-mask/jquery.inputmask.bundle.min.js'); ?>"></script>
    <script type="text/javascript" id="tinymce-js" src="<?= site_url('assets/plugins/tinymce/tinymce.min.js') ?>"></script>
    <script src="<?= site_url('assets/plugins/jquery-steps/jquery.steps.js') ?>"></script>
    <script src="<?= site_url('assets/plugins/signature-pad/signature_pad.min.js') ?>"></script>
    <script>
        var id = '<?php echo $form['id']; ?>';
        var partnerIndex = <?= (isset($member_data) && !empty($member_data)) ? count($member_data) : 0 ?>;
        var partnerData = <?= (isset($member_data) && !empty($member_data)) ? json_encode($member_data) : [] ?>;
        var requireEnable = "<?= (!empty($requiredEnable) && $requiredEnable) ? true : false ?>";
        var relationTypes = <?= (isset($relation_types_data) && !empty($relation_types_data)) ? json_encode($relation_types_data) : [] ?>;

        var stepwizard;
        $(function() {
            fileuploadrevalidate();
            priceCalculation();
            SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
                var canvas = this._ctx.canvas;
                // First duplicate the canvas to not alter the original
                var croppedCanvas = document.createElement('canvas'),
                    croppedCtx = croppedCanvas.getContext('2d');

                croppedCanvas.width = canvas.width;
                croppedCanvas.height = canvas.height;
                croppedCtx.drawImage(canvas, 0, 0);

                // Next do the actual cropping
                var w = croppedCanvas.width,
                    h = croppedCanvas.height,
                    pix = {
                        x: [],
                        y: []
                    },
                    imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
                    x, y, index;

                for (y = 0; y < h; y++) {
                    for (x = 0; x < w; x++) {
                        index = (y * w + x) * 4;
                        if (imageData.data[index + 3] > 0) {
                            pix.x.push(x);
                            pix.y.push(y);

                        }
                    }
                }
                pix.x.sort(function(a, b) {
                    return a - b
                });
                pix.y.sort(function(a, b) {
                    return a - b
                });
                var n = pix.x.length - 1;

                w = pix.x[n] - pix.x[0];
                h = pix.y[n] - pix.y[0];
                var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

                croppedCanvas.width = w;
                croppedCanvas.height = h;
                croppedCtx.putImageData(cut, 0, 0);

                return croppedCanvas.toDataURL();
            };

            function signaturePadChanged() {
                var input = document.getElementById('signatureInput');
                var $signatureLabel = $('#signatureLabel');
                $signatureLabel.removeClass('text-danger');
                $('#signatureInput-error').remove();
                if (signaturePad.isEmpty()) {
                    $signatureLabel.addClass('text-danger');
                    $('#signature').after("<div id='signatureInput-error' class='error'>Signature Required</div>");
                    input.value = '';
                    return false;
                }
                var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
                partBase64 = partBase64.split(',')[1];
                input.value = partBase64;
                return true;
            }

            if ($('#signature').length > 0) {
                var canvas = document.getElementById("signature");
                var clearButton = document.querySelector("[data-action=clear]");
                var undoButton = document.querySelector("[data-action=undo]");
                var signaturePad = new SignaturePad(canvas, {
                    maxWidth: 2,
                    onEnd: function() {
                        signaturePadChanged();
                    }
                });

                clearButton.addEventListener("click", function(event) {
                    signaturePad.clear();
                    signaturePadChanged();
                });

                undoButton.addEventListener("click", function(event) {
                    var data = signaturePad.toData();
                    if (data) {
                        data.pop();
                        signaturePad.fromData(data);
                        signaturePadChanged();
                    }
                });
            }

            tinyMceEditor('.texteditor', 400);
            if (partnerIndex != 0) {
                var totalRecods = partnerIndex;
                if (totalRecods > 0) {
                    partnerIndex = 0;
                    for (var i = 0; i < totalRecods; i++) {
                        addMemberSection();
                        $('#id-' + i).val(partnerData[i].id);
                        $('#name-' + i).val(partnerData[i].name);
                        $('#contact_no-' + i).val(partnerData[i].contact_no);
                        $('#occupation-' + i).val(partnerData[i].occupation);
                        $('#professional_field-' + i).val(partnerData[i].professional_field);
                        $('#age-' + i).val(partnerData[i].age);
                        $('#aadhar_no-' + i).val(partnerData[i].aadhar_no);
                        $('#pan-' + i).val(partnerData[i].pan_no);
                        $('#email-' + i).val(partnerData[i].email);

                        let relationSelectedValue = partnerData[i].relation_with_applicant;
                        let $relationDropdown = $('#relation-' + i);
                        if (relationSelectedValue != "" && relationSelectedValue != null) {
                            if ($relationDropdown.find(`option[value="${relationSelectedValue}"]`).length > 0) {
                                $relationDropdown.val(relationSelectedValue).selectpicker('refresh');
                            } else {
                                partnerData[i].other_relation = relationSelectedValue;
                                partnerData[i].relation_with_applicant = "Other";
                                $relationDropdown.val("Other").selectpicker('refresh');
                            }
                        }

                        if (partnerData[i].photo_preview) {
                            var html = `<span class="file-preview-section"><i class="fa fa-paperclip" aria-hidden="true"></i>
                                <a href="${partnerData[i].photo_preview}" class="preview-file" target="_blank">${partnerData[i].photo}</a>
                                <i class="fa fa-trash delete-doc-file text-danger" data-id="${partnerData[i].id}" aria-hidden="true"></i>
                            </span>`;
                            $('#photo-' + i).closest('.form-group').append(html);
                        }

                        if (partnerData[i].aadhar_card != "" && partnerData[i].aadhar_card != null) {
                            var html = `<span class="file-preview-section"><i class="fa fa-paperclip" aria-hidden="true"></i>
                                <a href="${partnerData[i].aadhar_card_preview}" class="preview-file" target="_blank">${partnerData[i].aadhar_card}</a>
                                <i class="fa fa-trash delete-doc-file text-danger" data-id="${partnerData[i].id}" aria-hidden="true"></i>
                            </span>`;
                            $('#aadhar_card-' + i).closest('.form-group').append(html);
                        }

                        if (partnerData[i].pan_card != "" && partnerData[i].pan_card != null) {
                            var html = `<span class="file-preview-section"><i class="fa fa-paperclip" aria-hidden="true"></i>
                                <a href="${partnerData[i].pan_card_preview}" class="preview-file" target="_blank">${partnerData[i].pan_card}</a>
                                <i class="fa fa-trash delete-doc-file text-danger" data-id="${partnerData[i].id}" aria-hidden="true"></i>
                            </span>`;
                            $('#pan_card-' + i).closest('.form-group').append(html);
                        }

                        if (partnerData[i].signature != "" && partnerData[i].signature != null) {
                            var html = `<span class="file-preview-section"><i class="fa fa-paperclip" aria-hidden="true"></i>
                                <a href="${partnerData[i].signature_preview}" class="preview-file" target="_blank">${partnerData[i].signature}</a>
                                <i class="fa fa-trash delete-doc-file text-danger" data-id="${partnerData[i].id}" aria-hidden="true"></i>
                            </span>`;
                            $('#signature-' + i).closest('.form-group').append(html);
                        }

                        if (partnerData[i].relation_with_applicant == "Other") {
                            $('#other-relation-' + i).val(partnerData[i].other_relation);
                            $('#other-relation-' + i).prop("required", true);
                            $('#other-relation-' + i).closest('.other-relation-section').removeClass('hide');
                        } else {
                            $('#other-relation-' + i).prop("required", false);
                            $('#other-relation-' + i).val("");
                            $('#other-relation-' + i).closest('.other-relation-section').addClass('hide');
                        }
                        fileuploadrevalidate();
                    }
                }

            }
            $(".duration-picker").inputmask("99:99", {
                placeholder: "HH:MM",
                insertMode: false,
                showMaskOnHover: false,
                hourFormat: "24"
            });


            $('.render-input-disabled').on('keydown', false);

            var validator = $('form').validate({
                ignore: [],
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    var inputType = $(element).attr('type')
                    var formGroup = $(element).closest('.form-group');
                    formGroup.find('span.error').remove();
                    var inputName = element.attr("name");
                    if (inputType == "checkbox" && inputName == "agree_terms") {
                        error.text("You must agree to the terms and conditions.");
                        $(formGroup).append(error);
                    } else {
                        $(formGroup).append(error);
                    }
                },
                submitHandler: function(form) {
                    if (requireEnable) {
                        if (signaturePadChanged()) {
                            form.submit();
                        }
                    } else {
                        form.submit();
                    }
                },
                invalidHandler: function(event, validator) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    var $stepSection = $(firstInvalidElement).closest('section');
                    if ($stepSection.length > 0) {
                        var id = $stepSection.attr("id");
                        var stepIndex = Number(id.match(/\d+/)[0]);
                        goToStep(stepIndex)
                    }
                }
            });

            function goToStep(targetStep) {
                var currentIndex = stepwizard.steps("getCurrentIndex");
                while (currentIndex !== targetStep) {
                    if (currentIndex < targetStep) {
                        stepwizard.steps("next");
                        currentIndex++;
                    } else {
                        stepwizard.steps("previous");
                        currentIndex--;
                    }
                }
            }


            var sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
            var day = String(sevenDaysAgo.getDate()).padStart(2, '0');
            var month = String(sevenDaysAgo.getMonth() + 1).padStart(2, '0');
            var year = sevenDaysAgo.getFullYear();
            var min_date = year + '-' + month + '-' + day;

            $('.datetimepicker').datetimepicker({
                format: 'd-m-Y H:i',
                beforeShowDay: function(date) {
                    var day = date.getDay();
                    if (day === 0) {
                        return [false, "", "Unavailable"];
                    }
                    return [true];
                },
                minDate: min_date,
                maxDate: false
            });


            $('select').selectpicker();

            $('select').on('change', function() {
                $(this).closest('.form-group').find('span.error').remove();
            });

            $('#add-member').on('click', function() {
                addMemberSection();
            });

            var previousVisitorType = $('#visitor_type :selected').val();
            $(document).on('change', '#visitor_type', function() {
                var currentVisitorType = $('#visitor_type :selected');
                var count = $('.partner-section').length;
                if (count > 0) {
                    if (currentVisitorType.val() == "" || currentVisitorType.val() == null) {
                        if (confirm("Are you sure you want to change visitor type ? all member data except applicant data will be discarded") == true) {
                            $('.dynamic-section .partner-section').remove();
                        } else {
                            $('#visitor_type').val(previousVisitorType).selectpicker('refresh');
                            return;
                        }
                    } else {
                        var maxAllowed = Number(currentVisitorType.attr('data-max-allowed-members'));
                        if (count > maxAllowed) {
                            if (confirm("For " + currentVisitorType.text() + " visitor type, only " + maxAllowed + " members are allowed, so all extra members details will be discarded. Are you sure you want to perform this action?") == true) {
                                $('.dynamic-section .partner-section')
                                    .slice(maxAllowed - 1)
                                    .remove();
                            } else {
                                $('#visitor_type').val(previousVisitorType).selectpicker('refresh');
                                return;
                            }
                        }
                    }
                }
                previousVisitorType = currentVisitorType.val();
                setHiddentFields();
            });

            $(document).on('click', '.remove-partner', function() {
                var index = $(this).data('index');
                removePartnerSection(index);
            });

            $(document).on('input', '.age', function() {
                if (this.value.length >= 2) this.value = this.value.slice(0, 2);
            });

            $(document).on('input', '.aadharno', function() {
                if (this.value.length >= 12) this.value = this.value.slice(0, 12);
            });

            $(document).on('input', '.panno', function() {
                if (this.value.length >= 10) this.value = this.value.slice(0, 10);
            });

            $(document).on('click', '.delete-new-file', function() {
                var formgroup = $(this).closest('.form-group');
                if (confirm("Are you sure you want perform this action?")) {
                    formgroup.find('.file-preview-section').remove();
                    formgroup.find('input[type="file"]').val('');
                    fileUploadRequiredValidationCheck(formgroup);
                    fileuploadrevalidate();
                }
            });

            $(document).on('change', 'input[type="file"]', function() {
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
                                <i class="fa fa-paperclip" aria-hidden="true"></i>  <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                                <i class='fa fa-trash text-danger delete-new-file' aria-hidden='true'></i>
                            </span>
                        `;
                    formgroup.append(previewHtml);
                }
                fileuploadrevalidate();
            });

            $('select').each(function() {
                if ($(this).attr('data-read-only') == "1") {
                    $(this).siblings('.dropdown-toggle').addClass('disable-select');
                }
            });

            $(document).on('change', 'select', function() {
                $(this).closest('.form-group').find('span.error').remove();
                $(this).closest('.form-group').removeClass('has-error');
            });

            $(document).on('input', 'input, textarea', function() {
                $(this).removeClass('error');
                $(this).closest('.form-group').find('span.error').remove();
                $(this).closest('.form-group').removeClass('has-error');
            });
            $(document).on('change', '#fieldvisit_date_time', function() {
                setHiddentFields();
            });

            $(document).on('change', '#tax_type', function() {
                setHiddentFields();
            });

            $(document).on('click', '.delete-doc-file', function() {
                var formgroup = $(this).closest('.form-group');
                if (confirm("Are you sure you want to delete this file?")) {
                    $.ajax({
                        url: "<?php echo site_url('forms/delete_pvf_file') ?>",
                        method: "POST",
                        data: {
                            id: "<?= $form['id'] ?>",
                            key: $(this).closest('.form-group').attr('data-name'),
                            member_id: ($(this).attr('data-id')) ? $(this).attr('data-id') : ""
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            formgroup.find('.file-preview-section').remove();
                            fileuploadrevalidate();
                        }
                    });
                }
            });

        });

        function step_form_init() {
            var form = $('form');
            stepwizard = $("#wizard").steps({
                headerTag: "h2",
                bodyTag: "section",
                transitionEffect: "slideLeft",
                onInit: function(event, currentIndex) {
                    $('select').selectpicker();
                },
                onStepChanging: function(event, currentIndex, newIndex) {
                    if (currentIndex > newIndex) {
                        return true;
                    }
                    var currentStep = $(this).find(".body:eq(" + currentIndex + ")");
                    return validateStep(currentStep);
                },
                onFinishing: function(event, currentIndex) {
                    var currentStep = $(this).find(".body:eq(" + currentIndex + ")");
                    return validateStep(currentStep);
                },
                onFinished: function(event, currentIndex) {
                    window.scrollTo(0, document.body.scrollHeight);
                }
            });
        }


        function validateStep(step) {
            var isValid = true;
            step.find('input[required], select[required], textarea[required]').each(function() {
                var field = $(this);
                var formGroup = field.closest('.form-group');
                if (!field.val()) {
                    isValid = false;
                    formGroup.addClass('has-error');
                    if (formGroup.find('.error').length === 0) {
                        formGroup.append('<span class="error">This field is required.</span>');
                    }
                } else {
                    formGroup.removeClass('has-error');
                    formGroup.find('.error').remove();
                }
            });
            return isValid;
        }

        function relation_change(element) {
            var parent = $(element).closest('.partner-section');
            if (parent.length > 0) {
                if ($(element).val() == "Other") {
                    parent.find('.other-relation-section').removeClass('hide');
                    parent.find('.other_relation').prop('required', true);
                } else {
                    parent.find('.other-relation-section').addClass('hide');
                    parent.find('.other_relation').prop('required', false);
                }
            }
        }


        function createMemberSection(index) {
            let trashButton = `
                <button type="button" class="btn btn-danger btn-sm remove-partner pull-right" data-index="${index}">
                    <i class="fa fa-trash"></i>
                </button>
            `;
            var relationOptions = '';
            $(relationTypes).each(function(index, item) {
                if (item.active == "1") {
                    relationOptions += `<option value="${item.title}">${item.title}</option>`;
                }
            });
            return `
                <div class="col-md-12 mb-3">
                    <div class="partner-section" id="partner-${index}">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">
                                    <span class="member-title">Member ${index + 2} Details</span>
                                    ${trashButton}
                                </h3>
                            </div>
                            <div class="panel-body">
                                <input type="hidden" id="id-${index}" name="dynamic_data[${index}][id]" value = "" />
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name-${index}">Name <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="name-${index}" name="dynamic_data[${index}][name]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="professional_field-${index}">Professional Field <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="professional_field-${index}" name="dynamic_data[${index}][professional_field]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="occupation-${index}">Occupation <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="occupation-${index}" name="dynamic_data[${index}][occupation]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email-${index}">Email <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="email-${index}" name="dynamic_data[${index}][email]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contact_no-${index}">Phone Number <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="contact_no-${index}" name="dynamic_data[${index}][contact_no]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="age-${index}">Age <span class="text-danger">* </span></label>
                                            <input type="number" class="form-control age" id="age-${index}" name="dynamic_data[${index}][age]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aadhar_no-${index}">Aadhar Number <span class="text-danger">* </span></label>
                                            <input type="number" class="form-control aadharno" id="aadhar_no-${index}" name="dynamic_data[${index}][aadhar_no]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pan-${index}">PAN Number <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control panno" id="pan-${index}" name="dynamic_data[${index}][pan_no]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="relation-${index}" class="control-label">Relation with applicant <span class="text-danger">*</span></label>
                                            <select id="relation-${index}" name="dynamic_data[${index}][relation_with_applicant]" class="relation-dropdown" data-width="100%" data-live-search="true" data-none-selected-text="Select Option" onchange="relation_change(this)" ${ (requireEnable) ? "required" : "" }>
                                                <option value="" disabled selected>Select relation</option>
                                                ${relationOptions}
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4 other-relation-section hide">
                                        <div class="form-group">
                                            <label for="other-relation-${index}">Other Relation Type <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control other_relation" id="other-relation-${index}" name="dynamic_data[${index}][other_relation]">
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <div class="form-group" data-type="fileupload" data-name="photo">
                                            <div class="control-label">Passport Size Photo <span class="text-danger">* </span> </div>
                                            <input type="file" class="form-control " id="photo-${index}" name="dynamic_data[photo][${index}]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" data-type="fileupload" data-name="aadhar_card">
                                            <div class="control-label">Aadhar Card <span class="text-danger">* </span> </div>
                                            <input type="file" class="form-control " id="aadhar_card-${index}" name="dynamic_data[aadhar_card][${index}]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" data-type="fileupload" data-name="pan_card">
                                            <div class="control-label">Pan Card <span class="text-danger">* </span> </div>
                                            <input type="file" class="form-control " id="pan_card-${index}" name="dynamic_data[pan_card][${index}]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4">
                                        <div class="form-group" data-type="fileupload" data-name="signature">
                                            <div class="control-label">Signature <span class="text-danger">* </span> </div>
                                            <input type="file" class="form-control " id="signature-${index}" name="dynamic_data[signature][${index}]" ${ (requireEnable) ? "required" : "" }>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function addMemberSection() {
            partnerIndex = $('.partner-section').length;
            var visitorType = $('#visitor_type :selected');
            if (visitorType.length > 0) {
                if (visitorType.val() != "" && visitorType.val() != null) {
                    var count = Number($('.partner-section').length) + 1;
                    var maxallowedMembers = Number($('input[name="max_allowed_members"]').val())
                    if (count >= maxallowedMembers) {
                        alert("Sorry! For " + visitorType.text() + " visitor type only " + maxallowedMembers + " members are allowed.");
                        return false;
                    }
                    $('.dynamic-section').append(createMemberSection(partnerIndex));
                    $('select').selectpicker('refresh');
                    partnerIndex++;
                    dynamicMemberTitle();
                } else {
                    alert("Please select visitor type first to add new member.")
                }
            }
        }

        function removePartnerSection(index) {
            $(`#partner-${index}`).remove();
            partnerIndex = $('.partner-section').length;
            dynamicMemberTitle()
        }

        function dynamicMemberTitle() {
            var count = 2;
            $(".partner-section").each(function() {
                $(this).find('.member-title').html("Member " + count + " Details");
                count++;
            });
            priceCalculation();
        }


        function setHiddentFields() {
            var taxElement = $('#tax_type :selected');
            if (taxElement.length > 0) {
                $('input[name="tax_name"]').val(taxElement.attr('data-name'));
                $('input[name="tax_rate"]').val(Number(taxElement.attr('data-taxrate')));
            }
            var visitor_type = $('#visitor_type :selected');
            if (visitor_type.length > 0) {
                $('input[name="visit_amount"]').val(visitor_type.attr('data-amount'));
                $('input[name="max_allowed_members"]').val(visitor_type.attr('data-max-allowed-members'));
                $('input[name="is_free_visit"]').val(visitor_type.attr('data-free-visit-allowed'));
                $('input[name="free_visit_day"]').val(visitor_type.attr('data-free-visit-day'));
                $('input[name="charge_type"]').val(visitor_type.attr('data-charge-type'));
            }
            priceCalculation();
        }



        function priceCalculation() {
            var members = Number($(".partner-section").length) + 1;
            var is_free_visit = $('input[name="is_free_visit"]').val();
            var free_visit_day = $('input[name="free_visit_day"]').val();
            var visit_amount = $('input[name="visit_amount"]').val();
            var charge_type = $('input[name="charge_type"]').val();
            var tax_rate = $('input[name="tax_rate"]').val();
            var tax_name = $('input[name="tax_name"]').val();
            var visit_date = $('#fieldvisit_date_time').val();
            var visitor_type = $('#visitor_type :selected');
            if (visitor_type.val() != "" && visitor_type.val() != null) {
                var extraAmountTxt = "";
                var total_amount = 0;
                var visitcharge_txt = "";
                if (charge_type == "fixed") {
                    total_amount = Number(visit_amount);
                    visitcharge_txt = "Rs." + formatToIndianCurrency(visit_amount) + "/- (Fixed Charge)";
                } else {
                    total_amount = Number(visit_amount) * Number(members);
                    visitcharge_txt = "Rs." + formatToIndianCurrency(visit_amount) + "/- (Per Person)";
                }

                if ($('#fieldvisit_date_time').val() != null && $('#fieldvisit_date_time').val() != "") {
                    $('.visit-date-time').html($('#fieldvisit_date_time').val() + " <br><small class='text-danger'>* Visit date & time will be change as per availability.</small>");
                    if (getDayName($('#fieldvisit_date_time').val()) == free_visit_day && is_free_visit == "1") {
                        extraAmountTxt = "(* Free Visit on " + free_visit_day + " For " + visitor_type.text() + ")";
                        total_amount = 0;
                    }
                }

                $('input[name="total_amount"]').val(total_amount);
                var tax_amount = 0;
                var total_pay_amount = 0;
                if (total_amount != 0) {
                    tax_amount = (Number(total_amount) * Number(tax_rate)) / 100;
                    total_pay_amount = Number(total_amount) + Number(tax_amount);
                }
                $('input[name="tax_amount"]').val(tax_amount);
                $('input[name="total_pay_amount"]').val(total_pay_amount);
                $('input[name="total_members"]').val(members);
                $('.tax-name').html(" (" + tax_name + " " + tax_rate + "%)");
                $('.visitor-type-text').html(visitor_type.text());
                $('.visitor-total-members').html(members);
                $('.visitor-charge').html(visitcharge_txt);
                $('.visitor-total-amount').html("Rs." + formatToIndianCurrency(total_amount) + "/- ");
                $('.tax-amount').html("Rs." + formatToIndianCurrency(tax_amount) + "/- ");
                $('.visitor-total-payable-amount').html("Rs." + formatToIndianCurrency(total_pay_amount) + "/- " + extraAmountTxt);
            } else {
                $('.visit-date-time').html("");
                $('.visitor-type-text').html("");
                $('.visitor-total-members').html("");
                $('.visitor-charge').html("");
                $('.visitor-total-amount').html("");
                $('.tax-amount').html("");
                $('.visitor-total-payable-amount').html("");
            }
        }

        function formatToIndianCurrency(amount) {
            let [integerPart, decimalPart] = amount.toString().split('.');
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            integerPart = integerPart.replace(/(\d+)(,)(\d{2},)/, "$1,$3");
            return decimalPart ? `${integerPart}.${decimalPart}` : integerPart;
        }

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

        function validateFileInput(fileInput) {
            var type = $(fileInput).closest('.form-group').attr('data-name');
            if (type == "photo" || type == "signature") {
                var maxSize = 1 * 1024 * 1024;
                var allowedExtensions = ["jpg", "jpeg", "png"];
            } else {
                var maxSize = 2 * 1024 * 1024;
                var allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
            }
            var isValid = true;
            var errors = [];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                var fileSize = file.size;
                var fileName = file.name;
                var fileExtension = fileName.split('.').pop().toLowerCase();

                if (fileSize > maxSize) {
                    isValid = false;
                    errors.push("File size exceeds. Maximum file size allowed up to " + (maxSize / 1024 / 1024) + " MB");
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

        function fileuploadrevalidate() {
            $('input[type="file"]').each(function() {
                var formgroup = $(this).closest('.form-group');
                var is_required = formgroup.attr('data-required');
                var fileuploaded = formgroup.find('.file-preview-section').length;
                if (fileuploaded > 0) {
                    $(this).removeAttr("required");
                    formgroup.removeAttr('data-required');
                } else if (requireEnable) {
                    $(this).prop("required", true);
                    formgroup.attr('data-required', 1);
                }
            });
        }

        function getDayName(dateString) {
            const [datePart, timePart] = dateString.split(' ');
            const [day, month, year] = datePart.split('-');
            const date = new Date(year, month - 1, day);
            return date.toLocaleDateString('en-US', {
                weekday: 'long'
            });
        }
    </script>
    <?php
    $company_logo = base_url('uploads/company/' . get_option('company_logo'));
    ?>
</body>

</html>