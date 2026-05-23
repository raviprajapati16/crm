<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <?php if (has_permission('expense_trip', '', 'create')) { ?>
                  <div class="panel-body _buttons">
                     <a href="javascript:void(0);" class="btn btn-info new-trip">New Trip</a>
                  </div>
               <?php } ?>
               <div class="row">
                  <div class="col-md-12">
                     <div class="panel_s">
                        <div class="panel-body">
                           <?php render_datatable(array(
                              _l('Trip ID'),
                              _l('Trip Name'),
                              _l('Trip Type'),
                              _l('Business Purpose'),
                              _l('Created By'),
                              _l('Created At'),
                           ), 'expense-trip'); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="tripModal" tabindex="-1" role="dialog" data-backdrop="static">
      <div class="modal-dialog">
         <?php echo form_open(admin_url('expense_trip/save'), array("name" => "tripForm", "id" => "tripForm")); ?>
         <input type="hidden" id="trip_id" name="id" value="" form="tripForm" />
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
               <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12">
                     <div class="form-group">
                        <label for="name" class="control-label">Trip Name</label>
                        <input type="text" id="name" name="name" class="form-control" maxlength="70" placeholder="Enter Trip Name" required>
                     </div>
                  </div>

                  <div class="col-md-12">
                     <div class="form-group">
                        <label class="control-label">Trip Type</label><br>
                        <label class="radio-inline">
                           <input type="radio" name="type" value="domestic" required> Domestic
                        </label>
                        <label class="radio-inline">
                           <input type="radio" name="type" value="international" required> International
                        </label>
                     </div>
                  </div>

                  <div class="col-md-12 international-fields" style="display: none;">
                     <div class="form-group">
                        <label for="country" class="control-label">Country</label>
                        <select name="country" id="country" class="form-control selectpicker" data-live-search="true">
                           <option value="">Select Country</option>
                           <?php foreach (get_all_countries() as $country) { ?>
                              <option value="<?php echo $country['country_id']; ?>"><?php echo $country['short_name']; ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>

                  <div class="col-md-12 international-fields" style="display: none;">
                     <div class="form-group">
                        <label class="control-label">Visa Required</label><br>
                        <label class="radio-inline">
                           <input type="radio" name="visa_required" value="1"> Yes
                        </label>
                        <label class="radio-inline">
                           <input type="radio" name="visa_required" value="0"> No
                        </label>
                     </div>
                  </div>

                  <div class="col-md-12">
                     <div class="form-group">
                        <label for="business_purpose" class="control-label">Business Purpose</label>
                        <textarea id="business_purpose" name="business_purpose" class="form-control" rows="4" placeholder="Enter Business Purpose"></textarea>
                     </div>
                  </div>

               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
               <button type="submit" class="btn btn-info" form="tripForm"><?php echo _l('submit'); ?></button>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
   </div>
   <?php init_tail(); ?>
   <script>
      $(function() {
         initDataTable('.table-expense-trip', window.location.href, [0], [0], undefined, [0, 'desc']);

         $('#tripForm').appFormValidator({
            rules: {
               name: 'required',
               type: 'required',
               country: {
                  required: function() {
                     return $('input[name="type"]:checked').val() === 'international';
                  }
               },
               visa_required: {
                  required: function() {
                     return $('input[name="type"]:checked').val() === 'international';
                  }
               },
            },
            errorPlacement: function(error, element) {
               var formGroup = $(element).closest('.form-group');
               formGroup.append(error);
            },
            submitHandler: function(form) {
               var $submitBtn = $('#tripForm').find('button[type="submit"]');
               $submitBtn.prop('disabled', true);
               var originalText = $submitBtn.html();
               $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
               form.submit();
            }
         });


         function toggleInternationalFields() {
            let tripType = $('input[name="type"]:checked').val();
            if (tripType === 'international') {
               $('.international-fields').show();
               $('#country').prop('required', true);
               $('input[name="visa_required"]').prop('required', true);
            } else {
               $('.international-fields').hide();
               $('#country').prop('required', false).val('');
               $('input[name="visa_required"]').prop('required', false).prop('checked', false);
            }
         }

         $(document).on('change', 'input[name="type"]', function() {
            toggleInternationalFields();
         });

         $(document).on('click', '.new-trip', function() {
            $('#tripModal .modal-title').text("New Trip");
            $('#tripForm')[0].reset();
            $('#tripModal').find('input[name="id"]').val('');
            $('.international-fields').hide();
            $('#tripModal').modal('show');
         });

         $(document).on('click', '.edit-trip', function() {
            var id = $(this).data('id');
            $('#tripModal .modal-title').text("Edit Trip");
            $('#tripForm')[0].reset();
            $('#tripModal').find('input[name="id"]').val(id);
            $('.international-fields').hide();

            $.ajax({
               url: admin_url + 'expense_trip/get_expense_trip',
               type: 'POST',
               data: {
                  id: id
               },
               dataType: 'json',
               success: function(response) {
                  if (response.success) {
                     $('#name').val(response.data.name);
                     $('input[name="type"][value="' + response.data.type + '"]').prop('checked', true);

                     if (response.data.type === 'international') {
                        $('#country').val(response.data.country).selectpicker('refresh');
                        $('input[name="visa_required"][value="' + response.data.visa_required + '"]').prop('checked', true);
                     }
                     toggleInternationalFields();
                     $('#business_purpose').val(response.data.business_purpose);
                     $('#tripModal').modal('show');
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