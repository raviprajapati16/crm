<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
  "#",
  "Debit Note",
  "Date",
  "Vendor Name",
  "Status",
  "Reference #",
  "Amount",
  "Remaining Amount",
);
render_datatable($table_data, 'debit-notes');