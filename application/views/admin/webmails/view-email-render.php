<?php
$folderName = strtolower($folder);
if (stripos($folder, "/") !== false) {
    $folderName = substr($folder, strpos($folder, '/') + 1);
}
?><div class="panel_s">
    <div class="panel-heading">
        <span class="back-btn"><i class="fa fa-arrow-left" data-toggle="tooltip" data-title="Back"></i><?= $email['subject']; ?></span>
        <div class="email-options">
            <?php
            if (stripos($folderName, "draf") === false) {
            ?>
                <button data-mailno="<?= trim($email['mail_no']); ?>" class="btn btn-default btn-sm reply-btn" data-toggle="tooltip" data-title="Reply"><i class="fa fa-reply"></i></button>
                <button data-mailno="<?= trim($email['mail_no']); ?>" class="btn btn-default btn-sm reply-to-btn" data-toggle="tooltip" data-title="Reply all"><i class="fa fa-reply-all"></i></button>
                <button data-mailno="<?= trim($email['mail_no']); ?>" class="btn btn-default btn-sm forward-btn" data-toggle="tooltip" data-title="Forward"><i class="fa fa-share"></i></button>
            <?php } else { ?>
                <button data-mailno="<?= trim($email['mail_no']); ?>" class="btn btn-default btn-sm edit-draft" data-toggle="tooltip" data-title="Edit Draft"><i class="fa fa-edit"></i></button>
            <?php } ?>
            <button id="move-to-folder-btn" class="btn btn-default" data-toggle="tooltip" data-title="Move Email(s) to folder"><i class="fa fa-folder-open"></i></button>
            <button data-mailno="<?= trim($email['mail_no']); ?>" class="btn btn-default btn-sm delete-mail" data-toggle="tooltip" data-title="Delete"><i class="fa fa-trash"></i></button>
        </div>
    </div>
    <div class="panel-body">
        <div class="sender-container">
            <div class="user-profile">
                <img src="<?= site_url("assets/images/user-icon.png") ?>" alt="User Profile Image">
            </div>
            <div class="email-sender">
                <div class="sender-details">
                    <span class="sender-name"><?= htmlspecialchars($email['from_name']) ?></span>
                    <span class="sender-email">&lt;<?= htmlspecialchars($email['from_email']) ?>&gt;</span>
                </div>
                <div class="to-me">
                    <?php
                    $staff = get_staff(get_staff_user_id());
                    $to_emails = [];
                    if (isset($email['header']->to)) {
                        $to_emails = array_map(function ($to) {
                            return $to->mailbox . '@' . $to->host;
                        }, $email['header']->to);
                    }

                    $staff_email_matched = in_array($staff->webmail_email, $to_emails);
                    ?>
                    <div>
                        <?php if ($staff_email_matched) { ?>
                            To Me<?php
                                    if (!empty($to_emails)) {
                                        $to_emails = array_filter($to_emails, function ($email) use ($staff) {
                                            return $email !== $staff->webmail_email;
                                        });
                                        if (count($to_emails) > 0) {
                                            echo ', ' . implode(', ', array_map('htmlspecialchars', $to_emails));
                                        }
                                    }
                                    ?>
                        <?php } else {
                            if (!empty($to_emails)) {
                                echo "To : " . implode(', ', array_map('htmlspecialchars', $to_emails));
                            } else {
                                echo "(No Recipien)";
                            }
                        ?>
                        <?php } ?>
                        <i class="fa fa-caret-down" data-toggle="tooltip" data-title=""></i>
                        <div class="tooltip-content">
                            <ul>
                                <li><strong>From:</strong> <?= htmlspecialchars($email['from_name']) ?> &lt;<?= htmlspecialchars($email['from_email']) ?>&gt;</li>
                                <li><strong>Date:</strong> <?= date('d-m-Y h:i A', strtotime($email['date'])) ?></li>
                                <li><strong>Subject:</strong> <?= htmlspecialchars($email['subject']) ?></li>
                                <?php if (isset($email['header']->to)) { ?>
                                    <li><strong>To:</strong>
                                        <?= implode(', ', array_map(function ($to) {
                                            return (isset($to->personal) ? htmlspecialchars($to->personal) : $to->mailbox) . ' &lt;' . htmlspecialchars($to->mailbox . '@' . $to->host) . '&gt;';
                                        }, $email['header']->to)) ?>
                                    </li>
                                <?php } ?>
                                </li>
                                <?php if (isset($email['header']->cc)) { ?>
                                    <li><strong>CC:</strong>
                                        <?= implode(', ', array_map(function ($cc) {
                                            return (isset($cc->personal) ? htmlspecialchars($cc->personal) : $cc->mailbox) . ' &lt;' . htmlspecialchars($cc->mailbox . '@' . $cc->host) . '&gt;';
                                        }, $email['header']->cc)) ?>
                                    </li>
                                <?php } ?>
                                <?php if (isset($email['header']->bcc)) { ?>
                                    <li><strong>BCC:</strong>
                                        <?= implode(', ', array_map(function ($bcc) {
                                            return (isset($bcc->personal) ? htmlspecialchars($bcc->personal) : $bcc->mailbox) . ' &lt;' . htmlspecialchars($bcc->mailbox . '@' . $bcc->host) . '&gt;';
                                        }, $email['header']->bcc)) ?>
                                    </li>
                                <?php } ?>
                                <?php if (isset($email['header']->reply_to)) { ?>
                                    <li><strong>Reply-To:</strong>
                                        <?= implode(', ', array_map(function ($reply_to) {
                                            return htmlspecialchars(isset($reply_to->personal) ? $reply_to->personal : $reply_to->mailbox) . ' &lt;' . htmlspecialchars($reply_to->mailbox . '@' . $reply_to->host) . '&gt;';
                                        }, $email['header']->reply_to)) ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($email['attachments']) && !empty($email['attachments'])) {
        ?>
            <div class="attachments">
                <div class="attachment-cards">
                    <?php
                    foreach ($email['attachments'] as $key => $attachment) {
                        if ($attachment['is_attachment']) {
                    ?>
                            <div class="attachment-card">
                                <a href="<?= site_url($attachment['attachment']) ?>" target="_blank" class="attachment-link" download="<?= $attachment['filename'] ?>">
                                    <div class="attachment-icon">
                                        <i class="fa fa-paperclip"></i>
                                    </div>
                                    <div class="attachment-name"><?= $attachment['filename'] ?></div>
                                    <div class="download-icon">
                                        <i class="fa fa-download"></i>
                                    </div>
                                </a>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        <?php } ?>
        <iframe style="border: none; width: 100%; height: 100vh;" srcdoc="<?= htmlspecialchars($email['body']) ?>"></iframe>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('[data-toggle="tooltip"]').tooltip();

        $('.to-me').hover(function() {
            $(this).find('.tooltip-content').show();
        }, function() {
            $(this).find('.tooltip-content').hide();
        });

        $('.reply-btn').click(function() {
            var email_no = $(this).attr("data-mailno");
            startSpinner(true, "Loading");
            $.ajax({
                url: "<?php echo admin_url('webmails/email_actions') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    folder: $('.list-group-item.active').attr('data-name'),
                    email_no: email_no,
                    mode: 'reply'
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('.content-wrapper').html(result.html)
                } else {
                    alert_float('danger', result.message);
                }
                stopSpinner();
            });
        });

        $('.reply-to-btn').click(function() {
            var email_no = $(this).attr("data-mailno");
            startSpinner(true, "Loading");
            $.ajax({
                url: "<?php echo admin_url('webmails/email_actions') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    folder: $('.list-group-item.active').attr('data-name'),
                    email_no: email_no,
                    mode: 'reply-all'
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('.content-wrapper').html(result.html)
                } else {
                    alert_float('danger', result.message);
                }
                stopSpinner();
            });
        });

        $('.forward-btn').click(function() {
            var email_no = $(this).attr("data-mailno");
            startSpinner(true, "Loading");
            $.ajax({
                url: "<?php echo admin_url('webmails/email_actions') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    folder: $('.list-group-item.active').attr('data-name'),
                    email_no: email_no,
                    mode: 'forward'
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('.content-wrapper').html(result.html)
                } else {
                    alert_float('danger', result.message);
                }
                stopSpinner();
            });
        });

        $('.edit-draft').click(function() {
            var email_no = $(this).attr("data-mailno");
            startSpinner(true, "Loading");
            $.ajax({
                url: "<?php echo admin_url('webmails/email_actions') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    folder: $('.list-group-item.active').attr('data-name'),
                    email_no: email_no,
                    mode: 'draft'
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    $('.content-wrapper').html(result.html)
                } else {
                    alert_float('danger', result.message);
                }
                stopSpinner();
            });
        });
    });
</script>
<style>
    .panel-heading {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
    }

    .panel-body {
        padding: 10px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .email-options {
        display: flex;
        align-items: center;
    }

    .email-options button {
        margin-left: 10px;
    }

    .email-details {
        margin-bottom: 10px;
    }

    .attachments ul {
        list-style: none;
        padding: 0;
    }

    .attachments ul li {
        margin-bottom: 5px;
    }

    .sender-container {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .user-profile {
        margin-right: 10px;
        flex-shrink: 0;
    }

    .user-profile img {
        height: 40px;
        border-radius: 50%;
    }

    .email-sender {
        display: flex;
        flex-direction: column;
    }

    .sender-details {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
    }

    .sender-details .sender-name {
        font-weight: bold;
        margin-right: 5px;
    }

    .sender-details .sender-email {
        font-size: 12px;
        color: #888;
    }

    .to-me {
        font-size: 12px;
        color: #000;
        cursor: pointer;
    }

    .to-me:hover .tooltip-content {
        display: block;
    }

    .tooltip-content {
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        display: none;
        z-index: 1;
    }

    .tooltip-content ul {
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .attachment-cards {
        display: flex;
        flex-wrap: wrap;
    }

    .attachment-card {
        margin-right: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
        width: 200px;
        transition: all 0.3s ease;
    }

    .attachment-card:hover {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .attachment-link {
        display: flex;
        align-items: center;
        padding: 5px;
        text-decoration: none;
        color: #333;
    }

    .attachment-icon {
        margin-right: 10px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f0f0;
        border-radius: 5px;
    }

    .attachment-icon i {
        font-size: 16px;
        color: #666;
    }

    .attachment-name {
        flex-grow: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 500;
    }

    .fa-download {
        font-size: 15px;
    }

    .tooltip-content {
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        display: none;
        z-index: 1;
    }

    .tooltip-content ul {
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .fa-arrow-left {
        margin-right: 10px;
    }
</style>