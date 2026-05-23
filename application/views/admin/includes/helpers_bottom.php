<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php include_once(APPPATH . 'views/admin/includes/modals/post_likes.php'); ?>
<?php include_once(APPPATH . 'views/admin/includes/modals/post_comment_likes.php'); ?>
<div id="event"></div>
<div id="newsfeed" class="animated fadeIn hide" <?php if ($this->session->flashdata('newsfeed_auto')) {
                                                  echo 'data-newsfeed-auto';
                                                } ?>>
</div>
<!-- Task modal view -->
<div class="modal fade task-modal-single" id="task-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog <?php echo get_option('task_modal_class'); ?>">
    <div class="modal-content data">

    </div>
  </div>
</div>

<!--Add/edit task modal-->
<div id="_task"></div>

<!-- Lead Data Add/Edit-->
<div class="modal fade lead-modal" id="lead-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog <?php echo get_option('lead_modal_class'); ?>">
    <div class="modal-content data">

    </div>
  </div>
</div>

<div id="timers-logout-template-warning" class="hide">
  <h2 class="bold"><?php echo _l('timers_started_confirm_logout'); ?></h2>
  <hr />
  <a href="<?php echo admin_url('authentication/logout'); ?>" class="btn btn-danger"><?php echo _l('confirm_logout'); ?></a>
</div>

<!--Lead convert to customer modal-->
<div id="lead_convert_to_customer"></div>

<!--Lead reminder modal-->
<div id="lead_reminder_modal"></div>
<!-- Task modal view -->
<div class="modal fade lead-email-send-modal" id="lead-email-send-modal" tabindex="-1" role="dialog" data-backdrop="static">
  <?php $this->load->view('admin/includes/modals/lead-send-custom-email-modal'); ?>
</div>
<div class="modal fade lead-whatsapp-send-modal" id="lead-whatsapp-send-modal" tabindex="-1" role="dialog" data-backdrop="static">
  <?php $this->load->view('admin/includes/modals/lead-send-custom-whatsapp-modal'); ?>
</div>
<div class="modal fade lead-email-track-modal" id="lead-email-track-modal" tabindex="-1" role="dialog" data-backdrop="static">
  <?php $this->load->view('admin/includes/modals/lead-email-track-modal'); ?>
</div>

<div class="modal fade email-track-body-view-modal" id="email-track-body-view-modal" tabindex="-1" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close close-email-track-body-view-modal" onclick="closeEmailBodyViewer()" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Email Viewer</h4>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default close-email-track-body-view-modal" onclick="closeEmailBodyViewer()"><?php echo _l('close'); ?></button>
      </div>
    </div>
  </div>
</div>
<script>
  function closeEmailBodyViewer() {
    $('#email-track-body-view-modal').modal('hide');
  }

  function openEmailBodyViewer(id) {
    $.ajax({
      url: "<?= admin_url('misc/get_email_tracking_mail') ?>",
      method: "POST",
      data: {
        id: id
      },
      dataType: 'json'
    }).done(function(result) {
      let modalBody = $('#email-track-body-view-modal').find('.modal-body');
      if (result.success) {
        let emailContent = result.data.email_body ? result.data.email_body : "<p>Email content not available</p>";
        let iframe = `<iframe style="width: 100%; height: 400px; border: none;"></iframe>`;
        modalBody.html(iframe);
        let iframeElement = modalBody.find("iframe")[0];
        if (iframeElement.contentWindow) {
          iframeElement.contentWindow.document.open();
          iframeElement.contentWindow.document.write(emailContent);
          iframeElement.contentWindow.document.close();
        }
      } else {
        alert_float('danger', result.message);
        modalBody.html("<p>Email content not available</p>");
      }
      $('#email-track-body-view-modal').modal('show');
    });
  }
</script>