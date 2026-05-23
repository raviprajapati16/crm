<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
   body {
      user-select: none;
      -moz-user-select: none;
      -webkit-user-select: none;
      -ms-user-select: none;
      background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
   }

   .btn-default,
   .btn-success,
   .btn-info,
   .btn-primary {
      color: #fff;
      border: #456e36 !important;
      background-color: #456e36 !important;
   }

   .logo.img-responsive {
      width: 20%;
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

   .label {
      background-color: #fff;
      font-weight: 600;
   }

   .navbar-fixed-bottom {
      background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
      color: #fff;
      font-weight: 500;
      border-top: 1px solid #000;
   }

   @media (max-width: 767px) {
      .action-button {
         border: 1px solid #fff !important;
      }
   }
</style>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="invoice-html-logo">
               <?php echo get_white_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="pull-left">
                  <h3 class="bold no-mtop invoice-html-number no-mbot">
                     <span class="sticky-visible hide">
                        <?php echo format_invoice_number($invoice->id); ?>
                     </span>
                  </h3>
                  <h4 class="invoice-html-status mtop7">
                     <?php echo format_invoice_status($invoice->status, '', true); ?>
                  </h4>
               </div>
               <div class="visible-xs">
                  <div class="clearfix"></div>
               </div>
               <a href="#" class="btn btn-success pull-right mleft5 mtop5 action-button invoice-html-pay-now-top hide sticky-hidden
                  <?php if (
                     ($invoice->status != Invoices_model::STATUS_PAID && $invoice->status != Invoices_model::STATUS_CANCELLED
                        && $invoice->total > 0) && found_invoice_mode($payment_modes, $invoice->id, false)
                  ) {
                     echo ' pay-now-top';
                  } ?>">
                  <?php echo _l('invoice_html_online_payment_button_text'); ?>
               </a>
               <?php echo form_open($this->uri->uri_string()); ?>
               <button type="submit" name="invoicepdf" value="invoicepdf"
                  class="btn btn-default pull-right action-button mtop5">
                  <i class='fa fa-file-pdf-o'></i>
                  <?php echo _l('clients_invoice_html_btn_download'); ?>
               </button>
               <?php echo form_close(); ?>
               <?php if (is_client_logged_in() && has_contact_permission('invoices')) { ?>
                  <a href="<?php echo site_url('clients/invoices/'); ?>"
                     class="btn btn-default pull-right mtop5 mright5 action-button go-to-portal">
                     <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
               <?php } ?>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body" id="invoice-preview">

   </div>
</div>
<script>
   $(function () {
      new Sticky('[data-sticky]');
      var $payNowTop = $('.pay-now-top');
      if ($payNowTop.length && !$('#pay_now').isInViewport()) {
         $payNowTop.removeClass('hide');
         $('.pay-now-top').on('click', function (e) {
            e.preventDefault();
            $('html,body').animate({
               scrollTop: $("#online_payment_form").offset().top
            },
               'slow');
         });
      }

      $('#online_payment_form').appFormValidator();

      var online_payments = $('.online-payment-radio');
      if (online_payments.length == 1) {
         online_payments.find('input').prop('checked', true);
      }

      invoice_preview('tax-invoice');

      function invoice_preview(type) {
         $('#invoice-preview').html(
            '<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
         $.ajax({
            url: "<?php echo site_url('invoice/preview') ?>",
            method: "POST",
            data: {
               invoice_id: "<?= $invoice->id ?>",
               type: type
            },
            dataType: 'json'
         }).done(function (result) {
            if (result.success) {
               // Create iframe
               var iframe = $('<iframe>', {
                  width: '100%',
                  height: '600px',
                  frameborder: '0',
                  allowfullscreen: true
               });

               // Append iframe to DOM first
               $('#invoice-preview').html(iframe);

               // Write HTML content into the iframe
               var iframeDoc = iframe[0].contentWindow || iframe[0].contentDocument;
               if (iframeDoc.document) iframeDoc = iframeDoc.document;

               iframeDoc.open();
               iframeDoc.write(result.html);
               iframeDoc.close();
            } else {
               alert_float('danger', "Something went wrong");
            }
         });
      }
   });
</script>