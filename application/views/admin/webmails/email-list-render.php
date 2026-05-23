<?php defined('BASEPATH') or exit('No direct script access allowed');
$folderName = strtolower($folder);
if (stripos($folder, "/") !== false) {
    $folderName = substr($folder, strpos($folder, '/') + 1);
}
?>
<div class="panel_s">
    <div class="panel-heading">
        <div class="email-actions">
            <div class="checkbox">
                <input type="checkbox" id="select-all-btn" />
                <label></label>
            </div>
            <?php
            if (
                stripos($folderName, "draf") === false &&
                stripos($folderName, "sent") === false
            ) {
            ?>
                <button id="mark-as-unread-all-btn" class="btn btn-default" data-toggle="tooltip" data-title="Mark as unread"><i class="fa fa-envelope"></i></button>
                <button id="mark-as-read-all-btn" class="btn btn-default" data-toggle="tooltip" data-title="Mark as read"><i class="fa fa-envelope-open"></i></button>
            <?php
            }
            ?>

            <?php
            if (
                stripos($folderName, "trash") !== false ||
                stripos($folderName, "spam") !== false
            ) {
            ?>
                <button id="restore-multiple-btn" class="btn btn-default" data-toggle="tooltip" data-title="Move to Inbox">
                    <i class="fa fa-recycle"></i></a></button>
            <?php } ?>

            <button id="delete-multiple-btn" class="btn btn-default" data-toggle="tooltip" data-title="Delete Selected Mail(s)"><i class="fa fa-trash"></i></button>
            <button id="forward-btn" class="btn btn-default" data-toggle="tooltip" data-title="Forward selected email"><i class="fa fa-share"></i></button>
            <button id="refresh-btn" class="btn btn-default" data-toggle="tooltip" data-title="Refresh"><i class="fa fa-refresh"></i></button>


            <?php
            if (
                stripos(strtolower($folderName), "draf") === false &&
                stripos(strtolower($folderName), "sent") === false &&
                stripos(strtolower($folderName), "bin") === false &&
                stripos(strtolower($folderName), "trash") === false
            ) {
            ?>
                <button id="move-to-folder-btn" class="btn btn-default" data-toggle="tooltip" data-title="Move Email(s) to folder">
                    <i class="fa fa-folder-open"></i>
                </button>
            <?php
            }
            ?>


            <div class="search-box">
                <i class="fa fa-search"></i>
                <input type="text" id="search-input" placeholder="Search emails" value="<?= isset($search) ? $search : '' ?>" />
            </div>
            <select data-live-search="true" id="status_filter" class="form-control mleft10" data-none-selected-text="Select Status">
                <option value="">Select Status</option>
                <option value="seen" <?= isset($status) && $status == "seen"  ? 'selected' : '' ?>>Seen Emails</option>
                <option value="unseen" <?= isset($status) && $status == "unseen"  ? 'selected' : '' ?>>Unseen Emails</option>
            </select>
            <div>
                <input type="text" id="date-range" class="form-control mleft10" value="<?= isset($dates) ? $dates : '' ?>" placeholder="Select Dates" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="panel-body">
        <ul class="email-list">
            <?php if (!empty($email_data['emails'])) {
                foreach ($email_data['emails'] as $key => $email) { ?>
                    <li class="email-item <?= (!$email['is_read']) ? 'email-item-unread' : ''; ?>" data-mailno="<?= trim($email['mail_no']) ?>">
                        <div class="checkbox">
                            <input type="checkbox" value="<?= trim($email['mail_no']) ?>" />
                            <label></label>
                        </div>
                        <div class="sender">
                            <div class="sender-name"><?= ($email['from_name']) ? $email['from_name'] : "No Recipient"  ?></div>
                            <div class="sender-email"><?= ($email['from_email']) ? $email['from_email'] : ''  ?></div>
                        </div>
                        <div class="subject"> <?= ($email['subject']) ? $email['subject'] : '(No Subject)'  ?></div>
                        <div class="date"><?php echo date('d-m-Y h:i A', strtotime($email['date'])); ?></div>
                        <div class="actions">
                            <?php if (!$email['is_read']) { ?>
                                <a href="javascript:;" class="mark-as-read-single-mail" data-mailno="<?= trim($email['mail_no']) ?>" data-toggle="tooltip" data-title="Mark as read">
                                    <i class="fa fa-envelope-open"></i> </a>
                            <?php } else { ?>
                                <a href="javascript:;" class="mark-as-unread-single-mail" data-mailno="<?= trim($email['mail_no']) ?>" data-toggle="tooltip" data-title="Mark as unread">
                                    <i class="fa fa-envelope"></i></a>
                            <?php } ?>

                            <?php
                            if (
                                stripos($folderName, "trash") !== false ||
                                stripos($folderName, "spam") !== false
                            ) {
                            ?>
                                <a href="javascript:;" data-mailno="<?= trim($email['mail_no']) ?>" class="restore-email" data-toggle="tooltip" data-title="Move to Inbox">
                                    <i class="fa fa-recycle"></i></a>
                            <?php } ?>
                            <a href="javascript:;" data-mailno="<?= trim($email['mail_no']) ?>" class="delete-mail" data-toggle="tooltip" data-title="Delete Email">
                                <i class="fa fa-trash"></i></a>
                        </div>
                    </li>
                <?php }
            } else {
                ?>
                <div class="text-center mtop30">No Emails Available.</div>
            <?php
            } ?>
        </ul>
        <div class="bottom-pagination-section">
            <?php if (!empty($email_data['emails'])) {
                if ($email_data['total_pages'] != 1) {
            ?>
                    <div class="pagination">
                        <button id="prev-page-btn" class="btn btn-default" data-toggle="tooltip" data-title="Previous Page"><i class="fa fa-chevron-left"></i></button>
                        <span class="pagination-text">Page <span id="current-page"><?= $email_data['current_page'] ?></span> of <span id="total-pages"><?= $email_data['total_pages'] ?></span></span>
                        <button id="next-page-btn" class="btn btn-default"><i class="fa fa-chevron-right" data-toggle="tooltip" data-title="Next Page"></i></button>
                    </div>
            <?php }
            } ?>
            <?php if (!empty($email_data['total_emails']) != 0) { ?>
                <div class="recordsCountSection">Showing
                    <span class="startIndex"><?= $email_data['record_start_at']; ?></span>
                    - <span class="endIndex"><?= $email_data['record_end_at']; ?></span>
                    out of <span class="totalEmails"><?= $email_data['total_emails']; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>