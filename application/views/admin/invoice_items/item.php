<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="sales_item_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('invoice_item_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('invoice_item_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/invoice_items/manage', array('id' => 'invoice_item_form')); ?>
            <?php echo form_hidden('itemid'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning affect-warning hide">
                            <?php echo _l('changing_items_affect_warning'); ?>
                        </div>
                        <?php echo render_select('group_id', $items_groups, array('id', 'name'), 'item_group'); ?>
                        <?php echo render_select('item_sub_group', array(), array('id', 'name'), 'Item Sub Group'); ?>
                        <?php echo render_input('description', 'invoice_item_add_edit_description'); ?>
                        <?php echo render_input('net_weight', 'Net Weight', '', 'number'); ?>
                        <?php echo render_input('gross_weight', 'Gross Weight', 'number'); ?>
                        <?php //echo render_textarea('long_description','invoice_item_long_description');
                        ?>
                        <?php //echo render_input('capacity', 'Capacity'); 
                        ?>
                        <?php echo render_input('hsn_code', 'HSN Code'); ?>
                        <div class="form-group">
                            <label for="rate" class="control-label">
                                <?php echo _l('invoice_item_add_edit_rate_currency', $base_currency->name . ' <small>(' . _l('base_currency_string') . ')</small>'); ?></label>
                            <input type="number" id="rate" name="rate" class="form-control" value="">
                        </div>
                        <?php
                        foreach ($currencies as $currency) {
                            if ($currency['isdefault'] == 0) { ?>
                                <div class="form-group">
                                    <label for="rate_currency_<?php echo $currency['id']; ?>" class="control-label">
                                        <?php echo _l('invoice_item_add_edit_rate_currency', $currency['name']); ?></label>
                                    <input type="number" id="rate_currency_<?php echo $currency['id']; ?>" name="rate_currency_<?php echo $currency['id']; ?>" class="form-control" value="">
                                </div>
                        <?php   }
                        }
                        ?>
                        <!-- <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="tax"><?php echo _l('tax_1'); ?></label>
                                    <select class="selectpicker display-block" data-width="100%" name="tax" data-none-selected-text="<?php echo _l('no_tax'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($taxes as $tax) { ?>
                                            <option value="<?php echo $tax['id']; ?>" data-subtext="<?php echo $tax['name']; ?>"><?php echo $tax['taxrate']; ?>%</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="tax2"><?php echo _l('tax_2'); ?></label>
                                    <select class="selectpicker display-block" disabled data-width="100%" name="tax2" data-none-selected-text="<?php echo _l('no_tax'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($taxes as $tax) { ?>
                                            <option value="<?php echo $tax['id']; ?>" data-subtext="<?php echo $tax['name']; ?>"><?php echo $tax['taxrate']; ?>%</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div> -->
                        <div class="clearfix mbot15"></div>
                        <?php echo render_input('unit', 'unit'); ?>
                        <div id="custom_fields_items">
                            <?php echo render_custom_fields('items'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    var is_invoice = <?= isset($is_invoice) ? $is_invoice : 0; ?>;
    // Maybe in modal? Eq convert to invoice or convert proposal to estimate/invoice
    if (typeof(jQuery) != 'undefined') {
        init_item_js();
    } else {
        window.addEventListener('load', function() {
            var initItemsJsInterval = setInterval(function() {
                if (typeof(jQuery) != 'undefined') {
                    init_item_js();
                    clearInterval(initItemsJsInterval);
                }
            }, 1000);
        });
    }
    // Items add/edit
    function manage_invoice_items(form) {
        var data = $(form).serialize();

        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var item_select = $('#item_select');
                if ($("body").find('.accounting-template').length > 0) {
                    if (!item_select.hasClass('ajax-search')) {
                        var group = item_select.find('[data-group-id="' + response.item.group_id + '"]');
                        if (group.length == 0) {
                            var _option = '<optgroup label="' + (response.item.group_name == null ? '' : response.item.group_name) + '" data-group-id="' + response.item.group_id + '">' + _option + '</optgroup>';
                            if (item_select.find('[data-group-id="0"]').length == 0) {
                                item_select.find('option:first-child').after(_option);
                            } else {
                                item_select.find('[data-group-id="0"]').after(_option);
                            }
                        } else {
                            group.prepend('<option data-subtext="' + response.item.long_description + '" value="' + response.item.itemid + '">(' + accounting.formatNumber(response.item.rate) + ') ' + response.item.description + '</option>');
                        }
                    }
                    if (!item_select.hasClass('ajax-search')) {
                        item_select.selectpicker('refresh');
                    } else {

                        item_select.contents().filter(function() {
                            return !$(this).is('.newitem') && !$(this).is('.newitem-divider');
                        }).remove();

                        var clonedItemsAjaxSearchSelect = item_select.clone();
                        item_select.selectpicker('destroy').remove();
                        $("body").find('.items-select-wrapper').append(clonedItemsAjaxSearchSelect);
                        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
                    }

                    add_item_to_preview(response.item.itemid);
                } else {
                    // Is general items view
                    $('.table-invoice-items').DataTable().ajax.reload(null, false);
                }
                alert_float('success', response.message);
            }
            $('#sales_item_modal').modal('hide');
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
        return false;
    }

    function init_item_js() {
        // Add item to preview from the dropdown for invoices estimates
        $("body").on('change', 'select[name="item_select"]', function() {
            var itemid = $(this).selectpicker('val');
            if (itemid != '') {
                get_item_to_preview(itemid);
            }
        });

        // Items modal show action
        $("body").on('show.bs.modal', '#sales_item_modal', function(event) {

            $('.affect-warning').addClass('hide');

            var $itemModal = $('#sales_item_modal');
            $('input[name="itemid"]').val('');
            $itemModal.find('input').not('input[type="hidden"]').val('');
            $itemModal.find('textarea').val('');
            $itemModal.find('select').selectpicker('val', '').selectpicker('refresh');
            $('select[name="tax2"]').selectpicker('val', '').change();
            $('select[name="tax"]').selectpicker('val', '').change();
            $itemModal.find('.add-title').removeClass('hide');
            $itemModal.find('.edit-title').addClass('hide');

            var id = $(event.relatedTarget).data('id');
            // If id found get the text from the datatable
            if (typeof(id) !== 'undefined') {

                $('.affect-warning').removeClass('hide');
                $('input[name="itemid"]').val(id);

                requestGetJSON('invoice_items/get_item_by_id/' + id).done(function(response) {
                    $itemModal.find('#item_sub_group').html(response.subgroup_options);
                    $itemModal.find('#item_sub_group').selectpicker('refresh');

                    $itemModal.find('input[name="description"]').val(response.description);
                    $itemModal.find('textarea[name="long_description"]').val(response.long_description.replace(/(<|<)br\s*\/*(>|>)/g, " "));
                    $itemModal.find('input[name="rate"]').val(response.rate);
                    $itemModal.find('input[name="unit"]').val(response.unit);
                    $itemModal.find('input[name="capacity"]').val(response.capacity);
                    $itemModal.find('input[name="hsn_code"]').val(response.hsn_code);
                    $itemModal.find('input[name="net_weight"]').val(response.net_weight);
                    $itemModal.find('input[name="gross_weight"]').val(response.gross_weight);
                    $('select[name="tax"]').selectpicker('val', response.taxid).change();
                    $('select[name="tax2"]').selectpicker('val', response.taxid_2).change();
                    $itemModal.find('#group_id').selectpicker('val', response.group_id);
                    $itemModal.find('#item_sub_group').selectpicker('val', response.subgroup_id);
                    $.each(response, function(column, value) {
                        if (column.indexOf('rate_currency_') > -1) {
                            $itemModal.find('input[name="' + column + '"]').val(value);
                        }
                    });

                    $('#custom_fields_items').html(response.custom_fields_html);

                    init_selectpicker();
                    init_color_pickers();
                    init_datepicker();

                    $itemModal.find('.add-title').addClass('hide');
                    $itemModal.find('.edit-title').removeClass('hide');
                    validate_item_form();
                });

            }
        });

        $("body").on("hidden.bs.modal", '#sales_item_modal', function(event) {
            $('#item_select').selectpicker('val', '');
        });

        // get items by group
        $("body").on('change', 'select[name="item_group"]', function() {
            var itemid = $(this).selectpicker('val');
            if (itemid != '') {
                get_subgroup_by_group(itemid)
            }
        });

        // get items by group id
        $("body").on('change', 'select[name="group_id"]', function() {
            var itemid = $(this).selectpicker('val');
            if (itemid != '') {
                get_subgroup_by_group(itemid)
            }
        });

        // get items by Sub group
        $("body").on('change', 'select[name="item_sub_group"]', function() {
            var subgroup_id = $('select[name="item_sub_group"]').val();
            var group_id = $('select[name="item_group"]').val();
            if (subgroup_id != "") {
                get_subgroup_by_subgroup_id(subgroup_id);
                get_items_by_group(group_id, subgroup_id);
            }
        });


        validate_item_form();
    }

    function validate_item_form() {
        // Set validation for invoice item form
        appValidateForm($('#invoice_item_form'), {
            description: 'required',
            rate: {
                required: true,
            }
        }, manage_invoice_items);
    }

    // Get items by item group
    function get_items_by_group(id, subgroup_id) {

        requestGetJSON('invoice_items/get_items_by_group/' + id + '/' + subgroup_id).done(function(response) {

            $('select[name="item_select"]').html(response);
            $('select[name="item_select"]').selectpicker('refresh');

        });
    }

    // Get sub gropus by group
    function get_subgroup_by_group(id) {
        requestGetJSON('invoice_items/get_subgroup_by_group/' + id).done(function(response) {

            $('select[name="item_sub_group"]').html(response);
            $('select[name="item_sub_group"]').selectpicker('refresh');

        });
    }

    // Get sub gropus by group
    function get_subgroup_by_subgroup_id(id) {
        requestGetJSON('invoice_items/edit_subgroup/' + id).done(function(response) {

            if (response.data != "") {
                $('.main textarea[name="long_description"]').val(response.data.description.replace(/(<|&lt;)br\s*\/*(>|&gt;)/g, " "));
            }

        });
    }

    function get_item_to_preview(id) {
        requestGetJSON('invoice_items/get_item_by_id/' + id).done(function(response) {
            clear_item_preview_values();

            $('.main textarea[name="description"]').val(response.description);
            $('.main input[name="capacity"]').val(response.capacity);
            $('.main input[name="hsn_code"]').val(response.hsn_code);
            $('.main input[name="net_weight"]').val(response.net_weight);
            $('.main input[name="gross_weight"]').val(response.gross_weight);

            _set_item_preview_custom_fields_array(response.custom_fields);

            $('.main input[name="quantity"]').val(1);

            var taxSelectedArray = [];
            if (response.taxname && response.taxrate) {
                taxSelectedArray.push(response.taxname + '|' + response.taxrate);
            }
            if (response.taxname_2 && response.taxrate_2) {
                taxSelectedArray.push(response.taxname_2 + '|' + response.taxrate_2);
            }

            $('.main select.tax').selectpicker('val', taxSelectedArray);
            $('.main input[name="unit"]').val(response.unit);

            var $currency = $("body").find('.accounting-template select[name="currency"]');
            var baseCurency = $currency.attr('data-base');
            var selectedCurrency = $currency.find('option:selected').val();
            var $rateInputPreview = $('.main input[name="rate"]');

            if (baseCurency == selectedCurrency) {
                $rateInputPreview.val(response.rate);
            } else {
                var itemCurrencyRate = response['rate_currency_' + selectedCurrency];
                if (!itemCurrencyRate || parseFloat(itemCurrencyRate) === 0) {
                    $rateInputPreview.val(response.rate);
                } else {
                    $rateInputPreview.val(itemCurrencyRate);
                }
            }

            $(document).trigger({
                type: "item-added-to-preview",
                item: response,
                item_type: 'item',
            });
        });
    }

    // Append the added items to the preview to the table as items
    function add_item_to_tables(data, itemid, merge_invoice, bill_expense) {



        // If not custom data passed get from the preview
        data = typeof(data) == 'undefined' || data == 'undefined' ? get_item_preview_values2() : data;
        if (data.description === "" && data.long_description === "" && data.rate === "") {
            return;
        }

        var table_row = '';
        var item_key = $("body").find('tbody .item').length + 1;

        table_row += '<tr class="sortable item" data-merge-invoice="' + merge_invoice + '" data-bill-expense="' + bill_expense + '">';

        table_row += '<td class="dragger">';

        // Check if quantity is number
        if (isNaN(data.qty)) {
            data.qty = 1;
        }

        // Check if rate is number
        if (data.rate === '' || isNaN(data.rate)) {
            data.rate = 0;
        }

        var amount = data.rate * data.qty;

        var tax_name = 'newitems[' + item_key + '][taxname][]';
        $("body").append('<div class="dt-loader"></div>');
        var regex = /<br[^>]*>/gi;
        //     get_taxes_dropdown_template(tax_name, data.taxname).done(function(tax_dropdown) {

        // order input
        table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

        table_row += '<input type="hidden" name="newitems[' + item_key + '][item_id]" value="' + data.item_id + '">';

        table_row += '<input type="hidden" name="newitems[' + item_key + '][main_group_id]" value="' + data.main_group_id + '">';

        table_row += '<input type="hidden" name="newitems[' + item_key + '][sub_group_id]" value="' + data.sub_group_id + '">';

        table_row += '</td>';

        if (is_invoice) {
            table_row += '<td><input type="text" name="newitems[' + item_key + '][kind_of_packages]" value="" class="form-control">';
        }

        table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description.replace(data.capacity + ' - ', '') + '</textarea></td>';

        table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';

        table_row += '<td><input name="newitems[' + item_key + '][hsn_code]" class="form-control" value="' + data.hsn_code + '"></td>';


        var custom_fields = $('tr.main td.custom_field');
        var cf_has_required = false;

        if (custom_fields.length > 0) {

            $.each(custom_fields, function() {

                var cf = $(this).clone();
                var cf_html = '';
                var cf_field = $(this).find('[data-fieldid]');
                var cf_name = 'newitems[' + item_key + '][custom_fields][items][' + cf_field.attr('data-fieldid') + ']';

                if (cf_field.is(':checkbox')) {

                    var checked = $(this).find('input[type="checkbox"]:checked');
                    var checkboxes = cf.find('input[type="checkbox"]');

                    $.each(checkboxes, function(i, e) {
                        var random_key = Math.random().toString(20).slice(2);
                        $(this).attr('id', random_key)
                            .attr('name', cf_name)
                            .next('label').attr('for', random_key);
                        if ($(this).attr('data-custom-field-required') == '1') {
                            cf_has_required = true;
                        }
                    });

                    $.each(checked, function(i, e) {
                        cf.find('input[value="' + $(e).val() + '"]')
                            .attr('checked', true);
                    });

                    cf_html = cf.html();

                } else if (cf_field.is('input') || cf_field.is('textarea')) {
                    if (cf_field.is('input')) {
                        cf.find('[data-fieldid]').attr('value', cf_field.val());
                    } else {
                        cf.find('[data-fieldid]').html(cf_field.val());
                    }
                    cf.find('[data-fieldid]').attr('name', cf_name);
                    if (cf.find('[data-fieldid]').attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }
                    cf_html = cf.html();
                } else if (cf_field.is('select')) {

                    if ($(this).attr('data-custom-field-required') == '1') {
                        cf_has_required = true;
                    }

                    var selected = $(this).find('select[data-fieldid]').selectpicker('val');
                    selected = typeof(selected != 'array') ? new Array(selected) : selected;

                    // Check if is multidimensional by multi-select customfield
                    selected = selected[0].constructor === Array ? selected[0] : selected;

                    var selectNow = cf.find('select');
                    var $wrapper = $('<div/>');
                    selectNow.attr('name', cf_name);

                    var $select = selectNow.clone();
                    $wrapper.append($select);
                    $.each(selected, function(i, e) {
                        $wrapper.find('select option[value="' + e + '"]').attr('selected', true);
                    });

                    cf_html = $wrapper.html();
                }
                table_row += '<td class="custom_field">' + cf_html + '</td>';
            });
        }

        table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

        if (!data.unit || typeof(data.unit) == 'undefined') {
            data.unit = '';
        }

        table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';


        if (is_invoice) {
            table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][net_weight]" value="' + data.net_weight + '" class="form-control net_weight">';
            table_row += '<td class="total-net-weight"></td>';
            table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][gross_weight]" value="' + data.gross_weight + '" class="form-control gross_weight">';
            table_row += '<td class="gross-net-weight"></td>';
        }


        table_row += '</td>';

        table_row += '<td class="rate"><input type="number" data-toggle="tooltip" title="' + app.lang.item_field_not_formatted + '" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][rate]" value="' + data.rate + '" class="form-control"></td>';

        //table_row += '<td class="taxrate">' + tax_dropdown + '</td>';

        table_row += '<td class="amount" align="right">' + format_money(amount, true) + '</td>';

        table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

        table_row += '</tr>';

        $('table.items tbody').append(table_row);

        $(document).trigger({
            type: "item-added-to-table",
            data: data,
            row: table_row
        });

        setTimeout(function() {
            calculate_total();
        }, 15);

        var billed_task = $('input[name="task_id"]').val();
        var billed_expense = $('input[name="expense_id"]').val();

        if (billed_task !== '' && typeof(billed_task) != 'undefined') {
            billed_tasks = billed_task.split(',');
            $.each(billed_tasks, function(i, obj) {
                $('#billed-tasks').append(hidden_input('billed_tasks[' + item_key + '][]', obj));
            });
        }

        if (billed_expense !== '' && typeof(billed_expense) != 'undefined') {
            billed_expenses = billed_expense.split(',');
            $.each(billed_expenses, function(i, obj) {
                $('#billed-expenses').append(hidden_input('billed_expenses[' + item_key + '][]', obj));
            });
        }

        if ($('#item_select').hasClass('ajax-search') && $('#item_select').selectpicker('val') !== '') {
            $('#item_select').prepend('<option></option>');
        }

        init_selectpicker();
        init_datepicker();
        init_color_pickers();
        clear_item_preview_values();
        reorder_items();

        $('body').find('#items-warning').remove();
        $("body").find('.dt-loader').remove();
        $('#item_select').selectpicker('val', '');
        $('#item_group').selectpicker('val', '');
        $('#item_sub_group').selectpicker('val', '');
        $('.tax.main-tax').selectpicker('val', '');
        $('.main').find('textarea').val('');
        if (cf_has_required && $('.invoice-form').length) {
            validate_invoice_form();
        } else if (cf_has_required && $('.estimate-form').length) {
            validate_estimate_form();
        } else if (cf_has_required && $('.proposal-form').length) {
            validate_proposal_form();
        } else if (cf_has_required && $('.credit-note-form').length) {
            validate_credit_note_form();
        }

        return true;

        // });
    }

    // Get the preview main values
    function get_item_preview_values2() {
        var response = {};
        response.description = $('.main select[name="item_select"] option:selected').text();
        response.long_description = $('.main textarea[name="long_description"]').val();
        response.capacity = $('.main input[name="capacity"]').val();
        response.qty = $('.main input[name="quantity"]').val();
        response.taxname = $('.main select.tax').selectpicker('val');
        response.rate = $('.main input[name="rate"]').val();
        response.unit = $('.main input[name="unit"]').val();
        response.main_group_id = $('.main #item_group').val();
        response.sub_group_id = $('.main #item_sub_group').val();
        response.hsn_code = $('.main input[name="hsn_code"]').val();
        response.net_weight = $('.main input[name="net_weight"]').val();
        response.gross_weight = $('.main input[name="gross_weight"]').val();
        response.item_id = $('.main select[name="item_select"] option:selected').val();
        return response;
    }
</script>