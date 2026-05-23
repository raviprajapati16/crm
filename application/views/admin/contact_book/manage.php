<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/css/intlTelInput.css" />
<style>
#contact_attachments_wrapper {
    margin-top: 15px;
}

.attachment-item {
    margin-bottom: 15px;
}

.attachment-wrapper {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 10px;
    background-color: #fafafa;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: box-shadow 0.2s ease;
}

.attachment-wrapper:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.attachment-preview {
    font-size: 28px;
    /* Smaller icon */
    text-align: center;
    color: #666;
    margin-bottom: 10px;
}

.attachment-info {
    text-align: center;
}

.attachment-name {
    font-weight: 500;
    font-size: 13px;
    margin-bottom: 8px;
    word-break: break-word;
}

.attachment-actions {
    display: flex;
    justify-content: center;
    gap: 6px;
}

.attachment-actions .btn-xs {
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 3px;
}

@media (max-width: 767px) {
    .attachment-actions {
        flex-direction: column;
        align-items: center;
    }
}


#contact-attachments-preview {
    margin-top: 15px;
}

.existing-files h5 {
    font-size: 14px;
    margin-bottom: 10px;
    font-weight: 600;
}

.existing-file {
    margin-bottom: 15px;
}

.file-wrapper {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 10px;
    background-color: #fefefe;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow 0.2s ease;
}

.file-wrapper:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.file-icon {
    font-size: 28px;
    /* smaller icon */
    color: #555;
    margin-bottom: 8px;
    text-align: center;
}

.file-name {
    font-size: 13px;
    font-weight: 500;
    text-align: center;
    margin-bottom: 8px;
    word-break: break-word;
}

.file-actions {
    display: flex;
    justify-content: center;
    gap: 6px;
}

.file-actions .btn-xs {
    padding: 3px 8px;
    font-size: 11px;
    border-radius: 3px;
}

@media (max-width: 767px) {
    .file-actions {
        flex-direction: column;
        align-items: center;
    }
}

.iti {
    width: 100% !important;
}

.iti__input {
    width: 100% !important;
    padding-left: 52px;
}
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('contact_book', '', 'create')) { ?>
                            <a href="javascript:;" onclick="openContactModal()" class="btn btn-info">Add Contact</a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php render_datatable(array(
                            _l('Sr. No.'),
                            _l('Category'),
                            _l('Company'),
                            _l('Name'),
                            _l('Email'),
                            _l('Phone'),
                            _l('State'),
                            _l('Country'),
                            _l('Contact Owner'),
                            _l('Action'),
                        ), 'contact-list'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact View Modal -->
<div class="modal fade" id="contact_view_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Contact Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <td id="view_name"></td>
                                    </tr>
                                    <tr>
                                        <th>Company</th>
                                        <td id="view_company"></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td id="view_email"></td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td id="view_phone"></td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <td id="view_category"></td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td id="view_address"></td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td id="view_city"></td>
                                    </tr>
                                    <tr>
                                        <th>State</th>
                                        <td id="view_state"></td>
                                    </tr>
                                    <tr>
                                        <th>Country</th>
                                        <td id="view_country"></td>
                                    </tr>
                                    <tr>
                                        <th>Details</th>
                                        <td id="view_details"></td>
                                    </tr>
                                    <tr>
                                        <th>Contact Owner</th>
                                        <td id="view_owner"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Files Section -->
            <div class="row" id="contact_attachments_wrapper">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <h4>Attachments</h4>
                    </div>
                    <div id="contact_attachments" class="col-md-12 mtop15"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <?php if (has_permission('contact_book', '', 'edit')) { ?>
                <button type="button" id="btn_edit_contact" class="btn btn-info"><?php echo _l('edit'); ?></button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Contact Edit Modal -->
<div class="modal fade" id="contact_modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <?php echo form_open_multipart(admin_url('contact_book/save'), array("id" => "contactForm")); ?>
        <input type="hidden" id="contact_id" name="id" form="contactForm" value="" />
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add Contact</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php
                        echo render_select_with_input_group("category", $categories, ['id', 'name'], "Category", "", '<a href="#" onclick="new_contact_category();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', []);
                        ;
                        ?>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" name="company" id="company" class="form-control" maxlength="40"
                                form="contactForm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" maxlength="50"
                                form="contactForm" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" name="firstname" id="firstname" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" name="lastname" id="lastname" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="phone">Phone</label><br>
                            <input type="text" name="phone" id="phone" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" name="state" id="state" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" name="country" id="country" class="form-control" maxlength="25"
                                form="contactForm" />
                        </div> -->
                        <?php
                        $countries = get_all_countries();
                        echo render_select('country', $countries, array('country_id', array('short_name')), 'Country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex'), "form" => "contactForm", "data-size" => "10"));
                        ?>
                    </div>



                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" class="form-control" rows="5"
                                form="contactForm"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="details">Details</label>
                            <textarea name="details" id="details" class="form-control" rows="5"
                                form="contactForm"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="file-upload" class="control-label">Attachments</label>
                            <div class="dropzone" id="contact-attachments-upload"></div>
                            <div id="contact-attachments-preview" class="mtop15">
                                <!-- Existing files will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="contactForm"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/utils.js"></script>
<?php init_tail(); ?>
<script>
const input = document.querySelector("#phone");
const iti = window.intlTelInput(input, {
    initialCountry: "in",
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17/build/js/utils.js"
});

var contactAttachmentsDropzone;
var contactCurrentAttachments = [];
Dropzone.autoDiscover = false;


function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

var contactId = getUrlParameter('id');
if (contactId && contactId.trim() !== '') {
    setTimeout(function() {
        viewContactDetails(contactId);
    }, 500);
}

function getCountryCode() {
    return iti.getSelectedCountryData().dialCode;
}

function setCountryByDialCode(dialCode) {
    const countries = window.intlTelInputGlobals.getCountryData();
    const country = countries.find(c => c.dialCode == dialCode.toString());
    if (country) {
        iti.setCountry(country.iso2);
    }
}

$(function() {
    $(window).off('beforeunload');

    initDataTable('.table-contact-list', admin_url + 'contact_book', [0], [0]);

    if (typeof(contactAttachmentsDropzone) != 'undefined') {
        contactAttachmentsDropzone.destroy();
    }

    contactAttachmentsDropzone = new Dropzone("#contact-attachments-upload", {
        url: admin_url + 'contact_book/upload_attachment',
        paramName: "file",
        uploadMultiple: true,
        parallelUploads: 15,
        maxFiles: 10,
        maxFilesize: 10, // MB
        acceptedFiles: ".png,.jpg,.jpeg,.pdf,.doc,.docx,.xls,.xlsx",
        autoProcessQueue: false,
        createImageThumbnails: true,
        previewTemplate: '<div class="dz-preview dz-file-preview"><div class="dz-image"><img data-dz-thumbnail /></div><div class="dz-details"><div class="dz-filename"><span data-dz-name></span></div><div class="dz-size"><span data-dz-size></span></div></div><div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div><div class="dz-error-message"><span data-dz-errormessage></span></div></div>',
        dictDefaultMessage: "<?php echo _l('drop_files_here_to_upload'); ?>",
        dictFallbackMessage: "<?php echo _l('browser_not_support_drag_and_drop'); ?>",
        dictFileTooBig: "<?php echo _l('file_exceeds_maxFileSize'); ?>",
        dictInvalidFileType: "<?php echo _l('not_allowed_file_type'); ?>",
        dictResponseError: "<?php echo _l('server_responded_with_code'); ?>",
        dictCancelUpload: "<?php echo _l('cancel_upload'); ?>",
        dictCancelUploadConfirmation: "<?php echo _l('are_you_sure_cancel_upload'); ?>",
        dictRemoveFile: "<?php echo _l('remove_file'); ?>",
        dictMaxFilesExceeded: "<?php echo _l('you_can_not_upload_any_more_files'); ?>",
        init: function() {
            this.on("success", function(file, response) {
                response = JSON.parse(response);
                if (response.success) {
                    contactCurrentAttachments.push({
                        id: response.attachment_id,
                        file_name: file.name
                    });
                }
            });

            this.on("addedfile", function(file) {
                // Create the remove button
                var removeButton = Dropzone.createElement(
                    "<button class='btn btn-danger btn-xs btn-remove-attachment' title='Remove File'><i class='fa fa-times'></i></button>"
                );

                // Listen to the click event
                removeButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Remove the file from dropzone
                    contactAttachmentsDropzone.removeFile(file);
                });

                // Add the button to the file preview element
                file.previewElement.appendChild(removeButton);
            });

            this.on("complete", function(file) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles()
                    .length === 0) {
                    var $submitBtn = $('button[type="submit"]');
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html("Save");
                    $('#contact_modal').modal('hide');
                    $('.table-contact-list').DataTable().ajax.reload();
                }
            });
        }
    });

    $('#contactForm').appFormValidator({
        rules: {
            category: 'required',
            email: {
                email: true,
            },
            phone: {
                phoneNumber: true
            }
        },
        errorPlacement: function(error, element) {
            var formGroup = $(element).closest('.form-group');
            formGroup.append(error);
        },
        submitHandler: function(form) {
            var $submitBtn = $('button[type="submit"]');
            $submitBtn.prop('disabled', true);
            var originalText = $submitBtn.html();
            $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');

            var formData = new FormData(form);

            if ($('#phone').val().trim() != '') {
                const countryCode = getCountryCode();
                formData.append('country_dial_code', countryCode);
            }

            var contactId = $('#contact_id').val();
            if (contactId) {
                formData.append('id', contactId);
            }

            if (contactCurrentAttachments.length > 0) {
                formData.append('attachments', JSON.stringify(contactCurrentAttachments));
            }

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (contactAttachmentsDropzone.getQueuedFiles().length > 0) {
                            contactAttachmentsDropzone.options.params = {
                                contact_id: response.id,
                                "<?php echo $this->security->get_csrf_token_name(); ?>": "<?php echo $this->security->get_csrf_hash(); ?>",

                            };
                            contactAttachmentsDropzone.processQueue();
                        }
                        alert_float('success', response.message ||
                            'Contact saved successfully');
                    } else {
                        alert_float('danger', response.message ||
                            'An error occurred while saving');
                    }

                    var $submitBtn = $('button[type="submit"]');
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html("Save");
                    $('#contact_modal').modal('hide');
                    $('.table-contact-list').DataTable().ajax.reload();
                },
                error: function() {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(originalText);
                    alert_float('danger', 'Unexpected error occurred');
                }
            });

            return false; // Prevent default form submission
        }
    });

    $('#contact_modal').on('hidden.bs.modal', function() {
        contactAttachmentsDropzone.removeAllFiles(true);
        contactCurrentAttachments = [];
        $('#contact-attachments-preview').empty();
    });

    $.validator.addMethod("phoneNumber", function(value, element) {
        if (value === '') return true;
        return this.optional(element) || /^[0-9\+\-\(\)\s]{6,20}$/.test(value);
    }, "Please enter a valid phone number");

    $('#btn_edit_contact').on('click', function() {
        var contactId = $(this).data('id');
        $('#contact_view_modal').modal('hide');
        openContactModal(contactId);
    });
});

function viewContactDetails(id) {
    $.ajax({
        url: admin_url + 'contact_book/get/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log(response);
                var contact = response.data;
                var fullName = contact.firstname + ' ' + contact.lastname;

                $('#view_name').text(fullName);
                $('#view_company').text(contact.company || '-');
                $('#view_email').text(contact.email || '-');
                $('#view_phone').text(contact.phone || '-');
                $('#view_category').text(contact.category_name || '-');
                $('#view_address').text(contact.address || '-');
                $('#view_city').text(contact.city || '-');
                $('#view_state').text(contact.state || '-');
                $('#view_country').text(contact.country || '-');
                $('#view_details').text(contact.details || '-');
                $('#view_owner').text(contact.contact_owner || '-');

                $('#btn_edit_contact').data('id', contact.id);

                loadContactAttachments(contact.id);

                $('#contact_view_modal').modal('show');
            } else {
                alert_float('danger', 'Error loading contact details');
            }
        },
        error: function() {
            alert_float('danger', 'Error loading contact details');
        }
    });
}

var delete_permission = "<?= has_permission('contact_book', '', 'delete'); ?>";

function loadContactAttachments(contactId) {
    $.ajax({
        url: admin_url + 'contact_book/get_attachments/' + contactId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var $attachmentsContainer = $('#contact_attachments');
            $attachmentsContainer.empty();

            if (response.success && response.attachments.length > 0) {
                $('#contact_attachments_wrapper').show();

                var attachmentsHtml = '<div class="row">';
                $.each(response.attachments, function(i, attachment) {
                    var fileIcon = getFileIcon(attachment.filetype);

                    attachmentsHtml += '<div class="col-md-4 col-sm-6 attachment-item">';
                    attachmentsHtml += '<div class="attachment-wrapper">';
                    attachmentsHtml += '<div class="attachment-preview">' + fileIcon + '</div>';
                    attachmentsHtml += '<div class="attachment-info">';
                    attachmentsHtml += '<div class="attachment-name">' + attachment.file_name +
                        '</div>';
                    attachmentsHtml += '<div class="attachment-actions">';
                    attachmentsHtml += '<a target="_blank" href="' + admin_url +
                        'contact_book/download_attachment/' + attachment.id +
                        '" class="btn btn-success btn-xs" title="Download"><i class="fa fa-eye"></i></a> ';
                    if (delete_permission) {
                        attachmentsHtml +=
                            '<a href="javascript:;" onclick="deleteContactAttachment(' + attachment
                            .id + ', ' + contactId +
                            ')" class="btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    attachmentsHtml += '</div>'; // end .attachment-actions
                    attachmentsHtml += '</div>'; // end .attachment-info
                    attachmentsHtml += '</div>'; // end .attachment-wrapper
                    attachmentsHtml += '</div>'; // end .col

                    // Create new row every 3 items
                    if ((i + 1) % 3 === 0 && i !== response.attachments.length - 1) {
                        attachmentsHtml += '</div><div class="row">';
                    }
                });
                attachmentsHtml += '</div>';

                $attachmentsContainer.html(attachmentsHtml);
            } else {
                $('#contact_attachments_wrapper').hide();
            }
        },
        error: function() {
            $('#contact_attachments_wrapper').hide();
        }
    });
}

function getFileIcon(filetype) {
    var icon = '<i class="fa fa-file-o"></i>';

    if (filetype) {
        filetype = filetype.toLowerCase();

        if (filetype.indexOf('pdf') !== -1) {
            icon = '<i class="fa fa-file-pdf-o"></i>';
        } else if (filetype.indexOf('doc') !== -1 || filetype.indexOf('word') !== -1) {
            icon = '<i class="fa fa-file-word-o"></i>';
        } else if (filetype.indexOf('xls') !== -1 || filetype.indexOf('excel') !== -1 || filetype.indexOf('sheet') !== -
            1) {
            icon = '<i class="fa fa-file-excel-o"></i>';
        } else if (filetype.indexOf('ppt') !== -1 || filetype.indexOf('powerpoint') !== -1) {
            icon = '<i class="fa fa-file-powerpoint-o"></i>';
        } else if (filetype.indexOf('jpg') !== -1 || filetype.indexOf('jpeg') !== -1 || filetype.indexOf('png') !== -
            1 ||
            filetype.indexOf('gif') !== -1 || filetype.indexOf('bmp') !== -1 || filetype.indexOf('image') !== -1) {
            icon = '<i class="fa fa-file-image-o"></i>';
        } else if (filetype.indexOf('zip') !== -1 || filetype.indexOf('rar') !== -1 || filetype.indexOf('archive') !== -
            1) {
            icon = '<i class="fa fa-file-archive-o"></i>';
        } else if (filetype.indexOf('text') !== -1 || filetype.indexOf('txt') !== -1) {
            icon = '<i class="fa fa-file-text-o"></i>';
        }
    }

    return icon;
}

function deleteContactAttachment(attachmentId, contactId) {
    if (confirm("Are you sure you want to delete this attachment?")) {
        $.ajax({
            url: admin_url + 'contact_book/delete_attachment/' + attachmentId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    loadContactAttachments(contactId);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', 'Error deleting attachment');
            }
        });
    }
}

function openContactModal(id = "") {
    var parent = $('#contact_modal');
    if (id == "") {
        parent.find('#contact_id').val("");
        parent.find('#firstname').val("");
        parent.find('#lastname').val("");
        parent.find('#email').val("");
        parent.find('#phone').val("");
        parent.find('#company').val("");
        parent.find('#address').val("");
        parent.find('#state').val("");
        parent.find('#city').val("");
        parent.find('#country').val("").selectpicker('refresh');
        parent.find('#zip').val("");
        parent.find('#details').val("");
        parent.find('#category').selectpicker('val', '');
        parent.find('.modal-title').html("Add Contact");

        iti.setCountry("IN");

        // Clear attachments
        contactAttachmentsDropzone.removeAllFiles(true);
        contactCurrentAttachments = [];
        $('#contact-attachments-preview').empty();

        parent.modal('show');
    } else {
        $.ajax({
            url: admin_url + 'contact_book/get/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var contact = response.data;
                    parent.find('#contact_id').val(contact.id);
                    parent.find('#firstname').val(contact.firstname);
                    parent.find('#lastname').val(contact.lastname);
                    parent.find('#email').val(contact.email);
                    parent.find('#phone').val(contact.phone);
                    parent.find('#company').val(contact.company);
                    parent.find('#address').val(contact.address);
                    parent.find('#state').val(contact.state);
                    parent.find('#city').val(contact.city);
                    parent.find('#country').val(contact.country_id).selectpicker('refresh');
                    parent.find('#details').val(contact.details);
                    parent.find('#category').selectpicker('val', contact.category);
                    parent.find('.modal-title').html("Edit Contact");

                    if (contact.country_dial_code != "" && contact.country_dial_code != null) {
                        setCountryByDialCode(contact.country_dial_code);
                    }

                    // Clear existing attachments display
                    contactAttachmentsDropzone.removeAllFiles(true);
                    contactCurrentAttachments = [];

                    // Load existing attachments
                    loadExistingAttachments(contact.id);

                    parent.modal('show');
                } else {
                    alert_float('danger', 'Error loading contact details');
                }
            },
            error: function() {
                alert_float('danger', 'Error loading contact details');
            }
        });
    }
}

function loadExistingAttachments(contactId) {
    $.ajax({
        url: admin_url + 'contact_book/get_attachments/' + contactId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var $previewContainer = $('#contact-attachments-preview');
            $previewContainer.empty();

            if (response.success && response.attachments.length > 0) {
                var filesHtml = '<div class="existing-files"><h5>Existing Files</h5><div class="row">';

                $.each(response.attachments, function(i, attachment) {
                    contactCurrentAttachments.push({
                        id: attachment.id,
                        file_name: attachment.file_name
                    });

                    var fileIcon = getFileIcon(attachment.filetype);

                    filesHtml += '<div class="col-md-4 existing-file" data-attachment-id="' +
                        attachment.id + '">';
                    filesHtml += '<div class="file-wrapper">';
                    filesHtml += '<div class="file-icon">' + fileIcon + '</div>';
                    filesHtml += '<div class="file-name">' + attachment.file_name + '</div>';
                    filesHtml += '<div class="file-actions">';
                    filesHtml += '<a target="_blank" href="' + admin_url +
                        'contact_book/download_attachment/' + attachment.id +
                        '" class="btn btn-success btn-xs" title="Download"><i class="fa fa-download"></i></a> ';
                    filesHtml += '<a href="javascript:;" onclick="removeAttachmentFromForm(' +
                        attachment.id +
                        ')" class="btn btn-danger btn-xs" title="Remove"><i class="fa fa-times"></i></a>';
                    filesHtml += '</div>'; // end .file-actions
                    filesHtml += '</div>'; // end .file-wrapper
                    filesHtml += '</div>'; // end .col

                    // Create new row every 3 items
                    if ((i + 1) % 3 === 0 && i !== response.attachments.length - 1) {
                        filesHtml += '</div><div class="row">';
                    }
                });

                filesHtml += '</div></div>';
                $previewContainer.html(filesHtml);
            }
        }
    });
}

function removeAttachmentFromForm(attachmentId) {
    if (confirm("Are you sure you want to remove this file?")) {
        // Remove from the DOM
        $('.existing-file[data-attachment-id="' + attachmentId + '"]').fadeOut(300, function() {
            $(this).remove();

            // If no more files, remove the container
            if ($('.existing-file').length === 0) {
                $('.existing-files').remove();
            }
        });

        // Remove from the tracking array
        contactCurrentAttachments = contactCurrentAttachments.filter(function(attachment) {
            return attachment.id !== attachmentId;
        });

        // Mark for deletion in the backend
        var contactId = $('#contact_id').val();
        if (contactId) {
            $.ajax({
                url: admin_url + 'contact_book/delete_attachment/' + attachmentId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (!response.success) {
                        alert_float('danger', response.message);
                    }
                }
            });
        }
    }
}

function deleteContact(id) {
    if (confirm("Are you sure you want to delete this contact?")) {
        $.ajax({
            url: admin_url + 'contact_book/delete/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('.table-contact-list').DataTable().ajax.reload();
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function() {
                alert_float('danger', 'Error deleting contact');
            }
        });
    }
}

function new_contact_category() {
    var type = 'category';
    var html = '';
    html = "<div id=\"new_lead_" + type + "_inline\" class=\"form-group\"><label for=\"new_" + type + "_name\">" + $(
            'label[for=\"' + type + '\"]').html() +
        "</label><div class=\"input-group\"><input type=\"text\" id=\"new_" + type + "_name\" name=\"new_" + type +
        "_name\" class=\"form-control\"><div class=\"input-group-addon\"><a href=\"#\" onclick=\"category_add_inline_select_submit('" +
        type + "'); return false;\" class=\"lead-add-inline-submit-" + type +
        "\"><i class=\"fa fa-check\"></i></a></div></div></div>";
    $('.form-group-select-input-' + type).after(html);
    $('body').find('#new_' + type + '_name').focus();
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit')
        .prop('disabled', true);
    $(".inline-field-new").addClass('disabled').css('opacity', 0.5);
    $('.form-group-select-input-' + type).addClass('hide');
}

function category_add_inline_select_submit(type) {
    var val = $('#new_' + type + '_name').val().trim();
    if (val !== '') {
        var requestURI = admin_url + 'contact_book_category/save_ajax';
        var data = {};
        data.name = val;
        data.inline = true;
        $.post(requestURI, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true') {
                var select = $('body').find('select#' + type);
                select.append('<option value="' + response.id + '">' + val + '</option>');
                select.selectpicker('val', response.id);
                select.selectpicker('refresh');
                select.parents('.form-group').removeClass('has-error');
            }
        });
    }

    $('#new_lead_' + type + '_inline').remove();
    $('.form-group-select-input-' + type).removeClass('hide');
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit')
        .prop('disabled', false);
    $(".inline-field-new").removeClass('disabled').removeAttr('style');
}
</script>
</body>

</html>