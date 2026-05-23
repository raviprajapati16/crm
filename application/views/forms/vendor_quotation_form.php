<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1">
    <title>Vendor Quotation Form</title>
    <?php app_external_form_header(); ?>
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

    .form-container .panel {
        border-width: 10px;
        border-color: #fff !important;
        background-color: #fff;
    }



    .panel-heading {
        padding: 25px;
        background: rgb(13, 87, 46);
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
        border-radius: 20px;
        margin: 15px;
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

    .mbottom10 {
        margin-bottom: 10px;
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

    .logo {
        width: 40%;
        margin: auto;
        margin-bottom: 10px;
    }

    @media only screen and (max-width: 768px) {
        .quotation-text {
            font-size: 15px !important;
        }

        .panel-heading {
            padding: 2px;
        }

        .logo {
            margin-top: 10px !important;
            width: 90% !important;
        }
    }
</style>


<body class="vendor-quotation-form <?php echo $form['formkey']; ?>">
    <!-- Form Section -->
    <div class="container form-container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-12">
                                <?php get_company_logo('/') ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="quotation-text">VENDOR QUOTATION FORM</div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php if ($form['is_active']) { ?>

                            <div class="row">
                                <div class="col-md-12 quotation-date">
                                    Date : <?= (!empty($form['quotation_date'])) ? date('d-m-Y', strtotime($form['quotation_date'])) : '' ?>
                                    <div class="pull-right"><button type="button" data-toggle="tooltip" data-title="Add New Item" class="btn btn-info btn-add-new-item"><i class="fa fa-plus"></i></button></div>
                                </div>
                                <?= all_type_input_render([
                                    "label" => "Supplier Name",
                                    "id" => "supplier_name",
                                    "name" => "supplier_name",
                                    "type" => "text",
                                    "selected_value" => (isset($form['supplier_name']) && !empty($form['supplier_name'])) ? $form['supplier_name'] : '',
                                    "is_required" => true,
                                    "form" => "mainForm",
                                ], 'col-md-4', true);
                                ?>
                                <?= all_type_input_render([
                                    "label" => "GST IN",
                                    "id" => "gst_in",
                                    "name" => "gst_in",
                                    "type" => "text",
                                    "selected_value" => (isset($form['gst_in']) && !empty($form['gst_in'])) ? $form['gst_in'] : '',
                                    "is_required" => true,
                                    "form" => "mainForm",
                                    "className" => "gst_validation"
                                ], 'col-md-4', true);
                                ?>
                                <?= all_type_input_render([
                                    "label" => "Address",
                                    "id" => "address",
                                    "name" => "address",
                                    "type" => "textarea",
                                    "rows" => 4,
                                    "is_required" => true,
                                    "selected_value" => (isset($form['address']) && !empty($form['address'])) ? $form['address'] : '',
                                    "form" => "mainForm",
                                ], 'col-md-4', true);
                                ?>
                                <div class="col-md-12 table-col b-top b-left b-right">
                                    <table style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th><b>Sr. No.</b></th>
                                                <th><b>Description Of Service</b></th>
                                                <th><b>HSN / SAC</b></th>
                                                <th><b>Quantity </b></th>
                                                <th><b>Unit </b></th>
                                                <th><b>Price INR</b></th>
                                                <th><b>Amount INR</b></th>
                                                <th style="width: 100px;"><b>Action</b></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($form_items_data)) {
                                                foreach ($form_items_data as $k1 => $item) { ?>
                                                    <tr class="quotation-item-row" data-id="<?= $item['id'] ?>" data-desc-disable="<?= (is_numeric($item['created_by'])) ? 1 : 0 ?>">
                                                        <td><?= $k1 + 1 ?></td>
                                                        <td><?= $item['service_description'] ?></td>
                                                        <td><?= $item['hsn_sac'] ?></td>
                                                        <td><?= $item['qty'] ?></td>
                                                        <td><?= $item['unit'] ?></td>
                                                        <td><?= $item['price_in_inr'] ?></td>
                                                        <td><?= $item['amount_in_inr'] ?></td>
                                                        <td>
                                                            <button type="button" data-id="<?= $item['id'] ?>" class="btn btn-info btn-xs quotation-item-edit"><i class="fa fa-edit"></i></button>
                                                            <?php if (!is_numeric($item['created_by'])) { ?>
                                                                <a href="<?= site_url('forms/vqf/' . $form['formkey'] . '/delete_item/' . $item['id']) ?>" class="btn btn-danger mleft5 btn-xs quotation-item-delete"><i class="fa fa-trash"></i></a>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr class="not-avl-tr">
                                                    <td colspan="8" class="text-center">Items not available.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php echo form_open_multipart(site_url('forms/vqf/' . $form['formkey'] . '/main_form_submit'), array('id' => "mainForm")); ?>
                                <input type="hidden" name="id" value="<?= $form['id']; ?>" form="mainForm" />
                                <div class="col-md-12 mtop10">
                                    <div class="terms-conditions-title"><strong> Terms & Conditions : </strong></div>
                                    <div class="mtop10">
                                        <textarea name="terms_conditions" id="terms_conditions" class="texteditor" form="mainForm"><?= $form['terms_conditions']; ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 mtop10">
                                    <div class="terms-conditions-title"><strong> Extra Notes : </strong></div>
                                    <div class="mtop10">
                                        <textarea name="notes" id="notes" class="texteditor" form="mainForm"><?= $form['notes']; ?></textarea>
                                    </div>
                                </div>
                                <?= all_type_input_render([
                                    "label" => "<b>Document Upload</b>",
                                    "id" => "file",
                                    "name" => "file",
                                    "type" => "fileupload",
                                    "is_required" => false,
                                    "selected_value" => (isset($form['file']) && !empty($form['file'])) ? $form['file'] : '',
                                    "preview_url" => site_url('download/file/vendor_quotation_files/' . $form['id']),
                                    "file_delete_class" => "delete-quotation-file",
                                    "form" => "mainForm",
                                ], 'col-md-12 mtop10', false);
                                ?>
                                <div class="col-md-12 mtop10">
                                    <?php $staffData = get_staff($form['created_by']);
                                    if (!empty($staffData)) {
                                        echo $staffData->email_signature;
                                    }
                                    ?>
                                </div>
                                <div class="col-md-12 text-center mtop10">
                                    <button type="submit" class="btn btn-info mbottom10" form="mainForm">Submit</button>
                                </div>
                                <?php echo form_close(); ?>
                                <?php echo form_open_multipart(site_url('forms/vqf/' . $form['formkey'] . '/add_update_item'), array('id' => "newItemForm", 'class' => 'disable-on-submit'));
                                echo form_close(); ?>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if ($form['form_status'] == "pending") { ?>
                                        <div class="alert alert-success text-center">
                                            Quotation form successfully submitted and approval pending.
                                        </div>
                                    <?php } else if ($form['form_status'] == "approved") { ?>
                                        <div class="alert alert-success text-center">
                                            Quotation form successfully submitted and approved.
                                        </div>
                                    <?php } else { ?>
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
                                                            <b>Form ID : </b> #<?= leadFormIdRender("VQF", $form['lead_id'], $form['id']) ?>
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
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php app_external_form_footer(); ?>
    <script type="text/javascript" id="tinymce-js" src="<?= site_url('assets/plugins/tinymce/tinymce.min.js') ?>"></script>
    <script>
        var form_id = '#<?php echo $form['formkey']; ?>';
        var validator;
        $(function() {
            tinyMceEditor('.texteditor', 150);
            $(document).on('click', '.quotation-item-delete', function(e) {
                e.preventDefault();
                if (confirm("Are you sure you want to perform this action ?")) {
                    window.location.href = $(this).attr('href');
                }
            });

            $(document).on('click', '.btn-add-new-item', function(e) {
                $('.new-tr').remove();
                $('.edit-tr').remove();
                $('.quotation-item-row').removeClass('hide');
                if ($('.new-tr').length == 0) {
                    $('.not-avl-tr').remove();
                    var countRow = $('table').find('input[name="id"]').length + 1;
                    var html = '';
                    html += '<tr class="new-tr"><input type="hidden" name="id" value="" form="newItemForm"/>' +
                        '<td>' + (countRow) + '</td>' +
                        '<td><textarea id="service_description" class="form-control" name="service_description" row="3" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="hsn_sac" class="form-control" name="hsn_sac" form="newItemForm"/></td>' +
                        '<td><input type="number" id="qty" class="form-control" name="qty" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="unit" class="form-control" name="unit" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="price_in_inr" class="form-control decimal-value" name="price_in_inr" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="amount_in_inr" class="form-control decimal-value" name="amount_in_inr" form="newItemForm" readonly="true"/></td>' +
                        '<td><button type="button" class="btn btn-danger btn-xs delete-row"><i class="fa fa-trash"></i></button>' +
                        '<button type="submit" class="btn btn-success mleft5 btn-xs" form="newItemForm"><i class="fa fa-check"></i></button></td>' +
                        '</tr>';
                    $('table').find('tbody').append(html);
                }
            });

            $(document).on('click', '.quotation-item-edit', function(e) {
                $('.new-tr').remove();
                $('.edit-tr').remove();
                $('.quotation-item-row').removeClass('hide');
                if ($('.edit-tr').length == 0) {
                    var tr = $(this).closest('.quotation-item-row');
                    var descHtml = '<textarea id="service_description" class="form-control" name="service_description" row="3" form="newItemForm" required="true"/>';
                    if (tr.attr('data-desc-disable') == "1") {
                        descHtml = tr.find('td:nth-child(2)').text();
                    }
                    tr.addClass('hide');
                    var countRow = $('table').find('tr').length;
                    var html = '';
                    html += '<tr class="edit-tr"><input type="hidden" name="id" value="" form="newItemForm"/>' +
                        '<td>' + (countRow + 1) + '</td>' +
                        '<td>' + descHtml + '</td>' +
                        '<td><input type="text" id="hsn_sac" class="form-control" name="hsn_sac" form="newItemForm"/></td>' +
                        '<td><input type="number" id="qty" class="form-control" name="qty" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="unit" class="form-control" name="unit" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="price_in_inr" class="form-control decimal-value" name="price_in_inr" form="newItemForm" required="true"/></td>' +
                        '<td><input type="text" id="amount_in_inr" class="form-control decimal-value" name="amount_in_inr" form="newItemForm" readonly="true"/></td>' +
                        '<td><button type="button" class="btn btn-danger btn-xs cancel-row-update"><i class="fa fa-times-circle"></i></button>' +
                        '<button type="submit" class="btn btn-success btn-xs mleft5" form="newItemForm"><i class="fa fa-check"></i></button></td>' +
                        '</tr>';
                    $(tr).after(html);
                    var edit_tr = $('.edit-tr');
                    edit_tr.find('input[name="id"]').val($(this).attr('data-id'));
                    edit_tr.find('textarea[name="service_description"]').val(tr.find('td:nth-child(2)').text());
                    edit_tr.find('input[name="hsn_sac"]').val(tr.find('td:nth-child(3)').text());
                    edit_tr.find('input[name="qty"]').val(tr.find('td:nth-child(4)').text());
                    edit_tr.find('input[name="unit"]').val(tr.find('td:nth-child(5)').text());
                    edit_tr.find('input[name="price_in_inr"]').val(tr.find('td:nth-child(6)').text());
                    edit_tr.find('input[name="amount_in_inr"]').val(tr.find('td:nth-child(7)').text());
                }
            });

            $(document).on('click', '.cancel-row-update', function(e) {
                $('.new-tr').remove();
                $('.edit-tr').remove();
                $('.quotation-item-row').removeClass('hide');
            });

            $(document).on('input', '.decimal-value', function() {
                var value = $(this).val();
                var regex = /^\d*\.?\d{0,2}$/;
                if (!regex.test(value)) {
                    $(this).val(value.substring(0, value.length - 1));
                }
            });

            $(document).on('click', '.delete-row', function() {
                $(this).closest('.new-tr').remove();
            });

            $(document).on('click', 'button[type="submit"]', function(e) {
                e.preventDefault();
                var formId = $(this).attr("form");
                var isValid = true;
                if (formId == "newItemForm") {
                    $('input[form="' + formId + '"],textarea[form="' + formId + '"]').each(function(index, item) {
                        $(this).siblings('.error').remove();
                        if ($(this).prop('required')) {
                            if (this.value == "" || this.value == null) {
                                $(this).after('<span class="text-danger error">* Required</span>');
                                isValid = false;
                            }
                        }
                    });
                }
                if (formId == "mainForm") {
                    $('input[form="' + formId + '"],textarea[form="' + formId + '"]').each(function(index, item) {
                        $(this).siblings('.error').remove();
                        if ($(this).prop('type') == "file") {
                            var fileInput = $(this);
                            if (!quotationValidateFileInput(fileInput[0])) {
                                isValid = false;
                            }

                        } else if ($(this).prop('required')) {
                            if (this.value == "" || this.value == null) {
                                $(this).after('<span class="text-danger error">This Field Required</span>');
                                isValid = false;
                            }
                        }
                    });
                }

                if (isValid) {
                    $('#' + formId).submit();
                }
            });

            $(document).on('input', 'input[form="newItemForm"],textarea[form="newItemForm"]', function(e) {
                $(this).siblings('.error').remove();
                if ($(this).prop('required')) {
                    if ($(this).val() == "" || $(this).val() == null) {
                        $(this).after('<span class="text-danger error">* Required</span>')
                    }
                }
            });

            $(document).on('change', 'input[type="file"]', function() {
                var formgroup = $(this).closest('.form-group');
                formgroup.find('.file-error').remove();
                const file = this.files[0];
                var validation = quotationValidateFileInput(this);
                if (!validation) {
                    $(this).val('');
                } else if (file && validation) {
                    const fileName = file.name
                    const fileUrl = URL.createObjectURL(file);
                    if (formgroup.find('.file-preview-section').length === 0) {
                        formgroup.append("<span class='file-preview-section'></span>");
                    }
                    const previewHtml = `
                    <span class='file-preview-section'>
                        Preview: <a href='${fileUrl}' class='preview-file' target='_blank'>${fileName}</a>
                        <i class='fa fa-trash text-danger delete-quotation-new-file' aria-hidden='true'></i>
                    </span>
                `;
                    formgroup.find('.file-preview-section').html(previewHtml);
                }
            });

            $(document).on('click', '.delete-quotation-new-file', function() {
                var formgroup = $(this).closest('.form-group');
                if (confirm("Are you sure you want perform this action?")) {
                    formgroup.find('.file-preview-section').remove();
                    formgroup.find('input[type="file"]').val('');
                }
            });

            $(document).on('click', '.delete-quotation-file', function() {
                var formgroup = $(this).closest('.form-group');
                var formId = $('#mainForm').find('input[name="id"]').val();
                if (formId != "" && formId != null) {
                    if (confirm("Are you sure you want to delete this file?")) {
                        $.ajax({
                            url: "<?php echo site_url('forms/delete_quotation_file') ?>",
                            method: "POST",
                            data: {
                                id: formId
                            },
                            dataType: 'json'
                        }).done(function(result) {
                            if (result.success) {
                                formgroup.find('.file-preview-section').remove();
                            }
                        });
                    }
                }
            });

            $(document).on('input', '.gst_validation', function() {
                var input = $(this).val().toUpperCase();
                var filteredInput = input.replace(/[^0-9A-Z]/g, '');
                if (filteredInput.length > 15) {
                    filteredInput = filteredInput.slice(0, 15);
                }
                $(this).val(filteredInput);
            });

            $(document).on('input', 'input,textarea', function() {
                $(this).closest('.form-group').find('span.error').remove();
            });

            $(document).on('input', "input[name='qty']", function() {
                var parent = $(this).closest('tr');
                amountCalculation(parent);
            });

            $(document).on('input', "input[name='price_in_inr']", function() {
                console.log("test2")
                var parent = $(this).closest('tr');
                amountCalculation(parent);
            });

        });

        function amountCalculation(parent) {
            var qty = Number(parent.find("input[name='qty']").val());
            var price = Number(parent.find("input[name='price_in_inr']").val());
            var amount = (price * qty).toFixed(2);
            parent.find("input[name='amount_in_inr']").val(amount);
        }

        function quotationValidateFileInput(fileInput) {
            var maxSize = 5 * 1024 * 1024;
            var allowedExtensions = ["jpg", "jpeg", "png", "pdf"];
            var isValid = true;
            var errors = [];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                var fileSize = file.size;
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
            $(fileInput).siblings('.file-error').remove();
            if (!isValid) {
                errors.forEach(function(error) {
                    $("<div class='validation-error text-danger file-error'>").text(error).insertAfter($(fileInput));
                });
            }
            return isValid;
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
    </script>
    <style>
        .logo {
            width: 40%;
            margin: auto;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 36px;
            font-weight: 600;
            text-align: center;
        }

        .company-address {
            font-size: 14px;
            font-weight: 400;
            text-align: center;
        }

        .panel-heading {
            border-bottom: unset;
        }

        .b-top {
            border-top: 1px solid grey !important;
        }

        .b-bottom {
            border-bottom: 1px solid grey !important;
        }

        .b-right {
            border-right: 1px solid grey !important;
        }

        .b-left {
            border-left: 1px solid grey !important;
        }

        .quotation-text {
            font-size: 25px;
            text-align: center;
            padding: 10px;
            font-weight: bolder;
        }

        .quotation-date {
            font-size: 17px;
            padding: 10px;
        }

        .mtop10 {
            margin-top: 10px;
        }

        .mleft5 {
            margin-left: 5px;
        }

        .table-col {
            padding-left: 0px;
            padding-right: 0px;
        }

        .form-control[readonly] {
            background-color: #d8d8d8 !important;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
        }

        th {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid grey;
        }

        th:not(:last-child) {
            border-right: 1px solid grey;
        }

        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid grey;
        }

        td:not(:first-child) {
            border-left: 1px solid grey;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        @media (max-width: 600px) {
            .table-wrapper {
                overflow-x: auto;
            }

            table {
                width: 100%;
                display: block;
            }

            th,
            td {
                white-space: nowrap;
            }
        }
    </style>
</body>

</html>