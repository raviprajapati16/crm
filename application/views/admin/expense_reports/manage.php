<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('expense_reports', '', 'create')) { ?>
                     <a href="javascript:void(0);" class="btn btn-info new-report">Create Report</a>
                  <?php } ?>
               </div>
               <div class="row">
                  <div class="col-md-12">
                     <div class="panel_s">
                        <div class="panel-body">
                           <?php
                           render_datatable(array(
                              "Report ID",
                              "Report Name",
                              "Duration",
                              "Status",
                              "Submitter",
                              "Approver",
                              "Total",
                              "To be Reimbursed",
                           ), 'expense-reports');
                           ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="expenseReportModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
         <?php echo form_open(admin_url('expense_reports/save'), array("name" => "expenseReportForm", "id" => "expenseReportForm")); ?>
         <input type="hidden" name="id" value="" form="expenseReportForm" />
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <!-- Report Name -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Report Name</label>
                        <input type="text" name="report_name" id="report_name" class="form-control" required placeholder="Enter Report Name" maxlength="70">
                     </div>
                  </div>

                  <!-- Business Purpose -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Business Purpose</label>
                        <textarea name="business_purpose" id="business_purpose" class="form-control" rows="4" required maxlength="500" placeholder="Enter Business Purpose"></textarea>
                     </div>
                  </div>

                  <!-- Duration -->
                  <div class="col-md-12">
                     <label class="control-label">Duration</label>
                     <div class="row">
                        <div class="col-md-6">
                           <div class="form-group">
                              <input type="text" name="start_date" id="start_date" class="form-control datepicker" required placeholder="Select Start Date" autocomplete="off" value="<?= date('d-m-Y') ?>">
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="form-group">
                              <input type="text" name="end_date" id="end_date" class="form-control datepicker" required placeholder="Select End Date" autocomplete="off" value="<?= date('d-m-Y') ?>">
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Trip -->
                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Apply to Trip</label>
                        <select name="trip_id" id="trip_id" class="form-control selectpicker" data-live-search="true">
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
               <button type="submit" class="btn btn-info" form="expenseReportForm"><?php echo _l('submit'); ?></button>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
   </div>
   <?php init_tail(); ?>
   <script>
      $(function() {
         var table = initDataTable('.table-expense-reports', window.location.href, [0], [0], undefined, [0, 'desc']);

         // Custom validation method for date comparison
         $.validator.addMethod("dateGreaterThanOrEqual", function(value, element, param) {
            if (!value || !$(param).val()) {
               return true; // Let required validation handle empty values
            }

            var startDate = convertDateToISO($(param).val());
            var endDate = convertDateToISO(value);

            return new Date(endDate) >= new Date(startDate);
         }, "End date must be greater than or equal to start date");

         // Function to convert dd-mm-yyyy to yyyy-mm-dd for date comparison
         function convertDateToISO(dateString) {
            if (!dateString) return '';
            var parts = dateString.split('-');
            return parts[2] + '-' + parts[1] + '-' + parts[0]; // Convert to yyyy-mm-dd
         }

         $('#expenseReportForm').appFormValidator({
            rules: {
               report_name: {
                  required: true,
                  maxlength: 70
               },
               business_purpose: {
                  required: false,
                  maxlength: 500
               },
               start_date: 'required',
               end_date: {
                  required: true,
                  dateGreaterThanOrEqual: '#start_date'
               }
            },
            messages: {
               end_date: {
                  dateGreaterThanOrEqual: "End date cannot be earlier than start date"
               }
            },
            errorPlacement: function(error, element) {
               var formGroup = $(element).closest('.form-group');
               $(formGroup).append(error);
            },
            submitHandler: function(form) {
               var $submitBtn = $('#expenseReportForm').find('button[type="submit"]');
               $submitBtn.prop('disabled', true);
               var originalText = $submitBtn.html();
               $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
               form.submit();
            }
         });

         // Real-time validation when dates are changed
         $('#start_date, #end_date').on('change', function() {
            $('#expenseReportForm').valid(); // Trigger validation
         });

         $(document).on('keydown', '.datepicker', function() {
            return false;
         });

         $(document).on('click', '.new-report', function() {
            $('#expenseReportModal .modal-title').text("New Report");
            $('#expenseReportForm')[0].reset();
            $('#expenseReportModal').find('input[name="id"]').val('');
            $('#expenseReportModal').modal('show');
         });

         $(document).on('click', '.edit-report', function() {
            var id = $(this).data('id');
            $('#expenseReportModal .modal-title').text("Edit Report");
            $('#expenseReportForm')[0].reset();
            $('#expenseReportModal').find('input[name="id"]').val(id);

            $.ajax({
               url: admin_url + 'expense_reports/get_expense_reports',
               type: 'POST',
               data: {
                  id: id
               },
               dataType: 'json',
               success: function(response) {
                  if (response.success) {
                     $('#report_name').val(response.data.report_name);
                     $('#business_purpose').val(response.data.business_purpose);
                     $('#start_date').val(response.data.start_date);
                     $('#end_date').val(response.data.end_date);
                     $('#trip_id').val(response.data.trip_id).selectpicker('refresh');
                     $('#expenseReportModal').modal('show');
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