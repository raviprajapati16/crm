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
$disable_visit_purpose = (!empty($form['visit_purpose']) && $updatedBystaff && !$staffLoggedIn) ? '1' : '0';
$disable_product = (!empty($form['main_group_id']) && $updatedBystaff && !$staffLoggedIn) ? '1' : '0';
$disable_product_type = (!empty($form['sub_group_id']) && $updatedBystaff && !$staffLoggedIn) ? '1' : '0';
$disable_service_type = (!empty($form['service_type']) && $updatedBystaff && !$staffLoggedIn) ? '1' : '0';
$disable_other_service = (!empty($form['other_service_type']) && $updatedBystaff && !$staffLoggedIn) ? '1' : '0';
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title>Office Visitor Form</title>
    <?php app_external_form_header(); ?>
    <link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/jquery-steps/css/jquery.steps.css') ?>">
    <?php
    theme_style_clients_area_head();
    ?>
    <style>

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

<body class="office-visit-form">
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
                            <h3 class="panel-title text-center">Customer Office Visit Form</h3>
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
                                        <?= all_type_input_render([
                                            "label" => "Organization / Company",
                                            "id" => "organization",
                                            "name" => "organization",
                                            "type" => "text",
                                            "selected_value" => (isset($form['organization']) && !empty($form['organization'])) ? $form['organization'] : '',
                                            "is_required" => true,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <div class="row">
                                        <?= all_type_input_render([
                                            "label" => "Any special requests for needs for the visit",
                                            "id" => "special_request",
                                            "name" => "special_request",
                                            "type" => "text",
                                            "selected_value" => (isset($form['special_request']) && !empty($form['special_request'])) ? $form['special_request'] : '',
                                            "is_required" => false,
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                        <?= all_type_input_render([
                                            "label" => "Please provide any additional information that may be relevant to the visit",
                                            "id" => "additional_info",
                                            "name" => "additional_info",
                                            "type" => "textarea",
                                            "rows" => 4,
                                            "is_required" => false,
                                            "selected_value" => (isset($form['additional_info']) && !empty($form['additional_info'])) ? $form['additional_info'] : '',
                                        ], 'col-md-4', $requiredEnable);
                                        ?>
                                    </div>
                                    <button type="button" id="add-member" class="btn btn-primary mt-3">Add New Group Member <i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                                    <div class="row dynamic-section">

                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="visit_purpose" class="control-label">Visit Purpose <span class="text-danger">*</span></label>
                                                <select name="visit_purpose" id="visit_purpose" class="selectpicker" data-width="100%" data-read-only="<?= $disable_visit_purpose ?>" data-none-selected-text="Select Option" <?= ($requiredEnable) ? "required" : ""  ?>>
                                                    <option value=""></option>
                                                    <?php
                                                    $is_customer = (total_rows(db_prefix() . 'clients', array('leadid' => $form['lead_id'], "deleted_at" => NULL))) ? "1" : "0";
                                                    foreach (officeVisitPurposeArr($is_customer) as $key => $item) { ?>
                                                        <option value="<?= $key ?>" <?= (isset($form['visit_purpose']) && $form['visit_purpose'] == $key) ? 'selected' : '' ?>><?= $item ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 main-group-section hide">
                                            <div class="form-group">
                                                <label for="main_group_id" class="control-label">Product <span class="text-danger">*</span></label>
                                                <select name="main_group_id" id="main_group_id" class="selectpicker" data-width="100%" data-read-only="<?= $disable_product ?>" data-none-selected-text="Select Option" <?= ($requiredEnable) ? "required" : ""  ?>>
                                                    <option value=""></option>
                                                    <?php
                                                    foreach ($main_group_data as $key => $item) {
                                                    ?>
                                                        <option value="<?php echo $item['id'] ?>" <?= (isset($form['main_group_id']) && $form['main_group_id'] == $item['id']) ? 'selected' : '' ?>><?php echo $item['name']; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 sub-group-section hide">
                                            <div class="form-group">
                                                <label for="sub_group_id" class="control-label">Product Type<span class="text-danger">*</span></label>
                                                <select name="sub_group_id" id="sub_group_id" class="selectpicker" data-width="100%" data-read-only="<?= $disable_product_type ?>" data-none-selected-text="Select Option" <?= ($requiredEnable) ? "required" : ""  ?>>
                                                    <option value=""></option>
                                                    <?php
                                                    foreach ($sub_group_data as $key => $item) {
                                                    ?>
                                                        <option data-main-group-id="<?php echo $item['group_id'] ?>" value="<?php echo $item['id'] ?>" <?= (isset($form['sub_group_id']) && $form['sub_group_id'] == $item['id']) ? 'selected' : '' ?>><?php echo $item['name']; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 service-section hide">
                                            <div class="form-group">
                                                <label for="service_type" class="control-label">Service Type <span class="text-danger">*</span></label>
                                                <select name="service_type" id="service_type" class="selectpicker" data-width="100%" data-read-only="<?= $disable_service_type ?>" data-none-selected-text="Select Option" <?= ($requiredEnable) ? "required" : ""  ?>>
                                                    <option value=""></option>
                                                    <?php
                                                    foreach (serviceRequestPurposeArr() as $key => $item) { ?>
                                                        <option value="<?= $key ?>" <?= (isset($form['service_type']) && $form['service_type'] == $key) ? 'selected' : '' ?>><?= $item ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4 other-service-section hide">
                                            <div class="form-group">
                                                <label for="service_type" class="control-label">Other Service Type <span class="text-danger">*</span></label>
                                                <input class="form-control" id="other_service_type" name="other_service_type" value="<?= (isset($form['other_service_type'])) ? $form['other_service_type'] : '' ?>" <?= ($disable_other_service) ? "readonly='true'" : "" ?> />
                                            </div>
                                        </div>
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
                    <?php if (!empty($form['customer_submitted'])) {
                        $currentTimestamp = time();
                        $submittedTimestamp = strtotime($form['customer_submitted']);
                        $interval = $currentTimestamp - $submittedTimestamp;
                        if ($interval <= 60) {
                    ?>
                            <div class="col-md-8 col-md-offset-2">
                                <div class="alert alert-success text-center">
                                    Thank you ! Your submission has been successfully submitted!<br>
                                </div>
                            </div>
                        <?php
                        } else {
                        ?>
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
                                                <b>Form ID : </b> #<?= leadFormIdRender("OVF", $form['lead_id'], $form['id']) ?>
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
                        <?php
                        }
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
                                            <b>Form ID : </b> #<?= leadFormIdRender("OVF", $form['lead_id'], $form['id']) ?>
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
    <script>
        var id = '<?php echo $form['id']; ?>';
        var partnerIndex = <?= (isset($member_data) && !empty($member_data)) ? count($member_data) : 0 ?>;
        var partnerData = <?= (isset($member_data) && !empty($member_data)) ? json_encode($member_data) : [] ?>;
        var relationTypes = <?= (isset($relation_types_data) && !empty($relation_types_data)) ? json_encode($relation_types_data) : [] ?>;
        var stepwizard;
        $(function() {
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
                        if ($relationDropdown.find(`option[value="${relationSelectedValue}"]`).length > 0) {
                            $relationDropdown.val(relationSelectedValue).selectpicker('refresh');
                        } else {
                            partnerData[i].other_relation = relationSelectedValue;
                            partnerData[i].relation_with_applicant = "Other";
                            $relationDropdown.val("Other").selectpicker('refresh');
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
                    form.submit();
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

            $('select').selectpicker();

            $('select').on('change', function() {
                $(this).closest('.form-group').find('span.error').remove();
            });

            $('#add-member').on('click', function() {
                addMemberSection();
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

            $(document).on('click', '.delete-inquiry-new-file', function() {
                var formgroup = $(this).closest('.form-group');
                if (confirm("Are you sure you want perform this action?")) {
                    formgroup.find('.file-preview-section').remove();
                    formgroup.find('input[type="file"]').val('');
                    fileUploadRequiredValidationCheck(formgroup);
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
                                Preview: <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                                <i class='fa fa-trash text-danger delete-inquiry-new-file' aria-hidden='true'></i>
                            </span>
                        `;
                    formgroup.append(previewHtml);
                }
                fileuploadrevalidate();
            });

            $(document).on('change', '#visit_purpose', function() {
                $('.main-group-section').addClass('hide');
                $('#main_group_id').prop('required', false);
                $('#main_group_id').val('').selectpicker('refresh');
                $('.sub-group-section').addClass('hide');
                $('#sub_group_id').prop('required', false);
                $('#sub_group_id').val('').selectpicker('refresh');
                $('.service-section').addClass('hide');
                $('#service_type').prop('required', false);
                $('.other-service-section').addClass('hide');
                $('#other_service_type').prop('required', false);
                $(".dynamic-lead-inquiry-form-section").html("");
                if ($(this).val() == "1" || $(this).val() == "2" || $(this).val() == "3") {
                    $('.main-group-section').removeClass('hide');
                    $('#main_group_id').prop('required', true);
                } else if ($(this).val() == "4") {
                    $('.service-section').removeClass('hide');
                    $('#service_type').prop('required', true);
                }
            });
            $('#visit_purpose').trigger('change');
            $('#main_group_id').val("<?= (isset($form['main_group_id'])) ? $form['main_group_id'] : '' ?>").selectpicker('refresh');
            $('#sub_group_id').val("<?= (isset($form['sub_group_id'])) ? $form['sub_group_id'] : '' ?>").selectpicker('refresh');

            $('#sub_group_id').find('option').hide();
            $(document).on('change', '#main_group_id', function() {
                var selectedMainGroup = $(this).val();
                var subDropdown = $('#sub_group_id');
                var subgroupSelected = "<?= (isset($form['sub_group_id'])) ? $form['sub_group_id'] : '' ?>";
                $('.sub-group-section').removeClass('hide');
                subDropdown.find('option').hide();
                var visibleOptions = subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').show();
                if (visibleOptions.length > 0) {
                    subDropdown.prop('required', true);
                    subDropdown.selectpicker('refresh');
                    if (subgroupSelected && visibleOptions.filter('[value="' + subgroupSelected + '"]').length > 0) {
                        subDropdown.val(subgroupSelected);
                    } else {
                        subDropdown.val('');
                    }
                } else {
                    subDropdown.val('');
                    subDropdown.prop('required', false);
                    $('.sub-group-section').addClass('hide');
                }
                subDropdown.selectpicker('refresh');
                $(".dynamic-lead-inquiry-form-section").html("");
                generateLeadInquiryForm();
            });
            $('#main_group_id').trigger('change');

            $(document).on('change', '#sub_group_id', function() {
                generateLeadInquiryForm();
            });
            $('#sub_group_id').trigger('change');

            $(document).on('change', '#service_type', function() {
                if ($(this).val() == "9") {
                    $('.other-service-section').removeClass('hide');
                    $('#other_service_type').prop('required', true);
                } else {
                    $('.other-service-section').addClass('hide');
                    $('#other_service_type').prop('required', false);
                    $('#other_service_type').val("");
                }
            });
            $('#service_type').trigger('change');

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

            if (parent.length > 0) { // Check if the parent element exists
                console.log($(element).val());
                if ($(element).val() == "Other") {
                    parent.find('.other-relation-section').removeClass('hide');
                    parent.find('.other_relation').prop('required', true);
                } else {
                    parent.find('.other-relation-section').addClass('hide');
                    parent.find('.other_relation').prop('required', false);
                }
            } else {
                console.error("Parent element not found. Please check your HTML structure.");
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
                                            <input type="text" class="form-control" id="name-${index}" name="dynamic_data[${index}][name]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="professional_field-${index}">Professional Field <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="professional_field-${index}" name="dynamic_data[${index}][professional_field]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="occupation-${index}">Occupation <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="occupation-${index}" name="dynamic_data[${index}][occupation]" required>
                                        </div>
                                    </div>
                                     <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email-${index}">Email <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="email-${index}" name="dynamic_data[${index}][email]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contact_no-${index}">Phone Number <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control" id="contact_no-${index}" name="dynamic_data[${index}][contact_no]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="age-${index}">Age <span class="text-danger">* </span></label>
                                            <input type="number" class="form-control age" id="age-${index}" name="dynamic_data[${index}][age]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aadhar_no-${index}">Aadhar Number <span class="text-danger">* </span></label>
                                            <input type="number" class="form-control aadharno" id="aadhar_no-${index}" name="dynamic_data[${index}][aadhar_no]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pan-${index}">PAN Number <span class="text-danger">* </span></label>
                                            <input type="text" class="form-control panno" id="pan-${index}" name="dynamic_data[${index}][pan_no]" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="relation-${index}" class="control-label">Relation with applicant <span class="text-danger">*</span></label>
                                            <select id="relation-${index}" name="dynamic_data[${index}][relation_with_applicant]" class="relation-dropdown" data-width="100%" data-live-search="true" data-none-selected-text="Select Option" onchange="relation_change(this)" required>
                                                <option value="" disabled selected>Select relation</option>
                                                ${relationOptions}
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-4 other-relation-section hide">
                                        <div class="form-group">
                                            <label for="other-relation-${index}">Other Relation Type <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control other_relation" id="other-relation-${index}" name="dynamic_data[${index}][other_relation]">
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
            $('.dynamic-section').append(createMemberSection(partnerIndex));
            $('select').selectpicker('refresh');
            partnerIndex++;
            dynamicMemberTitle();
        }

        function generateLeadInquiryForm() {
            var mainGroupId = $('#main_group_id').val();
            var subGroupId = $('#sub_group_id').val();
            if (mainGroupId != "" && mainGroupId != null) {
                if ($('#sub_group_id option[data-main-group-id="' + mainGroupId + '"]').length != 0 && (subGroupId == null || subGroupId == "")) {
                    return false;
                }
                $('#loading-spinner').show();
                $.ajax({
                    url: "<?php echo site_url('forms/covf_render_lead_inquiry_form'); ?>",
                    method: "POST",
                    data: {
                        lead_id: "<?= $form['lead_id']; ?>",
                        officeFormId: "<?= $form['id']; ?>",
                        visit_purpose: $('#visit_purpose').val(),
                        mainGroupId: mainGroupId,
                        subGroupId: subGroupId,
                    },
                    dataType: 'json'
                }).done(function(result) {
                    if (result.success) {
                        $(".dynamic-lead-inquiry-form-section").html(result.html);
                        step_form_init();
                    } else {
                        $(".dynamic-lead-inquiry-form-section").html("");
                    }
                }).always(function() {
                    setTimeout(() => {
                        $('#loading-spinner').hide();
                        fileuploadrevalidate();
                    }, 500);
                });;
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
            var maxSize = 5 * 1024 * 1024; // 5 MB
            var allowedExtensions = ["jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx", "csv"];
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

        function fileuploadrevalidate() {
            $('input[type="file"]').each(function() {
                var formgroup = $(this).closest('.form-group');
                var is_required = formgroup.attr('data-required');
                var fileuploaded = formgroup.find('.file-uploaded').length;
                if (fileuploaded > 0) {
                    $(this).prop("required", false);
                } else if (is_required == "1") {
                    $(this).prop("required", true);
                }
            });
        }
    </script>
    <?php
    $company_logo = base_url('uploads/company/' . get_option('company_logo'));
    ?>
</body>

</html>