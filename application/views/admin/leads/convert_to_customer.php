<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="convert_lead_to_client_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
   <div class="modal-dialog modal-lg" role="document">
      <?php echo form_open('admin/leads/convert_to_customer',array('id'=>'lead_to_client_form')); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">
               <?php echo _l('lead_convert_to_client'); ?>
            </h4>
         </div>
         <div class="modal-body">
            <?php echo form_hidden('leadid',$lead->id); ?>
            <?php if(mb_strpos($lead->name,' ') !== false){
               $_temp = explode(' ',$lead->name);
               $firstname = $_temp[0];
               if(isset($_temp[2])){
                 $lastname = $_temp[1] . ' ' . $_temp[2];
              } else {
                 $lastname = $_temp[1];
              }
           } else {
              $lastname = '';
              $firstname = $lead->name;
           }
           ?>
           <?php echo form_hidden('default_language',$lead->default_language); ?>
           <?php echo render_input('firstname','lead_convert_to_client_firstname',$firstname); ?>
           <?php echo render_input('lastname','lead_convert_to_client_lastname',$lastname); ?>
           <?php echo render_input('title','contact_position',$lead->title); ?>
           <?php echo render_input('email','lead_convert_to_email',$lead->email); ?>
           <?php echo render_input('company','lead_company',$lead->company); ?>
           <?php echo render_input('phonenumber','lead_convert_to_client_phone',$lead->phonenumber); ?>
           <?php echo render_input('website','client_website',$lead->website); ?>
           <?php echo render_textarea('address','client_address',$lead->address); ?>
           <?php
           $countries                = get_all_countries();
           $customer_default_country = get_option('customer_default_country');
           $selected_country         = ($lead->country != 0 ? $lead->country : $customer_default_country);
           echo render_select('country', $countries, ['country_id', ['short_name']], 'clients_country', $selected_country, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);

           $state_options = isset($initial_states) ? $initial_states : [];
           if (!empty($lead->state)) {
               $state_found = false;
               foreach ($state_options as $state_row) {
                   if ($state_row['state'] === $lead->state) {
                       $state_found = true;
                       break;
                   }
               }
               if (!$state_found) {
                   $state_options[] = ['state' => $lead->state];
               }
           }
           $state_wrapper_class = !empty($selected_country) ? 'convert-state-wrapper' : 'convert-state-wrapper hide';
           echo render_select('state', $state_options, ['state', 'state'], 'client_state', $lead->state, ['data-none-selected-text' => _l('dropdown_non_selected_tex')], [], $state_wrapper_class);

           $city_options = isset($initial_cities) ? $initial_cities : [];
           if (!empty($lead->city)) {
               $city_found = false;
               foreach ($city_options as $city_row) {
                   if ($city_row['city'] === $lead->city) {
                       $city_found = true;
                       break;
                   }
               }
               if (!$city_found) {
                   $city_options[] = ['city' => $lead->city];
               }
           }
           $city_wrapper_class = (!empty($selected_country) && !empty($lead->state) && country_uses_city_dropdown($selected_country)) ? 'convert-city-wrapper' : 'convert-city-wrapper hide';
           echo render_select('city', $city_options, ['city', 'city'], 'client_city', $lead->city, ['data-none-selected-text' => _l('dropdown_non_selected_tex')], [], $city_wrapper_class);
           ?>
           <?php echo render_input('zip','clients_zip',$lead->zip); ?>
           <?php
           $not_mergable_customer_fields  = array('userid','datecreated','leadid','default_language','default_currency','active');
           $not_mergable_contact_fields  = array('id','userid','datecreated','is_primary','password','new_pass_key','new_pass_key_requested','last_ip','last_login','last_password_change','active','profile_image','direction');
           $customer_fields = $this->db->list_fields(db_prefix().'clients');
           $contact_fields = $this->db->list_fields(db_prefix().'contacts');
           $custom_fields = get_custom_fields('leads');
           $found_custom_fields = false;
           foreach ($custom_fields as $field) {
             $value = get_custom_field_value($lead->id, $field['id'], 'leads');
             if ($value == '') {
              continue;
           } else {
              $found_custom_fields = true;
           }
        }
        if($found_custom_fields == true){
         echo '<h4 class="bold text-center mtop30">'._l('copy_custom_fields_convert_to_customer').'</h4><hr />';
      }
      foreach ($custom_fields as $field) {
         $value = get_custom_field_value($lead->id, $field['id'], 'leads');
         if ($value == '') {
            continue;
         }
         ?>
         <p class="bold text-info"><?php echo $field['name']; ?> (<?php echo $value; ?>)</p>
         <hr />
         <p class="bold no-margin"><?php echo _l('leads_merge_customer'); ?></p>
         <div class="radio radio-primary">
            <input type="radio" data-field-id="<?php echo $field['id']; ?>" id="m_1_<?php echo $field['id']; ?>" class="include_leads_custom_fields" checked name="include_leads_custom_fields[<?php echo $field['id']; ?>]" value="1">
            <label for="m_1_<?php echo $field['id']; ?>" class="bold">
               <span data-toggle="tooltip" data-title="<?php echo _l('copy_custom_fields_convert_to_customer_help'); ?>"><i class="fa fa-info-circle"></i></span> <?php echo _l('lead_merge_custom_field'); ?>
            </label>
         </div>
         <div class="radio radio-primary">
            <input type="radio" data-field-id="<?php echo $field['id']; ?>" id="m_2_<?php echo $field['id']; ?>" class="include_leads_custom_fields" name="include_leads_custom_fields[<?php echo $field['id']; ?>]" value="2">
            <label for="m_2_<?php echo $field['id']; ?>" class="bold">
               <?php echo _l('lead_merge_custom_field_existing'); ?>
            </label>
         </div>
         <div class="hide" id="merge_db_field_<?php echo $field['id']; ?>">
            <hr />
            <select name="merge_db_fields[<?php echo $field['id']; ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <option value=""></option>
               <?php foreach($customer_fields as $c_field){
                  if(!in_array($c_field, $not_mergable_customer_fields)){
                   echo '<option value="'.$c_field.'">'.str_replace('_',' ',ucfirst($c_field)).'</option>';
                }
             }
             ?>
          </select>
          <hr />
       </div>
       <p class="bold"><?php echo _l('leads_merge_contact'); ?></p>
       <div class="radio radio-primary">
         <input type="radio" data-field-id="<?php echo $field['id']; ?>" id="m_3_<?php echo $field['id']; ?>" class="include_leads_custom_fields" name="include_leads_custom_fields[<?php echo $field['id']; ?>]" value="3">
         <label for="m_3_<?php echo $field['id']; ?>" class="bold">
            <?php echo _l('leads_merge_as_contact_field'); ?>
         </label>
      </div>
      <div class="radio radio-primary">
         <input type="radio" data-field-id="<?php echo $field['id']; ?>" id="m_4_<?php echo $field['id']; ?>" class="include_leads_custom_fields" name="include_leads_custom_fields[<?php echo $field['id']; ?>]" value="4">
         <label for="m_4_<?php echo $field['id']; ?>" class="bold">
            <span data-toggle="tooltip" data-title="<?php echo _l('copy_custom_fields_convert_to_customer_help'); ?>"><i class="fa fa-info-circle"></i></span>
            <?php echo _l('lead_merge_custom_field'); ?>
         </label>
      </div>
      <div class="hide" id="merge_db_contact_field_<?php echo $field['id']; ?>">
         <hr />
         <select name="merge_db_contact_fields[<?php echo $field['id']; ?>]" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <option value=""></option>
            <?php foreach($contact_fields as $c_field){
               if(!in_array($c_field, $not_mergable_contact_fields)){
                echo '<option value="'.$c_field.'">'.str_replace('_',' ',ucfirst($c_field)).'</option>';
             }
          }
          ?>
       </select>
    </div>
    <hr />
    <div class="radio radio-primary">
      <input type="radio" data-field-id="<?php echo $field['id']; ?>" id="m_5_<?php echo $field['id']; ?>" class="include_leads_custom_fields" name="include_leads_custom_fields[<?php echo $field['id']; ?>]" value="5">
      <label for="m_5_<?php echo $field['id']; ?>" class="bold">
         <?php echo _l('lead_dont_merge_custom_field'); ?>
      </label>
   </div>
   <hr />
   <?php } ?>
   <?php echo form_hidden('original_lead_email',$lead->email); ?>

   <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
   <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1"/>
   <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>

   <div class="client_password_set_wrapper">
      <label for="password" class="control-label"><?php echo _l('client_password'); ?></label>
      <div class="input-group">
         <input type="password" class="form-control password" name="password" autocomplete="off">
         <span class="input-group-addon">
            <a href="#password" class="show_password" onclick="showPassword('password');return false;"><i class="fa fa-eye"></i></a>
         </span>
         <span class="input-group-addon">
            <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
         </span>
      </div>
   </div>
   <?php if(total_rows(db_prefix().'emailtemplates',array('slug'=>'contact-set-password','active'=>0)) == 0){ ?>
   <div class="checkbox checkbox-primary">
      <input type="checkbox" name="send_set_password_email" id="send_set_password_email">
      <label for="send_set_password_email">
         <?php echo _l( 'client_send_set_password_email'); ?>
      </label>
   </div>
   <?php } ?>
   <?php if(total_rows(db_prefix().'emailtemplates',array('slug'=>'new-client-created','active'=>0)) == 0){ ?>
   <div class="checkbox checkbox-primary">
      <input type="checkbox" name="donotsendwelcomeemail" id="donotsendwelcomeemail">
      <label for="donotsendwelcomeemail"><?php echo _l('client_do_not_send_welcome_email'); ?></label>
   </div>
   <?php } ?>
   <?php if(total_rows(db_prefix().'notes',array('rel_type'=>'lead','rel_id'=>$lead->id)) > 0){ ?>
   <div class="checkbox checkbox-primary">
      <input type="checkbox" name="transfer_notes" id="transfer_notes">
      <label for="transfer_notes"><?php echo _l('transfer_lead_notes_to_customer'); ?></label>
   </div>
   <?php } ?>
   <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1' && count($purposes) > 0) { ?>
    <div class="checkbox checkbox-primary">
      <input type="checkbox" name="transfer_consent" id="transfer_consent">
      <label for="transfer_consent"><?php echo _l('transfer_consent'); ?></label>
   </div>
   <?php } ?>
</div>
<div class="modal-footer">
   <button type="button" class="btn btn-default" onclick="init_lead(<?php echo $lead->id; ?>); return false;" data-dismiss="modal"><?php echo _l('back_to_lead'); ?></button>
   <button type="submit" data-form="#lead_to_client_form" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('submit'); ?></button>
</div>
</div>
<?php echo form_close(); ?>
</div>
</div>
<script>
   validate_lead_convert_to_client_form();
   init_selectpicker();

   (function() {
      var $form = $('#lead_to_client_form');
      var INDIA_COUNTRY_ID = <?php echo (int) get_india_country_id(); ?>;

      function isIndiaCountry(countryId) {
         return countryId && String(countryId) === String(INDIA_COUNTRY_ID);
      }

      function toggleConvertLocationFields() {
         var countryId = $form.find('select[name="country"]').val();
         var stateVal  = $form.find('select[name="state"]').val();
         $form.find('.convert-state-wrapper').toggleClass('hide', !countryId);
         var showCity = countryId && stateVal && isIndiaCountry(countryId);
         $form.find('.convert-city-wrapper').toggleClass('hide', !showCity);
      }

      function appendLocationOption($select, value) {
         if (!value) {
            return;
         }
         if ($select.find('option').filter(function() { return $(this).val() === value; }).length === 0) {
            $select.append($('<option>', { value: value, text: value }));
         }
      }

      function refreshConvertLocation(type, preselectState, preselectCity) {
         var countryId = $form.find('select[name="country"]').val();
         var $state    = $form.find('select[name="state"]');
         var $city     = $form.find('select[name="city"]');

         if (type === 'state') {
            toggleConvertLocationFields();
            $state.empty().append('<option value=""></option>');
            $city.empty().append('<option value=""></option>');
            $state.selectpicker('refresh');
            $city.selectpicker('refresh');
         } else {
            toggleConvertLocationFields();
            $city.empty().append('<option value=""></option>');
            $city.selectpicker('refresh');
         }

         if (!countryId) {
            toggleConvertLocationFields();
            return;
         }

         var postData = {
            type: type,
            country_id: countryId
         };

         if (typeof csrfData !== 'undefined') {
            postData[csrfData.token_name] = csrfData.hash;
         }

         if (type === 'city') {
            postData.state = $state.val();
            if (!postData.state) {
               toggleConvertLocationFields();
               return;
            }
            if (!isIndiaCountry(countryId)) {
               toggleConvertLocationFields();
               return;
            }
         }

         $.ajax({
            url: admin_url + 'leads/get_state_city',
            method: 'POST',
            data: postData,
            dataType: 'json'
         }).done(function(result) {
            if (!result || !result.success) {
               toggleConvertLocationFields();
               return;
            }

            var $target = (type === 'state') ? $state : $city;
            var key     = (type === 'state') ? 'state' : 'city';
            var pre     = (type === 'state') ? preselectState : preselectCity;

            $.each(result.data, function(i, item) {
               if (item[key]) {
                  $target.append(new Option(item[key], item[key]));
               }
            });

            if (pre) {
               appendLocationOption($target, pre);
               $target.selectpicker('val', pre);
            }

            $target.selectpicker('refresh');

            if (type === 'state' && preselectState && isIndiaCountry(countryId)) {
               refreshConvertLocation('city', null, preselectCity);
            } else {
               toggleConvertLocationFields();
            }
         }).fail(function() {
            toggleConvertLocationFields();
         });
      }

      $(document).off('changed.bs.select.convertLocation', '#lead_to_client_form select[name="country"]');
      $(document).on('changed.bs.select.convertLocation', '#lead_to_client_form select[name="country"]', function() {
         refreshConvertLocation('state');
      });

      $(document).off('changed.bs.select.convertLocation', '#lead_to_client_form select[name="state"]');
      $(document).on('changed.bs.select.convertLocation', '#lead_to_client_form select[name="state"]', function() {
         var countryId = $form.find('select[name="country"]').val();
         if (isIndiaCountry(countryId)) {
            refreshConvertLocation('city');
         } else {
            toggleConvertLocationFields();
         }
      });

      toggleConvertLocationFields();
   })();
</script>
