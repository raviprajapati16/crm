<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="shipping_details_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <?php
                    $countries                = get_all_countries();
                    $location_select_attrs    = ['data-none-selected-text' => _l('dropdown_non_selected_tex')];
                    $selected_country         = (isset($invoice) ? $invoice->shipping_country : '');
                    $selected_state           = (isset($invoice) ? $invoice->shipping_state : '');
                    $selected_city            = (isset($invoice) ? $invoice->shipping_city : '');
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

                    $state_wrapper_class = !empty($selected_country) ? 'invoice-location-state-wrapper location-group-invoice-shipping' : 'invoice-location-state-wrapper location-group-invoice-shipping hide';
                    $city_wrapper_class  = (!empty($selected_country) && !empty($selected_state) && country_uses_city_dropdown($selected_country)) ? 'invoice-location-city-wrapper location-group-invoice-shipping' : 'invoice-location-city-wrapper location-group-invoice-shipping hide';
                    ?>
                    <div class="col-md-12">
                        <div id="shipping_details">
                            <input type="checkbox" id="include_shipping" name="include_shipping" checked class="hide" style="display:none;">
                            <input type="checkbox" id="show_shipping_on_invoice" name="show_shipping_on_invoice" checked class="hide" style="display:none;">

                            <?php $value = (isset($invoice) ? $invoice->shipping_notify_party : ''); ?>
                            <?php echo render_input('shipping_notify_party', 'Notify Party (Ship To)', $value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_street : ''); ?>
                            <?php echo render_textarea('shipping_street', 'shipping_street', $value); ?>
                            <?php echo render_select('shipping_country', $countries, ['country_id', ['short_name'], 'iso2'], 'shipping_country', $selected_country, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'country',
                            ])); ?>
                            <?php echo render_select('shipping_state', $state_options, ['state', 'state'], 'shipping_state', $selected_state, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'state',
                            ]), [], $state_wrapper_class); ?>
                            <?php echo render_select('shipping_city', $city_options, ['city', 'city'], 'District', $selected_city, array_merge($location_select_attrs, [
                                'data-location-group' => 'invoice-shipping',
                                'data-location-role'  => 'city',
                            ]), [], $city_wrapper_class); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_zip : ''); ?>
                            <?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
                        </div>
                    </div>
                    <!-- <div class="col-md-12">
                        <hr />
                        <a href="#" class="pull-right" id="get_shipping_from_customer_profile" data-placement="left" data-toggle="tooltip" title="<?php echo _l('get_shipping_from_customer_profile'); ?>"><i class="fa fa-user"></i></a>
                        <div class="clearfix"></div>
                      <div class="form-group no-mbot">
                            <div class="checkbox checkbox-primary checkbox-inline">
                            <input type="checkbox" id="include_shipping" name="include_shipping" <?php if (isset($invoice) && $invoice->include_shipping == 1) {
                                                                                                        echo 'checked';
                                                                                                    } ?>>
                            <label for="include_shipping"><?php echo _l('shipping_address'); ?></label>
                        </div>
                      </div>
                        <div id="shipping_details" class="<?php if ((isset($invoice) && $invoice->include_shipping != 1) || !isset($invoice)) {
                                                                echo 'hide';
                                                            } ?>">
                          <div class="form-group">
                                <div class="checkbox checkbox-primary checkbox-inline">
                                <input type="checkbox" id="show_shipping_on_invoice" name="show_shipping_on_invoice" <?php if ((isset($invoice) && $invoice->show_shipping_on_invoice == 1) || !isset($invoice)) {
                                                                                                                            echo 'checked';
                                                                                                                        } ?>>
                                <label for="show_shipping_on_invoice"><?php echo _l('show_shipping_on_invoice'); ?></label>
                            </div>
                          </div>
                            <?php $value = (isset($invoice) ? $invoice->shipping_street : ''); ?>
                            <?php echo render_textarea('shipping_street', 'shipping_street', $value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_city : ''); ?>
                            <?php echo render_input('shipping_city', 'shipping_city', $value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_state : ''); ?>
                            <?php echo render_input('shipping_state', 'shipping_state', $value); ?>
                            <?php $value = (isset($invoice) ? $invoice->shipping_zip : ''); ?>
                            <?php echo render_input('shipping_zip', 'shipping_zip', $value); ?>
                            <?php $selected = (isset($invoice) ? $invoice->shipping_country : ''); ?>
                            <?php echo render_select('shipping_country', $countries, array('country_id', array('short_name'), 'iso2'), 'shipping_country', $selected); ?>
                        </div>
                    </div> -->
                </div>
            </div>
            <div class="modal-footer modal-not-full-width">
                <a href="#" onclick="resetInvoiceShippingForm(); return false;" class="btn btn-default invoice-shipping-reset" style="margin-right:6px;">
                    <i class="fa fa-refresh"></i> <?php echo _l('reset'); ?>
                </a>
                <a href="#" onclick="applyInvoiceShippingAddress(); return false;" class="btn btn-info invoice-shipping-apply"><?php echo _l('apply'); ?></a>
            </div>
        </div>
    </div>
</div>
<script>
    window._invoiceShippingPreviewCache = window._invoiceShippingPreviewCache || {};
    window._invoiceApplyingShipping = false;

    window.getInvoiceShipToSpan = function(fieldName) {
        var map = {
            shipping_notify_party: '#invoice_ship_to_notify_party',
            shipping_street: '#invoice_ship_to_street',
            shipping_city: '#invoice_ship_to_city',
            shipping_state: '#invoice_ship_to_state',
            shipping_country: '#invoice_ship_to_country',
            shipping_zip: '#invoice_ship_to_zip'
        };

        if (map[fieldName]) {
            var $byId = $(map[fieldName]);
            if ($byId.length) {
                return $byId;
            }
        }

        return $('#invoice-form .' + fieldName).first();
    };

    window.captureInvoiceShippingSelectDisplay = function($select) {
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
        return captureInvoiceShippingSelectDisplay($select);
    };

    window.readInvoiceBillingSelectValue = function($select) {
        if (!$select || !$select.length) {
            return '';
        }

        var fieldName = $select.attr('name') || '';
        if (fieldName && window._invoiceShippingPreviewCache[fieldName]) {
            return String(window._invoiceShippingPreviewCache[fieldName]);
        }

        var stored = $select.data('invoice-preview-value');
        if (stored) {
            return String(stored);
        }

        return captureInvoiceShippingSelectDisplay($select);
    };

    window.cacheInvoiceShippingField = function(fieldName, value) {
        if (fieldName && value && String(value).trim() !== '' && String(value).trim() !== '--') {
            window._invoiceShippingPreviewCache[fieldName] = String(value).trim();
        }
    };

    window.preserveInvoiceShipToSpanValues = function() {
        ['shipping_notify_party', 'shipping_street', 'shipping_city', 'shipping_state', 'shipping_country', 'shipping_zip'].forEach(function(fieldName) {
            var text = $.trim(getInvoiceShipToSpan(fieldName).text());
            if (text && text !== '--') {
                cacheInvoiceShippingField(fieldName, text);
            }
        });
    };

    window.snapshotInvoiceShippingPreviewFields = function() {
        var $modal = $('#shipping_details_modal');
        if (!$modal.length) {
            return;
        }

        preserveInvoiceShipToSpanValues();

        cacheInvoiceShippingField('shipping_notify_party', $.trim($modal.find('input[name="shipping_notify_party"]').val() || ''));
        cacheInvoiceShippingField('shipping_street', $.trim($modal.find('textarea[name="shipping_street"]').val() || ''));
        cacheInvoiceShippingField('shipping_zip', $.trim($modal.find('input[name="shipping_zip"]').val() || ''));

        var $countrySelect = $modal.find('select[name="shipping_country"]');
        var country = $countrySelect.find('option:selected').data('subtext') || captureInvoiceShippingSelectDisplay($countrySelect);
        cacheInvoiceShippingField('shipping_country', country);

        ['shipping_state', 'shipping_city'].forEach(function(fieldName) {
            var $select = $modal.find('select[name="' + fieldName + '"]');
            var value = captureInvoiceShippingSelectDisplay($select);
            if (!value) {
                value = window._invoiceShippingPreviewCache[fieldName] || '';
            }
            if (value) {
                $select.data('invoice-preview-value', value);
                cacheInvoiceShippingField(fieldName, value);
            }
        });
    };

    window.renderInvoiceShipToAddress = function() {
        var cache = window._invoiceShippingPreviewCache;
        var notify_party = cache.shipping_notify_party || '--';
        var street = cache.shipping_street || '--';
        var city = cache.shipping_city || '--';
        var state = cache.shipping_state || '--';
        var country = cache.shipping_country || '--';
        var zip = cache.shipping_zip || '--';

        getInvoiceShipToSpan('shipping_notify_party').html(notify_party !== '--' ? String(notify_party).replace(/(?:\r\n|\r|\n)/g, '<br />') : '--');
        getInvoiceShipToSpan('shipping_street').html(street !== '--' ? String(street).replace(/(?:\r\n|\r|\n)/g, '<br />') : '--');
        getInvoiceShipToSpan('shipping_city').text(city);
        getInvoiceShipToSpan('shipping_state').text(state);
        getInvoiceShipToSpan('shipping_country').text(country);
        getInvoiceShipToSpan('shipping_zip').text(zip);
    };

    window.updateInvoiceShipToAddress = function() {
        var $modal = $('#shipping_details_modal');
        if (!$modal.length || window._invoiceApplyingShipping) {
            return;
        }

        snapshotInvoiceShippingPreviewFields();
        renderInvoiceShipToAddress();
    };

    window.applyInvoiceShippingAddress = function() {
        window._invoiceApplyingShipping = true;
        snapshotInvoiceShippingPreviewFields();
        renderInvoiceShipToAddress();
        $('#shipping_details_modal').modal('hide');
        setTimeout(function() {
            window._invoiceApplyingShipping = false;
        }, 300);
    };

    window.initInvoiceShippingLocationDropdowns = function() {
        if (typeof jQuery === 'undefined' || $('select[data-location-group="invoice-shipping"][data-location-role="country"]').length === 0) {
            return;
        }

        if (window._invoiceShippingLocationInitialized) {
            toggleInvoiceLocationFields('invoice-shipping');
            return;
        }
        window._invoiceShippingLocationInitialized = true;

        <?php if (!empty($selected_state)) { ?>
            cacheInvoiceShippingField('shipping_state', <?php echo json_encode($selected_state); ?>);
            $('#shipping_details_modal select[name="shipping_state"]').data('invoice-preview-value', <?php echo json_encode($selected_state); ?>);
        <?php } ?>
        <?php if (!empty($selected_city)) { ?>
            cacheInvoiceShippingField('shipping_city', <?php echo json_encode($selected_city); ?>);
            $('#shipping_details_modal select[name="shipping_city"]').data('invoice-preview-value', <?php echo json_encode($selected_city); ?>);
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

        var _invoiceLocationSuppressChange = false;

        function isIndiaCountry(countryId) {
            return countryId && String(countryId) === String(typeof INDIA_COUNTRY_ID !== 'undefined' ? INDIA_COUNTRY_ID : 0);
        }

        window.toggleInvoiceLocationFields = function(group) {
            var $country = $('select[data-location-group="' + group + '"][data-location-role="country"]');
            var $state = $('select[data-location-group="' + group + '"][data-location-role="state"]');
            var countryId = $country.selectpicker('val') || $country.val();
            var stateVal = $state.selectpicker('val') || $state.val();

            $('.invoice-location-state-wrapper.location-group-' + group).toggleClass('hide', !countryId);

            var showCity = countryId && stateVal && isIndiaCountry(countryId);
            $('.invoice-location-city-wrapper.location-group-' + group).toggleClass('hide', !showCity);
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
            var $country = $('select[data-location-group="' + group + '"][data-location-role="country"]');
            var $state = $('select[data-location-group="' + group + '"][data-location-role="state"]');
            var $city = $('select[data-location-group="' + group + '"][data-location-role="city"]');
            var countryId = $country.selectpicker('val') || $country.val();

            function finishLocationUpdate() {
                toggleInvoiceLocationFields(group);
                if (typeof onComplete === 'function') {
                    onComplete();
                } else if ($('#invoice-form').length && typeof updateInvoiceShipToAddress === 'function') {
                    updateInvoiceShipToAddress();
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
                    $target.selectpicker('val', pre);
                    $target.data('invoice-preview-value', pre);
                    cacheInvoiceShippingField($target.attr('name'), pre);
                }

                $target.selectpicker('refresh');

                if (type === 'state' && preselectState && isIndiaCountry(countryId)) {
                    refreshInvoiceLocationDropdown(group, 'city', null, preselectCity, onComplete);
                } else {
                    if (type === 'state' && preselectCity) {
                        appendInvoiceLocationOption($city, preselectCity);
                        $city.selectpicker('val', preselectCity);
                        $city.data('invoice-preview-value', preselectCity);
                        cacheInvoiceShippingField('shipping_city', preselectCity);
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
            var $state = $('select[data-location-group="' + group + '"][data-location-role="state"]');
            var $city = $('select[data-location-group="' + group + '"][data-location-role="city"]');

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

        $(document).off('changed.bs.select.invoiceLocation', '#shipping_details_modal select[name="shipping_country"]');
        $(document).on('changed.bs.select.invoiceLocation', '#shipping_details_modal select[name="shipping_country"]', function() {
            if (_invoiceLocationSuppressChange) {
                return;
            }
            refreshInvoiceLocationDropdown('invoice-shipping', 'state');
        });

        $(document).off('changed.bs.select.invoiceLocation', '#shipping_details_modal select[name="shipping_state"]');
        $(document).on('changed.bs.select.invoiceLocation', '#shipping_details_modal select[name="shipping_state"]', function() {
            if (_invoiceLocationSuppressChange) {
                return;
            }
            var countryId = $('select[name="shipping_country"]').selectpicker('val') || $('select[name="shipping_country"]').val();
            if (isIndiaCountry(countryId)) {
                refreshInvoiceLocationDropdown('invoice-shipping', 'city');
            } else {
                toggleInvoiceLocationFields('invoice-shipping');
            }
        });

        $('#shipping_details_modal').off('shown.bs.modal.invoiceLocation').on('shown.bs.modal.invoiceLocation', function() {
            $(this).find('select.selectpicker').selectpicker('refresh');
            toggleInvoiceLocationFields('invoice-shipping');
        });

        function rememberInvoiceShippingSelectValue($select) {
            if (!$select || !$select.length) {
                return;
            }

            if ($select.attr('name') === 'shipping_country') {
                var iso2 = $select.find('option:selected').data('subtext') || '';
                if (iso2) {
                    cacheInvoiceShippingField('shipping_country', iso2);
                }
                return;
            }

            var value = captureInvoiceShippingSelectDisplay($select);
            if (value) {
                $select.data('invoice-preview-value', value);
                cacheInvoiceShippingField($select.attr('name'), value);
            }
        }

        $(document).off('changed.bs.select.invoicePreview', '#shipping_details_modal select[name="shipping_country"], #shipping_details_modal select[name="shipping_state"], #shipping_details_modal select[name="shipping_city"]');
        $(document).on('changed.bs.select.invoicePreview', '#shipping_details_modal select[name="shipping_country"], #shipping_details_modal select[name="shipping_state"], #shipping_details_modal select[name="shipping_city"]', function() {
            if (_invoiceLocationSuppressChange || window._invoiceApplyingShipping) {
                return;
            }
            var $select = $(this);
            setTimeout(function() {
                rememberInvoiceShippingSelectValue($select);
                if (typeof updateInvoiceShipToAddress === 'function') {
                    updateInvoiceShipToAddress();
                }
            }, 0);
        });

        $(document).off('mousedown.invoiceShippingApply', '#shipping_details_modal .invoice-shipping-apply');
        $(document).on('mousedown.invoiceShippingApply', '#shipping_details_modal .invoice-shipping-apply', function(e) {
            e.preventDefault();
            if (typeof applyInvoiceShippingAddress === 'function') {
                applyInvoiceShippingAddress();
            }
        });

        $(document).off('click.invoiceShippingApply', '#shipping_details_modal .invoice-shipping-apply');
        $(document).on('click.invoiceShippingApply', '#shipping_details_modal .invoice-shipping-apply', function(e) {
            e.preventDefault();
            return false;
        });

        $(document).off('change.invoicePreview', '#shipping_details_modal textarea[name="shipping_street"], #shipping_details_modal input[name="shipping_zip"]');
        $(document).on('change.invoicePreview keyup.invoicePreview', '#shipping_details_modal textarea[name="shipping_street"], #shipping_details_modal input[name="shipping_zip"]', function() {
            cacheInvoiceShippingField(this.name, $(this).val());
            if (typeof updateInvoiceShipToAddress === 'function') {
                updateInvoiceShipToAddress();
            }
        });

        var $modal = $('#shipping_details_modal');
        rememberInvoiceShippingSelectValue($modal.find('select[name="shipping_state"]'));
        rememberInvoiceShippingSelectValue($modal.find('select[name="shipping_city"]'));

        if ($('#invoice-form').length && typeof renderInvoiceShipToAddress === 'function') {
            var hasBillingData = window._invoiceShippingPreviewCache.shipping_state ||
                window._invoiceShippingPreviewCache.shipping_city ||
                window._invoiceShippingPreviewCache.shipping_street;
            if (hasBillingData) {
                renderInvoiceShipToAddress();
            }
        }

        toggleInvoiceLocationFields('invoice-shipping');

        // ── Reset shipping form ─────────────────────────────────────────
        $(document).off('click.invoiceShippingReset', '#shipping_details_modal .invoice-shipping-reset');
        $(document).on('click.invoiceShippingReset', '#shipping_details_modal .invoice-shipping-reset', function(e) {
            e.preventDefault();
            resetInvoiceShippingForm();
        });
    };

    window.resetInvoiceShippingForm = function() {
        var $modal = $('#shipping_details_modal');
        if (!$modal.length) { return; }

        // Clear text / textarea fields
        $modal.find('input[name="shipping_notify_party"]').val('');
        $modal.find('textarea[name="shipping_street"]').val('');
        $modal.find('input[name="shipping_zip"]').val('');

        // Reset selectpicker dropdowns
        $modal.find('select[name="shipping_country"]').selectpicker('val', '');
        $modal.find('select[name="shipping_state"]').empty().append('<option value=""></option>').selectpicker('refresh');
        $modal.find('select[name="shipping_city"]').empty().append('<option value=""></option>').selectpicker('refresh');

        // Hide dependent dropdowns
        if (typeof toggleInvoiceLocationFields === 'function') {
            toggleInvoiceLocationFields('invoice-shipping');
        }

        // \u2500\u2500 KEY FIX \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500
        // Set cache values to '' so Apply does not find stale entries.
        // (Using '' instead of delete ensures cacheInvoiceShippingField's
        //  non-empty guard prevents old values from being re-written back.)
        ['shipping_notify_party','shipping_street','shipping_city','shipping_state','shipping_country','shipping_zip'].forEach(function(f) {
            window._invoiceShippingPreviewCache[f] = '';
        });

        // Blank the invoice-page span elements NOW.
        // applyInvoiceShippingAddress() calls preserveInvoiceShipToSpanValues()
        // first, which reads current span text and re-caches it.  If we don't
        // blank the spans here the old address text is restored into the cache
        // before the modal fields are even read.
        if (typeof getInvoiceShipToSpan === 'function') {
            ['shipping_notify_party','shipping_street','shipping_city','shipping_state','shipping_country','shipping_zip'].forEach(function(f) {
                getInvoiceShipToSpan(f).html('--');
            });
        }
    };
</script>