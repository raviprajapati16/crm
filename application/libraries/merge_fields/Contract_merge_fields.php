<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Agreement ID',
                'key'       => '{agreement_id}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Number',
                'key'       => '{agreement_number}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Subject',
                'key'       => '{agreement_subject}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Main Type',
                'key'       => '{agreement_main_type}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Sub Type',
                'key'       => '{agreement_sub_type}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Capacity',
                'key'       => '{agreement_capacity}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Product Name',
                'key'       => '{product_name}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Description',
                'key'       => '{agreement_description}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Items Table',
                'key'       => '{agreement_items_table}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Customer Sign Section',
                'key'       => '{agreement_customer_sign_section}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Company Sign & Stamp Section',
                'key'       => '{company_sign_stamp_section}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Date Start',
                'key'       => '{agreement_datestart}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Date End',
                'key'       => '{agreement_dateend}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Value',
                'key'       => '{agreement_value}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Agreement Link',
                'key'       => '{agreement_link}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'OTP',
                'key'       => '{otp_code}',
                'available' => [],
                'templates' => [
                    'contract-signed-otp-send'
                ],
            ],
            [
                'name'      => 'Agreement Payment Terms',
                'key'       => '{agreement_payment_terms}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Received Payments',
                'key'       => '{received_payments}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
            ],
            [
                'name'      => 'Payment Term Total Amount',
                'key'       => '{payment_term_amount}',
                'available' => [],
                'templates' => [
                    'payment-terms-reminder',
                    'payment-terms-overdue-notice'
                ],
            ],
            [
                'name'      => 'Payment Term Percentage',
                'key'       => '{payment_term_percentage}',
                'available' => [],
                'templates' => [
                    'payment-terms-reminder',
                    'payment-terms-overdue-notice'
                ],
            ],
            [
                'name'      => 'Payment Term Received Amount',
                'key'       => '{payment_term_received_amount}',
                'available' => [],
                'templates' => [
                    'payment-terms-reminder',
                    'payment-terms-overdue-notice'
                ],
            ],
            [
                'name'      => 'Payment Term Due Amount',
                'key'       => '{payment_term_due_amount}',
                'available' => [],
                'templates' => [
                    'payment-terms-reminder',
                    'payment-terms-overdue-notice'
                ],
            ],
            [
                'name'      => 'Payment Term Due Date',
                'key'       => '{payment_term_due_date}',
                'available' => [],
                'templates' => [
                    'payment-terms-reminder',
                    'payment-terms-overdue-notice'
                ],
            ],
            [
                'name'      => 'Customer / Vendor Information',
                'key'       => '{customer_vendor_information}',
                'available' => [
                    'contract',
                    'contract-drafts'
                ],
                'templates' => [],
            ],
        ];
    }


    public function format($contract_id, $otp_code = "", $term_id = "")
    {
        $fields = [];

        $this->ci->db->select('contracts.*,' . db_prefix() . 'contracts_types.name as type_name,' . db_prefix() . 'contract_subtype.name as sub_type_name,' . db_prefix() . 'contracts.id as id, ' . db_prefix() . 'contracts.addedfrom,' . db_prefix() . 'clients.company');
        $this->ci->db->join(db_prefix() . 'contracts_types', '' . db_prefix() . 'contracts_types.id = ' . db_prefix() . 'contracts.contract_type', 'left');
        $this->ci->db->join(db_prefix() . 'contract_subtype', '' . db_prefix() . 'contract_subtype.id = ' . db_prefix() . 'contracts.sub_type', 'left');
        $this->ci->db->join(db_prefix() . 'clients', '' . db_prefix() . 'clients.userid = ' . db_prefix() . 'contracts.client', 'left');
        $this->ci->db->where(db_prefix() . 'contracts.id', $contract_id);
        $contract = $this->ci->db->get(db_prefix() . 'contracts')->row();

        if (!$contract) {
            return $fields;
        }

        $currency = get_base_currency();

        $fields['{agreement_id}']             = $contract->id;
        $fields['{agreement_number}']         = format_contract_number($contract->id);
        $fields['{agreement_subject}']        = $contract->subject;
        $fields['{agreement_description}']    = nl2br($contract->description);
        $fields['{agreement_datestart}']      = _d($contract->datestart);
        $fields['{agreement_dateend}']        = _d($contract->dateend);
        $fields['{agreement_value}'] = app_format_money($contract->contract_value, $currency);
        $fields['{agreement_main_type}']      = $contract->type_name;
        $fields['{agreement_sub_type}']       = $contract->sub_type_name;

        $fields['{customer_vendor_information}'] = format_contract_customer_info($contract->rel_type, $contract->client);
        $fields['{product_name}']  = "";
        if (!empty($contract->client)) {
            $this->ci->load->model('clients_model');
            $client = $this->ci->clients_model->get($contract->client);
            if (!empty($client) && isset($client->leadid) && !empty($client->leadid)) {
                $fields['{product_name}'] = implode(", ", get_tags_in($client->leadid, 'lead'));
            }
        }

        $fields['{agreement_capacity}'] = "";
        $fields['{agreement_items_table}'] = "";
        $proposal_ids = get_contract_linked_proposals($contract->id);
        if (!empty($proposal_ids)) {
            $this->ci->load->model('proposals_model');
            $items_data = [];
            $contract_item_table_html = "";
            foreach ($proposal_ids as $id) {
                //capacity extract code
                $proposal_items = get_items_by_type('proposal', $id);
                if (!empty($proposal_items)) {
                    $items = array_unique(array_map('trim', array_column($proposal_items, 'capacity')));
                    $items_data = array_values(array_filter(array_merge($items_data, $items)));
                }
                //proposal item table code
                $proposal = $this->ci->proposals_model->get($id);
                $contract_item_table_html .= "<br>" . $this->ci->load->view(
                    'themes/' . active_clients_theme() . '/mpdf/contracts/contract-items-table',
                    ['proposal' => $proposal],
                    true
                );
            }
            $fields['{agreement_capacity}'] = implode(", ", $items_data);
            $fields['{agreement_items_table}'] = $contract_item_table_html;
        }

        // dynamic partners sign section
        $this->ci->load->model('contracts_model');
        $contract->contacts = $this->ci->contracts_model->get_contract_contacts($contract->id, $contract->rel_type);
        $sign_html = $this->ci->load->view(
            'themes/' . active_clients_theme() . '/mpdf/contracts/contract-partners-sign-section',
            ['contract' => $contract],
            true
        );
        $fields['{agreement_customer_sign_section}'] = $sign_html;

        // dynamic company sign section
        $sign_html = $this->ci->load->view(
            'themes/' . active_clients_theme() . '/mpdf/contracts/company-sign-stamp-section',
            ['contract' => $contract],
            true
        );
        $fields['{company_sign_stamp_section}'] = $sign_html;

        $fields['{agreement_payment_terms}'] = "";
        $paymentTermData = $this->ci->contracts_model->get_payment_terms($contract->id);
        if (!empty($paymentTermData)) {
            $fields['{agreement_payment_terms}'] = $this->ci->load->view(
                'themes/' . active_clients_theme() . '/mpdf/contracts/payment-terms-table-render',
                ['paymentData' => $paymentTermData],
                true
            );
        }


        $fields['{received_payments}'] = "";
        $proposal_ids = get_contract_linked_proposals($contract->id);
        $paymentsData = array();
        if (!empty($proposal_ids)) {
            $data['proposals'] = $this->ci->proposals_model->get_multiple_proposals($proposal_ids);
            foreach ($data['proposals'] as $key => $proposal) {
                $payment_data = $this->ci->proposals_model->get_proposal_payments($proposal['id']);
                if (!empty($payment_data)) {
                    $paymentsData = array_merge($payment_data, $paymentsData);
                    usort($paymentsData, function ($a, $b) {
                        return $a['paymentid'] <=> $b['paymentid'];
                    });
                    $paymentsData = array_values($paymentsData);
                }
            }
        }
        if (!empty($paymentsData)) {
            $fields['{received_payments}'] = $this->ci->load->view(
                'themes/' . active_clients_theme() . '/mpdf/proposal/received_payments_render',
                [
                    'payments' => $paymentsData,
                    'currency' => get_contract_currency($contract->id),
                ],
                true
            );
        }

        $fields['{payment_term_amount}'] = "";
        $fields['{payment_term_percentage}'] = "";
        $fields['{payment_term_received_amount}'] = "";
        $fields['{payment_term_due_amount}'] = "";
        $fields['{payment_term_due_date}'] = "";
        if (!empty($term_id)) {
            $termData = $this->ci->contracts_model->get_payment_term($contract->id);
            $termPaymentData = get_contract_term_payment_data($termData['id']);
            $fields['{payment_term_amount}'] = app_format_money($termPaymentData['total_amount'], get_contract_currency($contract->id)) . '/-';
            $fields['{payment_term_percentage}'] = $termData['percentage'];
            $fields['{payment_term_received_amount}'] = app_format_money($termPaymentData['received_for_term'], get_contract_currency($contract->id)) . '/-';
            $fields['{payment_term_due_amount}'] = app_format_money($termPaymentData['remaining_amount'], get_contract_currency($contract->id)) . '/-';
            $fields['{payment_term_due_date}'] = _d($termData['scheduled_payment_date']);
        }

        $fields['{agreement_link}'] = site_url('contract/' . $contract->id . '/' . $contract->hash);
        $custom_fields = get_custom_fields('contracts');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($contract_id, $field['id'], 'contracts');
        }

        $fields['{otp_code}'] = $otp_code;
        return hooks()->apply_filters('contract_merge_fields', $fields, [
            'id'       => $contract_id,
            'contract' => $contract,
        ]);
    }
}
