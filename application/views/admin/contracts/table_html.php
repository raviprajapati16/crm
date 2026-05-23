<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
  "Sr. No.",
  "Agreement Sub Type",
  "Agreement Number",
  "Name",
  "State",
  _l('contract_value'),
  _l('contract_list_start_date'),
  _l('contract_list_end_date'),
  "Status",
);
$custom_fields = get_custom_fields('contracts', array('show_on_table' => 1));

foreach ($custom_fields as $field) {
  array_push($table_data, $field['name']);
}

$table_data = hooks()->apply_filters('contracts_table_columns', $table_data);

render_datatable($table_data, (isset($class) ? $class : 'contracts'), [], [
  'data-last-order-identifier' => 'contracts',
'data-default-order' => get_table_last_order('contracts'),
]);