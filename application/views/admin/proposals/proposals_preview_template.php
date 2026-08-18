<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id', $proposal->id); ?>
<?php echo form_hidden('_attachment_sale_type', 'proposal'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_proposal" aria-controls="tab_proposal" role="tab" data-toggle="tab">
                     <?php echo _l('proposal'); ?>
                  </a>
               </li>
               <?php if (isset($proposal)) { ?>
                  <li role="presentation">
                     <a href="#tab_payments" aria-controls="tab_payments" role="tab" data-toggle="tab">
                        Payment Received
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_comments" onclick="get_proposal_comments(); return false;" aria-controls="tab_comments" role="tab" data-toggle="tab">
                        <?php echo _l('proposal_comments'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $proposal->id; ?> + '/' + 'proposal', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                        <?php echo _l('estimate_reminders'); ?>
                        <?php
                        $total_reminders = total_rows(
                           db_prefix() . 'reminders',
                           array(
                              'isnotified' => 0,
                              'staff' => get_staff_user_id(),
                              'rel_type' => 'proposal',
                              'rel_id' => $proposal->id,
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
                     <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $proposal->id; ?>,'proposal'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                        <?php echo _l('tasks'); ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $proposal->id; ?>,'proposals'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
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
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                     <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                        <?php if (!is_mobile()) { ?>
                           <i class="fa fa-eye"></i>
                        <?php } else { ?>
                           <?php echo _l('view_tracking'); ?>
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
            <?php echo format_proposal_status($proposal->status, 'pull-left mright5 mtop5'); ?>
         </div>
         <div class="col-md-9 text-right _buttons proposal_buttons">
       <?php if (has_permission('proposals', '', 'edit') && ($proposal->status == 6 || $proposal->status == 4)) { ?>
    <a href="<?php echo admin_url('proposals/proposal/' . $proposal->id); ?>" 
       data-placement="left" 
       data-toggle="tooltip" 
       title="<?php echo _l('proposal_edit'); ?>" 
       class="btn btn-default btn-with-tooltip" 
       data-placement="bottom">
       <i class="fa fa-pencil-square-o"></i>
    </a>
<?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                                                                                                                                                                        echo ' PDF';
                                                                                                                                                                     } ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo admin_url('proposals/pdf/' . $proposal->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo admin_url('proposals/pdf/' . $proposal->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li><a href="<?php echo admin_url('proposals/pdf/' . $proposal->id); ?>"><?php echo _l('download'); ?></a></li>
               </ul>
            </div>
            <?php
            if (!empty($proposal->phone)) {
               $countryData =  get_country($proposal->country);
               $proposal_link = site_url("proposal/$proposal->id/$proposal->hash");
               $phoneNumberArr = phonenumberSplit($proposal->phone);
               if (!empty($phoneNumberArr)) {
                  $whatsappMessage = "Dear " . $proposal->proposal_to . ",\n\n This proposal is valid until: " . date("d-m-Y", strtotime($proposal->open_till)) . ". \n\n You can view the proposal on the following link: $proposal_link. \n\n" . get_whatsapp_signature();
                  foreach ($phoneNumberArr as $phoneNumber) {
                     $whatappLink = generateWhatsappLink($phoneNumber, (isset($countryData->iso2)) ? $countryData->iso2 : null, $whatsappMessage);
            ?>
                     <a href="<?= $whatappLink ?>" target="_blank" class="btn btn-default btn-with-tooltip"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?= $phoneNumber ?>" data-placement="bottom"><i class="fa fa-whatsapp"></i></span></a>
               <?php }
               } ?>
            <?php
            }
            ?>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#proposal_send_to_customer" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?php echo _l('proposal_send_to_email'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="<?php echo site_url('proposal/' . $proposal->id . '/' . $proposal->hash); ?>" target="_blank"><?php echo _l('proposal_view'); ?></a>
                  </li>
                  <?php hooks()->do_action('after_proposal_view_as_client_link', $proposal); ?>
                  <?php if (!empty($proposal->open_till) && date('Y-m-d') < $proposal->open_till && ($proposal->status == 4 || $proposal->status == 1) && is_proposals_expiry_reminders_enabled()) { ?>
                     <li>
                        <a href="<?php echo admin_url('proposals/send_expiry_reminder/' . $proposal->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                     </li>
                  <?php } ?>
                  <li>
                     <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if (has_permission('proposals', '', 'create')) { ?>
                     <li>
                        <a href="<?php echo admin_url() . 'proposals/copy/' . $proposal->id; ?>"><?php echo _l('proposal_copy'); ?></a>
                     </li>
                  <?php } ?>
                  <?php if ($proposal->estimate_id == NULL && $proposal->invoice_id == NULL) { ?>
                     <?php foreach ($proposal_statuses as $status) {
                        if (has_permission('proposals', '', 'edit')) {
                           if ($proposal->status != $status) { ?>
                              <li>
                                 <a href="<?php echo admin_url() . 'proposals/mark_action_status/' . $status . '/' . $proposal->id; ?>"><?php echo _l('proposal_mark_as', format_proposal_status($status, '', false)); ?></a>
                              </li>
                     <?php
                           }
                        }
                     } ?>
                  <?php } ?>
                  <?php if (!empty($proposal->signature) && has_permission('proposals', '', 'delete')) { ?>
                     <li>
                        <a href="<?php echo admin_url('proposals/clear_signature/' . $proposal->id); ?>" class="_delete">
                           <?php echo _l('clear_signature'); ?>
                        </a>
                     </li>
                  <?php } ?>
                  <?php if (has_permission('proposals', '', 'delete')) { ?>
                     <li>
                        <a href="<?php echo admin_url() . 'proposals/delete/' . $proposal->id; ?>" class="text-danger delete-text _delete"><?php echo _l('proposal_delete'); ?></a>
                     </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if ($proposal->estimate_id == NULL && $proposal->invoice_id == NULL) { ?>
               <?php if (has_permission('estimates', '', 'create') || has_permission('invoices', '', 'create')) { ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-success dropdown-toggle<?php if ($proposal->rel_type == 'customer' && total_rows(db_prefix() . 'clients', array('active' => 0, 'userid' => $proposal->rel_id)) > 0) {
                                                                                    echo ' disabled';
                                                                                 } ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo _l('proposal_convert'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <?php
                        $disable_convert = false;
                        $not_related = false;

                        if ($proposal->rel_type == 'lead') {
                           if (total_rows(db_prefix() . 'clients', array('leadid' => $proposal->rel_id, "deleted_at" => NULL)) == 0) {
                              $disable_convert = true;
                              $help_text = 'proposal_convert_to_lead_disabled_help';
                           }
                        } else if (empty($proposal->rel_type)) {
                           $disable_convert = true;
                           $help_text = 'proposal_convert_not_related_help';
                        }
                        ?>
                        <!-- <?php if (has_permission('estimates', '', 'create')) { ?>
                           <li <?php if ($disable_convert) {
                                    echo 'data-toggle="tooltip" title="' . _l($help_text, _l('proposal_convert_estimate')) . '"';
                                 } ?>><a href="#" <?php if ($disable_convert) {
                                                      echo 'style="cursor:not-allowed;" onclick="return false;"';
                                                   } else {
                                                      echo 'data-template="estimate" onclick="proposal_convert_template(this); return false;"';
                                                   } ?>><?php echo _l('proposal_convert_estimate'); ?></a></li>
                        <?php } ?> -->
                        <?php if (has_permission('invoices', '', 'create')) { ?>
                           <li <?php if ($disable_convert) {
                                    echo 'data-toggle="tooltip" title="' . _l($help_text, _l('proposal_convert_invoice')) . '"';
                                 } ?>><a href="#" <?php if ($disable_convert) {
                                                      echo 'style="cursor:not-allowed;" onclick="return false;"';
                                                   } else {
                                                      echo 'data-template="invoice" onclick="proposal_convert_template(this); return false;"';
                                                   } ?>><?php echo _l('proposal_convert_invoice'); ?></a></li>
                        <?php } ?>
                     </ul>
                  </div>
               <?php } ?>
            <?php } else {
               if ($proposal->estimate_id != NULL) {
                  echo '<a href="' . admin_url('estimates/list_estimates/' . $proposal->estimate_id) . '" class="btn btn-info">' . format_estimate_number($proposal->estimate_id) . '</a>';
               } else {
                  echo '<a href="' . admin_url('invoices/list_invoices/' . $proposal->invoice_id) . '" class="btn btn-info">' . format_invoice_number($proposal->invoice_id) . '</a>';
               }
            } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_proposal">

                  <div class="row mtop10">
                     <?php if ($proposal->status == 3 && !empty($proposal->acceptance_firstname) && !empty($proposal->acceptance_lastname) && !empty($proposal->acceptance_email)) { ?>
                        <div class="col-md-12">
                           <div class="alert alert-info">
                              <?php echo _l('accepted_identity_info', array(
                                 _l('proposal_lowercase'),
                                 '<b>' . $proposal->acceptance_firstname . ' ' . $proposal->acceptance_lastname . '</b> (<a href="mailto:' . $proposal->acceptance_email . '">' . $proposal->acceptance_email . '</a>)',
                                 '<b>' . _dt($proposal->acceptance_date) . '</b>',
                                 '<b>' . $proposal->acceptance_ip . '</b>' . (is_admin() ? '&nbsp;<a href="' . admin_url('proposals/clear_acceptance_info/' . $proposal->id) . '" class="_delete text-muted" data-toggle="tooltip" data-title="' . _l('clear_this_information') . '"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
                  <!--<hr class="hr-panel-heading" />-->
                                  <!-- Nested tabs within proposal tab -->
                  <div class="row">
                     <div class="col-md-12">
                        <ul class="nav nav-tabs" role="tablist" id="proposal-nested-tabs">
                           <li role="presentation" class="active">
                              <a href="#nested_tab_proposal_content" aria-controls="nested_tab_proposal_content" role="tab" data-toggle="tab" onclick="proposal_preview()">
                                 Proposal Preview
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

                  <div class="tab-content" id="proposal-nested-content">
                     <!-- Proposal Content Tab -->
                     <div role="tabpanel" class="tab-pane active" id="nested_tab_proposal_content">
                        <div class="row">
                           <div class="col-md-12" id="proposal_preview">
                           </div>
                        </div>
                     </div>

                     <?php if (is_admin()) { ?>
                        <!-- Terms and Conditions Editor Tab -->
                        <div role="tabpanel" class="tab-pane" id="nested_tab_terms_conditions">
                           <?php if (isset($proposal_merge_fields)) { ?>
                              <p class="bold text-right"><a href="#" onclick="slideToggle('.avilable_merge_fields'); return false;"><?php echo _l('available_merge_fields'); ?></a></p>
                              <hr class="hr-panel-heading" />
                              <div class="hide avilable_merge_fields mtop15">
                                 <div class="row">
                                    <div class="col-md-12">
                                       <ul class="list-group">
                                          <?php
                                          foreach ($proposal_merge_fields as $field) {
                                             foreach ($field as $f) {
                                                echo '<li class="list-group-item"><b>' . $f['name'] . '</b> <a href="#" class="pull-right" onclick="insert_proposal_merge_field(this); return false;">' . $f['key'] . '</a></li>';
                                             }
                                          }
                                          ?>
                                       </ul>
                                    </div>
                                 </div>
                              </div>
                           <?php } ?>
                           <div class="row mtop15">
                              <div class="col-md-12">
                                 <div class="editable proposal tc-content" id="proposal_content_area" style="border:1px solid #d2d2d2;min-height:70px;border-radius:4px;">
                                    <?php if (empty($proposal->content)) {
                                       echo '<span class="text-danger text-uppercase mtop15 editor-add-content-notice"> ' . _l('click_to_add_content') . '</span>';
                                    } else {
                                       echo $proposal->content;
                                    }
                                    ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
                  <?php if (!empty($proposal->signature)) { ?>
                     <div class="row mtop25">
                        <div class="col-md-6 col-md-offset-6 text-right">
                           <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                              <?php if (has_permission('proposals', '', 'delete')) { ?>
                                 <a href="<?php echo admin_url('proposals/clear_signature/' . $proposal->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                    <i class="fa fa-remove"></i>
                                 </a>
                              <?php } ?>
                           </p>
                           <div class="pull-right">
                              <img src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature)); ?>" class="img-responsive" alt="">
                           </div>
                        </div>
                     </div>
                  <?php } ?>
               </div>

               <div role="tabpanel" class="tab-pane" id="tab_comments">
                  <div class="row proposal-comments mtop15">
                     <div class="col-md-12">
                        <div id="proposal-comments"></div>
                        <div class="clearfix"></div>
                        <textarea name="content" id="comment" rows="4" class="form-control mtop15 proposal-comment"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_proposal_comment();"><?php echo _l('proposal_add_comment'); ?></button>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_payments">
                  <?php
                  $this->load->view(
                     'admin/proposals/payments_table',
                     array(
                        'payments' => $payments,
                        'proposal' => $proposal
                     )
                  );
                  ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_notes">
                  <?php echo form_open(admin_url('proposals/add_note/' . $proposal->id), array('id' => 'sales-notes', 'class' => 'proposal-notes-form')); ?>
                  <?php echo render_textarea('description'); ?>
                  <div class="row mtop15 mbot15">
                     <div class="col-md-6"></div>
                     <div class="col-md-6 text-right">
                        <div class="pull-right" style="width: 250px; text-align: left;">
                            <?php 
                            $source_staff = isset($members) ? $members : (isset($staff) ? $staff : []);
                            $filtered_notify_staff = get_staff_for_notification($source_staff);
                            echo render_select('notify_staff_id', $filtered_notify_staff, array('staffid', array('firstname', 'lastname')), 'Select Staff to Notify', '', array('required' => 'true')); 
                            ?>
                        </div>
                        <div class="clearfix"></div>
                        <button type="submit" class="btn btn-info pull-right mtop15"><?php echo _l('estimate_add_note'); ?></button>
                     </div>
                  </div>
                  <?php echo form_close(); ?>
                  <script>
                  $(document).ajaxComplete(function(event, xhr, settings) {
                      if (settings.url.indexOf("proposals/add_note") > -1) {
                          $.get(admin_url + 'proposals/get_whatsapp_link', function(res) {
                              if (res && res.link) {
                                  if (typeof swal !== 'undefined') {
                                      swal({
                                          title: "Notification Ready",
                                          text: "Email sent successfully! Click below to send the WhatsApp message.",
                                          type: "success",
                                          showCancelButton: true,
                                          confirmButtonText: "Send WhatsApp",
                                          cancelButtonText: "Close"
                                      }, function(isConfirm) {
                                          if (isConfirm) {
                                              window.open(res.link, '_blank');
                                          }
                                      });
                                  } else {
                                      window.open(res.link, '_blank');
                                  }
                              }
                          }, 'json');
                      }
                  });
                  </script>
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
                        get_tracked_emails($proposal->id, 'proposal')
                     )
                  );
                  ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tasks">
                  <?php init_relation_tasks_table(array('data-new-rel-id' => $proposal->id, 'data-new-rel-type' => 'proposal')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-proposal-<?php echo $proposal->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('proposal_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array(_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder', array('id' => $proposal->id, 'name' => 'proposal', 'members' => $members, 'reminder_title' => _l('proposal_set_reminder_title'))); ?>
               </div>
               <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                  <?php
                  $views_activity = get_views_tracking('proposal', $proposal->id);
                  if (count($views_activity) === 0) {
                     echo '<h4 class="no-margin">' . _l('not_viewed_yet', _l('proposal_lowercase')) . '</h4>';
                  }
                  foreach ($views_activity as $activity) { ?>
                     <p class="text-success no-margin">
                        <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                     </p>
                     <p class="text-muted">
                        <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                     </p>
                     <hr />
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/proposals/send_proposal_to_email_template'); ?>
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
   proposal_preview();
   // defined in manage proposals
   proposal_id = '<?php echo $proposal->id; ?>';

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
            save_proposal_content(true);
         });

         editor.addShortcut('Meta+S', '', 'mceSave');

         editor.on('MouseLeave blur', function() {
            if (tinymce.activeEditor.isDirty()) {
               save_proposal_content();
            }
         });

         var typingTimer;
         var doneTypingInterval = 1000;
         editor.on('input change keyup', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(save_proposal_content, doneTypingInterval);
         });

         editor.on('MouseDown ContextMenu', function() {
            if (!is_mobile() && !$('.left-column').hasClass('hide')) {
               contract_full_view();
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
            save_contract_content();
         }
      });
   }

   tinymce.init(editor_settings);

   function insert_template(varname) {
      var data = $("#" + varname).attr("data-title");
      tinymce.activeEditor.execCommand('mceInsertContent', false, data);
      tinymce.activeEditor.focus();
   }


   $(document).ready(function() {
    //   $(document).on('click', '.btn-payment-create', function() {
    //      get_payment_modal();
    //   });
    $(document).on('click', '.btn-payment-create', function() {
    console.log("✅ Create Payment button clicked!");
    get_payment_modal();
});


      $(document).on('click', '.btn-edit-payment', function() {
         var id = $(this).attr('data-id');
         get_payment_modal(id);
      });

      $(document).on('click', '#payment-form button[type="submit"]', function(e) {
         e.preventDefault();
         var formId = 'payment-form';
         var isValid = true;
         $('#payment-form input, #payment-form textarea, #payment-form select').each(function(index, item) {
            $(this).closest('.form-group').find('.error').remove();
            if ($(this).prop('required')) {
               if (this.value === "" || this.value === null) {
                  if ($(this).is('select')) {
                     $(this).closest('.form-group').append('<span class="text-danger error">This field is required</span>');
                  } else {
                     $(this).after('<span class="text-danger error">This field is required</span>');
                  }
                  isValid = false;
               }
            }
         });
         if (isValid) {
            var $btn = $('#payment-form button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
            $btn.closest('form').submit();
         }
      });

      $(document).on('input', '#payment-form input[name="amount"]', function() {
         $(this).siblings(".text-danger").remove();
         var totalPendingAmount = Number($('#total_pending_amount').text()) || 0;
         var enteredAmount = $(this).val();
         var validAmount = enteredAmount.match(/^\d*\.?\d{0,2}$/);
         if (!validAmount) {
            $(this).val(enteredAmount.slice(0, enteredAmount.length - 1));
            return;
         }
         enteredAmount = Number(enteredAmount);
         if (enteredAmount == 0) {
            $(this).val("");
            return;
         }
         if (enteredAmount > totalPendingAmount) {
            $('#payment-form button[type="submit"]').prop('disabled', true);
            $(this).after("<span id='amount-error' class='text-danger'>You can't enter more than the remaining amount</span>");
         } else {
            $('#payment-form button[type="submit"]').prop('disabled', false);
         }
      });



   });

   function get_payment_modal(id = "") {
      $('#payment-modal .modal-content').html("");
      $.ajax({
         url: "<?php echo admin_url('proposals/get_payment_modal') ?>",
         method: "POST",
         data: {
            id: id,
            proposal_id: "<?= $proposal->id ?>",
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            $('#payment-modal').modal('show');
            $('#payment-modal .modal-content').html(result.html);
            appSelectPicker();
            appDatepicker();
            $(function() {
               appValidateForm($('#payment-form'), {
                  amount: 'required',
                  date: 'required'
               });
            });
         } else {
            alert_float('danger', "Something went wrong");
         }
      });
   }

   function proposal_preview() {

      $('#proposal_preview').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
      $.ajax({
         url: "<?php echo admin_url('proposals/preview') ?>",
         method: "POST",
         data: {
            proposal_id: "<?= $proposal->id ?>",
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            // Create iframe
            var iframe = $('<iframe>', {
               width: '100%',
               height: '600px',
               frameborder: '0',
               allowfullscreen: true
            });

            // Append iframe to DOM first
            $('#proposal_preview').html(iframe);

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
</script>