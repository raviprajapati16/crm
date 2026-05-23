<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vendors_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Vendor Name',
                'key'       => '{vendor_name}',
                'available' => [
                    'vendors',
                    'contract',
                    'contract-drafts',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Email',
                'key'       => '{vendor_email}',
                'available' => [
                    'vendors',
                    'contract',
                    'contract-drafts',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Position',
                'key'       => '{vendor_position}',
                'available' => [
                    'vendors',
                    'contract',
                    'contract-drafts',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Website',
                'key'       => '{vendor_website}',
                'available' => [
                    'vendors',
                    'contract',
                    'contract-drafts',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Description',
                'key'       => '{vendor_description}',
                'available' => [
                    'vendors',
                    'contract',
                    'contract-drafts',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Phone Number',
                'key'       => '{vendor_phonenumber}',
                'available' => [
                    'vendors',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Company',
                'key'       => '{vendor_company}',
                'available' => [
                    'vendors',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Country',
                'key'       => '{vendor_country}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor Zip',
                'key'       => '{vendor_zip}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor City',
                'key'       => '{vendor_city}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor State',
                'key'       => '{vendor_state}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor Address',
                'key'       => '{vendor_address}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor Assigned',
                'key'       => '{vendor_assigned}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Vendor Status',
                'key'       => '{vendor_status}',
                'available' => [
                    'vendors',
                    'debit_note'
                ],
            ],
            [
                'name'      => 'Vendor Souce',
                'key'       => '{vendor_source}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Product Name',
                'key'       => '{product_name}',
                'available' => [
                    'vendors',
                    'debit_note',
                    'debit_note'
                ]
            ],
            [
                'name'      => 'Quotation Form Link',
                'key'       => '{quotation_form_link}',
                'available' => [],
                'templates' => [
                    'vendor-quotation-form-send',
                    'vendor-quotation-form-approved',
                    'vendor-quotation-form-not-approved'
                ],
            ],
            [
                'name'      => 'Reject Note',
                'key'       => '{reject_note}',
                'available' => [],
                'templates' => [
                    'vendor-quotation-form-not-approved'
                ],
            ],

        ];
    }

    /**
     * Lead merge fields
     * @param  mixed $id lead id
     * @return array
     */
    public function format($id)
    {
        $fields = [];

        $fields['{vendor_name}'] = '';
        $fields['{vendor_email}'] = '';
        $fields['{vendor_position}'] = '';
        $fields['{vendor_website}'] = '';
        $fields['{vendor_description}'] = '';
        $fields['{vendor_phonenumber}'] = '';
        $fields['{vendor_company}'] = '';
        $fields['{vendor_country}'] = '';
        $fields['{vendor_zip}'] = '';
        $fields['{vendor_city}'] = '';
        $fields['{vendor_state}'] = '';
        $fields['{vendor_address}'] = '';
        $fields['{vendor_assigned}'] = '';
        $fields['{vendor_status}'] = '';
        $fields['{vendor_source}'] = '';
        $fields['{product_name}'] = '';
        $fields['{quotation_form_link}']  = '';
        $fields['{reject_note}']  = '';

        if (is_numeric($id)) {
            $this->ci->db->where('id', $id);
            $vendor = $this->ci->db->get(db_prefix() . 'leads')->row();
        } else {
            $vendor = $id;
        }

        if (!$vendor) {
            return $fields;
        }


        $fields['{vendor_name}'] = $vendor->name;
        $fields['{vendor_email}']              = $vendor->email;
        $fields['{vendor_position}']           = $vendor->title;
        $fields['{vendor_phonenumber}']        = $vendor->phonenumber;
        $fields['{vendor_company}']            = $vendor->company;
        $fields['{vendor_zip}']                = $vendor->zip;
        $fields['{vendor_city}']               = $vendor->city;
        $fields['{vendor_state}']              = $vendor->state;
        $fields['{vendor_address}']            = $vendor->address;
        $fields['{vendor_website}']            = $vendor->website;
        $fields['{vendor_description}']        = $vendor->description;
        $fields['{product_name}']  = implode(", ", get_tags_in($vendor->id, 'lead'));
        if ($vendor->assigned != 0) {
            $fields['{vendor_assigned}'] = get_staff_full_name($vendor->assigned);
        }
        if ($vendor->country != 0) {
            $country                  = get_country($vendor->country);
            $fields['{vendor_country}'] = $country->short_name;
        }
        if ($vendor->junk == 1) {
            $fields['{vendor_status}'] = _l('lead_junk');
        } elseif ($vendor->lost == 1) {
            $fields['{vendor_status}'] = _l('lead_lost');
        } else {
            $this->ci->db->select('name');
            $this->ci->db->from(db_prefix() . 'leads_status');
            $this->ci->db->where('id', $vendor->status);
            $status = $this->ci->db->get()->row();
            if ($status) {
                $fields['{vendor_status}'] = $status->name;
            }
        }

        $this->ci->db->select('name');
        $this->ci->db->from(db_prefix() . 'leads_sources');
        $this->ci->db->where('id', $vendor->source);
        $source = $this->ci->db->get()->row();
        if ($source) {
            $fields['{vendor_source}'] = $source->name;
        }

        if (isset($vendor->quotation_form_link)) {
            $fields['{quotation_form_link}'] = $vendor->quotation_form_link;
        }

        if (isset($vendor->reject_note)) {
            $fields['{reject_note}'] = $vendor->reject_note;
        }


        $custom_fields = get_custom_fields('vendors');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($vendor->id, $field['id'], 'vendors');
        }

        return hooks()->apply_filters('vendors_merge_fields', $fields, ['id' => $vendor->id, 'vendors' => $vendor]);
    }
}
