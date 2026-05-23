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
   }

   #wrapper {
      overflow: hidden !important;
   }

   #proposal-wrapper {
      padding: 2%;
   }

   .panel_s .panel-body {
      border: 1px solid #bebfc0;
   }

   .proposal-html-logo {
      /* padding: 10px;
      background: #fff;
      border-radius: 20px; */
   }

   .proposal-html-subject,
   .proposal-html-number {
      color: #000;
   }

   .proposal-html-tabs {
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
   .btn-info {
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
   .btn-success:active {
      color: #000;
      border: #FDC900 !important;
      background-color: #FDC900 !important;
   }

   .navbar-fixed-bottom {
      background-color: #456e36;
      color: #fff;
      font-weight: 500;
      border-top: 1px solid #000;
   }

   .success-bg.content-view-status {
      color: #000 !important;
   }

   .header-action-section {
      display: flex;
      justify-content: flex-end;
   }

   .proposal-status-img {
      height: 31px;
      width: 120px;
      background: #fff;
      padding: 4px;
      border-radius: 4px;
      margin-top: 5px;
   }

   .logo.img-responsive {
      width: 20%;
   }

   img {
      pointer-events: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
   }

   @media (max-width: 767px) {

      .logo.img-responsive {
         width: 50%;
      }

      .action-button {
         border: 1px solid #fff !important;
      }

      .proposal-html-logo {
         display: flex;
         justify-content: center;
      }

      .proposal-status-img {
         height: 31px;
         width: 120px;
         position: static;
         margin-top: 5px;
      }

      .header-action-section {
         display: flex;
         justify-content: center;
         align-items: center;
         margin-top: 25px;
      }
   }

   @media (min-width: 1281px),
   (min-width: 1025px) and (max-width: 1280px) {
      .preview-top-wrapper {
         margin-top: 0px;
      }
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

   @keyframes logo-flip {
      0% {
         transform: rotateY(0deg)
      }

      100% {
         transform: rotateY(360deg)
      }
   }

   .proposal-right {
      color: #000;
   }
</style>
<div id="proposal-wrapper">
   <div class="preview-top-wrapper">
      <div class="row">
         <div class="col-md-3">
            <div class="proposal-html-logo">
               <?php echo get_white_company_logo(); ?>
            </div>
         </div>
         <div class="col-md-9 header-action-section">
            <?php if (($proposal->status != 2 && $proposal->status != 3)) {
               if (!empty($proposal->open_till) && date('Y-m-d', strtotime($proposal->open_till)) < date('Y-m-d')) {
                  ?>
                  <div class="status-image">
                     <img class="proposal-status-img" src="<?= site_url('assets/images/expired.png') ?>" />
                  </div>
                  <?php
               } else { ?>
                  <?php if ($identity_confirmation_enabled == '1') { ?>
                     <button type="button" id="accept_action" class="btn btn-success pull-right action-button mleft5">
                        <i class="fa fa-check"></i> <?php echo _l('proposal_accept_info'); ?>
                     </button>
                  <?php } else { ?>
                     <?php echo form_open($this->uri->uri_string()); ?>
                     <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off"
                        class="btn btn-success pull-right action-button mleft5"><i class="fa fa-check"></i>
                        <?php echo _l('proposal_accept_info'); ?></button>
                     <?php echo form_hidden('action', 'accept_proposal'); ?>
                     <?php echo form_close(); ?>
                  <?php } ?>
                  <?php echo form_open($this->uri->uri_string()); ?>
                  <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off"
                     class="btn btn-default pull-right action-button mleft5"><i class="fa fa-remove"></i>
                     <?php echo _l('proposal_decline_info'); ?></button>
                  <?php echo form_hidden('action', 'decline_proposal'); ?>
                  <?php echo form_close(); ?>
               <?php } ?>
               <!-- end expired proposal -->
            <?php } else {
               if ($proposal->status == 2) {
                  ?>
                  <div class="status-image">
                     <img class="proposal-status-img" src="<?= site_url('assets/images/declined.png') ?>" />
                  </div>
                  <?php
               } else if ($proposal->status == 3) {
                  ?>
                     <div class="status-image">
                        <img class="proposal-status-img" src="<?= site_url('assets/images/accepted.png') ?>" />
                     </div>
                  <?php
               }
            } ?>
            <?php if (!is_client_logged_in() && !is_staff_logged_in()) { ?>
               <?php if ($proposal->status == 3 && $proposal->download_request == '1') { ?>
                  <?php echo form_open($this->uri->uri_string()); ?>
                  <button type="submit" class="btn btn-default pull-right action-button mleft5"><i
                        class="fa fa-file-pdf-o"></i> <?php echo _l('clients_invoice_html_btn_download'); ?></button>
                  <?php echo form_hidden('action', 'proposal_pdf'); ?>
                  <?php echo form_close(); ?>
               <?php } ?>

               <!--<?php if ($proposal->status == 3 && $proposal->download_request != '1') { ?>-->
               <!--   <?php if ($proposal->download_request == '2') { ?>-->
               <!--      <div class="pull-right mtop10"><i class="fa fa-clock-o" aria-hidden="true"></i>-->
               <!--         <?php echo _l('proposal_download_request_pending'); ?></div>-->
               <!--   <?php } else { ?>-->
               <!--      <button type="button" class="btn btn-default request-for-download pull-right mleft5"><i-->
               <!--            class="fa fa-file-pdf-o"></i> <?php echo _l('proposal_download_request'); ?></button>-->
               <!--   <?php } ?>-->
               <!--<?php } ?>-->
            <?php } else { ?>
               <?php echo form_open($this->uri->uri_string()); ?>
               <button type="submit" class="btn btn-default pull-right action-button mleft5 mtop5"><i
                     class="fa fa-file-pdf-o"></i> <?php echo _l('clients_invoice_html_btn_download'); ?></button>
               <?php echo form_hidden('action', 'proposal_pdf'); ?>
               <?php echo form_close(); ?>
            <?php } ?>

            <?php if (is_client_logged_in() && has_contact_permission('proposals')) { ?>
               <a href="<?php echo site_url('clients/proposals/'); ?>"
                  class="btn btn-default mleft5 pull-right action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
               </a>
            <?php } ?>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md-8 proposal-left">
         <div class="panel_s mtop20">
            <div class="panel-body tc-content proposal-html-content" id="pdfContainer">

            </div>
         </div>
      </div>
      <div class="col-md-4 proposal-right">
         <div class="inner mtop20 proposal-html-tabs">
            <ul class="nav nav-tabs nav-tabs-flat mbot15" role="tablist">
               <li role="presentation" class="<?php if (!$this->input->get('tab') || $this->input->get('tab') === 'summary') {
                  echo 'active';
               } ?>">
                  <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">
                     <i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo _l('summary'); ?></a>
               </li>
               <?php if ($proposal->allow_comments == 1) { ?>
                  <li role="presentation" class="<?php if ($this->input->get('tab') === 'discussion') {
                     echo 'active';
                  } ?>">
                     <a href="#discussion" aria-controls="discussion" role="tab" data-toggle="tab">
                        <i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo _l('discussion'); ?>
                     </a>
                  </li>
               <?php } ?>
            </ul>
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab') || $this->input->get('tab') === 'summary') {
                  echo ' active';
               } ?>" id="summary">
                  <address class="proposal-html-company-info">
                     <?php echo format_organization_info(); ?>
                  </address>
                  <hr />
                  <p class="bold proposal-html-information">
                     <?php echo _l('proposal_information'); ?>
                  </p>
                  <address class="no-margin proposal-html-info">
                     <?php echo format_proposal_info($proposal, 'html'); ?>
                  </address>
                  <div class="row mtop20">
                     <?php if ($proposal->total != 0) { ?>
                        <div class="col-md-12 proposal-html-total">
                           <h4 class="bold mbot30">
                              <?php echo _l('proposal_total_info', app_format_money($proposal->total, $proposal->currency_name)); ?>
                           </h4>
                        </div>
                     <?php } ?>
                     <div class="col-md-4 text-muted proposal-status">
                        <?php echo _l('proposal_status'); ?>
                     </div>
                     <div class="col-md-8 proposal-status">
                        <?php echo format_proposal_status($proposal->status, '', false); ?>
                     </div>
                     <div class="col-md-4 text-muted proposal-date">
                        <?php echo _l('proposal_date'); ?>
                     </div>
                     <div class="col-md-8 proposal-date">
                        <?php echo _d($proposal->date); ?>
                     </div>
                     <?php if (!empty($proposal->open_till)) { ?>
                        <div class="col-md-4 text-muted proposal-open-till">
                           <?php echo _l('proposal_open_till'); ?>
                        </div>
                        <div class="col-md-8 proposal-open-till">
                           <?php echo _d($proposal->open_till); ?>
                        </div>
                     <?php } ?>
                  </div>
                  <!--<?php if (count($proposal->attachments) > 0 && $proposal->visible_attachments_to_customer_found == true) { ?>-->
                     <!--<div class="proposal-attachments">-->
                        <!--<hr />-->
                        <!--<p class="bold mbot15"><?php echo _l('proposal_files'); ?></p>-->
                        <!--<?php foreach ($proposal->attachments as $attachment) {
                        //   if ($attachment['visible_to_customer'] == 0) {
                            // continue;
                        // <!--   }-->
                        // <!--   $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);-->
                        // <!--   if (!empty($attachment['external'])) {-->
                        // <!--      $attachment_url = $attachment['external_link'];-->
                        // <!--   }-->
                        //   ?>
                        <!--   <div class="col-md-12 row mbot15">-->
                        <!--      <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>-->
                        <!--      </div>-->
                        <!--      <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>-->
                        <!--   </div>-->
                        <!--<?php } ?>-->
                     <!--</div>-->
                  <!--<?php } ?>-->
               </div>
               <?php if ($proposal->allow_comments == 1) { ?>
                  <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') === 'discussion') {
                     echo ' active';
                  } ?>" id="discussion">
                     <?php echo form_open($this->uri->uri_string()); ?>
                     <div class="proposal-comment">
                        <textarea name="content" rows="4" class="form-control"></textarea>
                        <button type="submit" class="btn btn-info mtop10 pull-right"
                           data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('proposal_add_comment'); ?></button>
                        <?php echo form_hidden('action', 'proposal_comment'); ?>
                     </div>
                     <?php echo form_close(); ?>
                     <div class="clearfix"></div>
                     <?php
                     $proposal_comments = '';
                     foreach ($comments as $comment) {
                        $proposal_comments .= '<div class="proposal_comment mtop10 mbot20" data-commentid="' . $comment['id'] . '">';
                        if ($comment['staffid'] != 0) {
                           $proposal_comments .= staff_profile_image($comment['staffid'], array(
                              'staff-profile-image-small',
                              'media-object img-circle pull-left mright10'
                           ));
                        }
                        $proposal_comments .= '<div class="media-body valign-middle">';
                        $proposal_comments .= '<div class="mtop5">';
                        $proposal_comments .= '<b>';
                        if ($comment['staffid'] != 0) {
                           $proposal_comments .= get_staff_full_name($comment['staffid']);
                        } else {
                           $proposal_comments .= _l('is_customer_indicator');
                        }
                        $proposal_comments .= '</b>';
                        $proposal_comments .= ' - <small class="mtop10 text-muted">' . time_ago($comment['dateadded']) . '</small>';
                        $proposal_comments .= '</div>';
                        $proposal_comments .= '<br />';
                        $proposal_comments .= check_for_links($comment['content']) . '<br />';
                        $proposal_comments .= '</div>';
                        $proposal_comments .= '</div>';
                     }
                     echo $proposal_comments; ?>
                  </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php
if ($identity_confirmation_enabled == '1') {
   get_template_part('identity_confirmation_form', array('formData' => form_hidden('action', 'accept_proposal')));
}
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

      // Render all pages
      for (var i = 1; i <= numPages; i++) {
         renderPage(i);
      }

      renderQueue.then(function () {
         $('#loading-spinner').remove();
      });
   });
</script>
<script>
   $(function () {
      new Sticky('[data-sticky]');
      $(".proposal-left table").wrap("<div class='table-responsive'></div>");
      // Create lightbox for proposal content images
      $('.proposal-content img').wrap(function () {
         return '<a href="' + $(this).attr('src') + '" data-lightbox="proposal"></a>';
      });

      $(document).on("keydown", function (event) {
         if (event.ctrlKey && (event.key === "p" || event.key === "P")) {
            event.preventDefault();
         }
      });

      // $(document).on("contextmenu", function (event) {
      //    event.preventDefault();
      // });

      $('.request-for-download').on('click', function () {
         var form_data = new FormData($('form')[0]);
         form_data.append('id', "<?= $proposal->id ?>")
         $.ajax({
            url: "<?= site_url('proposal/download_request') ?>",
            type: 'POST',
            data: form_data,
            dataType: 'JSON',
            contentType: false,
            processData: false,
            success: function (response) {
               if (response.success) {
                  alert_float('success', response.message);
                  setTimeout(() => {
                     location.reload(true);
                  }, 1500);
               } else {
                  alert_float('danger', "Error : File not deleted...");
               }
            },
            error: function () {
               alert_float('danger', "Something went wrong...");
            }
         });
      });

   });
</script>
</div>