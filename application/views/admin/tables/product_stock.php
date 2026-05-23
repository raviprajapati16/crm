<?php
$CI = &get_instance();
$where = $CI->input->post();
$limit = (int)$where['length'];
$offset = (int)$where['start'];
$search_value = $where['search']['value'];

$output = [
    "draw" => intval($where['draw']),
    "iTotalRecords" => 0,
    "iTotalDisplayRecords" => 0,
    "aaData" => [],
];

$year = $CI->input->post('year');

$low_stock_limit = get_option('stock_low_alert_limit');
if (empty($low_stock_limit)) {
    $low_stock_limit = 0;
}

$year_condition_invoice = '';

if (!empty($year)) {
    $year = (int)$year;
    $year_condition_invoice = " AND YEAR(inv.date) = {$year} ";
}

$search_condition = '';
if (!empty($search_value)) {
    $search_value = $CI->db->escape_like_str($search_value);
    $search_condition = " HAVING (
        COALESCE(i.description, 'Unknown Item') LIKE '%{$search_value}%' OR 
        ig.name LIKE '%{$search_value}%' OR 
        sg.name LIKE '%{$search_value}%' OR
        purchased_qty LIKE '%{$search_value}%' OR
        sold_qty LIKE '%{$search_value}%' OR
        available_qty LIKE '%{$search_value}%' OR
        sale_percentage LIKE '%{$search_value}%' OR
        stock_percentage LIKE '%{$search_value}%'
    ) ";
}

$sql = "
    SELECT 
        i.id AS item_id,
        COALESCE(i.description, 'Unknown Item') AS item_name,
        ig.name AS main_group_name,
        sg.name AS sub_group_name,

        COALESCE(i.total_purchase, 0) AS purchased_qty,

        COALESCE(SUM(CASE 
            WHEN ti.rel_type = 'invoice' AND inv.status NOT IN (5,6) AND inv.deleted_at IS NULL {$year_condition_invoice}
            THEN ti.qty ELSE 0 END), 0) AS sold_qty,


        (COALESCE(i.total_purchase, 0)
        - 
        COALESCE(SUM(CASE 
            WHEN ti.rel_type = 'invoice' AND inv.status NOT IN (5,6) AND inv.deleted_at IS NULL {$year_condition_invoice}
            THEN ti.qty ELSE 0 END), 0)) AS available_qty,


        CASE 
            WHEN COALESCE(i.total_purchase, 0) = 0 THEN 0
            ELSE ROUND(
                COALESCE(SUM(CASE 
                    WHEN ti.rel_type = 'invoice' AND inv.status NOT IN (5,6) AND inv.deleted_at IS NULL {$year_condition_invoice}
                    THEN ti.qty ELSE 0 END), 0) * 100.0 /
                COALESCE(i.total_purchase, 0), 2)
        END AS sale_percentage,


        CASE 
            WHEN COALESCE(i.total_purchase, 0) = 0 THEN 0
            ELSE ROUND(( 
                COALESCE(i.total_purchase, 0) 
                - 
                COALESCE(SUM(CASE 
                    WHEN ti.rel_type = 'invoice' AND inv.status NOT IN (5,6) AND inv.deleted_at IS NULL {$year_condition_invoice}
                    THEN ti.qty ELSE 0 END), 0)
            ) * 100.0 / 
            COALESCE(i.total_purchase, 0), 2)
        END AS stock_percentage

        FROM tblitems i

        LEFT JOIN tblitemable ti ON ti.item_id = i.id


        LEFT JOIN tblinvoices inv ON inv.id = ti.rel_id AND ti.rel_type = 'invoice'

        LEFT JOIN tblitems_groups ig ON ig.id = i.group_id
        LEFT JOIN tblsub_groups sg ON sg.id = i.subgroup_id

        GROUP BY i.id, i.description, i.total_purchase, ig.name, sg.name
        {$search_condition}
        ORDER BY 
            (CASE WHEN 
                (COALESCE(i.total_purchase, 0)
                - 
                COALESCE(SUM(CASE 
                    WHEN ti.rel_type = 'invoice' AND inv.status NOT IN (5,6) AND inv.deleted_at IS NULL {$year_condition_invoice}
                    THEN ti.qty ELSE 0 END), 0)) < {$low_stock_limit} THEN 0 ELSE 1 
            END),
            available_qty DESC
        LIMIT {$limit} OFFSET {$offset}
";

$query = $CI->db->query($sql);
$result = $query->result_array();

$total_records = !empty($result) ? count($result) : 0;
$output['iTotalRecords'] = $total_records;
$output['iTotalDisplayRecords'] = $total_records;

$no = 0;
foreach ($result as $row) {
    $entry = [];

    $index = "";
    if ((int)$row['available_qty'] < $low_stock_limit) {
        $index .= "<i class='fa fa-bell text-danger' style='margin-right:10px'></i> ";
    }
    $index .= ++$no;

    $total_stock = (int)$row['purchased_qty'];
    $avl_qty = (int)$row['available_qty'];
    $avl_qty = ($avl_qty < 0)? 0 : $avl_qty;

    $entry[] = $index;
    $entry[] = $row['item_name'];
    $entry[] = $row['main_group_name'];
    $entry[] = $row['sub_group_name'];
    $entry[] = $total_stock . " <a href='#' data-avl-stock='".$avl_qty."' onclick='stock_edit(".$row['item_id'].")' class='edit-item' title='Edit Quantity'><i class='fa fa-pencil text-primary' style='margin-left:10px;' aria-hidden='true'></i></a>";
    $entry[] = (int)$row['available_qty'] . " <br>(" . $row['stock_percentage'] . "%)";
    $entry[] = (int)$row['sold_qty'] . " <br>(" . $row['sale_percentage'] . "%)";


    $entry['DT_RowClass'] = ((int)$row['available_qty'] < $low_stock_limit) ? 'bg-danger' : '';
    $output['aaData'][] = $entry;
}

return $output;