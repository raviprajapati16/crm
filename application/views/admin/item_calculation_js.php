<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {

        // Add new dynamic field
        $('#add-dynamic-field').click(function() {
            var dynamicFieldIndex = $('.dynamic-field-row').length + 1;
            var fieldHtml = '<div class="dynamic-field-row" data-index="' + dynamicFieldIndex + '">' +
                '<div class="dynamic-field-label">' +
                '<input type="hidden" name="dynamic_fields[' + dynamicFieldIndex + '][id]" value = "">' +
                '<input type="text" name="dynamic_fields[' + dynamicFieldIndex + '][label]" class="form-control dynamic-label" placeholder="Label" required>' +
                '</div>' +
                '<div class="dynamic-field-amount">' +
                '<input type="number" step="0.01" name="dynamic_fields[' + dynamicFieldIndex + '][amount]" class="form-control dynamic-amount" placeholder="Amount" required>' +
                '</div>' +
                '<div class="dynamic-field-remove">' +
                '<button type="button" class="btn btn-danger btn-sm remove-dynamic-field">' +
                '<i class="fa fa-trash"></i>' +
                '</button>' +
                '</div>' +
                '</div>';

            $('#dynamic-fields-container').append(fieldHtml);
            dynamicFieldIndex++
            validate_proposal_form();
        });

        // Remove dynamic field
        $(document).on('click', '.remove-dynamic-field', function() {
            $(this).closest('.dynamic-field-row').remove();
            calculate_total();
            validate_proposal_form();
        });

        $(document).on('focusout', '.dynamic-amount,.dynamic-label', function() {
            calculate_total();
        });

        $(document).on('change', 'select[name="tax_id"]', function() {
            calculate_total();
        });

        $(document).on('change', '#item_select', function() {
            stock_check($(this).val());
        });

        $(document).on('click', '.add-product-btn', function() {
            $('.stock-label').remove();
        });

    });

    function stock_check(id) {
        var rel_type = "";
        var rel_id = "";
        if ($('body').hasClass('proposals')) {
            rel_type = 'proposal';
        } else if ($('body').hasClass('invoices')) {
            rel_type = 'invoice';
        }
        $('.stock-label').remove();
        if (id != '' && rel_type != '') {
            $.ajax({
                url: '<?php echo admin_url('misc/stock_check'); ?>',
                type: 'post',
                data: {
                    item_id: id,
                    rel_type: rel_type,
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#item_select').closest('.form-group').append('<span class="text ' + response.color_class + ' stock-label"> Available Stock : ' + response.stock + '</span>');
                    }
                }
            })
        }
    }

    function calculate_total() {
        if ($('body').hasClass('no-calculate-total')) {
            return false;
        }

        var calculated_tax,
            taxrate,
            item_taxes,
            row,
            _amount,
            net_weight,
            gross_weight,
            tax_row,
            _tax_name,
            taxes = {},
            taxes_rows = [],
            subtotal = 0,
            total = 0,
            quantity = 1,
            total_discount_calculated = 0,
            rows = $('.table.has-calculations tbody tr.item'),
            discount_area = $('#discount_area'),
            adjustment = $('input[name="adjustment"]').val(),
            discount_percent = $('input[name="discount_percent"]').val(),
            discount_fixed = $('input[name="discount_total"]').val(),
            discount_total_type = $('.discount-total-type.selected'),
            discount_type = $('select[name="discount_type"]').val();

        $('.tax-area').remove();

        $.each(rows, function() {

            quantity = $(this).find('[data-quantity]').val();
            if (quantity === '') {
                quantity = 1;
                $(this).find('[data-quantity]').val(1);
            }

            _amount = accounting.toFixed($(this).find('td.rate input').val() * quantity, app.options.decimal_places);
            _amount = parseFloat(_amount);


            $(this).find('td.amount').html(format_money(_amount, true));
            subtotal += _amount;
            row = $(this);
            // item_taxes = $(this).find('select.tax').selectpicker('val');
            // if (item_taxes) {
            //     $.each(item_taxes, function(i, taxname) {
            //         taxrate = row.find('select.tax [value="' + taxname + '"]').data('taxrate');
            //         calculated_tax = (_amount / 100 * taxrate);
            //         if (!taxes.hasOwnProperty(taxname)) {
            //             if (taxrate != 0) {
            //                 _tax_name = taxname.split('|');
            //                 tax_row = '<tr class="tax-area"><td>' + _tax_name[0] + '(' + taxrate + '%)</td><td id="tax_id_' + slugify(taxname) + '"></td></tr>';
            //                 $(discount_area).after(tax_row);
            //                 taxes[taxname] = calculated_tax;
            //             }
            //         } else {
            //             // Increment total from this tax
            //             taxes[taxname] = taxes[taxname] += calculated_tax;
            //         }
            //     });
            // }
        });



        // $.each(taxes, function(taxname, total_tax) {
        //     if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
        //         total_tax_calculated = (total_tax * discount_percent) / 100;
        //         total_tax = (total_tax - total_tax_calculated);
        //     } else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
        //         var t = (discount_fixed / subtotal) * 100;
        //         total_tax = (total_tax - (total_tax * t) / 100);
        //     }

        //     total += total_tax;
        //     total_tax = format_money(total_tax);
        //     $('#tax_id_' + slugify(taxname)).html(total_tax);
        // });

        total = (total + subtotal);

        // Discount Before Tax
        if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-percent')) {
            total_discount_calculated = (subtotal * discount_percent) / 100;
            total = total - total_discount_calculated;
        } else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'before_tax' && discount_total_type.hasClass('discount-type-fixed')) {
            total_discount_calculated = parseFloat(discount_fixed);
            total = total - total_discount_calculated;
        }


        // dynamic amounts
        if ($('.dynamic-amount').length > 0) {
            $('.dynamic-amount').each(function() {
                var label = $(this).closest('.dynamic-field-row').find('.dynamic-label').val();
                if (label != '' && label != null) {
                    var dynamic_amount = parseFloat($(this).val());
                    if (!isNaN(dynamic_amount)) {
                        total = total + dynamic_amount;
                    }
                }
            });
        }

        //Taxable Amount
        $('.taxable_amount').text(format_money(total));
        $('input[name="taxable_amount"]').val(total);

        // Calculate taxes
        var tax_amount = 0;
        var tax_rate = parseFloat($('select[name="tax_id"] :selected').attr('data-taxrate'));
        if (tax_rate != 0) {
            tax_amount = parseFloat((total * tax_rate) / 100);
            total += tax_amount;
        }
        $('.total_tax').text(format_money(tax_amount));
        $('input[name="total_tax"]').val(tax_amount);



        // Discount After Tax
        if ((discount_percent !== '' && discount_percent != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-percent')) {
            total_discount_calculated = (total * discount_percent) / 100;
            total = total - total_discount_calculated;
        } else if ((discount_fixed !== '' && discount_fixed != 0) && discount_type == 'after_tax' && discount_total_type.hasClass('discount-type-fixed')) {
            total_discount_calculated = discount_fixed;
            total = total - total_discount_calculated;
        }
        adjustment = parseFloat(adjustment);

        // Check if adjustment not empty
        if (!isNaN(adjustment)) {
            total = total + adjustment;
        }

        var discount_html = '-' + format_money(total_discount_calculated);
        $('input[name="discount_total"]').val(accounting.toFixed(total_discount_calculated, app.options.decimal_places));

        // Append, format to html and display
        $('.discount-total').html(discount_html);
        $('.adjustment').html(format_money(adjustment));
        $('.subtotal').html(format_money(subtotal) + hidden_input('subtotal', accounting.toFixed(subtotal, app.options.decimal_places)));
        $('.total').html(format_money(total) + hidden_input('total', accounting.toFixed(total, app.options.decimal_places)));

        // net weight
        if ($('.net_weight').length > 0) {
            var total_net_weight = 0;
            $('.net_weight').each(function() {
                $(this).closest('tr').find('.total-net-weight').text('0');
                var net_weight = parseFloat($(this).val());
                var qty = parseFloat($(this).closest('tr').find('[data-quantity]').val());
                net_weight = net_weight * qty;
                if (!isNaN(net_weight)) {
                    $(this).closest('tr').find('.total-net-weight').text(net_weight);
                    total_net_weight = total_net_weight + net_weight;
                }
            });
            $('.total_net_weight').val(total_net_weight);
        }


        if ($('.gross_weight').length > 0) {
            var total_gross_weight = 0;
            $('.gross_weight').each(function() {
                var gross_weight = parseFloat($(this).val());
                var qty = parseFloat($(this).closest('tr').find('[data-quantity]').val());
                gross_weight = gross_weight * qty;
                if (!isNaN(gross_weight)) {
                    $(this).closest('tr').find('.total-gross-weight').text(gross_weight);
                    total_gross_weight = total_gross_weight + gross_weight;
                }
            });
            $('.total_gross_weight').val(total_gross_weight);
        }


        $(document).trigger('sales-total-calculated');
    }
</script>