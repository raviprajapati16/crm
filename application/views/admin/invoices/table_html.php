<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
  "Date",
  "Invoice No.",
  "Party Name",
  "State",
  "Country",
  "Bill Amount",
  "Due Date",
  "Status",
  array(
    'name' => _l('invoice_estimate_year'),
    'th_attrs' => array('class' => 'not_visible')
  )
);
$custom_fields = get_custom_fields('invoice', array('show_on_table' => 1));
foreach ($custom_fields as $field) {
  array_push($table_data, $field['name']);
}
$table_data = hooks()->apply_filters('invoices_table_columns', $table_data);
render_datatable($table_data, (isset($class) ? $class : 'invoices'));