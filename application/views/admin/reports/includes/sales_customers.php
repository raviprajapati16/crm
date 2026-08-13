<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="customers-report" class="hide">
   <?php render_datatable(array(
    _l('reports_sales_dt_customers_client'),
    array(
        'name' => 'Country',
        'th_attrs' => array('class' => 'export-pdf-only not-export hide')
    ),
    array(
        'name' => 'State',
        'th_attrs' => array('class' => 'export-pdf-only not-export hide')
    ),
    array(
        'name' => 'District',
        'th_attrs' => array('class' => 'export-pdf-only not-export hide')
    ),
    _l('reports_sales_dt_customers_total_invoices'),
    _l('reports_sales_dt_items_customers_amount'),
    _l('reports_sales_dt_items_customers_amount_with_tax')
    ),'customers-report scroll-responsive'); ?>
</div>
