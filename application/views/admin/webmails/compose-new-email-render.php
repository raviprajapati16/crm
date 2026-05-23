<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s">
    <?php
    $panel_title = "Compose New Email";
    $mode = (isset($mode)) ? $mode : "";
    if ($mode == "forward") {
        $panel_title = "Forward Email";
    } else if ($mode == "reply") {
        $panel_title = "Reply";
    } else if ($mode == "reply-all") {
        $panel_title = "Reply All";
    } else if ($mode == "draft") {
        $panel_title = "Draft";
    }

    if ($mode == "draft") {
        $email_body = isset($email['body']) ? $email['body'] : "";
    } else {
        $email_signature = "<br>Thanks & Regards,<br>" . get_webmail_signature();
        $email_body = isset($email['body']) ? $email['body'] . '<br><br>' . $email_signature : '<br><br>' . $email_signature;
    }

    ?>
    <div class="panel-heading" style="justify-content: flex-start;">
        <?php
        if ($send_only == "0") {
        ?>
            <span class="back-btn" data-toggle="tooltip" data-title="Back"><i class="fa fa-arrow-left "></i></span>
        <?php
        }
        ?>
        <span class="panel-title-text"><?= $panel_title ?></span>
    </div>
    <div class="panel-body">
        <form id="compose-email-form" action="<?= admin_url('webmails/send_mail') ?>" enctype="multipart/form-data">
            <?php
            $staff = get_staff(get_staff_user_id());
            $to = "";
            $cc = "";
            $bcc = "";
            $replyto = "";
            if ($mode == "reply" || $mode == "reply-all") {
                $to = $email['from_email'];
                if ($mode == "reply-all") {
                    $tempArr = [];
                    if (isset($email['header']->to) && !empty($email['header']->to)) {
                        foreach ($email['header']->to as $key => $to_email) {
                            $tempArr[] = $to_email->mailbox . '@' . $to_email->host;
                        }
                    }
                    if (isset($email['header']->cc) && !empty($email['header']->cc)) {
                        foreach ($email['header']->cc as $key => $cc_email) {
                            $tempArr[] = $cc_email->mailbox . '@' . $cc_email->host;
                        }
                    }
                    if (!empty($tempArr)) {
                        $tempArr = array_values(array_diff($tempArr, array($staff->webmail_email, $email['from_email'])));
                        $cc = implode(",", $tempArr);
                    }
                }
            } else if ($mode == "draft") {

                echo "<input type='hidden' name='draft_no' value='" . trim($email_no) . "'/>";

                if (isset($email['header']->to) && !empty($email['header']->to)) {
                    $tempArr = [];
                    foreach ($email['header']->to as $key => $to_email) {
                        $tempArr[] = $to_email->mailbox . '@' . $to_email->host;
                    }
                    $to = implode(",", $tempArr);
                }
                if (isset($email['header']->cc) && !empty($email['header']->cc)) {
                    $tempArr = [];
                    foreach ($email['header']->cc as $key => $cc_email) {
                        $tempArr[] = $cc_email->mailbox . '@' . $cc_email->host;
                    }
                    $cc = implode(",", $tempArr);
                }
                if (isset($email['header']->bcc) && !empty($email['header']->bcc)) {
                    $tempArr = [];
                    foreach ($email['header']->bcc as $key => $bcc_email) {
                        $tempArr[] = $bcc_email->mailbox . '@' . $bcc_email->host;
                    }
                    $bcc = implode(",", $tempArr);
                }
                if (isset($email['header']->reply_to) && !empty($email['header']->reply_to)) {
                    $tempArr = [];
                    foreach ($email['header']->reply_to as $key => $replyto_email) {
                        $tempArr[] = $replyto_email->mailbox . '@' . $replyto_email->host;
                    }
                    $replyto = implode(",", $tempArr);
                }
            }            ?>
            <div class="form-group">
                <label for="to">To:</label>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="to" name="to" class="emails-input" style="width: calc(100% - 40px);" value="<?= $to; ?>" required>
                    <div class="dropdown" style="width: 40px; display: flex; justify-content: center; align-items: center;">
                        <button class="dropdown-toggle" type="button" data-toggle="dropdown" style="display: flex; justify-content: center; align-items: center;"><i class="fa fa-plus"></i></button>
                        <ul class="dropdown-menu">
                            <li><a href="#" class="toggle-input" data-target="cc">CC</a></li>
                            <li><a href="#" class="toggle-input" data-target="bcc">BCC</a></li>
                            <li><a href="#" class="toggle-input" data-target="replyto">Reply To</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="form-group <?= (empty($cc)) ? 'hidden' : '' ?>" id="cc-group">
                <label for="cc">CC:</label>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="cc" name="cc" class="emails-input" value="<?= $cc ?>" style="width: calc(100% - 40px);">
                    <i class="fa fa-trash hide-input remove-icon"></i>
                </div>
            </div>
            <div class="form-group <?= (empty($bcc)) ? 'hidden' : '' ?>" id="bcc-group">
                <label for="bcc">BCC:</label>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="bcc" name="bcc" class="emails-input" value="<?= $bcc ?>" style="width: calc(100% - 40px);">
                    <i class="fa fa-trash hide-input remove-icon"></i>
                </div>
            </div>
            <div class="form-group <?= (empty($replyto)) ? 'hidden' : '' ?>" id="replyto-group">
                <label for="replyto">Reply-To:</label>
                <div style="display: flex; align-items: center;">
                    <input type="text" id="replyto" name="replyto" class="emails-input" value="<?= $replyto ?>" style="width: calc(100% - 40px);">
                    <i class="fa fa-trash hide-input remove-icon"></i>
                </div>
            </div>
            <?php
            $subject = "";
            if ($mode == "forward") {
                $subject = "Fwd : " . $email['subject'];
            } else if ($mode == "reply" || $mode == "reply-all") {
                $subject = "Re : " . $email['subject'];
            } else if ($mode == "draft") {
                $subject = $email['subject'];
            }
            ?>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" value="<?= $subject ?>" required>
            </div>
            <div class="form-group">
                <label for="body"><small class="req text-danger">* </small>Body:</label>
                <textarea id="body" name="body" class="tinmce">
                    <?php if ($mode == "forward") {
                    ?>
                    -------- Original Message --------
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">Subject:</th>
                                    <td><?= $email['subject']; ?></td>
                                </tr>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">Date:</th>
                                    <td><?php echo date('d-m-Y h:i A', strtotime($email['date'])); ?></td>
                                </tr>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">From:</th>
                                    <td><?= $email['from_name']; ?> &lt;<?= $email['from_email']; ?>&gt;</td>
                                </tr>
                                <?php if (isset($email['header']->to)) { ?>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">To:</th>
                                    <td>
                                        <?= implode(', ', array_map(function ($to) {
                                            return htmlspecialchars($to->mailbox . '@' . $to->host);
                                        }, $email['header']->to)) ?>
                                    </td>
                                </tr>
                                <?php } ?>

                                <?php if (isset($email['header']->cc)) { ?>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">CC:</th>
                                    <td>
                                        <?= implode(', ', array_map(function ($cc) {
                                            return htmlspecialchars($cc->mailbox . '@' . $cc->host);
                                        }, $email['header']->cc)) ?>
                                    </td>
                                </tr>
                                <?php } ?>

                                <?php if (isset($email['header']->reply_to)) { ?>
                                <tr>
                                    <th align="right" nowrap="nowrap" valign="baseline">Reply To:</th>
                                    <td>
                                        <?= implode(', ', array_map(function ($reply_to) {
                                            return htmlspecialchars($reply_to->mailbox . '@' . $reply_to->host);
                                        }, $email['header']->reply_to)) ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <br><br>
                        <?php echo htmlspecialchars($email_body); ?>
                        <?php } else if ($mode == "reply" || $mode == "reply-all") { ?>
                            On <?php echo date('d-m-Y H:i:s'); ?>, <?= $email['from_name']; ?> &lt;<?= $email['from_email']; ?>&gt; wrote :
                            <blockquote><?php echo $email_body; ?></blockquote>
                        <?php } else { ?>
                            <?php echo $email_body; ?>
                        <?php } ?>
                </textarea>
            </div>
            <div class="form-group">
                <label for="attachments">Attachments:</label>
                <div id="file-drop-area" class="drop-area">
                    <input type="file" id="attachments" multiple style="display: none;" accept=".jpg, .jpeg, .png, .pdf">
                    <label for="attachments" class="drop-text">Drag & Drop files here or <span>browse</span></label>
                </div>
                <div class="attachments" id="attachments-list">
                    <?php if (isset($email['attachments']) && !empty($email['attachments'])) {
                        foreach ($email['attachments'] as $key => $attachment) {
                            if ($attachment['is_attachment']) {
                    ?>
                                <div class="attachment-item">
                                    <a href="<?= site_url($attachment['attachment']) ?>" target="_blank"><?= $attachment['filename'] ?></a>
                                    <input type="hidden" class="uploaded_file" name="uploaded_files[]" value="<?= $attachment['attachment'] ?>">
                                    <i class="fa fa-trash delete-temp-file" style="color: rgb(255, 0, 0);"></i>
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>

                </div>
            </div>
            <button class="btn btn-primary" type="submit">Send</button>

            <?php
            if ($send_only == "0") {
            ?>
                <button class="btn btn-primary save-draft-btn" type="button">Save Draft</button>
                <?php
                if ($mode == "draft") {
                ?>
                    <button class="btn btn-danger delete-mail" data-mailno="<?= trim($email_no) ?>" type="button">Discard Draft</button>
                <?php
                }
                ?>
            <?php
            }
            ?>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        destroyEditors('textarea[name="body"]');
        init_editor('textarea[name="body"]');

        $('.emails-input').each(function(index) {
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
                    url: "<?= admin_url('webmails/email_suggestions') ?>",
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


        $("#compose-email-form").appFormValidator({
            rules: {
                to: 'required',
                subject: 'required',
                body: {
                    tinymceRequired: true
                }
            },
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                var submitButton = $("#compose-email-form").find("button[type='submit']");
                submitButton.attr("disabled", true);
                submitButton.html('Please wait <i class="fa fa-spinner fa-spin"></i>');
                event.preventDefault();
                var formData = new FormData(form);
                formData.append("csrf_token_name", $("input[name='csrf_token_name']").val());
                $.ajax({
                    url: $(form).attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'JSON',
                    processData: false,
                    contentType: false,
                    enctype: 'multipart/form-data',
                }).done(function(response) {
                    if (response.success) {
                        $('.list-group-item').removeClass('active');
                        $('.list-group-item[data-name="INBOX"]').addClass('active');
                        alert_float('success', response.message);
                        if (onlySendEmails == "0") {
                            refreshMailList(true);
                        } else {
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        }
                    } else {
                        alert_float('danger', response.message);
                    }
                    submitButton.attr("disabled", false);
                    submitButton.html('Submit');
                });
            }
        });

        $.validator.addMethod("tinymceRequired", function(value, element, params) {
            var editorContent = tinyMCE.get($(element).attr("id")).getContent();
            return editorContent.trim() !== '';
        }, "This field is required.");

        $(document).off('click', '.toggle-input');
        $(document).on('click', '.toggle-input', function() {
            const targetId = $(this).data('target');
            $(`#${targetId}-group`).toggleClass('hidden');
        });

        $(document).off('click', '.hide-input');
        $(document).on('click', '.hide-input', function() {
            const parentGroup = $(this).closest('.form-group');
            parentGroup.find("input").val("");
            parentGroup.addClass('hidden');
        });

        $(document).off('change', '#attachments');
        $(document).on('change', '#attachments', function(event) {
            const files = event.target.files;
            handleFiles(files);
        });

        $(document).off('drag dragstart dragend dragover dragenter dragleave drop', '#file-drop-area');
        $(document).on('drag dragstart dragend dragover dragenter dragleave drop', '#file-drop-area', function(e) {
            e.preventDefault();
            e.stopPropagation();
        }).on('dragover dragenter', function() {
            $(this).addClass('drag-over');
        }).on('dragleave dragend drop', function() {
            $(this).removeClass('drag-over');
        }).on('drop', function(e) {
            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        $(document).off('click', '.delete-temp-file');
        $(document).on('click', '.delete-temp-file', function() {
            var parent = $(this).parent('.attachment-item');
            $.ajax({
                url: "<?= admin_url('webmails/delete_temp_file') ?>",
                type: 'POST',
                data: {
                    path: parent.find('.uploaded_file').val()
                },
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        parent.remove();
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', "Error : File not deleted...");
                    }
                },
                error: function() {
                    alert_float('danger', "Something went wrong...");
                }
            });
        });

        $(document).off('click', '.save-draft-btn');
        $(document).on('click', '.save-draft-btn', function() {
            if (tinymce) {
                tinymce.triggerSave();
            }
            var formData = new FormData($('#compose-email-form')[0]);
            formData.append("csrf_token_name", $("input[name='csrf_token_name']").val());
            var submitButton = $("#compose-email-form").find("button[type='submit']");
            var saveDraftButton = $("#compose-email-form").find(".save-draft-btn");
            var saveDraftButton = $("#compose-email-form").find(".save-draft-btn");
            saveDraftButton.attr("disabled", true);
            submitButton.attr("disabled", true);
            saveDraftButton.html('Please wait <i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: "<?= admin_url('webmails/save_draft')  ?>",
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                processData: false,
                contentType: false,
            }).done(function(response) {
                if (response.success) {
                    refreshMailList(true);
                    alert_float('success', response.message);
                } else {
                    alert_float('danger', response.message);
                }
                saveDraftButton.attr("disabled", false);
                submitButton.attr("disabled", false);
                saveDraftButton.html('Save as Draft');
            });
        });


    });

    function checkFileSize(file) {
        const fileSize = file.size / (1024 * 1024);
        return fileSize <= 25;
    }

    function addFileToList(file) {
        const attachmentItem = $('<div class="attachment-item"></div>').text(file.name);
        const loadingIcon = $('<i class="fa fa-spinner fa-spin"></i>').addClass('loading-icon').css('margin-left', '10px');
        attachmentItem.append(loadingIcon);
        $('#attachments-list').append(attachmentItem);

        uploadFile(file, function(response) {
            if (response.success) {
                loadingIcon.removeClass('fa-spinner fa-spin').addClass('fa-check').css('color', 'green');
                attachmentItem.html("");
                attachmentItem.append("<a href='" + site_url + response.file_path + "' target='_blank'>" + response.filename + "</a>");
                $('<input>').attr({
                    type: 'hidden',
                    class: 'uploaded_file',
                    name: 'uploaded_files[]',
                    value: response.file_path
                }).appendTo(attachmentItem);
                attachmentItem.append($(' <i class="fa fa-trash delete-temp-file"></i>').css('color', 'red'));
                $('#attachments').val("");
            } else {
                alert_float('danger', "File not uploaded.. Try Again...");
                attachmentItem.remove();
            }
        });
    }

    function handleFiles(files) {
        const attachmentsList = $('#attachments-list');
        for (let i = 0; i < files.length; i++) {
            if (checkFileSize(files[i])) {
                addFileToList(files[i]);
            } else {
                alert('File size exceeds 25MB limit: ' + files[i].name);
            }
        }
    }

    function uploadFile(file, callback) {
        var formData = new FormData();
        formData.append("csrf_token_name", $("input[name='csrf_token_name']").val());
        formData.append('file', file);
        $.ajax({
            url: "<?= admin_url('webmails/temp_upload') ?>",
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    callback({
                        success: true,
                        filename: response.filename,
                        file_path: response.file_path
                    });
                } else {
                    callback({
                        success: false
                    });
                }
            },
            error: function() {
                callback({
                    success: false
                });
            }
        });
    }

    function destroyEditors(selector) {
        var elements = document.querySelectorAll(selector);
        elements.forEach(function(element) {
            var editor = tinymce.get(element.id);
            if (editor) {
                editor.remove();
            }
        });
    }
</script>

<style>
    .panel-heading {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        align-items: center;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        padding-left: 20px !important;
    }

    .panel-body {
        padding: 10px !important;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group textarea {
        width: calc(100% - 30px);
        /* Adjusted width to accommodate remove icon */
        padding: 8px;
        box-sizing: border-box;
    }

    .form-group input[type="file"] {
        padding: 3px;
    }

    .form-group .attachments {
        margin-top: 5px;
    }

    .form-group .attachments .attachment-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .form-group .attachments .attachment-item span {
        margin-left: 10px;
        cursor: pointer;
        color: red;
        vertical-align: middle;
    }

    .dropdown-toggle {
        background: none;
        border: none;
        padding: 0;
        margin-left: 5px;
    }

    .dropdown-item {
        cursor: pointer;
    }

    .hidden {
        display: none;
    }

    .remove-icon {
        cursor: pointer;
        margin-left: 15px;
        font-size: 15px;
    }

    .drop-area {
        position: relative;
        border: 2px dashed #ccc;
        padding: 20px;
        text-align: center;
        cursor: pointer;
    }

    .drop-text {
        margin: 0;
    }

    .drop-text span {
        color: blue;
        text-decoration: underline;
        cursor: pointer;
        display: inline-block;
    }

    #attachments {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 1;
    }

    .drop-area.drag-over {
        background-color: #f0f0f0;
    }

    .attachment-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .attachment-item span {
        margin-left: 10px;
        cursor: pointer;
        color: red;
    }

    .tagify {
        width: 100%;
    }

    .panel-title-text {
        margin-left: 10px;
    }
</style>