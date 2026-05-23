<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$panelClass = "";
$formLink = "";
$phoneNumberArr = phonenumberSplit($lead_data->phonenumber);
$ctr_iso2 = (isset($leadData) && $leadData->country != 0 ? get_country($leadData->country)->iso2 : 'IN');
?>
<div class="row">
    <div class="col-md-12">
        <button type="button" class="btn btn-info pull-left new-form-btn">Create New Form</button>
        <?= tutorialLinkButtonRender('lead-office-visitor-create-btn', 'right'); ?>
    </div>
</div>
<div class="row mtop5 ovf-verification-section hide">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Office Visit New Form : OTP Verification
                    <div class="pull-right header-action-section">
                        <button type="button" class="btn btn-default text-danger ovf-new-form-delete btn-xs mleft5">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </h4>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <div><strong><?= $lead_data->name ?></strong></div>
                </div>
                <?php if (empty($phoneNumberArr) && empty($lead_data->email)) { ?>
                    <div class="text-danger">Email or Phone number not exists. Please update email or phone number in lead for OTP verification.</div>
                <?php } else { ?>
                    <div class="form-group">
                        <label>OTP Verification via:</label>
                        <?php if (!empty($lead_data->email)) { ?>
                            <div class="radio-inline">
                                <label><input type="radio" name="otp_type" value="email" checked> Email</label>
                            </div>
                        <?php }  ?>
                        <?php if (!empty($phoneNumberArr)) { ?>
                            <div class="radio-inline">
                                <label><input type="radio" name="otp_type" value="mobile"> Mobile</label>
                            </div>
                        <?php }  ?>
                    </div>
                    <div class="form-group email-show-section">
                        <label for="email">Email:</label>
                        <div><strong><?= $lead_data->email ?></strong></div>
                    </div>
                    <div class="form-group phonenumber-show-section hide">
                        <label for="mobile">Mobile Number:</label>
                        <div>
                            <?php if (!empty($phoneNumberArr)) {
                                foreach ($phoneNumberArr as $key => $phonenumber) {
                                    $formattedNumber = convert_phonenumer_by_country($phonenumber, $ctr_iso2);
                                    $checked = "";
                                    if ($key == 0) {
                                        $checked = "checked";
                                    }
                            ?>
                                    <div class="radio-inline">
                                        <label><input type="radio" name="phonenumber" value="<?= $formattedNumber ?>" <?= $checked ?>> +<?= $formattedNumber ?></label>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="ovf-otp-send">Send OTP</button>
                    <div class="ovf-otp-section hide">
                        <div class="form-group">
                            <label for="otp">Enter OTP:</label>
                            <div class="otp-inputs">
                                <input type="text" class="form-control otp-input" maxlength="1">
                                <input type="text" class="form-control otp-input" maxlength="1">
                                <input type="text" class="form-control otp-input" maxlength="1">
                                <input type="text" class="form-control otp-input" maxlength="1">
                                <!-- <input type="text" class="form-control otp-input" maxlength="1">
                                <input type="text" class="form-control otp-input" maxlength="1"> -->
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="ovf-resend-otp">Resend OTP</button>
                        <button type="button" class="btn btn-success" id="ovf-verify-otp">Verify OTP</button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="row mtop5">
    <?php if (isset($forms) && !empty($forms)) {
    ?>
        <div class="col-md-12 ovf-preview-section">
            <div class="panel-group" id="accordion">
                <?php
                $formCount = 1;
                foreach ($forms as $fkey => $form_data) {
                    if (isset($form_data)) {
                        if (!empty($form_data['customer_submitted'])) {
                            $panelClass .= "border-success ";
                        } else if (!empty($form_data['whatsapp_send_timestamp']) || !empty($form_data['email_send_timestamp'])) {
                            $panelClass .= "border-warning ";
                        } else {
                            $panelClass .= " border-black ";
                        }
                        $formLink = site_url('forms/covf/') . $form_data["hash"];
                    }
                ?>
                    <div class="panel panel-default <?= $panelClass ?> " data-id="<?= $form_data['id'] ?>">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" class="accordian-collapse" data-parent="#accordion" href="#<?= $form_data["hash"]; ?>">
                                    Office Visit Form #<?= leadFormIdRender("OVF", $form_data['lead_id'], $form_data['id']);  ?></a>
                                <?php
                                if (!empty($form_data['customer_submitted'])) {
                                    echo '<span class="label label-success">Customer Submitted</span>';
                                } else if (!empty($form_data['whatsapp_send_timestamp']) || !empty($form_data['email_send_timestamp'])) {
                                    echo '<span class="label label-warning">Sent</span>';
                                } else {
                                    echo '<span class="label label-primary">Draft</span>';
                                }
                                ?>

                                <div class="pull-right header-action-section">
                                    <?php
                                    if (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])) {
                                        if (!empty($phoneNumberArr)) {
                                            $whatsappMessage = visitorFormWhatsappTemplate('lead-customer-office-visitor-form-send', $lead_data->name, $formLink);
                                            foreach ($phoneNumberArr as $phoneNumber) {
                                                $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                                    ?>
                                                <a href="<?= $whatappLink ?>" target="_blank" onclick="sendVisitorForm('whatsapp',this)" class="btn btn-default mleft5 btn-xs <?php echo (!empty($form_data['whatsapp_send_timestamp'])) ? 'border-success' : '' ?>" data-toggle="tooltip" data-title="<?= $phoneNumber ?>"><i class="fa fa-whatsapp"></i></a>
                                        <?php }
                                        } ?>
                                        <?php if (!empty($lead_data->email)) { ?>
                                            <a href="javascript:;" onclick="sendVisitorForm('email',this)" class="btn btn-default btn-xs mleft5 <?php echo (!empty($form_data['email_send_timestamp'])) ? 'border-success' : '' ?>" data-toggle="tooltip" data-title="<?= $lead_data->email ?>"><i class="fa fa-envelope"></i></a>
                                        <?php } ?>
                                        <div class="mleft5">
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox ovf-formswitch" id="form_switch_<?= $form_data['id'] ?>" <?= ($form_data['active'] == "1") ? "checked" : "" ?>>
                                                <label class="onoffswitch-label" for="form_switch_<?= $form_data['id'] ?>"></label>
                                            </div>
                                        </div>
                                        <a href="<?= $formLink ?>" target="_blank" class="btn btn-default ovf-form-edit btn-xs mleft5"><i class="fa fa-edit"></i></a>
                                        <button type="button" data-id="" class="btn btn-default text-danger ovf-form-delete btn-xs mleft5"><i class="fa fa-trash"></i></button>
                                    <?php } ?>
                                </div>
                            </h4>
                        </div>
                        <div id="<?= $form_data["hash"]; ?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Name Of Applicant</strong></label>
                                            <p><?= $form_data['name'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Professional Field</strong></label>
                                            <p><?= $form_data['professional_field'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Occupation</strong></label>
                                            <p><?= $form_data['occupation'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Email</strong></label>
                                            <p><?= $form_data['email'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Phone Number</strong></label>
                                            <p><?= $form_data['phone'] ?></p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Age</strong></label>
                                            <p><?= $form_data['age'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Aadhar Number</strong></label>
                                            <p><?= $form_data['aadhar_no'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>PAN Number</strong></label>
                                            <p><?= $form_data['pan_no'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Organization / Company</strong></label>
                                            <p><?= $form_data['organization'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Purpose Of visit</strong></label>
                                            <p><?= (is_numeric($form_data['visit_purpose'])) ? officeVisitPurposeArr("0", $form_data['visit_purpose']) : $form_data['visit_purpose'] ?></p>
                                        </div>
                                    </div>

                                    <?php
                                    if (in_array($form_data['visit_purpose'], ['1', '2', '3'])) {
                                        $main_group_data = $this->leads_model->get_main_group_by_id($form_data['main_group_id']);
                                        $sub_group_data = $this->leads_model->get_sub_group_by_id($form_data['sub_group_id']);
                                        if (!empty($main_group_data)) {
                                    ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="field1"><strong>Product</strong></label>
                                                    <p><?= $main_group_data['name']  ?></p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        if (!empty($sub_group_data)) {
                                        ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="field1"><strong>Product Type</strong></label>
                                                    <p><?= $sub_group_data['name']  ?></p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    <?php
                                    } else if ($form_data['visit_purpose'] == "4") {
                                    ?>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="field1"><strong>Service Type</strong></label>
                                                <p><?= serviceRequestPurposeArr($form_data['service_type'])  ?></p>
                                            </div>
                                        </div>
                                        <?php
                                        if ($form_data['service_type'] == "9") {
                                        ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="field1"><strong>Other Service Type</strong></label>
                                                    <p><?= $form_data['other_service_type']  ?></p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>

                                    <?php
                                    }
                                    ?>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="field1"><strong>Any special requests for needs for the visit</strong></label>
                                            <p><?= $form_data['special_request'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="field1"><strong>Please provide any additional information that may be relevant to the visit</strong></label>
                                            <p><?= $form_data['additional_info'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <?php
                                        $member_data = $this->office_visitor_form_model->get_members_data($form_data['id']);
                                        if (!empty($member_data)) {
                                            foreach ($member_data as $key => $item) { ?>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title">Member <?= ($key + 2) ?> Details</h3>
                                                    </div>
                                                    <div class="panel-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <td><strong>Name</strong> : <?= $item['name'] ?></td>
                                                                <td><strong>Professional Field</strong> : <?= $item['professional_field'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Occupation</strong> : <?= $item['occupation'] ?></td>
                                                                <td><strong>Email</strong> : <?= $item['email'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Phone Number</strong> : <?= $item['contact_no'] ?></td>
                                                                <td><strong>Age</strong> : <?= $item['age'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Aadhar Number</strong> : <?= $item['aadhar_no'] ?></td>
                                                                <td><strong>PAN Number</strong> : <?= $item['pan_no'] ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Relation with applicant</strong> : <?= $item['relation_with_applicant'] ?></td>
                                                                <?php if ($item['relation_with_applicant'] == "Other") { ?>
                                                                    <td><strong>Other Relation</strong> : <?= $item['other_relation'] ?></td>
                                                                <?php } ?>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                        <?php }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php $formCount++;
                } ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="no-form-msg">No Forms available.</div>
    <?php } ?>
</div>
<script>
    var email = "<?= (isset($lead_data->email) && !empty($lead_data->email)) ? $lead_data->email : ''  ?>";
    var resendTimeout;
    $(document).ready(function() {
        if (!window.recaptchaVerifier) {
            renderRecaptcha();
        }

        $(document).off('input', '.otp-input');
        $(document).on('input', '.otp-input', function() {
            $(this).closest('.form-group').find('.text-message').remove()
            var $this = $(this);
            if ($this.val().length === 1) {
                $this.next('.otp-input').focus();
            }
        });

        $(document).off('keydown', '.otp-input');
        $(document).on('keydown', '.otp-input', function(e) {
            var $this = $(this);
            if (e.key === "Backspace" && $this.val().length === 0) {
                $this.prev('.otp-input').focus();
            }
        });

        $(document).off('click', '#ovf-otp-send');
        $(document).on('click', '#ovf-otp-send', function() {
            ovf_sendOTP("send");
        });

        $(document).off('click', '#ovf-resend-otp');
        $(document).on('click', '#ovf-resend-otp', function() {
            ovf_sendOTP("resend");
        });

        $(document).off('change', '.ovf-verification-section input[name="otp_type"]');
        $(document).on('change', '.ovf-verification-section input[name="otp_type"]', function() {
            var selected = $('input[name="otp_type"]:checked').val();
            if (selected == "email") {
                $('.email-show-section').removeClass('hide');
                $('.phonenumber-show-section').addClass('hide');
            } else {
                $('.email-show-section').addClass('hide');
                $('.phonenumber-show-section').removeClass('hide');
            }
            OTPInputSection("hide");
        });

        $(document).off('change', '.ovf-verification-section input[name="phonenumber"]');
        $(document).on('change', '.ovf-verification-section input[name="phonenumber"]', function() {
            OTPInputSection("hide");
        });

        $(document).off('click', '#ovf-verify-otp');
        $(document).on('click', '#ovf-verify-otp', function() {
            ovf_verifyOTP();
        });

        $(document).off('change', '.ovf-formswitch');
        $(document).on('change', '.ovf-formswitch', function() {
            var id = $(this).closest('.panel').attr('data-id');
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads_office_visitor_forms/form_status_change') ?>",
                method: "POST",
                data: {
                    id: id,
                    status: status
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.ovf-form-delete');
        $(document).on('click', '.ovf-form-delete', function() {
            var panel = $(this).closest('.panel');
            var id = $(this).closest('.panel').attr('data-id');
            if (confirm_delete()) {
                if (id != "" && id != null) {
                    $.ajax({
                        url: "<?php echo admin_url('leads_office_visitor_forms/delete_form') ?>",
                        method: "POST",
                        data: {
                            id: id
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            getOfficeVisitorFormSection();
                        } else {
                            alert_float('danger', result.message);
                        }
                    });

                }
            }

        });

        $(document).off('click', '.ovf-new-form-delete');
        $(document).on('click', '.ovf-new-form-delete', function() {
            if (confirm_delete()) {
                $('.ovf-verification-section').addClass('hide');
                $('.ovf-otp-section').addClass('hide');
                $('#ovf-otp-send').removeClass('hide');
                $('.otp-input').val("");
            }
        });

        $(document).off('click', '.new-form-btn');
        $(document).on('click', '.new-form-btn', function() {
            if ($('.ovf-verification-section').hasClass('hide')) {
                $('.ovf-verification-section').removeClass('hide');
                $('#ovf-otp-send').removeClass('hide');
            }
        });

    });

    function renderRecaptcha() {
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'invisible',
            'callback': function(response) {
                console.log("reCAPTCHA solved");
            }
        });
    }

    function ovf_sendOTP(sendType = "send") {
        var sendvia = $('input[name="otp_type"]:checked').val();
        var phoneNumber = $('.ovf-verification-section input[name="phonenumber"]:checked').val();
        if (sendType == "send") {
            $('#ovf-otp-send').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
        } else {
            $('#ovf-resend-otp').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
        }
        var isValid = true;
        var error = "";
        if (sendvia == "mobile") {
            if (phoneNumber == "" || phoneNumber == null) {
                isValid = false;
                error = 'Error: Invalid phone number.';
            }
        } else {
            if (email == "" || email == null) {
                isValid = false;
                error = 'Error: Invalid email.';
            }
        }

        if (isValid) {
            $.ajax({
                url: "<?= admin_url('leads_office_visitor_forms/otp_verification') ?>",
                method: 'POST',
                dataType: 'JSON',
                data: {
                    sendvia: sendvia,
                    email: email,
                    phoneNumber: phoneNumber,
                    leadid: "<?= (isset($lead_data->id) && !empty($lead_data->id)) ? $lead_data->id : ''  ?>",
                    type: 'send-otp',
                },
                success: function(response) {
                    if (response.success) {
                        if (sendType == "send") {
                            OTPInputSection("show");
                            $('#ovf-otp-send').prop('disabled', false).html('Send OTP');
                        } else {
                            $('#ovf-resend-otp').prop('disabled', false).html('Resend OTP');
                        }
                        startResendCountdown();
                        alert_float('success', response.message);
                    } else {
                        if (sendType == "send") {
                            $('#ovf-otp-send').prop('disabled', false).html('Send OTP');
                        } else {
                            $('#ovf-resend-otp').prop('disabled', false).html('Resend OTP');
                        }
                        alert_float('danger', response.message);
                    }
                },
                complete: function() {
                    if (sendType == "send") {
                        $('#ovf-otp-send').prop('disabled', false).html('Send OTP');
                    } else {
                        $('#ovf-resend-otp').prop('disabled', false).html('Resend OTP');
                    }
                }
            });
        } else {
            if (sendType == "send") {
                $('#ovf-otp-send').prop('disabled', false).html('Send OTP');
            } else {
                $('#ovf-resend-otp').prop('disabled', false).html('Resend OTP');
            }
            alert_float('danger', error);
        }
    }

    function ovf_verifyOTP(sendType = "send") {
        var sendvia = $('input[name="otp_type"]:checked').val();
        var phoneNumber = $('.ovf-verification-section input[name="phonenumber"]:checked').val();
        var otp = $('.otp-input').map(function() {
            return $(this).val();
        }).get().join('');

        var isValid = true;
        var error = "";
        if (sendvia == "mobile") {
            if (phoneNumber == "" || phoneNumber == null) {
                isValid = false;
                error = 'Error: Invalid phone number.';
            }
        } else {
            if (email == "" || email == null) {
                isValid = false;
                error = 'Error: Invalid email.';
            }
        }

        if (isValid) {
            if (otp.length == 4) {
                $('#ovf-resend-otp').prop('disabled', true);
                $('#ovf-verify-otp').prop('disabled', true).html('Verifying... <i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "<?= admin_url('leads_office_visitor_forms/otp_verification') ?>",
                    method: 'POST',
                    dataType: 'JSON',
                    data: {
                        email: email,
                        leadid: "<?= (isset($lead_data->id) && !empty($lead_data->id)) ? $lead_data->id : ''  ?>",
                        type: 'verify-otp',
                        phoneNumber: phoneNumber,
                        sendvia: sendvia,
                        otp: otp,
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#ovf-resend-otp').prop('disabled', false);
                            $('#ovf-verify-otp').prop('disabled', false).html('Verify OTP');
                            alert_float('success', response.message);
                            getOfficeVisitorFormSection(true);
                        } else {
                            $('#ovf-resend-otp').prop('disabled', false);
                            $('#ovf-verify-otp').prop('disabled', false).html('Verify OTP');
                            alert_float('danger', response.message);
                        }
                    },
                    complete: function() {
                        $('#ovf-resend-otp').prop('disabled', false);
                        $('#ovf-verify-otp').prop('disabled', false).html('Verify OTP');
                    }
                });
            } else {
                alert_float('danger', "Please enter OTP");
            }
        } else {
            alert_float('danger', error);
        }
    }

    function OTPInputSection(action = "show") {
        if (action == "show") {
            $('.otp-input').val('');
            $('.ovf-otp-section').removeClass('hide');
            $('#ovf-otp-send').addClass('hide');
        } else {
            $('.otp-input').val('');
            $('.ovf-otp-section').addClass('hide');
            $('.ovf-otp-section').find(".text-message").remove();
            $('#ovf-otp-send').removeClass('hide');
            $('#ovf-resend-otp').prop('disabled', false).html("Resend OTP");
        }
    }

    function startResendCountdown() {
        clearInterval(resendTimeout);
        var secondsLeft = 60;
        $('#ovf-resend-otp').prop('disabled', true).html('Resend OTP (' + secondsLeft + 's)');
        resendTimeout = setInterval(function() {
            secondsLeft--;
            if (secondsLeft > 0) {
                $('#ovf-resend-otp').prop('disabled', true).html('Resend OTP (' + secondsLeft + 's)');
            } else {
                clearInterval(resendTimeout);
                $('#ovf-resend-otp').prop('disabled', false).html('Resend OTP');
            }
        }, 1000);
    }

    function sendVisitorForm(type, element) {
        var panel = $(element).closest('.ovf-preview-section .panel');
        var id = panel.attr('data-id');
        if (type != "") {
            $.ajax({
                url: "<?php echo admin_url('leads_office_visitor_forms/send_form') ?>",
                method: "POST",
                data: {
                    id: id,
                    lead_id: "<?= (isset($lead_data->id) && !empty($lead_data->id)) ? $lead_data->id : ''  ?>",
                    type: type,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    if (type == "email") {
                        panel.find('.panel-heading').find('.fa-envelope').closest('a').addClass('border-success');
                    } else {
                        panel.find('.panel-heading').find('.fa-whatsapp').closest('a').addClass('border-success');
                    }
                    alert_float('success', result.message);
                    getOfficeVisitorFormSection();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    }
</script>

<style>
    .otp-inputs {
        display: flex;
        justify-content: space-between;
        max-width: 300px;
    }

    .otp-input {
        width: 40px;
        text-align: center;
        font-size: 20px;
        margin: 0 5px;
    }

    .radio-inline {
        display: inline-block;
        margin-right: 10px;
    }

    .form-control-static {
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #f9f9f9;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
    }

    .ovf-verification-section label {
        margin-bottom: unset;
    }

    .border-success {
        border: 1px solid green !important;
    }

    .border-warning {
        border: 1px solid #ff6f00 !important;
    }

    .border-danger {
        border: 1px solid red !important;
    }

    .border-black {
        border: 1px solid black !important;
    }

    .header-action-section {
        display: flex;
        margin-top: -5px;
    }

    .onoffswitch {
        top: 5px;
    }

    .no-form-msg {
        text-align: center;
        padding: 30px;
    }
</style>