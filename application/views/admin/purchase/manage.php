<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
            foreach ($statuses as $_status) {
               $val = '';
               if ($_status['id'] == $this->input->get('status')) {
                  $val = $_status['id'];
               }
               echo form_hidden('purchase_' . $_status['id'], $val);
            }
            foreach ($years as $year) {
               echo form_hidden('year_' . $year['year'], $year['year']);
            }
            foreach ($purchase_sale_agents as $agent) {
               echo form_hidden('sale_agent_' . $agent['sale_agent']);
            }
            ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('purchase', '', 'create')) { ?>
                     <a href="<?php echo admin_url('purchase/purchase'); ?>" class="btn btn-info pull-left display-block">New Purchase</a>
                  <?php } ?>
                  <div class="display-block text-right">
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-purchase',''); return false;">
                                 All
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach ($statuses as $status) { ?>
                              <li class="<?php if ($this->input->get('status') == $status['id']) {
                                             echo 'active';
                                          } ?>">
                                 <a href="#" data-cview="purchase_<?php echo $status['id']; ?>" onclick="dt_custom_view('purchase_<?php echo $status['id']; ?>','.table-purchase','purchase_<?php echo $status['id']; ?>'); return false;">
                                    <?php echo  $status['name']; ?>
                                 </a>
                              </li>
                           <?php } ?>
                           <?php if (count($years) > 0) { ?>
                              <li class="divider"></li>
                              <?php foreach ($years as $year) { ?>
                                 <li class="active">
                                    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-purchase','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                                    </a>
                                 </li>
                              <?php } ?>
                           <?php } ?>
                           <?php if (count($purchase_sale_agents) > 0) { ?>
                              <div class="clearfix"></div>
                              <li class="divider"></li>
                              <li class="dropdown-submenu pull-left">
                                 <a href="#" tabindex="-1">Assignee</a>
                                 <ul class="dropdown-menu dropdown-menu-left">
                                    <?php foreach ($purchase_sale_agents as $agent) { ?>
                                       <li>
                                          <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view('sale_agent_<?php echo $agent['sale_agent']; ?>','.table-purchase','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo get_staff_full_name($agent['sale_agent']); ?>
                                          </a>
                                       </li>
                                    <?php } ?>
                                 </ul>
                              </li>
                           <?php } ?>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-purchase','#purchase'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if invoiceid found in url -->
                        <?php echo form_hidden('purchase_id', $purchase_id); ?>
                        <?php
                        $table_data = array(
                           "Date",
                           "Purchase Order No.",
                           "Vendor Name",
                           "State",
                           "Country",
                           "Bill Amount",
                           "Status",
                        );
                        render_datatable($table_data, 'purchase');
                        ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="purchase" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
   var hidden_columns = [];
</script>
<script>
   var purchase_id;
   $(function() {
      var Purchases_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
         Purchases_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      initDataTable('.table-purchase', admin_url + 'purchase/table', ['undefined'], ['undefined'], Purchases_ServerParams, [1, 'desc']);
      init_purchase();

     
   });

   function init_purchase(id) {
      load_small_table_item(id, '#purchase', 'purchase_id', 'purchase/get_purchase_data_ajax', '.table-purchase');
   }
</script>
</body>

</html>