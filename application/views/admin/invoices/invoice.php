<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(), array('id' => 'invoice-form', 'class' => '_transaction_form invoice-form'));
			if (isset($invoice)) {
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/invoices/invoice_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function() {
		init_editor('textarea[name="terms"]');
		validate_invoice_form();
		// Init accountacy currency symbol
		init_currency();
		// Project ajax search
		init_ajax_project_search_by_customer_id();
		// Maybe items ajax search
		init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');

		$('input[name="number"]').on('focusout', function() {
			check_invoice_number();
		})

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

		<?php if (!isset($invoice)) { ?>
			$('input[type=radio][name=type][value="0"]').trigger('change');
		<?php } ?>
	});

	function get_item_preview_values() {
		var response = {};
		response.description = $('.main textarea[name="description"]').val();
		response.long_description = $('.main textarea[name="long_description"]').val();
		response.qty = $('.main input[name="quantity"]').val();
		response.taxname = $('.main select.tax').selectpicker('val');
		response.rate = $('.main input[name="rate"]').val();
		response.unit = $('.main input[name="unit"]').val();
		response.hsn_code = $('.main input[name="hsn_code"]').val();
		return response;
	}

	function add_item_to_table(data, itemid, merge_invoice, bill_expense) {

		// If not custom data passed get from the preview
		data = typeof(data) == 'undefined' || data == 'undefined' ? get_item_preview_values() : data;
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
		get_taxes_dropdown_template(tax_name, data.taxname).done(function(tax_dropdown) {

			// order input
			table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

			table_row += '</td>';

			table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

			table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';

			table_row += '<td><input type="text" name="newitems[' + item_key + '][hsn_code]" class="form-control hsn_code" value="' + data.hsn_code + '" /></td>';

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

			table_row += '</td>';

			table_row += '<td class="rate"><input type="number" data-toggle="tooltip" title="' + app.lang.item_field_not_formatted + '" onblur="calculate_total();" onchange="calculate_total();" name="newitems[' + item_key + '][rate]" value="' + data.rate + '" class="form-control"></td>';

			table_row += '<td class="taxrate">' + tax_dropdown + '</td>';

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

		});

		return false;
	}

	function check_invoice_number() {
		$('#number').closest('.form-group').find('.text-danger').remove();
		$.ajax({
			url: "<?= admin_url("misc/check_invoice_number") ?>",
			type: 'POST',
			data: {
				type: 'invoice',
				number: $('#number').val(),
				prefix: $('#invoice_prefix').text(),
				id: "<?= isset($invoice) ? $invoice->id : "" ?>"
			},
			dataType: 'json',
			success: function(response) {
				if (!response.success) {
					$('#number').closest('.form-group').append('<span class="text-danger">' + response.message + '</span>');
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

	function validate_invoice_form(selector) {
		selector = typeof(selector) == 'undefined' ? '#invoice-form' : selector;
		let dynamicRules = {};
		$('[name^="dynamic_fields"]').each(function() {
			const name = $(this).attr('name');
			if (name.includes('[label]') || name.includes('[amount]')) {
				dynamicRules[name] = 'required';
			}
		});
		appValidateForm($(selector), {
			clientid: {
				required: {
					depends: function() {
						var customerRemoved = $('select#clientid').hasClass('customer-removed');
						return !customerRemoved;
					}
				}
			},
			date: 'required',
			currency: 'required',
			repeat_every_custom: {
				min: 1
			},
			number: {
				required: true,
			},
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
		});
	}
</script>
<?php
$this->load->view('admin/item_calculation_js');
?>
</body>

</html>