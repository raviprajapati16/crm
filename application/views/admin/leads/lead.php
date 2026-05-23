<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-header">
   <button type="button" class="close leadModalCloseBtn"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title">
      <?php if (isset($lead)) {
         if (!empty($lead->name)) {
            $name = $lead->name;
         } else if (!empty($lead->company)) {
            $name = $lead->company;
         } else {
            $name = _l('lead');
         }
         echo '#' . $lead->id . ' - ' . $name;
      } else {
         echo _l('add_new', _l('lead_lowercase'));
      }
      ?>
      <?php
      if (isset($lead)) {
         if ($lead->lost == 1) {
            echo '<span class="lead-customer-label" style="background-color:#fc2d42">' . _l('lead_lost') . '</span>';
         } else if ($lead->junk == 1) {
            echo '<span class="lead-customer-label" style="background-color:#ff6f00">' . _l('lead_junk') . '</span>';
         } else {
            if (total_rows(db_prefix() . 'clients', array(
               'leadid' => $lead->id,
               'deleted_at' => NULL
            ))) {
               echo '<img class="customer-label-image" src="' . site_url('assets/images/customer-label.png') . '" />';
               //echo '<span class="lead-customer-label">'._l('lead_is_client').'</span>';
            }
         }
      }
      ?>
   </h4>
</div>
<div class="modal-body">
   <div class="row">
      <div class="col-md-12">
         <?php if (isset($lead)) {
            echo form_hidden('leadid', $lead->id);
         } ?>
         <div class="top-lead-menu">
            <div class="horizontal-scrollable-tabs preview-tabs-top">
               <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
               <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
               <div class="horizontal-tabs">

                  <ul class="nav-tabs-horizontal nav nav-tabs<?php if (!isset($lead)) {
                                                                  echo ' lead-new';
                                                               } ?>" role="tablist">
                     <li role="presentation" class="active">
                        <a href="#tab_lead_profile" aria-controls="tab_lead_profile" role="tab">
                           <?php echo _l('lead_profile'); ?>
                        </a>
                     </li>
                     <?php if (isset($lead)) { ?>
                        <?php if (count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity) { ?>
                           <li role="presentation">
                              <a href="#tab_email_activity" aria-controls="tab_email_activity" role="tab">
                                 <?php echo hooks()->apply_filters('lead_email_activity_subject', _l('lead_email_activity')); ?>
                              </a>
                           </li>
                        <?php } ?>
                          <li role="presentation">
                           <a href="#lead_notes" aria-controls="lead_notes" role="tab">
                              <?php echo _l('lead_add_edit_notes'); ?>
                           </a>
                        </li>
                            <li role="presentation">
                           <a href="#lead_reminders" onclick="initReminderTable();" aria-controls="lead_reminders" role="tab">
                              <?php echo _l('leads_reminders_tab'); ?>
                              <?php
                              $total_reminders = total_rows(
                                 db_prefix() . 'reminders',
                                 array(
                                    'isnotified' => 0,
                                    'staff' => get_staff_user_id(),
                                    'status' => 'Pending',
                                    'rel_type' => 'lead',
                                    'rel_id' => $lead->id,
                                    'deleted_at' => NULL
                                 )
                              );
                              if ($total_reminders > 0) {
                                 echo '<span class="badge">' . $total_reminders . '</span>';
                              }
                              ?>
                           </a>
                        </li>
                        <?php if (isset($lead) && !$lead->is_vendor) { ?>

                           <li role="presentation">
                              <a href="#tab_proposals_leads" onclick="initDataTable('.table-proposals-lead', admin_url + 'proposals/proposal_relations/' + <?php echo $lead->id; ?> + '/lead','undefined', 'undefined','undefined',[6,'desc']);" aria-controls="tab_proposals_leads" role="tab">
                                 <?php echo _l('proposals'); ?>
                              </a>
                           </li>
                        <?php } ?>
                        <?php if (isset($lead) && $lead->is_vendor) { ?>
                           <!-- <li role="presentation">
                              <a href="#tab_vendor_quotation" onclick="getVendorQuotationFormList()" aria-controls="tab_vendor_quotation" role="tab">
                                 <?php echo _l('tab_vendor_quotation'); ?>
                              </a>
                           </li> -->
                           <li role="presentation">
                              <a href="#tab_purchase_order" onclick="initDataTable('.table-purchase-order', admin_url + 'purchase/purchase_orders_by_vendors/' + <?php echo $lead->id; ?>,'undefined', 'undefined','undefined',[1,'desc']);" aria-controls="tab_purchase_order" role="tab">
                                 Purchase Order
                              </a>
                           </li>
                        <?php } ?>

                        <li role="presentation">
                           <a href="#tab_tasks_leads" onclick="init_rel_tasks_table(<?php echo $lead->id; ?>,'lead','.table-rel-tasks-leads');" aria-controls="tab_tasks_leads" role="tab">
                              <?php echo _l('tasks'); ?>
                           </a>
                        </li>
                        <li role="presentation">
                           <a href="#attachments" aria-controls="attachments" role="tab">
                              <?php echo _l('lead_attachments'); ?>
                           </a>
                        </li>
                    
                      
                        <li role="presentation">
                           <a href="#lead_activity" aria-controls="lead_activity" role="tab">
                              <?php echo _l('lead_add_edit_activity'); ?>
                           </a>
                        </li>
                        <!-- <li role="presentation">
                           <a href="#call_logs" aria-controls="call_logs" role="tab">
                              <?php echo _l('Call_logs'); ?>
                           </a>
                        </li> -->
                        <?php if (is_gdpr() && (get_option('gdpr_enable_lead_public_form') == '1' || get_option('gdpr_enable_consent_for_leads') == '1')) { ?>
                           <li role="presentation">
                              <a href="#gdpr" aria-controls="gdpr" role="tab">
                                 <?php echo _l('gdpr_short'); ?>
                              </a>
                           </li>
                        <?php } ?>
                        <?php if (isset($lead) && !$lead->is_vendor) { ?>
                           <li role="presentation">
                              <a href="#lead_inquiry_forms" onclick="getInquiryFormLists()" aria-controls="lead_inquiry_forms" role="tab">
                                 <?php echo _l('lead_inquiry_form'); ?>
                              </a>
                           </li>
                        <?php } ?>
                        <?php if (isset($lead) && !$lead->is_vendor) { ?>
                           <li role="presentation">
                              <a href="#lead_office_visitors_form" onclick="getOfficeVisitorFormSection()" aria-controls="lead_office_visitors_form" role="tab">
                                 <?php echo _l('lead_office_visitors_form'); ?>
                              </a>
                           </li>
                        <?php } ?>
                        <?php if (isset($lead) && !$lead->is_vendor) { ?>
                           <li role="presentation">
                              <a href="#lead_plant_visit_form" onclick="getPlantVisitorFormSection()" aria-controls="lead_plant_visit_form" role="tab">
                                 <?php echo _l('lead_plant_visit_form'); ?>
                              </a>
                           </li>
                        <?php } ?>
                     <?php } ?>
                  </ul>
               </div>
            </div>
         </div>
         <!-- Tab panes -->
         <div class="tab-content">
            <!-- from leads modal -->
            <div role="tabpanel" class="tab-pane active" id="tab_lead_profile">
               <?php $this->load->view('admin/leads/profile'); ?>
            </div>
            <?php if (isset($lead)) { ?>
               <?php if (count($mail_activity) > 0 || isset($show_email_activity) && $show_email_activity) { ?>
                  <div role="tabpanel" class="tab-pane" id="tab_email_activity">
                     <?php hooks()->do_action('before_lead_email_activity', array('lead' => $lead, 'email_activity' => $mail_activity)); ?>
                     <?php foreach ($mail_activity as $_mail_activity) { ?>
                        <div class="lead-email-activity">
                           <div class="media-left">
                              <i class="fa fa-envelope"></i>
                           </div>
                           <div class="media-body">
                              <h4 class="bold no-margin lead-mail-activity-subject">
                                 <?php echo $_mail_activity['subject']; ?>
                                 <br />
                                 <small class="text-muted display-block mtop5 font-medium-xs"><?php echo _dt($_mail_activity['dateadded']); ?></small>
                              </h4>
                              <div class="lead-mail-activity-body">
                                 <hr />
                                 <?php echo $_mail_activity['body']; ?>
                              </div>
                              <hr />
                           </div>
                        </div>
                        <div class="clearfix"></div>
                     <?php } ?>
                     <?php hooks()->do_action('after_lead_email_activity', array('lead_id' => $lead->id, 'emails' => $mail_activity)); ?>
                  </div>
               <?php } ?>
               <?php if (is_gdpr() && (get_option('gdpr_enable_lead_public_form') == '1' || get_option('gdpr_enable_consent_for_leads') == '1' || (get_option('gdpr_data_portability_leads') == '1') && is_admin())) { ?>
                  <div role="tabpanel" class="tab-pane" id="gdpr">

                     <?php if (get_option('gdpr_enable_lead_public_form') == '1') { ?>
                        <a href="<?php echo $lead->public_url; ?>" target="_blank" class="mtop5">
                           <?php echo _l('view_public_form'); ?>
                        </a>
                     <?php } ?>
                     <?php if (get_option('gdpr_data_portability_leads') == '1' && is_admin()) { ?>
                        <?php
                        if (get_option('gdpr_enable_lead_public_form') == '1') {
                           echo ' | ';
                        }
                        ?>
                        <a href="<?php echo admin_url('leads/export/' . $lead->id); ?>">
                           <?php echo _l('dt_button_export'); ?>
                        </a>
                     <?php } ?>
                     <?php if (get_option('gdpr_enable_lead_public_form') == '1' || (get_option('gdpr_data_portability_leads') == '1' && is_admin())) { ?>
                        <hr class="hr-margin-n-15" />
                     <?php } ?>
                     <?php if (get_option('gdpr_enable_consent_for_leads') == '1') { ?>
                        <h4 class="no-mbot">
                           <?php echo _l('gdpr_consent'); ?>
                        </h4>
                        <?php $this->load->view('admin/gdpr/lead_consent'); ?>
                        <hr />
                     <?php } ?>
                  </div>
               <?php } ?>
               <div role="tabpanel" class="tab-pane" id="lead_activity">
                  <div class="row">
                     <div class="col-md-12">
                        <?= tutorialLinkButtonRender('lead-activity-log-btn', 'right'); ?>
                     </div>
                  </div>
                  <br />
                  <div class="panel_s no-shadow">
                     <div class="activity-feed">
                        <?php foreach ($activity_log as $log) { ?>
                           <div class="feed-item">
                              <div class="date">
                                 <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                                    <?php echo time_ago($log['date']); ?>
                                 </span>
                              </div>
                              <div class="text">
                                 <?php if ($log['staffid'] != 0) { ?>
                                    <a href="<?php echo admin_url('profile/' . $log["staffid"]); ?>">
                                       <?php echo staff_profile_image($log['staffid'], array('staff-profile-xs-image pull-left mright5'));
                                       ?>
                                    </a>
                                 <?php
                                 }
                                 $additional_data = '';
                                 if (!empty($log['additional_data'])) {
                                    $additional_data = unserialize($log['additional_data']);
                                    echo ($log['staffid'] == 0) ? _l($log['description'], $additional_data) : $log['full_name'] . ' - ' . _l($log['description'], $additional_data);
                                 } else {
                                    echo $log['full_name'] . ' - ';
                                    if ($log['custom_activity'] == 0) {
                                       echo _l($log['description']);
                                    } else {
                                       echo _l($log['description'], '', false);
                                    }
                                 }
                                 ?>
                              </div>
                           </div>
                        <?php } ?>
                     </div>
                     <div class="col-md-12">
                        <?php echo render_textarea('lead_activity_textarea', '', '', array('placeholder' => _l('enter_activity')), array(), 'mtop15'); ?>
                        <div class="text-right">
                           <button id="lead_enter_activity" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                     </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="call_logs">
                  <div class="activity-feed">
                     <?php foreach ($call_logs as $call) { ?>
                        <div class="feed-item">
                           <div class="col-md-3 col-xs-12 lead-information-col">
                              <p class="text-muted lead-field-heading no-mtop"> <?php echo _l('start_stamp'); ?> </p>
                              <p class="bold font-medium-xs"> <?php echo $call['start_stamp']; ?> </p>
                           </div>
                           <div class="col-md-2 col-xs-12 lead-information-col">
                              <p class="text-muted lead-field-heading no-mtop"> <?php echo _l('call_phone_number'); ?> </p>
                              <p class="bold font-medium-xs"> <?php echo $call['call_to_number']; ?> </p>
                           </div>
                           <div class="col-md-2 col-xs-12 lead-information-col">
                              <p class="text-muted lead-field-heading no-mtop"> <?php echo _l('call_type'); ?> </p>
                              <p class="bold font-medium-xs"> <?php echo $call['call_status']; ?> </p>
                           </div>
                           <div class="col-md-3 col-xs-12 lead-information-col">
                              <p class="text-muted lead-field-heading no-mtop"><i class="fa fa-clock-o"></i> <?php echo _l('call_duration'); ?> </p>
                              <p class="bold font-medium-xs"><?php echo $call['billsec']; ?>seconds</p>
                           </div>
                           <?php echo form_close(); ?>
                           <?php if ($call['call_status'] == 'answered') { ?>
                              <button class="btn btn-info" onclick="playAudio()" id="playButton"><i class="fa fa-play"></i></button>
                              <audio id="recording" controls style="display: none; height: 80px; float: left; margin-left: 0px;">
                                 <source id="audioSource" src="<?php echo $call['recording_url']; ?>" type="audio/mpeg">
                              </audio>
                              <a href="<?php echo $call['recording_url']; ?>" download="true" target="_blank" class="btn-s uppercase btn btn-info with-ico"><i class="fa fa-download"></i></a>
                           <?php } else { ?>
                              <button class="btn btn-info" onclick="alert('no recording available!');" id="playButton"><i class="fa fa-play"></i></button>
                              <a href="" disabled class="btn-s uppercase btn btn-info with-ico"><i class="fa fa-download"></i></a>
                           <?php } ?>

                        </div>
                     <?php } ?>
                  </div>
                  <div class="clearfix"></div>
                  <hr />
               </div>
               <?php if (isset($lead) && !$lead->is_vendor) { ?>
                  <div role="tabpanel" class="tab-pane" id="tab_proposals_leads">
                     <?php if (has_permission('proposals', '', 'create')) { ?>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mbot25">
                           <a href="<?php echo admin_url('proposals/proposal?rel_type=lead&rel_id=' . $lead->id); ?>"
                              class="btn btn-info mb-2">
                              <?php echo _l('new_proposal'); ?>
                           </a>
                          <?php if (
                              total_rows(db_prefix() . 'proposals', array('rel_type' => 'lead', 'rel_id' => $lead->id)) > 0 &&
                              (has_permission('proposals', '', 'create') || has_permission('proposals', '', 'edit'))
                           ) { ?>
                              <!-- <a href="#" class="btn btn-info mb-2" data-toggle="modal" data-target="#sync_data_proposal_data">
                                 <?php echo _l('sync_data'); ?>
                              </a> -->
                              <?php
                              // $this->load->view(
                              //    'admin/proposals/sync_data',
                              //    array('related' => $lead, 'rel_id' => $lead->id, 'rel_type' => 'lead')
                              // );
                              ?>
                           <?php } ?>
                           <?= tutorialLinkButtonRender('lead-propsal-create-btn', 'right'); ?>
                        </div>
                     <?php } ?> 

                     <?php
                     $table_data = array(
                        _l('proposal') . ' #',
                        _l('proposal_subject'),
                        _l('proposal_total'),
                        _l('proposal_date'),
                        _l('proposal_open_till'),
                        _l('tags'),
                        _l('proposal_date_created'),
                        _l('proposal_status')
                     );
                     $custom_fields = get_custom_fields('proposal', array('show_on_table' => 1));
                     foreach ($custom_fields as $field) {
                        array_push($table_data, $field['name']);
                     }
                     $table_data = hooks()->apply_filters('proposals_relation_table_columns', $table_data);
                     render_datatable($table_data, 'proposals-lead', [], [
                        'data-last-order-identifier' => 'proposals-relation',
                        'data-default-order'         => get_table_last_order('proposals-relation'),
                     ]);
                     ?>
                  </div>
               <?php } ?>
               <div role="tabpanel" class="tab-pane" id="tab_tasks_leads">
                  <?php init_relation_tasks_table(array('data-new-rel-id' => $lead->id, 'data-new-rel-type' => 'lead')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="lead_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info reminder-lead-modal-btn" data-target=".reminder-modal-lead-<?php echo $lead->id; ?>"><i class="fa fa-pencil-square-o"></i> Action</a>
                  <?= tutorialLinkButtonRender('lead-reminder-create-btn', 'right'); ?>
                  <hr />
                  <?php render_datatable(array(_l('reminder_description'), _l('reminder_created_date'), _l('reminder_date'), _l('reminder_status'), _l('reminder_staff'), _l('reminder_action'), _l('reminder_is_notified')), 'reminders-leads'); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="attachments">
                  <div class="row">
                     <div class="col-md-12">
                        <?= tutorialLinkButtonRender('lead-attachment-add-btn', 'right'); ?>
                     </div>
                  </div>
                  <?php echo form_open('admin/leads/add_lead_attachment', array('class' => 'dropzone mtop15 mbot15', 'id' => 'lead-attachment-upload')); ?>
                  <?php echo form_close(); ?>
                  <?php if (get_option('dropbox_app_key') != '') { ?>
                     <hr />
                     <div class="text-right">
                        <button class="gpicker">
                           <i class="fa fa-google" aria-hidden="true"></i>
                           <?php echo _l('choose_from_google_drive'); ?>
                        </button>
                        <div id="dropbox-chooser-lead"></div>
                     </div>
                  <?php } ?>
                  <?php if (count($lead->attachments) > 0) { ?>
                     <div class="mtop20" id="lead_attachments">
                        <?php $this->load->view('admin/leads/leads_attachments_template', array('attachments' => $lead->attachments)); ?>
                     </div>
                  <?php } ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="lead_notes">
                  <div class="row">
                     <div class="col-md-12">
                        <?= tutorialLinkButtonRender('lead-note-create-btn', 'right'); ?>
                     </div>
                  </div>
                  <br />
                  <?php echo form_open(admin_url('leads/add_note/' . $lead->id), array('id' => 'lead-notes')); ?>
                  <div class="form-group">
                     <textarea id="lead_note_description" name="lead_note_description" class="form-control" rows="4"></textarea>
                  </div>
                  <div class="lead-select-date-contacted hide">
                     <?php echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', '', array('data-date-end-date' => date('Y-m-d'))); ?>
                  </div>
                  <div class="radio radio-primary">
                     <input type="radio" name="contacted_indicator" id="contacted_indicator_yes" value="yes">
                     <label for="contacted_indicator_yes"><?php echo _l('lead_add_edit_contacted_this_lead'); ?></label>
                  </div>
                  <div class="radio radio-primary">
                     <input type="radio" name="contacted_indicator" id="contacted_indicator_no" value="no" checked>
                     <label for="contacted_indicator_no"><?php echo _l('lead_not_contacted'); ?></label>
                  </div>
                  <button type="submit" class="btn btn-info pull-right"><?php echo _l('lead_add_edit_add_note'); ?></button>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <hr />
                  <div class="panel_s no-shadow">
                     <?php
                     $len = count($notes);
                     $i = 0;
                     foreach ($notes as $note) { ?>
                        <div class="media lead-note">
                           <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                              <?php echo staff_profile_image($note['addedfrom'], array('staff-profile-image-small', 'pull-left mright10')); ?>
                           </a>
                           <div class="media-body">
                              <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                 <a href="#" class="pull-right text-danger" onclick="delete_lead_note(this,<?php echo $note['id']; ?>, <?php echo $lead->id; ?>);return false;"><i class="fa fa fa-times"></i></a>
                                 <a href="#" class="pull-right mright5" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                              <?php } ?>
                              <?php if (!empty($note['date_contacted'])) { ?>
                                 <span data-toggle="tooltip" data-title="<?php echo _dt($note['date_contacted']); ?>">
                                    <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                                 </span>
                              <?php } ?>
                              <small><?php echo _l('lead_note_date_added', _dt($note['dateadded'])); ?></small>
                              <a href="<?php echo admin_url('profile/' . $note["addedfrom"]); ?>" target="_blank">
                                 <h5 class="media-heading bold"><?php echo get_staff_full_name($note['addedfrom']); ?></h5>
                              </a>
                              <div data-note-description="<?php echo $note['id']; ?>" class="text-muted">
                                 <?php echo check_for_links(app_happy_text($note['description'])); ?>
                              </div>
                              <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide mtop15">
                                 <?php echo render_textarea('note', '', $note['description']); ?>
                                 <div class="text-right">
                                    <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                                    <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                                 </div>
                              </div>
                           </div>
                           <?php if ($i >= 0 && $i != $len - 1) {
                              echo '<hr />';
                           }
                           ?>
                        </div>
                     <?php $i++;
                     } ?>
                  </div>
               </div>
               <?php if (isset($lead) && !$lead->is_vendor) { ?>
                  <div role="tabpanel" class="tab-pane" id="lead_inquiry_forms">
                     <div class="row">
                        <div class="col-md-12">
                           <?= tutorialLinkButtonRender('lead-inquiry-form-btn', 'right'); ?>
                        </div>
                     </div>
                     <br />
                     <div class="row">
                        <div class="col-md-4">
                           <label for="main_group_id" class="control-label">Main Group</label>
                           <?php echo render_select('main_group_id', $main_group_data, array('id', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Not Selected"), array(), 'no-mbot'); ?>
                        </div>
                        <div class="col-md-4">
                           <label for="sub_group_id" class="control-label">Sub Group</label>
                           <select name="sub_group_id" id="sub_group_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                              <option value=""></option>
                              <?php
                              foreach ($sub_group_data as $key => $item) {
                              ?>
                                 <option data-main-group-id="<?php echo $item['group_id'] ?>" value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
                              <?php
                              }
                              ?>
                           </select>
                        </div>
                     </div>
                     <div class="row mtop5">
                        <div class="col-md-12">
                           <div class="panel-group inquiry-new-form-section" id="accordionnew">
                           </div>
                           <div class="panel-group inquiry-form-section" id="accordionold">
                           </div>
                        </div>
                     </div>
                  </div>
               <?php } ?>
               <?php if (isset($lead) && !$lead->is_vendor) { ?>
                  <div role="tabpanel" class="tab-pane" id="lead_office_visitors_form"></div>
               <?php } ?>
               <?php if (isset($lead) && !$lead->is_vendor) { ?>
                  <div role="tabpanel" class="tab-pane" id="lead_plant_visit_form"></div>
               <?php } ?>
               <?php if (isset($lead) && $lead->is_vendor) { ?>
                  <!-- <div role="tabpanel" class="tab-pane" id="tab_vendor_quotation">
                     <div class="row">
                        <div class="col-md-12">
                           <button type="button" onclick="createNewQuotationForm()" class="btn btn-primary add-new-vendor-quotation">Create</button>
                        </div>
                     </div>
                     <div class="row mtop5">
                        <div class="col-md-12">
                           <div class="panel-group vendor-quotation-html-section">
                           </div>
                        </div>
                     </div>
                  </div> -->
                  <div role="tabpanel" class="tab-pane" id="tab_purchase_order">
                     <?php if (has_permission('purchase', '', 'create')) { ?>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mbot25">
                           <a href="<?php echo admin_url('purchase/purchase?vendor_id=' . $lead->id); ?>"
                              class="btn btn-info mb-2">
                              New Purchase Order
                           </a>
                           <?php if (
                              total_rows(db_prefix() . 'purchase', array('vendor_id' => $lead->id)) > 0 &&
                              (has_permission('purchase', '', 'create') || has_permission('purchase', '', 'edit'))
                           ) { ?>
                              <!-- <a href="#" class="btn btn-info mb-2" data-toggle="modal" data-target="#sync_data_purchase_data">
                                 <?php echo _l('sync_data'); ?>
                              </a> -->
                              <?php
                              // $this->load->view(
                              //    'admin/purchase/sync_data',
                              //    array('related' => $lead, 'vendor_id' => $lead->id)
                              // );
                              ?>
                           <?php } ?>
                        </div>
                     <?php } ?>

                     <?php
                     $table_data = array(
                        "Date",
                        "Purchase Order No.",
                        "Vendor Name",
                        "State",
                        "Country",
                        "Bill Amount",
                        "Status",
                     );
                     $table_data = hooks()->apply_filters('purchase_table_columns', $table_data);
                     render_datatable($table_data, 'purchase-order', [], []);
                     ?>
                  </div>
               <?php } ?>
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<?php hooks()->do_action('lead_modal_profile_bottom', (isset($lead) ? $lead->id : '')); ?>

<script>
   $(document).ready(function() {
      init_ajax_search('customer', '#assigned_customer_id.ajax-search', undefined, "<?php echo admin_url('misc/get_relation_data'); ?>");
      var module_type = $('#wrapper').attr('data-module');
      $('.convert-lead-vendor-btn').on('click', function() {
         if (confirm("Are you sure you want to perform this action?")) {
            var type = $(this).attr('data-type');
            var lead_id = $('input[name="leadid"]').val();
            if (type != '' && lead_id != '') {
               $.ajax({
                  url: "<?php echo admin_url('leads/lead_vendor_convert'); ?>",
                  method: "POST",
                  data: {
                     type: type,
                     leadId: lead_id
                  },
                  dataType: 'json'
               }).done(function(result) {
                  if (result.success) {
                     location.reload();
                     alert_float('success', result.message);
                  } else {
                     alert_float('danger', result.message);
                  }
               });
            }
         }
      });

      $('#sub_group_id').find('option').hide();
      $('#main_group_id').on('change', function() {
         var selectedMainGroup = $(this).val();
         var subDropdown = $('#sub_group_id');
         subDropdown.find('option').hide();
         subDropdown.find('option[data-main-group-id="' + selectedMainGroup + '"]').show();
         subDropdown.val('');
         subDropdown.selectpicker('refresh');
         $('.inquiry-new-form-section').empty();
         createNewInquiryForm();
      });
      $('#sub_group_id').on('change', function() {
         $('.inquiry-new-form-section').empty();
         createNewInquiryForm();
      });

      $('a[role="tab"]').off('click')
      $('a[role="tab"]').on('click', function(e) {
         e.preventDefault();
         if (checkChangesUnsaved()) {
            if (confirm("Your changes will be not saved. are you sure you want change tab ?")) {
               $(this).tab('show');
            }
         } else {
            $(this).tab('show');
         }
      });

      $(document).off('click', '#lead-modal .leadModalCloseBtn');
      $(document).on('click', '#lead-modal .leadModalCloseBtn', function() {
         if (checkChangesUnsaved()) {
            if (confirm("Your changes will be not saved. are you sure you want to close ?")) {
               $('#lead-modal').modal('hide');
            }
         } else {
            $('#lead-modal').modal('hide');
         }
      });

      $(document).off("hidden.bs.modal", '.modal-reminder-update');
      $(document).on("hidden.bs.modal", '.modal-reminder-update', function(e) {
         var $this = $(this);
         $this.find(':input:not([type=hidden]), textarea').val('');
         $this.find('select').selectpicker('val', '');
         $this.find('.start-time-section').addClass('hide');
         $this.find('.end-time-section').addClass('hide');
         $this.find('.duration-section').addClass('hide');
      });
   });

   $(document).off("click", '.delete_lead_global');
   $(document).on('click', '.delete_lead_global', function() {
      var delete_url = $(this).data('url');
      $.ajax({
         url: delete_url,
         method: "POST",
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            alert_float('success', result.message);
            $('#lead-modal').modal('hide');
            if (table) {
               table.draw();
            }
         } else {
            alert_float('danger', result.message);
         }
      });
   });


   $(document).off("shown.bs.modal", '#sync_data_purchase_data');
   $(document).on("shown.bs.modal", '#sync_data_purchase_data', function() {
      $('#lead-modal .data').eq(0).css('height', ($('#sync_data_purchase_data .modal-content').height() + 80) + 'px').css('overflow-x', 'hidden');
   });

   $(document).off("hidden.bs.modal", '#sync_data_purchase_data');
   $(document).on("hidden.bs.modal", '#sync_data_purchase_data', function() {
      $('#lead-modal .data').prop('style', '');
   });

   $(document).off("click", '.close-purchase-sync-modal');
   $(document).on("click", '.close-purchase-sync-modal', function() {
      $('#sync_data_purchase_data').modal('hide');
   });

   $(document).off("click", '.close-proposal-sync-modal');
   $(document).on("click", '.close-proposal-sync-modal', function() {
      $('#sync_data_proposal_data').modal('hide');
   });

   $(document).on('hidden.bs.modal', '.modal', function () {
    if($('.modal:visible').length) {
        $('body').addClass('modal-open'); // Keep scroll disabled
    }
});

$(document).on('show.bs.modal', '.modal', function () {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
});



   function sync_purchase_data(vendor_id) {
      var data = {};
      var modal_sync = $('#sync_data_purchase_data');
      data.country = modal_sync.find('select[name="country"]').val();
      data.zip = modal_sync.find('input[name="zip"]').val();
      data.state = modal_sync.find('input[name="state"]').val();
      data.city = modal_sync.find('input[name="city"]').val();
      data.address = modal_sync.find('textarea[name="address"]').val();
      data.phone = modal_sync.find('input[name="phone"]').val();
      data.vendor_id = vendor_id;
      $.post(admin_url + 'purchase/sync_data', data).done(function(response) {
         response = JSON.parse(response);
         alert_float('success', response.message);
         modal_sync.modal('hide');
      });
   }


   $('.lead-company-name').prop('required', true);
   var label = $("label[for='" + $('.lead-company-name').attr('id') + "']");
   label.find('.required-asterisk').remove();
   label.prepend('<span class="required-asterisk text-danger"> *</span>');
   validate_lead_form();

   function createNewInquiryForm() {
      var mainGroupId = $('#main_group_id').val();
      var subGroupId = $('#sub_group_id').val();
      if (mainGroupId != "" && mainGroupId != null) {
         if ($('#sub_group_id option[data-main-group-id="' + mainGroupId + '"]').length != 0 && (subGroupId == null || subGroupId == "")) {
            return false;
         }
         $.ajax({
            url: "<?php echo admin_url('leads/inquriry_form_render'); ?>",
            method: "POST",
            data: {
               mainGroupId: mainGroupId,
               subGroupId: subGroupId
            },
            dataType: 'json'
         }).done(function(result) {
            if (result.success && result.html != null && result.html != "") {
               $('.inquiry-new-form-section').html(result.html);
               appSelectPicker();
               appDatepicker();
               $('.render-input-disabled').on('keydown', false);
            } else {
               alert_float('danger', result.message);
            }
         });
      }
   }

   function getInquiryFormLists(formId = "") {
      $('.inquiry-form-section').empty();
      $('.inquiry-form-section').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
      $.ajax({
         url: "<?php echo admin_url('leads/getInquiryFormLists'); ?>",
         method: "POST",
         data: {
            leadid: $('input[name="leadid"]').val(),
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success && result.html != null && result.html != "") {
            $('.inquiry-form-section').html(result.html);
            appSelectPicker();
            appDatepicker();
            $('.render-input-disabled').on('keydown', false);
            if (formId != "") {
               $('.panel-main-form-' + formId).find('.accordian-collapse').trigger('click');
            }
         } else {
            $('.inquiry-form-section').html("<div class='text-center'>No forms available</div>");
         }
      });
   }

   function getOfficeVisitorFormSection(create_form = false) {
      $('#lead_office_visitors_form').empty();
      $('#lead_office_visitors_form').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
      $.ajax({
         url: "<?php echo admin_url('Leads_office_visitor_forms/getOfficeVisitFormSection'); ?>",
         method: "POST",
         data: {
            create_form: create_form,
            leadid: $('input[name="leadid"]').val(),
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success && result.html != null && result.html != "") {
            $('#lead_office_visitors_form').html(result.html);
            appSelectPicker();
            appDatepicker();
         } else {
            $('#lead_office_visitors_form').html("<div class='text-center'>No forms available</div>");
         }
      });
   }

   function getPlantVisitorFormSection(create_form = false) {
      $('#lead_plant_visit_form').empty();
      $('#lead_plant_visit_form').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
      $.ajax({
         url: "<?php echo admin_url('Leads_plant_visit_forms/getPlantVisitFormSection'); ?>",
         method: "POST",
         data: {
            create_form: create_form,
            leadid: $('input[name="leadid"]').val(),
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success && result.html != null && result.html != "") {
            $('#lead_plant_visit_form').html(result.html);
            appSelectPicker();
            appDatepicker();
         } else {
            $('#lead_plant_visit_form').html("<div class='text-center'>No forms available</div>");
         }
      });
   }

   function createNewQuotationForm() {
      var totalQuotationForms = $('.quotation-main-panel').length;
      if (totalQuotationForms == 3) {
         alert_float('danger', "Sorry ! You can create maximum 3 quotation forms. please delete any one old quotation form and you will create new form.");
         return false;
      }
      $.ajax({
         url: "<?php echo admin_url('vendors/save_quotation_form'); ?>",
         data: {
            lead_id: $('input[name="leadid"]').val(),
         },
         method: "POST",
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            getVendorQuotationFormList(result.id);
            alert_float('success', result.message);
         } else {
            alert_float('danger', result.message);
         }

      });

   }

   function getVendorQuotationFormList(id = "", mode = "view") {
      $('.vendor-quotation-html-section').empty();
      $('.vendor-quotation-html-section').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
      $.ajax({
         url: "<?php echo admin_url('vendors/get_vendor_quotation_form_list'); ?>",
         method: "POST",
         data: {
            lead_id: $('input[name="leadid"]').val(),
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success && result.html != null && result.html != "") {
            $('.vendor-quotation-html-section').html(result.html);
            appDatepicker();
            $('.render-input-disabled').on('keydown', false);
            if (id != "") {
               console.log(mode)
               if (mode == "view") {
                  $('.panel-quotation-main-form-' + id).find('.quotation-form-section').addClass('hide');
                  $('.panel-quotation-main-form-' + id).find('.quotation-preview-section').removeClass('hide');
               } else {
                  $('.panel-quotation-main-form-' + id).find('.quotation-form-section').removeClass('hide');
                  $('.panel-quotation-main-form-' + id).find('.quotation-preview-section').addClass('hide');
               }
               $('.panel-quotation-main-form-' + id).find('.accordian-collapse').trigger('click');
            }
         } else {
            $('.vendor-quotation-html-section').html("<div class='text-center'>No forms available</div>");
         }
      });
   }

   function checkChangesUnsaved() {
      var newFormCount = $('.inquiry-new-form-section').children().length;
      var formEditMode = false;
      $('.question-form-section').each(function() {
         if (!$(this).hasClass('hide')) {
            formEditMode = true;
         }
      });
      $('.quotation-form-section').each(function() {
         if (!$(this).hasClass('hide')) {
            formEditMode = true;
         }
      });
      if (newFormCount > 0 || formEditMode) {
         return true;
      }
      return false;
   }

   function playAudio() {
      var audio = document.getElementById('recording');
      var playButton = document.getElementById('playButton');
      audio.style.display = 'block';
      audio.play();
      playButton.style.display = 'none';
   }

   function meetinglinkValidate(url) {
      if (isValidURL(url)) {
         var domain = extractDomain(url);
         if (domain.includes('meet.google.com')) {
            return 'Google Meet'
         } else if (domain.includes('zoom.us')) {
            return 'Zoom'
         } else if (domain.includes('teams.microsoft.com')) {
            return 'Microsoft Teams'
         } else {
            return 'Other'
         }
         return false;
      } else {
         return false;
      }
   }

   function isValidURL(url) {
      var pattern = new RegExp('^(https?:\\/\\/)?' +
         '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' +
         '((\\d{1,3}\\.){3}\\d{1,3}))' +
         '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' +
         '(\\?[;&a-z\\d%_.~+=-]*)?' +
         '(\\#[-a-z\\d_]*)?$', 'i');
      return pattern.test(url);
   }

   function extractDomain(url) {
      var domain;
      if (url.indexOf("://") > -1) {
         domain = url.split('/')[2];
      } else {
         domain = url.split('/')[0];
      }
      domain = domain.split(':')[0];
      return domain;
   }

   function initReminderTable() {
      initDataTable('.table-reminders-leads', admin_url + 'misc/get_reminders/' + <?php echo $lead->id; ?> + '/' + 'lead', undefined, undefined, undefined, [1, 'asc']);
   }
</script>
<style>
   .customer-label-image {
      width: 100px;
      position: absolute;
      top: -9px;
      transform: rotate(40deg);
   }
</style>