<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" data-module="<?= $module_type ?>">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <a href="#" onclick="init_lead(); return false;" class="btn mright5 btn-info pull-left display-block">
                        <?php echo _l('new_lead'); ?>
                     </a>
                     <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                        <a href="<?php echo admin_url('leads/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                           <?php echo _l('import_leads'); ?>
                        </a>
                     <?php } ?>
                     <div class="row">
                        <div class="col-md-5">
                           <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a>
                           <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>" class="btn btn-default mleft10 hidden-xs">
                              <?php if ($switch_kanban == 1) {
                                 echo _l('leads_switch_to_kanban');
                              } else {
                                 echo _l('switch_to_list_view');
                              }; ?>
                           </a>
                        </div>
                        <div class="col-md-4 col-xs-12 pull-right leads-search">
                           <?php if ($this->session->userdata('leads_kanban_view') == 'true') { ?>
                              <div data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                                 <?php echo render_input('search', '', '', 'search', array('data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')), array(), 'no-margin') ?>
                              </div>
                           <?php } ?>
                           <?php echo form_hidden('sort_type'); ?>
                           <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                        </div>
                     </div>
                     <div class="clearfix"></div>
                     <div class="row hide leads-overview">
                        <hr class="hr-panel-heading" />
                        <div class="col-md-12">
                           <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                        </div>
                        <?php
                        foreach ($summary as $status) { ?>
                           <div class="col-md-2 col-xs-6 border-right">
                              <h3 class="bold">
                                 <?php
                                 if (isset($status['percent'])) {
                                    echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                 } else {
                                    // Is regular status
                                    echo $status['total'];
                                 }
                                 ?>
                              </h3>
                              <span style="color:<?php echo $status['color']; ?>" class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>"><?php echo $status['name']; ?></span>
                           </div>
                        <?php } ?>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <?php render_lead_color_legend($statuses); ?>
                  <div class="tab-content">
                     <?php
                     if ($this->session->has_userdata('leads_kanban_view') && $this->session->userdata('leads_kanban_view') == 'true') { ?>
                        <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                           <div class="kanban-leads-sort">
                              <span class="bold"><?php echo _l('leads_sort_by'); ?>: </span>
                              <a href="#" onclick="leads_kanban_sort('dateadded'); return false" class="dateadded">
                                 <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                    echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                 } ?><?php echo _l('leads_sort_by_datecreated'); ?>
                              </a>
                              |
                              <a href="#" onclick="leads_kanban_sort('leadorder');return false;" class="leadorder">
                                 <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                    echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                 } ?><?php echo _l('leads_sort_by_kanban_order'); ?>
                              </a>
                              |
                              <a href="#" onclick="leads_kanban_sort('lastcontact');return false;" class="lastcontact">
                                 <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                    echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                 } ?><?php echo _l('leads_sort_by_lastcontact'); ?>
                              </a>
                           </div>
                           <div class="row">
                              <div class="container-fluid leads-kan-ban">
                                 <div id="kan-ban"></div>
                              </div>
                           </div>
                        </div>
                     <?php } else { ?>
                        <div class="row" id="leads-table">
                           <div class="col-md-12">
                              <div class="row">
                                 <div class="col-md-12">
                                    <p class="bold"><?php echo _l('filter_by'); ?></p>
                                 </div>
                                 <?php if (has_permission('leads', '', 'view')) { ?>
                                    <div class="col-md-2 leads-filter-column">
                                       <label for="view_assigned" class="control-label">Assigned</label>
                                       <?php echo render_select('view_assigned', $staff, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot'); ?>
                                    </div>
                                 <?php } ?>
                                 <div class="col-md-2 leads-filter-column">
                                    <label for="rel_type" class="control-label">Lead Status</label>
                                    <?php
                                    $selected = array();
                                    if ($this->input->get('status')) {
                                       $selected[] = $this->input->get('status');
                                    } else {
                                       foreach ($statuses as $key => $status) {
                                          if ($status['isdefault'] == 0) {
                                             if ($status['name'] != 'NOT INTRESTED') {
                                                $selected[] = $status['id'];
                                             }
                                          } else {
                                              if($status['id'] == 1){
                                                $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
                                             }
                                             if($status['id'] == 75){
                                                $statuses[$key]['option_attributes'] = ['data-subtext' => "Assigned to Customer"];
                                             }
                                          }
                                       }
                                    }
                                    echo '<div id="leads-filter-status">';
                                    echo render_select('view_status[]', $statuses, array('id', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                    echo '</div>';
                                    ?>
                                 </div>
                                 <div class="col-md-2 leads-filter-column">
                                    <label for="rel_type" class="control-label">Lead Source</label>
                                    <?php
                                    echo render_select('view_source', $sources, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source')), array(), 'no-mbot');
                                    ?>
                                 </div>
                                 <!--start country select -->
                                 <div class="col-md-2  border-right">
                                    <label for="rel_type" class="control-label">Country</label>
                                    <?php
                                    // $lead_countries[] = array('id' => -1, 'name' => "Country");
                                    echo render_select('countries[]', $lead_countries, array('id', 'name'), '', $countries, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false); ?>
                                 </div>
                                 <div class="col-md-2   border-right">
                                  <label for="rel_type" class="control-label">State</label>
                                    <?php
                                    // $lead_states[] = array('id' => -1, 'name' => "State");
                                    echo render_select('states[]', $lead_states, array('id', 'name'), '', $states, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false); ?>
                                 </div>
                                 <div class="col-md-2  border-right">
                                    <label for="rel_type" class="control-label">City</label>
                                    <?php
                                    // $lead_cities[] = array('id' => -1, 'name' => "City");
                                    echo render_select('cities[]', $lead_cities, array('name', 'name'), '', $cities, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false); ?>
                                 </div>

                                 <?php
                                 if (defined('CRM_UTILITY_MODULE_NAME')) {
                                    $params = ['show' => 'dropdown'];
                                    hooks()->do_action('init_leads_interface', $params);
                                 }
                                 ?>

                                 <div class="col-md-2 mtop10 leads-filter-column">
                                    <label for="custom_view" class="control-label">Lead Filter</label>
                                    <div class="select-placeholder">
                                       <select name="custom_view" title="<?php echo _l('additional_filters'); ?>" id="custom_view" class="selectpicker" data-width="100%">
                                          <option value=""></option>
                                          <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                          <option value="junk"><?php echo _l('lead_junk'); ?></option>
                                          <option value="public"><?php echo _l('lead_public'); ?></option>
                                          <option value="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                          <option value="created_today"><?php echo _l('created_today'); ?></option>
                                          <option value="today_leads">Today Leads</option>
                                          <option value="lapsed_lead"><?php echo _l('lapsed_lead'); ?></option>
                                          <?php if (has_permission('leads', '', 'edit')) { ?>
                                             <option value="not_assigned"><?php echo _l('leads_not_assigned'); ?></option>
                                          <?php } ?>
                                          <?php if (is_admin()) { ?>
                                             <option value="deleted"><?php echo _l('deleted_lead'); ?></option>
                                          <?php } ?>
                                          <?php if (isset($consent_purposes)) { ?>
                                             <optgroup label="<?php echo _l('gdpr_consent'); ?>">
                                                <?php foreach ($consent_purposes as $purpose) { ?>
                                                   <option value="consent_<?php echo $purpose['id']; ?>">
                                                      <?php echo $purpose['name']; ?>
                                                   </option>
                                                <?php } ?>
                                             </optgroup>
                                          <?php } ?>
                                       </select>
                                    </div>
                                 </div>
                                 <div class="col-md-2 leads-filter-column mtop10">
                                    <label for="rel_type" class="control-label">Followup Date</label>
                                    <?php echo render_date_input('followup_date', '', '', array('title' => "Followup date", 'placeholder' => 'Followup Date', 'name' => 'followupdate'), '', 'no-mbot', 'followupdate'); ?>
                                    <!--?php echo render_date_input('followup_date', '', date('d-m-Y'), array('onchange' => "dt_custom_view('followup_date'); return false;", array('name', 'followupdate')), '', 'no-mbot', 'followupdate'); -->
                                 </div>

                                 <div class="col-md-2  mtop10 border-right form-group">
                                    <label for="date_by" class="control-label"><span class="control-label">FilterByDate</span></label>
                                    <select name="date_by" id="date_by" class="selectpicker no-margin" title="FilterBy Date" data-width=" 100%">
                                       <option value="-1"></option>
                                       <option value="dateadded">Date Added</option>
                                       <option value="lastcontact" <?php echo ($date_by != '' && $date_by == 'lastcontact' ? 'selected' : '') ?>>Last Contacted</option>
                                    </select>
                                 </div>

                                 <div class="col-md-2 leads-filter-column mtop10">
                                    <label for="rel_type" class="control-label">From Date</label>
                                    <?php echo render_date_input('from_date', '', '', array('title' => "From date", 'placeholder' => 'From Date'), '', 'no-mbot', 'fromdate'); ?>
                                 </div>

                                 <div class="col-md-2 leads-filter-column mtop10">
                                    <label for="rel_type" class="control-label">To Date</label>
                                    <?php echo render_date_input('to_date', '', '', array('title' => "To date", 'placeholder' => 'To Date'), '', 'no-mbot', 'todate'); ?>
                                 </div>
                              </div>
                           </div>
                           <div class="clearfix"></div>
                           <hr class="hr-panel-heading" />
                           <div class="col-md-12">
                              <a href="#" data-toggle="modal" data-table=".table-leads" data-target="#leads_bulk_actions" class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                              <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1" role="dialog">
                                 <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                       <div class="modal-header">
                                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                          <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                       </div>
                                       <div class="modal-body">
                                          <?php if (has_permission('leads', '', 'delete')) { ?>
                                             <div class="checkbox checkbox-danger">
                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                             </div>
                                             <hr class="mass_delete_separator" />
                                          <?php } ?>
                                          <div id="bulk_change">
                                             <div class="form-group">
                                                <div class="checkbox checkbox-primary checkbox-inline">
                                                   <input type="checkbox" name="leads_bulk_mark_lost" id="leads_bulk_mark_lost" value="1">
                                                   <label for="leads_bulk_mark_lost">
                                                      <?php echo _l('lead_mark_as_lost'); ?>
                                                   </label>
                                                </div>
                                             </div>
                                             <?php echo render_select('move_to_status_leads_bulk', $statuses, array('id', 'name'), 'ticket_single_change_status'); ?>
                                             <?php
                                             echo render_select('move_to_source_leads_bulk', $sources, array('id', 'name'), 'lead_source');
                                             echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');
                                             if (has_permission('leads', '', 'edit')) {
                                                echo render_select('assign_to_leads_bulk', $staff, array('staffid', array('firstname', 'lastname')), 'leads_dt_assigned');
                                             }
                                             ?>
                                             <div class="form-group">
                                                <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk" value="" data-role="tagsinput">
                                             </div>
                                             <hr />
                                             <div class="form-group no-mbot">
                                                <div class="radio radio-primary radio-inline">
                                                   <input type="radio" name="leads_bulk_visibility" id="leads_bulk_public" value="public">
                                                   <label for="leads_bulk_public">
                                                      <?php echo _l('lead_public'); ?>
                                                   </label>
                                                </div>
                                                <div class="radio radio-primary radio-inline">
                                                   <input type="radio" name="leads_bulk_visibility" id="leads_bulk_private" value="private">
                                                   <label for="leads_bulk_private">
                                                      <?php echo _l('private'); ?>
                                                   </label>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="modal-footer">
                                          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                          <a href="#" class="btn btn-info" onclick="leads_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                       </div>
                                    </div>
                                    <!-- /.modal-content -->
                                 </div>
                                 <!-- /.modal-dialog -->
                              </div>
                              <!-- /.modal -->
                              <?php

                              $table_data = array();
                              $_table_data = array(
                                 '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="leads"><label></label></div>',
                                 array(
                                    'name' => _l('the_number_sign'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-number')
                                 ),
                                 array(
                                    'name' => _l('leads_dt_name'),
                                    'th_attrs' => array('class' => 'toggleable', 'id' => 'th-name')
                                 ),
                              );
                              if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                                 $_table_data[] = array(
                                    'name' => _l('gdpr_consent') . ' (' . _l('gdpr_short') . ')',
                                    'th_attrs' => array('id' => 'th-consent', 'class' => 'not-export')
                                 );
                              }
                              $_table_data[] = array(
                                 'name' => _l('lead_company'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-company')
                              );
                              $_table_data[] =   array(
                                 'name' => _l('leads_dt_email'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-email')
                              );
                              $_table_data[] =  array(
                                 'name' => _l('leads_dt_phonenumber'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-phone')
                              );

                              $_table_data[] =  array(
                                 'name' => _l('tags'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-tags')
                              );
                              $_table_data[] =  array(
                                 'name' => 'Follow up date',
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-followup')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_assigned'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-assigned')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_status'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-status')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_source'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-source')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_last_contact'),
                                 'th_attrs' => array('class' => 'toggleable', 'id' => 'th-last-contact')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_datecreated'),
                                 'th_attrs' => array('class' => 'date-created toggleable', 'id' => 'th-date-created')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_datedeleted'),
                                 'th_attrs' => array('class' => 'date-deleted toggleable', 'id' => 'th-date-deleted')
                              );
                              $_table_data[] = array(
                                 'name' => _l('leads_dt_deletedby'),
                                 'th_attrs' => array('class' => 'date-deleted-by toggleable', 'id' => 'th-date-deleted-by')
                              );
                              foreach ($_table_data as $_t) {
                                 array_push($table_data, $_t);
                              }
                              $custom_fields = get_custom_fields('leads', array('show_on_table' => 1));
                              foreach ($custom_fields as $field) {
                                 array_push($table_data, $field['name']);
                              }
                              $table_data = hooks()->apply_filters('leads_table_columns', $table_data);
                              render_datatable(
                                 $table_data,
                                 'leads',
                                 array('customizable-table'),
                                 array(
                                    'id' => 'table-leads',
                                    'data-last-order-identifier' => 'leads',
                                    'data-default-order' => get_table_last_order('leads'),
                                 )); ?>
                           </div>
                        </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<style :scope>
#wrapper{
    min-height: unset !important;
}
</style>
<script id="hidden-columns-table-leads" type="text/json">
   <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<?php if (staff_can('export', 'leads')) {
   echo '<style>#table-leads_wrapper button.btn.btn-default.buttons-collection.btn-default-dt-options {display: unset!important;}</style>';
} ?>
<script>
   var openLeadID = '<?php echo $leadid; ?>';
   <?php
   if (defined('CRM_UTILITY_MODULE_NAME')) {
      $params = ['show' => 'js'];
      hooks()->do_action('init_leads_interface', $params);
   }
   ?>
   $(function() {
      leads_kanban();
      $('#leads_bulk_mark_lost').on('change', function() {
         $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
         $('#move_to_status_leads_bulk').selectpicker('refresh')
      });
      $('#move_to_status_leads_bulk').on('change', function() {
         if ($(this).selectpicker('val') != '') {
            $('#leads_bulk_mark_lost').prop('disabled', true);
            $('#leads_bulk_mark_lost').prop('checked', false);
         } else {
            $('#leads_bulk_mark_lost').prop('disabled', false);
         }
      });
      $('#table-leads').DataTable().column(13).visible(false);
      $('#table-leads').DataTable().column(14).visible(false);
      $("#custom_view").on("change", function () {
            if ($(this).val() == "deleted") {
                $('#table-leads').DataTable().column(13).visible(true);
                $('#table-leads').DataTable().column(14).visible(true);
            } else {
               $('#table-leads').DataTable().column(13).visible(false);
               $('#table-leads').DataTable().column(14).visible(false);
            }
         });
   });
</script>
</body>
</html>
