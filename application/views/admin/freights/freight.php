<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php echo form_open($this->uri->uri_string(), ['id' => 'freight-form']); ?>
                        
                        <div class="row">
                            <!-- FROM SECTION -->
                            <div class="col-md-6">
                                <h4>FROM</h4>
                                <?php
                                $from_country = (isset($freight) ? $freight->from_country : '');
                                $from_state = (isset($freight) ? $freight->from_state : '');
                                $from_city = (isset($freight) ? $freight->from_city : '');
                                ?>
                                <?php echo render_select('from_country', $countries, ['country_id', ['short_name']], 'Country', $from_country, ['data-location-role' => 'country', 'data-location-group' => 'from']); ?>
                                
                                <div class="from-location-state-wrapper location-group-from <?php echo empty($from_country) ? 'hide' : ''; ?>">
                                    <?php echo render_select('from_state', [], [], 'State', $from_state, ['data-location-role' => 'state', 'data-location-group' => 'from']); ?>
                                </div>
                                
                                <div class="from-location-city-wrapper location-group-from <?php echo empty($from_state) ? 'hide' : ''; ?>">
                                    <?php echo render_select('from_city', [], [], 'City/Port', $from_city, ['data-location-role' => 'city', 'data-location-group' => 'from']); ?>
                                </div>
                                
                                <?php echo render_input('from_pin', 'Pin Code', (isset($freight) ? $freight->from_pin : '')); ?>
                            </div>

                            <!-- TO SECTION -->
                            <div class="col-md-6">
                                <h4>TO</h4>
                                <?php
                                $to_country = (isset($freight) ? $freight->to_country : '');
                                $to_state = (isset($freight) ? $freight->to_state : '');
                                $to_city = (isset($freight) ? $freight->to_city : '');
                                ?>
                                <?php echo render_select('to_country', $countries, ['country_id', ['short_name']], 'Country', $to_country, ['data-location-role' => 'country', 'data-location-group' => 'to']); ?>
                                
                                <div class="to-location-state-wrapper location-group-to <?php echo empty($to_country) ? 'hide' : ''; ?>">
                                    <?php echo render_select('to_state', [], [], 'State', $to_state, ['data-location-role' => 'state', 'data-location-group' => 'to']); ?>
                                </div>
                                
                                <div class="to-location-city-wrapper location-group-to <?php echo empty($to_state) ? 'hide' : ''; ?>">
                                    <?php echo render_select('to_city', [], [], 'City/Port', $to_city, ['data-location-role' => 'city', 'data-location-group' => 'to']); ?>
                                </div>
                                
                                <?php echo render_input('to_pin', 'Pin Code', (isset($freight) ? $freight->to_pin : '')); ?>
                            </div>
                        </div>

                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                $group_id = (isset($freight) ? $freight->group_id : '');
                                echo render_select('group_id', $groups, ['id', 'name'], 'Truck/Container', $group_id);
                                ?>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="size_id" class="control-label">Size of Truck/Container</label>
                                    <select name="size_id" id="size_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_input('carrier', 'Carrier', (isset($freight) ? $freight->carrier : '')); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('freight_cost', 'Freight (Cost/Price)', (isset($freight) ? $freight->freight_cost : '')); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('transit_time', 'Transit Time', (isset($freight) ? $freight->transit_time : '')); ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        
                        <?php echo form_close(); ?>
                    </div>
                </div>
                
                <?php if(isset($freight)) { ?>
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Activity Log</h4>
                        <hr class="hr-panel-heading" />
                        <div class="activity-feed">
                            <?php foreach($activity_log as $log){ ?>
                            <div class="feed-item">
                                <div class="date">
                                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($log['date']); ?>">
                                        <?php echo time_ago($log['date']); ?>
                                    </span>
                                </div>
                                <div class="text">
                                    <?php if($log['staffid'] != 0){ ?>
                                    <a href="<?php echo admin_url('profile/'.$log['staffid']); ?>">
                                        <?php echo staff_profile_image($log['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                        ?>
                                    </a>
                                    <?php } ?>
                                    <?php
                                    echo $log['full_name'] . ' - ' . $log['description'];
                                    if($log['additional_data'] != ''){
                                        echo ' - ' . $log['additional_data'];
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<!-- Pass saved values to JS for pre-selection -->
<script>
    var saved_from_state = '<?php echo isset($freight) ? $freight->from_state : ''; ?>';
    var saved_from_city = '<?php echo isset($freight) ? $freight->from_city : ''; ?>';
    var saved_to_state = '<?php echo isset($freight) ? $freight->to_state : ''; ?>';
    var saved_to_city = '<?php echo isset($freight) ? $freight->to_city : ''; ?>';
    var saved_size_id = '<?php echo isset($freight) ? $freight->size_id : ''; ?>';
</script>

<script>
$(function(){
    appValidateForm($('#freight-form'), {
        from_country: 'required',
        to_country: 'required',
        from_state: 'required',
        from_city: 'required',
        to_state: 'required',
        to_city: 'required',
        group_id: 'required',
        size_id: 'required'
    });

    // Dependent dropdown for Truck/Container -> Size
    $('select[name="group_id"]').on('change', function() {
        var group_id = $(this).val();
        var $sizeSelect = $('select[name="size_id"]');
        $sizeSelect.empty().append('<option value=""></option>').selectpicker('refresh');
        
        if (group_id) {
            $.get(admin_url + 'freights/get_sizes_by_group/' + group_id, function(response) {
                var sizes = JSON.parse(response);
                $.each(sizes, function(i, size) {
                    var selected = (saved_size_id == size.id) ? ' selected' : '';
                    $sizeSelect.append('<option value="' + size.id + '"' + selected + '>' + size.name + '</option>');
                });
                $sizeSelect.selectpicker('refresh');
                saved_size_id = ''; // clear after first load
            });
        }
    });

    // Trigger change on load if editing
    if ($('select[name="group_id"]').val()) {
        $('select[name="group_id"]').trigger('change');
    }

    // Since we don't have the full client_js.php available on this page directly, 
    // and Perfex location JS relies on some global ajax URLs (`admin/misc/get_states` etc),
    // we implement the location dropdowns here manually for both groups (from / to).

    $('select[data-location-role="country"]').on('change', function() {
        var countryId = $(this).val();
        var group = $(this).data('location-group');
        var $stateSelect = $('select[data-location-group="' + group + '"][data-location-role="state"]');
        var $citySelect = $('select[data-location-group="' + group + '"][data-location-role="city"]');
        
        $stateSelect.empty().append('<option value=""></option>').selectpicker('refresh');
        $citySelect.empty().append('<option value=""></option>').selectpicker('refresh');
        
        if (countryId) {
            $.ajax({
                url: admin_url + 'leads/get_state_city',
                method: 'POST',
                data: { type: 'state', country_id: countryId },
                dataType: 'json'
            }).done(function(result) {
                if (result && result.success && result.data.length > 0) {
                    $('.' + group + '-location-state-wrapper').removeClass('hide');
                    $.each(result.data, function(i, item) {
                        var stateName = item.state;
                        if (stateName) {
                            var selected = ((group == 'from' && saved_from_state == stateName) || (group == 'to' && saved_to_state == stateName)) ? ' selected' : '';
                            $stateSelect.append('<option value="' + stateName + '"' + selected + '>' + stateName + '</option>');
                        }
                    });
                    $stateSelect.selectpicker('refresh');
                    if (group == 'from' && saved_from_state) {
                        $stateSelect.trigger('change');
                        saved_from_state = '';
                    } else if (group == 'to' && saved_to_state) {
                        $stateSelect.trigger('change');
                        saved_to_state = '';
                    }
                } else {
                    $('.' + group + '-location-state-wrapper').addClass('hide');
                }
            });
        } else {
            $('.' + group + '-location-state-wrapper').addClass('hide');
            $('.' + group + '-location-city-wrapper').addClass('hide');
        }
    });

    $('select[data-location-role="state"]').on('change', function() {
        var stateId = $(this).val();
        var group = $(this).data('location-group');
        var $citySelect = $('select[data-location-group="' + group + '"][data-location-role="city"]');
        
        $citySelect.empty().append('<option value=""></option>').selectpicker('refresh');
        
        if (stateId) {
            var countryId = $('select[data-location-group="' + group + '"][data-location-role="country"]').val();
            $.ajax({
                url: admin_url + 'leads/get_state_city',
                method: 'POST',
                data: { type: 'city', country_id: countryId, state: stateId },
                dataType: 'json'
            }).done(function(result) {
                if (result && result.success && result.data.length > 0) {
                    $('.' + group + '-location-city-wrapper').removeClass('hide');
                    $.each(result.data, function(i, item) {
                        var cityName = item.city;
                        if (cityName) {
                            var selected = ((group == 'from' && saved_from_city == cityName) || (group == 'to' && saved_to_city == cityName)) ? ' selected' : '';
                            $citySelect.append('<option value="' + cityName + '"' + selected + '>' + cityName + '</option>');
                        }
                    });
                    $citySelect.selectpicker('refresh');
                    if (group == 'from') saved_from_city = '';
                    if (group == 'to') saved_to_city = '';
                } else {
                    $('.' + group + '-location-city-wrapper').addClass('hide');
                }
            }).fail(function() {
                $('.' + group + '-location-city-wrapper').addClass('hide');
            });
        } else {
            $('.' + group + '-location-city-wrapper').addClass('hide');
        }
    });

    // Trigger on load
    if ($('select[data-location-group="from"][data-location-role="country"]').val()) {
        $('select[data-location-group="from"][data-location-role="country"]').trigger('change');
    }
    if ($('select[data-location-group="to"][data-location-role="country"]').val()) {
        $('select[data-location-group="to"][data-location-role="country"]').trigger('change');
    }

});
</script>
</body>
</html>
