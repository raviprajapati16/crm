<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$rel_id = "";
if (isset($contract->client)) {
   if (!empty($contract->client)) {
      $rel_id = $contract->client;
   }
}
?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-5 left-column">
            <div class="panel_s">
               <div class="panel-body">
                  <?php echo form_open($this->uri->uri_string(), array('id' => 'contract-form')); ?>
                  <div class="form-group">
                     <div class="checkbox checkbox-primary no-mtop checkbox-inline">
                        <input type="checkbox" id="trash" name="trash" <?php if (isset($contract)) {
                                                                           if ($contract->trash == 1) {
                                                                              echo ' checked';
                                                                           }
                                                                        }; ?>>
                        <label for="trash"><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="left" title="<?php echo _l('contract_trash_tooltip'); ?>"></i> <?php echo _l('contract_trash'); ?></label>
                     </div>
                     <div class="checkbox checkbox-primary checkbox-inline hide">
                        <input type="checkbox" name="not_visible_to_client" id="not_visible_to_client" <?php if (isset($contract)) {
                                                                                                            if ($contract->not_visible_to_client == 1) {
                                                                                                               echo 'checked';
                                                                                                            }
                                                                                                         }; ?>>
                        <label for="not_visible_to_client"><?php echo _l('contract_not_visible_to_client'); ?></label>
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="number">Agreement Number</label>
                     <div class="input-group">
                        <span class="input-group-addon" id="prefix"><?= (isset($contract) && $contract->prefix) ? $contract->prefix : contract_number_prefix() ?></span>
                        <input type="number" id="number" name="number" class="form-control" value="<?= (isset($contract) && $contract->number) ? $contract->number : get_next_number("contract", contract_number_prefix()) ?>">
                     </div>
                  </div>
                  <div class="form-group select-placeholder">
                     <label for="rel_type" class="control-label"><?php echo _l('proposal_related'); ?></label>
                     <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""></option>
                        <option value="customer" <?php if ((isset($contract) &&  $contract->rel_type == 'customer')) {
                                                      echo 'selected';
                                                   } ?>>Customer</option>
                        <option value="vendor" <?php if ((isset($contract) && $contract->rel_type == 'vendor')) {
                                                   echo 'selected';
                                                } ?>>Vendor</option>
                        <option value="contact_book" <?php if ((isset($contract) && $contract->rel_type == 'contact_book')) {
                                                         echo 'selected';
                                                      } ?>>Contact Book</option>
                     </select>
                  </div>
                  <div class="form-group select-placeholder <?php if (empty($rel_id)) {
                                                               echo ' hide';
                                                            } ?>" id="rel_id_wrapper">
                     <label for="rel_id" class="control-label"><small class="req text-danger">* </small> <span class="rel_id_label"></span></label>
                     <div id="rel_id_select">
                        <select id="rel_id" name="client" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php $selected = (isset($contract) ? $contract->client : '');
                           if ($selected != '') {
                              if ($contract->rel_type == 'vendor') {
                                 $rel_data = get_relation_data('vendor', $selected);
                                 $rel_val = get_relation_values($rel_data, 'vendor');
                              } else if ($contract->rel_type == 'customer') {
                                 $rel_data = get_relation_data('customer', $selected);
                                 $rel_val = get_relation_values($rel_data, 'customer');
                              } else if ($contract->rel_type == 'contact_book') {
                                 $rel_data = get_relation_data('contact_book', $selected);
                                 $rel_val = get_relation_values($rel_data, 'contact_book');
                              }
                              echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                           } ?>
                        </select>
                     </div>
                  </div>
                  <?php $value = (isset($contract) ? $contract->subject : ''); ?>
                  <i class="fa fa-question-circle pull-left" data-toggle="tooltip" title="<?php echo _l('contract_subject_tooltip'); ?>"></i>
                  <?php echo render_input('subject', 'contract_subject', $value); ?>
                  <div class="form-group">
                     <label for="contract_value"><?php echo _l('contract_value'); ?></label>
                     <div class="input-group" data-toggle="tooltip" title="<?php echo _l('contract_value_tooltip'); ?>">
                        <input type="number" class="form-control" name="contract_value" value="<?php if (isset($contract)) {
                                                                                                   echo $contract->contract_value;
                                                                                                } ?>">
                        <div class="input-group-addon">
                           <?php echo $base_currency->symbol; ?>
                        </div>
                     </div>
                  </div>
                  <?php
                  if (!isset($contract->id)) {
                     echo render_select('contract_type', $types, array('id', 'name'), 'Agreement Type',);
                     echo render_select('sub_type', [], [], 'Agreement Sub Type');
                     echo render_select('draft_id', [], [], 'Agreement Draft');
                  } else {
                  ?>
                     <div class="form-group">
                        <label class="control-label">
                           <small class="req text-danger">* </small>Agreement Type
                        </label>
                        <input type="text" class="form-control" value="<?= (isset($selected_contract_type->name)) ? $selected_contract_type->name : '' ?>" readonly>
                     </div>
                     <div class="form-group">
                        <label class="control-label">
                           <small class="req text-danger">* </small>Agreement Sub Type
                        </label>
                        <input type="text" class="form-control" value="<?= (isset($selected_sub_contract_type['name'])) ? $selected_sub_contract_type['name'] : '' ?>" readonly>
                     </div>
                     <div class="form-group">
                        <label class="control-label">
                           <small class="req text-danger">* </small>Agreement Draft
                        </label>
                        <input type="text" class="form-control" value="<?= (isset($selected_contract_draft['draft_title'])) ? $selected_contract_draft['draft_title'] : '' ?>" readonly>
                     </div>
                  <?php
                  }
                  ?>
                  <div class="row">
                     <div class="col-md-12">
                        <div class="form-group">
                           <label for="proposal_id">Proposal</label>
                           <select id="proposal_id" class="selectpicker" data-width="100%" data-none-selected-text="Select Proposal" multiple="1" data-actions-box="1" data-live-search="true" tabindex="-98" <?= (isset($contract->id) ? "disabled" : "name='proposal_id[]'") ?>>
                              <option value=""></option>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($contract) ? _d($contract->datestart) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('datestart', 'contract_start_date', $value); ?>
                     </div>
                     <div class="col-md-6">
                        <?php $value = (isset($contract) ? _d($contract->dateend) : ''); ?>
                        <?php echo render_date_input('dateend', 'contract_end_date', $value); ?>
                     </div>
                  </div>
                  <?php $value = (isset($contract) ? $contract->description : ''); ?>
                  <?php echo render_textarea('description', 'contract_description', $value, array('rows' => 10)); ?>
                  <?php $contract_id = (isset($contract) ? $contract->id : false); ?>
                  <?php echo render_custom_fields('contracts', $contract_id); ?>
                  <div class="btn-bottom-toolbar text-right">
                     <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
               </div>
            </div>

            <div class="row mtop25">
               <div class="col-md-12">
                  <h4>Agreement Sign Details</h4>
               </div>
               <div class="col-md-12">
                  <div class="panel-group" id="accordion">
                     <?php if (!empty($contract_contacts)) {
                        foreach ($contract_contacts as $key => $contact) {
                           $contact_name = $contact['name'];
                     ?>
                           <div class="panel panel-primary">
                              <div class="panel-heading">
                                 <h4 class="panel-title sign-panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $key ?>">
                                       <i class="fa fa-user"></i> <?= $contact_name  ?></a>
                                    <?php if ($contact['signed'] == '1') { ?>
                                       <span class="text text-default pull-right">Signed <i class="fa fa-check-circle text-success" aria-hidden="true"></i></span>
                                    <?php } else { ?>
                                       <span class="text text-default pull-right">Not Signed <i class="fa fa-times text-danger" aria-hidden="true"></i></span>

                                    <?php } ?>
                                 </h4>

                              </div>
                              <div id="collapse<?= $key ?>" class="panel-collapse collapse">
                                 <div class="panel-body">
                                    <div class="row">
                                       <?php if ($contact['signed'] == '1') { ?>
                                          <div class="col-md-12">
                                             <table class="table table-bordered">
                                                <tr>
                                                   <td><strong>Full Name</strong></td>
                                                   <td><?= $contact['acceptance_firstname'] ?> <?= $contact['acceptance_lastname'] ?></td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Email</strong></td>
                                                   <td><?= $contact['acceptance_email'] ?></td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Agreement Signed Date & Time</strong></td>
                                                   <td><?= date('d-m-Y H:i:s a', strtotime($contact['acceptance_date'])); ?></td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Agreement Signed System IP</strong></td>
                                                   <td><?= $contact['acceptance_ip']; ?></td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Selfie</strong></td>
                                                   <td>
                                                      <img width="200px" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contact['contract_id'] . '/' . $contact['acceptance_selfie'])); ?>" class="img-responsive" alt="">
                                                   </td>
                                                </tr>
                                                <tr data-type="digital" data-contactid="<?= $contact['id'] ?>" class="changeSign">
                                                   <td><strong class="sign-text bold <?= ($contact['default_signature'] == "digital") ? 'default-sign' : '' ?>">Digital Signature <?= ($contact['default_signature'] == "digital") ? '<i class="fa fa-check-circle" aria-hidden="true"></i>' : '' ?></strong></td>
                                                   <td data-type="digital" data-contactid="<?= $contact['id'] ?>" class="changeSign">
                                                      <img width="200px" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contact['contract_id'] . '/' . $contact['digital_signature'])); ?>" class="img-responsive" alt="">
                                                   </td>
                                                </tr>
                                                <tr data-type="physical" data-contactid="<?= $contact['id'] ?>" class="changeSign">
                                                   <td><strong class="sign-text bold <?= ($contact['default_signature'] == "physical") ? 'default-sign' : '' ?>">Physical Signature <?= ($contact['default_signature'] == "physical") ? '<i class="fa fa-check-circle" aria-hidden="true"></i>' : '' ?></strong></td>
                                                   <td>
                                                      <img width="200px" src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contact['contract_id'] . '/' . $contact['physical_signature'])); ?>" class="img-responsive" alt="">
                                                   </td>
                                                </tr>
                                                <?php if ($contact['signed'] == '1' && has_permission('contracts', '', 'delete')) { ?>
                                                   <tr>
                                                      <td class="text-center" colspan="2">
                                                         <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('contracts/clear_signature/' . $contract->id . '/' . $contact['id']); ?>" data-toggle="tooltip" title="Clear Signatures">
                                                            <i class="fa fa-remove"></i> Clear Signature
                                                         </a>
                                                      </td>
                                                   </tr>
                                                <?php } ?>
                                             </table>
                                          </div>
                                       <?php } else { ?>
                                          <div class="bold text-danger text-center">Not signed yet</div>
                                       <?php } ?>
                                    </div>
                                 </div>
                              </div>
                           </div>
                     <?php }
                     } ?>

                  </div>
               </div>
            </div>
         </div>
         <?php if (isset($contract)) { ?>
            <div class="col-md-7 right-column">
               <div class="panel_s">
                  <div class="panel-body">
                     <h4 class="no-margin"><?php echo $contract->subject; ?></h4>
                     <a href="<?php echo site_url('contract/' . $contract->id . '/' . $contract->hash); ?>" target="_blank">
                        <?php echo _l('view_contract'); ?>
                     </a>
                     <?= get_contract_status($contract->id, 'contract-status'); ?>
                     <hr class="hr-panel-heading" />
                     <?php if ($contract->trash > 0) {
                        echo '<div class="ribbon default"><span>' . _l('contract_trash') . '</span></div>';
                     } ?>
                     <div class="horizontal-scrollable-tabs preview-tabs-top">
                        <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                        <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                        <div class="horizontal-tabs">
                           <ul class="nav nav-tabs tabs-in-body-no-margin contract-tab nav-tabs-horizontal mbot15" role="tablist">
                              <li role="presentation" class="<?php if (!$this->input->get('tab') || $this->input->get('tab') == 'tab_content') {
                                                                  echo 'active';
                                                               } ?>">
                                 <a href="#tab_content" aria-controls="tab_content" role="tab" data-toggle="tab">
                                    <?php echo _l('contract_content'); ?>
                                 </a>
                              </li>
                              <?php if (isset($contract) && !empty($proposals)) { ?>
                                 <li role="presentation" class="<?php if ($this->input->get('tab') == 'payments_terms') {
                                                                     echo 'active';
                                                                  } ?>">
                                    <a href="#tab_payment_terms" aria-controls="tab_payment_terms" role="tab" data-toggle="tab">
                                       <?php echo _l('contract_payment_terms'); ?>
                                    </a>
                                 </li>
                                 <li role="presentation" class="<?php if ($this->input->get('tab') == 'payments_received') {
                                                                     echo 'active';
                                                                  } ?>">
                                    <a href="#tab_payments_received" aria-controls="tab_payments_received" role="tab" data-toggle="tab">
                                       <?php echo _l('contract_payment_received'); ?>
                                    </a>
                                 </li>
                              <?php } ?>
                              <li role="presentation" class="<?php if ($this->input->get('tab') == 'attachments') {
                                                                  echo 'active';
                                                               } ?>">
                                 <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
                                    <?php echo _l('contract_attachments'); ?>
                                    <?php if ($totalAttachments = count($contract->attachments)) { ?>
                                       <span class="badge attachments-indicator"><?php echo $totalAttachments; ?></span>
                                    <?php } ?>
                                 </a>
                              </li>
                              <li role="presentation">
                                 <a href="#tab_comments" aria-controls="tab_comments" role="tab" data-toggle="tab" onclick="get_contract_comments(); return false;">
                                    <?php echo _l('contract_comments'); ?>
                                    <?php
                                    $totalComments = total_rows(db_prefix() . 'contract_comments', 'contract_id=' . $contract->id)
                                    ?>
                                    <span class="badge comments-indicator<?php echo $totalComments == 0 ? ' hide' : ''; ?>"><?php echo $totalComments; ?></span>
                                 </a>
                              </li>
                              <li role="presentation" class="<?php if ($this->input->get('tab') == 'renewals') {
                                                                  echo 'active';
                                                               } ?>">
                                 <a href="#renewals" aria-controls="renewals" role="tab" data-toggle="tab">
                                    <?php echo _l('no_contract_renewals_history_heading'); ?>
                                    <?php if ($totalRenewals = count($contract_renewal_history)) { ?>
                                       <span class="badge"><?php echo $totalRenewals; ?></span>
                                    <?php } ?>
                                 </a>
                              </li>
                              <li role="presentation" class="tab-separator">
                                 <a href="#tab_tasks" aria-controls="tab_tasks" role="tab" data-toggle="tab" onclick="init_rel_tasks_table(<?php echo $contract->id; ?>,'contract'); return false;">
                                    <?php echo _l('tasks'); ?>
                                 </a>
                              </li>
                              <li role="presentation" class="tab-separator">
                                 <a href="#tab_notes" onclick="get_sales_notes(<?php echo $contract->id; ?>,'contracts'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                                    <?php echo _l('contract_notes'); ?>
                                    <span class="notes-total">
                                       <?php if ($totalNotes > 0) { ?>
                                          <span class="badge"><?php echo $totalNotes; ?></span>
                                       <?php } ?>
                                    </span>
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
                                 <a href="#" onclick="contract_full_view(); return false;" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>">
                                    <i class="fa fa-expand"></i></a>
                              </li>
                           </ul>
                        </div>
                     </div>
                     <div class="tab-content">
                        <div role="tabpanel" class="tab-pane<?php if (!$this->input->get('tab') || $this->input->get('tab') == 'tab_content') {
                                                               echo ' active';
                                                            } ?>" id="tab_content">
                           <div class="row">
                              <div class="col-md-12 text-right _buttons">
                                 <div class="btn-group">
                                    <?php
                                    if ($contract->contract_status == "in review") {
                                    ?>
                                       <a href="#" class="btn btn-success btn-sm mright5 verify-btn <?= (empty($contract->other_content)) ? 'hide' : '' ?>" data-target="#contract_verification_modal" data-toggle="modal"><span class="btn-with-tooltip" data-toggle="tooltip" data-title="Agreement Verification" data-placement="bottom">
                                             Mark as Verified</span>
                                       </a>
                                    <?php
                                    }
                                    ?>
                                    <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                                                                                                                                                                                             echo ' PDF';
                                                                                                                                                                                          } ?> <span class="caret"></span></a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li class="hidden-xs"><a href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                                       <li class="hidden-xs"><a href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                                       <li><a href="<?php echo admin_url('contracts/pdf/' . $contract->id); ?>"><?php echo _l('download'); ?></a></li>
                                       <li>
                                          <a href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?print=true'); ?>" target="_blank">
                                             <?php echo _l('print'); ?>
                                          </a>
                                       </li>
                                    </ul>
                                 </div>
                                 <?php if ($contract->contract_status != "verified") { ?>
                                    <a href="javascript:;" class="btn btn-default btn-with-tooltip" data-target="#send_to_client_whatsapp_modal" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="Send to Whatsapp" data-placement="bottom"><i class="fa fa-whatsapp"></i></span></a>
                                    <a href="#" class="btn btn-default" data-target="#contract_send_to_client_modal" data-toggle="modal"><span class="btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('contract_send_to_email'); ?>" data-placement="bottom">
                                          <i class="fa fa-envelope"></i></span>
                                    </a>
                                 <?php } ?>
                                 <div class="btn-group">
                                    <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                       <?php echo _l('more'); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                       <li>
                                          <a href="<?php echo site_url('contract/' . $contract->id . '/' . $contract->hash); ?>" target="_blank">
                                             <?php echo _l('view_contract'); ?>
                                          </a>
                                       </li>
                                       <?php hooks()->do_action('after_contract_view_as_client_link', $contract); ?>
                                       <?php if (has_permission('contracts', '', 'create')) { ?>
                                          <li>
                                             <a href="<?php echo admin_url('contracts/copy/' . $contract->id); ?>">
                                                <?php echo _l('contract_copy'); ?>
                                             </a>
                                          </li>
                                       <?php } ?>
                                       <?php if ($contract->contract_status == 'verified') { ?>
                                          <li>
                                             <a href="<?php echo admin_url('contracts/status/' . $contract->id . '/cancelled'); ?>">
                                                Mark as Cancel
                                             </a>
                                          </li>
                                       <?php } ?>
                                       <?php if (has_permission('contracts', '', 'delete')) { ?>
                                          <li>
                                             <a href="<?php echo admin_url('contracts/delete/' . $contract->id); ?>" class="_delete">
                                                <?php echo _l('delete'); ?></a>
                                          </li>
                                       <?php } ?>
                                       <?php if (has_permission('contracts', '', 'edit')) {
                                          if ($contract->payment_reminder == "0") {
                                       ?>
                                             <li>
                                                <a href="<?php echo admin_url('contracts/payment_reminder/' . $contract->id . '/1'); ?>" class="_delete">Resume Payment Reminder</a>
                                             </li>
                                          <?php } else { ?>
                                             <li>
                                                <a href="<?php echo admin_url('contracts/payment_reminder/' . $contract->id . '/0'); ?>" class="_delete">Stop Payment Reminder</a>
                                             </li>
                                          <?php } ?>
                                          <li>
                                             <a href="<?php echo admin_url('contracts/delete/' . $contract->id); ?>" class="_delete">
                                                <?php echo _l('delete'); ?></a>
                                          </li>
                                       <?php } ?>

                                    </ul>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <?php if (isset($contract_merge_fields)) { ?>
                                    <hr class="hr-panel-heading" />
                                    <p class="bold mtop10 text-right"><a href="#" onclick="slideToggle('.avilable_merge_fields'); return false;"><?php echo _l('available_merge_fields'); ?></a></p>
                                    <div class=" avilable_merge_fields mtop15 hide">
                                       <ul class="list-group">
                                          <?php
                                          foreach ($contract_merge_fields as $field) {
                                             foreach ($field as $f) {
                                                echo '<li class="list-group-item"><b>' . $f['name'] . '</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false">' . $f['key'] . '</a></li>';
                                             }
                                          }
                                          ?>
                                       </ul>
                                    </div>
                                 <?php } ?>
                              </div>
                           </div>
                           <hr class="hr-panel-heading" />
                           <strong>Agreement Content</strong>
                           <div class="editable tc-content" id="main-content" style="border:1px solid #d2d2d2;min-height:70px; border-radius:4px;">
                              <?php
                              echo $contract->content;
                              ?>
                           </div>
                           <strong>Certificate</strong>
                           <div class="other-content-editable tc-content" id="other-content" style="border:1px solid #d2d2d2;min-height:70px; border-radius:4px;">
                              <?php
                              echo $contract->other_content;
                              ?>
                           </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_notes">
                           <?php echo form_open(admin_url('contracts/add_note/' . $contract->id), array('id' => 'sales-notes', 'class' => 'contract-notes-form')); ?>
                           <?php echo render_textarea('description'); ?>
                           <div class="text-right">
                              <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('contract_add_note'); ?></button>
                           </div>
                           <?php echo form_close(); ?>
                           <hr />
                           <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                           </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_comments">
                           <div class="row contract-comments mtop15">
                              <div class="col-md-12">
                                 <div id="contract-comments"></div>
                                 <div class="clearfix"></div>
                                 <textarea name="content" id="comment" rows="4" class="form-control mtop15 contract-comment"></textarea>
                                 <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_contract_comment();"><?php echo _l('proposal_add_comment'); ?></button>
                              </div>
                           </div>
                        </div>
                        <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') == 'attachments') {
                                                               echo ' active';
                                                            } ?>" id="attachments">
                           <?php echo form_open(admin_url('contracts/add_contract_attachment/' . $contract->id), array('id' => 'contract-attachments-form', 'class' => 'dropzone')); ?>
                           <?php echo form_close(); ?>
                           <div class="text-right mtop15">
                              <button class="gpicker" data-on-pick="contractGoogleDriveSave">
                                 <i class="fa fa-google" aria-hidden="true"></i>
                                 <?php echo _l('choose_from_google_drive'); ?>
                              </button>
                              <div id="dropbox-chooser"></div>
                              <div class="clearfix"></div>
                           </div>
                           <!-- <img src="https://drive.google.com/uc?id=14mZI6xBjf-KjZzVuQe8-rjtv_wXEbDTw" /> -->

                           <div id="contract_attachments" class="mtop30">
                              <?php
                              $data = '<div class="row">';
                              foreach ($contract->attachments as $attachment) {
                                 $href_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                                 if (!empty($attachment['external'])) {
                                    $href_url = $attachment['external_link'];
                                 }
                                 $data .= '<div class="display-block contract-attachment-wrapper">';
                                 $data .= '<div class="col-md-10">';
                                 $data .= '<div class="pull-left"><i class="' . get_mime_class($attachment['filetype']) . '"></i></div>';
                                 $data .= '<a href="' . $href_url . '" target="_blank">' . $attachment['file_name'] . '</a>';
                                 $data .= ($attachment['rel_type'] != "contract") ? " <small class='text-muted'>From " . $attachment['rel_type'] . "</small>" : "";
                                 $data .= '<p class="text-muted">' . $attachment["filetype"] . '</p>';
                                 $data .= '</div>';

                                 if ($attachment['rel_type'] == "contract") {
                                    $data .= '<div class="col-md-2 text-right">';
                                    if (has_permission('attachments', '', 'delete')) {
                                       $data .= '<a href="#" class="text-danger" onclick="delete_contract_attachment(this,' . $attachment['id'] . '); return false;"><i class="fa fa fa-times"></i></a>';
                                    }
                                    $data .= '</div>';
                                 }
                                 $data .= '<div class="clearfix"></div><hr/>';
                                 $data .= '</div>';
                              }
                              $data .= '</div>';
                              echo $data;
                              ?>
                           </div>
                        </div>
                        <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') == 'payments_terms') {
                                                               echo ' active';
                                                            } ?>" id="tab_payment_terms">
                           <?php
                           $this->load->view(
                              'admin/contracts/payments_terms_table',
                              array(
                                 'payments_terms' => $payments_terms,
                                 'contract_id' => $contract->id
                              )
                           );
                           ?>

                        </div>

                        <?php if (isset($contract) && !empty($proposals)) { ?>
                           <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') == 'payments_received') {
                                                                  echo ' active';
                                                               } ?>" id="tab_payments_received">
                              <?php
                              $this->load->view(
                                 'admin/proposals/payments_table',
                                 array(
                                    'payments' => $payments_received_data,
                                    'contract_id' => $contract->id,
                                 )
                              );
                              ?>

                           </div>
                        <?php } ?>




                        <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') == 'renewals') {
                                                               echo ' active';
                                                            } ?>" id="renewals">
                           <?php if (has_permission('contracts', '', 'create') || has_permission('contracts', '', 'edit')) { ?>
                              <div class="_buttons">
                                 <a href="#" class="btn btn-default" data-toggle="modal" data-target="#renew_contract_modal">
                                    <i class="fa fa-refresh"></i> <?php echo _l('contract_renew_heading'); ?>
                                 </a>
                              </div>
                              <hr />
                           <?php } ?>
                           <div class="clearfix"></div>
                           <?php
                           if (count($contract_renewal_history) == 0) {
                              echo _l('no_contract_renewals_found');
                           }
                           foreach ($contract_renewal_history as $renewal) { ?>
                              <div class="display-block">
                                 <div class="media-body">
                                    <div class="display-block">
                                       <b>
                                          <?php
                                          echo _l('contract_renewed_by', $renewal['renewed_by']);
                                          ?>
                                       </b>
                                       <?php if ($renewal['renewed_by_staff_id'] == get_staff_user_id() || is_admin()) { ?>
                                          <a href="<?php echo admin_url('contracts/delete_renewal/' . $renewal['id'] . '/' . $renewal['contractid']); ?>" class="pull-right _delete text-danger"><i class="fa fa-remove"></i></a>
                                          <br />
                                       <?php } ?>
                                       <small class="text-muted"><?php echo _dt($renewal['date_renewed']); ?></small>
                                       <hr class="hr-10" />
                                       <span class="text-success bold" data-toggle="tooltip" title="<?php echo _l('contract_renewal_old_start_date', _d($renewal['old_start_date'])); ?>">
                                          <?php echo _l('contract_renewal_new_start_date', _d($renewal['new_start_date'])); ?>
                                       </span>
                                       <br />
                                       <?php if (is_date($renewal['new_end_date'])) {
                                          $tooltip = '';
                                          if (is_date($renewal['old_end_date'])) {
                                             $tooltip = _l('contract_renewal_old_end_date', _d($renewal['old_end_date']));
                                          }
                                       ?>
                                          <span class="text-success bold" data-toggle="tooltip" title="<?php echo $tooltip; ?>">
                                             <?php echo _l('contract_renewal_new_end_date', _d($renewal['new_end_date'])); ?>
                                          </span>
                                          <br />
                                       <?php } ?>
                                       <?php if ($renewal['new_value'] > 0) {
                                          $contract_renewal_value_tooltip = '';
                                          if ($renewal['old_value'] > 0) {
                                             $contract_renewal_value_tooltip = ' data-toggle="tooltip" data-title="' . _l('contract_renewal_old_value', app_format_money($renewal['old_value'], $base_currency)) . '"';
                                          } ?>
                                          <span class="text-success bold" <?php echo $contract_renewal_value_tooltip; ?>>
                                             <?php echo _l('contract_renewal_new_value', app_format_money($renewal['new_value'], $base_currency)); ?>
                                          </span>
                                          <br />
                                       <?php } ?>
                                    </div>
                                 </div>
                                 <hr />
                              </div>
                           <?php } ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                           <?php
                           $this->load->view(
                              'admin/includes/emails_tracking',
                              array(
                                 'tracked_emails' =>
                                 get_tracked_emails($contract->id, 'contract')
                              )
                           );
                           ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="tab_tasks">
                           <?php init_relation_tasks_table(array('data-new-rel-id' => $contract->id, 'data-new-rel-type' => 'contract')); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         <?php } ?>
      </div>
   </div>
</div>

<div class="modal fade payment-terms-modal" id="payment-terms-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">

      </div>
   </div>
</div>
<div class="modal fade payment-modal" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">

      </div>
   </div>
</div>

<?php init_tail(); ?>
<?php if (isset($contract)) { ?>
   <!-- init table tasks -->
   <script>
      var contract_id = "<?php isset($contract) ? $contract->id : ''; ?>";
   </script>
   <?php $this->load->view('admin/contracts/send_to_client'); ?>
   <?php $this->load->view('admin/contracts/send_to_client_whatsapp', array('contract' => $contract)); ?>
   <?php $this->load->view('admin/contracts/renew_contract'); ?>
   <?php $this->load->view('admin/contracts/contract_verification'); ?>
<?php } ?>
<?php $this->load->view('admin/contracts/contract_type'); ?>
<script>
   var _rel_id = $('#rel_id'),
      _rel_type = $('#rel_type'),
      _rel_id_wrapper = $('#rel_id_wrapper'),
      data = {};

   var contractStatus = "<?php echo isset($contract) ? $contract->contract_status : ''; ?>";
   var contract_id = "<?= isset($contract->id) ? $contract->id : ''; ?>";
   Dropzone.autoDiscover = false;
   $(function() {
      if ($('#contract-attachments-form').length > 0) {
         new Dropzone("#contract-attachments-form", appCreateDropzoneOptions({
            acceptedFiles: ".jpg,.jpeg,.png,.pdf",
            success: function(file) {
               if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                  var location = window.location.href;
                  window.location.href = location.split('?')[0] + '?tab=attachments';
               }
            }
         }));
      }

      $('.rel_id_label').html(_rel_type.find('option:selected').text());
      _rel_type.on('change', function() {
         var clonedSelect = _rel_id.html('').clone();
         _rel_id.selectpicker('destroy').remove();
         _rel_id = clonedSelect;
         $('#rel_id_select').append(clonedSelect);
         contract_rel_id_select();
         if ($(this).val() != '') {
            _rel_id_wrapper.removeClass('hide');
         } else {
            _rel_id_wrapper.addClass('hide');
         }

         if ($(this).val() == 'customer') {
            $('#not_visible_to_client').closest('.checkbox-inline').removeClass('hide');
         } else {
            $('#not_visible_to_client').prop("checked", false);
            $('#not_visible_to_client').closest('.checkbox-inline').addClass('hide');
         }

         $('.rel_id_label').html(_rel_type.find('option:selected').text());
      });
      contract_rel_id_select();
      <?php if (!isset($contract) && $rel_id != '') { ?>
         _rel_id.change();
         _rel_id.change();
      <?php } ?>

      function contract_rel_id_select() {
         var serverData = {};
         serverData.rel_id = _rel_id.val();
         data.type = _rel_type.val();
         init_ajax_search(_rel_type.val(), _rel_id, serverData);
      }

      // In case user expect the submit btn to save the contract content
      $('#contract-form').on('submit', function() {
         $('#inline-editor-save-btn').click();
         return true;
      });

      if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
         document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
            success: function(files) {
               $.post(admin_url + 'contracts/add_external_attachment', {
                  files: files,
                  contract_id: contract_id,
                  external: 'dropbox'
               }).done(function() {
                  var location = window.location.href;
                  window.location.href = location.split('?')[0] + '?tab=attachments';
               });
            },
            linkType: "preview",
            extensions: app.options.allowed_files.split(','),
         }));
      }

      $($('#contract-form')).appFormValidator({
         rules: {
            client: 'required',
            number: 'required',
            datestart: 'required',
            subject: 'required',
            contract_type: 'required',
            sub_type: 'required',
            // draft_id: 'required',
            rel_type: 'required',
         },
         errorPlacement: function(error, element) {
            var inputType = $(element).attr('type')
            var formGroup = $(element).closest('.form-group');
            $(formGroup).append(error);
         },
      });

      appValidateForm($('#renew-contract-form'), {
         new_start_date: 'required'
      });

      var _templates = [];
      $.each(contractsTemplates, function(i, template) {
         _templates.push({
            url: admin_url + 'contracts/get_template?name=' + template,
            title: template
         });
      });

      // main content
      var editor_settings = {
         selector: 'div.editable',
         inline: true,
         relative_urls: false,
         remove_script_host: false,
         verify_html: false,
         cleanup: false,
         apply_source_formatting: false,
         valid_elements: '+*[*]',
         valid_children: "+body[style], +style[type]",
         file_browser_callback: elFinderBrowser,
         table_default_styles: {
            width: '100%'
         },
         fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
         pagebreak_separator: '<p pagebreak="true"></p>',
         plugins: [
            'advlist pagebreak autolink autoresize lists link image charmap hr',
            'searchreplace visualblocks visualchars code',
            'media nonbreaking table contextmenu',
            'paste textcolor colorpicker'
         ],
         autoresize_bottom_margin: 50,
         insert_toolbar: 'image media quicktable | bullist numlist | h2 h3 | hr',
         selection_toolbar: 'save_button bold italic underline superscript | forecolor backcolor link | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect h2 h3',
         contextmenu: "image media inserttable | cell row column deletetable | paste pastetext searchreplace | visualblocks pagebreak charmap | code",
         setup: function(editor) {

            editor.addCommand('mceSave', function() {
               save_contract_content(true);
            });

            editor.addShortcut('Meta+S', '', 'mceSave');

            editor.on('MouseLeave blur', function() {
               if (tinymce.activeEditor.isDirty()) {
                  save_contract_content();
               }
            });

            editor.on('MouseDown ContextMenu', function() {
               if (!is_mobile() && !$('.left-column').hasClass('hide')) {
                  contract_full_view();
               }
            });

            var typingTimer;
            var doneTypingInterval = 1000;
            editor.on('input change keyup', function() {
               if (editor.id == "other_content" && is_verified) {
                  return false;
               }
               clearTimeout(typingTimer);
               typingTimer = setTimeout(save_contract_content, doneTypingInterval);
            });

            editor.on('blur', function() {
               $.Shortcuts.start();
            });

            editor.on('focus', function() {
               $.Shortcuts.stop();
            });

         }
      }

      if (_templates.length > 0) {
         editor_settings.templates = _templates;
         editor_settings.plugins[3] = 'template ' + editor_settings.plugins[3];
         editor_settings.contextmenu = editor_settings.contextmenu.replace('inserttable', 'inserttable template');
      }

      if (is_mobile()) {

         editor_settings.theme = 'modern';
         editor_settings.mobile = {};
         editor_settings.mobile.theme = 'mobile';
         editor_settings.mobile.toolbar = _tinymce_mobile_toolbar();

         editor_settings.inline = false;
         window.addEventListener("beforeunload", function(event) {
            if (tinymce.activeEditor.isDirty()) {
               save_contract_content();
            }
         });
      }

      if (contractStatus == 'verified') {
         editor_settings.readonly = true;
         editor_settings.toolbar = false;
         editor_settings.menubar = false;
      }
      tinymce.init(editor_settings);


      // other content
      var other_editor_settings = {
         selector: 'div.other-content-editable',
         inline: true,
         relative_urls: false,
         remove_script_host: false,
         verify_html: false,
         cleanup: false,
         apply_source_formatting: false,
         valid_elements: '+*[*]',
         valid_children: "+body[style], +style[type]",
         file_browser_callback: elFinderBrowser,
         table_default_styles: {
            width: '100%'
         },
         fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
         pagebreak_separator: '<p pagebreak="true"></p>',
         plugins: [
            'advlist pagebreak autolink autoresize lists link image charmap hr',
            'searchreplace visualblocks visualchars code',
            'media nonbreaking table contextmenu',
            'paste textcolor colorpicker'
         ],
         autoresize_bottom_margin: 50,
         insert_toolbar: 'image media quicktable | bullist numlist | h2 h3 | hr',
         selection_toolbar: 'save_button bold italic underline superscript | forecolor backcolor link | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect h2 h3',
         contextmenu: "image media inserttable | cell row column deletetable | paste pastetext searchreplace | visualblocks pagebreak charmap | code",
         setup: function(editor) {

            editor.addCommand('mceSave', function() {
               save_contract_content(true);
            });

            editor.addShortcut('Meta+S', '', 'mceSave');

            editor.on('MouseLeave blur', function() {
               if (tinymce.activeEditor.isDirty()) {
                  save_contract_content();
               }
            });

            editor.on('MouseDown ContextMenu', function() {
               if (!is_mobile() && !$('.left-column').hasClass('hide')) {
                  contract_full_view();
               }
            });

            var typingTimer;
            var doneTypingInterval = 1000;
            editor.on('input change keyup', function() {
               if (editor.id == "other_content" && is_verified) {
                  return false;
               }
               clearTimeout(typingTimer);
               typingTimer = setTimeout(save_contract_content, doneTypingInterval);
            });

            editor.on('blur', function() {
               $.Shortcuts.start();
            });

            editor.on('focus', function() {
               $.Shortcuts.stop();
            });

         }
      }

      if (_templates.length > 0) {
         other_editor_settings.templates = _templates;
         other_editor_settings.plugins[3] = 'template ' + other_editor_settings.plugins[3];
         other_editor_settings.contextmenu = other_editor_settings.contextmenu.replace('inserttable', 'inserttable template');
      }

      if (is_mobile()) {

         other_editor_settings.theme = 'modern';
         other_editor_settings.mobile = {};
         other_editor_settings.mobile.theme = 'mobile';
         other_editor_settings.mobile.toolbar = _tinymce_mobile_toolbar();

         other_editor_settings.inline = false;
         window.addEventListener("beforeunload", function(event) {
            if (tinymce.activeEditor.isDirty()) {
               save_contract_content();
            }
         });
      }

      tinymce.init(other_editor_settings);


      $(document).on('change', '#contract_type', function() {
         if ($(this).val() != "" && $(this).val() != null) {
            get_sub_type($(this).val());
         }
      });

      $(document).on('change', '#sub_type', function() {
         var main_type = $('#contract_type').val();
         var sub_type = $('#sub_type').val();
         if (main_type != "" && main_type != null && sub_type != "" && sub_type != null) {
            get_drafts(main_type, sub_type);
         }

      });

      $(document).on('change', '#draft_id', function() {
         if ($(this).val() != "" && $(this).val() != null) {
            get_draft($(this).val());
         }
      });

      $(document).on('click', '.changeSign', function() {
         var section = $(this);
         var type = section.attr('data-type');
         $.ajax({
            url: "<?php echo admin_url('contracts/change_default_signature') ?>",
            method: "POST",
            data: {
               default_signature: type,
               id: $(this).attr('data-contactid'),
               contract_id: contract_id,
            },
            dataType: 'json'
         }).done(function(result) {
            if (result.success) {
               $('.changeSign').find('.fa-check-circle').remove();
               $('.changeSign').find('.sign-text').removeClass('default-sign');
               $('.changeSign[data-type="' + type + '"]').find('.sign-text').addClass('default-sign');
               $('.changeSign[data-type="' + type + '"]').find('.sign-text').append('<i class="fa fa-check-circle" aria-hidden="true"></i>');
               alert_float('success', result.message);
            } else {
               alert_float('danger', result.message);
            }
         });

      });

      $(document).on('change', '#rel_id', function() {
         if ($(this).val() != "" && $(this).val() != null) {
            get_proposals($(this).val());
         }
      });

      $(document).on('change', '#proposal_id', function() {
         update_contract_value();
      });

      $('#rel_id').trigger('change');



      $(document).on('click', '.btn-payment-term-create', function() {
         get_payment_terms_modal(id = "");
      });

      $(document).on('click', '.btn-edit-payment-term', function() {
         var id = $(this).attr('data-id');
         get_payment_terms_modal(id);
      });

      $(document).on('input', '#fieldamount', function() {
         const regex = /^\d+(\.\d{0,2})?$/;
         if (!regex.test(this.value)) {
            this.value = this.value.slice(0, -1);
         }
      });

      $(document).on('input', '#fieldpercentage', function() {
         const regex = /^\d+(\.\d{0,2})?$/;
         if (!regex.test(this.value)) {
            this.value = this.value.slice(0, -1);
            return;
         }
         const value = parseFloat(this.value);
         if (value > 100) {
            this.value = '100';
         } else if (value < 0) {
            this.value = '0';
         }
      });

      $(document).on('input', '.pecentage_input, .amount_input', function() {

         var mainTotal = Number($("#payment-terms-form #total_contract_amount").text());
         var totalAmount = Number($("#payment-terms-form #total_pending_amount").text());
         var total_percentage = (totalAmount / mainTotal) * 100;


         var $element = $(this);
         var isPercentageInput = $element.hasClass('pecentage_input');
         var value = Number($element.val());
         var curPaymentPanel = $element.closest('.payment-panel');


         var lockedAmount = 0;
         var remaining = 0;
         var paymentPanels = $('.payment-panel');
         paymentPanels.each(function(index) {
            if (!$(this).is(curPaymentPanel)) {
               lockedAmount += Number($(this).find('.amount_input').val());
            }
         });
         remaining = Number(totalAmount) - Number(lockedAmount);

         // Validate the input
         if (!/^\d*\.?\d{0,2}$/.test($element.val())) {
            $element.val($element.val().slice(0, -1));
            return;
         }

         if (!isNaN(value) && totalAmount > 0) {
            var amount, percentage;
            if (isPercentageInput) {
               percentage = value;
               amount = (totalAmount * percentage) / total_percentage;
            } else {
               amount = value;
               percentage = (amount / totalAmount) * total_percentage;
            }

            // Ensure amount does not exceed total
            if (amount > totalAmount) {
               amount = totalAmount;
               percentage = (amount / totalAmount) * total_percentage;
               $element.closest('.payment-panel').find('.pecentage_input').val(percentage.toFixed(2));
               $element.closest('.payment-panel').find('.amount_input').val(totalAmount.toFixed(2));
            }

            if (isPercentageInput) {
               $element.closest('.payment-panel').find('.amount_input').val(amount.toFixed(2));
            } else {
               $element.closest('.payment-panel').find('.pecentage_input').val(percentage.toFixed(2));
            }
         }

         handleDuplicateEntries(curPaymentPanel, total_percentage, totalAmount);
      });

      function handleDuplicateEntries(curPaymentPanel, total_percentage, totalAmount) {
         var panels = curPaymentPanel.nextAll('.payment-panel');
         panels.remove();

         var curPaymentPanelPercentage = Number(curPaymentPanel.find('.pecentage_input').val()) || 0;
         var curPaymentPanelAmount = Number(curPaymentPanel.find('.amount_input').val()) || 0;

         if (curPaymentPanelPercentage === 0 || curPaymentPanelAmount === 0) {
            return;
         }

         var lockedAmount = 0;
         var actualLockedAmount = 0;
         var paymentPanels = $('.payment-panel');
         paymentPanels.each(function(index) {
            if ($(this).is(curPaymentPanel) || $(this).index() < curPaymentPanel.index()) {
               lockedAmount += Number($(this).find('.amount_input').val());
            }
            if ($(this).index() < curPaymentPanel.index()) {
               actualLockedAmount += Number($(this).find('.amount_input').val());
            }
         });

         var actualRemainingAmount = totalAmount - actualLockedAmount;
         var actualRemainingPercentage = (actualRemainingAmount / totalAmount) * total_percentage;

         var remainingAmount = totalAmount - lockedAmount;
         while (remainingAmount > 0) {
            var remainingPercentage = (remainingAmount / totalAmount) * total_percentage;
            var newId = new Date().getTime();

            var newRowHtml = `
                  <div class="panel payment-panel panel-default">
                        <div class="panel-heading">
                           <h4 class="panel-title">Payment Details</h4>
                        </div>
                        <div class="panel-body">
                           <div class="row">
                              <div class="col-md-6">
                                    <div class="form-group" data-type="text" data-name="percentage[]" data-required="1">
                                       <div class="control-label">Percentage Of Total Agreement Value <span class="text-danger">* </span></div>
                                       <input form="payment-terms-form" required="true" type="text" name="percentage[]" id="fieldpercentage_${newId}" class="form-control pecentage_input" value="${remainingPercentage.toFixed(2)}">
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group" data-type="text" data-name="amount[]" data-required="1">
                                       <div class="control-label">Payment Amount <span class="text-danger">* </span></div>
                                       <input form="payment-terms-form" required="true" type="text" name="amount[]" id="fieldamount_${newId}" class="form-control amount_input" value="${remainingAmount.toFixed(2)}">
                                    </div>
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-6">
                                    <div class="form-group" data-type="date_picker" data-name="scheduled_payment_date[]" data-required="1">
                                       <div class="control-label">Payment Due Date <span class="text-danger">* </span></div>
                                       <input form="payment-terms-form" required="true" placeholder="" type="text" class="form-control scheduled_payment_date_input render-input-disabled datepicker" name="scheduled_payment_date[]" id="fieldscheduled_payment_date_${newId}" value="" autocomplete="off">
                                    </div>
                              </div>
                              <div class="col-md-6">
                                    <div class="form-group" data-type="textarea" data-name="note[]" data-required="1">
                                       <div class="control-label">Note <span class="text-danger">* </span></div>
                                       <textarea form="payment-terms-form" required="true" id="fieldnote_${newId}" name="note[]" rows="4" class="form-control note_input" placeholder=""></textarea>
                                    </div>
                              </div>
                           </div>
                        </div>
                  </div>`;

            $(newRowHtml).insertAfter(curPaymentPanel);
            appSelectPicker();
            appDatepicker();
            updateNextDatePicker();
            curPaymentPanel = $(newRowHtml);
            lockedAmount += remainingAmount;
            remainingAmount = totalAmount - lockedAmount;
         }

         if (remainingAmount < 0) {
            curPaymentPanel.find('.pecentage_input').val(actualRemainingPercentage.toFixed(2));
            curPaymentPanel.find('.amount_input').val(actualRemainingAmount.toFixed(2));
         }
      }

      $(document).on('input keyup change', 'input[form="payment-terms-form"], textarea[form="payment-terms-form"], select[form="payment-terms-form"]', function(e) {
         $(this).closest('.form-group').find('.error').remove();
         if ($(this).prop('required')) {
            if ($(this).val() == "" || $(this).val() == null) {
               if ($(this).is('select')) {
                  $(this).closest('.form-group').append('<span class="text-danger error">This field is required</span>');
               } else {
                  $(this).after('<span class="text-danger error">This field is required</span>');
               }
            }
         }
      });

      $(document).on('change', '#payment_proposal_id', function() {
         var selected = $('#payment_proposal_id :selected')
         var val = selected.val();
         var paymentid = $('#payment-form input[name="id"]').val();
         if (val != "" && val != null) {
            $('#payment-form #total_contract_amount').text(Number(selected.attr('data-total-amount')));
            $('#payment-form #total_received_amount').text(Number(selected.attr('data-total-received-amount')));
            $('#payment-form #total_pending_amount').text(Number(selected.attr('data-remaining-amount')));
            $('#payment-form input[name="invoiceid"]').val(selected.attr('data-invoice-id'));
            if (Number(selected.attr('data-remaining-amount')) > 0 && (paymentid == "" || paymentid == null)) {
               $('#payment-form input[name="amount"]').val(Number(selected.attr('data-remaining-amount')));
            }
         } else {
            $('#payment-form #total_contract_amount').text(0);
            $('#payment-form #total_received_amount').text(0);
            $('#payment-form #total_pending_amount').text(0);
            $('#payment-form input[name="invoiceid"]').val("");
            $('#payment-form input[name="amount"]').val("");
         }
      });

      $(document).on('click', '.btn-payment-create', function() {
         get_payment_modal();
      });

      $(document).on('click', '.btn-edit-payment', function() {
         var id = $(this).attr('data-id');
         get_payment_modal(id);
      });

      $(document).on('keydown', '#payment-form input[name="date"]', function(e) {
         return false;
      });

      $(document).on('click', '#payment-form button[type="submit"]', function(e) {
         e.preventDefault();
         var formId = 'payment-form';
         var isValid = true;
         $('#payment-form input, #payment-form textarea, #payment-form select').each(function(index, item) {
            $(this).closest('.form-group').find('.error').remove();
            if ($(this).prop('required')) {
               if (this.value === "" || this.value === null) {
                  if ($(this).is('select')) {
                     $(this).closest('.form-group').append('<span class="text-danger error">This field is required</span>');
                  } else {
                     $(this).after('<span class="text-danger error">This field is required</span>');
                  }
                  isValid = false;
               }
            }
         });
         if (isValid) {
            var $btn = $('#payment-form button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
            $btn.closest('form').submit();
         }
      });

      $(document).on('click', '#payment-terms-form button[type="submit"]', function(e) {
         e.preventDefault();
         var formId = 'payment-terms-form';
         var isValid = true;
         $('#payment-terms-form input, #payment-terms-form textarea, #payment-terms-form select').each(function(index, item) {
            $(this).closest('.form-group').find('.error').remove();
            if ($(this).prop('required')) {
               if (this.value === "" || this.value === null) {
                  if ($(this).is('select')) {
                     $(this).closest('.form-group').append('<span class="text-danger error">This field is required</span>');
                  } else {
                     $(this).after('<span class="text-danger error">This field is required</span>');
                  }
                  isValid = false;
               }
            }
         });
         if (isValid) {
            var $btn = $('#payment-terms-form button[type="submit"]');
            $btn.prop('disabled', true);
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
            $btn.closest('form').submit();
         }
      });

      $(document).on('input', '#payment-form input[name="amount"]', function() {
         $(this).siblings(".text-danger").remove();
         var totalPendingAmount = Number($('#payment-form #total_pending_amount').text()) || 0;
         var enteredAmount = $(this).val();
         var validAmount = enteredAmount.match(/^\d*\.?\d{0,2}$/);
         if (!validAmount) {
            $(this).val(enteredAmount.slice(0, enteredAmount.length - 1));
            return;
         }
         enteredAmount = Number(enteredAmount);
         if (enteredAmount == 0) {
            $(this).val("");
            return;
         }
         if (enteredAmount > totalPendingAmount) {
            $('#payment-form button[type="submit"]').prop('disabled', true);
            $(this).after("<span id='amount-error' class='text-danger'>You can't enter more than the remaining amount</span>");
         } else {
            $('#payment-form button[type="submit"]').prop('disabled', false);
         }
      });

      $(document).on('change', '.scheduled_payment_date_input', function() {
         updateNextDatePicker();
      });

      $('input[name="number"]').on('focusout', function() {
         check_contract_number();
      })

   });

   function updateNextDatePicker() {
      $(document).find('.scheduled_payment_date_input').each(function() {
         var selectedDate = $(this).datetimepicker('getValue');
         if (selectedDate) {
            var nextDay = new Date(selectedDate);
            nextDay.setDate(nextDay.getDate() + 1);
            var nextDatePicker = $(this).closest('.panel').next('.panel').find('.datepicker');
            if (nextDatePicker.length) {
               nextDatePicker.datetimepicker({
                  timepicker: false,
                  format: 'd-m-Y',
                  onShow: function() {
                     var nextDateValue = nextDatePicker.datetimepicker('getValue');
                     if (nextDateValue && nextDateValue <= selectedDate) {
                        nextDatePicker.val('');
                     }
                     nextDatePicker.datetimepicker('setOptions', {
                        minDate: nextDay
                     });
                  }
               });
               var nextDateValue = nextDatePicker.datetimepicker('getValue');
               if (nextDateValue && nextDateValue <= selectedDate) {
                  nextDatePicker.val('');
               }
            }
         }
      });
   }

   function get_payment_modal(id = "") {
      $('#payment-modal .modal-content').html("");
      $.ajax({
         url: "<?php echo admin_url('proposals/get_payment_modal') ?>",
         method: "POST",
         data: {
            id: id,
            contract_id: "<?= $contract->id ?>",
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            $('#payment-modal').modal('show');
            $('#payment-modal .modal-content').html(result.html);
            appSelectPicker();
            appDatepicker();
            $(function() {
               appValidateForm($('#payment-form'), {
                  amount: 'required',
                  date: 'required'
               });
            });
            if ($('#payment_proposal_id').length > 0) {
               $('#payment_proposal_id').trigger('change');
            }
         } else {
            alert_float('danger', "Something went wrong");
         }
      });
   }

   function get_payment_terms_modal(id = "") {
      $('#payment-terms-modal .modal-content').html("");
      $.ajax({
         url: "<?php echo admin_url('contracts/get_payment_terms_modal') ?>",
         method: "POST",
         data: {
            id: id,
            contract_id: "<?= $contract->id ?>",
            contract_value: $("input[name='contract_value']").val(),
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            $('#payment-terms-modal').modal('show');
            $('#payment-terms-modal .modal-content').html(result.html);
            appSelectPicker();
            appDatepicker();
            $('.render-input-disabled').on('keydown', false);
            setTimeout(() => {
               $(document).find('#fieldstatus').trigger('change');
            }, 200);
         } else {
            alert_float('danger', "Something went wrong");
         }
      });
   }

   function get_proposals(customer_id) {
      var selected_ids = <?= json_encode(get_contract_linked_proposals($contract->id)) ?>;
      $('#proposal_id').empty();
      $('#proposal_id').selectpicker("refresh");
      $.ajax({
         url: "<?php echo admin_url('contracts/get_proposal_list') ?>",
         method: "POST",
         data: {
            customer_id: customer_id,
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            $('#proposal_id').append('<option value="" ' + ((selected_ids.length == 0) ? 'selected' : '') + '></option>');
            $(result.proposals).each(function(index, item) {
               var isSelected = selected_ids.includes(item.id.toString());
               $('#proposal_id').append('<option data-total="' + item.total + '" value="' + item.id + '"' + (isSelected ? ' selected' : '') + '>' + item.proposal_formatted_id + '</option>');
            });
            $('#proposal_id').selectpicker('refresh');
            update_contract_value();
         } else {
            alert_float('danger', result.message);
         }
      });
   }

   function update_contract_value() {
      contract_value = 0;
      $('select[id="proposal_id"] option:selected').each(function() {
         var dataTotal = $(this).attr('data-total');
         if (dataTotal) {
            contract_value += Number(dataTotal);
         }
      });
      if (contract_value !== 0) {
         $('input[name="contract_value"]').val(contract_value.toFixed(2));
         $('input[name="contract_value"]').attr('value', contract_value.toFixed(2));
         $('input[name="contract_value"]').attr('readonly', true);
      } else {
         $('input[name="contract_value"]').attr('readonly', false);
      }

   }

   function get_sub_type(main_type) {
      var selected_id = "<?= isset($contract->sub_type) ? $contract->sub_type : '' ?>";
      $('#sub_type').empty();
      $('#sub_type').val("").selectpicker("refresh");
      $.ajax({
         url: "<?php echo admin_url('contracts/get_sub_types_list') ?>",
         method: "POST",
         data: {
            main_id: main_type,
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            $('#sub_type').append('<option value="" selected></option>');
            $(result.sub_types).each(function(index, item) {
               if (selected_id != "" && item.id == selected_id) {
                  $('#sub_type').append('<option value="' + item.id + '" selected>' + item.name + '</option>');
               } else {
                  $('#sub_type').append('<option value="' + item.id + '" >' + item.name + '</option>');
               }
            });
            $('#sub_type').selectpicker('refresh');
         } else {
            alert_float('danger', result.message);
         }
      });
   }

   function get_drafts(main_type, sub_type) {
      if (main_type != "" && sub_type != "") {
         var selected_id = "<?= isset($contract->draft_id) ? $contract->draft_id : '' ?>";
         $('#draft_id').empty();
         $('#draft_id').val("").selectpicker("refresh");
         $.ajax({
            url: "<?php echo admin_url('contracts/get_drafts_list') ?>",
            method: "POST",
            data: {
               main_id: main_type,
               sub_id: sub_type,
            },
            dataType: 'json'
         }).done(function(result) {
            if (result.success) {
               $('#draft_id').append('<option value="" selected></option>');
               $(result.sub_types).each(function(index, item) {
                  if (selected_id != "" && item.id == selected_id) {
                     $('#draft_id').append('<option value="' + item.id + '" selected>' + item.draft_title + '</option>');
                  } else {
                     $('#draft_id').append('<option value="' + item.id + '" >' + item.draft_title + '</option>');
                  }
               });
               $('#draft_id').selectpicker('refresh');
            } else {
               alert_float('danger', result.message);
            }
         });
      }
   }

   function get_draft(id) {
      $.ajax({
         url: "<?php echo admin_url('contracts/get_draft') ?>",
         method: "POST",
         data: {
            id: id,
         },
         dataType: 'json'
      }).done(function(result) {
         if (result.success) {
            if (result.success) {
               console.log(result)
            }

         } else {
            alert_float('danger', result.message);
         }
      });
   }

   function save_contract_content(manual) {
      var editor = tinyMCE.activeEditor;
      var data = {};
      data.contract_id = contract_id;
      data.content = tinymce.get('main-content').getContent();
      data.other_content = tinymce.get('other-content').getContent();
      if ($('.verify-btn').length > 0 && data.other_content.length > 0) {
         $('.verify-btn').removeClass('hide');
      } else {
         $('.verify-btn').addClass('hide');
      }
      $.post(admin_url + 'contracts/save_contract_data', data).done(function(response) {
         response = JSON.parse(response);
         if (typeof(manual) != 'undefined') {
            alert_float('success', response.message);
         }
         editor.save();
      }).fail(function(error) {
         var response = JSON.parse(error.responseText);
         alert_float('danger', response.message);
      });
   }

   function delete_contract_attachment(wrapper, id) {
      if (confirm_delete()) {
         $.get(admin_url + 'contracts/delete_contract_attachment/' + id, function(response) {
            if (response.success == true) {
               $(wrapper).parents('.contract-attachment-wrapper').remove();

               var totalAttachmentsIndicator = $('.attachments-indicator');
               var totalAttachments = totalAttachmentsIndicator.text().trim();
               if (totalAttachments == 1) {
                  totalAttachmentsIndicator.remove();
               } else {
                  totalAttachmentsIndicator.text(totalAttachments - 1);
               }
            } else {
               alert_float('danger', response.message);
            }
         }, 'json');
      }
      return false;
   }

   function insert_merge_field(field) {
      var key = $(field).text();
      tinymce.activeEditor.execCommand('mceInsertContent', false, key);
   }

   function contract_full_view() {
      $('.left-column').toggleClass('hide');
      $('.right-column').toggleClass('col-md-7');
      $('.right-column').toggleClass('col-md-12');
      $(window).trigger('resize');
   }

   function add_contract_comment() {
      var comment = $('#comment').val();
      if (comment == '') {
         return;
      }
      var data = {};
      data.content = comment;
      data.contract_id = contract_id;
      $('body').append('<div class="dt-loader"></div>');
      $.post(admin_url + 'contracts/add_comment', data).done(function(response) {
         response = JSON.parse(response);
         $('body').find('.dt-loader').remove();
         if (response.success == true) {
            $('#comment').val('');
            get_contract_comments();
         }
      });
   }

   function get_contract_comments() {
      if (typeof(contract_id) == 'undefined') {
         return;
      }
      requestGet('contracts/get_comments/' + contract_id).done(function(response) {
         $('#contract-comments').html(response);
         var totalComments = $('[data-commentid]').length;
         var commentsIndicator = $('.comments-indicator');
         if (totalComments == 0) {
            commentsIndicator.addClass('hide');
         } else {
            commentsIndicator.removeClass('hide');
            commentsIndicator.text(totalComments);
         }
      });
   }

   function remove_contract_comment(commentid) {
      if (confirm_delete()) {
         requestGetJSON('contracts/remove_comment/' + commentid).done(function(response) {
            if (response.success == true) {

               var totalComments = $('[data-commentid]').length;

               $('[data-commentid="' + commentid + '"]').remove();

               var commentsIndicator = $('.comments-indicator');
               if (totalComments - 1 == 0) {
                  commentsIndicator.addClass('hide');
               } else {
                  commentsIndicator.removeClass('hide');
                  commentsIndicator.text(totalComments - 1);
               }
            }
         });
      }
   }

   function edit_contract_comment(id) {
      var content = $('body').find('[data-contract-comment-edit-textarea="' + id + '"] textarea').val();
      if (content != '') {
         $.post(admin_url + 'contracts/edit_comment/' + id, {
            content: content
         }).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
               alert_float('success', response.message);
               $('body').find('[data-contract-comment="' + id + '"]').html(nl2br(content));
            }
         });
         toggle_contract_comment_edit(id);
      }
   }

   function toggle_contract_comment_edit(id) {
      $('body').find('[data-contract-comment="' + id + '"]').toggleClass('hide');
      $('body').find('[data-contract-comment-edit-textarea="' + id + '"]').toggleClass('hide');
   }

   function contractGoogleDriveSave(pickData) {
      var data = {};
      data.contract_id = contract_id;
      data.external = 'gdrive';
      data.files = pickData;
      $.post(admin_url + 'contracts/add_external_attachment', data).done(function() {
         var location = window.location.href;
         window.location.href = location.split('?')[0] + '?tab=attachments';
      });
   }

   function check_contract_number() {
      $('#number').closest('.form-group').find('.text-danger').remove();
      $.ajax({
         url: "<?= admin_url("misc/check_invoice_number") ?>",
         type: 'POST',
         data: {
            type: 'contract',
            number: $('#number').val(),
            prefix: $('#prefix').text(),
            id: "<?= isset($proposal) ? $proposal->id : "" ?>"
         },
         dataType: 'json',
         success: function(response) {
            if (!response.success) {
               $('#number').closest('.form-group').append('<span class="text-danger">' + response.message + '</span>');
            }
         }
      });
   }
</script>
<style>
   .default-sign {
      color: #659c18;
      font-weight: 500;
   }

   .changeSign {
      cursor: pointer;
   }

   .sign-panel-title {
      color: #fff;
   }

   .sign-panel-title a:hover,
   .sign-panel-title a:focus {
      color: #fff !important;
   }

   .sign-user {
      font-size: 18px;
   }

   .contract-status {
      position: absolute;
      right: 3%;
      top: 4%;
   }
</style>
</body>

</html>