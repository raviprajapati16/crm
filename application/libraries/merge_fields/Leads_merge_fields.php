<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leads_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Lead Name',
                'key'       => '{lead_name}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Email',
                'key'       => '{lead_email}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Position',
                'key'       => '{lead_position}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Website',
                'key'       => '{lead_website}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Description',
                'key'       => '{lead_description}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead Phone Number',
                'key'       => '{lead_phonenumber}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Company',
                'key'       => '{lead_company}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Country',
                'key'       => '{lead_country}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Zip',
                'key'       => '{lead_zip}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead City',
                'key'       => '{lead_city}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead State',
                'key'       => '{lead_state}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead Address',
                'key'       => '{lead_address}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => 'Lead Assigned',
                'key'       => '{lead_assigned}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead Status',
                'key'       => '{lead_status}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead Souce',
                'key'       => '{lead_source}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
            ],
            [
                'name'      => 'Lead Link',
                'key'       => '{lead_link}',
                'available' => [
                    'leads',
                    'leads-follow-up'
                ],
                'templates' => [
                    'gdpr-removal-request-lead',
                ],
            ],
            [
                'name'      => is_gdpr() && get_option('gdpr_enable_lead_public_form') == '1' ? 'Lead Public Form URL' : '',
                'key'       => is_gdpr() && get_option('gdpr_enable_lead_public_form') == '1' ? '{lead_public_form_url}' : '',
                'available' => [],
                'templates' => [
                    'new-web-to-lead-form-submitted',
                ],
            ],
            [
                'name'      => is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1' ? 'Lead Consent Link' : '',
                'key'       => is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1' ? '{lead_public_consent_url}' : '',
                'available' => [],
                'templates' => [
                    'new-web-to-lead-form-submitted',
                ],
            ],
            [
                'name'      => 'Product Description',
                'key'       => '{product_description}',
                'templates' => [
                    'lead-customer-inquiry-form-send',
                ],
            ],
            [
                'name'      => 'Inquiry Form Link',
                'key'       => '{inquiry_form_link}',
                'templates' => [
                    'lead-customer-inquiry-form-send',
                ],
            ],
            [
                'name'      => 'Inquiry Form Not Approved Reason',
                'key'       => '{inquiry_form_not_approved_reason}',
                'templates' => [
                    'lead-customer-inquiry-form-not-approved',
                ],
            ],
            [
                'name'      => 'OTP Code',
                'key'       => '{otp_code}',
                'templates' => [
                    'lead-customer-office-visit-form-otp-send',
                ],
            ],
            [
                'name'      => 'Visitor Form Link',
                'key'       => '{visitor_form_link}',
                'templates' => [
                    'lead-customer-office-visitor-form-send',
                ],
            ],
            [
                'name'      => 'Leads Product',
                'key'       => '{product_name}',
                'available' => [
                    'leads',
                    'leads-follow-up',
                ],
                'templates' => [
                    'lead-new-mail-draft',
                ],
            ],
            [
                'name'      => 'Plant Visit Form Link',
                'key'       => '{plant_visit_form_link}',
                'templates' => [
                    'lead-plant-visit-form-not-approved',
                ],
            ],
            [
                'name'      => 'Plant Visit Form Not Approved Reason',
                'key'       => '{plant_visit_form_not_approved_reason}',
                'templates' => [
                    'lead-plant-visit-form-not-approved',
                ],
            ],
            [
                'name'      => 'Visit Date & Time',
                'key'       => '{plant_visit_date_time}',
                'templates' => [
                    'lead-plant-visit-form-approved',
                ],
            ],
            [
                'name'      => 'Total Payable Amount',
                'key'       => '{plant_visit_payable_amount}',
                'templates' => [
                    'lead-plant-visit-form-approved',
                ],
            ],
            [
                'name'      => 'Plant Visit Total Members',
                'key'       => '{plant_visit_total_members}',
                'templates' => [
                    'lead-plant-visit-form-approved',
                ],
            ],

        ];
    }

    /**
     * Lead merge fields
     * @param  mixed $id lead id
     * @return array
     */
    public function format($id, $otp_code = "")
    {
        $fields = [];

        $fields['{lead_name}']               = '';
        $fields['{lead_email}']              = '';
        $fields['{lead_position}']           = '';
        $fields['{lead_company}']            = '';
        $fields['{lead_country}']            = '';
        $fields['{lead_zip}']                = '';
        $fields['{lead_city}']               = '';
        $fields['{lead_state}']              = '';
        $fields['{lead_address}']            = '';
        $fields['{lead_assigned}']           = '';
        $fields['{lead_status}']             = '';
        $fields['{lead_source}']             = '';
        $fields['{lead_phonenumber}']        = '';
        $fields['{lead_link}']               = '';
        $fields['{lead_website}']            = '';
        $fields['{lead_description}']        = '';
        $fields['{lead_public_form_url}']    = '';
        $fields['{lead_public_consent_url}'] = '';
        $fields['{product_description}']     = '';
        $fields['{inquiry_form_link}']       = '';
        $fields['{inquiry_form_not_approved_reason}']       = '';
        $fields['{otp_code}']                = '';
        $fields['{visitor_form_link}']       = '';
        $fields['{product_name}']            = '';

        if (is_numeric($id)) {
            $this->ci->db->where('id', $id);
            $lead = $this->ci->db->get(db_prefix() . 'leads')->row();
        } else {
            $lead = $id;
        }

        if (!$lead) {
            return $fields;
        }

        $fields['{inquiry_form_not_approved_reason}'] = (isset($lead->inquiry_form_not_approved_reason)) ? $lead->inquiry_form_not_approved_reason : '';
        $fields['{product_description}']     =  (isset($lead->product_description)) ? $lead->product_description : '';
        $fields['{inquiry_form_link}']       = (isset($lead->inquiry_form_link)) ? $lead->inquiry_form_link : '';
        $fields['{visitor_form_link}']       = (isset($lead->visitor_form_link)) ? $lead->visitor_form_link : '';
        $fields['{lead_public_form_url}']    = leads_public_url($lead->id);
        $fields['{lead_public_consent_url}'] = lead_consent_url($lead->id);
        $fields['{lead_link}']               = admin_url('leads/index/' . $lead->id);
        $fields['{lead_name}']               = $lead->name;
        $fields['{lead_email}']              = $lead->email;
        $fields['{lead_position}']           = $lead->title;
        $fields['{lead_phonenumber}']        = $lead->phonenumber;
        $fields['{lead_company}']            = $lead->company;
        $fields['{lead_zip}']                = $lead->zip;
        $fields['{lead_city}']               = $lead->city;
        $fields['{lead_state}']              = $lead->state;
        $fields['{lead_address}']            = $lead->address;
        $fields['{lead_website}']            = $lead->website;
        $fields['{lead_description}']        = $lead->description;
        $fields['{otp_code}']                = $otp_code;
        $fields['{product_name}']  = implode(", ", get_tags_in($lead->id, 'lead'));

        $fields['{plant_visit_form_link}']   = (isset($lead->plant_visit_form_link)) ? $lead->plant_visit_form_link : '';
        $fields['{plant_visit_form_not_approved_reason}']  = (isset($lead->plant_visit_form_not_approved_reason)) ? $lead->plant_visit_form_not_approved_reason : '';
        $fields['{plant_visit_date_time}']       = (isset($lead->plant_visit_date_time)) ? date('d-m-Y H:i', strtotime($lead->plant_visit_date_time))  : '';
        $fields['{plant_visit_total_members}']       = (isset($lead->plant_visit_total_members)) ? $lead->plant_visit_total_members : '';
        $fields['{plant_visit_payable_amount}']       = (isset($lead->plant_visit_payable_amount)) ? app_format_money($lead->plant_visit_payable_amount, "INR") . "/-" : '';

        if ($lead->assigned != 0) {
            $fields['{lead_assigned}'] = get_staff_full_name($lead->assigned);
        }

        if ($lead->country != 0) {
            $country                  = get_country($lead->country);
            $fields['{lead_country}'] = $country->short_name;
        }

        if ($lead->junk == 1) {
            $fields['{lead_status}'] = _l('lead_junk');
        } elseif ($lead->lost == 1) {
            $fields['{lead_status}'] = _l('lead_lost');
        } else {
            $this->ci->db->select('name');
            $this->ci->db->from(db_prefix() . 'leads_status');
            $this->ci->db->where('id', $lead->status);
            $status = $this->ci->db->get()->row();
            if ($status) {
                $fields['{lead_status}'] = $status->name;
            }
        }

        $this->ci->db->select('name');
        $this->ci->db->from(db_prefix() . 'leads_sources');
        $this->ci->db->where('id', $lead->source);
        $source = $this->ci->db->get()->row();
        if ($source) {
            $fields['{lead_source}'] = $source->name;
        }

        $custom_fields = get_custom_fields('leads');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($lead->id, $field['id'], 'leads');
        }

        return hooks()->apply_filters('lead_merge_fields', $fields, ['id' => $lead->id, 'lead' => $lead]);
    }
}
