<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
            foreach ($statuses as $_status) {
               $val = '';
               if ($_status == $this->input->get('status')) {
                  $val = $_status;
               }
               echo form_hidden('proposals_' . $_status, $val);
            }
            foreach ($years as $year) {
               echo form_hidden('year_' . $year['year'], $year['year']);
            }
            foreach ($proposals_sale_agents as $agent) {
               echo form_hidden('sale_agent_' . $agent['sale_agent']);
            }
            echo form_hidden('leads_related');
            echo form_hidden('customers_related');
            echo form_hidden('expired');
            ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('proposals', '', 'create')) { ?>
                     <a href="<?php echo admin_url('proposals/proposal'); ?>" class="btn btn-info pull-left display-block">
                        <?php echo _l('new_proposal'); ?>
                     </a>
                  <?php } ?>
                  <a href="<?php echo admin_url('proposals/pipeline/' . $switch_pipeline); ?>" class="btn btn-default mleft5 pull-left hidden-xs"><?php echo _l('switch_to_pipeline'); ?></a>
                  <div class="display-block text-right">
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-proposals',''); return false;">
                                 <?php echo _l('proposals_list_all'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach ($statuses as $status) { ?>
                              <li class="<?php if ($this->input->get('status') == $status) {
                                             echo 'active';
                                          } ?>">
                                 <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-proposals','proposals_<?php echo $status; ?>'); return false;">
                                    <?php echo format_proposal_status($status, '', false); ?>
                                 </a>
                              </li>
                           <?php } ?>
                           <?php if (count($years) > 0) { ?>
                              <li class="divider"></li>
                              <?php foreach ($years as $year) { ?>
                                 <li class="active">
                                    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-proposals','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                                    </a>
                                 </li>
                              <?php } ?>
                           <?php } ?>
                           <?php if (count($proposals_sale_agents) > 0) { ?>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                              <li class="dropdown-submenu pull-left">
                                 <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($proposals_sale_agents as $agent) { ?>
                                       <li>
                                          <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view('sale_agent_<?php echo $agent['sale_agent']; ?>','.table-proposals','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo get_staff_full_name($agent['sale_agent']); ?>
                                          </a>
                                       </li>
                                    <?php } ?>
                                 </ul>
                              </li>
                           <?php } ?>
                           <?php //if (count($proposals_sale_agents)) { ?>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                              <li class="dropdown-submenu pull-left">
                                 <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($proposals_sale_agents as $agent) { ?>
                                       <li>
                                          <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view('sale_agent_<?php echo $agent['sale_agent']; ?>','.table-proposals','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo get_staff_full_name($agent['sale_agent']); ?>
                                          </a>
                                       </li>
                                    <?php } ?>
                                 </ul>
                              </li>
                           <?php //} ?>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li>
                              <a href="#" data-cview="expired" onclick="dt_custom_view('expired','.table-proposals','expired'); return false;">
                                 <?php echo _l('proposal_expired'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="leads_related" onclick="dt_custom_view('leads_related','.table-proposals','leads_related'); return false;">
                                 <?php echo _l('proposals_leads_related'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="customers_related" onclick="dt_custom_view('customers_related','.table-proposals','customers_related'); return false;">
                                 <?php echo _l('proposals_customers_related'); ?>
                              </a>
                           </li>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-proposals','#proposal'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if invoiceid found in url -->
                        <?php echo form_hidden('proposal_id', $proposal_id); ?>
                        <?php
                        $table_data = array(
                           _l('proposal_date'),
                           "Proposal No.",
                           "To (Party Name)",
                           "State",
                           "Country",
                           "Total Amount",
                           _l('proposal_open_till'),
                           _l('proposal_status'),
                        );

                        $custom_fields = get_custom_fields('proposal', array('show_on_table' => 1));
                        foreach ($custom_fields as $field) {
                           array_push($table_data, $field['name']);
                        }

                        $table_data = hooks()->apply_filters('proposals_table_columns', $table_data);
                        render_datatable($table_data, 'proposals', [], [
                           'data-last-order-identifier' => 'proposals',
                           'data-default-order'         => get_table_last_order('proposals'),
                        ]);
                        ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="proposal" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
   var hidden_columns = [4, 5, 6, 7];
</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<!-- <script>
   <?php
      // ✅ Fetch current logged-in user details
      $getStaff = get_staff(get_staff_user_id());

      // ✅ Convert to JSON
      $user_json = json_encode($getStaff);
   ?>

   // Now you can use it in JS
   var current_user = <?php echo $user_json; ?>;
   console.log("Logged-in User Details:", current_user);

   var proposal_id;
   $(function() {
      var Proposals_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
         Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'], Proposals_ServerParams, [1, 'desc']);
      init_proposal();
      
 
   // ✅ Get the DataTable instance
   var table = $('.table-proposals').DataTable();

   // Print all table data after draw
   $('.table-proposals').on('draw.dt', function () {
       var data = table.rows().data().toArray();
       console.log("📊 All Table Data:", data);
   });
   });
   
</script> -->
<!-- <script>
<?php
   // 🔹 Get logged-in staff details
   $getStaff = get_staff(get_staff_user_id());

   // 🔹 Example: check some permissions
   $permissions = [
       'proposals_view_own'   => has_permission('proposals', get_staff_user_id(), 'view_own'),
       'proposals_view'   => has_permission('proposals', get_staff_user_id(), 'view'),
       'proposals_edit'   => has_permission('proposals', get_staff_user_id(), 'edit'),
       'proposals_create' => has_permission('proposals', get_staff_user_id(), 'create'),
   ];

   // 🔹 Prepare JS object
   $user_data = [
       'id' => $getStaff->staffid,
       'full_name' => get_staff_full_name($getStaff->staffid),
       'email' => $getStaff->email,
       'role' => $getStaff->role,
       'permissions' => $permissions
   ];
?>

// 🔹 Pass PHP data to JS
var current_user = <?php echo json_encode($user_data); ?>;

// 🔹 Log user details
console.log("Logged-in User Details:", current_user);
console.log("User Permissions:", current_user.permissions);

// 🔹 Example usage in JS
if(current_user.permissions.contracts_edit) {
    console.log("User can edit contracts ✅");
} else {
    console.log("User cannot edit contracts ❌");
}

 var proposal_id;
   $(function() {
      var Proposals_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
         Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'], Proposals_ServerParams, [1, 'desc']);
      init_proposal();
       var table = $('.table-proposals').DataTable();

    // 🔹 Print all table data on draw
    $('.table-proposals').on('draw.dt', function () {
        var data = table.rows().data().toArray();
        console.log("📊 All Table Data:", data);
    });
        });
        // 🔹 Get DataTable instance
   
</script> -->
<script>
<?php
   $getStaff = get_staff(get_staff_user_id());

   $permissions = [
       'proposals_view'   => has_permission('proposals', get_staff_user_id(), 'view'),
       'proposals_edit'   => has_permission('proposals', get_staff_user_id(), 'edit'),
       'proposals_create' => has_permission('proposals', get_staff_user_id(), 'create'),
   ];

   $user_data = [
       'id' => $getStaff->staffid,
       'full_name' => get_staff_full_name($getStaff->staffid),
       'email' => $getStaff->email,
       'role' => $getStaff->role,
       'permissions' => $permissions
   ];
?>

var current_user = <?php echo json_encode($user_data); ?>;
// console.log("Logged-in User Details:", current_user);

$(function() {
    var Proposals_ServerParams = {};
    $.each($('._hidden_inputs._filters input'), function() {
        Proposals_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    // Initialize DataTable
    var table = initDataTable('.table-proposals', admin_url + 'proposals/table', ['undefined'], ['undefined'], Proposals_ServerParams, [1, 'desc']);
    init_proposal();

    var skipStatuses = [
        '<span class="label label-success  s-status proposal-status-5">Completed</span>',
        '<span class="label label-warning  s-status proposal-status-1">Order In Process</span>'
    ];

    table.on('draw.dt', function () {
        var allPermissionsTrue = Object.values(current_user.permissions).every(p => p === true);

        table.rows().every(function () {
            var rowData = this.data();

            // Array-based columns
            var addFromId = rowData[8]; // Added from column
            var statusHtml = rowData[7]; // Status column

            // Debug: print each row data
            // console.log("Row Data → addedfrom:", addFromId, ", status:", statusHtml);

            if(current_user.role == "13" && allPermissionsTrue) {
                if(skipStatuses.includes(statusHtml)) {
                    $(this.node()).show();
                } else if(addFromId == current_user.id) {
                    $(this.node()).show();
                } else {
                    $(this.node()).hide();
                }
            } else {
                $(this.node()).show();
            }
        });

        // 🔹 Count visible rows after filter
        var visibleCount = table.rows({ filter: 'applied' }).nodes().to$().filter(':visible').length;
      //   console.log("Final visible rows after filter:", visibleCount);

        // 🔹 Optional: update DataTable info text
        $('.dataTables_info').text('Showing 1 to ' + visibleCount + ' of ' + visibleCount + ' entries');
    });
});
</script>




</body>

</html>