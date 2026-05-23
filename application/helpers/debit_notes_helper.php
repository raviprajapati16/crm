<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Sum total debits applied for invoice
 * @param  mixed $id invoice id
 * @return mixed
 */
function total_debits_applied_to_invoice($id)
{
    $total = sum_from_table(db_prefix() . 'debits', ['field' => 'amount', 'where' => ['invoice_id' => $id, 'deleted_at' => NULL]]);

    if ($total == 0) {
        return false;
    }

    return $total;
}

/**
 * Return debit note status color RGBA for pdf
 * @param  mixed $status_id current debit note status id
 * @return string
 */
function debit_note_status_color_pdf($status_id)
{
    $statusColor = '';

    if ($status_id == 1) {
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 2) {
        $statusColor = '132, 197, 41';
    } else {
        // Status VOID
        $statusColor = '119, 119, 119';
    }

    return $statusColor;
}


function debits_can_be_applied_to_purchase($status_id)
{
    if ($status_id != 2) { // 2 is Completed
        return true;
    }
    return false;
}

/**
 * Check if is last debit note created
 * @param  mixed  $id debit note id
 * @return boolean
 */
function is_last_debit_note($id)
{
    $CI = &get_instance();
    $CI->db->select('id')->from(db_prefix() . 'debitnotes')->where('deleted_at IS NULL')->order_by('id', 'desc')->limit(1);
    $query            = $CI->db->get();
    $last_debit_note = $query->row();

    if ($last_debit_note && $last_debit_note->id == $id) {
        return true;
    }

    return false;
}

/**
 * Function that format debit note number based on the prefix option and the debit note number
 * @param  mixed $id debit note id
 * @return string
 */
function format_debit_note_number($id)
{
    $CI = &get_instance();
    $CI->db->select('date,number,prefix,number_format')
        ->from(db_prefix() . 'debitnotes')
        ->where('id', $id);
    $debit_note = $CI->db->get()->row();

    if (!$debit_note) {
        return '';
    }

    $number = sales_number_format($debit_note->number, $debit_note->number_format, $debit_note->prefix, $debit_note->date);

    return hooks()->apply_filters('format_debit_note_number', $number, [
        'id'          => $id,
        'debit_note' => $debit_note,
    ]);
}

/**
 * Format debit note status
 * @param  mixed  $status debit note current status
 * @param  boolean $text   to return text or with applied styles
 * @return string
 */
function format_debit_note_status($status, $text = false)
{
    $CI = &get_instance();
    if (!class_exists('debit_notes_model')) {
        $CI->load->model('debit_notes_model');
    }

    $statuses    = $CI->debit_notes_model->get_statuses();
    $statusArray = false;
    foreach ($statuses as $s) {
        if ($s['id'] == $status) {
            $statusArray = $s;

            break;
        }
    }

    if (!$statusArray) {
        return $status;
    }

    if ($text) {
        return $statusArray['name'];
    }

    $style = 'border: 1px solid ' . $statusArray['color'] . ';color:' . $statusArray['color'] . ';';
    $class = 'label s-status';

    return '<span class="' . $class . '" style="' . $style . '">' . $statusArray['name'] . '</span>';
}

/**
 * Function that return debit note item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_debit_note_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'debit_note');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}
