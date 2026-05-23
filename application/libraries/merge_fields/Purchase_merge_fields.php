<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Purchase ID',
                'key'       => '{purchase_id}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase Number',
                'key'       => '{purchase_number}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Subject',
                'key'       => '{purchase_subject}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase To',
                'key'       => '{purchase_to}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase Total',
                'key'       => '{purchase_total}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase Subtotal',
                'key'       => '{purchase_subtotal}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase Assigned',
                'key'       => '{purchase_assigned}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Purchase Assigned Desgination',
                'key'       => '{purchase_assigned_designation}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Vendor Name',
                'key'       => '{purchase_vendor_name}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Vendor Company Name',
                'key'       => '{purchase_vendor_company_name}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Address',
                'key'       => '{purchase_address}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'City',
                'key'       => '{purchase_city}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'State',
                'key'       => '{purchase_state}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Zip Code',
                'key'       => '{purchase_zip}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Country',
                'key'       => '{purchase_country}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Email',
                'key'       => '{purchase_email}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Phone',
                'key'       => '{purchase_phone}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'GST Number',
                'key'       => '{purchase_gst_number}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Place Of Loading',
                'key'       => '{loading_place}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Place Of Discharge',
                'key'       => '{discharge_place}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Payment Term',
                'key'       => '{payment_term}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Shipment Term',
                'key'       => '{shipment_term}',
                'available' => [
                    'purchase',
                ],
            ],
            [
                'name'      => 'Notes',
                'key'       => '{purchase_notes}',
                'available' => [
                    'purchase',
                ],
            ]
        ];
    }


    public function format($purchase_id)
    {
        $fields = [];
        $this->ci->db->where('id', $purchase_id);
        $this->ci->db->join(db_prefix() . 'countries', db_prefix() . 'countries.country_id=' . db_prefix() . 'purchase.country', 'left');
        $purchase = $this->ci->db->get(db_prefix() . 'purchase')->row();


        if (!$purchase) {
            return $fields;
        }

        if ($purchase->currency != 0) {
            $currency = get_currency($purchase->currency);
        } else {
            $currency = get_base_currency();
        }

        $fields['{purchase_id}']          = $purchase_id;
        $fields['{purchase_number}']      = format_purchase_number($purchase_id);
        $fields['{purchase_subject}']     = $purchase->subject;
        $fields['{purchase_total}']       = app_format_money($purchase->total, $currency);
        $fields['{purchase_subtotal}']    = app_format_money($purchase->subtotal, $currency);
        $fields['{purchase_to}']          = $purchase->purchase_to;
        $fields['{purchase_address}']     = $purchase->address;
        $fields['{purchase_email}']       = $purchase->email;
        $fields['{purchase_phone}']       = $purchase->phone;
        $fields['{purchase_city}']     = $purchase->city;
        $fields['{purchase_state}']    = $purchase->state;
        $fields['{purchase_zip}']      = $purchase->zip;
        $fields['{purchase_country}']  = $purchase->short_name;
        $fields['{purchase_assigned}'] = get_staff_full_name($purchase->assigned);
        $fields['{purchase_assigned_designation}'] = get_staff_designation($purchase->assigned);
        $fields['{loading_place}']      = $purchase->loading_place;
        $fields['{discharge_place}']    = $purchase->discharge_place;
        $fields['{payment_term}']       = $purchase->payment_term;
        $fields['{shipment_term}']      = $purchase->shipment_term;
        $fields['{purchase_notes}']     = $purchase->notes;
        $relData = get_relation_data('vendor', $purchase->vendor_id);
        $fields['{purchase_vendor_name}'] = $relData->name;
        $fields['{purchase_vendor_company_name}'] = $relData->company;
        $fields['{purchase_gst_number}'] = $relData->gst_in;

        return hooks()->apply_filters('purchase_merge_fields', $fields, [
            'id'       => $purchase_id,
            'purchase' => $purchase,
        ]);
    }
}
