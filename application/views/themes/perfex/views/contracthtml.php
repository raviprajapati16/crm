<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
theme_style_clients_area_head();
?>
<style>
    body {
        user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
    }

    #wrapper {
        overflow: hidden !important;
    }

    #contract-wrapper {
        padding: 2%;
    }

    img {
        pointer-events: none;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .contract-html-logo {
        /* padding: 10px;
      background: #fff;
      border-radius: 20px; */
    }

    .contract-html-subject,
    .contract-html-subject small,
    .contract-html-number,
    .text-right {
        color: #fff !important;
    }

    .contract-html-tabs {
        background: #fff;
        padding: 15px;
        border-radius: 20px;
        height: auto;
        overflow: hidden;
    }

    .nav-tabs {
        border-top: 0;
        background-color: transparent;
    }

    .nav-tabs li a {
        color: #000 !important;
    }

    .nav-tabs li.active a {
        color: #fff !important;
        background-color: #456e36 !important;
        padding: 10px !important;
        border-radius: 10px !important;
    }

    .btn-default,
    .btn-success,
    .btn-info,
    .btn-primary {
        color: #fff;
        border: #456e36 !important;
        background-color: #456e36 !important;
    }

    .btn-default:hover,
    .btn-default:focus,
    .btn-default:active,
    .btn-info:hover,
    .btn-info:focus,
    .btn-info:active,
    .btn-success:hover,
    .btn-success:focus,
    .btn-success:active,
    .btn-primary:hover,
    .btn-primary:focus,
    .btn-primary:active {
        color: #000;
        border: #FDC900 !important;
        background-color: #FDC900 !important;
    }

    .navbar-fixed-bottom {
        background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
        color: #fff;
        font-weight: 500;
        border-top: 1px solid #000;
    }

    #pdfContainer {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        max-height: 90vh;
        overflow-y: scroll;
    }

    .pdf-page {
        border: 1px solid #000;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 10px;
        overflow: hidden;
        page-break-inside: avoid;
        margin-right: 2px;
    }

    .pdf-page img {
        display: block;
        max-width: 100%;
        height: auto;
    }

    .dt-loader-logo {
        transform: translateZ(1px);
        display: flex;
        flex-direction: column;
        align-items: center
    }

    .dt-loader-logo:after {
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

    .dt-loader-logo span {
        margin-top: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #333
    }

    .header-action-section {
        display: flex;
        justify-content: flex-end;
    }

    @media (min-width: 1281px),
    (min-width: 1025px) and (max-width: 1280px) {
        .preview-top-wrapper {
            margin-top: 0px;
        }

    }

    .logo.img-responsive {
        width: 20%;
    }

    @media (max-width: 767px) {
        .action-button {
            border: 1px solid #fff !important;
        }

        .logo.img-responsive {
            width: 60%;
        }

        .contract-html-logo {
            display: flex;
            justify-content: center;
        }

        .header-action-section {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
        }
    }

    @keyframes logo-flip {
        0% {
            transform: rotateY(0deg)
        }

        100% {
            transform: rotateY(360deg)
        }
    }

    .contract-right {
        color: #000;
    }
</style>
<?php
if (!is_client_logged_in() && !is_staff_logged_in() && $contract->signed == 0 && !empty($contract->open_till) && date('Y-m-d', strtotime($contract->open_till)) < date('Y-m-d')) {
    $this->load->view('admin/contracts/link_expired_page');
    exit;
}
?>
<div id="contract-wrapper">
    <div class="mtop15 preview-top-wrapper">
        <div class="row">
            <div class="col-md-3">
                <div class="contract-html-logo">
                    <?php echo get_white_company_logo(); ?>
                </div>
            </div>
            <div class="col-md-9 text-right">
                <?php if (!is_staff_logged_in() || !is_admin()) {
                    if ($this->session->userdata('contract_' . $contract->id . '_customer_authenticated')) {
                        ?>
                        <div class="login-text"><strong>Logged in as</strong></div>
                        <div class="login-text">
                            <?= $this->session->userdata('contract_' . $contract->id . '_customer_auth_email') ?><span
                                class="btn btn-xs btn-danger mleft5 btn-logout" data-toggle="tooltip" data-title="Logout"><i
                                    class="fa fa-sign-out" aria-hidden="true"></i></span>
                        </div>
                    <?php }
                } ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="top">
            <div class="container-fluid preview-sticky-container">
                <div class="row">
                    <div class="col-md-12">
                        <?php if ($contract->signed == 0) { ?>
                            <?php if (!is_staff_logged_in() && !is_admin()) { ?>
                                <button type="submit" id="accept_action" data-toggle="tooltip"
                                    data-title="Please read all pages of contract to sign the document"
                                    class="btn btn-success pull-right action-button "
                                    disabled><?php echo _l('e_signature_sign'); ?></button>
                            <?php } ?>
                        <?php } else { ?>
                            <span
                                class="success-bg content-view-status contract-html-is-signed"><?php echo _l('is_signed'); ?></span>
                        <?php } ?>

                        <?php if (is_staff_logged_in() || is_admin()) { ?>
                            <?php echo form_open($this->uri->uri_string()); ?>
                            <button type="submit"
                                class="btn btn-default pull-right action-button mright5 contract-html-pdf">
                                <i class="fa fa-file-pdf-o"></i>
                                <?php echo _l('clients_invoice_html_btn_download'); ?></button>
                            <?php echo form_hidden('action', 'contract_pdf'); ?>
                            <?php echo form_close(); ?>
                        <?php } ?>

                        <?php if (is_client_logged_in() && has_contact_permission('contracts')) { ?>
                            <a href="<?php echo site_url('clients/contracts/'); ?>"
                                class="btn btn-default mright5 pull-right action-button go-to-portal">
                                <?php echo _l('client_go_to_dashboard'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 contract-left">
            <div class="panel_s mtop20">
                <div class="panel-body tc-content contract-html-content" id="pdfContainer">

                </div>
            </div>
        </div>
        <div class="col-md-4 contract-right">
            <div class="inner mtop20 contract-html-tabs">
                <ul class="nav nav-tabs nav-tabs-flat mbot15" role="tablist">
                    <li role="presentation" class="<?php if (!$this->input->get('tab') || $this->input->get('tab') === 'summary') {
                        echo 'active';
                    } ?>">
                        <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">
                            <i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo _l('summary'); ?></a>
                    </li>
                    <li role="presentation" class="<?php if ($this->input->get('tab') === 'discussion') {
                        echo 'active';
                    } ?>">
                        <a href="#discussion" aria-controls="discussion" role="tab" data-toggle="tab">
                            <i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo _l('discussion'); ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab') || $this->input->get('tab') === 'summary') {
                        echo ' active';
                    } ?>" id="summary">
                        <address class="contract-html-company-info">
                            <?php echo format_organization_info(); ?>
                        </address>
                        <div class="row mtop20">
                            <?php if ($contract->contract_value != 0) { ?>
                                <div class="col-md-12 contract-value">
                                    <h4 class="bold mbot30">
                                        <?php echo _l('contract_value'); ?>:
                                        <?php echo app_format_money($contract->contract_value, get_base_currency()); ?>
                                    </h4>
                                </div>
                            <?php } ?>
                            <div class="col-md-5 text-muted contract-number">
                                # <?php echo _l('contract_number'); ?>
                            </div>
                            <div class="col-md-7 contract-number">
                                <?php echo $contract->prefix, $contract->number; ?>
                            </div>
                            <div class="col-md-5 text-muted contract-start-date">
                                <?php echo _l('contract_start_date'); ?>
                            </div>
                            <div class="col-md-7 contract-start-date">
                                <?php echo _d($contract->datestart); ?>
                            </div>
                            <?php if (!empty($contract->dateend)) { ?>
                                <div class="col-md-5 text-muted contract-end-date">
                                    <?php echo _l('contract_end_date'); ?>
                                </div>
                                <div class="col-md-7 contract-end-date">
                                    <?php echo _d($contract->dateend); ?>
                                </div>
                            <?php } ?>
                            <?php if (!empty($contract->type_name)) { ?>
                                <div class="col-md-5 text-muted contract-type">
                                    <?php echo _l('contract_type'); ?>
                                </div>
                                <div class="col-md-7 contract-type">
                                    <?php echo $contract->type_name; ?>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (count($contract->attachments) > 0) { ?>
                            <div class="contract-attachments">
                                <div class="clearfix"></div>
                                <hr />
                                <p class="bold mbot15"><?php echo _l('contract_files'); ?></p>
                                <?php foreach ($contract->attachments as $attachment) {
                                    $attachment_url = site_url('download/file/contract/' . $attachment['attachment_key']);
                                    if (!empty($attachment['external'])) {
                                        $attachment_url = $attachment['external_link'];
                                    }
                                    ?>
                                    <div class="col-md-12 row mbot15">
                                        <div class="pull-left"><i
                                                class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                                        </div>
                                        <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <!-- If Customer contract -->
                        <?php if (count($contract_contacts) > 0) { ?>
                            <div class="contract-attachments">
                                <div class="clearfix"></div>
                                <hr />
                                <p class="bold mbot15">Agreement Sign Status</p>
                                <?php foreach ($contract_contacts as $contact) {
                                    ?>
                                    <div class="col-md-12 row mbot15">
                                        <div class="pull-left">
                                            <?php
                                            $contact_name = $contact['name'];
                                            $tooltip_msg = ($contact['signed'] == "1") ? "This contract has been signed by " . $contact_name . " on " . date('d-m-Y h:i:s A', strtotime($contact['acceptance_date'])) : "Not Signed Yet";
                                            ?>
                                            <span class="contact-text" data-toggle="tooltip"
                                                data-title="<?= $tooltip_msg ?>"><?= $contact_name ?></span>
                                            <?=
                                                ($contact['signed'] == "1") ? "<i class='fa fa-check text-success signicon'></i> " : "<i class='fa fa-times signicon text-danger'></i>";
                                            ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <!-- If vendor contract -->

                    </div>
                    <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') === 'discussion') {
                        echo ' active';
                    } ?>" id="discussion">
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <div class="contract-comment">
                            <textarea name="content" rows="4" class="form-control"></textarea>
                            <button type="submit" class="btn btn-info mtop10 pull-right"
                                data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('proposal_add_comment'); ?></button>
                            <?php echo form_hidden('action', 'contract_comment'); ?>
                        </div>
                        <?php echo form_close(); ?>
                        <div class="clearfix"></div>
                        <?php
                        $comment_html = '';
                        foreach ($comments as $comment) {
                            $comment_html .= '<div class="contract_comment mtop10 mbot20" data-commentid="' . $comment['id'] . '">';
                            if ($comment['rel_type'] == "staff") {
                                $comment_html .= staff_profile_image($comment['staffid'], array(
                                    'staff-profile-image-small',
                                    'media-object img-circle pull-left mright10'
                                ));
                            } else {
                                $comment_html .= staff_profile_image("0", array(
                                    'staff-profile-image-small',
                                    'media-object img-circle pull-left mright10'
                                ));
                            }
                            $comment_html .= '<div class="media-body valign-middle">';
                            $comment_html .= '<div class="mtop5">';
                            $comment_html .= '<b>';
                            if ($comment['rel_type'] == "staff") {
                                $comment_html .= get_staff_full_name($comment['staffid']);
                            } else if ($comment['rel_type'] == "customer") {
                                $comment_html .= get_contact_full_name($comment['staffid']);
                            } else if ($comment['rel_type'] == "vendor") {
                                $comment_html .= get_lead_full_name($comment['staffid']);
                            } else if ($comment['rel_type'] == "contact_book") {
                                $comment_html .= get_contact_book_full_name($comment['staffid']);
                            }
                            $comment_html .= '</b>';
                            $comment_html .= ' - <small class="mtop10 text-muted">' . time_ago($comment['dateadded']) . '</small>';
                            $comment_html .= '</div>';
                            $comment_html .= '<br />';
                            $comment_html .= check_for_links($comment['content']) . '<br />';
                            $comment_html .= '</div>';
                            $comment_html .= '</div>';
                        }
                        echo $comment_html; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
get_template_part('identity_confirmation_form', array('formData' => form_hidden('action', 'sign_contract')));
?>
<script src="<?= site_url('assets/plugins/pdf/pdf.min.js') ?>"></script>
<script>
    var pdfUrl = "<?= $tmp_pdf_url ?>";
    var pdfContainer = document.getElementById('pdfContainer');
    $('#pdfContainer').html(
        '<div id="loading-spinner" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;"><div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"><div class="dt-loader-logo"><span></span></div></div></div>'
    );
    pdfjsLib.getDocument(pdfUrl).promise.then(function (pdfDoc) {
        var numPages = pdfDoc.numPages;
        var renderQueue = Promise.resolve();

        function renderPage(pageNumber) {
            renderQueue = renderQueue.then(function () {
                return pdfDoc.getPage(pageNumber).then(function (page) {
                    var scale = 2;
                    var viewport = page.getViewport({
                        scale: scale
                    });
                    var canvas = document.createElement('canvas');
                    var context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    var renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    return page.render(renderContext).promise.then(function () {
                        var img = new Image();
                        img.src = canvas.toDataURL('image/png');
                        var pageDiv = document.createElement('div');
                        pageDiv.classList.add('pdf-page');
                        pageDiv.appendChild(img);
                        pdfContainer.appendChild(pageDiv);
                    });
                });
            });
        }
        for (var i = 1; i <= numPages; i++) {
            renderPage(i);
        }
        renderQueue.then(function () {
            $('#loading-spinner').remove();
        });
    });
    pdfContainer.addEventListener('scroll', function () {
        const scrollHeight = pdfContainer.scrollHeight;
        const scrollTop = pdfContainer.scrollTop;
        const clientHeight = pdfContainer.clientHeight;
        if (scrollHeight - scrollTop <= clientHeight + 10) {
            $('#accept_action').prop("disabled", false);
            $('#accept_action').attr("data-title", "Click to sign the contract");
        }
    });

    $('.btn-logout').on('click', function () {
        if (confirm("Are you sure you want to perform this action?")) {
            $.ajax({
                url: "<?= site_url('contract/session_logout') ?>",
                method: 'POST',
                dataType: 'JSON',
                data: {
                    contract_id: "<?= $contract->id; ?>",
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    }
                },
            });
        }
    });
</script>

<script>
    document.addEventListener('contextmenu', event => event.preventDefault());
    $(function () {
        new Sticky('[data-sticky]');
        $(".contract-left table").wrap("<div class='table-responsive'></div>");
        // Create lightbox for contract content images
        $('.contract-html-content img').wrap(function () {
            return '<a href="' + $(this).attr('src') + '" data-lightbox="contract"></a>';
        });

        $(document).on("keydown", function (event) {
            if (event.ctrlKey && (event.key === "p" || event.key === "P")) {
                event.preventDefault();
            }
        });

        $(document).on("contextmenu", function (event) {
            event.preventDefault();
        });
    })
</script>
<style>
    body {
        user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }

    .panel_s .panel-body {
        border: 1px solid #bebfc0;
    }

    .signicon {
        font-size: 15px;
    }

    .contact-text {
        font-size: 14px;
    }
</style>