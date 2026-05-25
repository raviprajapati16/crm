<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content accounting-template proposal">
        <div class="row">
            <?php
      if (isset($proposal)) {
        echo form_hidden('isedit', $proposal->id);
      }
      $rel_type = '';
      $rel_id = '';
      if (isset($proposal) || ($this->input->get('rel_id') && $this->input->get('rel_type'))) {
        if ($this->input->get('rel_id')) {
          $rel_id = $this->input->get('rel_id');
          $rel_type = $this->input->get('rel_type');
        } else {
          $rel_id = $proposal->rel_id;
          $rel_type = $proposal->rel_type;
        }
      }
      ?>
            <?php echo form_open($this->uri->uri_string(), array('id' => 'proposal-form', 'class' => '_transaction_form proposal-form')); ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <?php if (isset($proposal)) { ?>
                            <div class="col-md-12">
                                <?php echo format_proposal_status($proposal->status); ?>
                                <?php $data['proposal_status'] = $proposal->status; ?>
                            </div>
                            <div class="clearfix"></div>
                            <hr />
                            <?php } ?>
                            <div class="col-md-6 border-right">
                                <?php
                                 // ── Load branch rows from unified branch_rows ──────────────────────
                                 $branches_json = get_option('branch_rows');
                                 $all_branches  = $branches_json ? json_decode($branches_json, true) : [];
                                 if (!is_array($all_branches)) {
                                     $all_branches = [];
                                 }

                                 // On edit, identify which branch matches the proposal's prefix and gst_number
                                 $matched_branch_id = null;
                                 if (isset($proposal)) {
                                     foreach ($all_branches as $br) {
                                         $resolved_pref = replace_dynamic_prefix($br['proposal_prefix'] ?? '');
                                         $br_gst = trim($br['gst_number'] ?? '');
                                         $prop_gst = trim($proposal->proposal_gst_number ?? '');
                                         if ($resolved_pref === $proposal->proposal_number_prefix && $br_gst === $prop_gst) {
                                             $matched_branch_id = $br['id'] ?? null;
                                             break;
                                         }
                                     }
                                     // Fallback
                                     if (empty($matched_branch_id)) {
                                         foreach ($all_branches as $br) {
                                             $resolved_pref = replace_dynamic_prefix($br['proposal_prefix'] ?? '');
                                             if ($resolved_pref === $proposal->proposal_number_prefix) {
                                                 $matched_branch_id = $br['id'] ?? null;
                                                 break;
                                             }
                                         }
                                     }
                                 }

                                 // Filter active branches, or the currently matched one if editing
                                 $proposal_branches = [];
                                 foreach ($all_branches as $br) {
                                     $is_deleted = !empty($br['deleted']);
                                     $is_matched = (isset($br['id']) && $matched_branch_id !== null && $br['id'] === $matched_branch_id);
                                     if (!$is_deleted || $is_matched) {
                                         $proposal_branches[] = $br;
                                     }
                                 }

                                 if (empty($proposal_branches)) {
                                     $proposal_branches = [[
                                         'id'              => '',
                                         'branch_name'     => 'Default',
                                         'proposal_prefix' => get_option('proposal_number_prefix') ?: 'PROP-',
                                         'gst_number'      => '',
                                     ]];
                                 }

                                 // Resolve dynamic variables for each branch prefix (for matching on edit)
                                 $proposal_branches_resolved = [];
                                 foreach ($proposal_branches as $br) {
                                     $br['resolved_prefix'] = replace_dynamic_prefix($br['proposal_prefix'] ?? '');
                                     $proposal_branches_resolved[] = $br;
                                 }

                                 // On edit: find which branch index in our resolved list is currently selected
                                 $selected_branch_index = 0;
                                 if (isset($proposal)) {
                                     foreach ($proposal_branches_resolved as $bidx => $br) {
                                         $br_gst = trim($br['gst_number'] ?? '');
                                         $prop_gst = trim($proposal->proposal_gst_number ?? '');
                                         if ($br['resolved_prefix'] === $proposal->proposal_number_prefix && $br_gst === $prop_gst) {
                                             $selected_branch_index = $bidx;
                                             break;
                                         }
                                     }
                                     // Fallback
                                     if ($selected_branch_index === 0) {
                                         foreach ($proposal_branches_resolved as $bidx => $br) {
                                             if ($br['resolved_prefix'] === $proposal->proposal_number_prefix) {
                                                 $selected_branch_index = $bidx;
                                                 break;
                                             }
                                         }
                                     }
                                 }
                                 $selected_branch = $proposal_branches_resolved[$selected_branch_index];
                                 ?>

                                 <!-- Proposal Branch / GST Dropdown -->
                                 <div class="form-group">
                                     <label for="proposal_branch_gst_select">Proposal Branch / GST</label>
                                     <select id="proposal_branch_gst_select" name="proposal_branch_gst_select" class="form-control" onchange="applyProposalBranchGst(this.value)">
                                         <?php foreach ($proposal_branches_resolved as $bidx => $br): ?>
                                         <option value="<?= $bidx ?>"
                                             data-prefix="<?= htmlspecialchars($br['resolved_prefix']) ?>"
                                             data-gst="<?= htmlspecialchars($br['gst_number'] ?? '') ?>"
                                             data-raw-prefix="<?= htmlspecialchars($br['proposal_prefix'] ?? '') ?>"
                                             <?= ($bidx === $selected_branch_index) ? 'selected' : '' ?>>
                                             <?= htmlspecialchars($br['branch_name']) ?>
                                             <?php if (!empty($br['gst_number'])): ?>
                                                 (<?= htmlspecialchars($br['gst_number']) ?>)
                                             <?php endif; ?>
                                         </option>
                                         <?php endforeach; ?>
                                     </select>
                                     <!-- Hidden: store selected GST number for form submission -->
                                     <input type="hidden" id="selected_proposal_gst_number" name="selected_proposal_gst_number"
                                            value="<?= htmlspecialchars($selected_branch['gst_number'] ?? '') ?>">
                                     <input type="hidden" id="selected_proposal_branch_prefix_raw" name="selected_proposal_branch_prefix_raw"
                                            value="<?= htmlspecialchars($selected_branch['proposal_prefix'] ?? '') ?>">
                                 </div>

                                <div class="form-group">
                                    <label for="proposal_number">Proposal Number</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"
                                            id="proposal_prefix"><?= htmlspecialchars($selected_branch['resolved_prefix']) ?></span>
                                        <input type="number" id="proposal_number" name="proposal_number"
                                            class="form-control"
                                            value="<?= (isset($proposal) && $proposal->proposal_number) ? $proposal->proposal_number : get_next_number("proposal", $selected_branch['resolved_prefix'], $selected_branch['gst_number'] ?? '') ?>">
                                    </div>
                                </div>
                                <?php $value = (isset($proposal) ? $proposal->subject : ''); ?>
                                <?php $attrs = (isset($proposal) ? array() : array('autofocus' => true)); ?>
                                <?php echo render_input('subject', 'proposal_subject', $value, 'text', $attrs); ?>
                                <div class="form-group select-placeholder">
                                    <label for="rel_type"
                                        class="control-label"><?php echo _l('proposal_related'); ?></label>
                                    <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="lead" <?php if ((isset($proposal) && $proposal->rel_type == 'lead') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'lead') {
                                              echo 'selected';
                                            }
                                          } ?>><?php echo _l('proposal_for_lead'); ?></option>
                                        <option value="customer" <?php if ((isset($proposal) &&  $proposal->rel_type == 'customer') || $this->input->get('rel_type')) {
                                                if ($rel_type == 'customer') {
                                                  echo 'selected';
                                                }
                                              } ?>><?php echo _l('proposal_for_customer'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group select-placeholder<?php if ($rel_id == '') {
                                                            echo ' hide';
                                                          } ?> " id="rel_id_wrapper">
                                    <label for="rel_id"><span class="rel_id_label"></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%"
                                            data-live-search="true"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <?php if ($rel_id != '' && $rel_type != '') {
                        $rel_data = get_relation_data($rel_type, $rel_id);
                        $rel_val = get_relation_values($rel_data, $rel_type);
                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                      } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? _d($proposal->date) : _d(date('Y-m-d'))) ?>
                                        <?php echo render_date_input('date', 'proposal_date', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                    $value = '';
                    if (isset($proposal)) {
                      $value = _d($proposal->open_till);
                    } else {
                      if (get_option('proposal_due_after') != 0) {
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('proposal_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                      }
                    }
                    echo render_date_input('open_till', 'proposal_open_till', $value); ?>
                                    </div>
                                </div>
                                <?php
                $selected = '';
                $currency_attr = array('data-show-subtext' => true);
                foreach ($currencies as $currency) {
                  if ($currency['isdefault'] == 1) {
                    $currency_attr['data-base'] = $currency['id'];
                  }
                  if (isset($proposal)) {
                    if ($currency['id'] == $proposal->currency) {
                      $selected = $currency['id'];
                    }
                    if ($proposal->rel_type == 'customer') {
                      $currency_attr['disabled'] = true;
                    }
                  } else {
                    if ($rel_type == 'customer') {
                      $customer_currency = $this->clients_model->get_customer_default_currency($rel_id);
                      if ($customer_currency != 0) {
                        $selected = $customer_currency;
                      } else {
                        if ($currency['isdefault'] == 1) {
                          $selected = $currency['id'];
                        }
                      }
                      $currency_attr['disabled'] = true;
                    } else {
                      if ($currency['isdefault'] == 1) {
                        $selected = $currency['id'];
                      }
                    }
                  }
                }
                $currency_attr = apply_filters_deprecated('proposal_currency_disabled', [$currency_attr], '2.3.0', 'proposal_currency_attributes');
                $currency_attr = hooks()->apply_filters('proposal_currency_attributes', $currency_attr);
                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                    echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'proposal_currency', $selected, $currency_attr);
                    ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="discount_type"
                                                class="control-label"><?php echo _l('discount_type'); ?></label>
                                            <select name="discount_type" class="selectpicker" data-width="100%"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <option value="" selected><?php echo _l('no_discount'); ?></option>
                                                <option value="before_tax" <?php
                                                    if (isset($estimate)) {
                                                      if ($estimate->discount_type == 'before_tax') {
                                                        echo 'selected';
                                                      }
                                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                                <option value="after_tax" <?php if (isset($estimate)) {
                                                    if ($estimate->discount_type == 'after_tax') {
                                                      echo 'selected';
                                                    }
                                                  } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?php $fc_rel_id = (isset($proposal) ? $proposal->id : false); ?>
                                <?php echo render_custom_fields('proposal', $fc_rel_id); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group no-mbot">
                                            <label for="tags" class="control-label"><i class="fa fa-tag"
                                                    aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                                            <input type="text" class="tagsinput" id="tags" name="tags"
                                                value="<?php echo (isset($proposal) ? prep_tags_input(get_tags_in($proposal->id, 'proposal')) : ''); ?>"
                                                data-role="tagsinput">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mtop10 no-mbot">
                                            <p><?php echo _l('proposal_allow_comments'); ?></p>
                                            <div class="onoffswitch">
                                                <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if ((isset($proposal) && $proposal->allow_comments == 1) || !isset($proposal)) {
                                                                                                  echo 'checked';
                                                                                                }; ?> value="on"
                                                    name="allow_comments">
                                                <label class="onoffswitch-label" for="allow_comments"
                                                    data-toggle="tooltip"
                                                    title="<?php echo _l('proposal_allow_comments_help'); ?>"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->loading_place : ''); ?>
                                        <?php echo render_input('loading_place', 'Place Of Loading', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->discharge_place : ''); ?>
                                        <?php echo render_input('discharge_place', 'Place Of Discharge', $value); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->payment_term : ''); ?>
                                        <?php echo render_input('payment_term', 'Payment Term', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->shipment_term : ''); ?>
                                        <?php echo render_input('shipment_term', 'Shipment Term', $value); ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->notes : ''); ?>
                                        <?php echo render_textarea('notes', 'Notes', $value); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="status"
                                                class="control-label"><?php echo _l('proposal_status'); ?></label>
                                            <!-- <?php
                      // $disabled = '';
                      // if (isset($proposal)) {
                        // if ($proposal->estimate_id != NULL || $proposal->invoice_id != NULL) {
                          // $disabled = 'disabled';
                        // }
                      // }
                      // ?> -->
                                            <select name="status" class="selectpicker" data-width="100%"
                                                <?php echo $disabled; ?>
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <?php foreach ($statuses as $status) { ?>
                                                <option value="<?php echo $status; ?>" <?php if ((isset($proposal) && $proposal->status == $status) || (!isset($proposal) && $status == 0)) {
                                                                    echo 'selected';
                                                                  } ?>>
                                                    <?php echo format_proposal_status($status, '', false); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                    $i = 0;
                    $selected = '';
                    foreach ($staff as $member) {
                      if (isset($proposal)) {
                        if ($proposal->assigned == $member['staffid']) {
                          $selected = $member['staffid'];
                        }
                      }
                      $i++;
                    }
                    echo render_select('assigned', $staff, array('staffid', array('firstname', 'lastname')), 'proposal_assigned', $selected);
                    ?>
                                    </div>
                                </div>
                                <?php $value = (isset($proposal) ? $proposal->proposal_to : ''); ?>
                                <?php echo render_input('proposal_to', 'proposal_to', $value); ?>
                                <?php $value = (isset($proposal) ? $proposal->address : ''); ?>
                                <?php echo render_textarea('address', 'proposal_address', $value); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->city : ''); ?>
                                        <?php echo render_input('city', 'billing_city', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->state : ''); ?>
                                        <?php echo render_input('state', 'billing_state', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $countries = get_all_countries(); ?>
                                        <?php $selected = (isset($proposal) ? $proposal->country : ''); ?>
                                        <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $selected); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->zip : ''); ?>
                                        <?php echo render_input('zip', 'billing_zip', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->email : ''); ?>
                                        <?php echo render_input('email', 'proposal_email', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->phone : ''); ?>
                                        <?php echo render_input('phone', 'proposal_phone', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?php
                      $rel_data = get_relation_data($rel_type, $rel_id);
                      $value = ($rel_type == "lead") ? $rel_data->gst_in : $rel_data->vat;
                      ?>
                                            <label for="gst_number" class="control-label">GST Number</label>
                                            <input type="text" id="gst_number" class="form-control"
                                                value="<?= $value ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Allow To Download PDF</label>
                                        <div class="form-group">
                                            <div class="radio-section">
                                                <label class="radio-inline" for="radio_0">
                                                    <input type="radio" id="radio_0" value="0" name="download_request"
                                                        <?= (isset($proposal->download_request) && $proposal->download_request == "0") ? 'checked' : '' ?>
                                                        >
                                                    Not Allowed
                                                </label>
                                                <label class="radio-inline" for="radio_1">
                                                    <input type="radio" id="radio_1" value="1" name="download_request"
                                                        <?= (isset($proposal->download_request) && $proposal->download_request == "1") ? 'checked' : '' ?>
                                                        <?= (!isset($proposal->download_request)) ? 'checked' : '' ?>>
                                                    Allowed
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Type</label>
                                        <div class="form-group">
                                            <div class="radio-section">
                                                <label class="radio-inline" for="type_0">
                                                    <input type="radio" id="type_0" value="0" name="type"
                                                        <?= (isset($proposal->type) && $proposal->type == "0") ? 'checked' : '' ?>
                                                        <?= (!isset($proposal->type)) ? 'checked' : '' ?>> Domestic
                                                </label>
                                                <label class="radio-inline" for="type_1">
                                                    <input type="radio" id="type_1" value="1" name="type"
                                                        <?= (isset($proposal->type) && $proposal->type == "1") ? 'checked' : '' ?>>
                                                    International
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h4>Bank Details</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_ac_name : ''); ?>
                                                <?php echo render_input('bank_ac_name', 'Account Name', $value); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_ac_no : ''); ?>
                                                <?php echo render_input('bank_ac_no', 'Account Number', $value, 'number'); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_name : ''); ?>
                                                <?php echo render_input('bank_name', 'Bank Name', $value); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_ifsc_code : ''); ?>
                                                <?php echo render_input('bank_ifsc_code', 'IFSC Code', $value); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_swift_code : ''); ?>
                                                <?php echo render_input('bank_swift_code', 'Swift Code', $value); ?>
                                            </div>
                                            <div class="col-md-4">
                                                <?php $value = (isset($proposal) ? $proposal->bank_address : ''); ?>
                                                <?php echo render_textarea('bank_address', 'Bank Address', $value); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar bottom-transaction text-right">
                            <p class="no-mbot pull-left mtop5 btn-toolbar-notice">
                                <?php echo _l('include_proposal_items_merge_field_help', '<b>{proposal_items}</b>'); ?>
                            </p>
                            <button class="btn btn-info mleft5 proposal-form-submit transaction-submit" type="button">
                                <?php echo _l('submit'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <?php
          $data['dynamic_amount_fields'] = get_dynamic_amount_fields('proposal', $proposal->id)
          ?>
                    <?php $this->load->view('admin/invoices/_add_edit_items', $data); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
var _rel_id = $('#rel_id'),
    _rel_type = $('#rel_type'),
    _rel_id_wrapper = $('#rel_id_wrapper'),
    data = {};

$(function() {
    init_currency();
    // Maybe items ajax search
    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
    validate_proposal_form();
    $('body').on('change', '#rel_id', function() {
        if ($(this).val() != '') {
            $.get(admin_url + 'proposals/get_relation_data_values/' + $(this).val() + '/' + _rel_type
                .val(),
                function(response) {
                    $('input[name="proposal_to"]').val(response.to);
                    $('textarea[name="address"]').val(response.address);
                    $('input[name="email"]').val(response.email);
                    $('input[name="phone"]').val(response.phone);
                    $('input[name="city"]').val(response.city);
                    $('input[name="state"]').val(response.state);
                    $('input[name="zip"]').val(response.zip);
                    $('#gst_number').val(response.gst_in);
                    $('select[name="country"]').selectpicker('val', response.country);
                    var currency_selector = $('#currency');
                    if (_rel_type.val() == 'customer') {
                        if (typeof(currency_selector.attr('multi-currency')) == 'undefined') {
                            currency_selector.attr('disabled', true);
                        }

                    } else {
                        currency_selector.attr('disabled', false);
                    }
                    var proposal_to_wrapper = $('[app-field-wrapper="proposal_to"]');
                    if (response.is_using_company == false && !empty(response.company)) {
                        proposal_to_wrapper.find('#use_company_name').remove();
                        proposal_to_wrapper.find('#use_company_help').remove();
                        proposal_to_wrapper.append('<div id="use_company_help" class="hide">' +
                            response.company + '</div>');
                        proposal_to_wrapper.find('label')
                            .prepend(
                                "<a href=\"#\" id=\"use_company_name\" data-toggle=\"tooltip\" data-title=\"<?php echo _l('use_company_name_instead'); ?>\" onclick='document.getElementById(\"proposal_to\").value = document.getElementById(\"use_company_help\").innerHTML.trim(); this.remove();'><i class=\"fa fa-building-o\"></i></a> "
                                );
                    } else {
                        proposal_to_wrapper.find('label #use_company_name').remove();
                        proposal_to_wrapper.find('label #use_company_help').remove();
                    }
                    /* Check if customer default currency is passed */
                    if (response.currency) {
                        currency_selector.selectpicker('val', response.currency);
                    } else {
                        /* Revert back to base currency */
                        currency_selector.selectpicker('val', currency_selector.data('base'));
                    }
                    currency_selector.selectpicker('refresh');
                    currency_selector.change();
                }, 'json');
        }
    });
    $('.rel_id_label').html(_rel_type.find('option:selected').text());
    _rel_type.on('change', function() {
        var clonedSelect = _rel_id.html('').clone();
        _rel_id.selectpicker('destroy').remove();
        _rel_id = clonedSelect;
        $('#rel_id_select').append(clonedSelect);
        proposal_rel_id_select();
        if ($(this).val() != '') {
            _rel_id_wrapper.removeClass('hide');
        } else {
            _rel_id_wrapper.addClass('hide');
        }
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
    });
    proposal_rel_id_select();
    <?php if (!isset($proposal) && $rel_id != '') { ?>
    _rel_id.change();
    <?php } ?>

    $('input[type=radio][name="type"]').on('change', function() {
        if ($('input[type=radio][name="type"]:checked').val() == '1') {
            $('select[name="tax_id"] option:first').prop('selected', true);
            $('select[name="tax_id"]').selectpicker('refresh');
            $('input[name="total_tax"]').val('');
            $('.tax-amount-tr,.taxable-amount-tr').addClass('hide');
        } else {
            $('.tax-amount-tr,.taxable-amount-tr').removeClass('hide');
        }
        change_type();
        calculate_total();
    })

    <?php if (!isset($proposal)) { ?>
    $('input[type=radio][name=type][value="0"]').trigger('change');
    <?php } ?>


    $('input[name="proposal_number"]').on('focusout', function() {
        check_proposal_number();
    })

});

function proposal_rel_id_select() {
    var serverData = {};
    serverData.rel_id = _rel_id.val();
    data.type = _rel_type.val();
    init_ajax_search(_rel_type.val(), _rel_id, serverData);
}

function validate_proposal_form() {
    let dynamicRules = {};
    $('[name^="dynamic_fields"]').each(function() {
        const name = $(this).attr('name');
        if (name.includes('[label]') || name.includes('[amount]')) {
            dynamicRules[name] = 'required';
        }
    });
    $($('#proposal-form')).appFormValidator({
        rules: {
            proposal_number: 'required',
            subject: 'required',
            proposal_to: 'required',
            rel_type: 'required',
            rel_id: 'required',
            date: 'required',
            download_request: 'required',
            type: 'required',
            open_till: 'required',
            email: {
                email: true,
                required: true
            },
            currency: 'required',
            loading_place: 'required',
            discharge_place: 'required',
            payment_term: 'required',
            shipment_term: 'required',
            bank_ac_name: 'required',
            bank_ac_no: 'required',
            bank_name: 'required',
            bank_ifsc_code: 'required',
            bank_swift_code: 'required',
            bank_address: 'required',
            ...dynamicRules
        },
        errorPlacement: function(error, element) {
            var formGroup = $(element).closest('.form-group');
            if (formGroup.length) {
                formGroup.append(error);
            } else {
                $(element).after(error);
            }
        }

    });
}

function change_type() {
    var type = $('input[name="type"]:checked').val();
    $.ajax({
        url: "<?= admin_url("proposals/get_bank_details") ?>",
        type: 'POST',
        data: {
            type: type
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $.each(response.data, function(index, value) {
                    $('[name="' + index + '"]').val(value);
                });
            } else {
                alert_float('danger', "Error : something went wrong");
            }
        }
    })
}

function check_proposal_number() {
    $('#proposal_number').closest('.form-group').find('.text-danger').remove();
    $.ajax({
        url: "<?= admin_url("misc/check_invoice_number") ?>",
        type: 'POST',
        data: {
            type: 'proposal',
            number: $('#proposal_number').val(),
            prefix: $('#proposal_prefix').text(),
            gst: $('#selected_proposal_gst_number').val(),
            id: "<?= isset($proposal) ? $proposal->id : "" ?>"
        },
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                $('#proposal_number').closest('.form-group').append('<span class="text-danger">' + response
                    .message + '</span>');
            }
        }
    });
}
// Branch data baked in by PHP — keyed by option value (index)
var _proposalBranchMap = {};
<?php foreach ($proposal_branches_resolved as $bidx => $br): ?>
_proposalBranchMap[<?= $bidx ?>] = {
   prefix    : <?= json_encode($br['resolved_prefix']) ?>,
   rawPrefix : <?= json_encode($br['proposal_prefix'] ?? '') ?>,
   gst       : <?= json_encode($br['gst_number'] ?? '') ?>
};
<?php endforeach; ?>

var _isProposalEdit = <?= isset($proposal) ? 'true' : 'false' ?>;
var _initialBranchIndex = <?= isset($selected_branch_index) ? $selected_branch_index : 0 ?>;
var _initialProposalNumber = <?= isset($proposal) ? json_encode($proposal->proposal_number) : '""' ?>;

function applyProposalBranchGst(idx) {
   idx = parseInt(idx, 10);
   var branch = _proposalBranchMap[idx];
   if (!branch) return;

   // Update the prefix addon span immediately
   document.getElementById('proposal_prefix').textContent = branch.prefix;

   // Update hidden inputs for form submission
   document.getElementById('selected_proposal_gst_number').value      = branch.gst;
   document.getElementById('selected_proposal_branch_prefix_raw').value = branch.rawPrefix;

   // Auto-fill next proposal number
   if (!_isProposalEdit) {
      $.post(
         '<?= admin_url('proposals/get_next_proposal_number_for_prefix') ?>',
         { prefix: branch.prefix, gst: branch.gst },
         function(res) {
            try {
               var d = (typeof res === 'string') ? JSON.parse(res) : res;
               if (d && d.next_number) {
                  document.getElementById('proposal_number').value = d.next_number;
               }
            } catch(e) {}
         }
      );
   } else {
      // Edit mode: restore original number if switching back to the initial branch
      if (idx === _initialBranchIndex) {
         document.getElementById('proposal_number').value = _initialProposalNumber;
      } else {
         // Otherwise, fetch the next number for the new prefix to prevent duplicate number errors
         $.post(
            '<?= admin_url('proposals/get_next_proposal_number_for_prefix') ?>',
            { prefix: branch.prefix, gst: branch.gst },
            function(res) {
               try {
                  var d = (typeof res === 'string') ? JSON.parse(res) : res;
                  if (d && d.next_number) {
                     document.getElementById('proposal_number').value = d.next_number;
                  }
               } catch(e) {}
            }
         );
      }
   }
}

$(function() {
   $('#proposal_branch_gst_select').on('change', function() {
      applyProposalBranchGst(this.value);
   });
});
</script>
<?php
$this->load->view('admin/item_calculation_js');
?>
</body>

</html>