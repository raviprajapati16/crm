<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
.dataTables_filter {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.dt-info-text {
    margin-right: 10px;
    white-space: nowrap;
    font-size: 15px;
}

.reset-filters-section {
    display: flex;
    justify-content: space-between;
}

.fa-search {
    cursor: pointer !important;
}
</style>
<div id="wrapper" data-module="<?= $module_type ?>">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="init_lead(); return false;"
                                class="btn mright5 btn-info pull-left display-block">
                                <?php echo _l('new_lead'); ?>
                            </a>
                            <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                            <a href="<?php echo admin_url('leads/import'); ?>"
                                class="btn btn-info pull-left display-block hidden-xs">
                                <?php echo _l('import_leads'); ?>
                            </a>
                            <?php } ?>
                            <?= tutorialLinkButtonRender('manage-lead-btn', 'right'); ?>
                            <div class="row">
                                <div class="col-md-5">
                                    <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip"
                                        data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom"
                                        onclick="slideToggle('.leads-overview'); return false;"><i
                                            class="fa fa-bar-chart"></i></a>
                                    <!-- <a href="<?php echo admin_url('leads/switch_kanban/' . $switch_kanban); ?>" class="btn btn-default mleft10 hidden-xs">
                              <?php if ($switch_kanban == 1) {
                                    echo _l('leads_switch_to_kanban');
                                } else {
                                    echo _l('switch_to_list_view');
                                }; ?>
                           </a> -->
                                </div>
                                <div class="col-md-4 col-xs-12 pull-right leads-search">
                                    <?php if ($this->session->userdata('leads_kanban_view') == 'true') { ?>
                                    <div data-toggle="tooltip" data-placement="bottom"
                                        data-title="<?php echo _l('search_by_tags'); ?>">
                                        <?php echo render_input('search', '', '', 'search', array('data-name' => 'search', 'onkeyup' => 'leads_kanban();', 'placeholder' => _l('leads_search')), array(), 'no-margin') ?>
                                    </div>
                                    <?php } ?>
                                    <?php echo form_hidden('sort_type'); ?>
                                    <?php echo form_hidden('sort', (get_option('default_leads_kanban_sort') != '' ? get_option('default_leads_kanban_sort_type') : '')); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row hide leads-overview">
                                <hr class="hr-panel-heading" />
                                <div class="col-md-12">
                                    <h4 class="no-margin">
                                        <?php echo _l('leads_summary'); ?>
                                    </h4>
                                </div>
                                <?php
                                foreach ($summary as $status) { ?>
                                <div class="col-md-2 col-xs-6 ">
                                    <h3 class="bold">
                                        <?php
                                            if (isset($status['percent'])) {
                                                echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                            } else {
                                                // Is regular status
                                                echo $status['total'];
                                            }
                                            ?>
                                    </h3>
                                    <span style="color:<?php echo $status['color']; ?>"
                                        class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>">
                                        <?php echo $status['name']; ?>
                                    </span>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <?php render_lead_color_legend($statuses); ?>
                        <div class="tab-content">
                            <?php
                            if ($this->session->has_userdata('leads_kanban_view') && $this->session->userdata('leads_kanban_view') == 'true') { ?>
                            <div class="active kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                                <div class="kanban-leads-sort">
                                    <span class="bold">
                                        <?php echo _l('leads_sort_by'); ?>:
                                    </span>
                                    <a href="#" onclick="leads_kanban_sort('dateadded'); return false"
                                        class="dateadded">
                                        <?php if (get_option('default_leads_kanban_sort') == 'dateadded') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>
                                        <?php echo _l('leads_sort_by_datecreated'); ?>
                                    </a>
                                    |
                                    <a href="#" onclick="leads_kanban_sort('leadorder');return false;"
                                        class="leadorder">
                                        <?php if (get_option('default_leads_kanban_sort') == 'leadorder') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>
                                        <?php echo _l('leads_sort_by_kanban_order'); ?>
                                    </a>
                                    |
                                    <a href="#" onclick="leads_kanban_sort('lastcontact');return false;"
                                        class="lastcontact">
                                        <?php if (get_option('default_leads_kanban_sort') == 'lastcontact') {
                                                echo '<i class="kanban-sort-icon fa fa-sort-amount-' . strtolower(get_option('default_leads_kanban_sort_type')) . '"></i> ';
                                            } ?>
                                        <?php echo _l('leads_sort_by_lastcontact'); ?>
                                    </a>
                                </div>
                                <div class="row">
                                    <div class="container-fluid leads-kan-ban">
                                        <div id="kan-ban"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row" id="leads-table">

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12 reset-filters-section">
                                            <div class="bold"><?php echo _l('filter_by'); ?></div>
                                            <button type="button" class="btn btn-primary pull-right reset-filters">Reset
                                                Filter</button>
                                        </div>
                                        <?php
                                            $staff_users = [];
                                            if (has_permission('leads', '', 'view_own') && is_manager()) {
                                            ?>
                                        <?php if (!empty($staff)) {
                                                    foreach ($staff as $user) {
                                                        if ((manager_employee_data_access_permission_check("leads") && in_array($user['staffid'], get_manager_assigned_staff_ids()) && is_staff_in_sales_department($user['id'])) || $user['staffid'] == get_staff_user_id()) {
                                                            $staff_users[] = $user;
                                                ?>
                                        <?php
                                                        }
                                                        ?>
                                        <?php
                                                    }
                                                }
                                            } else {
                                                foreach ($staff as $staffData) {
                                                    if (is_staff_in_sales_department($staffData['staffid'])) {
                                                        $staff_users[] = $staffData;
                                                    }
                                                }
                                            } ?>
                                        <?php
                                            if (has_permission('leads', '', 'view') || is_manager()) {
                                            ?>
                                        <div class="col-md-2 leads-filter-column">
                                            <label for="view_assigned" class="control-label">Assigned</label>
                                            <?php echo render_select('view_assigned', $staff_users, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'data-size' => 6, 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot'); ?>
                                        </div>
                                        <?php
                                            }
                                            ?>
                                        <div class="col-md-2 leads-filter-column">
                                            <label for="rel_type" class="control-label">Lead Status</label>
                                            <?php
                                                $selected = array();
                                                if (empty($_GET)) {
                                                    if ($this->input->get('status')) {
                                                        $selected[] = $this->input->get('status');
                                                    } else {
                                                        foreach ($statuses as $key => $status) {
                                                            if ($status['isdefault'] == 0) {
                                                                if ($status['name'] != 'NOT INTRESTED' && $status['name'] != 'Compititor') {
                                                                    $selected[] = $status['name'];
                                                                }
                                                            } else {
                                                                 if($status['id'] == 1){
                                                                    $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
                                                                }
                                                                if($status['id'] == 75){
                                                                    $statuses[$key]['option_attributes'] = ['data-subtext' => "Assigned to Customer"];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                echo '<div id="leads-filter-status">';
                                                echo render_select('view_status', $statuses, array('name', 'name'), '', $selected, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => true, 'data-actions-box' => true), array(), 'no-mbot', '', false);
                                                echo '</div>';
                                                ?>
                                        </div>
                                        <div
                                            class="col-md-2 leads-filter-column not-interested-from-status-filter hide">
                                            <label for="rel_type" class="control-label">From Status To Not
                                                Interested</label>
                                            <?php
                                                $from_statuses = array();
                                                if (!empty($statuses)) {
                                                    foreach ($statuses as $key => $status) {
                                                        if ($status['name'] != 'NOT INTRESTED') {
                                                            $from_statuses[] = $status;
                                                        }
                                                    }
                                                }
                                                echo '<div id="leads-filter-status">';
                                                echo render_select('no_interested_from', $from_statuses, array('name', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => "Select Status"), array(), 'no-mbot');
                                                echo '</div>';
                                                ?>
                                        </div>
                                        <div class="col-md-2 leads-filter-column">
                                            <label for="rel_type" class="control-label">Lead Source</label>
                                            <?php
                                                echo render_select('view_source', $sources, array('name', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('leads_source')), array(), 'no-mbot');
                                                ?>
                                        </div>
                                        <!--start country select -->
                                        <div class="col-md-2 ">
                                            <label for="rel_type" class="control-label">Country</label>
                                            <?php
                                                $selectedCountry = [];
                                                if (isset($_GET['country'])) {
                                                    array_push($selectedCountry, $_GET['country'], strtolower($_GET['country']), strtoupper($_GET['country']));
                                                }
                                                echo render_select('countries_filter', $lead_countries, array('name', 'name'), '', $selectedCountry, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false);
                                                ?>
                                        </div>
                                        <div class="col-md-2 ">
                                            <label for="rel_type" class="control-label">State</label>
                                            <?php
                                                $selectedState = [];
                                                if (isset($_GET['state'])) {
                                                    array_push($selectedState, $_GET['state'], strtolower($_GET['state']), strtoupper($_GET['state']));
                                                }
                                                echo render_select('states_filter', $lead_states, array('id', 'name'), '', $selectedState, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false);
                                                ?>
                                        </div>

                                        <div class="col-md-2 mtop10 leads-filter-column">
                                            <label for="rel_type" class="control-label">City</label>
                                            <?php
                                                $selectedCity = [];
                                                if (isset($_GET['city'])) {
                                                    array_push($selectedCity, $_GET['city'], strtolower($_GET['city']), strtoupper($_GET['city']));
                                                }
                                                echo render_select('cities_filter', $lead_cities, array('name', 'name'), '', $selectedCity, array('data-width' => '100%', 'data-none-selected-text' => _l('leads_all'), 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', '', false);
                                                ?>
                                        </div>

                                        <div class="col-md-2 mtop10 leads-filter-column">
                                            <label for="rel_type" class="control-label">Products</label>
                                            <?php
                                                $selectedProduct = [];
                                                if (isset($_GET['product'])) {
                                                    array_push($selectedProduct, $_GET['product'], strtolower($_GET['product']), strtoupper($_GET['product']));
                                                }
                                                echo render_select('view_products', $products, array('name', 'name'), '', $selectedProduct, array('data-width' => '100%', 'data-none-selected-text' => _l('tags')), array(), 'no-mbot');

                                                ?>
                                        </div>

                                        <div class="col-md-2 mtop10 leads-filter-column">
                                            <label for="custom_view" class="control-label">Lead Filter</label>
                                            <div class="select-placeholder">
                                                <select name="custom_view"
                                                    title="<?php echo _l('additional_filters'); ?>" id="custom_view"
                                                    class="selectpicker" data-width="100%">
                                                    <option value=""></option>
                                                    <option value="lost"><?php echo _l('lead_lost'); ?></option>
                                                    <option value="junk"><?php echo _l('lead_junk'); ?></option>
                                                    <option value="public"><?php echo _l('lead_public'); ?></option>
                                                    <option value="contacted_today">
                                                        <?php echo _l('lead_add_edit_contacted_today'); ?></option>
                                                    <option value="created_today"><?php echo _l('created_today'); ?>
                                                    </option>
                                                    <option value="today_leads">Today Leads</option>
                                                    <option value="lapsed_lead"><?php echo _l('lapsed_lead'); ?>
                                                    </option>
                                                    <?php if (has_permission('leads', '', 'edit')) { ?>
                                                    <option value="not_assigned"><?php echo _l('leads_not_assigned'); ?>
                                                    </option>
                                                    <?php } ?>
                                                    <?php if (is_admin()) { ?>
                                                    <option value="deleted"><?php echo _l('deleted_lead'); ?></option>
                                                    <?php } ?>
                                                    <?php if (isset($consent_purposes)) { ?>
                                                    <optgroup label="<?php echo _l('gdpr_consent'); ?>">
                                                        <?php foreach ($consent_purposes as $purpose) { ?>
                                                        <option value="consent_<?php echo $purpose['id']; ?>">
                                                            <?php echo $purpose['name']; ?>
                                                        </option>
                                                        <?php } ?>
                                                    </optgroup>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 leads-filter-column mtop10">
                                            <label for="rel_type" class="control-label">Followup Date</label>
                                            <?php echo render_date_input('followup_date', '', '', array('title' => "Followup date", 'placeholder' => 'Followup Date', 'name' => 'followupdate'), '', 'no-mbot', 'followupdate'); ?>
                                            <!--?php echo render_date_input('followup_date', '', date('d-m-Y'), array('onchange' => "dt_custom_view('followup_date'); return false;", array('name', 'followupdate')), '', 'no-mbot', 'followupdate'); -->
                                        </div>

                                        <div class="col-md-2 leads-filter-column mtop10">
                                            <label for="date_by" class="control-label"><span
                                                    class="control-label">FilterByDate</span></label>
                                            <select name="date_by" id="date_by" class="selectpicker no-margin"
                                                title="FilterBy Date" data-width=" 100%">
                                                <option value=""></option>
                                                <option value="dateadded">By Date Added</option>
                                                <option value="lastcontact"
                                                    <?php echo ($date_by != '' && $date_by == 'lastcontact' ? 'selected' : '') ?>>
                                                    By Last Contacted</option>
                                                <option value="Call">By Call</option>
                                                <option value="Online Meeting">By Online Meeting</option>
                                                <option value="Plant Visit">By Plant Visit</option>
                                                <option value="Face To Face">By Face to Face Meeting</option>
                                                <option value="Inquiry Forms">By Inquiry Forms</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2 leads-filter-column mtop10">
                                            <label for="rel_type" class="control-label">From Date</label>
                                            <?php echo render_date_input('from_date', '', '', array('title' => "From date", 'placeholder' => 'From Date'), '', 'no-mbot', 'fromdate'); ?>
                                        </div>

                                        <div class="col-md-2 leads-filter-column mtop10">
                                            <label for="rel_type" class="control-label">To Date</label>
                                            <?php echo render_date_input('to_date', '', '', array('title' => "To date", 'placeholder' => 'To Date'), '', 'no-mbot', 'todate'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <?php } ?>
                        </div>
                        <div id="overlay" class="mtop10">
                            <div class="dt-loader">
                                <span></span>
                            </div>
                        </div>
                        <div class="datatable-section d-none mtop10">
                            <div class="modal fade bulk_actions" id="leads_bulk_actions" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php if (has_permission('leads', '', 'delete')) { ?>
                                            <div class="checkbox checkbox-danger">
                                                <input type="checkbox" name="mass_delete" id="mass_delete">
                                                <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                            </div>
                                            <hr class="mass_delete_separator" />
                                            <?php } ?>
                                            <div id="bulk_change">
                                                <div class="form-group">
                                                    <div class="checkbox checkbox-primary checkbox-inline">
                                                        <input type="checkbox" name="leads_bulk_mark_lost"
                                                            id="leads_bulk_mark_lost" value="1">
                                                        <label for="leads_bulk_mark_lost">
                                                            <?php echo _l('lead_mark_as_lost'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php echo render_select('move_to_status_leads_bulk', $statuses, array('id', 'name'), 'ticket_single_change_status'); ?>
                                                <?php
                                                echo render_select('move_to_source_leads_bulk', $sources, array('id', 'name'), 'lead_source');
                                                echo render_datetime_input('leads_bulk_last_contact', 'leads_dt_last_contact');

                                                if (is_manager()) {
                                                ?>
                                                <div class="form-group">
                                                    <label for="state"
                                                        class="control-label"><?= _l('leads_dt_assigned') ?></label>
                                                    <select id="assign_to_leads_bulk" name="assign_to_leads_bulk"
                                                        class="selectpicker" data-live-search="true"
                                                        data-none-selected-text="" data-width="100%">
                                                        <option value=""></option>
                                                        <?php if (!empty($staff)) {
                                                                foreach ($staff as $user) {
                                                                    if (manager_employee_data_access_permission_check("leads") && is_staff_in_sales_department($user['staffid'])  && in_array($user['staffid'], get_manager_assigned_staff_ids())) {
                                                            ?>
                                                        <option value="<?= $user['staffid'] ?>">
                                                            <?= $user['firstname'] ?> <?= $user['lastname'] ?></option>
                                                        <?php
                                                                    }
                                                                    ?>
                                                        <?php
                                                                }
                                                            } ?>
                                                    </select>
                                                </div>
                                                <?php

                                                } else if (has_permission('leads', '', 'edit')) {
                                                    $staff_users = [];
                                                    foreach ($staff as $staffData) {
                                                        if (is_staff_in_sales_department($staffData['staffid'])) {
                                                            $staff_users[] = $staffData;
                                                        }
                                                    }
                                                    echo render_select('assign_to_leads_bulk', $staff_users, array('staffid', array('firstname', 'lastname')), 'leads_dt_assigned');
                                                }
                                                    ?><?php
                                                        echo render_input('state', "State");
                                                        ?>
                                                <?php
                                                    echo render_input('city', "City");
                                                    ?>
                                                <div class="form-group">
                                                    <label for="country" class="control-label">Country</label>
                                                    <select id="country" name="country"
                                                        class="form-control selectpicker"
                                                        data-none-selected-text="Select Country">
                                                        <option value=""></option>
                                                        <?php if (!empty($all_countries)) {
                                                                foreach ($all_countries as $key => $country) {
                                                            ?>
                                                        <option value="<?= $country['country_id'] ?>">
                                                            <?= $country['short_name'] ?></option>
                                                        <?php
                                                                }
                                                                ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <?php echo '<p><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
                                                    <input type="text" class="tagsinput" id="tags_bulk" name="tags_bulk"
                                                        value="">
                                                </div>
                                                <hr />
                                                <div class="form-group no-mbot">
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" name="leads_bulk_visibility"
                                                            id="leads_bulk_public" value="public">
                                                        <label for="leads_bulk_public">
                                                            <?php echo _l('lead_public'); ?>
                                                        </label>
                                                    </div>
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" name="leads_bulk_visibility"
                                                            id="leads_bulk_private" value="private">
                                                        <label for="leads_bulk_private">
                                                            <?php echo _l('private'); ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default"
                                                data-dismiss="modal"><?php echo _l('close'); ?></button>
                                            <a id="bulkaction_confirm_btn" href="#" class="btn btn-info"
                                                onclick="leads_new_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="leads_table" style="width:100% !important;"
                                class="table customizable-table dataTable dtr-inline collapsed data-table   table-responsive table-leadsnew">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="checkbox mass_select_all_wrap"><input type="checkbox"
                                                    id="mass_select_all" data-to-table="leadsnew"><label></label></div>
                                        </th>
                                        <th>#</th>
                                        <th>Country</th>
                                        <th>State</th>
                                        <th>Company</th>
                                        <th>Phone Number</th>
                                        <th>Status</th>
                                        <th>Follow up date</th>
                                        <th>Last Contact</th>
                                        <th>Assigned</th>
                                        <th>City</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Products</th>
                                        <th>Source</th>
                                        <th>Created</th>
                                        <th>Deleted Date</th>
                                        <th>Deleted By</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once APPPATH . 'views/admin/leads/status.php'; ?>
<?php init_tail(); ?>

<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/2.0.3/pagination/select.js"></script>

<script>
var leadIndexUrl = "<?php echo admin_url('leads/index/'); ?>";
var leadEditUrl = "<?php echo admin_url('leads/index/'); ?>";
var leadDeleteUrl = "<?php echo admin_url('leads/delete/'); ?>";
var leadRestoreUrl = "<?php echo admin_url('leads/restore/'); ?>";
var table;
var resultData;
var statusData = JSON.parse('<?php echo json_encode($statuses) ?>');
var lockAfterConvert = '<?php echo get_option('lead_lock_after_convert_to_customer'); ?>';
var is_admin = '<?php echo is_admin() ?>';
var get_staff_user_id = '<?php echo get_staff_user_id() ?>';
var deletePermissionCheck = '<?php echo has_permission('leads', '', 'delete') ?>';
var staffExportPermission = '<?php echo staff_can('export', 'leads'); ?>';

$(document).ready(function() {
    $('#view_products').append($('<option>', {
        value: 'No Product',
        text: 'No Product'
    }));
    $('#view_products').selectpicker({
        liveSearchNormalize: true,
        liveSearchStyle: 'startsWith'
    }).selectpicker('refresh');

    $('#view_source').selectpicker({
        liveSearchNormalize: true,
        liveSearchStyle: 'startsWith'
    }).selectpicker('refresh');



    $('.reset-filters').on('click', function() {
        $('#from_date').val("");
        $('#to_date').val("");
        $('#followup_date').val("");
        $('#view_assigned').val("").selectpicker('refresh');
        $('#view_status option').each(function() {
            let text = $(this).val();
            if (text !== "CUSTOMER" && text !== "NOT INTRESTED") {
                $(this).prop('selected', true);
            }
        });
        $('#view_status').selectpicker('refresh');
        $('#view_source').val("").selectpicker('refresh');
        $('#countries_filter').val("").selectpicker('refresh');
        $('#view_products').val("").selectpicker('refresh');
        $('#custom_view').val("").selectpicker('refresh');
        $('#date_by').val("").selectpicker('refresh');
        $('#no_interested_from').val("").selectpicker('refresh');

        $('#states_filter option').prop('selected', false);
        $('#cities_filter option').prop('selected', false);
        $('#states_filter').selectpicker('refresh');
        $('#cities_filter').selectpicker('refresh');

        if (table) {
            table.search('').draw();
        }
    });

    var exportHideClass = "";
    if (!staffExportPermission) {
        exportHideClass = "d-none";
    }
    loadTableData();

    function loadTableData() {
        let currentXHR = null;
        table = $('#leads_table').DataTable({
            ajax: {
                url: '<?php echo admin_url('leadsnew/get_lead'); ?>',
                type: "POST",
                data: function(d) {
                    d.assigneFilter = $('#view_assigned').val();
                    d.statusFilter = $('#view_status').val();
                    d.fromStatusToNotInterestedFilter = $('#no_interested_from').val();
                    d.sourceFilter = $('#view_source').val();
                    d.countryFilter = $('#countries_filter').val();
                    d.stateFilter = $('#states_filter').val();
                    d.cityFilter = $('#cities_filter').val();
                    d.productFilter = $('#view_products').val();
                    d.customFilter = $('#custom_view').val();
                    d.followUpdateFilter = $('#followup_date').val();
                    d.dateTypeFilter = $('#date_by').val();
                    d.fromDate = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                },
                beforeSend: function(jqXHR) {
                    if (currentXHR && currentXHR.readyState !== 4) {
                        currentXHR.abort();
                    }
                    currentXHR = jqXHR;
                    //$('tbody').html('<tr><td colspan="100%" style="text-align: center; height: 100px; vertical-align: middle;"><div class="dt-loader"><span></span></div></td></tr>');
                },
                complete: function() {
                    currentXHR = null;
                }
            },
            order: [
                [12, 'desc']
            ],
            lengthMenu: [
                [10, 25, 50, 100, "-1"],
                [10, 25, 50, 100, "All"]
            ],
            language: {
                sLengthMenu: "_MENU_",
                search: '',
                searchPlaceholder: "Search..."
            },
            searching: true,
            search: {
                return: true
            },
            rowCallback: function(row, data, index) {
                $(row).attr('role', 'row');
                $(row).attr('leadid', data.id);
            },
            dom: "<'row'<'col-md-7'lB><'col-md-5'f>>rt<'row'<'col-md-4'i>><'row'<'#colvis'><'.dt-page-jump'>p>",
            buttons: [{
                    extend: 'collection',
                    text: 'Export',
                    className: 'btn btn-default buttons-collection btn-default-dt-options ' +
                        exportHideClass,
                    buttons: [{
                            extend: 'excelHtml5',
                            className: 'export-dropdown-btn',
                            exportOptions: {
                                orthogonal: 'export'
                            },
                            filename: function() {
                                return exportFileNameGenerate("lead_export");
                            },
                        },
                        {
                            extend: 'csvHtml5',
                            className: 'export-dropdown-btn',
                            exportOptions: {
                                orthogonal: 'export'
                            },
                            filename: function() {
                                return exportFileNameGenerate("lead_export");
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'export-dropdown-btn',
                            orientation: 'landscape',
                            pageSize: 'A2',
                            exportOptions: {
                                orthogonal: 'export'
                            },
                            filename: function() {
                                return exportFileNameGenerate("lead_export");
                            },
                        },
                        {
                            extend: 'print',
                            className: 'export-dropdown-btn',
                            exportOptions: {
                                orthogonal: 'export'
                            },
                            filename: function() {
                                return exportFileNameGenerate("lead_export");
                            },
                        }
                    ]
                },
                {
                    text: "<?php echo _l('dt_button_export_all'); ?>",
                    className: 'btn btn-default btn-default-dt-options ' + exportHideClass,
                    action: function(e, dt, node, config) {
                        location.href = '<?php echo admin_url('leadsnew/export_all_csv'); ?>';
                    }
                },
                {
                    text: "<?php echo _l('bulk_actions'); ?>",
                    className: 'btn btn-default btn-default-dt-options',
                    action: function(e, dt, node, config) {
                        $('#leads_bulk_actions').modal('show');
                    }
                },
                {
                    text: "",
                    className: "btn btn-default btn-default-dt-options fa fa-refresh",
                    action: function(e, dt, node, config) {
                        table.draw();
                    }
                }
            ],
            columns: [{
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        if (type == "export") {
                            return "";
                        }
                        return '<div class="checkbox">' +
                            '<input type="checkbox" value="' + data.id +
                            '"><label></label></div>';
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row, meta) {
                        const serialNumber = meta.row + 1; // 0-based index to 1-based
                        if (type == "export") {
                            return serialNumber;
                        }
                        return '<a href="javascript:;" onclick="init_lead(' + row.id +
                            ');return false;">' + serialNumber + '</a>';
                    }
                },
                // {
                //     data: 'id',
                //     render: function(data, type, row) {
                //         if (type == "export") {
                //             return data;
                //         }
                //         return '<a href="javascript:;" onclick="init_lead(' + row.id +
                //             ');return false;">' + data + '</a>';
                //     }
                // },
                {
                    data: 'country_name',
                    render: function(data, type, row) {
                        if (type == "export") {
                            return data;
                        }
                        var deleteRestoreHtml = "";
                        if (deletePermissionCheck) {
                            if (row.isDeleted == "true") {
                                deleteRestoreHtml = '| <a data-url="' + leadRestoreUrl + row
                                    .id +
                                    '" href="javascript:;" class="restore_lead _delete text-success">Restore</a>';
                            } else {
                                deleteRestoreHtml = '| <a data-url="' + leadDeleteUrl + row.id +
                                    '" href="javascript:;" class="delete_lead _delete text-danger">Delete</a>';
                            }
                        }
                        var rowOptionsHtml = '<a href="javascript:;" onclick="init_lead(' + row
                            .id + ');return false;">' + data + '</a>';
                        if (!row.edit_locked) {
                            rowOptionsHtml +=
                                '<div class="row-options">' +
                                '<a href="' + leadIndexUrl + row.id +
                                '" onclick="init_lead(' + row.id +
                                '); return false;">View</a> | ' +
                                '<a href="' + leadEditUrl + row.id +
                                '" onclick="init_lead(' + row.id +
                                ',true); return false;">Edit</a> ' + deleteRestoreHtml +
                                '</div>';
                        } else {
                            rowOptionsHtml +=
                                '<div class="row-options">' +
                                '<a href="' + leadIndexUrl + row.id +
                                '" onclick="init_lead(' + row.id +
                                '); return false;">View</a> | ' +
                                '<?php echo _l("edit") ?>' + deleteRestoreHtml +
                                '</div>';
                        }
                        return rowOptionsHtml;
                    }
                },
                {
                    data: 'state',
                    render: function(data, type, row) {
                        if (data != "" && data != null) {
                            if (type == "export") {
                                return data;
                            }
                            return '<a href="javascript:;" onclick="init_lead(' + row.id +
                                ');return false;">' + data + '</a>';
                        } else {
                            return "";
                        }
                    }
                },
                {
                    data: 'company',
                    render: function(data, type, row) {
                        if (data != "" && data != null) {
                            if (type == "export") {
                                return data;
                            }
                            return '<a href="javascript:;" onclick="init_lead(' + row.id +
                                ');return false;">' + data + '</a>';
                        } else {
                            return "";
                        }
                    }
                },
                {
                    data: 'phonenumber',
                    render: function(data, type, row) {
                        if (type == "export") {
                            return data;
                        }
                        return '<a href="tel:' + data + '">' + data + '</a>';
                    }
                },
                {
                    data: 'status_name',
                    render: function(data, type, row, meta) {
                        if (type == "export") {
                            return data;
                        }
                        return renderStatusDropdown(row);
                    }
                },
                {
                    data: 'reminderdate',
                    "render": function(data, type, row) {
                        if (data) {
                            return dateConvert(data)
                        } else {
                            return '';
                        }
                    }
                },
                {
                    data: 'lastcontact',
                    render: function(data, type, row, meta) {
                        if (data) {
                            if (type == "export") {
                                return $(data).attr("data-title");
                            }
                        }
                        return data;
                    }
                },
                {
                    data: 'assigned_output',
                    render: function(data, type, row, meta) {
                        if (type == "export") {
                            return $(data).attr("data-title");
                        }
                        return data;
                    }
                },
                {
                    data: 'city'
                },
                {
                    data: 'name',
                    // render: function(data, type, row) {
                    //     if (type == "export") {
                    //         return data;
                    //     }
                    //     var deleteRestoreHtml = "";
                    //     if (deletePermissionCheck) {
                    //         if (row.isDeleted == "true") {
                    //             deleteRestoreHtml = '| <a data-url="' + leadRestoreUrl + row
                    //                 .id +
                    //                 '" href="javascript:;" class="restore_lead _delete text-success">Restore</a>';
                    //         } else {
                    //             deleteRestoreHtml = '| <a data-url="' + leadDeleteUrl + row.id +
                    //                 '" href="javascript:;" class="delete_lead _delete text-danger">Delete</a>';
                    //         }
                    //     }
                    //     var rowOptionsHtml = '<a href="javascript:;" onclick="init_lead(' + row
                    //         .id + ');return false;">' + data + '</a>';
                    //     if (!row.edit_locked) {
                    //         rowOptionsHtml +=
                    //             '<div class="row-options">' +
                    //             '<a href="' + leadIndexUrl + row.id +
                    //             '" onclick="init_lead(' + row.id +
                    //             '); return false;">View</a> | ' +
                    //             '<a href="' + leadEditUrl + row.id +
                    //             '" onclick="init_lead(' + row.id +
                    //             ',true); return false;">Edit</a> ' + deleteRestoreHtml +
                    //             '</div>';
                    //     } else {
                    //         rowOptionsHtml +=
                    //             '<div class="row-options">' +
                    //             '<a href="' + leadIndexUrl + row.id +
                    //             '" onclick="init_lead(' + row.id +
                    //             '); return false;">View</a> | ' +
                    //             '<?php echo _l("edit") ?>' + deleteRestoreHtml +
                    //             '</div>';
                    //     }
                    //     return rowOptionsHtml;
                    // }
                },

                {
                    data: 'email',
                    render: function(data, type, row) {
                        if (data != "" && data != null) {
                            if (type == "export") {
                                return data;
                            }
                            return '<a href="mailto:' + data + '">' + data + '</a>';
                        } else {
                            return "";
                        }

                    }
                },

                {
                    data: 'tags',
                    render: function(data, type, row) {
                        if (data) {
                            data = data.replace('New Inquiry for', '');
                            data = data.replace('Requirement for', '');
                            data = data.trim();
                            if (data === '') {
                                return 'No Product';
                            } else {
                                if (type == "export") {
                                    return data;
                                }
                                data = data.split(",");
                                var productHtml = '<div class="tags-labels">';
                                $.each(data, function(index, item) {
                                    productHtml += '<span class="label label-tag">' +
                                        '<span class="tag">' + item + '</span>' +
                                        '<span class="hide">, </span>' +
                                        '</span>';
                                });
                                productHtml += '</div>';
                                return productHtml;
                            }
                        } else {
                            return 'No Product';
                        }
                    }
                },




                {
                    data: 'source_name'
                },

                {
                    data: 'dateadded',
                    render: function(data, type, row, meta) {
                        if (data) {
                            if (type == "export") {
                                return $(data).attr("data-title");
                            }
                        }
                        return data;
                    }
                },

                {
                    data: 'datedeleted',
                    visible: false,
                    render: function(data, type, row, meta) {
                        if (data) {
                            return dateConvert(data)
                        } else {
                            return '-';
                        }
                    }
                },
                {
                    data: 'deletedBy',
                    visible: false,
                    render: function(data, type, row, meta) {
                        if (data) {
                            return data
                        } else {
                            return '-';
                        }
                    }
                },
            ],
            // Define other DataTable options here
            processing: true,
            responsive: true,
            pageLength: 50,
            paging: true,
            serverSide: true,
            createdRow: function(row, data, dataIndex) {
                var rowColor = "";
                if ($('#date_by').val() == "Inquiry Forms") {
                    if (data.form_status == "pending") {
                        rowColor = 'alert-my-info';
                    } else if (data.form_status == "send") {
                        rowColor = 'alert-info';
                    } else if (data.form_status == "approved") {
                        rowColor = 'alert-success';
                    } else if (data.form_status == "not-approved") {
                        rowColor = 'alert-danger';
                    }
                } else {
                    rowColor = getLeadRowColorClass(data, get_staff_user_id);
                }

                if (rowColor) {
                    $(row).addClass(rowColor);
                }
            },
            drawCallback: function(oSettings) {

                $('#overlay').css('display', 'none');
                $('.datatable-section').removeClass('d-none');
                $("tr").on("click", "[type=checkbox]", function(e) {
                    e.stopPropagation();
                });

                var start = oSettings._iDisplayStart + 1;
                var end = oSettings._iDisplayStart + oSettings._iDisplayLength;
                var total = oSettings.fnRecordsTotal();
                end = end > total ? total : end;

                var headerInfo = 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';

                var $filterDiv = $(this).closest('.dataTables_wrapper').find('.dataTables_filter');
                var $infoSpan = $filterDiv.find('.dt-info-text');
                if ($infoSpan.length === 0) {
                    $filterDiv.prepend('<span class="dt-info-text">' + headerInfo + '</span>');
                } else {
                    $infoSpan.html(headerInfo);
                }

            },
            initComplete: function(settings, json) {
                var info = this.api().page.info();
                var start = info.start + 1;
                var end = info.end;
                var total = info.recordsTotal;
                var headerInfo = 'Showing ' + start + ' to ' + end + ' of ' + total + ' entries';
                $(this).closest('.dataTables_wrapper').find('.dt-info-header').html(headerInfo);
            }
        });

        table.on('draw', function() {
            updateLengthMenu();
            var select = $('<select>')
                .appendTo('.dataTables_paginate')
                .addClass('dt-page-jump-select')
                .addClass('form-control')
                .on('change', function() {
                    var val = Number($(this).val()) - 1;
                    table.page(val).draw('page');
                });

            var pageInfo = table.page.info();
            var currentPage = pageInfo.page + 1;
            var totalPages = pageInfo.pages;

            select.empty();
            for (var i = 1; i <= totalPages; i++) {
                var option = $('<option>', {
                    value: i,
                    text: i
                });

                if (i === currentPage) {
                    option.attr('selected', 'selected');
                }

                select.append(option);
            }
        }).draw();

        $('.dataTables_filter input').off('keyup.DT search.DT input.DT paste.DT cut.DT')
            .on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.search(this.value).draw();
                }
            });
        $('.dataTables_filter').append(
            $('<span>')
            .css('padding', '5px')
            .html('<i class="fa fa-search"></i>')
            .on('click', function() {
                table.search($('.dataTables_filter input').val()).draw();
            })
        );
    }

    $('#date_by, #from_date, #to_date')
        .on('change', function() {
            if (is_admin && $('#custom_view').val() == "deleted") {
                table.column(16).visible(true);
                table.column(17).visible(true);
            } else {
                table.column(16).visible(false);
                table.column(17).visible(false);
            }
            updateLengthMenu();
            var date_by = $('#date_by').val();
            var fromDate = $('#from_date').val();
            if (date_by && fromDate) {
                table.draw();
            }
        });

    $('#view_status').on('change', function() {
        let selectedValues = $(this).val();
        if (selectedValues && selectedValues.length == 1 && selectedValues[0] == "NOT INTRESTED") {
            $('.not-interested-from-status-filter select').val('').selectpicker('refresh');
            $('.not-interested-from-status-filter').removeClass('hide');
        } else {
            $('.not-interested-from-status-filter select').val('').selectpicker('refresh');
            $('.not-interested-from-status-filter').addClass('hide');
        }
    });


    $('#view_assigned, #view_status, #no_interested_from ,#view_source, #countries_filter, #states_filter, #cities_filter, #view_products, #custom_view, #followup_date')
        .on('change', function() {
            if (is_admin && $('#custom_view').val() == "deleted") {
                table.column(16).visible(true);
                table.column(17).visible(true);
            } else {
                table.column(16).visible(false);
                table.column(17).visible(false);
            }
            updateLengthMenu();
            table.draw();
        });
    $('#countries_filter').on('change', function() {
        let currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.has('country')) {
            currentUrl.searchParams.delete('country');
            history.replaceState(null, '', currentUrl.toString());
        }
    });
    $('#states_filter').on('change', function() {
        let currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.has('state')) {
            currentUrl.searchParams.delete('state');
            history.replaceState(null, '', currentUrl.toString());
        }
    });
    $('#cities_filter').on('change', function() {
        let currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.has('city')) {
            currentUrl.searchParams.delete('city');
            history.replaceState(null, '', currentUrl.toString());
        }
    });
    $('#view_products').on('change', function() {
        let currentUrl = new URL(window.location.href);
        if (currentUrl.searchParams.has('product')) {
            currentUrl.searchParams.delete('product');
            history.replaceState(null, '', currentUrl.toString());
        }
    });
    $('.dataTables_length select').addClass('form-control input-sm');
    $('.dt-buttons').addClass('btn-group');

    $(document).on('click', '.delete_lead', function() {
        var delete_url = $(this).data('url');
        $.ajax({
            url: delete_url,
            method: "POST",
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert_float('success', result.message);
                if (table) {
                    table.draw();
                }
            } else {
                alert_float('danger', result.message);
            }
        });
    });

    $(document).on('click', '.restore_lead', function() {
        var restore_url = $(this).data('url');
        $.ajax({
            url: restore_url,
            method: "POST",
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert_float('success', result.message);
                if (table) {
                    table.draw();
                }
            } else {
                alert_float('danger', result.message);
            }
        });
    });

});

function updateLengthMenu() {
    if (hasActiveFilter()) {
        $('select[name="leads_table_length"] option[value="-1"]').removeClass('hide');
    } else {
        var $lengthSelect = $('select[name="leads_table_length"]');
        if ($lengthSelect.val() === "-1") {
            $lengthSelect.val("100").trigger('change');
        }
        $('select[name="leads_table_length"] option[value="-1"]').addClass('hide');
    }
}

function hasActiveFilter() {
    var assigned = $('#view_assigned').val();
    // var statusFilter = $('#view_status').val();
    var sourceFilter = $('#view_source').val();
    var countryFilter = $('#countries_filter').val();
    var stateFilter = $('#states_filter').val();
    var cityFilter = $('#cities_filter').val();
    var productFilter = $('#view_products').val();
    var customFilter = $('#custom_view').val();
    var followUpdateFilter = $('#followup_date').val();
    var dateTypeFilter = $('#date_by').val();
    var hasSearch = table.search();

    return [
        assigned,
        sourceFilter,
        countryFilter,
        stateFilter,
        cityFilter,
        productFilter,
        customFilter,
        followUpdateFilter,
        dateTypeFilter,
        hasSearch
    ].some(function(filter) {
        if (Array.isArray(filter)) {
            return filter.some(function(item) {
                return item && item.trim() !== '';
            });
        } else {
            return filter && filter.trim() !== '';
        }
    });
}

function checkDateInRange(checkDate, fromDate, toDate) {
    // Convert string dates to Date objects
    checkDate = new Date(checkDate);
    fromDate = new Date(fromDate);
    toDate = new Date(toDate);

    // Check if checkDate is within the range fromDate - toDate
    return (checkDate >= fromDate && checkDate <= toDate);
}

function compareDates(date1, date2) {
    // Extract date, month, and year components from the dates
    const day1 = date1.getDate();
    const month1 = date1.getMonth();
    const year1 = date1.getFullYear();

    const day2 = date2.getDate();
    const month2 = date2.getMonth();
    const year2 = date2.getFullYear();

    // Compare year
    if (year1 > year2) {
        return 1; // date1 is greater
    } else if (year1 < year2) {
        return -1; // date2 is greater
    }

    // If years are equal, compare month
    if (month1 > month2) {
        return 1; // date1 is greater
    } else if (month1 < month2) {
        return -1; // date2 is greater
    }

    // If months are equal, compare day
    if (day1 > day2) {
        return 1; // date1 is greater
    } else if (day1 < day2) {
        return -1; // date2 is greater
    }

    // If all components are equal
    return 0; // Both dates are equal
}

function dateConvert(inputDate) {
    // Split the date string into parts
    var parts = inputDate.split(" ");
    var datePart = parts[0];
    var timePart = parts[1];

    // Split the date part into year, month, and day
    var dateParts = datePart.split("-");
    var year = dateParts[0];
    var month = dateParts[1];
    var day = dateParts[2];

    // Rearrange the date parts and join with "/"
    var formattedDate = day + "/" + month + "/" + year;

    // Combine formatted date with time part
    var formattedDateTime = formattedDate + " " + timePart;

    return formattedDateTime;
}

function convertDateTimeFormat(dateTimeString, seprator) {
    // Split the date-time string into date and time parts
    var dateTimeParts = dateTimeString.split(" ");
    var datePart = dateTimeParts[0];
    var timePart = dateTimeParts[1] || ""; // If time part is not present, default to an empty string

    // Split the date part into day, month, and year
    var dateParts = datePart.split(seprator);

    // Rearrange the parts to form the new date string in "yyyy-mm-dd" format
    var newDateString = dateParts[2] + "-" + dateParts[1] + "-" + dateParts[0];

    // Combine the new date string with the time part (if present)
    var newDateTimeString = newDateString;
    if (timePart !== "") {
        newDateTimeString += " " + timePart;
    }

    return newDateTimeString;
}

function renderStatusDropdown(row) {
    var statusHtml = "";
    if (row.status_name == null || row.status_name == "") {
        if (row.lost == "1") {
            statusHtml += '<span class="label label-danger inline-block">' + "<?= _l('lead_lost') ?>" + '</span>';
        } else if (row.junk == "1") {
            statusHtml += '<span class="label label-warning inline-block">' + "<?= _l('lead_junk') ?>" + '</span>';
        }
    } else {
        var locked = false;
        if (row.is_converted > 0) {
            locked = ((!is_admin && lockAfterConvert == 1) ? true : false);
        }
        var color = (row.color == "" || row.color == null) ? 'default' : '';
        statusHtml += '<span class="inline-block label label-' + color + '" style="color:#000;border:1px solid ' + row
            .color + '">' + row.status_name;
        if (!locked) {
            statusHtml += '<div class="dropdown inline-block mleft5 table-export-exclude">';
            statusHtml +=
                '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' +
                row.id + '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            statusHtml += '<span data-toggle="tooltip" title="' + "<?= _l('ticket_single_change_status'); ?>" +
                '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            statusHtml += '</a>';
            statusHtml += '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' + row.id +
                '">';
            statusData.forEach(function(leadChangeStatus) {
                if (row.status != leadChangeStatus.id) {
                    statusHtml += '<li><a href="#" onclick="lead_new_mark_as(' + leadChangeStatus.id + ',' + row
                        .id + '); return false;">' + leadChangeStatus.name + '</a></li>';
                }
            });
            statusHtml += '</ul>';
            statusHtml += '</div>';
        }
        statusHtml += '</span>';
    }
    return statusHtml;
}

function findObjectByProperty(array, property, value) {
    return array.find(item => item[property] == value);
}

function modifyObjectProperty(object, property, newValue) {
    object[property] = newValue;
}

function replaceCellValue(rowIdx, colIdx, newValue) {
    table.cell(rowIdx, colIdx).data(newValue).draw();
}

function lead_new_mark_as(status_id, lead_id) {
    var data = {};
    data.status = status_id;
    data.leadid = lead_id;
    $.post(admin_url + 'leads/update_lead_status', data).done(function(response) {
        table.draw(false);
    });
}

$('#leads_bulk_mark_lost').on('change', function() {
    $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
    $('#move_to_status_leads_bulk').selectpicker('refresh')
});

$('#move_to_status_leads_bulk').on('change', function() {
    if ($(this).selectpicker('val') != '') {
        $('#leads_bulk_mark_lost').prop('disabled', true);
        $('#leads_bulk_mark_lost').prop('checked', false);
    } else {
        $('#leads_bulk_mark_lost').prop('disabled', false);
    }
});

$("body").on('click', 'table.dataTable tbody .tags-labels .label-tag', function() {
    $(this).parents('table').DataTable().search($(this).find('.tag').text()).draw();
    $('div.dataTables_filter input').focus();
});

function leads_new_bulk_action(event) {
    if (confirm_delete()) {
        $('#bulkaction_confirm_btn').prop('disabled', true);
        $('#bulkaction_confirm_btn').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
        var table_new_leads = $('table.table-leadsnew');
        var mass_delete = $('#mass_delete').prop('checked');
        var ids = [];
        var data = {};
        if (mass_delete == false || typeof(mass_delete) == 'undefined') {
            data.lost = $('#leads_bulk_mark_lost').prop('checked');
            data.status = $('#move_to_status_leads_bulk').val();
            data.assigned = $('#assign_to_leads_bulk').val();
            data.source = $('#move_to_source_leads_bulk').val();
            data.last_contact = $('#leads_bulk_last_contact').val();
            data.tags = $('#tags_bulk').tagit('assignedTags');
            data.visibility = $('input[name="leads_bulk_visibility"]:checked').val();
            data.city = $('input[name="city"]').val();
            data.state = $('input[name="state"]').val();
            data.country = $('select[name="country"]').val();
            data.assigned = typeof(data.assigned) == 'undefined' ? '' : data.assigned;
            data.visibility = typeof(data.visibility) == 'undefined' ? '' : data.visibility;

            if (data.status === '' &&
                data.lost === false &&
                data.assigned === '' &&
                data.source === '' &&
                data.last_contact === '' &&
                data.tags.length == 0 &&
                data.visibility === '' &&
                data.city === '' &&
                data.state === '' &&
                data.country === ''
            ) {
                $('#bulkaction_confirm_btn').prop('disabled', false);
                $('#bulkaction_confirm_btn').html('CONFIRM');
                alert_float('danger', "No changes found.");
                return;
            }
        } else {
            data.mass_delete = true;
        }
        var rows = table_new_leads.find('tbody tr');
        $.each(rows, function() {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        data.ids = ids;
        $(event).addClass('disabled');
        setTimeout(function() {
            $.post("<?php echo admin_url('leads/bulk_action'); ?>", data).done(function() {
                $('#bulkaction_confirm_btn').prop('disabled', true);
                $('#bulkaction_confirm_btn').html('CONFIRM');
                alert_float('success', '<?php echo _l('bulk_actions_success'); ?>');
                setTimeout(() => {
                    if (table) {
                        $('#leads_bulk_actions').modal('hide');
                        $('#leads_bulk_actions').find('button').removeClass('disabled');
                        $('#leads_bulk_actions').find('button').removeAttr('disabled');
                        $('#leads_bulk_actions').find('input').val('');
                        $('#leads_bulk_actions').find('select').val('');
                        $('#leads_bulk_actions').find('select').selectpicker('refresh');
                        $('#leads_bulk_actions').find('#bulkaction_confirm_btn').removeClass(
                            'disabled');
                        table.draw();
                    }
                    // window.location.reload();
                }, 1000);
            }).fail(function(data) {
                $('#leads_bulk_actions').modal('hide');
                alert_float('danger', data.responseText);
                $('#bulkaction_confirm_btn').prop('disabled', false);
                $('#bulkaction_confirm_btn').html('CONFIRM');
            });
        }, 200);
    }
}

$("body").on('click', '#lead-modal .close', function() {
    table.draw(false);
});

function exportFileNameGenerate(filename) {
    var currentDate = new Date();
    var day = currentDate.getDate();
    var month = currentDate.getMonth() + 1; // Months are zero based
    var year = currentDate.getFullYear();
    var hours = currentDate.getHours();
    var minutes = currentDate.getMinutes();
    var seconds = currentDate.getSeconds();
    return filename + '_' +
        (day < 10 ? '0' + day : day) + '_' +
        (month < 10 ? '0' + month : month) + '_' +
        year + '-' +
        (hours < 10 ? '0' + hours : hours) + '-' +
        (minutes < 10 ? '0' + minutes : minutes);
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.38/moment-timezone-with-data.min.js"></script>
<style :scope>
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5em 1em;
    margin-left: 0.5em;
    border-radius: 4px;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
    color: #333;
}

@media screen and (max-width: 767px) {
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 3px 3px;
        margin-left: 0.5em;
        border-radius: 4px;
        border: 1px solid #ddd;
        background-color: #f8f9fa;
        color: #333;
    }

    .dt-page-jump-select {
        width: 70px !important;
        height: 30px !important;
        font-size: 14px !important;
        text-align: left !important;
    }
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #e9ecef;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #007bff;
    color: #fff;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    background-color: #f8f9fa;
    color: #868e96;
    pointer-events: none;
}

#overlay {
    z-index: 100;
    width: 100%;
    height: 100%;
    margin-top: 10%;
}

.cv-spinner {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px #ddd solid;
    border-top: 4px #2e93e6 solid;
    border-radius: 50%;
    animation: sp-anime 0.8s infinite linear;
}

.cv-spinner {
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px #ddd solid;
    border-top: 4px #2e93e6 solid;
    border-radius: 50%;
    animation: sp-anime 0.8s infinite linear;
}

@keyframes sp-anime {
    100% {
        transform: rotate(360deg);
    }
}

.d-none {
    display: none !important;
}

table td {
    color: black !important;
}

table.dataTable.dtr-inline.collapsed>tbody>tr[role=row]>td:first-child {
    padding-top: 13px;
}

.export-dropdown-btn {
    font-weight: 400 !important;
    border: none !important;
    text-align: justify !important;
    color: #4e75ad !important;
    font-size: 14px !important;
    background: transparent !important;
    padding: 4px !important;
}

.export-dropdown-btn:active {
    box-shadow: none !important;
}

.export-dropdown-btn:hover {
    box-shadow: none !important;
    color: black !important;
}

div.dt-button-background {
    background: transparent !important;
}

div.dt-button-collection {
    border: none !important;
    box-shadow: none !important;
}

button.dt-button {
    margin-right: 0 !important;
}

.tag {
    white-space: break-spaces;
}

@media screen and (max-width: 767px) {
    div.dataTables_wrapper div.dataTables_length {
        position: relative !important;
    }
}

@media screen and (max-width: 640px) {
    div.dt-buttons {
        float: right !important;
    }
}

.dt-page-jump-select {
    display: inline;
    width: 70px;
    margin-left: 10px;
    height: 31px;
}
</style>
</body>

</html>