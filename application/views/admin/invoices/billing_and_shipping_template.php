<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="billing_and_shipping_details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <?php
                    $countries                = get_all_countries();
                    $location_select_attrs    = ['data-none-selected-text' => _l('dropdown_non_selected_tex')];
                    $selected_country         = (isset($invoice) ? $invoice->billing_country : '');
                    $selected_state           = (isset($invoice) ? $invoice->billing_state : '');
                    $selected_city            = (isset($invoice) ? $invoice->billing_city : '');
                    $billing_location         = build_location_dropdown_data($selected_country, $selected_state, $selected_city);
                    $state_options            = $billing_location['states'];
                    $city_options             = $billing_location['cities'];

                    if (!empty($selected_state)) {
                        $state_found = false;
                        foreach ($state_options as $state_row) {
                            if ($state_row['state'] === $selected_state) {
                                $state_found = true;
                                break;
                            }
                        }
                        if (!$state_found) {
                            $state_options[] = ['state' => $selected_state];
                        }
                    }
                    if (!empty($selected_city)) {
                        $city_found = false;
                        foreach ($city_options as $city_row) {
                            if ($city_row['city'] === $selected_city) {
                                $city_found = true;
                                break;
                            }
                        }
                        if (!$city_found) {
                            $city_options[] = ['city' => $selected_city];
                        }
                    }

                    $state_wrapper_class = !empty($selected_country) ? 'invoice-location-state-wrapper location-group-invoice-billing' : 'invoice-location-state-wrapper location-group-invoice-billing hide';
                    $city_wrapper_class  = (!empty($selected_country) && !empty($selected_state) && country_uses_city_dropdown($selected_country)) ? 'invoice-location-city-wrapper location-group-invoice-billing' : 'invoice-location-city-wrapper location-group-invoice-billing hide';
                    ?>
                    <div class="col-md-12">
                        <div id="billing_details">
                            <?php $value = (isset($invoice) ? $invoice->billing_street : ''); ?>
                            <?php echo render_textarea('billing_street', 'billing_street', $value); ?>
                            <?php echo render_select('billing_country', $countries, ['country_id', ['short_name'], 'iso2'], 'billing_country', $selected_country, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-billing',
                                'data-location-role'  => 'country',
                            ])); ?>
                            <?php echo render_select('billing_state', $state_options, ['state', 'state'], 'billing_state', $selected_state, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-billing',
                                'data-location-role'  => 'state',
                            ]), [], $state_wrapper_class); ?>
                            <?php echo render_select('billing_city', $city_options, ['city', 'city'], 'District', $selected_city, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-billing',
                                'data-location-role'  => 'city',
                            ]), [], $city_wrapper_class); ?>
                            <?php $value = (isset($invoice) ? $invoice->billing_zip : ''); ?>
                            <?php echo render_input('billing_zip', 'billing_zip', $value); ?>
                        </div>
                    </div>
                    <!-- <div class="col-md-12">
                        <hr />
                        <a href="#" class="pull-right" id="get_shipping_from_customer_profile" data-placement="left" data-toggle="tooltip" title="<?php echo _l('get_shipping_from_customer_profile'); ?>"><i class="fa fa-user"></i></a>
                        <div class="clearfix"></div>
                      <div class="form-group no-mbot">
                            <div class="checkbox checkbox-primary checkbox-inline">
                            <input type="checkbox" id="include_shipping" name="include_shipping" <?php if(isset($invoice) && $invoice->include_shipping == 1){echo 'checked';} ?>>
                            <label for="include_shipping"><?php echo _l('shipping_address'); ?></label>
                        </div>
                      </div>
                        <div id="shipping_details" class="<?php if((isset($invoice) && $invoice->include_shipping != 1) || !isset($invoice)){echo 'hide';} ?>">
                          <div class="form-group">
                                <div class="checkbox checkbox-primary checkbox-inline">
                                <input type="checkbox" id="show_shipping_on_invoice" name="show_shipping_on_invoice" <?php if((isset($invoice) && $invoice->show_shipping_on_invoice == 1) || !isset($invoice)){echo 'checked';} ?>>
                                <label for="show_shipping_on_invoice"><?php echo _l('show_shipping_on_invoice'); ?></label>
                            </div>
                          </div>
                            <?php $value = (isset($invoice) ? $invoice->shipping_street : ''); ?>
                            <?php echo render_textarea('shipping_street','shipping_street',$value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_city : ''); ?>
                            <?php echo render_input('shipping_city','shipping_city',$value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_state : ''); ?>
                            <?php echo render_input('shipping_state','shipping_state',$value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_zip : ''); ?>
                            <?php echo render_input('shipping_zip','shipping_zip',$value); ?>
                            <?php $selected = (isset($invoice) ? $invoice->shipping_country : ''); ?>
                            <?php echo render_select('shipping_country',$countries,array('country_id',array('short_name'),'iso2'),'shipping_country',$selected); ?>
                        </div>
                    </div> -->
                </div>
            </div>
            <div class="modal-footer modal-not-full-width">
                <a href="#" class="btn btn-info save-shipping-billing invoice-billing-apply"><?php echo _l('apply'); ?></a>
            </div>
        </div>
    </div>
</div>
<script>
window._invoiceBillingPreviewCache = window._invoiceBillingPreviewCache || {};
window._invoiceApplyingBilling = false;

window.getInvoiceBillToSpan = function(fieldName) {
    var map = {
        billing_street: '#invoice_bill_to_street',
        billing_city: '#invoice_bill_to_city',
        billing_state: '#invoice_bill_to_state',
        billing_country: '#invoice_bill_to_country',
        billing_zip: '#invoice_bill_to_zip'
    };

    if (map[fieldName]) {
        var $byId = $(map[fieldName]);
        if ($byId.length) {
            return $byId;
        }
    }

    return $('#invoice-form .' + fieldName).first();
};

window.captureInvoiceBillingSelectDisplay = function($select) {
    if (!$select || !$select.length) {
        return '';
    }

    var value = '';
    if ($select.hasClass('selectpicker')) {
        try {
            var picked = $select.selectpicker('val');
            if (picked !== null && typeof picked !== 'undefined' && picked !== '') {
                value = $.isArray(picked) ? (picked.length ? String(picked[0]) : '') : String(picked);
            }
        } catch (e) {}
    }

    if (!value) {
        value = $select.val() || '';
    }

    if (!value) {
        var $selected = $select.find('option:selected');
        if ($selected.length) {
            value = $selected.val() || $.trim($selected.text());
        }
    }

    var $bs = $select.closest('.bootstrap-select');
    if (!value && $bs.length) {
        value = $.trim($bs.find('.filter-option-inner-inner').text());
    }

    var noneSelectedText = $select.attr('data-none-selected-text') || '';
    if (value === noneSelectedText) {
        value = '';
    }

    return value ? String(value).trim() : '';
};

window.readInvoiceBillingSelectValueFresh = function($select) {
    return captureInvoiceBillingSelectDisplay($select);
};

window.readInvoiceBillingSelectValue = function($select) {
    if (!$select || !$select.length) {
        return '';
    }

    var fieldName = $select.attr('name') || '';
    if (fieldName && window._invoiceBillingPreviewCache[fieldName]) {
        return String(window._invoiceBillingPreviewCache[fieldName]);
    }

    var stored = $select.data('invoice-preview-value');
    if (stored) {
        return String(stored);
    }

    return captureInvoiceBillingSelectDisplay($select);
};

window.cacheInvoiceBillingField = function(fieldName, value) {
    if (fieldName && value && String(value).trim() !== '' && String(value).trim() !== '--') {
        window._invoiceBillingPreviewCache[fieldName] = String(value).trim();
    }
};

window.preserveInvoiceBillToSpanValues = function() {
    ['billing_street', 'billing_city', 'billing_state', 'billing_country', 'billing_zip'].forEach(function(fieldName) {
        var text = $.trim(getInvoiceBillToSpan(fieldName).text());
        if (text && text !== '--') {
            cacheInvoiceBillingField(fieldName, text);
        }
    });
};

window.snapshotInvoiceBillingPreviewFields = function() {
    var $modal = $('#billing_and_shipping_details');
    if (!$modal.length) {
        return;
    }

    preserveInvoiceBillToSpanValues();

    cacheInvoiceBillingField('billing_street', $.trim($modal.find('textarea[name="billing_street"]').val() || ''));
    cacheInvoiceBillingField('billing_zip', $.trim($modal.find('input[name="billing_zip"]').val() || ''));

    var $countrySelect = $modal.find('select[name="billing_country"]');
    var country = $countrySelect.find('option:selected').data('subtext') || captureInvoiceBillingSelectDisplay($countrySelect);
    cacheInvoiceBillingField('billing_country', country);

    ['billing_state', 'billing_city'].forEach(function(fieldName) {
        var $select = $modal.find('select[name="' + fieldName + '"]');
        var value = captureInvoiceBillingSelectDisplay($select);
        if (!value) {
            value = window._invoiceBillingPreviewCache[fieldName] || '';
        }
        if (value) {
            $select.data('invoice-preview-value', value);
            cacheInvoiceBillingField(fieldName, value);
        }
    });
};

window.renderInvoiceBillToAddress = function() {
    var cache = window._invoiceBillingPreviewCache;
    var street = cache.billing_street || '--';
    var city = cache.billing_city || '--';
    var state = cache.billing_state || '--';
    var country = cache.billing_country || '--';
    var zip = cache.billing_zip || '--';

    getInvoiceBillToSpan('billing_street').html(street !== '--' ? String(street).replace(/(?:\r\n|\r|\n)/g, '<br />') : '--');
    getInvoiceBillToSpan('billing_city').text(city);
    getInvoiceBillToSpan('billing_state').text(state);
    getInvoiceBillToSpan('billing_country').text(country);
    getInvoiceBillToSpan('billing_zip').text(zip);
};

window.updateInvoiceBillToAddress = function() {
    var $modal = $('#billing_and_shipping_details');
    if (!$modal.length || window._invoiceApplyingBilling) {
        return;
    }

    snapshotInvoiceBillingPreviewFields();
    renderInvoiceBillToAddress();
};

window.applyInvoiceBillingAddress = function() {
    window._invoiceApplyingBilling = true;
    snapshotInvoiceBillingPreviewFields();
    renderInvoiceBillToAddress();
    $('#billing_and_shipping_details').modal('hide');
    setTimeout(function() {
        window._invoiceApplyingBilling = false;
    }, 300);
};

window.initInvoiceBillingLocationDropdowns = function() {
    if (typeof jQuery === 'undefined' || $('select[data-location-group="invoice-billing"][data-location-role="country"]').length === 0) {
        return;
    }

    if (window._invoiceBillingLocationInitialized) {
        toggleInvoiceLocationFields('invoice-billing');
        return;
    }
    window._invoiceBillingLocationInitialized = true;

    <?php if (!empty($selected_state)) { ?>
    cacheInvoiceBillingField('billing_state', <?php echo json_encode($selected_state); ?>);
    $('#billing_and_shipping_details select[name="billing_state"]').data('invoice-preview-value', <?php echo json_encode($selected_state); ?>);
    <?php } ?>
    <?php if (!empty($selected_city)) { ?>
    cacheInvoiceBillingField('billing_city', <?php echo json_encode($selected_city); ?>);
    $('#billing_and_shipping_details select[name="billing_city"]').data('invoice-preview-value', <?php echo json_encode($selected_city); ?>);
    <?php } ?>
    <?php if (isset($invoice) && !empty($invoice->billing_street)) { ?>
    cacheInvoiceBillingField('billing_street', <?php echo json_encode($invoice->billing_street); ?>);
    <?php } ?>
    <?php if (isset($invoice) && !empty($invoice->billing_zip)) { ?>
    cacheInvoiceBillingField('billing_zip', <?php echo json_encode($invoice->billing_zip); ?>);
    <?php } ?>
    <?php if (isset($invoice) && !empty($invoice->billing_country)) { ?>
    cacheInvoiceBillingField('billing_country', <?php echo json_encode(get_country_short_name($invoice->billing_country)); ?>);
    <?php } ?>

    var _invoiceLocationSuppressChange = false;

    function isIndiaCountry(countryId) {
        return countryId && String(countryId) === String(typeof INDIA_COUNTRY_ID !== 'undefined' ? INDIA_COUNTRY_ID : 0);
    }

    window.toggleInvoiceLocationFields = function(group) {
        var $country  = $('select[data-location-group="' + group + '"][data-location-role="country"]');
        var $state    = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var countryId = $country.selectpicker('val') || $country.val();
        var stateVal  = $state.selectpicker('val') || $state.val();

        $('.invoice-location-state-wrapper.location-group-' + group).toggleClass('hide', !countryId);

        var showCity = countryId && stateVal && isIndiaCountry(countryId);
        $('.invoice-location-city-wrapper.location-group-' + group).toggleClass('hide', !showCity);
    };

    function appendInvoiceLocationOption($select, value) {
        if (!value) {
            return;
        }
        if ($select.find('option').filter(function() { return $(this).val() === value; }).length === 0) {
            $select.append($('<option>', { value: value, text: value }));
        }
    }

    window.refreshInvoiceLocationDropdown = function(group, type, preselectState, preselectCity, onComplete) {
        var $country  = $('select[data-location-group="' + group + '"][data-location-role="country"]');
        var $state    = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var $city     = $('select[data-location-group="' + group + '"][data-location-role="city"]');
        var countryId = $country.selectpicker('val') || $country.val();

        function finishLocationUpdate() {
            toggleInvoiceLocationFields(group);
            if (typeof onComplete === 'function') {
                onComplete();
            } else if ($('#invoice-form').length && typeof updateInvoiceBillToAddress === 'function') {
                updateInvoiceBillToAddress();
            }
        }

        if (type === 'state') {
            toggleInvoiceLocationFields(group);
            $state.empty().append('<option value=""></option>');
            $city.empty().append('<option value=""></option>');
            $state.selectpicker('refresh');
            $city.selectpicker('refresh');
        } else {
            toggleInvoiceLocationFields(group);
            $city.empty().append('<option value=""></option>');
            $city.selectpicker('refresh');
        }

        if (!countryId) {
            finishLocationUpdate();
            return;
        }

        var postData = {
            type: type,
            country_id: countryId
        };

        if (typeof csrfData !== 'undefined') {
            postData[csrfData.token_name] = csrfData.hash;
        }

        if (type === 'city') {
            postData.state = $state.selectpicker('val') || $state.val();
            if (!postData.state || !isIndiaCountry(countryId)) {
                finishLocationUpdate();
                return;
            }
        }

        $.ajax({
            url: admin_url + 'leads/get_state_city',
            method: 'POST',
            data: postData,
            dataType: 'json'
        }).done(function(result) {
            if (!result || !result.success) {
                finishLocationUpdate();
                return;
            }

            var $target = (type === 'state') ? $state : $city;
            var key     = (type === 'state') ? 'state' : 'city';
            var pre     = (type === 'state') ? preselectState : preselectCity;

            $.each(result.data, function(i, item) {
                if (item[key]) {
                    $target.append(new Option(item[key], item[key]));
                }
            });

            if (pre) {
                appendInvoiceLocationOption($target, pre);
                $target.selectpicker('val', pre);
                $target.data('invoice-preview-value', pre);
                cacheInvoiceBillingField($target.attr('name'), pre);
            }

            $target.selectpicker('refresh');

            if (type === 'state' && preselectState && isIndiaCountry(countryId)) {
                refreshInvoiceLocationDropdown(group, 'city', null, preselectCity, onComplete);
            } else {
                if (type === 'state' && preselectCity) {
                    appendInvoiceLocationOption($city, preselectCity);
                    $city.selectpicker('val', preselectCity);
                    $city.data('invoice-preview-value', preselectCity);
                    cacheInvoiceBillingField('billing_city', preselectCity);
                    $city.selectpicker('refresh');
                }
                finishLocationUpdate();
            }
        }).fail(function() {
            finishLocationUpdate();
        });
    };

    window.setInvoiceLocationValues = function(group, country, state, city, onComplete) {
        var $country = $('select[data-location-group="' + group + '"][data-location-role="country"]');
        var $state   = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var $city    = $('select[data-location-group="' + group + '"][data-location-role="city"]');

        if (!$country.length) {
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        if (!country) {
            _invoiceLocationSuppressChange = true;
            $country.selectpicker('val', '');
            _invoiceLocationSuppressChange = false;
            $state.empty().append('<option value=""></option>').selectpicker('refresh');
            $city.empty().append('<option value=""></option>').selectpicker('refresh');
            toggleInvoiceLocationFields(group);
            if (typeof onComplete === 'function') {
                onComplete();
            }
            return;
        }

        _invoiceLocationSuppressChange = true;
        $country.selectpicker('val', country);
        refreshInvoiceLocationDropdown(group, 'state', state || '', city || '', onComplete);
        setTimeout(function() {
            _invoiceLocationSuppressChange = false;
        }, 0);
    };

    $(document).off('changed.bs.select.invoiceLocation', '#billing_and_shipping_details select[name="billing_country"]');
    $(document).on('changed.bs.select.invoiceLocation', '#billing_and_shipping_details select[name="billing_country"]', function() {
        if (_invoiceLocationSuppressChange) {
            return;
        }
        refreshInvoiceLocationDropdown('invoice-billing', 'state');
    });

    $(document).off('changed.bs.select.invoiceLocation', '#billing_and_shipping_details select[name="billing_state"]');
    $(document).on('changed.bs.select.invoiceLocation', '#billing_and_shipping_details select[name="billing_state"]', function() {
        if (_invoiceLocationSuppressChange) {
            return;
        }
        var countryId = $('select[name="billing_country"]').selectpicker('val') || $('select[name="billing_country"]').val();
        if (isIndiaCountry(countryId)) {
            refreshInvoiceLocationDropdown('invoice-billing', 'city');
        } else {
            toggleInvoiceLocationFields('invoice-billing');
        }
    });

    $('#billing_and_shipping_details').off('shown.bs.modal.invoiceLocation').on('shown.bs.modal.invoiceLocation', function() {
        $(this).find('select.selectpicker').selectpicker('refresh');
        toggleInvoiceLocationFields('invoice-billing');
    });

    function rememberInvoiceBillingSelectValue($select) {
        if (!$select || !$select.length) {
            return;
        }

        if ($select.attr('name') === 'billing_country') {
            var iso2 = $select.find('option:selected').data('subtext') || '';
            if (iso2) {
                cacheInvoiceBillingField('billing_country', iso2);
            }
            return;
        }

        var value = captureInvoiceBillingSelectDisplay($select);
        if (value) {
            $select.data('invoice-preview-value', value);
            cacheInvoiceBillingField($select.attr('name'), value);
        }
    }

    $(document).off('changed.bs.select.invoicePreview', '#billing_and_shipping_details select[name="billing_country"], #billing_and_shipping_details select[name="billing_state"], #billing_and_shipping_details select[name="billing_city"]');
    $(document).on('changed.bs.select.invoicePreview', '#billing_and_shipping_details select[name="billing_country"], #billing_and_shipping_details select[name="billing_state"], #billing_and_shipping_details select[name="billing_city"]', function() {
        if (_invoiceLocationSuppressChange || window._invoiceApplyingBilling) {
            return;
        }
        var $select = $(this);
        setTimeout(function() {
            rememberInvoiceBillingSelectValue($select);
            if (typeof updateInvoiceBillToAddress === 'function') {
                updateInvoiceBillToAddress();
            }
        }, 0);
    });

    $(document).off('mousedown.invoiceBillingApply', '#billing_and_shipping_details .invoice-billing-apply');
    $(document).on('mousedown.invoiceBillingApply', '#billing_and_shipping_details .invoice-billing-apply', function(e) {
        e.preventDefault();
        if (typeof applyInvoiceBillingAddress === 'function') {
            applyInvoiceBillingAddress();
        }
    });

    $(document).off('click.invoiceBillingApply', '#billing_and_shipping_details .invoice-billing-apply');
    $(document).on('click.invoiceBillingApply', '#billing_and_shipping_details .invoice-billing-apply', function(e) {
        e.preventDefault();
        return false;
    });

    $(document).off('change.invoicePreview', '#billing_and_shipping_details textarea[name="billing_street"], #billing_and_shipping_details input[name="billing_zip"]');
    $(document).on('change.invoicePreview keyup.invoicePreview', '#billing_and_shipping_details textarea[name="billing_street"], #billing_and_shipping_details input[name="billing_zip"]', function() {
        cacheInvoiceBillingField(this.name, $(this).val());
        if (typeof updateInvoiceBillToAddress === 'function') {
            updateInvoiceBillToAddress();
        }
    });

    var $modal = $('#billing_and_shipping_details');
    rememberInvoiceBillingSelectValue($modal.find('select[name="billing_state"]'));
    rememberInvoiceBillingSelectValue($modal.find('select[name="billing_city"]'));

    if ($('#invoice-form').length && typeof renderInvoiceBillToAddress === 'function') {
        var hasBillingData = window._invoiceBillingPreviewCache.billing_state
            || window._invoiceBillingPreviewCache.billing_city
            || window._invoiceBillingPreviewCache.billing_street;
        if (hasBillingData) {
            renderInvoiceBillToAddress();
        }
    }

    toggleInvoiceLocationFields('invoice-billing');
};
</script>
