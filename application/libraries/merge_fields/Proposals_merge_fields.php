<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proposals_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Proposal ID',
                'key'       => '{proposal_id}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Number',
                'key'       => '{proposal_number}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Subject',
                'key'       => '{proposal_subject}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Products',
                'key'       => '{proposal_products}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Items Table',
                'key'       => '{proposal_items}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Total',
                'key'       => '{proposal_total}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Subtotal',
                'key'       => '{proposal_subtotal}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Open Till',
                'key'       => '{proposal_open_till}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Assigned',
                'key'       => '{proposal_assigned}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Assigned Desgination',
                'key'       => '{proposal_assigned_designation}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Cusomer Name',
                'key'       => '{proposal_customer_name}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Company Name',
                'key'       => '{proposal_proposal_to}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Address',
                'key'       => '{proposal_address}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'City',
                'key'       => '{proposal_city}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'State',
                'key'       => '{proposal_state}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Zip Code',
                'key'       => '{proposal_zip}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Country',
                'key'       => '{proposal_country}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Email',
                'key'       => '{proposal_email}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Phone',
                'key'       => '{proposal_phone}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'GST Number',
                'key'       => '{proposal_gst_number}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Place Of Loading',
                'key'       => '{loading_place}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Place Of Discharge',
                'key'       => '{discharge_place}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Payment Term',
                'key'       => '{payment_term}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Shipment Term',
                'key'       => '{shipment_term}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'notes',
                'key'       => '{proposal_notes}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Type',
                'key'       => '{proposal_type}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Customer Signature',
                'key'       => '{customer_signature}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Acceptance Name',
                'key'       => '{proposal_acceptance_name}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Company Signature',
                'key'       => '{company_signature}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Proposal Link',
                'key'       => '{proposal_link}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Capacity',
                'key'       => '{proposal_capacity}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Main Group',
                'key'       => '{main_group}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Sub Group',
                'key'       => '{sub_group}',
                'available' => [
                    'proposals',
                    'proposalterms'
                ],
            ],
            [
                'name'      => 'Payment Recorded Total',
                'key'       => '{payment_total}',
                'available' => [],
                'templates' => [
                    'proposal-advance-payment-recorded',
                ],
            ],
            [
                'name'      => 'Payment Recorded Date',
                'key'       => '{payment_date}',
                'available' => [],
                'templates' => [
                    'proposal-advance-payment-recorded',
                ],
            ],
            // [
            //     'name'      => 'Received Payments',
            //     'key'       => '{received_payments}',
            //     'available' => [
            //         'proposals',
            //         'proposalterms'
            //     ],
            // ],
        ];
    }

    /**
     * Merge fields for proposals
     * @param  mixed $proposal_id proposal id
     * @return array
     */
    public function format($proposal_id, $payment_id = '')
    {
        $fields = [];
        $this->ci->db->where('id', $proposal_id);
        $this->ci->db->join(db_prefix() . 'countries', db_prefix() . 'countries.country_id=' . db_prefix() . 'proposals.country', 'left');
        $proposal = $this->ci->db->get(db_prefix() . 'proposals')->row();


        if (!$proposal) {
            return $fields;
        }

        if ($proposal->currency != 0) {
            $currency = get_currency($proposal->currency);
        } else {
            $currency = get_base_currency();
        }

        $fields['{proposal_id}']          = $proposal_id;
        $fields['{proposal_number}']      = format_proposal_number($proposal_id);
        $fields['{proposal_link}']        = site_url('proposal/' . $proposal_id . '/' . $proposal->hash);
        $fields['{proposal_subject}']     = $proposal->subject;
        $fields['{proposal_total}']       = app_format_money($proposal->total, $currency);
        $fields['{proposal_subtotal}']    = app_format_money($proposal->subtotal, $currency);
        $fields['{proposal_open_till}']   = _d($proposal->open_till);
        $fields['{proposal_proposal_to}'] = $proposal->proposal_to;
        $fields['{proposal_address}']     = $proposal->address;
        $fields['{proposal_email}']       = $proposal->email;
        $fields['{proposal_phone}']       = $proposal->phone;
        $fields['{proposal_city}']     = $proposal->city;
        $fields['{proposal_state}']    = $proposal->state;
        $fields['{proposal_zip}']      = $proposal->zip;
        $fields['{proposal_country}']  = $proposal->short_name;
        $fields['{proposal_assigned}'] = get_staff_full_name($proposal->assigned);
        $fields['{proposal_products}'] = implode(", ", get_tags_in($proposal->id, 'proposal'));
        $fields['{proposal_assigned_designation}'] = get_staff_designation($proposal->assigned);


        $fields['{loading_place}']      = $proposal->loading_place;
        $fields['{discharge_place}']    = $proposal->discharge_place;
        $fields['{payment_term}']       = $proposal->payment_term;
        $fields['{shipment_term}']      = $proposal->shipment_term;
        $fields['{proposal_notes}']     = $proposal->notes;
        $fields['{proposal_type}']      = ($proposal->type == "0") ? "Domestic" : "International";
        $fields['{proposal_acceptance_name}']  = $proposal->acceptance_firstname . ' ' . $proposal->acceptance_lastname;

        $relData = get_relation_data($proposal->rel_type, $proposal->rel_id);
        if ($proposal->rel_type == "lead") {
            $fields['{proposal_customer_name}'] = $relData->name;
            $fields['{proposal_gst_number}'] = $relData->gst_in;
        }

        $CI = &get_instance();
        $relData =  $CI->clients_model->get($proposal->rel_id);
        if ($proposal->rel_type == "customer") {
            $fields['{proposal_customer_name}'] = $relData->firstname . ' ' . $relData->lastname;
            $fields['{proposal_gst_number}'] = $relData->vat;
        }

        $custom_fields = get_custom_fields('proposal');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($proposal_id, $field['id'], 'proposal');
        }

        $fields['{proposal_capacity}'] = "";
        $proposal_items = get_items_by_type('proposal', $proposal_id, ["items.unit" => "PLANT"]);
        if (!empty($proposal_items)) {
            $fields['{proposal_capacity}'] = implode(", ", array_unique(array_map('trim', array_column($proposal_items, 'capacity'))));
        }


        $fields['{main_group}'] = "";
        $proposal_items = get_items_by_type('proposal', $proposal_id);
        if (!empty($proposal_items)) {
            $fields['{main_group}'] = implode(", ", array_unique(array_map('trim', array_column($proposal_items, 'main_group_name'))));
        }

        $fields['{sub_group}'] = "";
        $proposal_items = get_items_by_type('proposal', $proposal_id);
        if (!empty($proposal_items)) {
            $fields['{sub_group}'] = implode(", ", array_unique(array_map('trim', array_column($proposal_items, 'sub_group_name'))));
        }

        $customer_sign_url = "";
        if (!empty($proposal->signature)) {
            $customer_sign_url = site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature));
        }

        $fields['{customer_signature}'] = "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
        $customer_sign_path = protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature);
        $customer_sign_url = site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('proposal') . $proposal->id . '/' . $proposal->signature));
        $fields['{customer_signature}'] = $CI->load->view(
            'themes/' . active_clients_theme() . '/mpdf/proposal/sign-section',
            [
                'file_exists' => file_exists($customer_sign_path),
                'signature' => $proposal->signature,
                'img_url' => $customer_sign_url
            ],
            true
        );


        $fields['{company_signature}'] = "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
        $company_signature_path = 'uploads/company/' . get_option('signature_image');
        $company_signature = base_url('uploads/company/' . get_option('signature_image'));
        $fields['{company_signature}'] = $CI->load->view(
            'themes/' . active_clients_theme() . '/mpdf/proposal/sign-section',
            [
                'file_exists' => file_exists($company_signature_path),
                'signature' => get_option('signature_image'),
                'img_url' => $company_signature
            ],
            true
        );


        $fields['{payment_total}'] = "";
        $fields['{payment_date}'] = "";
        if (!empty($payment_id)) {
            $this->ci->load->model('payments_model');
            $paymentData = $CI->payments_model->get($payment_id);
            if (!empty($paymentData)) {
                $fields['{payment_total}'] = app_format_money($paymentData->amount, $proposal->currency);
                $fields['{payment_date}'] = _d($paymentData->date);
            }
        }

        // $fields['{received_payments}'] = "";
        // $paymentData = $this->ci->proposals_model->get_proposal_payments($proposal_id);
        // if (!empty($paymentData)) {
        //     $fields['{received_payments}'] = $this->ci->load->view(
        //         'themes/' . active_clients_theme() . '/mpdf/proposal/received_payments_render',
        //         [
        //             'payments' => $paymentData,
        //             'currency' => $currency,
        //             'proposal_id'  => $proposal_id,
        //         ],
        //         true
        //     );
        // }

        return hooks()->apply_filters('proposal_merge_fields', $fields, [
            'id'       => $proposal_id,
            'proposal' => $proposal,
        ]);
    }
}
