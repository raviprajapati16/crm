<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Debit_note_merge_fields extends App_merge_fields
{
    public function build()
    {
        return  [
                [
                    'name'      => 'Debit Note Number',
                    'key'       => '{debit_note_number}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Date',
                    'key'       => '{debit_note_date}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Status',
                    'key'       => '{debit_note_status}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Total',
                    'key'       => '{debit_note_total}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Subtotal',
                    'key'       => '{debit_note_subtotal}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Debits Used',
                    'key'       => '{debit_note_debits_used}',
                    'available' => [
                        'debit_note',
                    ],
                ],
                [
                    'name'      => 'Debits Remaining',
                    'key'       => '{debit_note_debits_remaining}',
                    'available' => [
                        'debit_note',
                    ],
                ],
            ];
    }

    public function format($id)
    {
        $fields = [];

        if (!class_exists('debit_notes_model')) {
            $this->ci->load->model('debit_notes_model');
        }

        $debit_note = $this->ci->debit_notes_model->get($id);

        if (!$debit_note) {
            return $fields;
        }

        $fields['{debit_note_number}']            = format_debit_note_number($id);
        $fields['{debit_note_total}']             = app_format_money($debit_note->total, $debit_note->currency_name);
        $fields['{debit_note_subtotal}']          = app_format_money($debit_note->subtotal, $debit_note->currency_name);
        $fields['{debit_note_debits_remaining}'] = app_format_money($debit_note->remaining_debits, $debit_note->currency_name);
        $fields['{debit_note_debits_used}']      = app_format_money($debit_note->debits_used, $debit_note->currency_name);
        $fields['{debit_note_date}']              = _d($debit_note->date);
        $fields['{debit_note_status}']            = format_debit_note_status($debit_note->status, true);

        $custom_fields = get_custom_fields('debit_note');

        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($id, $field['id'], 'debit_note');
        }

        return hooks()->apply_filters('debit_note_merge_fields', $fields, [
            'id'          => $id,
            'debit_note' => $debit_note,
         ]);
    }
}
