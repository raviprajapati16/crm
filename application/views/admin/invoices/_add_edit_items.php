<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    /* Dynamic Fields Responsive Styles */
    .dynamic-fields-wrapper {
        width: 50%;
        margin: 15px 0;
        float: inline-end;
    }

    .dynamic-fields-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .dynamic-fields-header h5 {
        margin: 0;
        font-weight: bold;
    }

    .dynamic-field-row {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 10px;
        width: 100%;
    }

    .dynamic-field-label {
        flex: 2;
        min-width: 200px;
    }

    .dynamic-field-amount {
        flex: 1;
        min-width: 120px;
    }

    .dynamic-field-remove {
        flex-shrink: 0;
        width: 40px;
    }

    .dynamic-field-row input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .dynamic-field-row button {
        width: 100%;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Responsive breakpoints */
    @media (max-width: 768px) {

        .dynamic-fields-wrapper {
            width: 100%;
            margin: 15px 0;
            float: inline-end;
        }

        .dynamic-field-row {
            flex-direction: column;
            gap: 8px;
        }

        .dynamic-field-label,
        .dynamic-field-amount,
        .dynamic-field-remove {
            flex: none;
            width: 100%;
        }

        .dynamic-field-remove {
            max-width: 100px;
            align-self: flex-end;
        }
    }

    @media (max-width: 480px) {

        .dynamic-fields-wrapper {
            width: 100%;
            margin: 15px 0;
            float: inline-end;
        }

        .dynamic-fields-header {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .dynamic-fields-header button {
            align-self: flex-end;
            width: auto;
        }
    }
</style>
<div class="panel-body mtop10">
    <div class="row">
        <!-- <div class="col-md-4">
          <?php $this->load->view('admin/invoice_items/item_select'); ?>
      </div> -->
        <!-- <div class="col-md-4">
            <button type="button" style="margin-top: 5px;" data-toggle="modal" data-target="#sales_item_modal"
                class="btn btn-info"><i class="fa fa-plus"></i> <?php echo _l('add_item'); ?></button>
        </div> -->
        <!-- <div class="col-md-8 text-right show_quantity_as_wrapper">
            <div class="mtop10">
                <span><?php echo _l('show_quantity_as'); ?></span>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="1" id="1" name="show_quantity_as" data-text="<?php echo _l('estimate_table_quantity_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 1) {
                                                                                                                                                        echo 'checked';
                                                                                                                                                    } else {
                                                                                                                                                        echo 'checked';
                                                                                                                                                    } ?>>
                    <label for="1"><?php echo _l('quantity_as_qty'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="2" id="2" name="show_quantity_as" data-text="<?php echo _l('estimate_table_hours_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 2) {
                                                                                                                                                    echo 'checked';
                                                                                                                                                } ?>>
                    <label for="2"><?php echo _l('quantity_as_hours'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="3" value="3" name="show_quantity_as" data-text="<?php echo _l('estimate_table_quantity_heading'); ?>/<?php echo _l('estimate_table_hours_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 3) {
                                                                                                                                                                                                        echo 'checked';
                                                                                                                                                                                                    } ?>>
                    <label for="3"><?php echo _l('estimate_table_quantity_heading'); ?>/<?php echo _l('estimate_table_hours_heading'); ?></label>
                </div>
            </div>
        </div> -->
    </div>
    <div class="row main" style="margin-top: 15px;">
        <div class="col-md-2">
            <?php echo render_select('item_group', $items_groups, array('id', 'name'), 'item_group'); ?>
        </div>
        <div class="col-md-2">
            <?php echo render_select('item_sub_group', array(), array('id', 'name'), 'Item Sub Group'); ?>
        </div>
        <div class="col-md-3">
            <?php echo render_select('item_select', array(), array('items'), 'Products'); ?>
            <input type="hidden" name="capacity" id="capacity">
            <input type="hidden" name="hsn_code" id="hsn_code">
            <?php if ($is_invoice) { ?>
                <input type="hidden" name="gross_weight" id="gross_weight">
                <input type="hidden" name="net_weight" id="net_weight">
            <?php } ?>
        </div>
        <!-- <div class="col-md-2">
         <?php echo render_input('capacity', 'Capacity', '', 'text', array('placeholder' => 'Capacity')); ?>
      </div> -->
        <div class="col-md-4">
            <label for="status" class="control-label"><?php echo _l('Description'); ?></label>
            <textarea name="long_description" rows="4" class="form-control"
                placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
        </div>
        <div class="col-md-2">
            <?php
            $custom_fields = get_custom_fields('items');
            foreach ($custom_fields as $cf) {
                echo '<th width="15%" align="left" class="custom_field">' . $cf['name'] . '</th>';
            }

            $qty_heading = _l('estimate_table_quantity_heading');
            if (isset($estimate) && $estimate->show_quantity_as == 2) {
                $qty_heading = _l('estimate_table_hours_heading');
            } else if (isset($estimate) && $estimate->show_quantity_as == 3) {
                $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
            }
            ?>
            <label for="status" class="control-label"><?php echo $qty_heading; ?></label>
            <input type="number" name="quantity" min="0" value="1" class="form-control"
                placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
            <input type="text" placeholder="<?php echo _l('unit'); ?>" name="unit"
                class="form-control input-transparent text-right">
        </div>
        <div class="col-md-2">
            <label for="status" class="control-label"><?php echo _l('estimate_table_rate_heading'); ?></label>
            <input type="number" name="rate" class="form-control"
                placeholder="<?php echo _l('item_rate_placeholder'); ?>">
        </div>

        <?php if ($proposal_status != 3 && $estimate_status != 4) { ?>
            <div class="col-md-1">
                <?php
                $new_item = 'undefined';
                if (isset($estimate)) {
                    $new_item = true;
                }
                ?>
                <button type="button" style="margin-top: 25px;"
                    onclick="add_item_to_tables('undefined','undefined',<?php echo $new_item; ?>); return false;"
                    class="btn pull-right btn-info add-product-btn"><i class="fa fa-check"></i></button>
            </div>
        <?php } ?>
    </div>

    <div class="table-responsive s_table" style="margin-top:15px;">
        <table class="table estimate-items-table items table-main-estimate-edit has-calculations no-mtop">
            <thead>
                <tr>
                    <th width="2%"></th>
                    <?php if ($is_invoice) { ?>
                        <th width="8%" align="left">Kind Of Packages</th>
                    <?php } ?>
                    <th width="18%" align="left">
                        <i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip"
                            data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i>
                        <?php echo _l('estimate_table_item_heading'); ?>
                    </th>
                    <th width="20%" align="left"><?php echo _l('estimate_table_item_description'); ?></th>
                    <th width="8%" align="left">HSN Code</th>
                    <?php
                    $custom_fields = get_custom_fields('items');
                    foreach ($custom_fields as $cf) {
                        echo '<th width="12%" align="left" class="custom_field">' . $cf['name'] . '</th>';
                    }

                    $qty_heading = _l('estimate_table_quantity_heading');
                    if (isset($estimate) && $estimate->show_quantity_as == 2) {
                        $qty_heading = _l('estimate_table_hours_heading');
                    } else if (isset($estimate) && $estimate->show_quantity_as == 3) {
                        $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
                    }
                    ?>
                    <th width="8%" class="qty" align="left"><?php echo $qty_heading; ?></th>
                    <?php if ($is_invoice) { ?>
                        <th width="8%" align="left">Net Weight <br> (Kg / Qty)</th>
                        <th width="8%" align="left">Total Net Weight <br> (In Kgs)</th>
                        <th width="8%" align="left">Gross Weight <br> (Kg / Qty)</th>
                        <th width="8%" align="left">Total Gross Weight <br> (In Kgs)</th>
                    <?php } ?>
                    <th width="10%" align="left"><?php echo _l('estimate_table_rate_heading'); ?></th>
                    <th width="8%" align="left"><?php echo _l('estimate_table_amount_heading'); ?></th>
                    <th width="2%" align="center"><i class="fa fa-cog"></i></th>
                </tr>

            </thead>
            <tbody>
                <?php if (isset($estimate) || isset($add_items)) {
                    $i               = 1;
                    $items_indicator = 'newitems';
                    if (isset($estimate)) {
                        $add_items       = $estimate->items;
                        $items_indicator = 'items';
                    }

                    foreach ($add_items as $item) {
                        $table_row = '<tr class="sortable item">';
                        $table_row .= '<td class="dragger">';
                        if ($item['qty'] == '' || $item['qty'] == 0) {
                            $item['qty'] = 1;
                        }
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][item_id]', $item['item_id']);
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][main_group_id]', $item['main_group_id']);
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][sub_group_id]', $item['sub_group_id']);

                        $amount = $item['rate'] * $item['qty'];
                        $amount = app_format_number($amount);
                        // order input
                        $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                        $table_row .= '</td>';
                        if ($is_invoice) {
                            $table_row .= '<td><input type="text" name="' . $items_indicator . '[' . $i . '][kind_of_packages]" onblur="calculate_total();" onchange="calculate_total();" value="' . $item['kind_of_packages'] . '" class="form-control">';
                        }
                        $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                        $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';
                        $table_row .= '<td><input type="text" placeholder="HSN Code" name="' . $items_indicator . '[' . $i . '][hsn_code]" class="form-control" value="' . $item['hsn_code'] . '"></td>';
                        $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');
                        $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                        $unit_placeholder = '';
                        if (!$item['unit']) {
                            $unit_placeholder = _l('unit');
                            $item['unit'] = '';
                        }
                        $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item['unit'] . '">';
                        if ($is_invoice) {
                            $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][net_weight]" value="' . $item['net_weight'] . '" class="form-control net_weight"></td>';
                            $table_row .= '<td class="total-net-weight"></td>';
                            $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][gross_weight]" value="' . $item['gross_weight'] . '" class="form-control gross_weight"></td>';
                            $table_row .= '<td class="total-gross-weight"></td>';
                        }
                        $table_row .= '</td>';
                        $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control"></td>';
                        $table_row .= '<td class="amount" align="right">' . $amount . '</td>';

                        if ($proposal_status != 3 && $estimate_status != 4) {
                            $table_row .= '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                        }

                        $table_row .= '</tr>';
                        echo $table_row;
                        $i++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($is_invoice)) { ?>
        <div class="col-md-12">
            <table class="table">
                <tbody>
                    <tr>
                        <td width="15%"><span class="bold">Total No. Of Packages</span></td>
                        <td width="45%"></td>
                        <td><span class="bold">Total Net Weight (In Kgs)</span></td>
                        <td><span class="bold">Total Gross Weight (In Kgs)</span></td>
                    </tr>
                    <tr>
                        <td width="15%">
                            <input type="number" name="total_packages" value="<?= $estimate->total_packages ?>"
                                class="form-control total_packages" />
                        </td>
                        <td width="45%"></td>
                        <td>
                            <span class="bold">
                                <input type="number" id="total_net_weight" name="total_net_weight"
                                    value="<?= $estimate->total_net_weight ?>" class="form-control total_net_weight"
                                    readonly />
                            </span>
                        </td>
                        <td>
                            <span class="bold">
                                <input type="number" id="total_gross_weight" name="total_gross_weight"
                                    value="<?= $estimate->total_gross_weight ?>" class="form-control total_gross_weight"
                                    readonly />
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <div class="col-md-8 col-md-offset-4">
        <table class="table text-right">
            <tbody>
                <tr id="subtotal">
                    <td><span class="bold">Sub Total Amount :</span>
                    </td>
                    <td class="subtotal">
                    </td>
                </tr>
                <tr id="discount_area">
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span class="bold"><?php echo _l('estimate_discount'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group" id="discount-total">

                                    <input type="number"
                                        value="<?php echo (isset($estimate) ? $estimate->discount_percent : 0); ?>"
                                        class="form-control pull-left input-discount-percent<?php if (isset($estimate) && !is_sale_discount($estimate, 'percent') && is_sale_discount_applied($estimate)) {
                                                                                                echo ' hide';
                                                                                            } ?>"
                                        min="0" max="100" name="discount_percent">

                                    <input type="number" data-toggle="tooltip"
                                        data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>"
                                        value="<?php echo (isset($estimate) ? $estimate->discount_total : 0); ?>"
                                        class="form-control pull-left input-discount-fixed<?php if (!isset($estimate) || (isset($estimate) && !is_sale_discount($estimate, 'fixed'))) {
                                                                                                echo ' hide';
                                                                                            } ?>"
                                        min="0" name="discount_total">

                                    <div class="input-group-addon">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span class="discount-total-type-selected">
                                                    <?php if (!isset($estimate) || isset($estimate) && (is_sale_discount($estimate, 'percent') || !is_sale_discount_applied($estimate))) {
                                                        echo '%';
                                                    } else {
                                                        echo _l('discount_fixed_amount');
                                                    }
                                                    ?>
                                                </span>
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                                aria-labelledby="dropdown_menu_tax_total_type">
                                                <li>
                                                    <a href="#"
                                                        class="discount-total-type discount-type-percent<?php if (!isset($estimate) || (isset($estimate) && is_sale_discount($estimate, 'percent')) || (isset($estimate) && !is_sale_discount_applied($estimate))) {
                                                                                                            echo ' selected';
                                                                                                        } ?>">%</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="discount-total-type discount-type-fixed<?php if (isset($estimate) && is_sale_discount($estimate, 'fixed')) {
                                                                                                                    echo ' selected';
                                                                                                                } ?>">
                                                        <?php echo _l('discount_fixed_amount'); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="discount-total"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <!-- Dynamic Fields Section -->
                        <div class="dynamic-fields-wrapper">
                            <div class="dynamic-fields-header">
                                <h5><strong>Additional Charges / Deductions</strong></h5>
                                <button type="button" id="add-dynamic-field" class="btn btn-success btn-sm">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>

                            <div id="dynamic-fields-container">
                                <?php
                                if (isset($dynamic_amount_fields) && !empty($dynamic_amount_fields)) {
                                    foreach ($dynamic_amount_fields as $index => $field) {
                                        echo '<div class="dynamic-field-row" data-index="' . $index . '">';
                                        echo '<div class="dynamic-field-label">';
                                        echo '<input type="hidden" name="dynamic_fields[' . $index . '][id]" class="form-control" value="' . $field['id'] . '">';
                                        echo '<input type="text" name="dynamic_fields[' . $index . '][label]" class="form-control dynamic-label" placeholder="Label" value="' . htmlspecialchars($field['label']) . '" required>';
                                        echo '</div>';
                                        echo '<div class="dynamic-field-amount">';
                                        echo '<input type="number" step="0.01" name="dynamic_fields[' . $index . '][amount]" class="form-control dynamic-amount" placeholder="Amount" value="' . $field['amount'] . '" onchange="calculate_total();" required>';
                                        echo '</div>';
                                        echo '<div class="dynamic-field-remove">';
                                        echo '<button type="button" class="btn btn-danger btn-sm remove-dynamic-field">';
                                        echo '<i class="fa fa-trash"></i>';
                                        echo '</button>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="taxable-amount-tr <?= ($estimate->type == 1) ? 'hide' : ''  ?>">
                    <td class="bold">Taxable Amount</td>
                    <td class="taxable_amount"></td>
                    <input type="hidden" name="taxable_amount" value="" class="taxable_amount_input">
                </tr>
                <tr class="tax-amount-tr <?= ($estimate->type == 1) ? 'hide' : ''  ?>">
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span class="bold">Tax</span>
                            </div>
                            <div class="col-md-5">

                                <div class="form-group">
                                    <select class="selectpicker display-block" data-width="100%" name="tax_id"
                                        id="tax_id" data-none-selected-text="<?php echo _l('no_tax'); ?>">
                                        <?php
                                        $rel_type = "";
                                        if ($is_proposal) {
                                            $rel_type = "proposal";
                                        } else if ($is_invoice) {
                                            $rel_type = "invoice";
                                        } else if ($is_purchase) {
                                            $rel_type = "purchase";
                                        }
                                        foreach ($taxes as $tax) {
                                            if (!empty($rel_type)) {
                                                $getTax = get_tax_by_relation($estimate->id, $rel_type);
                                            }
                                        ?>
                                            <option value="<?php echo $tax['id']; ?>"
                                                data-subtext="<?php echo $tax['name']; ?>"
                                                data-taxrate="<?php echo $tax['taxrate']; ?>"
                                                <?= ($getTax && $getTax->taxrate == $tax['taxrate']) ? "selected" : "" ?>>
                                                <?php echo $tax['taxrate']; ?>%</option>
                                        <?php }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="total_tax"></td>
                    <input type="hidden" id="total_tax" name="total_tax"
                        value="<?= isset($estimate->total_tax) ? $estimate->total_tax : 0 ?>" />
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span class="bold"><?php echo _l('estimate_adjustment'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <input type="number" data-toggle="tooltip"
                                    data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>"
                                    value="<?php if (isset($estimate)) {
                                                echo $estimate->adjustment;
                                            } else {
                                                echo 0;
                                            } ?>" class="form-control pull-left"
                                    name="adjustment">
                            </div>
                        </div>
                    </td>
                    <td class="adjustment"></td>
                </tr>
                <tr>
                    <td><span class="bold"><?php echo _l('estimate_total'); ?> :</span>
                    </td>
                    <td class="total">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="removed-items"></div>
</div>