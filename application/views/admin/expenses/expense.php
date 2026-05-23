<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php
         if (isset($expense)) {
            echo form_hidden('is_edit', 'true');
         }
         ?>
         <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'expense-form', 'class' => 'dropzone dropzone-manual')); ?>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php echo $title; ?></h4>
                  <hr class="hr-panel-heading" />
                  <?php if (isset($expense) && $expense->attachment !== '') { ?>
                     <div class="row">
                        <div class="col-md-10">
                           <i class="<?php echo get_mime_class($expense->filetype); ?>"></i> <a target="_blank" class="download-receipt" href="<?php echo site_url('download/file/expense/' . $expense->expenseid); ?>"><?php echo $expense->attachment; ?></a>
                        </div>
                        <?php if (has_permission('attachments', '', 'delete')) { ?>
                           <div class="col-md-2 text-right">
                              <a href="<?php echo admin_url('expenses/delete_expense_attachment/' . $expense->expenseid); ?>" class="text-danger _delete"><i class="fa fa-times"></i></a>
                           </div>
                        <?php } ?>
                     </div>
                  <?php } ?>
                  <?php if (!isset($expense) || (isset($expense) && $expense->attachment == '')) { ?>
                     <div id="dropzoneDragArea" class="dz-default dz-message">
                        <span><?php echo _l('expense_add_edit_attach_receipt'); ?></span>
                     </div>
                     <div class="dropzone-previews"></div>
                  <?php } ?>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">

                  <div class="form-group">
                     <label class="control-label">Report</label>
                     <select name="report_id" id="report_id" class="form-control selectpicker" data-live-search="true">
                        <option value="">Select Report</option>
                        <?php foreach ($reports as $report) { ?>
                           <option value="<?php echo $report['id']; ?>" <?= ($expense->report_id == $report['id']) ? "selected" : "" ?>><?= expenseReportIdFormat($report['id']) ?> - <?= $report['report_name'] ?></option>
                        <?php } ?>
                     </select>
                  </div>

                  <?php $value = (isset($expense) ? _d($expense->date) : _d(date('Y-m-d'))); ?>
                  <?php echo render_date_input('date', 'expense_add_edit_date', $value); ?>

                  <?php
                  $selected = (isset($expense) ? $expense->merchant_id : '');
                  echo render_select('merchant_id', $merchants, array('id', 'name'), 'Merchant', $selected, array('data-none-selected-text' => 'Select Merchant'));
                  ?>

                  <?php
                  $selected = (isset($expense) ? $expense->category : '');
                  echo render_select('category', $categories, array('id', 'name'), 'expense_category', $selected, array('data-none-selected-text' => 'Select Category'));
                  ?>

                  <?php $value = (isset($expense) ? $expense->amount : ''); ?>
                  <?php echo render_input('amount', 'expense_add_edit_amount', $value, 'text', array("autocomplete" => "off")); ?>

                  <div class="checkbox checkbox-primary reimbursement">
                     <input type="checkbox" id="reimbursement" name="reimbursement"
                        <?php if (!isset($expense) || (isset($expense) && $expense->reimbursement == 1)) echo 'checked'; ?>>
                     <label for="reimbursement">Claim reimbursement</label>
                  </div>

                  <?php $value = (isset($expense) ? $expense->note : ''); ?>
                  <?php echo render_textarea('note', 'Description', $value, array('rows' => 4), array()); ?>

                  <div class="form-group select-placeholder" id="expense_city_wrapper">
                     <label for="expense_city">Location<span class="expense_city_label"></span></label>
                     <div id="expense_city_select">
                        <select name="expense_city" id="expense_city" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="Select Location" required>
                           <?php
                           if (isset($expense) && (isset($selected_city) && !empty($selected_city)) && $expense->expense_city != 0 &&  $expense->expense_city != null) {
                              echo '<option value="' . $selected_city['id'] . '" selected>' . $selected_city['name'] . '</option>';
                           }
                           ?>
                        </select>
                     </div>
                  </div>

                  <?php $value = (isset($expense) ? $expense->reference : ''); ?>
                  <?php echo render_input('reference', 'expense_add_edit_reference_no', $value); ?>

                  <div class="checkbox checkbox-primary billable">
                     <input type="checkbox" id="billable" name="billable"
                        <?php if (!isset($expense) || (isset($expense) && $expense->billable == 1)) echo 'checked'; ?>>
                     <label for="billable">Billed</label>
                  </div>

                  <?php $rel_id = (isset($expense) ? $expense->expenseid : false); ?>
                  <div class="text-right">
                     <button id="expenseSubmit" type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div>
               </div>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
      <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   var expense_receipt_required = <?= get_option('expense_receipt_required') ?>;
   var expense_receipt_amount_threshold = <?= get_option('expense_receipt_amount_threshold') ?>;

   init_ajax_search('expense_city', '#expense_city.ajax-search', undefined, admin_url + 'expenses/ajax_city_search');

   Dropzone.options.expenseForm = false;
   var expenseDropzone;
   $(function() {
      if ($('#dropzoneDragArea').length > 0) {
         expenseDropzone = new Dropzone("#expense-form", appCreateDropzoneOptions({
            autoProcessQueue: false,
            clickable: '#dropzoneDragArea',
            previewsContainer: '.dropzone-previews',
            acceptedFiles: ".jpg,.jpeg,.png,.pdf",
            addRemoveLinks: true,
            maxFiles: 1,
            success: function(file, response) {
               response = JSON.parse(response);
               if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                  window.location.assign(response.url);
               }
            },
         }));
      }

      appValidateForm($('#expense-form'), {
         category: 'required',
         date: 'required',
         amount: 'required',
      }, expenseSubmitHandler);

      $('input[name="amount"]').on('input', function() {
         this.value = this.value.match(/^\d*(\.\d{0,2})?/)[0];
      });

      $('#date').on('keydown', function() {
         return false;
      });

      function expenseSubmitHandler(form) {
         var amount = parseFloat($('input[name="amount"]').val()) || 0;
         var hasReceipt = (typeof expenseDropzone !== 'undefined' && expenseDropzone.getAcceptedFiles().length > 0) || ($('.download-receipt').length > 0);
         var threshold = parseFloat(expense_receipt_amount_threshold) || 0;

         if (expense_receipt_required == 1 && amount >= threshold && !hasReceipt) {
            alert_float('danger', 'Receipt is required for expenses above ₹' + threshold)
            return false;
         }

         $('#expenseSubmit').prop('disabled', true);
         $('#expenseSubmit').html('Processing...');

         $.post(form.action, $(form).serialize()).done(function(response) {
            response = JSON.parse(response);
            if (response.expenseid) {
               if (typeof(expenseDropzone) !== 'undefined') {
                  if (expenseDropzone.getQueuedFiles().length > 0) {
                     expenseDropzone.options.url = admin_url + 'expenses/add_expense_attachment/' + response.expenseid;
                     expenseDropzone.processQueue();
                  } else {
                     window.location.assign(response.url);
                  }
               } else {
                  window.location.assign(response.url);
               }
            } else {
               window.location.assign(response.url);
            }
         });
         return false;
      }

   });
</script>
</body>

</html>