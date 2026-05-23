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

if (!empty($customer_id)) {
    $sql = "
    WITH FilteredMedia AS (
        SELECT
            cm.id,
            cm.customer_id,
            cm.rel_type,
            cm.rel_id,
            cm.created_at,
            cm.created_by,

            CASE
                WHEN cm.rel_type = 'brochure' THEN b.title
                WHEN cm.rel_type = 'product_presentation' THEN pp.title
                WHEN cm.rel_type = 'tutorial' THEN t.title
                ELSE NULL
            END AS title,
            CASE
                WHEN cm.rel_type = 'tutorial' THEN t.link
                WHEN cm.rel_type = 'product_presentation' THEN pp.file_name
                WHEN cm.rel_type = 'brochure' THEN b.file_name
                ELSE NULL
            END AS media_file,
            CASE
                WHEN cm.rel_type = 'product_presentation' THEN pp.hash
                WHEN cm.rel_type = 'brochure' THEN b.hash
                ELSE NULL
            END AS hash
        FROM tblcustomer_media cm
        LEFT JOIN tblbrochure b ON cm.rel_type = 'brochure' AND cm.rel_id = b.id
        LEFT JOIN tblproduct_presentation pp ON cm.rel_type = 'product_presentation' AND cm.rel_id = pp.id
        LEFT JOIN tbltutorial_videos t ON cm.rel_type = 'tutorial' AND cm.rel_id = t.id
        WHERE cm.customer_id = ?
        " . (!empty($search_value) ? "AND (
            cm.rel_type LIKE ? OR
            b.title LIKE ? OR
            pp.title LIKE ? OR
            t.title LIKE ?
        )" : "") . "
    ),
    TotalCount AS (
        SELECT COUNT(*) AS total_count FROM FilteredMedia
    )
    SELECT
        *,
        (SELECT total_count FROM TotalCount) AS total_records
    FROM FilteredMedia
    LIMIT ? OFFSET ?";

    $params = [$customer_id];

    if (!empty($search_value)) {
        $search_param = '%' . $search_value . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $params[] = $limit;
    $params[] = $offset;

    $query = $CI->db->query($sql, $params);

    $result = $query->result_array();

    $total_records = !empty($result) ? (int)$result[0]['total_records'] : 0;

    $output['iTotalRecords'] = $total_records;
    $output['iTotalDisplayRecords'] = $total_records;

    $no = 0;
    foreach ($result as $row) {
        $entry = [];
        $entry[] = $no + 1;

        $title = $row['title'];
        $title .= '<div class="row-options">';
        if (has_permission('customer_media', '', 'edit')) {
            $title .= '<a href="javascript:;" data-id="' . $row['id'] . '" data-type="' . $row['rel_type'] . '" data-rel-id="' . $row['rel_id'] . '" class="edit-customer-media" data-media-id="' . $row['id'] . '" data-rel-id="' . $row['rel_id'] . '" data-rel-type="' . $row['rel_type'] . '" data-customer-id="' . $customer_id . '">' . _l('Edit') . '</a>';
        }

        if (has_permission('customer_media', '', 'delete')) {
            $title .= ' | <a href="' . admin_url('customer_media/delete/' . $row['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
        }
        $title .= '</div>';
        $entry[] = $title;

        $entry[] = ucfirst(str_replace('_', ' ', $row['rel_type']));

        $link = $row['media_file'];
        if ($row['rel_type'] == 'product_presentation') {
            $link = site_url('product_presentation/view/' . $row['hash']);
        } else if ($row['rel_type'] == 'brochure') {
            $link = site_url('brochure/view/' . $row['hash']);
        }

        $entry[] = !empty($link) ? "<a href='" . $link . "' target='_blank' class='btn btn-primary btn-xs'><i class='fa fa-eye'></i></a>" : '-';
        $output['aaData'][] = $entry;
        $no++;
    }
}

return $output;
