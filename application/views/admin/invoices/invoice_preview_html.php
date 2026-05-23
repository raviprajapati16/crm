<?php defined('BASEPATH') or exit('No direct script access allowed');
if ($invoice->status == Invoices_model::STATUS_DRAFT) { ?>
   <div class="alert alert-info">
      <?php echo _l('invoice_draft_status_info'); ?>
   </div>
<?php } ?>
<div id="invoice-preview">
   <div class="row">
      <?php

      if ($invoice->recurring > 0 || $invoice->is_recurring_from != NULL) {

         $recurring_invoice = $invoice;
         $show_recurring_invoice_info = true;

         if ($invoice->is_recurring_from != NULL) {
            $recurring_invoice = $this->invoices_model->get($invoice->is_recurring_from);
            // Maybe recurring invoice not longer recurring?
            if ($recurring_invoice->recurring == 0) {
               $show_recurring_invoice_info = false;
            } else {
               $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
            }
         } else {
            $next_recurring_date_compare = to_sql_date($recurring_invoice->date);
            if ($recurring_invoice->last_recurring_date) {
               $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
            }
         }
         if ($show_recurring_invoice_info) {
            if ($recurring_invoice->custom_recurring == 0) {
               $recurring_invoice->recurring_type = 'MONTH';
            }
            $next_date = date('Y-m-d', strtotime('+' . $recurring_invoice->recurring . ' ' . strtoupper($recurring_invoice->recurring_type), strtotime($next_recurring_date_compare)));
         }
      ?>
         <div class="col-md-12">
            <div class="mbot10">
               <?php if (
                  $invoice->is_recurring_from == null
                  && $recurring_invoice->cycles > 0
                  && $recurring_invoice->cycles == $recurring_invoice->total_cycles
               ) { ?>
                  <div class="alert alert-info no-mbot">
                     <?php echo _l('recurring_has_ended', _l('invoice_lowercase')); ?>
                  </div>
               <?php } else if ($show_recurring_invoice_info) { ?>
                  <span class="label label-default padding-5">
                     <?php
                     if ($invoice->status == Invoices_model::STATUS_DRAFT) {
                        echo '<i class="fa fa-exclamation-circle fa-fw text-warning" data-toggle="tooltip" title="' . _l('recurring_invoice_draft_notice') . '"></i>';
                     }
                     echo _l('cycles_remaining'); ?>:
                     <b>
                        <?php
                        echo $recurring_invoice->cycles == 0 ? _l('cycles_infinity') : $recurring_invoice->cycles - $recurring_invoice->total_cycles;
                        ?>
                     </b>
                  </span>
               <?php
                  if ($recurring_invoice->cycles == 0 || $recurring_invoice->cycles != $recurring_invoice->total_cycles) {
                     echo '<span class="label label-default padding-5 mleft5"><i class="fa fa-question-circle fa-fw" data-toggle="tooltip" data-title="' . _l('recurring_recreate_hour_notice', _l('invoice')) . '"></i> ' . _l('next_invoice_date', '<b>' . _d($next_date) . '</b>') . '</span>';
                  }
               }
               ?>
            </div>
            <?php if ($invoice->is_recurring_from != NULL) { ?>
               <?php echo '<p class="text-muted' . ($show_recurring_invoice_info ? ' mtop15' : '') . '">' . _l('invoice_recurring_from', '<a href="' . admin_url('invoices/list_invoices/' . $invoice->is_recurring_from) . '" onclick="init_invoice(' . $invoice->is_recurring_from . ');return false;">' . format_invoice_number($invoice->is_recurring_from) . '</a></p>'); ?>
            <?php } ?>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-10" />
      <?php } ?>
      <?php if ($invoice->project_id != 0) { ?>
         <div class="col-md-12">
            <h4 class="font-medium mtop15 mbot20"><?php echo _l('related_to_project', array(
                                                      _l('invoice_lowercase'),
                                                      _l('project_lowercase'),
                                                      '<a href="' . admin_url('projects/view/' . $invoice->project_id) . '" target="_blank">' . $invoice->project_data->name . '</a>',
                                                   )); ?></h4>
         </div>
      <?php } ?>
      <div class="col-md-12 col-sm-12">
         <div class="row">
            <div class="col-md-12">
               <ul class="nav nav-tabs" role="tablist" id="invoice-nested-tabs">
                  <li role="presentation" class="active">
                     <a href="#nested_tab_tax_invoice" aria-controls="nested_tab_tax_invoice" role="tab" data-toggle="tab" onclick="invoice_preview('tax-invoice')">
                        Tax Invoice
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#nested_tab_packing_list" aria-controls="nested_tab_packing_list" role="tab" data-toggle="tab" onclick="invoice_preview('packing-list')">
                        Packing / Weight List
                     </a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="tab-content" id="invoice-nested-content">
            <div role="tabpanel" class="tab-pane active" id="nested_tab_tax_invoice">
               <div class="row">
                  <div class="col-md-12" id="tax-invoice-preview">

                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="nested_tab_packing_list">
               <div class="row">
                  <div class="col-md-12" id="packing-list-preview">

                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php if (count($invoice->attachments) > 0) { ?>
      <div class="clearfix"></div>
      <hr />
      <p class="bold text-muted"><?php echo _l('invoice_files'); ?></p>
      <?php foreach ($invoice->attachments as $attachment) {
         $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
         if (!empty($attachment['external'])) {
            $attachment_url = $attachment['external_link'];
         }
      ?>
         <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
            <div class="col-md-8">
               <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
               <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?> <?= ($attachment['rel_type'] != "invoice") ? "<small class='text-muted'>From " . $attachment['rel_type'] . "</small>" : "" ?></a>
               <br />
               <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
            </div>
            <div class="col-md-4 text-right">
               <?php if ($attachment['visible_to_customer'] == 0) {
                  $icon = 'fa-toggle-off';
                  $tooltip = _l('show_to_customer');
               } else {
                  $icon = 'fa-toggle-on';
                  $tooltip = _l('hide_from_customer');
               }
               ?>
               <?php if ($attachment['rel_type'] == "invoice") { ?>
                  <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $invoice->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                  <?php if (has_permission('attachments', '', 'delete')) { ?>
                     <a href="#" class="text-danger" onclick="delete_invoice_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                  <?php } ?>
               <?php } ?>
            </div>
         </div>
      <?php } ?>
   <?php } ?>
</div>