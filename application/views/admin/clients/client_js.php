<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Included in application/views/admin/clients/client.php
 */
?>
<script>
    Dropzone.options.clientAttachmentsUpload = false;
    var customer_id = $('input[name="userid"]').val();
    var _table_customer_lead_assigne;
    $(function() {

        if ($('#client-attachments-upload').length > 0) {
            new Dropzone('#client-attachments-upload', appCreateDropzoneOptions({
                paramName: "file",
                acceptedFiles: ".jpg,.jpeg,.png,.pdf",
                accept: function(file, done) {
                    done();
                },
                success: function(file, response) {
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        window.location.reload();
                    }
                }
            }));
        }

        // Save button not hidden if passed from url ?tab= we need to re-click again
        if (tab_active) {
            $('body').find('.nav-tabs [href="#' + tab_active + '"]').click();
        }

        $('.note-private-switch').on('change', function() {
            var isPrivate = 0;
            if (this.checked) {
                var isPrivate = 1;
            }
            var id = $(this).attr('data-id');
            $.ajax({
                url: "<?php echo admin_url('misc/note_status_change') ?>",
                method: "POST",
                data: {
                    id: id,
                    is_private: isPrivate
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $('a[href="#customer_admins"]').on('click', function() {
            $('.btn-bottom-toolbar').addClass('hide');
        });

        $('.profile-tabs a').not('a[href="#customer_admins"]').on('click', function() {
            $('.btn-bottom-toolbar').removeClass('hide');
        });

        $("input[name='tasks_related_to[]']").on('change', function() {
            var tasks_related_values = []
            $('#tasks_related_filter :checkbox:checked').each(function(i) {
                tasks_related_values[i] = $(this).val();
            });
            $('input[name="tasks_related_to"]').val(tasks_related_values.join());
            $('.table-rel-tasks').DataTable().ajax.reload();
        });

        var contact_id = get_url_param('contactid');
        if (contact_id) {
            contact(customer_id, contact_id);
        }

        // consents=CONTACT_ID
        var consents = get_url_param('consents');
        if (consents) {
            view_contact_consent(consents);
        }

        // If user clicked save and add new contact
        if (get_url_param('new_contact')) {
            contact(customer_id);
        }

        $('body').on('change', '.onoffswitch input.customer_file', function(event, state) {
            var invoker = $(this);
            var checked_visibility = invoker.prop('checked');
            var share_file_modal = $('#customer_file_share_file_with');
            setTimeout(function() {
                $('input[name="file_id"]').val(invoker.attr('data-id'));
                if (checked_visibility && share_file_modal.attr('data-total-contacts') > 1) {
                    share_file_modal.modal('show');
                } else {
                    do_share_file_contacts();
                }
            }, 200);
        });

        $('.customer-form-submiter').on('click', function() {
            var form = $('.client-form');
            if (form.valid()) {
                if ($(this).hasClass('save-and-add-contact')) {
                    form.find('.additional').html(hidden_input('save_and_add_contact', 'true'));
                } else {
                    form.find('.additional').html('');
                }
                form.submit();
            }
        });

        if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
            document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
                success: function(files) {
                    saveCustomerProfileExternalFile(files, 'dropbox');
                },
                linkType: "preview",
                extensions: app.options.allowed_files.split(','),
            }));
        }

        /* Customer profile tickets table */
        $('.table-tickets-single').find('#th-submitter').removeClass('toggleable');

        initDataTable('.table-tickets-single', admin_url + 'tickets/index/false/' + customer_id, undefined, undefined, 'undefined', [$('table thead .ticket_created_column').index(), 'desc']);

        /* Customer profile contracts table */
        var fnServerParams = {
            "only_customer": '#only_customer',
        }
        initDataTable('.table-contracts-single-client', admin_url + 'contracts/table/' + customer_id, undefined, undefined, fnServerParams, [6, 'desc']);


        /* Custome profile contacts table */
        var contactsNotSortable = [];
        <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') { ?>
            contactsNotSortable.push($('#th-consent').index());
        <?php } ?>
        _table_api = initDataTable('.table-contacts', admin_url + 'clients/contacts/' + customer_id, contactsNotSortable, contactsNotSortable);
        if (_table_api) {
            <?php if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') { ?>
                _table_api.on('draw', function() {
                    var tableData = $('.table-contacts').find('tbody tr');
                    $.each(tableData, function() {
                        $(this).find('td:eq(1)').addClass('bg-light-gray');
                    });
                });
            <?php } ?>
        }
        /* Customer profile invoices table */
        initDataTable('.table-invoices-single-client',
            admin_url + 'invoices/table/' + customer_id,
            ['undefined'],
            ['undefined'],
            undefined,
            [1, 'desc']
        );

        initDataTable('.table-credit-notes', admin_url + 'credit_notes/table/' + customer_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

        /* Customer profile Estimates table */
        initDataTable('.table-estimates-single-client',
            admin_url + 'estimates/table/' + customer_id,
            'undefined',
            'undefined',
            'undefined', [
                [3, 'desc'],
                [0, 'desc']
            ]);

        /* Customer profile payments table */
        initDataTable('.table-payments-single-client',
            admin_url + 'payments/table/' + customer_id, undefined, undefined,
            'undefined', [0, 'desc']);

        /* Customer profile reminders table */
        initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + customer_id + '/' + 'customer', undefined, undefined, undefined, [1, 'asc']);

        /* Customer profile expenses table */
        initDataTable('.table-expenses-single-client',
            admin_url + 'expenses/table/' + customer_id,
            'undefined',
            'undefined',
            'undefined', [5, 'desc']);

        /* Customer profile proposals table */
        initDataTable('.table-proposals-client-profile',
            admin_url + 'proposals/proposal_relations/' + customer_id + '/customer',
            'undefined',
            'undefined',
            'undefined', [6, 'desc']);

        /* Custome profile projects table */
        initDataTable('.table-projects-single-client', admin_url + 'projects/table/' + customer_id, undefined, undefined, 'undefined', <?php echo hooks()->apply_filters('projects_table_default_order', json_encode(array(5, 'asc'))); ?>);

        /* Customer media table */
        var _table_customer_media = initDataTable('.table-customer-media', admin_url + 'customer_media/table/' + customer_id, '', '');

        /* Assigned Leads */
        _table_customer_lead_assigne = initDataTable('.table-customer-assigned-leads', admin_url + 'leads/customer_assigned_leads_table/' + customer_id, '', '');


        var vRules = {};
        if (app.options.company_is_required == 1) {
            vRules = {
                company: 'required',
            }
        }

        appValidateForm($('.client-form'), vRules);

        if (typeof(customer_id) == 'undefined') {
            $('#company').on('blur', function() {
                var company = $(this).val();
                var $companyExistsDiv = $('#company_exists_info');

                if (company == '') {
                    $companyExistsDiv.addClass('hide');
                    return;
                }

                $.post(admin_url + 'clients/check_duplicate_customer_name', {
                        company: company
                    })
                    .done(function(response) {
                        if (response) {
                            response = JSON.parse(response);
                            if (response.exists == true) {
                                $companyExistsDiv.removeClass('hide');
                                $companyExistsDiv.html('<div class="info-block mbot15">' + response.message + '</div>');
                            } else {
                                $companyExistsDiv.addClass('hide');
                            }
                        }
                    });
            });
        }

        $('.billing-same-as-customer').on('click', function(e) {
            e.preventDefault();
            $('textarea[name="billing_street"]').val($('textarea[name="address"]').val());
            $('input[name="billing_zip"]').val($('input[name="zip"]').val());
            copyClientLocationGroup('profile', 'billing');
        });

        $('.customer-copy-billing-address').on('click', function(e) {
            e.preventDefault();
            $('textarea[name="shipping_street"]').val($('textarea[name="billing_street"]').val());
            $('input[name="shipping_zip"]').val($('input[name="billing_zip"]').val());
            copyClientLocationGroup('billing', 'shipping');
        });

        $('body').on('hidden.bs.modal', '#contact', function() {
            $('#contact_data').empty();
        });

        $('.client-form').on('submit', function() {
            $('select[name="default_currency"]').prop('disabled', false);
        });

        initClientLocationDropdowns();

    });

    var INDIA_COUNTRY_ID = <?php echo (int) get_india_country_id(); ?>;
    var _clientLocationSuppressChange = false;

    function isIndiaCountry(countryId) {
        return countryId && String(countryId) === String(INDIA_COUNTRY_ID);
    }

    function toggleClientLocationFields(group) {
        var $country = $('select[data-location-group="' + group + '"][data-location-role="country"]');
        var $state   = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var countryId = $country.val();
        var stateVal  = $state.val();

        $('.client-location-state-wrapper.location-group-' + group).toggleClass('hide', !countryId);

        var showCity = countryId && stateVal && isIndiaCountry(countryId);
        $('.client-location-city-wrapper.location-group-' + group).toggleClass('hide', !showCity);
    }

    function appendClientLocationOption($select, value) {
        if (!value) {
            return;
        }
        if ($select.find('option').filter(function() { return $(this).val() === value; }).length === 0) {
            $select.append($('<option>', { value: value, text: value }));
        }
    }

    function refreshClientLocation(group, type, preselectState, preselectCity) {
        var $country = $('select[data-location-group="' + group + '"][data-location-role="country"]');
        var $state   = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var $city    = $('select[data-location-group="' + group + '"][data-location-role="city"]');
        var countryId = $country.val();

        if (type === 'state') {
            toggleClientLocationFields(group);
            $state.empty().append('<option value=""></option>');
            $city.empty().append('<option value=""></option>');
            $state.selectpicker('refresh');
            $city.selectpicker('refresh');
        } else {
            toggleClientLocationFields(group);
            $city.empty().append('<option value=""></option>');
            $city.selectpicker('refresh');
        }

        if (!countryId) {
            toggleClientLocationFields(group);
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
            postData.state = $state.val();
            if (!postData.state) {
                toggleClientLocationFields(group);
                return;
            }
            if (!isIndiaCountry(countryId)) {
                toggleClientLocationFields(group);
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
                toggleClientLocationFields(group);
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
                appendClientLocationOption($target, pre);
                $target.selectpicker('val', pre);
            }

            $target.selectpicker('refresh');

            if (type === 'state' && preselectState && isIndiaCountry(countryId)) {
                refreshClientLocation(group, 'city', null, preselectCity);
            } else {
                if (type === 'state' && preselectCity) {
                    appendClientLocationOption($city, preselectCity);
                    $city.selectpicker('val', preselectCity);
                    $city.selectpicker('refresh');
                }
                toggleClientLocationFields(group);
            }
        }).fail(function() {
            toggleClientLocationFields(group);
        });
    }

    function getClientLocationValues(group) {
        return {
            country: $('select[data-location-group="' + group + '"][data-location-role="country"]').val() || '',
            state: $('select[data-location-group="' + group + '"][data-location-role="state"]').val() || '',
            city: $('select[data-location-group="' + group + '"][data-location-role="city"]').val() || ''
        };
    }

    function copyClientLocationGroup(fromGroup, toGroup) {
        var values = getClientLocationValues(fromGroup);
        var $toCountry = $('select[data-location-group="' + toGroup + '"][data-location-role="country"]');
        var $toState   = $('select[data-location-group="' + toGroup + '"][data-location-role="state"]');
        var $toCity    = $('select[data-location-group="' + toGroup + '"][data-location-role="city"]');

        if (!values.country) {
            _clientLocationSuppressChange = true;
            $toCountry.selectpicker('val', '');
            _clientLocationSuppressChange = false;
            $toState.empty().append('<option value=""></option>').selectpicker('refresh');
            $toCity.empty().append('<option value=""></option>').selectpicker('refresh');
            toggleClientLocationFields(toGroup);
            return;
        }

        _clientLocationSuppressChange = true;
        $toCountry.selectpicker('val', values.country);
        refreshClientLocation(toGroup, 'state', values.state, values.city);
        setTimeout(function() {
            _clientLocationSuppressChange = false;
        }, 0);
    }

    function initClientLocationDropdowns() {
        if ($('select[data-location-role="country"]').length === 0) {
            return;
        }

        $(document).off('changed.bs.select.clientLocation', 'select[data-location-role="country"]');
        $(document).on('changed.bs.select.clientLocation', 'select[data-location-role="country"]', function() {
            if (_clientLocationSuppressChange) {
                return;
            }
            refreshClientLocation($(this).attr('data-location-group'), 'state');
        });

        $(document).off('changed.bs.select.clientLocation', 'select[data-location-role="state"]');
        $(document).on('changed.bs.select.clientLocation', 'select[data-location-role="state"]', function() {
            if (_clientLocationSuppressChange) {
                return;
            }
            var group = $(this).attr('data-location-group');
            var countryId = $('select[data-location-group="' + group + '"][data-location-role="country"]').val();
            if (isIndiaCountry(countryId)) {
                refreshClientLocation(group, 'city');
            } else {
                toggleClientLocationFields(group);
            }
        });

        ['profile', 'billing', 'shipping'].forEach(function(group) {
            if ($('select[data-location-group="' + group + '"][data-location-role="country"]').length) {
                toggleClientLocationFields(group);
            }
        });
    }

    function view_lead_data(lead_id) {
        $.ajax({
            url: "<?php echo admin_url('leads/get_lead_data') ?>",
            method: "POST",
            data: {
                lead_id: lead_id
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                var data = result.lead_data || {};

                // fields to display: key = property in result.lead_data, label = shown text
                var fields = [{
                        k: 'name',
                        label: 'Name'
                    },
                    {
                        k: 'title',
                        label: 'Position'
                    },
                    {
                        k: 'company',
                        label: 'Company'
                    },
                    {
                        k: 'phonenumber',
                        label: 'Phone'
                    },
                    {
                        k: 'email',
                        label: 'Email'
                    },
                    {
                        k: 'website',
                        label: 'Website'
                    },
                    {
                        k: 'address',
                        label: 'Address'
                    },
                    {
                        k: 'city',
                        label: 'City'
                    },
                    {
                        k: 'state',
                        label: 'State'
                    },
                    {
                        k: 'country',
                        label: 'Country'
                    },
                    {
                        k: 'zip',
                        label: 'ZIP / Postal Code'
                    },
                    {
                        k: 'gst_in',
                        label: 'GST IN'
                    }
                ];

                // build HTML table
                var html = '<div class="col-md-12"><table class="table table-striped table-condensed">';
                html += '<tbody>';
                fields.forEach(function(f) {
                    var raw = (typeof data[f.k] !== 'undefined' && data[f.k] !== null && data[f.k] !== '') ? data[f.k] : '-';

                    // escape text (simple jQuery trick)
                    var safe = $('<div>').text(raw).html();

                    // make phone/email clickable
                    if (f.k === 'phonenumber' && raw !== '-') {
                        safe = '<a href="tel:' + $('<div>').text(raw).html() + '">' + safe + '</a>';
                    } else if (f.k === 'email' && raw !== '-') {
                        safe = '<a href="mailto:' + $('<div>').text(raw).html() + '">' + safe + '</a>';
                    }

                    html += '<tr>';
                    html += '<th style="width:30%; vertical-align:middle;">' + f.label + '</th>';
                    html += '<td>' + safe + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div>';

                $('#leadDataContent').html(html);

                // set modal title (use lead id or name when available)
                var title = 'Lead #' + (data.id || lead_id);
                if (data.name) title += ' — ' + $('<div>').text(data.name).html();
                $('#leadDataModal .modal-title').html(title);

                $('#leadDataModal').modal('show');
            } else {
                alert_float('danger', result.message);
            }
        }).fail(function(xhr, status, err) {
            alert_float('danger', 'Request failed: ' + status);
        });
    }


    function remove_lead_from_customer(lead_id) {
        if (confirm("Are you sure you want to perform this action ?")) {
            $.ajax({
                url: "<?php echo admin_url('leads/remove_lead_from_customer') ?>",
                method: "POST",
                data: {
                    lead_id: lead_id,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    alert_float('success', result.message);
                    _table_customer_lead_assigne.draw();
                } else {
                    alert_float('danger', result.message);
                }
            });
        }
    }

    function customer_lead_change_status(status, lead_id) {
        $.ajax({
            url: "<?php echo admin_url('leads/customer_lead_status_change') ?>",
            method: "POST",
            data: {
                status: status,
                lead_id: lead_id,
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert_float('success', result.message);
                _table_customer_lead_assigne.draw();
            } else {
                alert_float('danger', result.message);
            }
        });
    }

    function delete_contact_profile_image(contact_id) {
        requestGet('clients/delete_contact_profile_image/' + contact_id).done(function() {
            $('body').find('#contact-profile-image').removeClass('hide');
            $('body').find('#contact-remove-img').addClass('hide');
            $('body').find('#contact-img').attr('src', '<?php echo base_url('assets/images/user-placeholder.jpg'); ?>');
        });
    }

    function customerGoogleDriveSave(pickData) {
        saveCustomerProfileExternalFile(pickData, 'gdrive');
    }

    function saveCustomerProfileExternalFile(files, externalType) {
        $.post(admin_url + 'clients/add_external_attachment', {
            files: files,
            clientid: customer_id,
            external: externalType
        }).done(function() {
            window.location.reload();
        });
    }

    function validate_contact_form() {
        appValidateForm('#contact-form', {
            firstname: 'required',
            lastname: 'required',
            password: {
                required: {
                    depends: function(element) {

                        var $sentSetPassword = $('input[name="send_set_password_email"]');

                        if ($('#contact input[name="contactid"]').val() == '' && $sentSetPassword.prop('checked') == false) {
                            return true;
                        }
                    }
                }
            },
            email: {
                <?php if (hooks()->apply_filters('contact_email_required', "true") === "true") { ?>
                    required: true,
                <?php } ?>
                email: true,
                // Use this hook only if the contacts are not logging into the customers area and you are not using support tickets piping.
                <?php if (hooks()->apply_filters('contact_email_unique', "true") === "true") { ?>
                    remote: {
                        url: admin_url + "misc/contact_email_exists",
                        type: 'post',
                        data: {
                            email: function() {
                                return $('#contact input[name="email"]').val();
                            },
                            userid: function() {
                                return $('body').find('input[name="contactid"]').val();
                            }
                        }
                    }
                <?php } ?>
            }
        }, contactFormHandler);
    }

    function contactFormHandler(form) {
        $('#contact input[name="is_primary"]').prop('disabled', false);

        $("#contact input[type=file]").each(function() {
            if ($(this).val() === "") {
                $(this).prop('disabled', true);
            }
        });

        var formURL = $(form).attr("action");
        var formData = new FormData($(form)[0]);

        $.ajax({
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            url: formURL
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if (typeof(response.is_individual) != 'undefined' && response.is_individual) {
                    $('.new-contact').addClass('disabled');
                    if (!$('.new-contact-wrapper')[0].hasAttribute('data-toggle')) {
                        $('.new-contact-wrapper').attr('data-toggle', 'tooltip');
                    }
                }
            }

            if ($.fn.DataTable.isDataTable('.table-contacts')) {
                $('.table-contacts').DataTable().ajax.reload(null, false);
            } else if ($.fn.DataTable.isDataTable('.table-all-contacts')) {
                $('.table-all-contacts').DataTable().ajax.reload(null, false);
            }

            if (response.proposal_warning && response.proposal_warning != false) {
                $('body').find('#contact_proposal_warning').removeClass('hide');
                $('body').find('#contact_update_proposals_emails').attr('data-original-email', response.original_email);
                $('#contact').animate({
                    scrollTop: 0
                }, 800);
            } else {
                $('#contact').modal('hide');
            }
        }).fail(function(error) {
            alert_float('danger', JSON.parse(error.responseText));
        });
        return false;
    }

    function contact(client_id, contact_id) {
        if (typeof(contact_id) == 'undefined') {
            contact_id = '';
        }
        requestGet('clients/form_contact/' + client_id + '/' + contact_id).done(function(response) {
            $('#contact_data').html(response);
            $('#contact').modal({
                show: true,
                backdrop: 'static'
            });
            $('body').off('shown.bs.modal', '#contact');
            $('body').on('shown.bs.modal', '#contact', function() {
                if (contact_id == '') {
                    $('#contact').find('input[name="firstname"]').focus();
                }
            });
            init_selectpicker();
            init_datepicker();
            custom_fields_hyperlink();
            validate_contact_form();
        }).fail(function(error) {
            var response = JSON.parse(error.responseText);
            alert_float('danger', response.message);
        });
    }


    function update_all_proposal_emails_linked_to_contact(contact_id) {
        var data = {};
        data.update = true;
        data.original_email = $('body').find('#contact_update_proposals_emails').data('original-email');
        $.post(admin_url + 'clients/update_all_proposal_emails_linked_to_customer/' + contact_id, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
            }
            $('#contact').modal('hide');
        });
    }

    function do_share_file_contacts(edit_contacts, file_id) {
        var contacts_shared_ids = $('select[name="share_contacts_id[]"]');
        if (typeof(edit_contacts) == 'undefined' && typeof(file_id) == 'undefined') {
            var contacts_shared_ids_selected = $('select[name="share_contacts_id[]"]').val();
        } else {
            var _temp = edit_contacts.toString().split(',');
            for (var cshare_id in _temp) {
                contacts_shared_ids.find('option[value="' + _temp[cshare_id] + '"]').attr('selected', true);
            }
            contacts_shared_ids.selectpicker('refresh');
            $('input[name="file_id"]').val(file_id);
            $('#customer_file_share_file_with').modal('show');
            return;
        }
        var file_id = $('input[name="file_id"]').val();
        $.post(admin_url + 'clients/update_file_share_visibility', {
            file_id: file_id,
            share_contacts_id: contacts_shared_ids_selected,
            customer_id: $('input[name="userid"]').val()
        }).done(function() {
            window.location.reload();
        });
    }

    function save_longitude_and_latitude(clientid) {
        var data = {};
        data.latitude = $('#latitude').val();
        data.longitude = $('#longitude').val();
        $.post(admin_url + 'clients/save_longitude_and_latitude/' + clientid, data).done(function(response) {
            if (response == 'success') {
                alert_float('success', "<?php echo _l('updated_successfully', _l('client')); ?>");
            }
            setTimeout(function() {
                window.location.reload();
            }, 1200);
        }).fail(function(error) {
            alert_float('danger', error.responseText);
        });
    }

    function fetch_lat_long_from_google_cprofile() {
        var data = {};
        data.address = $('#long_lat_wrapper').data('address');
        data.city = $('#long_lat_wrapper').data('city');
        data.country = $('#long_lat_wrapper').data('country');
        $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
        $.post(admin_url + 'misc/fetch_address_info_gmaps', data).done(function(data) {
            data = JSON.parse(data);
            $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
            if (data.response.status == 'OK') {
                $('input[name="latitude"]').val(data.lat);
                $('input[name="longitude"]').val(data.lng);
            } else {
                if (data.response.status == 'ZERO_RESULTS') {
                    alert_float('warning', "<?php echo _l('g_search_address_not_found'); ?>");
                } else {
                    alert_float('danger', data.response.status + ' - ' + data.response.error_message);
                }
            }
        });
    }


    $(document).on('click', '.edit-customer-media', function() {
        var customer_id = $(this).data('customer-id');
        var media_id = $(this).data('media-id');
        var rel_type = $(this).data('rel-type');
        var rel_id = $(this).data('rel-id');
        changeMediaType(rel_type, rel_id);
        $('#customerMediaModal .modal-title').text("Edit Media");
        $('#customerMediaModal').find('input[name="id"]').val(media_id);
        $('#customerMediaModal').find('input[name="customer_id"]').val(customer_id);
        $('#customerMediaModal').find('select[name="rel_type"]').val(rel_type).selectpicker('refresh');
        $('#customerMediaModal').find('select[name="rel_id"]').val(rel_id).selectpicker('refresh');
        $('#customerMediaModal').modal('show');
    });

    function customerMediaFormPopup(customer_id) {
        $('#customerMediaModal .modal-title').text("Add New Media");
        $('#customerMediaModal').find('input[name="customer_id"]').val(customer_id);
        $('#customerMediaModal').find('input[name="id"]').val('');
        $('#customerMediaModal').find('select[name="rel_type"]').val('').selectpicker('refresh');
        $('#customerMediaModal').find('select[name="rel_id"]').val('').selectpicker('refresh');
        $('#customerMediaModal').modal('show');
    }

    function changeMediaType(selected_type = "", selected_id = "") {
        if (selected_type == '') {
            var selected_type = $('#customerMediaModal #rel_type').val();
        }
        $('#customerMediaModal #rel_id').html('<option value="">Select Media</option>');
        $('#customerMediaModal #rel_id').empty();
        $('#customerMediaModal #rel_id').selectpicker('refresh');
        $.ajax({
            url: "<?php echo admin_url('customer_media/get_media_by_type'); ?>",
            method: "POST",
            data: {
                type: selected_type
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $.each(result.data, function(index, item) {
                    if (selected_id != "" && selected_id == item.id) {
                        console.log("called");
                        $('#customerMediaModal #rel_id').append($('<option>', {
                            value: item.id,
                            text: item.title,
                            selected: true
                        }));
                    } else {
                        $('#customerMediaModal #rel_id').append($('<option>', {
                            value: item.id,
                            text: item.title
                        }));
                    }
                });
                $('#customerMediaModal #rel_id').selectpicker('refresh');
            } else {
                alert_float('danger', result.message);
            }
        });
    }
</script>