<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
            foreach ($statuses as $status) {
               echo form_hidden('debit_notes_status_' . $status['id'], isset($status['filter_default'])
                  && $status['filter_default'] ? 'true' : '');
            }
            foreach ($years as $year) {
               echo form_hidden('year_' . $year['year'], $year['year']);
            }
            ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if (has_permission('debit_notes', '', 'create')) { ?>
                     <a href="<?php echo admin_url('debit_notes/debit_note'); ?>" class="btn btn-info pull-left display-block">New Debit Note</a>
                  <?php } ?>
                  <div class="display-block text-right">
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                           <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-debit-notes',''); return false;">
                                 <?php echo _l('debit_notes_list_all'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach ($statuses as $status) { ?>
                              <li class="<?php if (isset($status['filter_default']) && $status['filter_default']) {
                                             echo 'active';
                                          } ?>">
                                 <a href="#" data-cview="debit_notes_status_<?php echo $status['id']; ?>" onclick="dt_custom_view('debit_notes_status_<?php echo $status['id']; ?>','.table-debit-notes','debit_notes_status_<?php echo $status['id']; ?>'); return false;">
                                    <?php echo format_debit_note_status($status['id'], true); ?>
                                 </a>
                              </li>
                           <?php } ?>
                           <div class="clearfix"></div>
                           <?php
                           if (count($years) > 0) { ?>
                              <li class="divider"></li>
                              <?php foreach ($years as $year) { ?>
                                 <li class="active">
                                    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-debit-notes','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                                    </a>
                                 </li>
                              <?php } ?>
                           <?php } ?>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-debit-notes','#debit_note'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if debit not id found in url -->
                        <?php echo form_hidden('debit_note_id', $debit_note_id); ?>
                        <?php $this->load->view('admin/debit_notes/table_html'); ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="debit_note" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<?php init_tail(); ?>
<script>
   var hidden_columns = [];
</script>
<script>
   $(function() {
      var debit_Notes_ServerParams = {};
      $.each($('._hidden_inputs._filters input'), function() {
         debit_Notes_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
      });
      initDataTable('.table-debit-notes', admin_url + 'debit_notes/table', ['undefined'], ['undefined'], debit_Notes_ServerParams, [0, 'desc']);
      init_debit_note();
   });

   function init_debit_note(id) {
      load_small_table_item(id, '#debit_note', 'debit_note_id', 'debit_notes/get_debit_note_data_ajax', '.table-debit-notes');
   }

   function delete_debit_note_attachment(id) {
      if (confirm_delete()) {
         requestGet('debit_notes/delete_attachment/' + id).done(function(success) {
            if (success == 1) {
               $("body").find('[data-attachment-id="' + id + '"]').remove();
               init_debit_note($("body").find('input[name="_attachment_sale_id"]').val());
            }
         }).fail(function(error) {
            alert_float('danger', error.responseText);
         });
      }
   }
</script>
</body>

</html>