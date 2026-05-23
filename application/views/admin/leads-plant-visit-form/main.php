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
        <?= tutorialLinkButtonRender('lead-plant-visit-form-create-btn', 'right'); ?>
    </div>
</div>
<div class="row mtop5 pvf-verification-section hide">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">Plant Visit New Form : OTP Verification
                    <div class="pull-right header-action-section">
                        <button type="button" class="btn btn-default text-danger pvf-new-form-delete btn-xs mleft5">
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
                    <button type="button" class="btn btn-primary" id="pvf-otp-send">Send OTP</button>
                    <div class="pvf-otp-section hide">
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
                        <button type="button" class="btn btn-primary" id="pvf-resend-otp">Resend OTP</button>
                        <button type="button" class="btn btn-success" id="pvf-verify-otp">Verify OTP</button>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="row mtop5">
    <?php if (isset($forms) && !empty($forms)) {
    ?>
        <div class="col-md-12 pvf-preview-section">
            <div class="panel-group" id="accordion">
                <?php
                $formCount = 1;
                foreach ($forms as $fkey => $form_data) {
                    $uniqid = uniqid();
                    $approval_form_id = "plantVisitApprovalForm_" . $uniqid;
                    if (isset($form_data)) {
                        if (!empty($form_data['customer_submitted'])) {
                            $panelClass .= "border-success ";
                        } else if (!empty($form_data['whatsapp_send_timestamp']) || !empty($form_data['email_send_timestamp'])) {
                            $panelClass .= "border-warning ";
                        } else {
                            $panelClass .= " border-black ";
                        }
                        $formLink = site_url('forms/pvf/') . $form_data["hash"];
                    }
                ?>
                    <div class="panel panel-main panel-default <?= $panelClass ?> " data-id="<?= $form_data['id'] ?>">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" class="accordian-collapse" data-parent="#accordion" href="#<?= $form_data["hash"]; ?>">
                                    Plant Visit Form #<?= leadFormIdRender("PVF", $form_data['lead_id'], $form_data['id']);  ?></a>
                                <?php
                                if (!empty($form_data['approval_staus'])) {
                                    if ($form_data['approval_staus'] == "Pending") {
                                        echo '<span class="label label-warning">Review Pending</span>';
                                    } else if ($form_data['approval_staus'] == "Approved") {
                                        echo '<span class="label label-success">Approved</span>';
                                    } else if ($form_data['approval_staus'] == "Not Approved") {
                                        echo '<span class="label label-danger">Not Approved</span>';
                                    }
                                } else if (!empty($form_data['whatsapp_send_timestamp']) || !empty($form_data['email_send_timestamp'])) {
                                    echo '<span class="label label-info">Sent</span>';
                                } else {
                                    echo '<span class="label label-primary">Draft</span>';
                                }
                                ?>

                                <div class="pull-right header-action-section">
                                    <?php
                                    if (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])) {
                                        if (!empty($phoneNumberArr)) {
                                            $whatsappMessage = visitorFormWhatsappTemplate('lead-plant-visit-form-send', $lead_data->name, $formLink);
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
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox pvf-formswitch" id="form_switch_<?= $form_data['id'] ?>" <?= ($form_data['active'] == "1") ? "checked" : "" ?>>
                                                <label class="onoffswitch-label" for="form_switch_<?= $form_data['id'] ?>"></label>
                                            </div>
                                        </div>
                                        <a href="<?= $formLink ?>" target="_blank" class="btn btn-default pvf-form-edit btn-xs mleft5"><i class="fa fa-edit"></i></a>
                                        <button type="button" data-id="" class="btn btn-default text-danger pvf-form-delete btn-xs mleft5"><i class="fa fa-trash"></i></button>
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
                                            <label for="field1"><strong>Visit Purpose</strong></label>
                                            <p><?= $form_data['visit_purpose'] ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Visitor Type</strong></label>
                                            <p>
                                                <?php
                                                $visitor_type = "";
                                                if (!empty($form_data['visitor_type'])) {
                                                    $visitor_type_data = get_plant_visitor_type($form_data['visitor_type']);
                                                    if (!empty($visitor_type_data)) {
                                                        $visitor_type = $visitor_type_data['title'];
                                                    }
                                                }
                                                ?>
                                                <?= $visitor_type ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Visit Plant Type</strong></label>

                                            <?php
                                            if (isset($form_data['plant_visit']) && !empty($form_data['plant_visit'])) {
                                                $plant_visit_type = $this->leads_model->get_main_group_by_id($form_data['plant_visit']);
                                                if (!empty($plant_visit_type)) {
                                            ?>
                                                    <p><?= $plant_visit_type['name'] ?></p>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="field1"><strong>Expected Visit Date & Time</strong></label>
                                            <p><?= (isset($form_data['visit_date_time']) && !empty($form_data['visit_date_time'])) ? date('d-m-Y H:i', strtotime($form_data['visit_date_time'])) : '' ?></p>
                                        </div>
                                    </div>
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
                                        <table class="table table-bordered">
                                            <tr>
                                                <td colspan="2" class="text-center"><b>Applicant's Documents</b></td>
                                            </tr>
                                            <tr>
                                                <td>Applicant Photo</td>
                                                <td><img width="100px" height="100px" class="profile-img" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['photo'])); ?>" class="img-responsive" alt="">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Applicant Signature</td>
                                                <td><img height="100px" class="profile-img" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['signature'])); ?>" class="img-responsive" alt=""></td>
                                            </tr>
                                            <tr>
                                                <td>Aadhar Card</td>
                                                <td>
                                                    <?php
                                                    $file_path = get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['aadhar_card'];
                                                    $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['aadhar_card']);
                                                    if (!empty($form_data['aadhar_card']) && file_exists($file_path)) {
                                                    ?>
                                                        <i class="fa fa-paperclip" aria-hidden="true"></i> <a target="_blank" href="<?php echo site_url('download/file_download?path=' . $protected_path) ?>"><?= $form_data['aadhar_card'] ?></a>
                                                    <?php
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Pan Card</td>
                                                <td>
                                                    <?php
                                                    $file_path = get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['pan_card'];
                                                    $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['pan_card']);
                                                    if (!empty($form_data['pan_card']) && file_exists($file_path)) {
                                                    ?>
                                                        <i class="fa fa-paperclip" aria-hidden="true"></i> <a target="_blank" href="<?php echo site_url('download/file_download?path=' . $protected_path) ?>"><?= $form_data['pan_card'] ?></a>
                                                    <?php
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="col-md-12">
                                        <?php
                                        $member_data = $this->plant_visit_form_model->get_members_data($form_data['id']);
                                        if (!empty($member_data)) {
                                            foreach ($member_data as $key => $item) { ?>
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title">Member <?= ($key + 2) ?> Details</h3>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Name</strong></label>
                                                                    <p><?= $item['name'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Professional Field</strong></label>
                                                                    <p><?= $item['professional_field'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Occupation</strong></label>
                                                                    <p><?= $item['occupation'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Email</strong></label>
                                                                    <p><?= $item['email'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Phone Number</strong></label>
                                                                    <p><?= $item['contact_no'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Age</strong></label>
                                                                    <p><?= $item['age'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Aadhar Number</strong></label>
                                                                    <p><?= $item['aadhar_no'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>PAN Number</strong></label>
                                                                    <p><?= $item['pan_no'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <label for="field1"><strong>Relation with applicant</strong></label>
                                                                    <p><?= $item['relation_with_applicant'] ?></p>
                                                                </div>
                                                            </div>
                                                            <?php if ($item['relation_with_applicant'] == "Other") { ?>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="field1"><strong>Other Relation Type</strong></label>
                                                                        <p><?= $item['other_relation'] ?></p>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                            <div class="col-md-12">
                                                                <table class="table table-bordered">
                                                                    <tr>
                                                                        <td colspan="2" class="text-center"><b>Member <?= ($key + 2) ?> Documents</b></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Applicant Photo</td>
                                                                        <td><img width="100px" height="100px" class="profile-img" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['photo'])); ?>" class="img-responsive" alt="">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Applicant Signature</td>
                                                                        <td><img height="100px" class="profile-img" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['signature'])); ?>" class="img-responsive" alt=""></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Aadhar Card</td>
                                                                        <td>
                                                                            <?php
                                                                            $file_path = get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['aadhar_card'];
                                                                            $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['aadhar_card']);
                                                                            if (!empty($item['aadhar_card']) && file_exists($file_path)) {
                                                                            ?>
                                                                                <i class="fa fa-paperclip" aria-hidden="true"></i> <a target="_blank" href="<?php echo site_url('download/file_download?path=' . $protected_path) ?>"><?= $item['aadhar_card'] ?></a>
                                                                            <?php
                                                                            } else {
                                                                                echo "-";
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Pan Card</td>
                                                                        <td>
                                                                            <?php
                                                                            $file_path = get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['pan_card'];
                                                                            $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $item['pan_card']);
                                                                            if (!empty($item['pan_card']) && file_exists($file_path)) {
                                                                            ?>
                                                                                <i class="fa fa-paperclip" aria-hidden="true"></i> <a target="_blank" href="<?php echo site_url('download/file_download?path=' . $protected_path) ?>"><?= $item['pan_card'] ?></a>
                                                                            <?php
                                                                            } else {
                                                                                echo "-";
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        <?php }
                                        } ?>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td colspan="2" class="text-center"><b>Visit Charges</b></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Visitor Type</strong></td>
                                                <td>
                                                    <?= $visitor_type ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Visit Date & Time </strong></td>
                                                <td><?= (isset($form_data['visit_date_time']) && !empty($form_data['visit_date_time'])) ? date('d-m-Y H:i', strtotime($form_data['visit_date_time'])) : '' ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Members</strong></td>
                                                <td><?= $form_data['total_members'] ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Visitor Charge</strong></td>
                                                <td>
                                                    <?php
                                                    if ($form_data['charge_type'] == "fixed") {
                                                    ?>
                                                        <?= app_format_money($form_data['visit_amount'], "INR") . '/-'; ?> (Fixed Charge)
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <?= app_format_money($form_data['visit_amount'], "INR") . '/-'; ?> (Per Person)
                                                    <?php
                                                    }
                                                    ?>

                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Amount</strong></td>
                                                <td><?= app_format_money($form_data['total_amount'], "INR") . '/-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Tax Amount (<?= $form_data['tax_name'] ?> <?= $form_data['tax_rate'] ?>%)</strong></td>
                                                <td><?= app_format_money($form_data['tax_amount'], "INR") . '/-'; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Payable Amount </strong></td>
                                                <td><?= app_format_money($form_data['total_pay_amount'], "INR") . '/-'; ?>
                                                    <?php
                                                    if (get_plant_visitform_check_free_visit($form_data)) {
                                                        echo  " (* " . $form_data['free_visit_day'] . " free visit for " . $visitor_type . ")";
                                                    }
                                                    ?>

                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="field1"><strong>Applicant Digital Signature</strong></label>
                                            <p><img width="200px" height="100px" class="profile-img" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('lead') . $form_data['lead_id'] . '/' . $form_data['digital_signature'])); ?>" class="img-responsive" alt=""></p>
                                        </div>
                                    </div>
                                    <?php
                                    if (isset($form_data) && (is_admin() || $form_data['created_by'] == get_staff_user_id() || leads_permission_allow_to_manager($form_data['lead_id'])))
                                        if (!empty($form_data['approval_staus'])) {
                                    ?>
                                        <div class="col-md-12 mtop5 plant-visit-form-approval-section">
                                            <div class="panel panel-primary">
                                                <div class="panel-heading"><b>Plant Visit Form Approval</b></div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <?php if ($form_data['approval_staus'] == "Pending") {
                                                            echo form_open_multipart(admin_url('leads_plant_visit_forms/form_approval_status_change'), array('id' => $approval_form_id, "class" => "plantvisitApprovalForm"));
                                                        ?>
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <div class="radio-section">
                                                                        <label class="radio-inline" for="form_status_approved_<?= $uniqid ?>">
                                                                            <input type="radio" id="form_status_approved_<?= $uniqid ?>" value="Approved" name="form_status" form="<?= $approval_form_id ?>" checked> Approved
                                                                        </label>
                                                                        <label class="radio-inline" for="form_status_not_approved_<?= $uniqid ?>">
                                                                            <input type="radio" id="form_status_not_approved_<?= $uniqid ?>" value="Not Approved" name="form_status" form="<?= $approval_form_id ?>"> Not Approved
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?= all_type_input_render([
                                                                "label" => "Visit Date & Time",
                                                                "id" => "visit_date_time_<?= $uniqid ?>",
                                                                "name" => "visit_date_time",
                                                                "type" => "date_picker_time",
                                                                "selected_value" => (isset($form_data['visit_date_time']) && !empty($form_data['visit_date_time'])) ? date('d-m-Y H:i', strtotime($form_data['visit_date_time'])) : '',
                                                                "is_required" => true,
                                                                "form" => $approval_form_id,
                                                            ], 'col-md-12 visit_date_section', true);
                                                            ?>

                                                            <input type="hidden" name="is_free_visit" value="<?= $form_data['is_free_visit'] ?>" form="<?= $approval_form_id ?>" />
                                                            <input type="hidden" name="free_visit_day" value="<?= $form_data['free_visit_day'] ?>" form="<?= $approval_form_id ?>" />

                                                            <div class="col-md-12 reject_reason_section hide">
                                                                <div class="form-group">
                                                                    <div class="control-label">Reject Reason<span class="text-danger">* </span></div>
                                                                    <textarea id="reject_reason_<?= $uniqid ?>" maxlength="500" name="reject_note" rows="4" class="form-control" placeholder="Enter Reason" form="<?= $approval_form_id ?>"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <button type="button" class="btn btn-primary btn-sm approval-form-save-btn">Submit</button>
                                                            </div>
                                                            <?php echo form_close(); ?>
                                                        <?php } elseif ($form_data['approval_staus'] == "Approved" || $form_data['approval_staus'] == "Not Approved") {
                                                        ?>
                                                            <div class="col-md-12">
                                                                <?php if ($form_data['approval_staus'] == "Approved") { ?>
                                                                    <div><b>Form Status : </b>
                                                                        <span class='text-success'>Approved</span>
                                                                    </div>
                                                                <?php } else if ($form_data['approval_staus'] == "Not Approved") { ?>
                                                                    <div><b>Form Status : </b>
                                                                        <span class='text-danger'>Not Approved</span>
                                                                    </div>
                                                                    <div><b>Reject Reason : </b><?= $form_data['reject_note'] ?> </div>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="col-md-12 mtop10 text-muted">Notifiy to Customer</div>
                                                            <div class="col-md-12 mtop5">
                                                                <?php
                                                                if (!empty($phoneNumberArr)) {
                                                                    $lead = $this->leads_model->get($form_data['lead_id']);
                                                                    $lead->plant_visit_form_link = site_url('forms/pvf/') . $form_data["hash"];
                                                                    $lead->plant_visit_form_not_approved_reason = $form_data['reject_note'];
                                                                    $lead->plant_visit_payable_amount = $form_data['total_pay_amount'];
                                                                    $lead->plant_visit_total_members = $form_data['total_members'];
                                                                    $lead->plant_visit_date_time = $form_data['visit_date_time'];
                                                                    $whatsappMessage = strip_tags(leadsEmailPreview(($form_data['approval_staus'] == "Approved") ? "lead_plant_visit_form_approved" : "lead_plant_visit_form_not_approved", $lead)->message);
                                                                    $whatsappMessage = str_replace('&nbsp;', ' ', $whatsappMessage);
                                                                    foreach ($phoneNumberArr as $phoneNumber) {
                                                                        $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
                                                                ?>
                                                                        <a href="<?= $whatappLink ?>" data-type="whatsapp" target="_blank" class="btn btn-success send-approved-not-approved-notify mleft5" data-toggle="tooltip" data-title="<?= $phoneNumber ?>">Share to Whatsapp <i class="fa fa-whatsapp"></i></a>
                                                                <?php }
                                                                } ?>
                                                                <?php if (!empty($lead_data->email)) { ?>
                                                                    <button type="button" data-type="email" class="btn btn-default send-approved-not-approved-notify" data-toggle="tooltip" data-title="<?= $lead_data->email ?>">Send Email <i class="fa fa-envelope"></i></button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

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


        var sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        var day = String(sevenDaysAgo.getDate()).padStart(2, '0');
        var month = String(sevenDaysAgo.getMonth() + 1).padStart(2, '0');
        var year = sevenDaysAgo.getFullYear();
        var min_date = year + '-' + month + '-' + day;

        $('input[name="visit_date_time"]').datetimepicker({
            format: 'd-m-Y H:i',
            beforeShowDay: function(date) {
                var day = date.getDay();
                if (day === 0) {
                    return [false, "", "Unavailable"];
                }
                return [true];
            },
            minDate: min_date,
            maxDate: false,
        });

        $(document).off('change input keyup', 'input[name="visit_date_time"]');
        $(document).on('change input keyup', 'input[name="visit_date_time"]', function(e) {
            var $this = $(this);
            $this.siblings(".text-success").remove();
            var panel = $(this).closest('.panel-main');
            var freeVisitDay = panel.find('input[name="free_visit_day"]').val();
            var isFreeVisitAllowed = panel.find('input[name="is_free_visit"]').val();
            if (getDayNameByDate($this.val()) == freeVisitDay && isFreeVisitAllowed == "1") {
                $this.after("<span class='text-success'>* Free Visit eligible. (Amount will be update once you approve the form.)</span>");
            }
        });

        $(document).off('keydown', '.otp-input');
        $(document).on('keydown', '.otp-input', function(e) {
            var $this = $(this);
            if (e.key === "Backspace" && $this.val().length === 0) {
                $this.prev('.otp-input').focus();
            }
        });

        $(document).off('click', '#pvf-otp-send');
        $(document).on('click', '#pvf-otp-send', function() {
            pvf_sendOTP("send");
        });

        $(document).off('click', '#pvf-resend-otp');
        $(document).on('click', '#pvf-resend-otp', function() {
            pvf_sendOTP("resend");
        });

        $(document).off('change', '.pvf-verification-section input[name="otp_type"]');
        $(document).on('change', '.pvf-verification-section input[name="otp_type"]', function() {
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

        $(document).off('change', '.pvf-verification-section input[name="phonenumber"]');
        $(document).on('change', '.pvf-verification-section input[name="phonenumber"]', function() {
            OTPInputSection("hide");
        });

        $(document).off('click', '#pvf-verify-otp');
        $(document).on('click', '#pvf-verify-otp', function() {
            pvf_verifyOTP();
        });

        $(document).off('change', '.pvf-formswitch');
        $(document).on('change', '.pvf-formswitch', function() {
            var id = $(this).closest('.panel').attr('data-id');
            var status = "";
            if ($(this).prop('checked')) {
                status = 1;
            } else {
                status = 0;
            }
            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/form_status_change') ?>",
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

        $(document).off('click', '.pvf-form-delete');
        $(document).on('click', '.pvf-form-delete', function() {
            var panel = $(this).closest('.panel');
            var id = $(this).closest('.panel').attr('data-id');
            if (confirm_delete()) {
                if (id != "" && id != null) {
                    $.ajax({
                        url: "<?php echo admin_url('leads_plant_visit_forms/delete_form') ?>",
                        method: "POST",
                        data: {
                            id: id
                        },
                        dataType: 'json'
                    }).done(function(result) {
                        if (result.success) {
                            alert_float('success', result.message);
                            getPlantVisitorFormSection();
                        } else {
                            alert_float('danger', result.message);
                        }
                    });

                }
            }
        });

        $(document).off('click', '.pvf-new-form-delete');
        $(document).on('click', '.pvf-new-form-delete', function() {
            if (confirm_delete()) {
                $('.pvf-verification-section').addClass('hide');
                $('.pvf-otp-section').addClass('hide');
                $('#pvf-otp-send').removeClass('hide');
                $('.otp-input').val("");
            }
        });

        $(document).off('click', '.new-form-btn');
        $(document).on('click', '.new-form-btn', function() {
            if ($('.pvf-verification-section').hasClass('hide')) {
                $('.pvf-verification-section').removeClass('hide');
                $('#pvf-otp-send').removeClass('hide');
            }
        });


        $(document).off('click', '.approval-form-save-btn');
        $(document).on('click', '.approval-form-save-btn', function() {
            var panel = $(this).closest('.panel-main');
            var formId = panel.attr('data-id');
            var reject_note_obj = panel.find('textarea[name="reject_note"]');
            var reject_note = reject_note_obj.val();
            panel.find('.form-group').find('.error').remove();
            var form_status = panel.find('input[type="radio"][name="form_status"]:checked').val();
            if (form_status == "Not Approved") {
                if (reject_note == "" || reject_note == null) {
                    reject_note_obj.closest('.form-group').append("<span class='error text-danger'>Reject Reason Required...</span>")
                    return false;
                }
            }

            var visit_date_obj = panel.find('input[name="visit_date_time"]');
            var visit_date = visit_date_obj.val();
            if (form_status == "Approved") {
                if (visit_date == "" || visit_date == null) {
                    visit_date_obj.closest('.form-group').append("<span class='error text-danger'>Visit date and time required..</span>")
                    return false;
                }
            }

            var form = panel.find('.plantvisitApprovalForm');
            var formData = new FormData(form[0]);
            formData.append('form_id', formId);
            $.ajax({
                url: form.attr('action'),
                method: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                    getPlantVisitorFormSection();
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.send-approved-not-approved-notify');
        $(document).on('click', '.send-approved-not-approved-notify', function() {
            var formId = $(this).closest('.panel-main').attr('data-id');
            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/send_approve_not_approved_notify') ?>",
                method: "POST",
                data: {
                    formId: formId,
                    type: $(this).attr('data-type')
                },
                dataType: 'json',
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('change', 'input[type="radio"][name="form_status"]');
        $(document).on('change', 'input[type="radio"][name="form_status"]', function() {
            var type = $(this).val();
            if (type == "Approved") {
                $(this).closest('.plant-visit-form-approval-section').find('textarea[name="reject_note"]').prop('required', false);
                $(this).closest('.plant-visit-form-approval-section').find('.reject_reason_section').addClass('hide');
                $(this).closest('.plant-visit-form-approval-section').find('input[name="visit_date_time"]').prop('required', true);
                $(this).closest('.plant-visit-form-approval-section').find('.visit_date_section').removeClass('hide');
            } else {
                $(this).closest('.plant-visit-form-approval-section').find('textarea[name="reject_note"]').prop('required', true);
                $(this).closest('.plant-visit-form-approval-section').find('.reject_reason_section').removeClass('hide');
                $(this).closest('.plant-visit-form-approval-section').find('input[name="visit_date_time"]').prop('required', false);
                $(this).closest('.plant-visit-form-approval-section').find('.visit_date_section').addClass('hide');
            }
        });

        $(document).off('keydown', 'input[name="visit_date_time"]');
        $(document).on('keydown', 'input[name="visit_date_time"]', function() {
            return false;
        });

        $(document).off('input', 'textarea[name="reject_note"]');
        $(document).on('input', 'textarea[name="reject_note"]', function() {
            var formgroup = $(this).closest('.form-group');
            formgroup.find('.error').remove();
            var type = $(this).val();
            if (this.value == "" || this.value == null) {
                formgroup.append("<span class='error text-danger'>Reject Reason Required...</span>")
            }
        });

    });

    function getDayNameByDate(dateString) {
        const [datePart, timePart] = dateString.split(' ');
        const [day, month, year] = datePart.split('-');
        const date = new Date(year, month - 1, day);
        return date.toLocaleDateString('en-US', {
            weekday: 'long'
        });
    }

    function renderRecaptcha() {
        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
            'size': 'invisible',
            'callback': function(response) {
                console.log("reCAPTCHA solved");
            }
        });
    }

    function pvf_sendOTP(sendType = "send") {
        var sendvia = $('.pvf-verification-section input[name="otp_type"]:checked').val();
        var phoneNumber = $('.pvf-verification-section input[name="phonenumber"]:checked').val();
        if (sendType == "send") {
            $('#pvf-otp-send').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
        } else {
            $('#pvf-resend-otp').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
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
                url: "<?= admin_url('leads_plant_visit_forms/otp_verification') ?>",
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
                            $('#pvf-otp-send').prop('disabled', false).html('Send OTP');
                        } else {
                            $('#pvf-resend-otp').prop('disabled', false).html('Resend OTP');
                        }
                        startResendCountdown();
                        alert_float('success', response.message);
                    } else {
                        if (sendType == "send") {
                            $('#pvf-otp-send').prop('disabled', false).html('Send OTP');
                        } else {
                            $('#pvf-resend-otp').prop('disabled', false).html('Resend OTP');
                        }
                        alert_float('danger', response.message);
                    }
                },
                complete: function() {
                    if (sendType == "send") {
                        $('#pvf-otp-send').prop('disabled', false).html('Send OTP');
                    } else {
                        $('#pvf-resend-otp').prop('disabled', false).html('Resend OTP');
                    }
                }
            });
        } else {
            if (sendType == "send") {
                $('#pvf-otp-send').prop('disabled', false).html('Send OTP');
            } else {
                $('#pvf-resend-otp').prop('disabled', false).html('Resend OTP');
            }
            alert_float('danger', error);
        }
    }

    function pvf_verifyOTP(sendType = "send") {
        var sendvia = $('input[name="otp_type"]:checked').val();
        var phoneNumber = $('.pvf-verification-section input[name="phonenumber"]:checked').val();
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
                $('#pvf-resend-otp').prop('disabled', true);
                $('#pvf-verify-otp').prop('disabled', true).html('Verifying... <i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "<?= admin_url('leads_plant_visit_forms/otp_verification') ?>",
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
                            $('#pvf-resend-otp').prop('disabled', false);
                            $('#pvf-verify-otp').prop('disabled', false).html('Verify OTP');
                            alert_float('success', response.message);
                            getPlantVisitorFormSection(true);
                        } else {
                            $('#pvf-resend-otp').prop('disabled', false);
                            $('#pvf-verify-otp').prop('disabled', false).html('Verify OTP');
                            alert_float('danger', response.message);
                        }
                    },
                    complete: function() {
                        $('#pvf-resend-otp').prop('disabled', false);
                        $('#pvf-verify-otp').prop('disabled', false).html('Verify OTP');
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
            $('.pvf-otp-section').removeClass('hide');
            $('#pvf-otp-send').addClass('hide');
        } else {
            $('.otp-input').val('');
            $('.pvf-otp-section').addClass('hide');
            $('.pvf-otp-section').find(".text-message").remove();
            $('#pvf-otp-send').removeClass('hide');
            $('#pvf-resend-otp').prop('disabled', false).html("Resend OTP");
        }
    }

    function startResendCountdown() {
        clearInterval(resendTimeout);
        var secondsLeft = 60;
        $('#pvf-resend-otp').prop('disabled', true).html('Resend OTP (' + secondsLeft + 's)');
        resendTimeout = setInterval(function() {
            secondsLeft--;
            if (secondsLeft > 0) {
                $('#pvf-resend-otp').prop('disabled', true).html('Resend OTP (' + secondsLeft + 's)');
            } else {
                clearInterval(resendTimeout);
                $('#pvf-resend-otp').prop('disabled', false).html('Resend OTP');
            }
        }, 1000);
    }

    function sendVisitorForm(type, element) {
        var panel = $(element).closest('.panel-main');
        var id = panel.attr('data-id');
        if (type != "") {
            $.ajax({
                url: "<?php echo admin_url('leads_plant_visit_forms/send_form') ?>",
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
                    getPlantVisitorFormSection();
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

    .pvf-verification-section label {
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

    .profile-img {
        border: 1px solid black;
        border-radius: 10px;
    }
</style>