<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Other_merge_fields extends App_merge_fields
{
    public function build()
    {
        $available_for = [
            'ticket',
            'client',
            'staff',
            'invoice',
            'estimate',
            'contract',
            'tasks',
            'proposals',
            'proposalterms',
            'project',
            'leads',
            'credit_note',
            'subscriptions',
            'gdpr',
            'vendors',
            'goals',
            'leads-follow-up',
            'purchase',
            'debit_note',
        ];

        $available_for = hooks()->apply_filters('other_merge_fields_available_for', $available_for);

        return [
            [
                'name'        => 'Logo URL',
                'key'         => '{logo_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Logo image with URL',
                'key'         => '{logo_image_with_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Dark logo image with URL',
                'key'         => '{dark_logo_image_with_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'CRM URL',
                'key'         => '{crm_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Admin URL',
                'key'         => '{admin_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Main Domain',
                'key'         => '{main_domain}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'      => 'Company Information',
                'key'       => '{company_information}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Company Name',
                'key'         => '{companyname}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Company GST Number',
                'key'         => '{company_gst_number}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Company PAN/IEC Number',
                'key'         => '{company_pan_number}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Company TAN Number',
                'key'         => '{company_tan_number}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Email Signature',
                'key'         => '{email_signature}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Terms & Conditions URL',
                'key'         => '{terms_and_conditions_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'        => 'Privacy Policy URL',
                'key'         => '{privacy_policy_url}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'      => 'Page Break',
                'key'       => '{page_break}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
            [
                'name'      => 'Company Brochure Link',
                'key'       => '{company_brochure_link}',
                'fromoptions' => true,
                'available'   => $available_for,
            ],
        ];
    }

    public function format()
    {
        $fields               = [];
        $fields['{logo_url}'] = base_url('uploads/company/' . get_option('company_logo'));

        $logo_width = hooks()->apply_filters('merge_field_logo_img_width', '');

        $fields['{logo_image_with_url}'] = '<a href="' . site_url() . '" target="_blank"><img src="' . base_url('uploads/company/' . get_option('company_logo')) . '"' . ($logo_width != '' ? ' width="' . $logo_width . '"' : '') . '></a>';

        $fields['{dark_logo_image_with_url}'] = '';
        if (get_option('company_logo_dark') != '') {
            $fields['{dark_logo_image_with_url}'] = '<a href="' . site_url() . '" target="_blank"><img src="' . base_url('uploads/company/' . get_option('company_logo_dark')) . '"' . ($logo_width != '' ? ' width="' . $logo_width . '"' : '') . '></a>';
        }

        $fields['{crm_url}']     = site_url('/authentication');
        $fields['{admin_url}']   = admin_url();
        $fields['{main_domain}'] = get_option('main_domain');
        $fields['{companyname}'] = get_option('companyname');
        $fields['{company_information}'] = format_organization_info();
        $fields['{company_gst_number}'] = get_option('company_vat');
        $fields['{company_pan_number}'] = get_option('company_pan_number');
        $fields['{company_tan_number}'] = get_option('company_tan_number');

        if (!is_staff_logged_in() || is_client_logged_in()) {
            $fields['{email_signature}'] = nl2br(get_option('email_signature'));
        } else {
            $this->ci->db->select('email_signature')->from(db_prefix() . 'staff')->where('staffid', get_staff_user_id());
            $signature = $this->ci->db->get()->row()->email_signature;
            if (empty($signature)) {
                $fields['{email_signature}'] = nl2br(get_option('email_signature'));
            } else {
                $fields['{email_signature}'] = $signature;
            }
        }

        $fields['{terms_and_conditions_url}'] = terms_url();
        $fields['{privacy_policy_url}']       = privacy_policy_url();

        $fields['{company_brochure_link}'] = '';
        if (get_option('company_brochure') != '') {
            $protected_path = protected_file_url_by_path(get_upload_path_by_type('company') . get_option('company_brochure'));
			$company_brochure_url = site_url('download/file_download?path=' . $protected_path);
            $fields['{company_brochure_link}'] = '<a href="' . $company_brochure_url . '" target="_blank">Download Brochure</a>';
        }


        return hooks()->apply_filters('other_merge_fields', $fields);
    }
}
