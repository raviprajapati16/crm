<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s invoice accounting-template">
   <div class="additional"></div>
   <div class="panel-body">
      <?php if (isset($invoice)) { ?>
         <?php echo format_invoice_status($invoice->status); ?>
         <hr class="hr-panel-heading" />
      <?php } ?>
      <?php hooks()->do_action('before_render_invoice_template'); ?>
      <div class="row">
         <div class="col-md-6">
            <div class="f_client_id">
               <div class="form-group select-placeholder">
                  <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                  <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($invoice) && empty($invoice->clientid)) {
                                                                                                                        echo ' customer-removed';
                                                                                                                     } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                     <?php $selected = (isset($invoice) ? $invoice->clientid : '');
                     if ($selected == '') {
                        $selected = (isset($customer_id) ? $customer_id : '');
                     }
                     if ($selected != '') {
                        $rel_data = get_relation_data('customer', $selected);
                        $rel_val = get_relation_values($rel_data, 'customer');
                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                     } ?>
                  </select>
               </div>
            </div>
            <?php
            if (!isset($invoice_from_project)) { ?>
               <div class="form-group select-placeholder projects-wrapper<?php if ((!isset($invoice)) || (isset($invoice) && !customer_has_projects($invoice->clientid))) {
                                                                              echo ' hide';
                                                                           } ?>">
                  <label for="project_id"><?php echo _l('project'); ?></label>
                  <div id="project_ajax_search_wrapper">
                     <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php
                        if (isset($invoice) && $invoice->project_id != 0) {
                           echo '<option value="' . $invoice->project_id . '" selected>' . get_project_name_by_id($invoice->project_id) . '</option>';
                        }
                        ?>
                     </select>
                  </div>
               </div>
            <?php } ?>
            <?php
            // ── Load branch rows from Invoice Settings ──────────────────────
            $branch_rows_json = get_option('branch_rows');
            $branch_rows      = $branch_rows_json ? json_decode($branch_rows_json, true) : [];
            if (!is_array($branch_rows)) {
                $branch_rows = [];
            }

            // On edit, identify which branch matches the invoice's prefix and gst_number
            $matched_branch_id = null;
            if (isset($invoice)) {
                foreach ($branch_rows as $br) {
                    $resolved_pref = replace_dynamic_prefix($br['invoice_prefix'] ?? '');
                    $br_gst = trim($br['gst_number'] ?? '');
                    $inv_gst = trim($invoice->gst_number ?? '');
                    if ($resolved_pref === $invoice->prefix && $br_gst === $inv_gst) {
                        $matched_branch_id = $br['id'] ?? null;
                        break;
                    }
                }
                // Fallback
                if (empty($matched_branch_id)) {
                    foreach ($branch_rows as $br) {
                        $resolved_pref = replace_dynamic_prefix($br['invoice_prefix'] ?? '');
                        if ($resolved_pref === $invoice->prefix) {
                            $matched_branch_id = $br['id'] ?? null;
                            break;
                        }
                    }
                }
            }

            // Filter active branches, or the currently matched one if editing
            $active_branch_rows = [];
            foreach ($branch_rows as $br) {
                $is_deleted = !empty($br['deleted']);
                $is_matched = (isset($br['id']) && $matched_branch_id !== null && $br['id'] === $matched_branch_id);
                if (!$is_deleted || $is_matched) {
                    $active_branch_rows[] = $br;
                }
            }

            if (empty($active_branch_rows)) {
                // Fallback: build a single entry from the legacy option
                $active_branch_rows = [[
                    'id'             => '',
                    'branch_name'    => 'Default',
                    'invoice_prefix' => get_option('invoice_prefix') ?: 'INV-',
                    'gst_number'     => '',
                ]];
            }

            // Resolve dynamic variables for each branch prefix (for matching on edit)
            $branch_rows_resolved = [];
            foreach ($active_branch_rows as $br) {
                $br['resolved_prefix'] = replace_dynamic_prefix($br['invoice_prefix'] ?? '');
                $branch_rows_resolved[] = $br;
            }

            // On edit or conversion: find which branch index is currently selected
            $selected_branch_index = 0;
            if (isset($invoice)) {
                $is_conversion = isset($invoice->proposal_number_prefix);
                $search_prefix = $is_conversion ? $invoice->proposal_number_prefix : ($invoice->prefix ?? '');
                $search_gst = $is_conversion ? ($invoice->proposal_gst_number ?? '') : ($invoice->gst_number ?? '');

                foreach ($branch_rows_resolved as $bidx => $br) {
                    $br_gst = trim($br['gst_number'] ?? '');
                    $comp_gst = trim($search_gst);
                    $br_pref = $is_conversion ? replace_dynamic_prefix($br['proposal_prefix'] ?? '') : $br['resolved_prefix'];

                    if ($br_pref === $search_prefix && $br_gst === $comp_gst) {
                        $selected_branch_index = $bidx;
                        break;
                    }
                }
                // Fallback if no exact match (e.g. legacy records with only prefix)
                if ($selected_branch_index === 0) {
                    foreach ($branch_rows_resolved as $bidx => $br) {
                        $br_pref = $is_conversion ? replace_dynamic_prefix($br['proposal_prefix'] ?? '') : $br['resolved_prefix'];
                        if ($br_pref === $search_prefix) {
                            $selected_branch_index = $bidx;
                            break;
                        }
                    }
                }
            }
            $selected_branch = $branch_rows_resolved[$selected_branch_index];
            ?>

            <!-- Branch / GST Dropdown -->
            <div class="form-group">
               <label for="branch_gst_select">Branch / GST</label>
               <select id="branch_gst_select" name="branch_gst_select" class="form-control" onchange="applyBranchGst(this.value)">
                  <?php foreach ($branch_rows_resolved as $bidx => $br): ?>
                  <option value="<?= $bidx ?>"
                     data-prefix="<?= htmlspecialchars($br['resolved_prefix']) ?>"
                     data-gst="<?= htmlspecialchars($br['gst_number'] ?? '') ?>"
                     data-raw-prefix="<?= htmlspecialchars($br['invoice_prefix'] ?? '') ?>"
                     <?= ($bidx === $selected_branch_index) ? 'selected' : '' ?>>
                     <?= htmlspecialchars($br['branch_name']) ?>
                     <?php if (!empty($br['gst_number'])): ?>
                        (<?= htmlspecialchars($br['gst_number']) ?>)
                     <?php endif; ?>
                  </option>
                  <?php endforeach; ?>
               </select>
               <!-- Hidden: store selected GST number for form submission -->
               <input type="hidden" id="selected_gst_number" name="selected_gst_number"
                      value="<?= htmlspecialchars($selected_branch['gst_number'] ?? '') ?>">
               <input type="hidden" id="selected_branch_prefix_raw" name="selected_branch_prefix_raw"
                      value="<?= htmlspecialchars($selected_branch['invoice_prefix'] ?? '') ?>">
            </div>

            <div class="form-group">
               <label for="number"><?php echo _l('invoice_add_edit_number'); ?></label>

               <div class="input-group">
                  <span class="input-group-addon" id="invoice_prefix"><?= htmlspecialchars($selected_branch['resolved_prefix']) ?></span>
                  <input type="number" id="number" name="number" class="form-control"
                         value="<?= (isset($invoice) && $invoice->number) ? $invoice->number : get_next_number('invoice', $selected_branch['resolved_prefix'], $selected_branch['gst_number'] ?? '') ?>">
               </div>
            </div>

            <script>
               // Branch data baked in by PHP — keyed by option value (index)
               var _branchMap = {};
               <?php foreach ($branch_rows_resolved as $bidx => $br): ?>
               _branchMap[<?= $bidx ?>] = {
                  prefix    : <?= json_encode($br['resolved_prefix']) ?>,
                  rawPrefix : <?= json_encode($br['invoice_prefix'] ?? '') ?>,
                  gst       : <?= json_encode($br['gst_number'] ?? '') ?>
               };
               <?php endforeach; ?>

               var _isEdit = <?= (isset($invoice) && !isset($convert_invoice)) ? 'true' : 'false' ?>;
               var _initialBranchIndex = <?= isset($selected_branch_index) ? $selected_branch_index : 0 ?>;
               var _initialInvoiceNumber = <?= (isset($invoice) && !isset($convert_invoice) && isset($invoice->number)) ? json_encode($invoice->number) : '""' ?>;

               // Called by inline onchange AND by jQuery .on('change')
               function applyBranchGst(idx) {
                  idx = parseInt(idx, 10);
                  var branch = _branchMap[idx];
                  if (!branch) return;

                  // Update the prefix addon span immediately
                  document.getElementById('invoice_prefix').textContent = branch.prefix;

                  // Update hidden inputs for form submission
                  document.getElementById('selected_gst_number').value      = branch.gst;
                  document.getElementById('selected_branch_prefix_raw').value = branch.rawPrefix;

                  // Auto-fill next invoice number
                  if (!_isEdit) {
                     $.post(
                        '<?= admin_url('invoices/get_next_invoice_number_for_prefix') ?>',
                        { prefix: branch.prefix, gst: branch.gst },
                        function(res) {
                           try {
                              var d = (typeof res === 'string') ? JSON.parse(res) : res;
                              if (d && d.next_number) {
                                 document.getElementById('number').value = d.next_number;
                              }
                           } catch(e) {}
                        }
                     );
                  } else {
                     // Edit mode: restore original number if switching back to the initial branch
                     if (idx === _initialBranchIndex) {
                        document.getElementById('number').value = _initialInvoiceNumber;
                     } else {
                        // Otherwise, fetch the next number for the new prefix to prevent duplicate number errors
                        $.post(
                           '<?= admin_url('invoices/get_next_invoice_number_for_prefix') ?>',
                           { prefix: branch.prefix, gst: branch.gst },
                           function(res) {
                              try {
                                 var d = (typeof res === 'string') ? JSON.parse(res) : res;
                                 if (d && d.next_number) {
                                    document.getElementById('number').value = d.next_number;
                                 }
                              } catch(e) {}
                           }
                        );
                     }
                  }
               }

               // Also bind via jQuery as a belt-and-suspenders fallback
               $(function() {
                  $('#branch_gst_select').on('change', function() {
                     applyBranchGst(this.value);
                  });
               });
            </script>

            <div class="row">
               <div class="col-md-12">
               <hr class="hr-10" />
                  <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                  <?php include_once(APPPATH .'views/admin/invoices/billing_and_shipping_template.php'); ?>
               </div>
               <div class="col-md-12">
                  <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                  <address>
                     <span class="billing_street" id="invoice_bill_to_street">
                     <?php $billing_street = (isset($invoice) ? $invoice->billing_street : '--'); ?>
                     <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                     <?php echo $billing_street; ?></span><br>
                     <span class="billing_city" id="invoice_bill_to_city">
                     <?php $billing_city = (isset($invoice) ? $invoice->billing_city : '--'); ?>
                     <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                     <?php echo $billing_city; ?></span>,
                     <span class="billing_state" id="invoice_bill_to_state">
                     <?php $billing_state = (isset($invoice) ? $invoice->billing_state : '--'); ?>
                     <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                     <?php echo $billing_state; ?></span>
                     <br/>
                     <span class="billing_country" id="invoice_bill_to_country">
                     <?php $billing_country = (isset($invoice) ? get_country_short_name($invoice->billing_country) : '--'); ?>
                     <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                     <?php echo $billing_country; ?></span>,
                     <span class="billing_zip" id="invoice_bill_to_zip">
                     <?php $billing_zip = (isset($invoice) ? $invoice->billing_zip : '--'); ?>
                     <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                     <?php echo $billing_zip; ?></span>
                  </address>
               </div>
               <!-- <div class="col-md-6">
                  <p class="bold"><?php echo _l('ship_to'); ?></p>
                  <address>
                     <span class="shipping_street">
                     <?php $shipping_street = (isset($invoice) ? $invoice->shipping_street : '--'); ?>
                     <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                     <?php echo $shipping_street; ?></span><br>
                     <span class="shipping_city">
                     <?php $shipping_city = (isset($invoice) ? $invoice->shipping_city : '--'); ?>
                     <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                     <?php echo $shipping_city; ?></span>,
                     <span class="shipping_state">
                     <?php $shipping_state = (isset($invoice) ? $invoice->shipping_state : '--'); ?>
                     <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                     <?php echo $shipping_state; ?></span>
                     <br/>
                     <span class="shipping_country">
                     <?php $shipping_country = (isset($invoice) ? get_country_short_name($invoice->shipping_country) : '--'); ?>
                     <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                     <?php echo $shipping_country; ?></span>,
                     <span class="shipping_zip">
                     <?php $shipping_zip = (isset($invoice) ? $invoice->shipping_zip : '--'); ?>
                     <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                     <?php echo $shipping_zip; ?></span>
                  </address>
               </div> -->
            </div>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? _d($invoice->date) : _d(date('Y-m-d')));
                  $date_attrs = array();
                  if (isset($invoice) && $invoice->recurring > 0 && $invoice->last_recurring_date != null) {
                     $date_attrs['disabled'] = true;
                  }
                  ?>
                  <?php echo render_date_input('date', 'invoice_add_edit_date', $value, $date_attrs); ?>
               </div>
               <div class="col-md-6">
                  <?php
                  $value = '';
                  if (isset($invoice)) {
                     $value = _d($invoice->duedate);
                  } else {
                     if (get_option('invoice_due_after') != 0) {
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                     }
                  }
                  ?>
                  <?php echo render_date_input('duedate', 'invoice_add_edit_duedate', $value); ?>
               </div>
            </div>
            <?php if (is_invoices_overdue_reminders_enabled()) { ?>
               <div class="form-group">
                  <div class="checkbox checkbox-danger">
                     <input type="checkbox" <?php if (isset($invoice) && $invoice->cancel_overdue_reminders == 1) {
                                                echo 'checked';
                                             } ?> id="cancel_overdue_reminders" name="cancel_overdue_reminders">
                     <label for="cancel_overdue_reminders"><?php echo _l('cancel_overdue_reminders_invoice') ?></label>
                  </div>
               </div>
            <?php } ?>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? $invoice->loading_place : ''); ?>
                  <?php echo render_input('loading_place', 'Place Of Loading', $value); ?>
               </div>
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? $invoice->discharge_place : ''); ?>
                  <?php echo render_input('discharge_place', 'Place Of Discharge', $value); ?>
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? $invoice->payment_term : ''); ?>
                  <?php echo render_input('payment_term', 'Payment Term', $value); ?>
               </div>
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? $invoice->shipment_term : ''); ?>
                  <?php echo render_input('shipment_term', 'Shipment Term', $value); ?>
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($invoice) ? $invoice->clientnote : ''); ?>
                  <?php echo render_textarea('clientnote', 'Notes', $value); ?>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <h4>Shipping Details</h4>
                  <div class="row">
                     <div class="col-md-4">
                        <?php $value = (isset($invoice) ? $invoice->transporter : ''); ?>
                        <?php echo render_input('transporter', 'Transporter', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($invoice) ? $invoice->lr_br_no : ''); ?>
                        <?php echo render_input('lr_br_no', 'LR No. / BL No.', $value); ?>
                     </div>
                     <div class="col-md-4">
                        <?php $value = (isset($invoice) ? $invoice->vehicle_no : ''); ?>
                        <?php echo render_input('vehicle_no', 'Vehicle No.', $value); ?>
                     </div>
                  </div>
               </div>
            </div>
            <?php $rel_id = (isset($invoice) ? $invoice->id : false); ?>
            <?php
            if (isset($custom_fields_rel_transfer)) {
               $rel_id = $custom_fields_rel_transfer;
            }
            ?>
            <?php echo render_custom_fields('invoice', $rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="panel_s no-shadow">
               <div class="form-group">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($invoice) ? prep_tags_input(get_tags_in($invoice->id, 'invoice')) : ''); ?>" data-role="tagsinput">
               </div>
               <div class="form-group mbot15 select-placeholder">
                  <label for="allowed_payment_modes" class="control-label"><?php echo _l('invoice_add_edit_allowed_payment_modes'); ?></label>
                  <br />
                  <?php if (count($payment_modes) > 0) { ?>
                     <select class="selectpicker"
                        data-toggle="<?php echo $this->input->get('allowed_payment_modes'); ?>"
                        name="allowed_payment_modes[]"
                        data-actions-box="true"
                        multiple="true"
                        data-width="100%"
                        data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php foreach ($payment_modes as $mode) {
                           $selected = '';
                           if (isset($invoice)) {
                              if ($invoice->allowed_payment_modes) {
                                 $inv_modes = unserialize($invoice->allowed_payment_modes);
                                 if (is_array($inv_modes)) {
                                    foreach ($inv_modes as $_allowed_payment_mode) {
                                       if ($_allowed_payment_mode == $mode['id']) {
                                          $selected = ' selected';
                                       }
                                    }
                                 }
                              }
                           } else {
                              if ($mode['selected_by_default'] == 1) {
                                 $selected = ' selected';
                              }
                           }
                        ?>
                           <option value="<?php echo $mode['id']; ?>" <?php echo $selected; ?>><?php echo $mode['name']; ?></option>
                        <?php } ?>
                     </select>
                  <?php } else { ?>
                     <p><?php echo _l('invoice_add_edit_no_payment_modes_found'); ?></p>
                     <a class="btn btn-info" href="<?php echo admin_url('paymentmodes'); ?>">
                        <?php echo _l('new_payment_mode'); ?>
                     </a>
                  <?php } ?>
               </div>

               <div class="row">
                  <div class="col-md-6">
                     <?php
                     $currency_attr = array('disabled' => true, 'data-show-subtext' => true);
                     $currency_attr = apply_filters_deprecated('invoice_currency_disabled', [$currency_attr], '2.3.0', 'invoice_currency_attributes');

                     foreach ($currencies as $currency) {
                        if ($currency['isdefault'] == 1) {
                           $currency_attr['data-base'] = $currency['id'];
                        }
                        if (isset($invoice)) {
                           if ($currency['id'] == $invoice->currency) {
                              $selected = $currency['id'];
                           }
                        } else {
                           if ($currency['isdefault'] == 1) {
                              $selected = $currency['id'];
                           }
                        }
                     }
                     $currency_attr = hooks()->apply_filters('invoice_currency_attributes', $currency_attr);
                     ?>
                     <?php echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                  </div>
                  <div class="col-md-6">
                     <?php
                     $i = 0;
                     $selected = '';
                     foreach ($staff as $member) {
                        if (isset($invoice)) {
                           if ($invoice->sale_agent == $member['staffid']) {
                              $selected = $member['staffid'];
                           }
                        }
                        $i++;
                     }
                     echo render_select('sale_agent', $staff, array('staffid', array('firstname', 'lastname')), 'Assigned', $selected);
                     ?>
                  </div>
                  <!-- <div class="col-md-6">
                     <div class="form-group select-placeholder" <?php if (isset($invoice) && !empty($invoice->is_recurring_from)) { ?> data-toggle="tooltip" data-title="<?php echo _l('create_recurring_from_child_error_message', [_l('invoice_lowercase'), _l('invoice_lowercase'), _l('invoice_lowercase')]); ?>" <?php } ?>>
                        <label for="recurring" class="control-label">
                           <?php echo _l('invoice_add_edit_recurring'); ?>
                        </label>
                        <select class="selectpicker"
                           data-width="100%"
                           name="recurring"
                           data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                           <?php
                           // The problem is that this invoice was generated from previous recurring invoice
                           // Then this new invoice you set it as recurring but the next invoice date was still taken from the previous invoice.
                           if (isset($invoice) && !empty($invoice->is_recurring_from)) {
                              echo 'disabled';
                           } ?>>
                           <?php for ($i = 0; $i <= 12; $i++) { ?>
                              <?php
                              $selected = '';
                              if (isset($invoice)) {
                                 if ($invoice->custom_recurring == 0) {
                                    if ($invoice->recurring == $i) {
                                       $selected = 'selected';
                                    }
                                 }
                              }
                              if ($i == 0) {
                                 $reccuring_string =  _l('invoice_add_edit_recurring_no');
                              } else if ($i == 1) {
                                 $reccuring_string = _l('invoice_add_edit_recurring_month', $i);
                              } else {
                                 $reccuring_string = _l('invoice_add_edit_recurring_months', $i);
                              }
                              ?>
                              <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $reccuring_string; ?></option>
                           <?php } ?>
                           <option value="custom" <?php if (isset($invoice) && $invoice->recurring != 0 && $invoice->custom_recurring == 1) {
                                                      echo 'selected';
                                                   } ?>><?php echo _l('recurring_custom'); ?></option>
                        </select>
                     </div>
                  </div> -->
                  <div class="col-md-6">
                     <div class="form-group select-placeholder">
                        <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                        <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="" selected><?php echo _l('no_discount'); ?></option>
                           <option value="before_tax" <?php
                                                      if (isset($invoice)) {
                                                         if ($invoice->discount_type == 'before_tax') {
                                                            echo 'selected';
                                                         }
                                                      } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                           <option value="after_tax" <?php if (isset($invoice)) {
                                                         if ($invoice->discount_type == 'after_tax') {
                                                            echo 'selected';
                                                         }
                                                      } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                        </select>
                     </div>
                  </div>
                  <!-- <div class="recurring_custom <?php if ((isset($invoice) && $invoice->custom_recurring != 1) || (!isset($invoice))) {
                                                   echo 'hide';
                                                } ?>">
                     <div class="col-md-6">
                        <?php $value = (isset($invoice) && $invoice->custom_recurring == 1 ? $invoice->recurring : 1); ?>
                        <?php echo render_input('repeat_every_custom', '', $value, 'number', array('min' => 1)); ?>
                     </div>
                     <div class="col-md-6">
                        <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="day" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'day') {
                                                   echo 'selected';
                                                } ?>><?php echo _l('invoice_recurring_days'); ?></option>
                           <option value="week" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'week') {
                                                   echo 'selected';
                                                } ?>><?php echo _l('invoice_recurring_weeks'); ?></option>
                           <option value="month" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'month') {
                                                      echo 'selected';
                                                   } ?>><?php echo _l('invoice_recurring_months'); ?></option>
                           <option value="year" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'year') {
                                                   echo 'selected';
                                                } ?>><?php echo _l('invoice_recurring_years'); ?></option>
                        </select>
                     </div>
                  </div> -->
                  <!-- <div id="cycles_wrapper" class="<?php if (!isset($invoice) || (isset($invoice) && $invoice->recurring == 0)) {
                                                      echo ' hide';
                                                   } ?>">
                     <div class="col-md-12">
                        <?php $value = (isset($invoice) ? $invoice->cycles : 0); ?>
                        <div class="form-group recurring-cycles">
                           <label for="cycles"><?php echo _l('recurring_total_cycles'); ?>
                              <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                 echo '<small>' . _l('cycles_passed', $invoice->total_cycles) . '</small>';
                              }
                              ?>
                           </label>
                           <div class="input-group">
                              <input type="number" class="form-control" <?php if ($value == 0) {
                                                                           echo ' disabled';
                                                                        } ?> name="cycles" id="cycles" value="<?php echo $value; ?>" <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                                                                                                                          echo 'min="' . ($invoice->total_cycles) . '"';
                                                                                                                                       } ?>>
                              <div class="input-group-addon">
                                 <div class="checkbox">
                                    <input type="checkbox" <?php if ($value == 0) {
                                                               echo ' checked';
                                                            } ?> id="unlimited_cycles">
                                    <label for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div> -->
               </div>
               <?php $value = (isset($invoice) ? $invoice->adminnote : ''); ?>
               <?php echo render_textarea('adminnote', 'invoice_add_edit_admin_note', $value); ?>
               <div class="row">
                  <div class="col-md-12">
                     <label>Type</label>
                     <div class="form-group">
                        <div class="radio-section">
                           <label class="radio-inline" for="type_0">
                              <input type="radio" id="type_0" value="0" name="type" <?= (isset($invoice->type) && $invoice->type == "0") ? 'checked' : '' ?> <?= (!isset($invoice->type)) ? 'checked' : '' ?>> Domestic
                           </label>
                           <label class="radio-inline" for="type_1">
                              <input type="radio" id="type_1" value="1" name="type" <?= (isset($invoice->type) && $invoice->type == "1") ? 'checked' : '' ?>> International
                           </label>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <h4>Bank Details</h4>
                     <div class="row">
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_ac_name : ''); ?>
                           <?php echo render_input('bank_ac_name', 'Account Name', $value); ?>
                        </div>
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_ac_no : ''); ?>
                           <?php echo render_input('bank_ac_no', 'Account Number', $value, 'number'); ?>
                        </div>
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_name : ''); ?>
                           <?php echo render_input('bank_name', 'Bank Name', $value); ?>
                        </div>
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_ifsc_code : ''); ?>
                           <?php echo render_input('bank_ifsc_code', 'IFSC Code', $value); ?>
                        </div>
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_swift_code : ''); ?>
                           <?php echo render_input('bank_swift_code', 'Swift Code', $value); ?>
                        </div>
                        <div class="col-md-4">
                           <?php $value = (isset($invoice) ? $invoice->bank_address : ''); ?>
                           <?php echo render_textarea('bank_address', 'Bank Address', $value); ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="panel-body mtop10">
      <?php
      $data['dynamic_amount_fields'] = get_dynamic_amount_fields('invoice', $invoice->id);
      if (isset($convert_invoice)) {
         $data['dynamic_amount_fields'] = get_dynamic_amount_fields('proposal', $proposal->id);
         foreach ($data['dynamic_amount_fields'] as $key => $field) {
            if (isset($field['id'])) {
               unset($data['dynamic_amount_fields'][$key]['id']);
            }
         }
      }
      $this->load->view('admin/invoices/_add_edit_items', $data);
      ?>
      <div id="removed-items"></div>
      <div id="billed-tasks"></div>
      <div id="billed-expenses"></div>
      <?php echo form_hidden('task_id'); ?>
      <?php echo form_hidden('expense_id'); ?>
   </div>
   <div class="row">
      <div class="col-md-12 mtop15">
         <div class="panel-body bottom-transaction">
            <?php $value = (isset($invoice) ? $invoice->terms : get_option('invoice_terms_and_condition')); ?>
            <div class="form-group mtop15" app-field-wrapper="terms">
               <label for="terms" class="control-label">Terms & Conditions</label>
               <textarea id="terms" name="terms" class="form-control" rows="4" aria-invalid="false">
                  <?= $value ?>
               </textarea>
            </div>
            <div class="btn-bottom-toolbar text-right">
               <?php if (!isset($invoice)) { ?>
                  <button class="btn-tr btn btn-default mleft10 text-right invoice-form-submit save-as-draft transaction-submit">
                     <?php echo _l('save_as_draft'); ?>
                  </button>
               <?php } ?>
               <div class="btn-group dropup">
                  <button type="button" class="btn-tr btn btn-info invoice-form-submit transaction-submit"><?php echo _l('submit'); ?></button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-right width200">
                     <li>
                        <a href="#" class="invoice-form-submit save-and-send transaction-submit">
                           <?php echo _l('save_and_send'); ?>
                        </a>
                     </li>
                     <?php if (!isset($invoice)) { ?>
                        <li>
                           <a href="#" class="invoice-form-submit save-and-record-payment transaction-submit">
                              <?php echo _l('save_and_record_payment'); ?>
                           </a>
                        </li>
                     <?php } ?>
                  </ul>
               </div>
            </div>
         </div>
         <div class="btn-bottom-pusher"></div>
      </div>
   </div>
</div>