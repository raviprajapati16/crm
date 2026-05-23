<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('expenses', '', 'create')) { ?>
                     <a href="<?php echo admin_url('expenses/expense'); ?>" class="btn btn-info"><?php echo _l('new_expense'); ?></a>
                  <?php } ?>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php
                        render_datatable(array(
                           "Expense ID",
                           "Created By",
                           "Expense Date",
                           "Category",
                           "Merchant",
                           "Amount",
                           "Report",
                           "Status",
                        ), 'expenses');
                        ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Optimized View Expense Modal -->
<div class="modal fade" id="viewExpenseModal" tabindex="-1" role="dialog" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">View Expense Record</h4>
         </div>
         <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div class="row">
               <div class="col-md-4"><strong>Expense ID:</strong>
                  <p id="view_expense_id"></p>
               </div>
               <div class="col-md-4"><strong>Amount:</strong>
                  <p id="view_amount"></p>
               </div>
               <div class="col-md-4"><strong>Status:</strong>
                  <p id="view_status_badge"></p>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4"><strong>Expense Created By:</strong>
                  <p id="view_created_by"></p>
               </div>
               <div class="col-md-4"><strong>Expense Date:</strong>
                  <p id="view_expense_date"></p>
               </div>
               <div class="col-md-4"><strong>Category:</strong>
                  <p id="view_category"></p>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4"><strong>Merchant:</strong>
                  <p id="view_merchant"></p>
               </div>
               <div class="col-md-4"><strong>Reference#:</strong>
                  <p id="view_reference"></p>
               </div>
               <div class="col-md-4"><strong>Report:</strong>
                  <p id="view_report"></p>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4"><strong>City:</strong>
                  <p id="view_city"></p>
               </div>
               <div class="col-md-4"><strong>Billable:</strong>
                  <p id="view_billable_badge"></p>
               </div>
               <div class="col-md-4"><strong>Reimbursement:</strong>
                  <p id="view_reimbursement_badge"></p>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4" id="description_section"><strong>Description:</strong>
                  <p id="view_description"></p>
               </div>
               <div class="col-md-4" id="note_section"><strong>Note:</strong>
                  <p id="view_note"></p>
               </div>
               <div class="col-md-4" id="attachment_section"><strong>Attachment:</strong>
                  <p id="view_attachment_link"></p>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4"><strong>Created At:</strong>
                  <p id="view_created_at"></p>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
               <i class="fa fa-times"></i> <?php echo _l('close'); ?>
            </button>
         </div>
      </div>
   </div>
</div>

<?php init_tail(); ?>
<script>
   Dropzone.autoDiscover = false;
   $(function() {

      var table = initDataTable('.table-expenses', window.location.href, [0], [0], undefined, [0, 'desc']);

      $(document).on('click', '.view-expense', function() {
         var id = $(this).data('id');
         $.ajax({
            url: admin_url + 'expenses/get_expense',
            type: 'POST',
            data: {
               id: id
            },
            dataType: 'json',
            success: function(response) {
               if (response.success) {
                  $('#viewExpenseModal').data('record-id', id);

                  // Primary information
                  $('#view_expense_id').text(response.data.id);
                  $('#view_created_by').text(response.data.created_by_name);
                  $('#view_expense_date').text(response.data.date);
                  $('#view_category').text(response.data.category_name);
                  $('#view_merchant').text(response.data.merchant_name || 'N/A');
                  $('#view_amount').text(response.data.amount);
                  $('#view_report').text(response.data.report_name || 'N/A');
                  $('#view_reference').text(response.data.reference || 'N/A');
                  $('#view_city').text(response.data.city_name || 'N/A');


                  // Status with color coding
                  var status = response.data.status || 'Unreported';
                  $('#view_status_badge').text(status);

                  $('#view_billable_badge').text(response.data.billable == 1 ? 'Yes' : 'No');
                  $('#view_reimbursement_badge').text(response.data.reimbursement == 1 ? 'Yes' : 'No');
                  $('#view_description').text(response.data.description);
                  $('#view_note').text(response.data.note);

                  if (response.data.attachment_url) {
                     $('#view_attachment_link').html("<a href='"+response.data.attachment_url+"' target='_blank'><i class='fa fa-eye'></i> View</a>");
                  } else {
                     $('#view_attachment_link').html("-");
                  }

                  $('#view_created_at').text(response.data.expense_created_at);

                  $('#viewExpenseModal').modal('show');
               } else {
                  alert_float('warning', response.message);
               }
            }
         });
      });

      $('#viewExpenseModal').on('hidden.bs.modal', function() {
         $('#viewExpenseModal .modal-body p').text('');
      });

   });
</script>
</body>

</html>