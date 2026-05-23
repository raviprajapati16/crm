<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ((debits_can_be_applied_to_purchase($purchase->status) && $debits_available > 0)) { ?>
   <div class="alert alert-warning mbot5">
      <?= app_format_money($debits_available, $purchase->currency) ?> debits available.
      <br />
      <a href="#" data-toggle="modal" data-target="#apply_debits">Apply Debits</a>
   </div>
<?php } ?>
<?php echo form_hidden('_attachment_sale_id', $purchase->id); ?>
<?php echo form_hidden('_attachment_sale_type', 'purchase'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_purchase" aria-controls="tab_purchase" role="tab" data-toggle="tab">
                     <?php echo _l('purchase'); ?>
                  </a>
               </li>
               <?php if (count($applied_debits) > 0) { ?>
                  <li role="presentation">
                     <a href="#purchase_applied_debits" aria-controls="purchase_applied_debits" role="tab" data-toggle="tab">
                        Applied Debits <span class="badge"><?php echo count($applied_debits); ?></span>
                     </a>
                  </li>
               <?php } ?>
               <?php if (isset($purchase)) { ?>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $purchase->id; ?> + '/' + 'purchase', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                        <?php echo _l('estimate_reminders'); ?>
                        <?php
                        $total_reminders = total_rows(
                           db_prefix() . 'reminders',
                           array(
                              'isnotified' => 0,
                              'staff' => get_staff_user_id(),
                              'rel_type' => 'purchase',
                              'rel_id' => $purchase->id,
                              'deleted_at' => NULL
                           )
                        );
                        if ($total_reminders > 0) {
                           echo '<span class="badge">' . $total_reminders . '</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $purchase->id; ?>,'purchase'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                        <?php echo _l('estimate_notes'); ?>
                        <span class="notes-total">
                           <?php if ($totalNotes > 0) { ?>
                              <span class="badge"><?php echo $totalNotes; ?></span>
                           <?php } ?>
                        </span>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                     <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                        <?php if (!is_mobile()) { ?>
                           <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                        <?php } else { ?>
                           <?php echo _l('emails_tracking'); ?>
                        <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;">
                        <i class="fa fa-expand"></i></a>
                  </li>
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="row">
         <div class="col-md-3">
            <div class="pull-left mright5 mtop5">
               <?php echo format_purchase_status($purchase->status); ?>
            </div>
         </div>
         <div class="col-md-9 text-right _buttons purchase_buttons">
            <?php if (has_permission('purchase', '', 'edit')) { ?>
               <a href="<?php echo admin_url('purchase/purchase/' . $purchase->id); ?>" data-placement="left" data-toggle="tooltip" title="Edit" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
            <?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                                                                                                                                                                        echo ' PDF';
                                                                                                                                                                     } ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo admin_url('purchase/pdf/' . $purchase->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo admin_url('purchase/pdf/' . $purchase->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li><a href="<?php echo admin_url('purchase/pdf/' . $purchase->id); ?>"><?php echo _l('download'); ?></a></li>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#purchase_send_to_vendor" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="Send Email" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if (has_permission('purchase', '', 'delete')) { ?>
                     <li>
                        <a href="<?php echo admin_url() . 'purchase/delete/' . $purchase->id; ?>" class="text-danger delete-text _delete">Delete</a>
                     </li>
                  <?php } ?>
               </ul>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_purchase">
                  <?php
                  if (count($purchase->attachments) > 0) { ?>
                     <p class="bold">Attachments</p>
                     <?php foreach ($purchase->attachments as $attachment) {
                        $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                        if (!empty($attachment['external'])) {
                           $attachment_url = $attachment['external_link'];
                        }
                     ?>
                        <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
                           <div class="col-md-8">
                              <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                              <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?> <?= ($attachment['rel_type'] != "purchase") ? "<small class='text-muted'>From " . $attachment['rel_type'] . "</small>" : "" ?></a>
                              <br />
                              <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                           </div>
                           <div class="col-md-4 text-right">
                              <?php if ($attachment['rel_type'] == "purchase") { ?>
                                 <?php if (has_permission('attachments', '', 'delete')) { ?>
                                    <a href="#" class="text-danger" onclick="delete_purchase_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                                 <?php } ?>
                              <?php } ?>
                           </div>
                        </div>
                     <?php } ?>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <!-- Nested tabs within purchase tab -->
                  <div class="row">
                     <div class="col-md-12">
                        <ul class="nav nav-tabs" role="tablist" id="purchase-nested-tabs">
                           <li role="presentation" class="active">
                              <a href="#nested_tab_purchase_content" aria-controls="nested_tab_purchase_content" role="tab" data-toggle="tab" onclick="purchase_preview()">
                                 Purchase Order Preview
                              </a>
                           </li>
                           <?php if (is_admin()) { ?>
                              <li role="presentation">
                                 <a href="#nested_tab_terms_conditions" aria-controls="nested_tab_terms_conditions" role="tab" data-toggle="tab">
                                    Terms and Condition Editor
                                 </a>
                              </li>
                           <?php } ?>
                        </ul>
                     </div>
                  </div>
                  <div class="tab-content" id="purchase-nested-content">
                     <!-- Proposal Content Tab -->
                     <div role="tabpanel" class="tab-pane active" id="nested_tab_purchase_content">
                        <div class="row">
                           <div class="col-md-12" id="purchase_preview">
                           </div>
                        </div>
                     </div>

                     <?php if (is_admin()) { ?>
                        <div role="tabpanel" class="tab-pane" id="nested_tab_terms_conditions">
                           <div class="row mtop15">
                              <div class="col-md-12">
                                 <div class="editable purchase tc-content" id="purchase_content_area" style="border:1px solid #d2d2d2;min-height:70px;border-radius:4px;">
                                    <?php
                                    echo $purchase->content;
                                    ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
               </div>
               <?php if (count($applied_debits) > 0) { ?>
               <div class="tab-pane" role="tabpanel" id="purchase_applied_debits">
                  <div class="table-responsive">
                     <table class="table table-bordered table-hover no-mtop">
                        <thead>
                           <th><span class="bold">Debit Note #</span></th>
                           <th><span class="bold">Date</span></th>
                           <th><span class="bold">Amount</span></th>
                        </thead>
                        <tbody>
                           <?php foreach ($applied_debits as $debit) { ?>
                              <tr>
                                 <td>
                                    <a href="<?php echo admin_url('debit_notes/list_debit_notes/' . $debit['debit_id']); ?>"><?php echo format_debit_note_number($debit['debit_id']); ?></a>
                                 </td>
                                 <td><?php echo _d($debit['date']); ?></td>
                                 <td><?php echo app_format_money($debit['amount'], $invoice->currency_name) ?>
                                    <?php if (has_permission('credit_notes', '', 'delete')) { ?>
                                       <a href="<?php echo admin_url('debit_notes/delete_purchase_applied_credit/' . $debit['id'] . '/' . $debit['debit_id'] . '/' . $purchase->id); ?>" class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                                    <?php } ?>
                                 </td>
                              </tr>
                           <?php } ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            <?php } ?>
               <div role="tabpanel" class="tab-pane" id="tab_notes">
                  <?php echo form_open(admin_url('purchase/add_note/' . $purchase->id), array('id' => 'sales-notes', 'class' => 'purchase-notes-form')); ?>
                  <?php echo render_textarea('description'); ?>
                  <div class="text-right">
                     <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
                  <hr />
                  <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                  <?php
                  $this->load->view(
                     'admin/includes/emails_tracking',
                     array(
                        'tracked_emails' =>
                        get_tracked_emails($purchase->id, 'purchase')
                     )
                  );
                  ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-purchase-<?php echo $purchase->id; ?>"><i class="fa fa-bell-o"></i> Set Purchase Reminder</a>
                  <hr />
                  <?php render_datatable(array("Description", "Reminder Date & Time", "Staff", _l('reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder', array('id' => $purchase->id, 'name' => 'purchase', 'members' => $members, 'reminder_title' => _l('purchase_set_reminder_title'))); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/purchase/send_purchase_to_email_template'); ?>
<?php $this->load->view('admin/debit_notes/apply_purchase_debits'); ?>
<div class="modal fade payment-modal" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">

      </div>
   </div>
</div>
<script>
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   purchase_preview();
   // defined in manage purchase
   purchase_id = '<?php echo $purchase->id; ?>';

   var _templates = [];
   var editor_settings = {
      selector: 'div.editable',
      inline: true,
      relative_urls: false,
      remove_script_host: false,
      verify_html: false,
      cleanup: false,
      apply_source_formatting: false,
      valid_elements: '+*[*]',
      valid_children: "+body[style], +style[type]",
      file_browser_callback: elFinderBrowser,
      table_default_styles: {
         width: '100%'
      },
      fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
      pagebreak_separator: '<p pagebreak="true"></p>',
      plugins: [
         'advlist pagebreak autolink autoresize lists link image charmap hr',
         'searchreplace visualblocks visualchars code',
         'media nonbreaking table contextmenu',
         'paste textcolor colorpicker'
      ],
      autoresize_bottom_margin: 50,
      insert_toolbar: 'image media quicktable | bullist numlist | h2 h3 | hr',
      selection_toolbar: 'save_button bold italic underline superscript | forecolor backcolor link | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect h2 h3',
      contextmenu: "image media inserttable | cell row column deletetable | paste pastetext searchreplace | visualblocks pagebreak charmap | code",
      setup: function(editor) {

         editor.addCommand('mceSave', function() {
            save_purchase_content(true);
         });

         editor.addShortcut('Meta+S', '', 'mceSave');

         editor.on('MouseLeave blur', function() {
            if (tinymce.activeEditor.isDirty()) {
               save_purchase_content();
            }
         });

         var typingTimer;
         var doneTypingInterval = 1000;
         editor.on('input change keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(save_purchase_content, doneTypingInterval);
         });

         editor.on('MouseDown ContextMenu', function() {
            if (!is_mobile() && !$('.left-column').hasClass('hide')) {

            }
         });

         editor.on('blur', function() {
            $.Shortcuts.start();
         });

         editor.on('focus', function() {
            $.Shortcuts.stop();
         });

      }
   }

   if (_templates.length > 0) {
      editor_settings.templates = _templates;
      editor_settings.plugins[3] = 'template ' + editor_settings.plugins[3];
      editor_settings.contextmenu = editor_settings.contextmenu.replace('inserttable', 'inserttable template');
   }

   if (is_mobile()) {

      editor_settings.theme = 'modern';
      editor_settings.mobile = {};
      editor_settings.mobile.theme = 'mobile';
      editor_settings.mobile.toolbar = _tinymce_mobile_toolbar();

      editor_settings.inline = false;
      window.addEventListener("beforeunload", function(event) {
         if (tinymce.activeEditor.isDirty()) {
            save_purchase_content();
         }
      });
   }

   tinymce.init(editor_settings);


   $('body').off('submit', '#sales-notes').on('submit', '#sales-notes', function(e) {
      e.preventDefault(); // Prevent default form submission

      var form = $(this);
      if (form.find('textarea[name="description"]').val() === '') {
         return;
      }

      $.post(form.attr('action'), form.serialize()).done(function(rel_id) {
         form.find('textarea[name="description"]').val('');
         get_sales_notes(rel_id, 'purchase');
      });
   });


   function insert_template(varname) {
      var data = $("#" + varname).attr("data-title");
      tinymce.activeEditor.execCommand('mceInsertContent', false, data);
      tinymce.activeEditor.focus();
   }

   function save_purchase_content(manual) {
      var editor = tinyMCE.activeEditor;
      var data = {};
      data.purchase_id = purchase_id;
      data.content = editor.getContent();
      $.post(admin_url + 'purchase/save_purchase_data', data).done(function(response) {
         response = JSON.parse(response);
         if (typeof(manual) != 'undefined') {
            alert_float('success', response.message);
         }
         editor.save();
      }).fail(function(error) {
         var response = JSON.parse(error.responseText);
         alert_float('danger', response.message);
      });
   }

   function purchase_preview() {
      $('#purchase_preview').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
      $.ajax({
         url: "<?php echo admin_url('purchase/preview') ?>",
         method: "POST",
         data: {
            purchase_id: "<?= $purchase->id ?>",
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            var iframe = $('<iframe>', {
               width: '100%',
               height: '600px',
               frameborder: '0',
               allowfullscreen: true
            });
            $('#purchase_preview').html(iframe);
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

   function delete_purchase_attachment(id) {
      if (confirm_delete()) {
         requestGet('purchase/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
               var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
               $("body").find('[data-attachment-id="' + id + '"]').remove();
               init_purchase(rel_id);
            }
         }).fail(function(error) {
            alert_float('danger', error.responseText);
         });
      }
   }
</script>