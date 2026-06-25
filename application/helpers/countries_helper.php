<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get all countries stored in database
 * @return array
 */
function get_all_countries()
{
    return hooks()->apply_filters('all_countries', get_instance()->db->get(db_prefix() . 'countries')->result_array());
}
/**
 * Get country row from database based on passed country id
 * @param  mixed $id
 * @return object
 */
function get_country($id)
{
    $CI = &get_instance();

    $country = $CI->app_object_cache->get('db-country-' . $id);

    if (!$country) {
        $CI->db->where('country_id', $id);
        $country = $CI->db->get(db_prefix() . 'countries')->row();
        $CI->app_object_cache->add('db-country-' . $id, $country);
    }

    return $country;
}
/**
 * Get country short name by passed id
 * @param  mixed $id county id
 * @return mixed
 */
function get_country_short_name($id)
{
    $country = get_country($id);
    if ($country) {
        return $country->iso2;
    }

    return '';
}
/**
 * Get country name by passed id
 * @param  mixed $id county id
 * @return mixed
 */
function get_country_name($id)
{
    $country = get_country($id);
    if ($country) {
        return $country->short_name;
    }

    return '';
}

function get_country_calling_code($id)
{
    $country = get_country($id);
    if ($country) {
        return $country->calling_code;
    }
    return '';
}

function convert_phonenumer_by_country($number, $country_iso2)
{
    $CI = &get_instance();
    if (empty($country_iso2)) {
        $country_iso2 = "IN";
    }
    if (strpos($number, '+') === 0) {
        return $number =  preg_replace('/[^0-9]/', '', $number);
    } else {
        $CI->db->where('iso2', $country_iso2);
        $country = $CI->db->get(db_prefix() . 'countries')->row();
        $country_code = $country->calling_code;
        if ($country) {
            $country_code = $country->calling_code;
            if (!empty($country_code)) {
                $number =  preg_replace('/[^0-9]/', '', $number);
                if (strpos($number, $country_code) === 0) {
                    return $number;
                } else {
                    $formatted_number = $country_code . $number;
                    return $formatted_number;
                }
            }
        }
    }
    return $number;
}

function get_country_id_by_iso2($iso2)
{
    $CI = &get_instance();
    $CI->db->where('iso2', $iso2);
    $country = $CI->db->get(db_prefix() . 'countries')->row();
    if ($country) {
        return $country->id;
    }
    return '';
}

/**
 * Country ID for India (used by location dropdowns).
 *
 * @return int
 */
function get_india_country_id()
{
    $CI = &get_instance();
    $CI->db->select('country_id');
    $CI->db->where('iso2', 'IN');
    $country = $CI->db->get(db_prefix() . 'countries')->row();

    return $country ? (int) $country->country_id : 0;
}

/**
 * Whether the Country → State → City dropdown flow applies (India only).
 *
 * @param int|string $country_id
 * @return bool
 */
function country_uses_city_dropdown($country_id)
{
    if (empty($country_id)) {
        return false;
    }

    return get_country_short_name($country_id) === 'IN';
}

/**
 * Build state/city option lists for location dropdowns.
 *
 * @param int|string $country_id
 * @param string     $state
 * @param string     $city
 * @return array{states: array, cities: array}
 */
function build_location_dropdown_data($country_id, $state = '', $city = '')
{
    $data = [
        'states' => [],
        'cities' => [],
    ];

    if (empty($country_id)) {
        return $data;
    }

    $CI = &get_instance();
    $CI->load->model('leadsnew_model');

    $country_code = get_country_short_name($country_id);
    if (!$country_code) {
        return $data;
    }

    $data['states'] = $CI->leadsnew_model->get_states(['country_code' => $country_code]);
    if (empty($data['states'])) {
        $country_name = get_country_name($country_id);
        if ($country_name) {
            $data['states'] = $CI->leadsnew_model->get_states(['country' => $country_name]);
        }
    }

    if (!empty($state)) {
        $data['cities'] = $CI->leadsnew_model->get_cities([
            'country_code' => $country_code,
            'state'        => $state,
        ]);
        if (empty($data['cities'])) {
            $country_name = get_country_name($country_id);
            if ($country_name) {
                $data['cities'] = $CI->leadsnew_model->get_cities([
                    'country' => $country_name,
                    'state'   => $state,
                ]);
            }
        }
    }

    return $data;
}
