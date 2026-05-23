<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content accounting-template purchase">
    <div class="row">
      <?php
      if (isset($purchase)) {
        echo form_hidden('isedit', $purchase->id);
      }
      $vendor_id = "";
      if (isset($purchase) || $this->input->get('vendor_id')) {
        if ($this->input->get('vendor_id')) {
          $vendor_id = $this->input->get('vendor_id');
        } else {
          $vendor_id = $purchase->vendor_id;
        }
      }
      ?>
      <?php echo form_open($this->uri->uri_string(), array('id' => 'purchase-form', 'class' => '_transaction_form purchase-form')); ?>
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
              <?php if (isset($purchase)) { ?>
                <div class="col-md-12">
                  <?php echo format_purchase_status($purchase->status); ?>
                  <?php $data['purchase_status'] = $purchase->status; ?>
                </div>
                <div class="clearfix"></div>
                <hr />
              <?php } ?>
              <div class="col-md-6 border-right">
                <div class="form-group">
                  <label for="purchase_number">Purchase Number</label>
                  <div class="input-group">
                    <span class="input-group-addon" id="purchase_prefix"><?= (isset($purchase) && $purchase->purchase_number_prefix) ? $purchase->purchase_number_prefix : purchase_number_prefix() ?></span>
                    <input type="number" id="purchase_number" name="purchase_number" class="form-control" value="<?= (isset($purchase) && $purchase->purchase_number) ? $purchase->purchase_number : get_next_number("purchase", purchase_number_prefix()) ?>">
                  </div>
                </div>
                <?php $value = (isset($purchase) ? $purchase->subject : ''); ?>
                <?php $attrs = (isset($purchase) ? array() : array('autofocus' => true)); ?>
                <?php echo render_input('subject', 'Subject', $value, 'text', $attrs); ?>
                <div class="form-group select-placeholder" id="vendor_id_wrapper">
                  <label for="vendor_id"><span class="vendor_id_label">Vendor</span></label>
                  <div id="rel_id_select">
                    <select name="vendor_id" id="vendor_id" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                      <?php if ($vendor_id != '') {
                        $rel_data = get_relation_data("vendor", $vendor_id);
                        $rel_val = get_relation_values($rel_data, "vendor");
                        echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                      } ?>
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? _d($purchase->date) : _d(date('Y-m-d'))) ?>
                    <?php echo render_date_input('date', 'Date', $value); ?>
                  </div>

                  <?php
                  $selected = '';
                  $currency_attr = array('data-show-subtext' => true);
                  foreach ($currencies as $currency) {
                    if ($currency['isdefault'] == 1) {
                      $currency_attr['data-base'] = $currency['id'];
                    }
                    if (isset($purchase)) {
                      if ($currency['id'] == $purchase->currency) {
                        $selected = $currency['id'];
                      }
                    } else {
                      if ($currency['isdefault'] == 1) {
                        $selected = $currency['id'];
                      }
                    }
                  }
                  ?>

                  <div class="col-md-6">
                    <?php
                    echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'Currency', $selected, $currency_attr);
                    ?>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                      <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value="" selected><?php echo _l('no_discount'); ?></option>
                        <option value="before_tax" <?php
                                                    if (isset($purchase)) {
                                                      if ($purchase->discount_type == 'before_tax') {
                                                        echo 'selected';
                                                      }
                                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                        <option value="after_tax" <?php if (isset($purchase)) {
                                                    if ($purchase->discount_type == 'after_tax') {
                                                      echo 'selected';
                                                    }
                                                  } ?>><?php echo _l('discount_type_after_tax'); ?></option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->loading_place : ''); ?>
                    <?php echo render_input('loading_place', 'Place Of Loading', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->discharge_place : ''); ?>
                    <?php echo render_input('discharge_place', 'Place Of Discharge', $value); ?>
                  </div>

                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->payment_term : ''); ?>
                    <?php echo render_input('payment_term', 'Payment Term', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->shipment_term : ''); ?>
                    <?php echo render_input('shipment_term', 'Shipment Term', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->notes : ''); ?>
                    <?php echo render_textarea('notes', 'Notes', $value); ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group select-placeholder">
                      <label for="status" class="control-label">Status</label>
                      <select name="status" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <?php foreach ($statuses as $status) { ?>
                          <option value="<?php echo $status['id']; ?>" <?php if ((isset($purchase) && $purchase->status == $status['id'])) {
                                                                          echo 'selected';
                                                                        } ?>><?= $status['name']; ?></option>
                        <?php } ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <?php
                    $i = 0;
                    $selected = '';
                    foreach ($staff as $member) {
                      if (isset($purchase)) {
                        if ($purchase->assigned == $member['staffid']) {
                          $selected = $member['staffid'];
                        }
                      }
                      $i++;
                    }
                    echo render_select('assigned', $staff, array('staffid', array('firstname', 'lastname')), 'Assigned', $selected);
                    ?>
                  </div>
                </div>
                <?php $value = (isset($purchase) ? $purchase->purchase_to : ''); ?>
                <?php echo render_input('purchase_to', 'To', $value); ?>
                <?php $value = (isset($purchase) ? $purchase->address : ''); ?>
                <?php echo render_textarea('address', 'Address', $value); ?>
                <div class="row">
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->city : ''); ?>
                    <?php echo render_input('city', 'billing_city', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->state : ''); ?>
                    <?php echo render_input('state', 'billing_state', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $countries = get_all_countries(); ?>
                    <?php $selected = (isset($purchase) ? $purchase->country : ''); ?>
                    <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $selected); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->zip : ''); ?>
                    <?php echo render_input('zip', 'billing_zip', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->email : ''); ?>
                    <?php echo render_input('email', 'Email', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <?php $value = (isset($purchase) ? $purchase->phone : ''); ?>
                    <?php echo render_input('phone', 'Phone', $value); ?>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <?php
                      $value = "";
                      if (isset($purchase)) {
                        $rel_data = get_relation_data('vendor', $purchase->vendor_id);
                        $value = $rel_data->gst_in;
                      }
                      ?>
                      <label for="gst_number" class="control-label">GST Number</label>
                      <input type="text" id="gst_number" class="form-control" value="<?= $value ?>" disabled>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="btn-bottom-toolbar bottom-transaction text-right">
              <button class="btn btn-info mleft5 purchase-form-submit transaction-submit" type="button">
                <?php echo _l('submit'); ?>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="panel_s">
          <?php
          $data['dynamic_amount_fields'] = get_dynamic_amount_fields('purchase', $purchase->id)
          ?>
          <?php $this->load->view('admin/purchase/_add_edit_items', $data); ?>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function() {
    var isEdit = $('input[name="isedit"]').length;
    init_currency();
    init_ajax_search('vendor', '#vendor_id.ajax-search', undefined, admin_url + 'misc/get_relation_data');

    validate_purchase_form();

    $('input[name="purchase_number"]').on('focusout', function() {
      check_purchase_number();
    });

    $('body').on('change', '#vendor_id', function() {
      if ($(this).val() != '') {
        $.get(admin_url + 'purchase/get_relation_data_values/' + $(this).val(), function(response) {
          $('input[name="purchase_to"]').val(response.to);
          $('textarea[name="address"]').val(response.address);
          $('input[name="email"]').val(response.email);
          $('input[name="phone"]').val(response.phone);
          $('input[name="city"]').val(response.city);
          $('input[name="state"]').val(response.state);
          $('input[name="zip"]').val(response.zip);
          $('#gst_number').val(response.gst_in);
          $('select[name="country"]').selectpicker('val', response.country);
        }, 'json');
      }
    });

    if (!isEdit) {
      $('#vendor_id').trigger('change');
    }

  });


  function validate_purchase_form() {
    let dynamicRules = {};
    $('[name^="dynamic_fields"]').each(function() {
      const name = $(this).attr('name');
      if (name.includes('[label]') || name.includes('[amount]')) {
        dynamicRules[name] = 'required';
      }
    });
    $($('#purchase-form')).appFormValidator({
      rules: {
        purchase_number: 'required',
        subject: 'required',
        purchase_to: 'required',
        vendor_id: 'required',
        date: 'required',
        type: 'required',
        email: {
          email: true,
          required: true
        },
        currency: 'required',
        loading_place: 'required',
        discharge_place: 'required',
        payment_term: 'required',
        shipment_term: 'required',
        status: 'required',
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

  function check_purchase_number() {
    $('#purchase_number').closest('.form-group').find('.text-danger').remove();
    $.ajax({
      url: "<?= admin_url("misc/check_invoice_number") ?>",
      type: 'POST',
      data: {
        type: 'purchase',
        number: $('#purchase_number').val(),
        prefix: $('#purchase_prefix').text(),
        id: "<?= isset($purchase) ? $purchase->id : "" ?>"
      },
      dataType: 'json',
      success: function(response) {
        if (!response.success) {
          $('#purchase_number').closest('.form-group').append('<span class="text-danger">' + response.message + '</span>');
        }
      }
    });
  }
</script>
<?php
$this->load->view('admin/item_calculation_js');
?>
</body>

</html>