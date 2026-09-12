<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="billing_and_shipping_details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <?php
                    $countries                = get_all_countries();
                    // ── Shipping location dropdowns ──────────────────────────────
                    $selected_shipping_country = (isset($invoice) ? $invoice->shipping_country : '');
                    $selected_shipping_state   = (isset($invoice) ? $invoice->shipping_state : '');
                    $selected_shipping_city    = (isset($invoice) ? $invoice->shipping_city : '');
                    $shipping_location         = build_location_dropdown_data($selected_shipping_country, $selected_shipping_state, $selected_shipping_city);
                    $shipping_state_options    = $shipping_location['states'];
                    $shipping_city_options     = $shipping_location['cities'];

                    if (!empty($selected_shipping_state)) {
                        $s_found = false;
                        foreach ($shipping_state_options as $sr) {
                            if ($sr['state'] === $selected_shipping_state) {
                                $s_found = true;
                                break;
                            }
                        }
                        if (!$s_found) {
                            $shipping_state_options[] = ['state' => $selected_shipping_state];
                        }
                    }
                    if (!empty($selected_shipping_city)) {
                        $c_found = false;
                        foreach ($shipping_city_options as $cr) {
                            if ($cr['city'] === $selected_shipping_city) {
                                $c_found = true;
                                break;
                            }
                        }
                        if (!$c_found) {
                            $shipping_city_options[] = ['city' => $selected_shipping_city];
                        }
                    }

                    $ship_state_wrapper_class = !empty($selected_shipping_country) ? 'invoice-location-state-wrapper location-group-invoice-shipping' : 'invoice-location-state-wrapper location-group-invoice-shipping hide';
                    $ship_city_wrapper_class  = (!empty($selected_shipping_country) && !empty($selected_shipping_state) && country_uses_city_dropdown($selected_shipping_country)) ? 'invoice-location-city-wrapper location-group-invoice-shipping' : 'invoice-location-city-wrapper location-group-invoice-shipping hide';

                    // ── Billing location dropdowns ───────────────────────────────
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
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
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
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('ship_to'); ?></p>
                        <div id="shipping_details">
                            <?php $value = (isset($invoice) ? $invoice->shipping_street : ''); ?>
                            <?php echo render_textarea('shipping_street', 'shipping_street', $value); ?>
                            <?php echo render_select('shipping_country', $countries, ['country_id', ['short_name'], 'iso2'], 'shipping_country', $selected_shipping_country, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'country',
                            ])); ?>
                            <?php echo render_select('shipping_state', $shipping_state_options, ['state', 'state'], 'shipping_state', $selected_shipping_state, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'state',
                            ]), [], $ship_state_wrapper_class); ?>
                            <?php echo render_select('shipping_city', $shipping_city_options, ['city', 'city'], 'District', $selected_shipping_city, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'city',
                            ]), [], $ship_city_wrapper_class); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_zip : ''); ?>
                            <?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
                            <!-- Hidden fields required by the existing JS billing loop -->
                            <input type="hidden" name="include_shipping" value="1">
                            <input type="hidden" name="show_shipping_on_invoice" value="1">
                        </div>
                    </div>
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
    window._invoiceShippingPreviewCache = window._invoiceShippingPreviewCache || {};
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

    window.getInvoiceShipToSpan = function(fieldName) {
        var map = {
            shipping_street: '#invoice_ship_to_street',
            shipping_city: '#invoice_ship_to_city',
            shipping_state: '#invoice_ship_to_state',
            shipping_country: '#invoice_ship_to_country',
            shipping_zip: '#invoice_ship_to_zip'
        };
        if (map[fieldName]) {
            var $byId = $(map[fieldName]);
            if ($byId.length) return $byId;
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
        var $modal = $('[id="billing_and_shipping_details"]');
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

    window.renderInvoiceShipToAddress = function() {
        var cache = window._invoiceShippingPreviewCache;
        var street = cache.shipping_street || '--';
        var city = cache.shipping_city || '--';
        var state = cache.shipping_state || '--';
        var country = cache.shipping_country || '--';
        var zip = cache.shipping_zip || '--';

        getInvoiceShipToSpan('shipping_street').html(street !== '--' ? String(street).replace(/(?:\r\n|\r|\n)/g, '<br />') : '--');
        getInvoiceShipToSpan('shipping_city').text(city);
        getInvoiceShipToSpan('shipping_state').text(state);
        getInvoiceShipToSpan('shipping_country').text(country);
        getInvoiceShipToSpan('shipping_zip').text(zip);
    };

    window.updateInvoiceBillToAddress = function() {
        var $modal = $('[id="billing_and_shipping_details"]');
        if (!$modal.length || window._invoiceApplyingBilling) {
            return;
        }

        snapshotInvoiceBillingPreviewFields();
        renderInvoiceBillToAddress();
        snapshotInvoiceShippingPreviewFields();
        renderInvoiceShipToAddress();
    };

    window.applyInvoiceBillingAddress = function() {
        window._invoiceApplyingBilling = true;
        snapshotInvoiceBillingPreviewFields();
        renderInvoiceBillToAddress();
        snapshotInvoiceShippingPreviewFields();
        renderInvoiceShipToAddress();
        $('[id="billing_and_shipping_details"]').modal('hide');
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
            $('[id="billing_and_shipping_details"] select[name="billing_state"]').data('invoice-preview-value', <?php echo json_encode($selected_state); ?>);
        <?php } ?>
        <?php if (!empty($selected_city)) { ?>
            cacheInvoiceBillingField('billing_city', <?php echo json_encode($selected_city); ?>);
            $('[id="billing_and_shipping_details"] select[name="billing_city"]').data('invoice-preview-value', <?php echo json_encode($selected_city); ?>);
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
            $('[id="billing_and_shipping_details"]').each(function() {
                var $modal = $(this);
                var $country = $modal.find('select[data-location-group="' + group + '"][data-location-role="country"]');
                var $state = $modal.find('select[data-location-group="' + group + '"][data-location-role="state"]');
                var countryId = $country.selectpicker('val') || $country.val();
                var stateVal = $state.selectpicker('val') || $state.val();

                $modal.find('.invoice-location-state-wrapper.location-group-' + group).toggleClass('hide', !countryId);

                var showCity = countryId && stateVal && isIndiaCountry(countryId);
                $modal.find('.invoice-location-city-wrapper.location-group-' + group).toggleClass('hide', !showCity);
            });
        };

        function appendInvoiceLocationOption($select, value) {
            if (!value) {
                return;
            }
            if ($select.find('option').filter(function() {
                    return $(this).val() === value;
                }).length === 0) {
                $select.append($('<option>', {
                    value: value,
                    text: value
                }));
            }
        }

        window.refreshInvoiceLocationDropdown = function(group, type, preselectState, preselectCity, onComplete) {
            var $modal = $('[id="billing_and_shipping_details"]:visible').last();
            if (!$modal.length) $modal = $('[id="billing_and_shipping_details"]').last();
            
            var $country = $modal.find('select[data-location-group="' + group + '"][data-location-role="country"]');
            var $state = $modal.find('select[data-location-group="' + group + '"][data-location-role="state"]');
            var $city = $modal.find('select[data-location-group="' + group + '"][data-location-role="city"]');
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
                var key = (type === 'state') ? 'state' : 'city';
                var pre = (type === 'state') ? preselectState : preselectCity;

                $.each(result.data, function(i, item) {
                    if (item[key]) {
                        $target.append(new Option(item[key], item[key]));
                    }
                });

                if (pre) {
                    appendInvoiceLocationOption($target, pre);
                    _invoiceLocationSuppressChange = true;
                    $target.selectpicker('val', pre);
                    _invoiceLocationSuppressChange = false;
                    $target.data('invoice-preview-value', pre);
                    cacheInvoiceBillingField($target.attr('name'), pre);
                }

                $target.selectpicker('refresh');

                if (type === 'state' && preselectState && isIndiaCountry(countryId)) {
                    refreshInvoiceLocationDropdown(group, 'city', null, preselectCity, onComplete);
                } else {
                    if (type === 'state' && preselectCity) {
                        appendInvoiceLocationOption($city, preselectCity);
                        _invoiceLocationSuppressChange = true;
                        $city.selectpicker('val', preselectCity);
                        _invoiceLocationSuppressChange = false;
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
            var $modal = $('[id="billing_and_shipping_details"]:visible').last();
            if (!$modal.length) $modal = $('[id="billing_and_shipping_details"]').last();
            var $country = $modal.find('select[data-location-group="' + group + '"][data-location-role="country"]');
            var $state = $modal.find('select[data-location-group="' + group + '"][data-location-role="state"]');
            var $city = $modal.find('select[data-location-group="' + group + '"][data-location-role="city"]');

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

        $(document).off('changed.bs.select.invoiceLocation', '[id="billing_and_shipping_details"] select[name="billing_country"]');
        $(document).on('changed.bs.select.invoiceLocation', '[id="billing_and_shipping_details"] select[name="billing_country"]', function() {
            if (_invoiceLocationSuppressChange) {
                return;
            }
            refreshInvoiceLocationDropdown('invoice-billing', 'state');
        });

        $(document).off('changed.bs.select.invoiceLocation', '[id="billing_and_shipping_details"] select[name="billing_state"]');
        $(document).on('changed.bs.select.invoiceLocation', '[id="billing_and_shipping_details"] select[name="billing_state"]', function() {
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

        $('[id="billing_and_shipping_details"]').off('shown.bs.modal.invoiceLocation').on('shown.bs.modal.invoiceLocation', function() {
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

        $(document).off('changed.bs.select.invoicePreview', '[id="billing_and_shipping_details"] select[name="billing_country"], [id="billing_and_shipping_details"] select[name="billing_state"], [id="billing_and_shipping_details"] select[name="billing_city"]');
        $(document).on('changed.bs.select.invoicePreview', '[id="billing_and_shipping_details"] select[name="billing_country"], [id="billing_and_shipping_details"] select[name="billing_state"], [id="billing_and_shipping_details"] select[name="billing_city"]', function() {
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

        $(document).off('mousedown.invoiceBillingApply', '[id="billing_and_shipping_details"] .invoice-billing-apply');
        $(document).on('mousedown.invoiceBillingApply', '[id="billing_and_shipping_details"] .invoice-billing-apply', function(e) {
            e.preventDefault();
            if (typeof applyInvoiceBillingAddress === 'function') {
                applyInvoiceBillingAddress();
            }
        });

        $(document).off('click.invoiceBillingApply', '[id="billing_and_shipping_details"] .invoice-billing-apply');
        $(document).on('click.invoiceBillingApply', '[id="billing_and_shipping_details"] .invoice-billing-apply', function(e) {
            e.preventDefault();
            return false;
        });

        $(document).off('change.invoicePreview', '[id="billing_and_shipping_details"] textarea[name="billing_street"], [id="billing_and_shipping_details"] input[name="billing_zip"]');
        $(document).on('change.invoicePreview keyup.invoicePreview', '[id="billing_and_shipping_details"] textarea[name="billing_street"], [id="billing_and_shipping_details"] input[name="billing_zip"]', function() {
            cacheInvoiceBillingField(this.name, $(this).val());
            if (typeof updateInvoiceBillToAddress === 'function') {
                updateInvoiceBillToAddress();
            }
        });

        var $modal = $('[id="billing_and_shipping_details"]');
        rememberInvoiceBillingSelectValue($modal.find('select[name="billing_state"]'));
        rememberInvoiceBillingSelectValue($modal.find('select[name="billing_city"]'));

        if ($('#invoice-form').length && typeof renderInvoiceBillToAddress === 'function') {
            var hasBillingData = window._invoiceBillingPreviewCache.billing_state ||
                window._invoiceBillingPreviewCache.billing_city ||
                window._invoiceBillingPreviewCache.billing_street;
            if (hasBillingData) {
                renderInvoiceBillToAddress();
            }
        }

        toggleInvoiceLocationFields('invoice-billing');
    };

    // ── Shipping preview snapshot / location helpers ─────────────────────────────
    window.cacheInvoiceShippingField = function(fieldName, value) {
        if (fieldName && value && String(value).trim() !== '' && String(value).trim() !== '--') {
            window._invoiceShippingPreviewCache[fieldName] = String(value).trim();
        }
    };

    window.captureInvoiceShippingSelectDisplay = function($select) {
        return captureInvoiceBillingSelectDisplay($select); // reuse same logic
    };

    window.snapshotInvoiceShippingPreviewFields = function() {
        var $modal = $('[id="billing_and_shipping_details"]');
        if (!$modal.length) return;

        cacheInvoiceShippingField('shipping_street', $.trim($modal.find('textarea[name="shipping_street"]').val() || ''));
        cacheInvoiceShippingField('shipping_zip', $.trim($modal.find('input[name="shipping_zip"]').val() || ''));

        var $countrySelect = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="country"]');
        var country = $countrySelect.find('option:selected').data('subtext') || captureInvoiceShippingSelectDisplay($countrySelect);
        cacheInvoiceShippingField('shipping_country', country);

        ['shipping_state', 'shipping_city'].forEach(function(fieldName) {
            var $select = $modal.find('select[name="' + fieldName + '"]');
            var value = captureInvoiceShippingSelectDisplay($select);
            if (!value) value = window._invoiceShippingPreviewCache[fieldName] || '';
            if (value) {
                $select.data('invoice-preview-value', value);
                cacheInvoiceShippingField(fieldName, value);
            }
        });
    };

    window.initInvoiceShippingLocationDropdowns = function() {
        // Bind events now that the DOM elements are confirmed to exist
        window._shipSuppressChange = false;

        <?php if (!empty($selected_shipping_state)) { ?>
            cacheInvoiceShippingField('shipping_state', <?php echo json_encode($selected_shipping_state); ?>);
        <?php } ?>
        <?php if (!empty($selected_shipping_city)) { ?>
            cacheInvoiceShippingField('shipping_city', <?php echo json_encode($selected_shipping_city); ?>);
        <?php } ?>
        <?php if (isset($invoice) && !empty($invoice->shipping_street)) { ?>
            cacheInvoiceShippingField('shipping_street', <?php echo json_encode($invoice->shipping_street); ?>);
        <?php } ?>
        <?php if (isset($invoice) && !empty($invoice->shipping_zip)) { ?>
            cacheInvoiceShippingField('shipping_zip', <?php echo json_encode($invoice->shipping_zip); ?>);
        <?php } ?>
        <?php if (isset($invoice) && !empty($invoice->shipping_country)) { ?>
            cacheInvoiceShippingField('shipping_country', <?php echo json_encode(get_country_short_name($invoice->shipping_country)); ?>);
        <?php } ?>

        $(document).off('changed.bs.select.shipLocation', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="country"]');
        $(document).on('changed.bs.select.shipLocation', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="country"]', function() {
            if (window._shipSuppressChange) return;
            window.refreshShippingDropdown('state');
        });

        $(document).off('changed.bs.select.shipLocation', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="state"]');
        $(document).on('changed.bs.select.shipLocation', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="state"]', function() {
            if (window._shipSuppressChange) return;
            var countryId = $('select[data-location-group="invoice-shipping"][data-location-role="country"]').selectpicker('val') || $('select[data-location-group="invoice-shipping"][data-location-role="country"]').val();
            if (window.isIndiaShip(countryId)) {
                window.refreshShippingDropdown('city');
            } else {
                window.toggleShippingFields();
            }
        });

        $(document).off('changed.bs.select.shipPreview', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="country"], [id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="state"], [id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="city"]');
        $(document).on('changed.bs.select.shipPreview', '[id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="country"], [id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="state"], [id="billing_and_shipping_details"] select[data-location-group="invoice-shipping"][data-location-role="city"]', function() {
            if (window._shipSuppressChange) return;
            var $select = $(this);
            setTimeout(function() {
                var name = $select.attr('name');
                var value = '';
                if (name === 'shipping_country') {
                    value = $select.find('option:selected').data('subtext') || captureInvoiceShippingSelectDisplay($select);
                } else {
                    value = captureInvoiceShippingSelectDisplay($select);
                }
                if (value) cacheInvoiceShippingField(name, value);
                if (typeof renderInvoiceShipToAddress === 'function') renderInvoiceShipToAddress();
            }, 0);
        });

        $(document).off('change.shipPreview keyup.shipPreview', '[id="billing_and_shipping_details"] textarea[name="shipping_street"], [id="billing_and_shipping_details"] input[name="shipping_zip"]');
        $(document).on('change.shipPreview keyup.shipPreview', '[id="billing_and_shipping_details"] textarea[name="shipping_street"], [id="billing_and_shipping_details"] input[name="shipping_zip"]', function() {
            cacheInvoiceShippingField(this.name, $(this).val());
            if (typeof renderInvoiceShipToAddress === 'function') renderInvoiceShipToAddress();
        });

        $('[id="billing_and_shipping_details"]').off('shown.bs.modal.shipLocation').on('shown.bs.modal.shipLocation', function() {
            $(this).find('select.selectpicker').selectpicker('refresh');
            window.toggleShippingFields();
        });

        if ($('#invoice-form').length && typeof renderInvoiceShipToAddress === 'function') {
            var hasShippingData = window._invoiceShippingPreviewCache.shipping_state ||
                window._invoiceShippingPreviewCache.shipping_city ||
                window._invoiceShippingPreviewCache.shipping_street;
            if (hasShippingData) renderInvoiceShipToAddress();
        }

        window.toggleShippingFields();
    };

    // ── Core shipping location helper functions defined at module level ───────────
    // These MUST be defined here (not inside initInvoiceShippingLocationDropdowns)
    // so they are available as soon as the script tag is parsed, before any init call.

    window.isIndiaShip = function(countryId) {
        return countryId && String(countryId) === String(typeof INDIA_COUNTRY_ID !== 'undefined' ? INDIA_COUNTRY_ID : 0);
    }

    window.toggleShippingFields = function() {
        $('[id="billing_and_shipping_details"]').each(function() {
            var $modal = $(this);
            var $country = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="country"]');
            var $state = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="state"]');
            var countryId = $country.selectpicker('val') || $country.val();
            var stateVal = $state.selectpicker('val') || $state.val();
            $modal.find('.invoice-location-state-wrapper.location-group-invoice-shipping').toggleClass('hide', !countryId);
            var showCity = countryId && stateVal && window.isIndiaShip(countryId);
            $modal.find('.invoice-location-city-wrapper.location-group-invoice-shipping').toggleClass('hide', !showCity);
        });
    }

    window.appendShippingOption = function($select, value) {
        if (!value) return;
        if ($select.find('option').filter(function() {
                return $(this).val() === value;
            }).length === 0) {
            $select.append($('<option>', {
                value: value,
                text: value
            }));
        }
    }

    window.refreshShippingDropdown = function(type, preselectState, preselectCity, onComplete) {
        var $modal = $('[id="billing_and_shipping_details"]:visible').last();
        if (!$modal.length) $modal = $('[id="billing_and_shipping_details"]').last();
        var $country = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="country"]');
        var $state = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="state"]');
        var $city = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="city"]');
        var countryId = $country.selectpicker('val') || $country.val();

        function finish() {
            window.toggleShippingFields();
            if (typeof onComplete === 'function') onComplete();
            else if (typeof renderInvoiceShipToAddress === 'function') renderInvoiceShipToAddress();
        }

        if (type === 'state') {
            window.toggleShippingFields();
            $state.empty().append('<option value=""></option>').selectpicker('refresh');
            $city.empty().append('<option value=""></option>').selectpicker('refresh');
        } else {
            window.toggleShippingFields();
            $city.empty().append('<option value=""></option>').selectpicker('refresh');
        }

        if (!countryId) {
            finish();
            return;
        }

        var postData = {
            type: type,
            country_id: countryId
        };
        if (typeof csrfData !== 'undefined') postData[csrfData.token_name] = csrfData.hash;
        if (type === 'city') {
            postData.state = $state.selectpicker('val') || $state.val();
            if (!postData.state || !window.isIndiaShip(countryId)) {
                finish();
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
                finish();
                return;
            }
            var $target = (type === 'state') ? $state : $city;
            var key = (type === 'state') ? 'state' : 'city';
            var pre = (type === 'state') ? preselectState : preselectCity;

            $.each(result.data, function(i, item) {
                if (item[key]) $target.append(new Option(item[key], item[key]));
            });

            if (pre) {
                window.appendShippingOption($target, pre);
                window._shipSuppressChange = true;
                $target.selectpicker('val', pre);
                window._shipSuppressChange = false;
                $target.data('invoice-preview-value', pre);
                cacheInvoiceShippingField($target.attr('name'), pre);
            }
            $target.selectpicker('refresh');

            if (type === 'state' && preselectState && window.isIndiaShip(countryId)) {
                window.refreshShippingDropdown('city', null, preselectCity, onComplete);
            } else {
                if (type === 'state' && preselectCity) {
                    window.appendShippingOption($city, preselectCity);
                    window._shipSuppressChange = true;
                    $city.selectpicker('val', preselectCity);
                    window._shipSuppressChange = false;
                    cacheInvoiceShippingField('shipping_city', preselectCity);
                    $city.selectpicker('refresh');
                }
                finish();
            }
        }).fail(function() {
            finish();
        });
    }

    window.setInvoiceShippingLocationValues = function(country, state, city, onComplete) {
        var $modal = $('[id="billing_and_shipping_details"]:visible').last();
        if (!$modal.length) $modal = $('[id="billing_and_shipping_details"]').last();
        var $country = $modal.find('select[data-location-group="invoice-shipping"][data-location-role="country"]');
        if (!$country.length) {
            if (typeof onComplete === 'function') onComplete();
            return;
        }
        if (!country) {
            window._shipSuppressChange = true;
            $country.selectpicker('val', '');
            window._shipSuppressChange = false;
            $('select[data-location-group="invoice-shipping"][data-location-role="state"]').empty().append('<option value=""></option>').selectpicker('refresh');
            $('select[data-location-group="invoice-shipping"][data-location-role="city"]').empty().append('<option value=""></option>').selectpicker('refresh');
            window.toggleShippingFields();
            if (typeof onComplete === 'function') onComplete();
            return;
        }
        window._shipSuppressChange = true;
        $country.selectpicker('val', country);
        window.refreshShippingDropdown('state', state || '', city || '', onComplete);
        setTimeout(function() {
            window._shipSuppressChange = false;
        }, 0);
    };
</script>
