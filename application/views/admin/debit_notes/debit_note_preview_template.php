<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id', $debit_note->id); ?>
<?php echo form_hidden('_attachment_sale_type', 'debit_note'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_debit_note" aria-controls="tab_debit_note" role="tab" data-toggle="tab">
                        <?php echo _l('debit_note'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#purchase_debited" aria-controls="purchase_debited" role="tab" data-toggle="tab">
                        Purchase Debited
                        <?php if (count($debit_note->applied_debits) > 0) {
                           echo '<span class="badge">' . count($debit_note->applied_debits) . '</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_refunds" aria-controls="tab_refunds" role="tab" data-toggle="tab">
                        <?php echo _l('refunds'); ?>
                        <?php if (count($debit_note->refunds) > 0) {
                           echo '<span class="badge">' . count($debit_note->refunds) . '</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $debit_note->id; ?> + '/' + 'debit_note', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                        <?php echo _l('reminders'); ?>
                        <?php
                        $total_reminders = total_rows(
                           db_prefix() . 'reminders',
                           array(
                              'isnotified' => 0,
                              'staff' => get_staff_user_id(),
                              'rel_type' => 'debit_note',
                              'rel_id' => $debit_note->id,
                              'deleted_at' => null
                           )
                        );
                        if ($total_reminders > 0) {
                           echo '<span class="badge">' . $total_reminders . '</span>';
                        }
                        ?>
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
                  <li role="presentation" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>">
                        <i class="fa fa-expand"></i></a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="row">
            <div class="col-md-3">
               <?php echo format_debit_note_status($debit_note->status);  ?>
            </div>
            <div class="col-md-9">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if (has_permission('debit_notes', '', 'edit') && $debit_note->status != 3) { ?>
                     <a href="<?php echo admin_url('debit_notes/debit_note/' . $debit_note->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit', _l('debit_note_lowercase')); ?>" data-placement="bottom">
                        <i class="fa fa-pencil-square-o"></i>
                     </a>
                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                                                                                                                                                                              echo ' PDF';
                                                                                                                                                                           } ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo admin_url('debit_notes/pdf/' . $debit_note->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('debit_notes/pdf/' . $debit_note->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo admin_url('debit_notes/pdf/' . $debit_note->id); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo admin_url('debit_notes/pdf/' . $debit_note->id . '?print=true'); ?>" target="_blank">
                              <?php echo _l('print'); ?>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <?php if ($debit_note->status != 3 && !empty($debit_note->vendorid)) { ?>
                     <a href="#" class="credit-note-send-to-client btn btn-default" data-toggle="modal" data-target="#debit_note_send_to_client_modal">
                        <i class="fa fa-envelope"></i>
                     </a>
                  <?php } ?>
                  <?php if ($debit_note->status == 1 && !empty($debit_note->vendorid)) { ?>
                     <a href="#" data-toggle="modal" data-target="#apply_debits" class="btn btn-info">Apply to Purchase</a>
                  <?php } ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <?php hooks()->do_action('debit_note_menu_links_start', $debit_note); ?>
                        <?php
                        if ($debit_note->status == 1 && has_permission('debit_notes', '', 'edit')) { ?>
                           <li>
                              <a href="#" onclick="refund_debit_note(); return false;" id="debit_note_refund">
                                 <?php echo _l('refund'); ?>
                              </a>
                           </li>
                        <?php }
                        // You can only mark as void, if it's not closed, not void, no credits applied, no refunds applied
                        if ($debit_note->status != 2 && $debit_note->status != 3 && !$debit_note->debits_used && !$debit_note->total_refunds && has_permission('debit_notes', '', 'edit')) { ?>
                           <li>
                              <a href="<?php echo admin_url('debit_notes/mark_void/' . $debit_note->id); ?>">Void</a>
                           </li>
                        <?php } else if ($debit_note->status == 3 && has_permission('debit_notes', '', 'edit')) { ?>
                           <li>
                              <a href="<?php echo admin_url('debit_notes/mark_open/' . $debit_note->id); ?>">Mark as Open</a>
                           </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file">
                              <?php echo _l('invoice_attach_file'); ?>
                           </a>
                        </li>
                        <?php
                        if (has_permission('debit_notes', '', 'delete')) {
                           $delete_tooltip = '';
                           $allow_delete = true;
                           if ($debit_note->status == 2) {
                              $delete_tooltip = _l('credits_applied_cant_delete_status_closed');
                              $allow_delete = false;
                           } else if ($debit_note->debits_used) {
                              $delete_tooltip = _l('credits_applied_cant_delete_debit_note');
                              $allow_delete = false;
                           } else if ($debit_note->total_refunds) {
                              $allow_delete = false;
                              $delete_tooltip = _l('refunds_applied_cant_delete_debit_note');
                           }
                        ?>
                           <li>
                              <a
                                 data-toggle="tooltip"
                                 data-title="<?php echo $delete_tooltip; ?>"
                                 href="<?php echo admin_url('debit_notes/delete/' . $debit_note->id); ?>"
                                 class="text-danger delete-text <?php if ($allow_delete) {
                                                                     echo ' _delete';
                                                                  } ?>"
                                 <?php
                                 if (!$allow_delete) {
                                    echo ' style="cursor:not-allowed;" onclick="return false;" ';
                                 }; ?>>
                                 <?php echo _l('delete'); ?>
                              </a>
                           </li>
                        <?php } ?>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_debit_note">
               <div id="credit-note-preview">
                  <div class="row">
                     <div class="col-md-6 col-sm-6">
                        <h4 class="bold">
                           <a href="<?php echo admin_url('debit_notes/debit_note/' . $debit_note->id); ?>">
                              <span id="credit-note-number">
                                 <?php echo format_debit_note_number($debit_note->id); ?>
                              </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold">Bill To:</span>
                        <address>
                           <?php echo format_customer_info($debit_note, 'debit_note', 'billing', true); ?>
                        </address>
                        <?php if ($debit_note->include_shipping == 1 && $debit_note->show_shipping_on_debit_note == 1) { ?>
                           <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                           <address>
                              <?php echo format_customer_info($debit_note, 'debit_note', 'shipping'); ?>
                           </address>
                        <?php } ?>
                        <p class="no-mbot">
                           <span class="bold">Debit Note Date</span>
                           <?php echo _d($debit_note->date) ?>
                        </p>
                        <?php if (!empty($debit_note->reference_no)) { ?>
                           <p class="no-mbot">
                              <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                              <?php echo $debit_note->reference_no; ?>
                           </p>
                        <?php } ?>
                        <?php if ($debit_note->project_id != 0 && get_option('show_project_on_debit_note') == '1') { ?>
                           <p class="no-mbot">
                              <span class="bold"><?php echo _l('project'); ?>:</span>
                              <?php echo get_project_name_by_id($debit_note->project_id); ?>
                           </p>
                        <?php } ?>
                        <?php $pdf_custom_fields = get_custom_fields('debit_note', array('show_on_pdf' => 1));
                        foreach ($pdf_custom_fields as $field) {
                           $value = get_custom_field_value($debit_note->id, $field['id'], 'debit_note');
                           if ($value == '') {
                              continue;
                           } ?>
                           <p class="no-mbot">
                              <span class="bold"><?php echo $field['name']; ?>: </span>
                              <?php echo $value; ?>
                           </p>
                        <?php } ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="table-responsive">
                           <?php
                           $items = get_items_table_data($debit_note, 'debit_note', 'html', true);
                           echo $items->table();
                           ?>
                        </div>
                     </div>
                     <div class="col-md-5 col-md-offset-7">
                        <table class="table text-right">
                           <tbody>
                              <tr id="subtotal">
                                 <td><span class="bold">Sub Total</span>
                                 </td>
                                 <td class="subtotal">
                                    <?php echo app_format_money($debit_note->subtotal, $debit_note->currency_name); ?>
                                 </td>
                              </tr>
                              <?php if (is_sale_discount_applied($debit_note)) { ?>
                                 <tr>
                                    <td>
                                       <span class="bold">Discount
                                          <?php if (is_sale_discount($debit_note, 'percent')) { ?>
                                             (<?php echo app_format_number($debit_note->discount_percent, true); ?>%)
                                          <?php } ?></span>
                                    </td>
                                    <td class="discount">
                                       <?php echo '-' . app_format_money($debit_note->discount_total, $debit_note->currency_name); ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                              <?php
                              foreach ($items->taxes() as $tax) {
                                 echo '<tr class="tax-area"><td class="bold">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)</td><td>' . app_format_money($tax['total_tax'], $debit_note->currency_name) . '</td></tr>';
                              }
                              ?>
                              <?php if ((int)$debit_note->adjustment != 0) { ?>
                                 <tr>
                                    <td>
                                       <span class="bold">Adjustment</span>
                                    </td>
                                    <td class="adjustment">
                                       <?php echo app_format_money($debit_note->adjustment, $debit_note->currency_name); ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                              <tr>
                                 <td><span class="bold">Total</span>
                                 </td>
                                 <td class="total">
                                    <?php echo app_format_money($debit_note->total, $debit_note->currency_name); ?>
                                 </td>
                              </tr>
                              <?php if ($debit_note->debits_used) { ?>
                                 <tr>
                                    <td>
                                       <span class="bold">Debit Used</span>
                                    </td>
                                    <td>
                                       <?php echo '-' . app_format_money($debit_note->debits_used, $debit_note->currency_name); ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                              <?php if ($debit_note->total_refunds) { ?>
                                 <tr>
                                    <td>
                                       <span class="bold">
                                          <?php echo _l('refund'); ?>
                                       </span>
                                    </td>
                                    <td>
                                       <?php echo '-' . app_format_money($debit_note->total_refunds, $debit_note->currency_name); ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                              <tr>
                                 <td>
                                    <span class="bold">Debit Remaining</span>
                                 </td>
                                 <td>
                                    <?php echo app_format_money($debit_note->remaining_debits, $debit_note->currency_name); ?>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                     <?php if ($debit_note->clientnote != '') { ?>
                        <div class="col-md-12 mtop15">
                           <p class="bold text-muted">Client Note</p>
                           <p><?php echo $debit_note->clientnote; ?></p>
                        </div>
                     <?php } ?>
                     <?php if ($debit_note->terms != '') { ?>
                        <div class="col-md-12 mtop15">
                           <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                           <p><?php echo $debit_note->terms; ?></p>
                        </div>
                     <?php } ?>
                  </div>
                  <?php
                  if (count($debit_note->attachments) > 0) { ?>
                     <div class="clearfix"></div>
                     <hr />
                     <p class="bold text-muted">Debit Note Files</p>
                     <?php foreach ($debit_note->attachments as $attachment) {
                        $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                        if (!empty($attachment['external'])) {
                           $attachment_url = $attachment['external_link'];
                        }
                     ?>
                        <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
                           <div class="col-md-8">
                              <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                              <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?> <?= ($attachment['rel_type'] != "debit_note") ? "<small class='text-muted'>From " . $attachment['rel_type'] . "</small>" : "" ?></a>
                              <br />
                              <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                           </div>
                           <?php if ($attachment['rel_type'] == "debit_note") { ?>
                              <div class="col-md-4 text-right">
                                 <?php if (has_permission('attachments', '', 'delete')) { ?>
                                    <a href="#" class="text-danger" onclick="delete_debit_note_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                                 <?php } ?>
                              </div>
                           <?php } ?>
                        </div>
                     <?php } ?>
                  <?php } ?>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="purchase_debited">
               <?php if (count($debit_note->applied_debits) == 0) {
                  echo '<div class="alert alert-info no-mbot">';
                  echo "Debited Purchase Not Found";
                  echo '</div>';
               } else { ?>
                  <table class="table table-bordered no-mtop">
                     <thead>
                        <tr>
                           <th><span class="bold">Purchase Number</span></th>
                           <th><span class="bold">Amount Debited</span></th>
                           <th><span class="bold">Date</span></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($debit_note->applied_debits as $debit) { ?>
                           <tr>
                              <td>
                                 <a href="<?php echo admin_url('purchase/list_purchase/' . $debit['purchase_id']); ?>">
                                    <?php echo format_purchase_number($debit['purchase_id']); ?>
                                 </a>
                              </td>
                              <td>
                                 <?php echo app_format_money($debit['amount'], $debit_note->currency_name); ?>
                              </td>
                              <td>
                                 <?php echo _d($debit['date']); ?>
                                 <?php if (has_permission('debit_notes', '', 'delete')) { ?>
                                    <a href="<?php echo admin_url('debit_notes/delete_debit_note_applied_debit/' . $debit['id'] . '/' . $debit['debit_id'] . '/' . $debit['purchase_id']); ?>" class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                                 <?php } ?>
                              </td>
                           </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               <?php }  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
               $this->load->view(
                  'admin/includes/emails_tracking',
                  array(
                     'tracked_emails' =>
                     get_tracked_emails($debit_note->id, 'debit_note')
                  )
               );
               ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_refunds">
               <?php if (count($debit_note->refunds) == 0) {
                  echo '<div class="alert alert-info no-mbot">';
                  echo _l('not_refunds_found');
                  echo '</div>';
               } else { ?>
                  <table class="table table-bordered no-mtop">
                     <thead>
                        <tr>
                           <th><span class="bold"><?php echo _l('credit_date'); ?></span></th>
                           <th><span class="bold"><?php echo _l('refund_amount'); ?></span></th>
                           <th><span class="bold"><?php echo _l('payment_mode'); ?></span></th>
                           <th><span class="bold"><?php echo _l('note'); ?></span></th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php foreach ($debit_note->refunds as $refund) { ?>
                           <tr>
                              <td>
                                 <?php echo _d($refund['refunded_on']); ?>
                              </td>
                              <td>
                                 <?php echo app_format_money($refund['amount'], $debit_note->currency_name); ?>
                              </td>
                              <td>
                                 <?php echo $refund['payment_mode_name']; ?>
                              </td>
                              <td>
                                 <?php if (has_permission('debit_notes', '', 'delete')) { ?>
                                    <a href="<?php echo admin_url('debit_notes/delete_refund/' . $refund['id'] . '/' . $refund['debit_note_id']); ?>"
                                       class="pull-right text-danger _delete">
                                       <i class="fa fa-trash"></i>
                                    </a>
                                 <?php } ?>
                                 <?php if (has_permission('debit_notes', '', 'edit')) { ?>
                                    <a href="#" onclick="edit_refund(<?php echo $refund['id']; ?>); return false;"
                                       class="pull-right mright5">
                                       <i class="fa fa-pencil-square-o"></i>
                                    </a>
                                 <?php } ?>
                                 <?php echo $refund['note']; ?>
                              </td>
                           </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               <?php }  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target=".reminder-modal-debit_note-<?php echo $debit_note->id; ?>">Set Reminder</a>
               <hr />
               <?php render_datatable(array(_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder', array('id' => $debit_note->id, 'name' => 'debit_note', 'members' => $members, 'reminder_title' => "Set Reminder")); ?>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?php $this->load->view('admin/debit_notes/send_to_vendor'); ?>
<?php $this->load->view('admin/debit_notes/apply_debits_to_purchase'); ?>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();

   function refund_debit_note() {
      var debit_note_id = <?php echo $debit_note->id; ?>;
      $('#debit_note').load(admin_url + 'debit_notes/refund/' + debit_note_id);
   }

   function edit_refund(refund_id) {
      var debit_note_id = <?php echo $debit_note->id; ?>;
      $('#debit_note').load(admin_url + 'debit_notes/refund/' + debit_note_id + '/' + refund_id);
   }
</script>