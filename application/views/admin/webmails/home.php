<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= site_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
<style>
    .welcome-message {
        display: flex;
        align-items: center;
        background-color: #f8f9fa;
        padding-top: 5px;
        padding-bottom: 5px;
        border-bottom: 1px solid #ddd;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .welcome-message .fa-user {
        font-size: 30px;
        margin-right: 15px;
    }

    .welcome-message .welcome-text {
        display: flex;
        flex-direction: column;
    }

    .welcome-message .welcome-text p.logged-text {
        font-size: 12px;
        margin: 0;
    }

    .welcome-message .welcome-text p.email {
        font-size: 18px;
        margin: 0;
    }

    .email-folders {
        background-color: #fff;
        padding: 20px;
        border-right: 1px solid #ddd;
    }

    .compose-button {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .compose-button button {
        background-color: #007bff;
        border: none;
        color: white;
        padding: 10px;
        border-radius: 10px;
        font-size: 17px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s, box-shadow 0.3s;
    }

    .compose-button button:hover {
        background-color: #0056b3;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .email-folders .list-group-item {
        padding: 10px 15px;
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .email-folders .list-group-item i {
        margin-right: 10px;
    }

    .email-folders .list-group-item:hover {
        background-color: #FDC900;
        color: #fff;
    }

    .email-folders .list-group-item.active:hover {
        background-color: #337ab7;
        color: #fff;
    }

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        margin-top: 20%;
        margin-bottom: 20%;
    }

    .loading-spinner i {
        color: #007bff;
    }

    .disable-click {
        cursor: not-allowed !important;
        pointer-events: none !important;
    }

    .refresh-text {
        font-size: 17px;
        margin-left: 8px;
        margin-top: 10px;
    }

    .email-sign-setting-btn {
        position: absolute;
        right: 15px;
        top: 10px;
    }

    .folder-create-btn {
        position: absolute;
        right: 60px;
        top: 10px;
    }

    div[data-name="fields[email_signature]"] .mce-edit-area {
        height: 250px !important;
    }

    #previewContent {
        border: 1px solid;
        padding: 10px;
        border-radius: 14px;
        height: 350px;
        width: 100%;
    }

    #fielddisclaimer {
        resize: none;
    }

    .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Flexbox for folder name and unread count */
    .list-group-item .d-flex {
        display: flex;
        align-items: center;
    }

    .list-group-item .folder-action-section {
        display: none !important;
    }

    .list-group-item.active .folder-action-section {
        display: block !important;
    }

    /* Spacing for folder name and unread count */
    .folder-name {
        margin-left: 8px;
    }

    .badge-warning {
        margin-right: 10px;
        /* Adjust if needed */
    }

    /* Align the folder action icons (edit/delete) */
    .folder-action-section i {
        margin-left: 10px;
        cursor: pointer;
    }
</style>
<style>
    .panel-heading {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        font-size: 18px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        padding-left: 20px !important;
    }

    .panel-body {
        padding: 10px !important;
    }

    .email-actions {
        display: flex;
        align-items: center;
    }

    .email-actions button {
        margin-left: 10px;
    }

    .email-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .email-item {
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        padding-top: 5px;
        padding-bottom: 5px;
        display: flex;
        align-items: center;
        transition: background-color 0.3s;
        padding-left: 10px;
        padding-right: 10px;
        cursor: pointer;
    }

    .email-item:hover {
        background-color: #f1f1f1;
    }

    .email-item .checkbox {
        margin-right: 15px;
    }

    .email-item .sender {
        width: 22%;
    }

    .email-item .sender .sender-name {
        flex: 1;
        font-weight: 590;
    }

    .email-item .sender .sender-email {
        flex: 1;
        font-size: 12px;
        font-weight: 500;
        color: grey;
    }

    .email-item-unread {
        background-color: bisque !important;
    }

    .email-item .subject {
        flex: 2;
        color: #555;
    }

    .email-item .date {
        flex: 1;
        text-align: right;
        color: #999;
    }

    .email-item .actions {
        margin-left: 15px;
    }

    .email-item .actions .fa {
        margin-left: 10px;
        color: #007bff;
        cursor: pointer;
    }

    .email-item .actions .fa:hover {
        color: #0056b3;
    }

    .pagination {
        display: flex;
        align-items: center;
    }

    .pagination .btn {
        margin-left: 10px;
    }

    .search-box {
        position: relative;
        margin-left: 20px;
    }

    .search-box input {
        border: 1px solid #ddd;
        padding: 5px 10px 5px 30px;
        border-radius: 4px;
    }

    .search-box input:focus {
        outline: none;
        border-color: #007bff;
    }

    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #aaa;
    }

    .pagination-text {
        font-size: 14px;
        margin: 0px 0px 0px 10px;
    }

    .recordsCountSection {
        font-size: 14px;
        color: dimgrey;
    }

    .bottom-pagination-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .mleft10 {
        margin-left: 10px;
    }

    #date-range {
        width: 200px;
    }

    @media (max-width: 768px) {
        .email-item {
            display: flow-root;
        }

        .email-item .actions {
            margin-left: 0px;

        }

        .email-item .actions {
            position: absolute;
            left: 7px;
            margin-top: -17px;
        }

        .recordsCountSection {
            font-size: 12px;
            margin-right: 10px;
        }

        .pagination .fa {
            font-size: 9px;
        }

        .pagination .pagination-text {
            font-size: 12px;
        }

        .email-actions {
            display: flex;
            align-content: space-around;
            align-items: baseline;
            flex-wrap: wrap;
            flex-direction: row;
        }

        #search-input {
            margin: 5px;
        }

        #status_filter {
            width: 64%;
            margin: 5px;
        }

        .search-box {
            margin-left: 5px;
        }

        #date-range {
            margin: 5px;
        }

        .folder-create-btn {
            right: 50px;
        }

    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row main-row">
            <?php
            if ($mail_service_data->only_send == "1") {
            ?>
                <div class="welcome-message col-md-12">
                    <i class="fa fa-user"></i>
                    <div class="welcome-text">
                        <p class="logged-text">You are logged in as</p>
                        <p class="email"><?= $staff_data->webmail_email; ?></p>
                    </div>
                </div>
                <div class="col-md-8 content-wrapper mtop1">

                </div>
            <?php
            } else if ($auth_data['success']) {
            ?>
                <div class="welcome-message col-md-12">
                    <i class="fa fa-user"></i>
                    <div class="welcome-text">
                        <p class="logged-text">You are logged in as</p>
                        <p class="email"><?= $auth_data['email']; ?></p>
                    </div>
                    <button type="button" data-toggle="tooltip" data-title="Create New Folder" class="btn btn-info folder-create-btn"><i class="fa fa-folder" aria-hidden="true"></i></button>
                    <button type="button" data-toggle="tooltip" data-title="Email Signature Setting" class="btn btn-info email-sign-setting-btn"><i class="fa fa-cog" aria-hidden="true"></i></button>
                </div>
                <div class="col-md-3 email-folders mtop1">
                    <div class="compose-button">
                        <button id="compose-btn"><i class="fa fa-pencil"></i> Compose</button>
                    </div>
                    <div class="list-group">
                        <?php
                        if (!empty($auth_data['folders'])) {
                            foreach ($auth_data['folders'] as $key => $folder) {
                                $folderNameText = $folder['name'];
                                if (stripos($folder['name'], "/") !== false) {
                                    $folderNameText = substr($folder['name'], strpos($folder['name'], '/') + 1);
                                }
                        ?>
                                <a href="#" data-name="<?= $folder['name']; ?>" class="list-group-item <?= ($key == 0) ? 'active' : '' ?>">
                                    <?php
                                    $icon = "fa-folder";
                                    $systemFolder = false;
                                    if (stripos($folder['name'], "inbox/") !== false) {
                                        $icon = "fa-folder";
                                    } else if (stripos($folder['name'], "inbox") !== false) {
                                        $icon = "fa-inbox";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "sent") !== false || stripos($folder['name'], "send") !== false) {
                                        $icon = "fa-paper-plane";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "spam") !== false) {
                                        $icon = "fa-exclamation-triangle";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "trash") !== false || stripos($folder['name'], "bin") !== false) {
                                        $icon = "fa-trash";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "star") !== false) {
                                        $icon = "fa-star";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "draf") !== false) {
                                        $icon = "fa-file";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "all mail") !== false) {
                                        $icon = "fa-envelope";
                                        $systemFolder = true;
                                    } else if (stripos($folder['name'], "important") !== false) {
                                        $icon = "fa-info-circle";
                                        $systemFolder = true;
                                    }
                                    ?>
                                    <div>
                                        <i class="fa <?= $icon; ?>"></i>
                                        <span class="folder-name ml-2"><?= ucwords(strtolower($folderNameText)); ?></span>
                                    </div>
                                    <?php
                                    if ($folder['unreadCount'] != 0) {
                                        echo "&nbsp;<span class='badge badge-warning'><b>" . $folder['unreadCount'] . "</b></span>";
                                    } else {
                                        echo "&nbsp;<span class='badge badge-warning hide'><b>" . $folder['unreadCount'] . "</b></span>";
                                    }
                                    ?>
                                    <?php
                                    if (!$systemFolder) {
                                    ?>
                                        <div class="folder-action-section">
                                            <span id="edit-folder-btn"><i class="fa fa-pencil" data-toggle="tooltip" data-title="Edit Folder Name"></i></span>
                                            <span id="delete-folder-btn"><i class="fa fa-trash" data-toggle="tooltip" data-title="Delete this folder"></i></span>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </a>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-9 content-wrapper mtop1">

                </div>
        </div>
    <?php
            } else {
    ?>
        <div class="col-md-12">
            <div class="alert alert-danger"><?= $auth_data['message']; ?></div>
        </div>
    <?php
            }
    ?>
    </div>
</div>

<div class="modal fade" id="folder-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('webmails/webmail_folder_save'), array("name" => "folderForm", "id" => "folderForm")); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create New Folder</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?= all_type_input_render([
                        "label" => 'Folder Name',
                        "id" => "foldername",
                        "name" => "foldername",
                        "type" => "text",
                        "is_required" => false,
                        "form" => "folderForm",
                    ], 'col-md-12', false);
                    ?>
                    <input type="hidden" name="oldFolderName" id="oldFolderName" value="" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="folderForm"><?php echo _l('Save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div class="modal fade" id="move-to-folder-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('webmails/webmail_move_to_folder'), array("name" => "moveTofolderForm", "id" => "moveTofolderForm")); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Move Email(s) To Folder</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="new_folder" class="control-label">Select Folder</label>
                            <select name="new_folder" data-live-search="true" id="new_folder" class="form-control selectpicker" data-none-selected-text="Select Folder" form="moveTofolderForm">
                                <option value=""></option>
                                <?php
                                if (!empty($auth_data['folders'])) {
                                    $avoidArr = [
                                        'Sent',
                                        'Drafts',
                                        'Trash',
                                    ];
                                    foreach ($auth_data['folders'] as $key => $folder) {
                                        $folder_name = $folder['name'];
                                        if (stripos($folder['name'], "/") !== false) {
                                            $folder_name = substr($folder['name'], strpos($folder['name'], '/') + 1);
                                        }

                                        $avoid = false;
                                        foreach ($avoidArr as $avoidStr) {
                                            if (stripos($folder['name'], $avoidStr) !== false) {
                                                $avoid = true;
                                                break;
                                            }
                                        }

                                        if (!$avoid) {
                                ?>
                                            <option value="<?= $folder['name'] ?>"><?= $folder_name ?></option>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            </select>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="moveTofolderForm"><?php echo _l('Save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<div class="modal fade" id="setting-modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <?php echo form_open(admin_url('webmails/update_webmail_signature'), array("name" => "signatureForm", "id" => "signatureForm")); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Email Signature Builder</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user" aria-hidden="true"></i> First Name',
                        "id" => "firstName",
                        "name" => "firstName",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('firstName'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user" aria-hidden="true"></i> Last Name',
                        "id" => "lastName",
                        "name" => "lastName",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('lastName'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-user-circle" aria-hidden="true"></i> Designation',
                        "id" => "title",
                        "name" => "title",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('title'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-building" aria-hidden="true"></i> Company',
                        "id" => "company",
                        "name" => "company",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('company'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-envelope" aria-hidden="true"></i> Email Address',
                        "id" => "email",
                        "name" => "email",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('email'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-mobile" aria-hidden="true"></i> Mobile Number',
                        "id" => "mobile",
                        "name" => "mobile",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('mobile'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-phone" aria-hidden="true"></i> Telephone Number',
                        "id" => "phone",
                        "name" => "phone",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('phone'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-link" aria-hidden="true"></i> Website',
                        "id" => "website",
                        "name" => "website",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('website'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-facebook-square" aria-hidden="true"></i> Facebook Page Link',
                        "id" => "facebook",
                        "name" => "facebook",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('facebook'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-twitter" aria-hidden="true"></i> X (twitter) Account Link',
                        "id" => "twitter",
                        "name" => "twitter",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('twitter'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-instagram" aria-hidden="true"></i> Instagram Account Link',
                        "id" => "instagram",
                        "name" => "instagram",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('instagram'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-linkedin-square" aria-hidden="true"></i> Linkedin Account Link',
                        "id" => "linkedin",
                        "name" => "linkedin",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('linkedin'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-youtube-square" aria-hidden="true"></i> Youtube Channel Link',
                        "id" => "youtube",
                        "name" => "youtube",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('youtube'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-pinterest-square" aria-hidden="true"></i> Pinterest Account Link',
                        "id" => "pinterest",
                        "name" => "pinterest",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('pinterest'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-address-book" aria-hidden="true"></i> Address Line 1',
                        "id" => "address1",
                        "name" => "address1",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('address1'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                    <?= all_type_input_render([
                        "label" => '<i class="fa fa-address-book" aria-hidden="true"></i> Address Line 2',
                        "id" => "address2",
                        "name" => "address2",
                        "type" => "text",
                        "selected_value" => get_webmail_signature_data('address2'),
                        "is_required" => false,
                        "form" => "signatureForm",
                    ], 'col-md-3', false);
                    ?>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <?= all_type_input_render([
                                "label" => '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Disclaimer Text',
                                "id" => "disclaimer",
                                "name" => "disclaimer",
                                "type" => "textarea",
                                "rows" => 5,
                                "selected_value" => get_webmail_signature_data('disclaimer'),
                                "is_required" => false,
                                "form" => "signatureForm",
                            ], 'col-md-12', false);
                            ?>
                            <?php
                            $templateArr = array(
                                "template-1" => "Template 1",
                                "template-2" => "Template 2",
                                "template-3" => "Template 3",
                                "template-4" => "Template 4",
                                "template-5" => "Template 5",
                                "template-6" => "Template 6",
                                "template-7" => "Template 7",
                                "template-8" => "Template 8",
                                "template-9" => "Template 9",
                            );
                            ?>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="control-label"><i class="fa fa-th-list" aria-hidden="true"></i> Template</div>
                                    <select name="template" class="selectpicker" id="template" data-width="100%" form="signatureForm">
                                        <option value="" <?= (empty(get_webmail_signature_data('template'))) ? 'selected' : '' ?>>No Signature</option>
                                        <?php
                                        foreach ($templateArr as $key => $item) {
                                        ?>
                                            <option value="<?= $key ?>" <?= (get_webmail_signature_data('template') == $key) ? 'selected' : '' ?>><?= $item ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="preview-section">
                            <h4><strong>Preview : </strong></h4>
                            <iframe id="previewContent"></iframe>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" form="signatureForm"><?php echo _l('Save'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<script src="<?= site_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script>
    let currentRequest = null;
    var onlySendEmails = "<?= $mail_service_data->only_send ?>";
    $(document).ready(function() {

        renderSignatureTemplate();

        if (onlySendEmails == "1") {
            composeNewEmail();
        } else {
            refreshMailList();
        }

        $(document).on('click', '#compose-btn', function(e) {
            composeNewEmail();
        });

        $(document).on('click', '.list-group-item', function(e) {
            e.preventDefault();
            $('.list-group-item').removeClass('active');
            $(this).addClass('active');
            refreshMailList();
        });

        //Search
        let typingTimer;
        const typingInterval = 500;
        $(document).on('input', '#search-input', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                if (currentRequest) {
                    currentRequest.abort();
                }
                refreshMailList(true);
            }, typingInterval);
        });

        $(document).on('keydown', '#search-input', function() {
            clearTimeout(typingTimer);
        });

        $(document).on('change', '#status_filter', function() {
            refreshMailList(true);
        });

        $(document).on('click', '.delete-mail', function(e) {
            e.stopPropagation();
            var mail_no = $(this).attr("data-mailno");
            if (confirm_delete()) {
                delete_emails([mail_no]);
            }
        });

        $(document).on('click', '#delete-multiple-btn', function(e) {
            e.stopPropagation();
            var emailIdArr = [];
            $('.email-list input[type="checkbox"]').each(function(index, item) {
                if (this.checked) {
                    emailIdArr.push(this.value);
                }
            });
            if (emailIdArr.length > 0) {
                if (confirm_delete()) {
                    delete_emails(emailIdArr);
                }
            } else {
                alert_float('danger', "Please select email(s).");
            }
        });

        $(document).on('click', '.restore-email', function(e) {
            e.stopPropagation();
            var mail_no = $(this).attr("data-mailno");
            if (confirm_delete()) {
                move_to_inbox([mail_no]);
            }
        });

        $(document).on('click', '#restore-multiple-btn', function(e) {
            e.stopPropagation();
            var emailIdArr = [];
            $('.email-list input[type="checkbox"]').each(function(index, item) {
                if (this.checked) {
                    emailIdArr.push(this.value);
                }
            });
            if (emailIdArr.length > 0) {
                if (confirm_delete()) {
                    move_to_inbox(emailIdArr);
                }
            } else {
                alert_float('danger', "Please select email(s).");
            }
        });

        $(document).on('click', '.mark-as-read-single-mail', function(e) {
            e.stopPropagation();
            var mail_no = $(this).attr("data-mailno");
            if (confirm("Are you sure you want to perform this actinon?")) {
                mark_as_read_emails([mail_no], 'read');
            }
        });


        $(document).on('click', '#mark-as-read-all-btn', function(e) {
            e.stopPropagation();
            var emailIdArr = [];
            $('.email-list input[type="checkbox"]').each(function(index, item) {
                if (this.checked) {
                    emailIdArr.push(this.value);
                }
            });
            if (emailIdArr.length > 0) {
                if (confirm("Are you sure you want to perform this actinon?")) {
                    mark_as_read_emails(emailIdArr, 'read');
                }
            } else {
                alert_float('danger', "Please select email(s).");
            }
        });

        $(document).on('click', '.mark-as-unread-single-mail', function(e) {
            e.stopPropagation();
            var mail_no = $(this).attr("data-mailno");
            if (confirm("Are you sure you want to perform this actinon?")) {
                mark_as_read_emails([mail_no], 'unread');
            }
        });


        $(document).on('click', '#mark-as-unread-all-btn', function(e) {
            e.stopPropagation();
            var emailIdArr = [];
            $('.email-list input[type="checkbox"]').each(function(index, item) {
                if (this.checked) {
                    emailIdArr.push(this.value);
                }
            });
            if (emailIdArr.length > 0) {
                if (confirm("Are you sure you want to perform this actinon?")) {
                    mark_as_read_emails(emailIdArr, 'unread');
                }
            } else {
                alert_float('danger', "Please select email(s).");
            }
        });

        $(document).on('click', '#forward-btn', function(e) {
            e.stopPropagation();
            var firstChecked = $('.email-list input[type="checkbox"]:checked').first();
            if (firstChecked.length > 0) {
                startSpinner(true, "Loading");
                $.ajax({
                    url: "<?php echo admin_url('webmails/email_actions') ?>",
                    method: "POST",
                    dataType: 'json',
                    data: {
                        folder: $('.list-group-item.active').attr('data-name'),
                        email_no: firstChecked.val(),
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
            } else {
                alert_float('danger', "Please select an email.");
            }
        });

        $(document).on('click', '#select-all-btn', function() {
            var checked = this.checked;
            $('.email-list input[type="checkbox"]').prop('checked', checked);
            var checkedCount = $('.email-list input[type="checkbox"]:checked').length;
            if (checkedCount == 0 || checkedCount == 1) {
                $('#forward-btn').removeClass('hide');
            } else {
                $('#forward-btn').addClass('hide');
            }
        });

        $(document).on('click', '.email-list input[type="checkbox"]', function() {
            var checkedCount = $('.email-list input[type="checkbox"]:checked').length;
            if (checkedCount == 0 || checkedCount == 1) {
                $('#forward-btn').removeClass('hide');
            } else {
                $('#forward-btn').addClass('hide');
            }
        });

        $(document).on('click', '.email-list input[type="checkbox"]', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', '.email-item', function(e) {
            e.stopPropagation();
            var mail_no = $(this).attr('data-mailno');
            viewEmail(mail_no);
        });

        $(document).on('click', '#next-page-btn', function() {
            var total = Number($('#total-pages').text());
            var current = Number($('#current-page').text());
            var next = current + 1;
            if (next > total) {
                next = 1;
            }
            $('#current-page').html(next);
            refreshMailList(true);
        });

        $(document).on('click', '#prev-page-btn', function() {
            var total = Number($('#total-pages').text());
            var current = Number($('#current-page').text());
            var prev = current - 1;
            if (prev < 1) {
                prev = total;
            }
            $('#current-page').html(prev);
            refreshMailList(true);
        });

        $(document).on('click', '#refresh-btn', function() {
            refreshMailList(true);
        });

        $(document).on('click', '.back-btn', function() {
            refreshMailList(true);
        });

        $(document).on('click', '.email-sign-setting-btn', function() {
            $('#setting-modal').modal('show');
        });

        $('#signatureForm').on('change input paste', 'input, select, textarea', function() {
            renderSignatureTemplate();
        });

        $('#signatureForm').submit(function(e) {
            e.preventDefault();
            var form_data = new FormData($('#signatureForm')[0]);
            $.ajax({
                type: 'POST',
                url: $('#signatureForm').attr('action'),
                data: form_data,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        $('#setting-modal').modal('hide');
                        alert_float('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        });

        $(document).on('click', '#move-to-folder-btn', function() {
            var active_folder = $('.list-group-item.active');
            var folderName = active_folder.attr('data-name');
            $('#move-to-folder-modal').find('select').each(function() {
                $(this).find('option').each(function() {
                    if ($(this).val() == folderName) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
            $('#move-to-folder-modal').find('select').selectpicker('refresh');
            $('#move-to-folder-modal').modal('show');
        });

        $($('#moveTofolderForm')).appFormValidator({
            rules: {
                new_folder: 'required',
            },
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
        });

        $('#moveTofolderForm').submit(function(e) {
            e.preventDefault();
            e.stopPropagation();
            var active_folder = $('.list-group-item.active');
            var folderName = active_folder.attr('data-name');
            var emailIdArr = [];
            if ($('.email-list input[type="checkbox"]').length > 0) {
                $('.email-list input[type="checkbox"]').each(function(index, item) {
                    if (this.checked) {
                        emailIdArr.push(this.value);
                    }
                });
            } else {
                var mailId = $('.reply-btn').attr('data-mailno');
                emailIdArr.push(mailId);
            }

            if (emailIdArr.length > 0) {
                if (confirm("Are you sure you want to perform this actinon?")) {
                    move_to_folder(emailIdArr, folderName);
                }
            } else {
                alert_float('danger', "Please select email(s).");
            }
        });

        $(document).on('click', '.folder-create-btn', function() {
            $('#folder-modal').find('#oldFolderName').val("");
            $('#folder-modal').find('.modal-title').val("Create New Folder");
            $('#folder-modal').modal('show');
        });

        $(document).on('click', '#edit-folder-btn', function(e) {
            e.stopImmediatePropagation();
            var active_folder = $('.list-group-item.active');
            var folderName = active_folder.attr('data-name');
            folderName = folderName.replace(/^INBOX[\/\.]/i, '');
            $('#folder-modal').find('#oldFolderName').val(folderName);
            $('#folder-modal').find('#fieldfoldername').val(folderName);
            $('#folder-modal').find('.modal-title').text("Update Folder Name");
            $('#folder-modal').modal('show');
        });

        $(document).on('click', '#delete-folder-btn', function(e) {
            e.stopImmediatePropagation();
            var active_folder = $('.list-group-item.active');
            var folderName = active_folder.attr('data-name');
            startSpinner(true, "Please Wait...");
            var form_data = new FormData();
            form_data.append("csrf_token_name", $("input[name='csrf_token_name']").val());
            form_data.append('folder', folderName);
            if (confirm("Are you sure you want to perform this action?")) {
                $.ajax({
                    type: 'POST',
                    url: "<?= admin_url('webmails/webmail_delete_folder') ?>",
                    data: form_data,
                    processData: false,
                    contentType: false,
                    dataType: 'JSON',
                    success: function(response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            alert_float('danger', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            }
        });


        $($('#folderForm')).appFormValidator({
            rules: {
                foldername: 'required',
            },
            errorPlacement: function(error, element) {
                var inputType = $(element).attr('type')
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
        });

        $('#folderForm').submit(function(e) {
            e.preventDefault();
            var form_data = new FormData($('#folderForm')[0]);
            $('#folderForm').prop("disabled", true);
            $('#folderForm').find('button[type="submit"]').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
            $.ajax({
                type: 'POST',
                url: $('#folderForm').attr('action'),
                data: form_data,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        $('#folder-modal').modal('hide');
                        alert_float('success', response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                },
                complete: function() {
                    $('#folderForm').prop("disabled", false);
                    $('#folderForm').find('button[type="submit"]').html('Submit');
                }
            });
        });

    });

    function move_to_folder(idArr, curFolder) {
        $('#moveTofolderForm').prop("disabled", true);
        $('#moveTofolderForm').find('button[type="submit"]').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
        var form_data = new FormData($('#moveTofolderForm')[0]);
        form_data.append('emails[]', idArr);
        form_data.append('old_folder', curFolder);
        $.ajax({
            type: 'POST',
            url: $('#moveTofolderForm').attr('action'),
            data: form_data,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                if (response.success) {
                    $('#move-to-folder-modal').modal('hide');
                    alert_float('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            },
            complete: function() {
                $('#moveTofolderForm').prop("disabled", false);
                $('#moveTofolderForm').find('button[type="submit"]').html('Submit');
            }
        });
    }

    function mark_as_read_emails(email_arr, type) {
        startSpinner(true, "Processing...");
        $.ajax({
            url: "<?php echo admin_url('webmails/mark_as_read') ?>",
            method: "POST",
            data: {
                email_no: email_arr,
                type: type,
                folder: $('.list-group-item.active').attr('data-name'),
            },
            dataType: 'json',
        }).done(function(result) {
            if (result.success) {
                refreshMailList(true)
                alert_float('success', result.message);
                getEmailInboxUnreadCount();
            } else {
                alert_float('danger', result.message);
            }
            stopSpinner();
        });
    }

    function delete_emails(email_arr) {
        startSpinner(true, "Deleting Email");
        $.ajax({
            url: "<?php echo admin_url('webmails/delete_email') ?>",
            method: "POST",
            data: {
                folder: $('.list-group-item.active').attr('data-name'),
                email_no: email_arr
            },
            dataType: 'json',
        }).done(function(result) {
            if (result.success) {
                refreshMailList(true)
                alert_float('success', result.message);
            } else {
                alert_float('danger', result.message);
            }
            stopSpinner();
        });
    }

    function move_to_inbox(email_arr) {
        startSpinner(true, "Moving Email(s) to Inbox");
        $.ajax({
            url: "<?php echo admin_url('webmails/move_to_inbox') ?>",
            method: "POST",
            data: {
                folder: $('.list-group-item.active').attr('data-name'),
                email_no: email_arr
            },
            dataType: 'json',
        }).done(function(result) {
            if (result.success) {
                refreshMailList(true)
                alert_float('success', result.message);
            } else {
                alert_float('danger', result.message);
            }
            stopSpinner();
        });
    }

    function refreshMailList(isRefresh = false) {
        var curPage = Number($('#current-page').text());
        if (curPage == 0) {
            curPage = 1;
        }
        var search = $('#search-input').val();
        var status = $('#status_filter').val();
        var dates = $('#date-range').val();
        startSpinner(isRefresh);
        currentRequest = $.ajax({
            url: "<?php echo admin_url('webmails/get_email_list') ?>",
            method: "POST",
            data: {
                "folder": $('.list-group-item.active').attr('data-name'),
                "page": curPage,
                "search": search,
                "status": status,
                "dates": dates
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    $('.content-wrapper').html(result.html);
                    getEmailInboxUnreadCount();
                    dateRangePickerInit();
                } else {
                    alert_float('danger', result.message);
                }
                stopSpinner();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (textStatus !== 'abort') {
                    alert('AJAX error: ' + textStatus);
                }
                stopSpinner();
            }
        });
    }

    function dateRangePickerInit() {
        $('#date-range').daterangepicker({
            locale: {
                format: 'DD-MM-YYYY'
            },
            opens: 'left',
            showCustomRangeLabel: true,
            alwaysShowCalendars: true,
            autoUpdateInput: false,
            maxDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment()],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            }
        });

        $('#date-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            refreshMailList(true);
        });

        $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            refreshMailList(true);
        });
    }

    function startSpinner(isRefresh = false, text = "Loading Email(s)") {
        if (isRefresh) {
            $('.content-wrapper .panel-heading').html('');
            $('.content-wrapper .panel-body').html('<div class="loading-spinner"><div class="dt-loader"><span></span></div><span class="refresh-text">' + text + '</span></div>');
        } else {
            $('.content-wrapper').html('<div class="loading-spinner"><div class="dt-loader"><span></span></div><span class="refresh-text">' + text + '</span></div>');
        }
        $('#compose-btn').addClass("disable-click");
        $('.list-group-item').addClass("disable-click");
    }

    function stopSpinner() {
        $('#compose-btn').removeClass("disable-click");
        $('.list-group-item').removeClass("disable-click");
    }

    function composeNewEmail() {
        $.ajax({
            url: "<?php echo admin_url('webmails/compose_new_email') ?>",
            method: "POST",
            data: {
                send_only: onlySendEmails,
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $('.content-wrapper').html(result.html)
            } else {
                alert_float('danger', result.message);
            }
        });
    }

    function viewEmail(email_no) {
        startSpinner(true, "Loading Email");
        $.ajax({
            url: "<?php echo admin_url('webmails/view_email') ?>",
            method: "POST",
            dataType: 'json',
            data: {
                folder: $('.list-group-item.active').attr('data-name'),
                email_no: email_no,
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $('.content-wrapper').html(result.html);
                getEmailInboxUnreadCount();
            } else {
                alert_float('danger', result.message);
            }
            stopSpinner();
        });
    }

    function renderSignatureTemplate() {
        var form_data = new FormData($('#signatureForm')[0]);
        $.ajax({
            type: 'POST',
            url: "<?php echo admin_url('webmails/email_signature_preview') ?>",
            data: form_data,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function(response) {
                var iframe = document.getElementById('previewContent');
                if (response.success) {
                    iframe.contentWindow.document.open();
                    iframe.contentWindow.document.write(response.html);
                    iframe.contentWindow.document.close();
                } else {
                    iframe.contentWindow.document.open();
                    iframe.contentWindow.document.write('<div style="text-align: center;margin-top: 13%;">No Preview available</div>');
                    iframe.contentWindow.document.close();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    }
</script>