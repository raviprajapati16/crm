<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contact_book_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'firstname',
                'key'       => '{contact_book_firstname}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'lastname',
                'key'       => '{contact_book_lastname}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Contact Category',
                'key'       => '{contact_book_category}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Company',
                'key'       => '{contact_book_company}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Email',
                'key'       => '{contact_book_email}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Phone',
                'key'       => '{contact_book_phone_number}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Address',
                'key'       => '{contact_book_address}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'City',
                'key'       => '{contact_book_city}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'State',
                'key'       => '{contact_book_state}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
            [
                'name'      => 'Country',
                'key'       => '{contact_book_country}',
                'available' => [
                    'contract',
                    'contract-drafts',
                ],
            ],
        ];
    }

    public function format($id)
    {
        $fields = [];

        $fields['{contact_book_firstname}'] = '';
        $fields['{contact_book_lastname}'] = '';
        $fields['{contact_book_category}'] = '';
        $fields['{contact_book_company}'] = '';
        $fields['{contact_book_email}'] = '';
        $fields['{contact_book_phone_number}'] = '';
        $fields['{contact_book_address}'] = '';
        $fields['{contact_book_city}'] = '';
        $fields['{contact_book_state}'] = '';
        $fields['{contact_book_country}'] = '';

        if (is_numeric($id)) {
            $this->ci->db->where('id', $id);
            $contact_book = $this->ci->db->get(db_prefix() . 'contact_book')->row();
        } else {
            $contact_book = $id;
        }

        if (!$contact_book) {
            return $fields;
        }

        $fields['{contact_book_firstname}']             = $contact_book->firstname;
        $fields['{contact_book_lastname}']              = $contact_book->lastname;
        $fields['{contact_book_category}']              = get_contact_book_category_name($contact_book->contact_book_category);
        $fields['{contact_book_company}']               = $contact_book->company;
        $fields['{contact_book_email}']                 = $contact_book->email;
        $fields['{contact_book_phone_number}']          = $contact_book->phone_number;
        $fields['{contact_book_address}']               = $contact_book->address;
        $fields['{contact_book_city}']                  = $contact_book->city;
        $fields['{contact_book_state}']                 = $contact_book->state;
        $fields['{contact_book_country}']               = get_country_name($contact_book->country);

        return hooks()->apply_filters('contact_book_merge_fields', $fields, ['id' => $contact_book->id, 'contact_book' => $contact_book]);
    }
}
