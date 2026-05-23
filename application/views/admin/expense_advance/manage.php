<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('expense_advance', '', 'create')) { ?>
                     <a href="javascript:void(0);" class="btn btn-info new-advance">Record Advance</a>
                  <?php } ?>
                  <?php if (has_permission('expense_advance', '', 'approve_reject_payment')) { ?>
                     <a href="javascript:void(0);" class="btn btn-primary action-records pull-right">Action</a>
                  <?php } ?>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="panel_s">
                        <div class="panel-body">
                           <?php
                           if (has_permission('expense_advance', '', 'approve_reject_payment')) {
                              render_datatable(array(
                                 "<div class='checkbox'><input type='checkbox' value='' class='adv-select-all'><label></label></div>",
                                 "Adv. Payment ID",
                                 "Submitter",
                                 "Date",
                                 "Trip",
                                 "Report",
                                 "Amount",
                                 "Status",
                                 "Approver"
                              ), 'expense-advance');
                           } else {
                              render_datatable(array(
                                 "Adv. Payment ID",
                                 "Submitter",
                                 "Date",
                                 "Trip",
                                 "Report",
                                 "Amount",
                                 "Status",
                                 "Approver"
                              ), 'expense-advance');
                           }
                           ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Action Modal -->
   <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">Select Action</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Action Type</label>
                        <select id="action_type" class="form-control selectpicker" required>
                           <option value="">Select Action</option>
                           <option value="approve">Approve</option>
                           <option value="reject">Reject</option>
                        </select>
                     </div>
                  </div>

                  <!-- Reject Reason Field (Initially Hidden) -->
                  <div class="col-md-12" id="reject_reason_container" style="display: none;">
                     <div class="form-group">
                        <label class="control-label">Reject Reason <span class="text-danger">*</span></label>
                        <textarea id="reject_reason" class="form-control" rows="4" placeholder="Please enter the reason for rejection..."></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
               <button type="button" class="btn btn-info" id="submit_action">Submit</button>
            </div>
         </div>
      </div>
   </div>

   <div class="modal fade" id="expenseAdvanceModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
         <?php echo form_open(admin_url('expense_advance/save'), array("name" => "expenseAdvanceForm", "id" => "expenseAdvanceForm")); ?>
         <input type="hidden" id="trip_id" name="id" value="" form="expenseAdvanceForm" />
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
               <div class="row">

                  <!-- Staff ID -->
                  <?php if (has_permission('expense_advance', '', 'view')) { ?>
                     <div class="col-md-12">
                        <div class="form-group">
                           <label class="control-label">Staff</label>
                           <select name="staff_id" id="staff_id" class="form-control selectpicker" data-live-search="true" required>
                              <option value="">Select Staff</option>
                              <?php foreach ($staff as $member) { ?>
                                 <option value="<?php echo $member['staffid']; ?>"><?php echo get_staff_full_name($member['staffid']); ?></option>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                  <?php } else { ?>
                     <input type="hidden" name="staff_id" value="<?php echo get_staff_user_id(); ?>" />
                  <?php } ?>

                  <!-- Amount -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control" required placeholder="Enter Amount" autocomplete="off" maxlength="12">
                     </div>
                  </div>

                  <!-- Date -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Date</label>
                        <input type="text" name="date" id="date" class="form-control datepicker" autocomplete="off" required autocomplete="off" placeholder="Select Date">
                     </div>
                  </div>

                  <!-- Paid Through -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Paid Through</label>
                        <select name="payment_mode" id="payment_mode" class="form-control selectpicker" data-live-search="true" required>
                           <option value="">Select</option>
                           <?php foreach ($payment_modes as $mode) { ?>
                              <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>

                  <!-- Reference# -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Reference#</label>
                        <input type="text" name="reference" id="reference" class="form-control" maxlength="50" placeholder="Enter Reference#">
                     </div>
                  </div>

                  <!-- Notes -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Enter Notes"></textarea>
                     </div>
                  </div>

                  <!-- Apply to Trip -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Apply to Trip</label>
                        <select name="trip" id="trip" class="form-control selectpicker" data-live-search="true">
                           <option value="">Select Trip</option>
                           <?php foreach ($trips as $trip) { ?>
                              <option value="<?php echo $trip['id']; ?>"><?= expenseTripIdFormat($trip['id']) ?> - <?php echo $trip['name']; ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>
               </div>
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
               <button type="submit" class="btn btn-info" form="expenseAdvanceForm"><?php echo _l('submit'); ?></button>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
   </div>

   <div class="modal fade" id="viewExpenseAdvanceModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title">View Advance Record</h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12"><strong>Advance Payment ID:</strong>
                     <p id="view_pay_id"></p>
                  </div>
                  <div class="col-md-12"><strong>Staff:</strong>
                     <p id="view_staff"></p>
                  </div>
                  <div class="col-md-12"><strong>Amount:</strong>
                     <p id="view_amount"></p>
                  </div>
                  <div class="col-md-12"><strong>Date:</strong>
                     <p id="view_date"></p>
                  </div>
                  <div class="col-md-12"><strong>Paid Through:</strong>
                     <p id="view_payment_mode"></p>
                  </div>
                  <div class="col-md-12"><strong>Reference#:</strong>
                     <p id="view_reference"></p>
                  </div>
                  <div class="col-md-12"><strong>Notes:</strong>
                     <p id="view_notes"></p>
                  </div>
                  <div class="col-md-12"><strong>Trip:</strong>
                     <p id="view_trip"></p>
                  </div>
                  <div class="col-md-12"><strong>Report:</strong>
                     <p id="view_report"></p>
                  </div>
                  <div class="col-md-12"><strong>Status:</strong>
                     <p id="view_status"></p>
                  </div>
                  <div class="col-md-12" id="view_reject_reason_container" style="display: none;">
                     <strong>Reject Reason:</strong>
                     <p id="view_reject_reason"></p>
                  </div>

                  <div class="col-md-12" id="view_action_section" style="display: none;">
                     <hr>
                     <h5><strong>Take Action:</strong></h5>
                     <div class="form-group">
                        <label class="control-label">Action Type</label>
                        <select id="view_action_type" class="form-control selectpicker" required>
                           <option value="">Select Action</option>
                           <option value="approve">Approve</option>
                           <option value="reject">Reject</option>
                        </select>
                     </div>
                     <div class="form-group" id="view_reject_reason_field" style="display: none;">
                        <label class="control-label">Reject Reason <span class="text-danger">*</span></label>
                        <textarea id="view_reject_reason_input" class="form-control" rows="4" placeholder="Please enter the reason for rejection..."></textarea>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
               <button type="button" class="btn btn-info" id="view_submit_action" style="display: none;">Submit Action</button>
            </div>
         </div>
      </div>
   </div>

   <?php init_tail(); ?>
   <script>
      $(function() {
         var table = initDataTable('.table-expense-advance', window.location.href, [0], [0], undefined, [1, 'desc']);

         $('#expenseAdvanceForm').appFormValidator({
            rules: {
               staff_id: 'required',
               amount: {
                  required: true,
                  number: true,
               },
               date: 'required',
               payment_mode: 'required',
               reference: {
                  maxlength: 50
               },
            },
            errorPlacement: function(error, element) {
               var inputType = $(element).attr('type')
               var formGroup = $(element).closest('.form-group');
               $(formGroup).append(error);
            },
            submitHandler: function(form) {
               var $submitBtn = $('#expenseAdvanceForm').find('button[type="submit"]');
               $submitBtn.prop('disabled', true);
               var originalText = $submitBtn.html();
               $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
               form.submit();
            }
         });

         $(document).on('change', '.adv-select-all', function() {
            const isChecked = $(this).prop('checked');
            $('.adv-checkbox').prop('checked', isChecked);
         });

         $(document).on('change', '.adv-checkbox', function() {
            const totalCheckboxes = $('.adv-checkbox').length;
            const checkedCheckboxes = $('.adv-checkbox:checked').length;
            $('.adv-select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
         });

         $('#expenseAdvanceForm input[name="amount"]').on('input', function() {
            this.value = this.value.match(/^\d*(\.\d{0,2})?/)[0];
         });

         $(document).on('keydown', '.datepicker', function() {
            return false;
         });

         $(document).on('click', '.action-records', function() {
            if ($('.adv-checkbox:checked').length === 0) {
               alert_float('warning', 'Please select at least one record to perform action.');
               return;
            }
            $('#actionModal').modal('show');
         });

         $(document).on('change', '#action_type', function() {
            var actionType = $(this).val();
            if (actionType === 'reject') {
               $('#reject_reason_container').show();
               $('#reject_reason').prop('required', true);
            } else {
               $('#reject_reason_container').hide();
               $('#reject_reason').prop('required', false);
               $('#reject_reason').val('');
            }
         });

         $(document).on('change', '#view_action_type', function() {
            var actionType = $(this).val();
            if (actionType === 'reject') {
               $('#view_reject_reason_field').show();
               $('#view_reject_reason_input').prop('required', true);
            } else {
               $('#view_reject_reason_field').hide();
               $('#view_reject_reason_input').prop('required', false);
               $('#view_reject_reason_input').val('');
            }
         });

         $(document).on('click', '#submit_action', function() {
            var actionType = $('#action_type').val();
            var rejectReason = $('#reject_reason').val();

            if (!actionType) {
               alert_float('warning', 'Please select an action type.');
               return;
            }

            if (actionType === 'reject' && !rejectReason.trim()) {
               alert_float('warning', 'Please enter a reject reason.');
               return;
            }

            var confirmMessage = actionType === 'approve' ?
               "Are you sure you want to approve selected advance records?" :
               "Are you sure you want to reject selected advance records?";

            if (confirm(confirmMessage)) {
               var ids = [];
               $('.adv-checkbox:checked').each(function() {
                  ids.push($(this).val());
               });

               if (ids.length > 0) {
                  var status = actionType === 'approve' ? 'Approved' : 'Rejected';
                  statusChange(ids, status, true, rejectReason);
                  $('#actionModal').modal('hide');

                  $('#action_type').val('').selectpicker('refresh');
                  $('#reject_reason_container').hide();
                  $('#reject_reason').val('');
               } else {
                  alert_float('warning', 'No records selected.');
               }
            }
         });

         $(document).on('click', '#view_submit_action', function() {
            var actionType = $('#view_action_type').val();
            var rejectReason = $('#view_reject_reason_input').val();
            var recordId = $('#viewExpenseAdvanceModal').data('record-id');

            if (!actionType) {
               alert_float('warning', 'Please select an action type.');
               return;
            }

            if (actionType === 'reject' && !rejectReason.trim()) {
               alert_float('warning', 'Please enter a reject reason.');
               return;
            }

            var confirmMessage = actionType === 'approve' ?
               "Are you sure you want to approve this advance record?" :
               "Are you sure you want to reject this advance record?";

            if (confirm(confirmMessage)) {
               var status = actionType === 'approve' ? 'Approved' : 'Rejected';
               statusChange(recordId, status, false, rejectReason);
               $('#viewExpenseAdvanceModal').modal('hide');
            }
         });

         $('#actionModal').on('hidden.bs.modal', function() {
            $('#action_type').val('').selectpicker('refresh');
            $('#reject_reason_container').hide();
            $('#reject_reason').val('');
         });

         $('#viewExpenseAdvanceModal').on('hidden.bs.modal', function() {
            $('#view_action_type').val('').selectpicker('refresh');
            $('#view_reject_reason_field').hide();
            $('#view_reject_reason_input').val('');
            $('#view_action_section').hide();
            $('#view_submit_action').hide();
            $('#view_reject_reason_container').hide();
         });

         function statusChange(id, status, bulk = false, rejectReason = '') {
            var requestUrl = admin_url + 'expense_advance/status_change';
            if (bulk) {
               requestUrl = admin_url + 'expense_advance/bulk_status_change';
            }

            var postData = {
               id: id,
               status: status,
               reason: rejectReason,
            };

            $.ajax({
               url: requestUrl,
               type: 'POST',
               data: postData,
               dataType: 'json',
               success: function(response) {
                  if (response.success) {
                     alert_float('success', response.message);
                     table.draw();
                  } else {
                     alert_float('warning', response.message);
                  }
               }
            });
         }

         $(document).on('click', '.new-advance', function() {
            $('#expenseAdvanceModal .modal-title').text("New Advance Record");
            $('#expenseAdvanceForm')[0].reset();
            $('#expenseAdvanceModal').find('input[name="id"]').val('');
            $('#expenseAdvanceModal').modal('show');
         });

         $(document).on('click', '.edit-advance', function() {
            var id = $(this).data('id');
            $('#expenseAdvanceModal .modal-title').text("Edit Advance Record");
            $('#expenseAdvanceForm')[0].reset();
            $('#expenseAdvanceModal').find('input[name="id"]').val(id);

            $.ajax({
               url: admin_url + 'expense_advance/get_expense_advance',
               type: 'POST',
               data: {
                  id: id
               },
               dataType: 'json',
               success: function(response) {
                  if (response.success) {
                     $('#staff_id').val(response.data.staff_id).selectpicker('refresh');
                     $('#amount').val(response.data.amount);
                     $('#date').val(response.data.date);
                     $('#payment_mode').val(response.data.payment_mode).selectpicker('refresh');
                     $('#reference').val(response.data.reference);
                     $('#notes').val(response.data.notes);
                     $('#trip').val(response.data.trip).selectpicker('refresh');
                     $('#expenseAdvanceModal').modal('show');
                  } else {
                     alert_float('warning', response.message);
                  }
               }
            });
         });

         $(document).on('click', '.view-advance', function() {
            var id = $(this).data('id');
            $.ajax({
               url: admin_url + 'expense_advance/get_expense_advance',
               type: 'POST',
               data: {
                  id: id
               },
               dataType: 'json',
               success: function(response) {
                  if (response.success) {
                     $('#viewExpenseAdvanceModal').data('record-id', id);

                     $('#view_pay_id').text(response.data.pay_id);
                     $('#view_staff').text(response.data.staff_name);
                     $('#view_amount').text(response.data.amount);
                     $('#view_date').text(response.data.date);
                     $('#view_payment_mode').text(response.data.payment_mode_name);
                     $('#view_reference').text(response.data.reference);
                     $('#view_notes').text(response.data.notes);
                     $('#view_trip').text(response.data.trip_name || '-');
                     $('#view_status').text(response.data.status || 'Pending');
                     $('#view_report').text(response.data.report_name || '-');


                     if (response.data.status === 'Rejected' && response.data.reject_reason) {
                        $('#view_reject_reason').text(response.data.reject_reason);
                        $('#view_reject_reason_container').show();
                     } else {
                        $('#view_reject_reason_container').hide();
                     }

                     <?php if (has_permission('expense_advance', '', 'approve_reject_payment')) { ?>
                        if (response.data.status === 'Pending' || !response.data.status) {
                           $('#view_action_section').show();
                           $('#view_submit_action').show();
                        } else {
                           $('#view_action_section').hide();
                           $('#view_submit_action').hide();
                        }
                     <?php } ?>

                     $('#viewExpenseAdvanceModal').modal('show');
                  } else {
                     alert_float('warning', response.message);
                  }
               }
            });
         });

      });
   </script>
   </body>

   </html>