$(document).ready(function () {
    init_editor('.lead_email_body', { height: 600 });

    $('.lead-emails-input').each(function(index) {
        var tagify = new Tagify(this, {
            enforceWhitelist: false,
            pattern: /^[\w+\-.]+@[a-z\d\-.]+\.[a-z]+$/i,
            maxTags: 10,
            dropdown: {
                maxItems: 20,
                classname: 'tags-look',
                enabled: 0,
                closeOnSelect: true
            }
        }).on('input', function(e) {
            tagify.whitelist = null;
            tagify.loading(true);
            $.ajax({
                url: admin_url + "/webmails/email_suggestions",
                method: 'POST',
                dataType: 'json',
                data: {
                    query: e.detail.value
                },
                success: function(result) {
                    tagify.settings.whitelist = result.concat(tagify.value);
                    tagify.loading(false);
                    tagify.dropdown.show(e.detail.value);
                },
                error: function(err) {
                    console.error('Error fetching data:', err);
                    tagify.dropdown.hide();
                }
            });
        });
        tagify.on('add', function(e) {
            if (!tagify.settings.whitelist.includes(e.detail.data.value)) {
                tagify.settings.whitelist.push(e.detail.data.value);
            }
        });
    });

    $("#form_lead_email_send").appFormValidator({
        rules: {
            lead_email_subject: 'required',
            lead_email_body: {
                tinymceRequired: true // Define custom rule for TinyMCE
            }
        },
        errorPlacement: function (error, element) {
            var inputType = $(element).attr('type')
            var formGroup = $(element).closest('.form-group');
            $(formGroup).append(error);
        },
        submitHandler: function (form) {
            if (tinymce) {
                tinymce.triggerSave();
            }
            var submitButton = $('#lead-email-send-modal').find("button[type='submit']");
            submitButton.attr("disabled", true);
            submitButton.html('Please wait <i class="fa fa-spinner fa-spin"></i>');
            event.preventDefault();
            var formData = new FormData($("#form_lead_email_send")[0]);
            $.ajax({
                url: $("#form_lead_email_send").attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
            }).done(function (response) {
                if (response.success) {
                    $('#lead-email-send-modal').modal('hide')
                    alert_float('success', response.message);
                    init_lead($('#lead-email-send-modal').find('input[name="email_send_lead_id"]').val());
                } else {
                    alert_float('danger', response.message);
                }
                submitButton.attr("disabled", false);
                submitButton.html('Submit');
            });
        }
    });

    $.validator.addMethod("tinymceRequired", function (value, element, params) {
        var editorContent = tinyMCE.get($(element).attr("id")).getContent();
        return editorContent.trim() !== '';
    }, "This field is required.");

    $(document).on('change', `#fieldlead_email_send_template`, function (e) {
        var leadId = $('input[name="leadid"]').val();
        $.ajax({
            url: admin_url + 'leads/leadComposeNewEmail',
            method: "POST",
            data: {
                leadId: leadId,
                selected_template: $(`#fieldlead_email_send_template`).val()
            },
            dataType: 'json'
        }).done(function (result) {
            if (result.success) {
                $('#lead-email-send-modal').find('input[name="email_send_lead_id"]').val(leadId);
                $('#lead-email-send-modal').find('#fieldlead_email_send_to').val(result.to);
                $('#lead-email-send-modal').find('#fieldlead_email_subject').val(result.subject);
                $('#lead-email-send-modal').find('#email_attachments').val("");
                $('#lead-email-send-modal').find('#email_attachments').siblings('.file-input-error').remove();
                var editor = tinymce.get($('.lead_email_body').attr('id'));
                editor.setContent(result.body);
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    $(document).on('click', '.lead-compose-email', function (e) {
        var leadId = $('input[name="leadid"]').val();
        $('#lead-email-send-modal').find('input[name="lead_email_subject"]').val("");
        const $select = $(`#fieldlead_email_send_template`);
        $.ajax({
            url: admin_url + 'leads/leadComposeNewEmail',
            method: "POST",
            data: {
                leadId: leadId,
            },
            dataType: 'json'
        }).done(function (result) {
            if (result.success) {
                $select.empty();
                result.templates.forEach(item => {
                    const option = $('<option>')
                        .val(item.emailtemplateid)
                        .text(item.name);
                    if (item.emailtemplateid == 135) {
                        option.attr('selected', 'selected');
                    }
                    $select.append(option);
                });
                if ($select.hasClass('selectpicker')) {
                    $select.selectpicker('refresh');
                }
                $('#lead-email-send-modal').find('input[name="email_send_lead_id"]').val(leadId);
                $('#lead-email-send-modal').find('#fieldlead_email_send_to').val(result.to);
                $('#lead-email-send-modal').find('#fieldlead_email_subject').val(result.subject);
                $('#lead-email-send-modal').find('#email_attachments').val("");
                $('#lead-email-send-modal').find('#email_attachments').siblings('.file-input-error').remove();
                var editor = tinymce.get($('.lead_email_body').attr('id'));
                editor.setContent(result.body);
                $('.leadModalCloseBtn').trigger('click');
                $('#lead-email-send-modal').modal('show');
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    $(document).on('click', '.close-email-send-modal', function (e) {
        e.stopPropagation();
        var leadId = $('#lead-email-send-modal').find('input[name="email_send_lead_id"]').val();
        init_lead(leadId);
        $('#lead-email-send-modal').modal('hide');
    });


    //Whatsapp
    $("#form_lead_whatsapp_send").appFormValidator({
        rules: {
            lead_whatsapp_body: 'required',
        },
        errorPlacement: function (error, element) {
            var inputType = $(element).attr('type')
            var formGroup = $(element).closest('.form-group');
            $(formGroup).append(error);
        },
        submitHandler: function (form) {
            var submitButton = $('#lead-whatsapp-send-modal').find("button[type='submit']");
            submitButton.attr("disabled", true);
            submitButton.html('Please wait <i class="fa fa-spinner fa-spin"></i>');
            event.preventDefault();
            var formData = $(form).serialize();
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json'
            }).done(function (response) {
                if (response.success) {
                    $('#lead-whatsapp-send-modal').modal('hide')
                    alert_float('success', response.message);
                    window.open(response.link, '_blank');
                    init_lead($('#lead-whatsapp-send-modal').find('input[name="whatsapp_send_lead_id"]').val());
                } else {
                    alert_float('danger', response.message);
                }
                submitButton.attr("disabled", false);
                $('#lead-whatsapp-send-modal').find("button[value='web']").html('<i class="fa fa-whatsapp" aria-hidden="true"></i> Web Share');
                $('#lead-whatsapp-send-modal').find("button[value='app']").html('<i class="fa fa-whatsapp" aria-hidden="true"></i> App Share');
            });
        }
    });

    $(document).on('change', `#fieldlead_whatsapp_send_template`, function (e) {
        var leadId = $('input[name="leadid"]').val();
        $.ajax({
            url: admin_url + 'leads/leadComposeNewWhatsappMessage',
            method: "POST",
            data: {
                leadId: leadId,
                phonenumber: $('#lead-whatsapp-send-modal').attr('data-number'),
                selected_template: $(`#fieldlead_whatsapp_send_template`).val()
            },
            dataType: 'json'
        }).done(function (result) {
            if (result.success) {
                $('#lead-whatsapp-send-modal').find('input[name="whatsapp_send_lead_id"]').val(leadId);
                $('#lead-whatsapp-send-modal').find('#fieldlead_whatsapp_send').val(result.to);
                $('#lead-whatsapp-send-modal').find('#fieldlead_whatsapp_body').val(result.body);
                $('#lead-whatsapp-send-modal').find('input[name="lead_whatsapp_number"]').val(result.phonenumber);
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    $(document).on('click', '.lead-compose-whatsapp', function (e) {
        var leadId = $('input[name="leadid"]').val();
        const $select = $(`#fieldlead_whatsapp_send_template`);
        var phonenumber = $(this).attr('data-number');
        $.ajax({
            url: admin_url + 'leads/leadComposeNewWhatsappMessage',
            method: "POST",
            data: {
                leadId: leadId,
                phonenumber: phonenumber
            },
            dataType: 'json'
        }).done(function (result) {
            if (result.success) {
                $select.empty();
                result.templates.forEach(item => {
                    const option = $('<option>')
                        .val(item.emailtemplateid)
                        .text(item.name);
                    if (item.emailtemplateid == 135) {
                        option.attr('selected', 'selected');
                    }
                    $select.append(option);
                });

                if ($select.hasClass('selectpicker')) {
                    $select.selectpicker('refresh');
                }
                $('#lead-whatsapp-send-modal').attr('data-number', phonenumber);
                $('#lead-whatsapp-send-modal').find('input[name="whatsapp_send_lead_id"]').val(leadId);
                $('#lead-whatsapp-send-modal').find('#fieldlead_whatsapp_send').val(result.to);
                $('#lead-whatsapp-send-modal').find('#fieldlead_whatsapp_body').val(result.body);
                $('#lead-whatsapp-send-modal').find('input[name="lead_whatsapp_number"]').val(result.phonenumber);
                $('.leadModalCloseBtn').trigger('click');
                $('#lead-whatsapp-send-modal').modal('show');
            } else {
                alert_float('danger', result.message);
            }
        });
    });



    $(document).on('click', '.close-whatsapp-send-modal', function (e) {
        e.stopPropagation();
        var leadId = $('#lead-whatsapp-send-modal').find('input[name="whatsapp_send_lead_id"]').val();
        init_lead(leadId);
        $('#lead-whatsapp-send-modal').modal('hide');
    });

    $(document).on('click', '.close-email-track-modal', function (e) {
        e.stopPropagation();
        $('#lead-email-track-modal').modal('hide');
    });

    $(document).on('click', '.lead-track-email-btn', function (e) {
        e.stopPropagation();
        var leadId = $('input[name="leadid"]').val();
        $('#lead-email-track-modal').attr('data-leadid', leadId);
        $.ajax({
            url: admin_url + 'leads/getEmailTrackingList',
            method: "POST",
            data: {
                leadId: leadId
            },
            dataType: 'json'
        }).done(function (result) {
            if (result.success) {
                $('#lead-email-track-modal').find('.modal-body').html(result.html);
            } else {
                $('#lead-email-track-modal').find('.modal-body').html("<span class='text-center'>Email(s) not available.</span>");
            }
            $('#lead-email-track-modal').modal('show');
        });
    });

    $('.lead-email-send-modal #email_attachments').on('change', function () {
        validateFileInput($(this));
    });

});

function validateFileInput($fileInput) {
    const maxFileSize = 30 * 1024 * 1024;
    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv'];
    const maxFiles = 5;

    $fileInput.siblings('.file-input-error').remove();

    const files = $fileInput[0].files;
    let validationPassed = true;

    if (files.length > maxFiles) {
        displayError($fileInput, `You can only upload a maximum of ${maxFiles} files.`);
        $fileInput.val('');
        return false;
    }

    $.each(files, function (index, file) {
        if (file.size > maxFileSize) {
            displayError($fileInput, `The file "${file.name}" exceeds the ${maxFileSize}MB size limit.`);
            removeFileAtIndex($fileInput, index);
            validationPassed = false;
        }

        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension)) {
            displayError($fileInput, `The file "${file.name}" has an invalid extension. Allowed extensions are: ${allowedExtensions.join(', ')}`);
            removeFileAtIndex($fileInput, index);
            validationPassed = false;
        }
    });

    return validationPassed;
}

function removeFileAtIndex($fileInput, index) {
    const dataTransfer = new DataTransfer();
    const files = $fileInput[0].files;

    $.each(files, function (i, file) {
        if (i !== index) {
            dataTransfer.items.add(file);
        }
    });

    $fileInput[0].files = dataTransfer.files;
}

function displayError($fileInput, message) {
    const errorSpan = `<span class="file-input-error text-danger">${message}</span>`;
    $fileInput.after(errorSpan);
}