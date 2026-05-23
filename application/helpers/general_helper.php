<?php

defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: text/html; charset=utf-8');

/**
 * Check if the document should be RTL or LTR
 * The checking are performed in multiple ways eq Contact/Staff Direction from profile or from general settings *
 * @param  boolean $client_area
 * @return boolean
 */
function is_rtl($client_area = false)
{
    $CI = &get_instance();
    if (is_client_logged_in()) {
        $CI->db->select('direction')->from(db_prefix() . 'contacts')->where('id', get_contact_user_id());
        $direction = $CI->db->get()->row()->direction;

        if ($direction == 'rtl') {
            return true;
        } elseif ($direction == 'ltr') {
            return false;
        } elseif (empty($direction)) {
            if (get_option('rtl_support_client') == 1) {
                return true;
            }
        }

        return false;
    } elseif ($client_area == true) {
        // Client not logged in and checked from clients area
        if (get_option('rtl_support_client') == 1) {
            return true;
        }
    } elseif (is_staff_logged_in()) {
        if (isset($GLOBALS['current_user'])) {
            $direction = $GLOBALS['current_user']->direction;
        } else {
            $CI->db->select('direction')->from(db_prefix() . 'staff')->where('staffid', get_staff_user_id());
            $direction = $CI->db->get()->row()->direction;
        }

        if ($direction == 'rtl') {
            return true;
        } elseif ($direction == 'ltr') {
            return false;
        } elseif (empty($direction)) {
            if (get_option('rtl_support_admin') == 1) {
                return true;
            }
        }

        return false;
    } elseif ($client_area == false) {
        if (get_option('rtl_support_admin') == 1) {
            return true;
        }
    }

    return false;
}

/**
 * Check whether the data is intended to be shown for the customer
 * For example this function is used for custom fields, pdf language loading etc...
 * @return boolean
 */
function is_data_for_customer()
{
    return is_client_logged_in()
        || (!is_staff_logged_in() && !is_client_logged_in())
        || defined('SEND_MAIL_TEMPLATE')
        || defined('CLIENTS_AREA')
        || defined('GDPR_EXPORT');
}

/**
 * Generate encryption key for app-config.php
 * @return stirng
 */
function generate_encryption_key()
{
    $CI = &get_instance();
    // In case accessed from my_functions_helper.php
    $CI->load->library('encryption');
    $key = bin2hex($CI->encryption->create_key(16));

    return $key;
}

/**
 * Return application version formatted
 * @return string
 */
function get_app_version()
{
    $CI = &get_instance();
    $CI->load->config('migration');

    return wordwrap($CI->config->item('migration_version'), 1, '.', true);
}

/**
 * Set current full url to for user to be redirected after login
 * Check below function to see why is this
 */
function redirect_after_login_to_current_url()
{
    $redirectTo = current_full_url();

    // This can happen if at the time you received a notification but your session was expired the system stored this as last accessed URL so after login can redirect you to this URL.
    if (strpos($redirectTo, 'notifications_check') !== false) {
        return;
    }

    get_instance()->session->set_userdata([
        'red_url' => $redirectTo,
    ]);
}
/**
 * Check if user accessed url while not logged in to redirect after login
 * @return null
 */
function maybe_redirect_to_previous_url()
{
    $CI = &get_instance();
    if ($CI->session->has_userdata('red_url')) {
        $red_url = $CI->session->userdata('red_url');
        $CI->session->unset_userdata('red_url');
        redirect($red_url);
    }
}
/**
 * Function used to validate all recaptcha from google reCAPTCHA feature
 * @param  string $str
 * @return boolean
 */
function do_recaptcha_validation($str = '')
{
    $CI = &get_instance();
    $CI->load->library('form_validation');
    $google_url = 'https://www.google.com/recaptcha/api/siteverify';
    $secret     = get_option('recaptcha_secret_key');
    $ip         = $CI->input->ip_address();
    $url        = $google_url . '?secret=' . $secret . '&response=' . $str . '&remoteip=' . $ip;
    $curl       = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($curl);
    curl_close($curl);
    $res = json_decode($res, true);
    //reCaptcha success check
    if ($res['success']) {
        return true;
    }
    $CI->form_validation->set_message('recaptcha', _l('recaptcha_error'));

    return false;
}
/**
 * Get current date format from options
 * @return string
 */
function get_current_date_format($php = false)
{
    $format = get_option('dateformat');
    $format = explode('|', $format);

    $format = hooks()->apply_filters('get_current_date_format', $format, $php);

    if ($php == false) {
        return $format[1];
    }

    return $format[0];
}
/**
 * Is user logged in
 * @return boolean
 */
function is_logged_in()
{
    return (is_client_logged_in() || is_staff_logged_in());
}
/**
 * Is client logged in
 * @return boolean
 */
function is_client_logged_in()
{
    return get_instance()->session->has_userdata('client_logged_in');
}
/**
 * Is staff logged in
 * @return boolean
 */
function is_staff_logged_in()
{
    return get_instance()->session->has_userdata('staff_logged_in');
}
/**
 * Return logged staff User ID from session
 * @return mixed
 */
function get_staff_user_id()
{
    $CI = &get_instance();

    if (defined('API')) {
        $CI->load->config('rest');

        $api_key_variable = $CI->config->item('rest_key_name');
        $key_name         = 'HTTP_' . strtoupper(str_replace('-', '_', $api_key_variable));

        if ($key = $CI->input->server($key_name)) {
            $CI->db->where('key', $key);
            $key = $CI->db->get($CI->config->item('rest_keys_table'))->row();
            if ($key) {
                return $key->user_id;
            }
        }
    }

    if (!is_staff_logged_in()) {
        return false;
    }

    return $CI->session->userdata('staff_user_id');
}
/**
 * Return logged client User ID from session
 * @return mixed
 */
function get_client_user_id()
{
    if (!is_client_logged_in()) {
        return false;
    }

    return get_instance()->session->userdata('client_user_id');
}

/**
 * Get contact user id
 * @return mixed
 */
function get_contact_user_id()
{
    $CI = &get_instance();
    if (!$CI->session->has_userdata('contact_user_id')) {
        return false;
    }

    return $CI->session->userdata('contact_user_id');
}
/**
 * Get timezones list
 * @return array timezones
 */
function get_timezones_list()
{
    return app\services\Timezones::get();
}

/**
 * Check if visitor is on mobile
 * @return boolean
 */
function is_mobile()
{
    if (get_instance()->agent->is_mobile()) {
        return true;
    }

    return false;
}
/**
 * Set session alert / flashdata
 * @param string $type    Alert type
 * @param string $message Alert message
 */
function set_alert($type, $message)
{
    get_instance()->session->set_flashdata('message-' . $type, $message);
}
/**
 * Redirect to blank admin page
 * @param  string $message Alert message
 * @param  string $alert   Alert type
 */
function blank_page($message = '', $alert = 'danger')
{
    set_alert($alert, $message);
    redirect(admin_url('not_found'));
}
/**
 * Redirect to access danied page and log activity
 * @param  string $permission If permission based to check where user tried to acces
 */
function access_denied($permission = '')
{
    set_alert('danger', _l('access_denied'));

    log_activity('Tried to access page where don\'t have permission' . ($permission != '' ? ' [' . $permission . ']' : ''));

    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        redirect($_SERVER['HTTP_REFERER']);
    } else {
        redirect(admin_url('access_denied'));
    }
}
/**
 * Throws header 401 not authorized, used for ajax requests
 */
function ajax_access_denied()
{
    header('HTTP/1.0 401 Unauthorized');
    echo _l('access_denied');
    die;
}
/**
 * Set debug message - message wont be hidden in X seconds from javascript
 * @since  Version 1.0.1
 * @param string $message debug message
 */
function set_debug_alert($message)
{
    get_instance()->session->set_flashdata('debug', $message);
}

/**
 * System popup message for admin area
 * This is used to show some general message for user within a big full screen div with white background
 * @param string $message message for the system popup
 */
function set_system_popup($message)
{
    if (!is_admin()) {
        return false;
    }

    if (defined('APP_DISABLE_SYSTEM_STARTUP_HINTS') && APP_DISABLE_SYSTEM_STARTUP_HINTS) {
        return false;
    }

    get_instance()->session->set_userdata([
        'system-popup' => $message,
    ]);
}
/**
 * Available date formats
 * @return array
 */
function get_available_date_formats()
{
    $date_formats = [
        'd-m-Y|%d-%m-%Y' => 'd-m-Y',
        'd/m/Y|%d/%m/%Y' => 'd/m/Y',
        'm-d-Y|%m-%d-%Y' => 'm-d-Y',
        'm.d.Y|%m.%d.%Y' => 'm.d.Y',
        'm/d/Y|%m/%d/%Y' => 'm/d/Y',
        'Y-m-d|%Y-%m-%d' => 'Y-m-d',
        'd.m.Y|%d.%m.%Y' => 'd.m.Y',
    ];

    return hooks()->apply_filters('available_date_formats', $date_formats);
}
/**
 * Get weekdays as array
 * @return array
 */
function get_weekdays()
{
    return [
        _l('wd_monday'),
        _l('wd_tuesday'),
        _l('wd_wednesday'),
        _l('wd_thursday'),
        _l('wd_friday'),
        _l('wd_saturday'),
        _l('wd_sunday'),
    ];
}
/**
 * Get non translated week days for query help
 * Do not edit this
 * @return array
 */
function get_weekdays_original()
{
    return [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];
}
/**
 * Outputs language string based on passed line
 * @since  Version 1.0.1
 * @param  string $line   language line key
 * @param  mixed $label   sprint_f label
 * @return string         language text
 */
function _l($line, $label = '', $log_errors = true)
{
    $CI = &get_instance();

    $hook_data = hooks()->apply_filters('before_get_language_text', ['line' => $line, 'label' => $label]);

    $line  = $hook_data['line'];
    $label = $hook_data['label'];

    if (is_array($label) && count($label) > 0) {
        $_line = vsprintf($CI->lang->line(trim($line), $log_errors), $label);
    } else {
        $_line = @sprintf($CI->lang->line(trim($line), $log_errors), $label);
    }

    $hook_data = hooks()->apply_filters('after_get_language_text', ['line' => $line, 'formatted_line' => $_line]);

    $_line = $hook_data['formatted_line'];
    $line  = $hook_data['line'];

    if ($_line != '') {
        if (preg_match('/"/', $_line) && !is_html($_line)) {
            $_line = html_escape($_line);
        }

        return ForceUTF8\Encoding::toUTF8($_line);
    }

    if (mb_strpos($line, '_db_') !== false) {
        return 'db_translate_not_found';
    }

    return ForceUTF8\Encoding::toUTF8($line);
}

/**
 * Format date to selected dateformat
 * @param  date $date Valid date
 * @return date/string
 */
function _d($date)
{
    $formatted = '';

    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return $formatted;
    }

    if (strpos($date, ' ') !== false) {
        return _dt($date);
    }

    $format    = get_current_date_format();
    $formatted = strftime($format, strtotime($date));

    return hooks()->apply_filters('after_format_date', $formatted, $date);
}

/**
 * Format datetime to selected datetime format
 * @param  datetime $date datetime date
 * @return datetime/string
 */
function _dt($date, $is_timesheet = false)
{
    $original = $date;

    if ($date == '' || is_null($date) || $date == '0000-00-00 00:00:00') {
        return '';
    }

    $format = get_current_date_format();
    $hour12 = (get_option('time_format') == 24 ? false : true);

    if ($is_timesheet == false) {
        $date = strtotime($date);
    }

    if ($hour12 == false) {
        $tf = '%H:%M:%S';
        if ($is_timesheet == true) {
            $tf = '%H:%M';
        }
        $date = strftime($format . ' ' . $tf, $date);
    } else {
        $date = date(get_current_date_format(true) . ' g:i A', $date);
    }

    return hooks()->apply_filters('after_format_datetime', $date, ['original' => $original, 'is_timesheet' => $is_timesheet]);
}

/**
 * Convert string to sql date based on current date format from options
 * @param  string $date date string
 * @return mixed
 */
function to_sql_date($date, $datetime = false)
{
    if ($date == '' || $date == null) {
        return null;
    }

    $to_date     = 'Y-m-d';
    $from_format = get_current_date_format(true);

    $date = hooks()->apply_filters('before_sql_date_format', $date, [
        'from_format' => $from_format,
        'is_datetime' => $datetime,
    ]);

    if ($datetime == false) {
        return hooks()->apply_filters('to_sql_date_formatted', date_format(date_create_from_format($from_format, $date), $to_date));
    }

    if (strpos($date, ' ') === false) {
        $date .= ' 00:00:00';
    } else {
        $hour12 = (get_option('time_format') == 24 ? false : true);
        if ($hour12 == false) {
            $_temp = explode(' ', $date);
            $time  = explode(':', $_temp[1]);
            if (count($time) == 2) {
                $date .= ':00';
            }
        } else {
            $tmp  = _simplify_date_fix($date, $from_format);
            $time = date('G:i', strtotime($tmp));
            $tmp  = explode(' ', $tmp);
            $date = $tmp[0] . ' ' . $time . ':00';
        }
    }

    $date = _simplify_date_fix($date, $from_format);
    $d    = strftime('%Y-%m-%d %H:%M:%S', strtotime($date));

    return hooks()->apply_filters('to_sql_date_formatted', $d);
}

/**
 * Function that will check the date before formatting and replace the date places
 * This function is custom developed because for some date formats converting to y-m-d format is not possible
 * @param  string $date        the date to check
 * @param  string $from_format from format
 * @return string
 */
function _simplify_date_fix($date, $from_format)
{
    if ($from_format == 'd/m/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $date);
    } elseif ($from_format == 'm/d/Y') {
        $date = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm.d.Y') {
        $date = preg_replace('#(\d{2}).(\d{2}).(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    } elseif ($from_format == 'm-d-Y') {
        $date = preg_replace('#(\d{2})-(\d{2})-(\d{4})\s(.*)#', '$3-$1-$2 $4', $date);
    }

    return $date;
}
/**
 * Check if passed string is valid date
 * @param  string  $date
 * @return boolean
 */
function is_date($date)
{
    if (strlen($date) < 10) {
        return false;
    }

    return (bool) strtotime($date);
}
/**
 * Get available locaes predefined for the system
 * If you add a language and the locale do not exist in this array you can use action hook to add new locale
 * @return array
 */
function get_locales()
{
    $locales = \app\services\utilities\Locale::app();

    return hooks()->apply_filters('before_get_locales', $locales);
}
/**
 * Get locale key by system language
 * @param  string $language language name from (application/languages) folder name
 * @return string
 */
function get_locale_key($language = 'english')
{
    $locale = \app\services\utilities\Locale::getByLanguage($language);

    return hooks()->apply_filters('before_get_locale', $locale);
}
/**
 * Get current url with query vars
 * @return string
 */
function current_full_url()
{
    $CI  = &get_instance();
    $url = $CI->config->site_url($CI->uri->uri_string());

    return $_SERVER['QUERY_STRING'] ? $url . '?' . $_SERVER['QUERY_STRING'] : $url;
}
/**
 * Triggers
 * @param  array  $users id of users to receive notifications
 * @return null
 */
function pusher_trigger_notification($users = [])
{
    if (get_option('pusher_realtime_notifications') == 0) {
        return false;
    }

    if (!is_array($users) || count($users) == 0) {
        return false;
    }

    $channels = [];
    foreach ($users as $id) {
        array_push($channels, 'notifications-channel-' . $id);
    }

    $channels = array_unique($channels);

    $CI = &get_instance();

    $CI->load->library('app_pusher');

    $CI->app_pusher->trigger($channels, 'notification', []);
}


/**
 * Generate md5 hash
 * @return string
 */
function app_generate_hash()
{
    return md5(rand() . microtime() . time() . uniqid());
}

/**
 * @since  2.3.2
 * Get CSRF formatter for AJAX usage
 * @return array
 */
function get_csrf_for_ajax()
{
    $csrf               = [];
    $csrf['formatted']  = [get_instance()->security->get_csrf_token_name() => get_instance()->security->get_csrf_hash()];
    $csrf['token_name'] = get_instance()->security->get_csrf_token_name();
    $csrf['hash']       = get_instance()->security->get_csrf_hash();

    return $csrf;
}

/**
 * If user have enabled CSRF proctection this function will take care of the ajax requests and append custom header for CSRF
 * @return mixed
 */
function csrf_jquery_token()
{
?>
    <script>
        if (typeof(jQuery) === 'undefined' && !window.deferAfterjQueryLoaded) {
            window.deferAfterjQueryLoaded = [];
            Object.defineProperty(window, "$", {
                set: function(value) {
                    window.setTimeout(function() {
                        $.each(window.deferAfterjQueryLoaded, function(index, fn) {
                            fn();
                        });
                    }, 0);
                    Object.defineProperty(window, "$", {
                        value: value
                    });
                },
                configurable: true
            });
        }

        var csrfData = <?php echo json_encode(get_csrf_for_ajax()); ?>;

        if (typeof(jQuery) == 'undefined') {
            window.deferAfterjQueryLoaded.push(function() {
                csrf_jquery_ajax_setup();
            });
            window.addEventListener('load', function() {
                csrf_jquery_ajax_setup();
            }, true);
        } else {
            csrf_jquery_ajax_setup();
        }

        function csrf_jquery_ajax_setup() {
            $.ajaxSetup({
                data: csrfData.formatted
            });
        }
    </script>
<?php
}

/**
 * In some places of the script we use app_happy_text function to output some words in orange color
 * @param  string $text the text to check
 * @return string
 */
function app_happy_text($text)
{
    $regex = hooks()->apply_filters('app_happy_text_regex', 'congratulations!?|congrats!?|happy!?|feel happy!?|awesome!?|yay!?');
    $re    = '/' . $regex . '/i';

    $app_happy_color = hooks()->apply_filters('app_happy_text_color', 'rgb(255, 59, 0)');

    preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
    foreach ($matches as $match) {
        $text = preg_replace(
            '/' . $match[0] . '/i',
            '<span style="color:' . $app_happy_color . ';font-weight:bold;">' . $match[0] . '</span>',
            $text
        );
    }

    return $text;
}

/**
 * Return server temporary directory
 * @return string
 */
function get_temp_dir()
{
    if (function_exists('sys_get_temp_dir')) {
        $temp = sys_get_temp_dir();
        if (@is_dir($temp) && is_writable($temp)) {
            return rtrim($temp, '/\\') . '/';
        }
    }

    $temp = ini_get('upload_tmp_dir');
    if (@is_dir($temp) && is_writable($temp)) {
        return rtrim($temp, '/\\') . '/';
    }

    $temp = app_temp_dir();

    if (is_dir($temp) && is_writable($temp)) {
        return $temp;
    }

    return '/tmp/';
}

/**
 * Creates instance of phpass
 * @since  2.3.1
 * @return object PasswordHash class
 */
function app_hasher()
{
    global $app_hasher;

    if (empty($app_hasher)) {
        require_once(APPPATH . 'third_party/phpass.php');
        // By default, use the portable hash from phpass
        $app_hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
    }

    return $app_hasher;
}

/**
 * Hashes password for user
 * @since  2.3.1
 * @param  string $password plain password
 * @return string
 */
function app_hash_password($password)
{
    return app_hasher()->HashPassword($password);
}

// TODO
function round_timesheet_time($datetime)
{
    $dt = new DateTime($datetime);
    $r  = 15;
    // echo roundUpToMinuteInterval($dt,$r)->format('Y-m-d H:i:s') . '<br />';
    // echo roundDownToMinuteInterval($dt,$r)->format('Y-m-d H:i:s') . '<br />';
    $datetime = roundUpToMinuteInterval($dt, $r)->format('Y-m-d H:i:s');

    return $datetime;
}

/**
 * @param $dateTime
 * @param int $minuteInterval
 * @return \DateTime
 */
function roundUpToMinuteInterval($dateTime, $minuteInterval = 10)
{
    return $dateTime->setTime(
        $dateTime->format('H'),
        ceil($dateTime->format('i') / $minuteInterval) * $minuteInterval,
        0
    );
}

/**
 * @param $dateTime
 * @param int $minuteInterval
 * @return \DateTime
 */
function roundDownToMinuteInterval($dateTime, $minuteInterval = 10)
{
    return $dateTime->setTime(
        $dateTime->format('H'),
        floor($dateTime->format('i') / $minuteInterval) * $minuteInterval,
        0
    );
}

/**
 * @param $dateTime
 * @param int $minuteInterval
 * @return \DateTime
 */
function roundToNearestMinuteInterval($dateTime, $minuteInterval = 10)
{
    return $dateTime->setTime(
        $dateTime->format('H'),
        round($dateTime->format('i') / $minuteInterval) * $minuteInterval,
        0
    );
}

/**
 * @since  2.3.2
 * Get last upgrade copy data if exists
 * @return mixed
 */
function get_last_upgrade_copy_data()
{
    $lastUpgradeCopyData = get_option('last_upgrade_copy_data');
    if ($lastUpgradeCopyData !== '') {
        $lastUpgradeCopyData = json_decode($lastUpgradeCopyData);

        return is_object($lastUpgradeCopyData) ? $lastUpgradeCopyData : false;
    }

    return false;
}

function get_count_proposal_download_request()
{
    $CI = &get_instance();
    $CI->db->where('download_request', '2');
    if (!is_admin()) {
        $CI->db->group_start();
        $CI->db->where('addedfrom', get_staff_user_id());
        $CI->db->or_where('assigned', get_staff_user_id());
        $CI->db->group_end();
    }
    $CI->db->from(db_prefix() . 'proposals');
    return $CI->db->count_all_results();
}

function convertNumberToWords($number, $currency = 'INR')
{
    if (is_numeric($currency)) {
        $currencyData = get_currency($currency);
        $currency = $currencyData->name;
    }
    if (strtoupper($currency) == "INR") {
        return convertAmountToWordsIndianFormat($number);
    } else {
        return convertAmountToWordsInternationalFormat($number, $currency);
    }
}


function convertAmountToWordsIndianFormat($number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety'
    );
    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore', 'billion', 'trillion');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
        } else
            $str[] = null;
    }
    $amountInWords = implode('', array_reverse($str));

    if ($decimal > 0) {
    $decimalInWords = " and " . ($words[floor($decimal / 10) * 10] . " " . $words[$decimal % 10]);
        $currencyInWords = ($amountInWords ? $amountInWords . 'rupees ' : '') . $decimalInWords . ' paisa only.';
    } else {
        $currencyInWords = ($amountInWords ? $amountInWords . 'rupees ' : '') . 'and zero paisa only.';
    }

    return $currencyInWords;
}

function convertAmountToWordsInternationalFormat($number, $currency)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $str = array();
    $words = array(
        0 => '',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety'
    );
    $digits = array('', 'thousand', 'million', 'billion', 'trillion');

    function convertLessThanOneThousand($number, $words)
    {
        $hundreds = intval($number / 100);
        $remainder = $number % 100;
        $result = '';
        if ($hundreds) {
            $result .= $words[$hundreds] . ' hundred';
            if ($remainder) $result .= ' and ';
        }
        if ($remainder < 20) {
            $result .= $words[$remainder];
        } else {
            $result .= $words[floor($remainder / 10) * 10];
            if (($remainder % 10) > 0) $result .= '-' . $words[$remainder % 10];
        }
        return $result;
    }

    $segment_counter = 0;
    while ($no > 0) {
        $divider = 1000;
        $number = $no % $divider;
        $no = floor($no / $divider);
        if ($number) {
            $segment = convertLessThanOneThousand($number, $words);
            if ($segment) $str[] = $segment . ' ' . $digits[$segment_counter];
        }
        $segment_counter++;
    }

    $amountInWords = implode(' ', array_reverse($str));
    $decimalInWords = ($decimal > 0) ? " and " . ($words[floor($decimal / 10) * 10] . " " . $words[$decimal % 10]) : '';

    switch (strtoupper($currency)) {
        case 'USD':
            $currencyInWords = ($amountInWords ? $amountInWords . ' USD' : '') . ($decimal > 0 ? $decimalInWords . ' cents' : '');
            break;
        case 'EUR':
            $currencyInWords = ($amountInWords ? $amountInWords . ' EUR' : '') . ($decimal > 0 ? $decimalInWords . ' cents' : '');
            break;
        case 'GBP':
            $currencyInWords = ($amountInWords ? $amountInWords . ' GBP' : '') . ($decimal > 0 ? $decimalInWords . ' pence' : '');
            break;
        default:
            $currencyInWords = $amountInWords . ($decimal > 0 ? $decimalInWords : '');
    }

    return trim($currencyInWords);
}




function calculateYearsAndMonths($date)
{
    $givenDate = new DateTime($date);
    $currentDate = new DateTime();
    $interval = $currentDate->diff($givenDate);
    $years = $interval->y;
    $months = $interval->m;
    return "{$years} years and {$months} months";
}


function get_count_staff_expire_contract()
{
    $CI = &get_instance();
    $current_date = date('Y-m-d');
    $next_week_date = date('Y-m-d', strtotime('+1 week', strtotime($current_date)));

    // Fetch count of expired contracts
    $CI->db->from(db_prefix() . 'staff_contract AS contract');
    $CI->db->join(db_prefix() . 'staff AS staff', 'staff.staffid = contract.staff');
    $CI->db->where('staff.active', 1);
    $CI->db->where('contract.end_valid <', $current_date);
    $expired_count = $CI->db->count_all_results();

    // Fetch count of contracts expiring soon
    $CI->db->from(db_prefix() . 'staff_contract AS contract');
    $CI->db->join(db_prefix() . 'staff AS staff', 'staff.staffid = contract.staff');
    $CI->db->where('staff.active', 1);
    $CI->db->where('contract.end_valid >=', $current_date);
    $CI->db->where('contract.end_valid <=', $next_week_date);
    $expiring_soon_count = $CI->db->count_all_results();

    return $expired_count + $expiring_soon_count;
}

function get_webmail_signature_data($name = "", $staff_id = "")
{
    if (empty($staff_id)) {
        $staff_id = get_staff_user_id();
    }
    $CI = &get_instance();
    $CI->db->where('user_id', $staff_id);
    $CI->db->order_by('id', 'DESC');
    $row = $CI->db->get(db_prefix() . 'webmail_signatures')->row_array();
    if (!empty($row)) {
        if (!empty($name)) {
            if (isset($row[$name])) {
                return $row[$name];
            }
        } else {
            return $row;
        }
    }
    return "";
}


function get_webmail_signature($staff_id = "")
{
    $CI = &get_instance();
    if (empty($staff_id)) {
        $staff_id = get_staff_user_id();
    }
    $data = get_webmail_signature_data("", $staff_id);
    if (isset($data['template']) && !empty($data['template'])) {
        return $CI->load->view("admin/webmails/email_signature_templates/" . $data['template'], $data, true);
    } else {
        return "";
    }
}

function get_whatsapp_signature()
{
    $CI = &get_instance();
    $CI->db->where('staffid', get_staff_user_id());
    $row = $CI->db->get(db_prefix() . 'staff')->row();
    if (!empty($row)) {
        return $row->whatsapp_signature;
    }
    return "";
}

function get_lead_reminder_status()
{
    return array(
        array(
            "status" => "Pending",
            "type" => "Call"
        ),
        array(
            "status" => "Attend",
            "type" => "Call"
        ),
        array(
            "status" => "Declined",
            "type" => "Call"
        ),
        array(
            "status" => "Not Attend",
            "type" => "Call"
        ),
        array(
            "status" => "Busy",
            "type" => "Call"
        ),
        array(
            "status" => "Not Reachable",
            "type" => "Call"
        ),
        array(
            "status" => "Pending",
            "type" => "Online Meeting"
        ),
        array(
            "status" => "Attend",
            "type" => "Online Meeting"
        ),
        array(
            "status" => "Not Attend",
            "type" => "Online Meeting"
        ),
        array(
            "status" => "Pending",
            "type" => "Plant Visit"
        ),
        array(
            "status" => "Visited",
            "type" => "Plant Visit"
        ),
        array(
            "status" => "Not Visited",
            "type" => "Plant Visit"
        ),
        array(
            "status" => "Pending",
            "type" => "Face To Face"
        ),
        array(
            "status" => "Present",
            "type" => "Face To Face"
        ),
        array(
            "status" => "Absent",
            "type" => "Face To Face"
        ),
    );
}

function message_html_to_text($htmlContent)
{
    $htmlContent = mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8');
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $plainText = domToPlainText($dom->documentElement);
    $plainText = preg_replace("/\n\s*\n/", "\n\n", $plainText);
    $plainText .= "\n\n" . get_whatsapp_signature();
    return trim($plainText);
}


function domToPlainText($node)
{
    $plainText = '';
    foreach ($node->childNodes as $child) {
        switch ($child->nodeType) {
            case XML_TEXT_NODE:
                $plainText .= $child->nodeValue;
                break;
            case XML_ELEMENT_NODE:
                if ($child->nodeName == 'br') {
                    $plainText .= "\n";
                } elseif ($child->nodeName == 'p') {
                    $plainText .= domToPlainText($child) . "\n\n";
                } elseif ($child->nodeName == 'span') {
                    $plainText .= domToPlainText($child);
                } else {
                    $plainText .= domToPlainText($child);
                }
                break;
        }
    }
    return $plainText;
}

function get_mergable_field_list($merge_file_name)
{
    $CI = &get_instance();
    $CI->load->library("merge_fields/$merge_file_name");
    return $CI->{$merge_file_name}->build();
}

function _error_on()
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}


function chartColors()
{
    return [
        '#3498DB', // Vivid Blue
        '#E74C3C', // Strong Red
        '#2ECC71', // Bright Green
        '#F39C12', // Bright Orange
        '#9B59B6', // Bright Purple
        '#1ABC9C', // Teal
        '#F1C40F', // Gold
        '#E67E22', // Deep Orange
        '#34495E', // Dark Blue Gray
        '#16A085', // Dark Teal
        '#F5B041', // Light Gold
        '#D35400', // Dark Orange
        '#7F8C8D', // Medium Gray
        '#EAB8AE', // Soft Pink
        '#C0392B', // Dark Red
        '#2C3E50', // Dark Blue
        '#BDC3C7', // Light Gray
        '#F4D03F', // Bright Yellow
        '#E74C3C', // Intense Red
        '#9B59B6', // Medium Purple
    ];
}

function _p($data)
{
    echo "<pre>";
    print_r($data);
}

function convertSecondsToTime($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf("%dh %dm %ds", $hours, $minutes, $seconds);
}

function getDays()
{
    return [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];
}

function get_count_today_plant_visit()
{
    $CI = &get_instance();
    $count = 0;
    $has_permission_view = (has_permission('meeting_dashboard', '', 'view'));
    if ($has_permission_view) {
        $CI->db->where('deleted_at IS NULL');
        $CI->db->where('DATE(date)', date('Y-m-d'));
        $CI->db->where_in('reminder_action', ["Online Meeting", "Face To Face", "Plant Visit"]);
        $CI->db->where('staff', get_staff_user_id());
        $CI->db->from(db_prefix() . 'reminders');
        $count = $CI->db->count_all_results();
    }
    return $count;
}

function get_month_list($year = null, $start_date = null)
{
    $months = [];

    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');

    if ($start_date) {
        $startDateObj = new DateTime($start_date);
    } else {
        $year = $year ?: $currentYear;
        $startDateObj = new DateTime("$year-01-01");
    }

    $startYear = (int)$startDateObj->format('Y');
    $startMonth = (int)$startDateObj->format('n');

    if ($year !== null && $year > $startYear) {
        $startYear = $year;
        $startMonth = 1;
    }

    $endYear = $year ?: $currentYear;
    $endMonth = ($year === null || $year == $currentYear) ? $currentMonth : 12;

    while ($startYear < $endYear || ($startYear == $endYear && $startMonth <= $endMonth)) {
        $startDate = date('Y-m-d', strtotime("$startYear-$startMonth-01"));
        $endDate = date('Y-m-t', strtotime("$startYear-$startMonth-01"));

        if ($startYear < $currentYear || ($startYear == $currentYear && $startMonth < $currentMonth)) {
            $status = 'past';
        } elseif ($startYear == $currentYear && $startMonth === $currentMonth) {
            $status = 'current';
        } else {
            $status = 'future';
        }

        $months[] = [
            'title' => date('F', strtotime("$startYear-$startMonth-01")),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status
        ];

        $startMonth++;
        if ($startMonth > 12) {
            $startMonth = 1;
            $startYear++;
        }
    }

    return array_reverse($months);
}

function get_half_yearly_list($year = null, $start_date = null)
{
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('n');
    $currentDate = date('Y-m-d');

    // Determine the year to use
    $year = $year ?: $currentYear;

    // Determine the start half
    $startHalf = 1;
    if ($start_date) {
        $startYear = (int)date('Y', strtotime($start_date));
        $startMonth = (int)date('n', strtotime($start_date));

        if ($startYear == $year && $startMonth > 6) {
            $startHalf = 2;
        }
    }

    // Determine the end half
    $endHalf = ($year == $currentYear) ? ceil($currentMonth / 6) : 2;

    $halves = [];

    for ($h = $startHalf; $h <= $endHalf; $h++) {
        if ($h == 1) {
            $startDate = "$year-01-01";
            $endDate = "$year-06-30";
            $title = "First Half Of $year";
        } else {
            $startDate = "$year-07-01";
            $endDate = "$year-12-31";
            $title = "Second Half Of $year";
        }

        $halves[] = [
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => ''
        ];
    }

    // Determine the status of each half: past, current, or future
    foreach ($halves as &$half) {
        if ($currentDate < $half['start_date']) {
            $half['status'] = 'future';
        } elseif ($currentDate >= $half['start_date'] && $currentDate <= $half['end_date']) {
            $half['status'] = 'current';
        } else {
            $half['status'] = 'past';
        }
    }

    // Sort halves by date in descending order
    usort($halves, function ($a, $b) {
        return strcmp($b['start_date'], $a['start_date']);
    });

    return array_values($halves);
}

function get_week_list($year = null, $start_date = null)
{
    $currentDate = new DateTime(); // Current date
    $currentWeek = (int)$currentDate->format('W'); // Current week number
    $currentYear = (int)$currentDate->format('Y'); // Current year

    // Convert start_date to DateTime if provided
    $startDateObj = $start_date ? new DateTime($start_date) : null;

    // If year is provided, use it, otherwise use the start year or current year
    $targetYear = $year ? (int)$year : $currentYear;

    // Determine the start week and year
    if ($startDateObj) {
        $startWeek = (int)$startDateObj->format('W');
        $startYear = (int)$startDateObj->format('Y');

        // Handle scenarios based on year and start date
        if ($startYear < $targetYear) {
            // If start_date is before the target year, start from week 1 of the target year
            $startWeek = 1;
        } else if ($startYear > $targetYear) {
            // If start_date is beyond the target year, return an empty array
            return [];
        }
    } else {
        // If no start_date is given, start from week 1 of the target year
        $startWeek = 1;
    }

    $weeks = [];
    $weekIterator = new DateTime();
    $weekIterator->setISODate($targetYear, $startWeek);

    // If year is null, continue until the present week of the current year, else use 52 weeks for the specified year
    $lastWeek = ($year === null || $targetYear == $currentYear) ? $currentWeek : 52;

    // Generate the week list
    while (true) {
        $weekNumber = (int)$weekIterator->format('W'); // Current week number in loop
        $weekYear = (int)$weekIterator->format('Y'); // Current year in loop

        // Stop if we reach beyond the current year or beyond the present week if year is null
        if ($weekYear > $currentYear || ($year !== null && $weekYear > $targetYear) || ($weekYear == $currentYear && $weekNumber > $currentWeek)) {
            break;
        }

        // Create start and end dates for the week
        $weekStartDate = clone $weekIterator;
        $weekEndDate = clone $weekIterator;
        $weekEndDate->modify('+6 days');

        $formattedStartDate = $weekStartDate->format('j M, Y');
        $formattedEndDate = $weekEndDate->format('j M, Y');
        $title = "Week $weekNumber: $formattedStartDate to $formattedEndDate";

        // Determine the status of the week (past, current, future)
        if ($weekYear < $currentYear || ($weekYear == $currentYear && $weekNumber < $currentWeek)) {
            $status = 'past';
        } elseif ($weekYear == $currentYear && $weekNumber == $currentWeek) {
            $status = 'current';
        } else {
            $status = 'future';
        }

        // Add the week data to the array
        $weeks[] = [
            'title' => $title,
            'start_date' => $weekStartDate->format('Y-m-d'),
            'end_date' => $weekEndDate->format('Y-m-d'),
            'status' => $status
        ];

        // Move to the next week
        $weekIterator->modify('+1 week');
    }

    // Return weeks in reverse order for past-to-future display
    return array_reverse($weeks);
}

function get_year_list($start_date)
{
    if (!$start_date) {
        throw new InvalidArgumentException("Start date is required");
    }

    $currentYear = (int)date('Y');
    $currentDate = date('Y-m-d');

    $startYear = (int)date('Y', strtotime($start_date));

    $years = [];

    for ($year = $startYear; $year <= $currentYear; $year++) {
        $yearStartDate = "$year-01-01";
        $yearEndDate = "$year-12-31";

        if ($year == $startYear && $start_date > $yearStartDate) {
            $yearStartDate = $start_date;
        }

        $years[] = [
            'title' => $year,
            'start_date' => $yearStartDate,
            'end_date' => $yearEndDate,
            'status' => ''
        ];
    }

    foreach ($years as &$yearData) {
        if ($currentDate < $yearData['start_date']) {
            $yearData['status'] = 'future';
        } elseif ($currentDate >= $yearData['start_date'] && $currentDate <= $yearData['end_date']) {
            $yearData['status'] = 'current';
        } else {
            $yearData['status'] = 'past';
        }
    }

    usort($years, function ($a, $b) {
        return strcmp($b['start_date'], $a['start_date']);
    });

    return array_values($years);
}

// function get_quarter_list($year = null, $start_date = null)
// {
//     $currentYear = (int)date('Y');
//     $currentMonth = (int)date('n');
//     $currentDate = date('Y-m-d');

//     $year = $year ?: $currentYear;

//     if ($start_date) {
//         $startYear = (int)date('Y', strtotime($start_date));
//         $startMonth = (int)date('n', strtotime($start_date));
//         $startQuarter = ceil($startMonth / 3);

//         // If start_date is in the previous year and selected year is current or future
//         if ($startYear < $year && $year >= $currentYear) {
//             $startQuarter = 1;
//         }
//         // If start_date is in the same year as selected year
//         elseif ($startYear == $year) {
//             $startQuarter = ceil($startMonth / 3);
//         }
//         // If start_date is in a future year compared to selected year
//         elseif ($startYear > $year) {
//             return []; // Return empty array as no valid quarters
//         }
//     } else {
//         $startQuarter = 1;
//     }

//     $endQuarter = ($year == $currentYear) ? ceil($currentMonth / 3) : 4;

//     $quarters = [];

//     for ($q = $startQuarter; $q <= $endQuarter; $q++) {
//         $startDate = date('Y-m-d', strtotime($year . '-' . (($q - 1) * 3 + 1) . '-01'));
//         $endDate = date('Y-m-t', strtotime($year . '-' . ($q * 3) . '-01'));

//         $quarters[] = [
//             'title' => "Quarter $q of $year",
//             'start_date' => $startDate,
//             'end_date' => $endDate,
//             'status' => ''
//         ];
//     }

//     foreach ($quarters as &$quarter) {
//         if ($currentDate < $quarter['start_date']) {
//             $quarter['status'] = 'future';
//         } elseif ($currentDate >= $quarter['start_date'] && $currentDate <= $quarter['end_date']) {
//             $quarter['status'] = 'current';
//         } else {
//             $quarter['status'] = 'past';
//         }
//     }

//     usort($quarters, function ($a, $b) {
//         return strcmp($b['start_date'], $a['start_date']);
//     });

//     return array_values($quarters);
// }
function get_quarter_list($year = null, $start_date = null)
{
    $currentYear = (int) date('Y');
    $currentMonth = (int) date('n');
    $currentDate = date('Y-m-d');

    // Use provided year or current year
    $fy_start_year = $year ?: $currentYear;

    // Adjust FY start if month is Jan-Mar
    if ($currentMonth < 4 && !$year) {
        $fy_start_year = $currentYear - 1;
    }

    $quarters = [];

    // Q1: Apr-Jun
    $quarters[] = [
        'title' => "Q1 (Apr - Jun $fy_start_year)",
        'start_date' => "$fy_start_year-04-01",
        'end_date' => "$fy_start_year-06-30",
    ];
    // Q2: Jul-Sep
    $quarters[] = [
        'title' => "Q2 (Jul - Sep $fy_start_year)",
        'start_date' => "$fy_start_year-07-01",
        'end_date' => "$fy_start_year-09-30",
    ];
    // Q3: Oct-Dec
    $quarters[] = [
        'title' => "Q3 (Oct - Dec $fy_start_year)",
        'start_date' => "$fy_start_year-10-01",
        'end_date' => "$fy_start_year-12-31",
    ];
    // Q4: Jan-Mar of next year
    $next_year = $fy_start_year + 1;
    $quarters[] = [
        'title' => "Q4 (Jan - Mar $next_year)",
        'start_date' => "$next_year-01-01",
        'end_date' => "$next_year-03-31",
    ];

    // Mark status
    foreach ($quarters as &$quarter) {
        if ($currentDate < $quarter['start_date']) {
            $quarter['status'] = 'future';
        } elseif ($currentDate >= $quarter['start_date'] && $currentDate <= $quarter['end_date']) {
            $quarter['status'] = 'current';
        } else {
            $quarter['status'] = 'past';
        }
    }

    // Return in descending order
    return array_reverse($quarters);
}


function getDatesBetween($startDate, $endDate, $notIncludeDate = [])
{
    $start = DateTime::createFromFormat('d-m-Y', $startDate);
    $end = DateTime::createFromFormat('d-m-Y', $endDate);

    if (!$start) {
        return [];
    }

    $currentDate = new DateTime();
    $currentDateStartOfDay = (clone $currentDate)->setTime(0, 0, 0);
    $currentDateEndOfDay = (clone $currentDate)->setTime(23, 59, 59);

    if ($end && $end > $currentDateEndOfDay) {
        $end = $currentDateEndOfDay;
    }

    $dates = [];
    $notIncludeDateTime = array_map(function ($date) {
        return DateTime::createFromFormat('Y-m-d', $date);
    }, $notIncludeDate);

    while ($start <= $end) {
        if ($start->format('N') == 7) {
            $start->modify('+1 day');
            continue;
        }

        $formattedDate = $start->format('d-m-Y');
        $title = $start->format('d M, Y');
        $shouldExclude = false;

        foreach ($notIncludeDateTime as $excludedDate) {
            if ($excludedDate && $excludedDate->format('Y-m-d') === $start->format('Y-m-d')) {
                $shouldExclude = true;
                break;
            }
        }

        if (!$shouldExclude) {
            $status = 'future';
            if ($start < $currentDateStartOfDay) {
                $status = 'past';
            } elseif ($start >= $currentDateStartOfDay && $start <= $currentDateEndOfDay) {
                $status = 'current';
            }

            $dates[] = [
                'title' => $title,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $start->format('Y-m-d'),
                'status' => $status
            ];
        }

        $start->modify('+1 day');
    }

    usort($dates, function ($a, $b) {
        return strtotime($b['start_date']) - strtotime($a['start_date']);
    });

    return $dates;
}

function getHolidayDatesArr($staff_id = "")
{
    if (empty($staff_id)) {
        $staff_id    = get_staff_user_id();
    }
    $staffData = get_staff($staff_id);
    $deptIds = get_hrm_department_ids_by_staffid($staff_id);
    $CI = &get_instance();
    $CI->db->select('break_date');
    $CI->db->where('staff_id', $staff_id);
    $CI->db->or_where('staff_id', '0');

    $CI->db->or_where('position', $staffData->job_position);
    $CI->db->or_where('position', '0');
    if (!empty($deptIds)) {
        $CI->db->or_where_in('department', $deptIds);
    }
    $CI->db->or_where('department', '0');
    $query = $CI->db->get(db_prefix() . 'day_off');
    $result = $query->result_array();
    if (!empty($result)) {
        return array_column($result, "break_date");
    }
    return [];
}


function get_current_quarter_dates()
{
    // Get the current year and month
    $currentMonth = date('m');
    $currentYear = date('Y');

    // Determine the current quarter and set the start and end dates
    if ($currentMonth >= 1 && $currentMonth <= 3) {
        // Q1: January - March
        $quarterStart = date('Y-01-01', strtotime($currentYear . '-01-01'));
        $quarterEnd = date('Y-03-31', strtotime($currentYear . '-03-31'));
    } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
        // Q2: April - June
        $quarterStart = date('Y-04-01', strtotime($currentYear . '-04-01'));
        $quarterEnd = date('Y-06-30', strtotime($currentYear . '-06-30'));
    } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
        // Q3: July - September
        $quarterStart = date('Y-07-01', strtotime($currentYear . '-07-01'));
        $quarterEnd = date('Y-09-30', strtotime($currentYear . '-09-30'));
    } else {
        // Q4: October - December
        $quarterStart = date('Y-10-01', strtotime($currentYear . '-10-01'));
        $quarterEnd = date('Y-12-31', strtotime($currentYear . '-12-31'));
    }

    return [
        'start_date' => $quarterStart,
        'end_date' => $quarterEnd
    ];
}


function get_current_week_dates()
{
    $currentDate = date('Y-m-d');

    $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($currentDate)));
    $weekEnd = date('Y-m-d', strtotime('sunday this week', strtotime($currentDate)));

    return [
        'start_date' => $weekStart,
        'end_date' => $weekEnd
    ];
}


function get_current_year_half_dates()
{
    $currentYear = date('Y');
    $currentMonth = date('m');

    if ($currentMonth >= 1 && $currentMonth <= 6) {
        $halfStart = date('Y-01-01', strtotime($currentYear . '-01-01'));
        $halfEnd = date('Y-06-30', strtotime($currentYear . '-06-30'));
        $title = "First Half of $currentYear";
    } else {
        $halfStart = date('Y-07-01', strtotime($currentYear . '-07-01'));
        $halfEnd = date('Y-12-31', strtotime($currentYear . '-12-31'));
        $title = "Second Half of $currentYear";
    }

    return [
        'start_date' => $halfStart,
        'end_date' => $halfEnd,
        'title' => $title
    ];
}

function decimalFormat($number)
{
    if (is_numeric($number) && floor($number) != $number) {
        return number_format((float)$number, 2, '.', '');
    }
    return $number;
}

function calculate_volume_of_business($goal_id = NULL, $staff_id = NULL, $start_date, $end_date)
{
    $CI = &get_instance();
    $staffIdArr = array();
    if (empty($staff_id)) {
        $staffIdArr = get_goal_staff_ids($goal_id, true);
    } else {
        if (is_array($staff_id)) {
            $staffIdArr = $staff_id;
        } else {
            $staffIdArr[] = $staff_id;
        }
    }
    $proposalIdArr = array();
    $invoiceIdArr = array();
    $estimateIdArr = array();
    $total_amount = 0;
    $total_transaction = 0;

    //fetch proposal created based on lead
    if (!empty($staffIdArr)) {
        $CI->db->select(db_prefix() . 'proposals.id,' . db_prefix() . 'proposals.invoice_id,' . db_prefix() . 'proposals.estimate_id');
        $CI->db->from(db_prefix() . 'proposals');
        $CI->db->join(db_prefix() . 'leads', 'leads.id = ' . db_prefix() . 'proposals.rel_id');
        $CI->db->where(db_prefix() . 'proposals.rel_type', 'lead');
        $CI->db->where_in(db_prefix() . 'leads.assigned', $staffIdArr);
        $CI->db->where(db_prefix() . 'leads.isDeleted', 'false');
        $CI->db->where(db_prefix() . 'proposals.deleted_at IS NULL');
        $CI->db->where(db_prefix() . 'leads.is_vendor', '0');
        $query = $CI->db->get();
        $resultData = $query->result_array();
        if (!empty($resultData)) {
            $proposalIdArr = array_values(array_filter(array_merge($proposalIdArr, array_column($resultData, 'id'))));
            $invoiceIdArr = array_values(array_filter(array_merge($invoiceIdArr, array_column($resultData, 'invoice_id'))));
            $estimateIdArr = array_values(array_filter(array_merge($estimateIdArr, array_column($resultData, 'estimate_id'))));
        }
    }

    //fetch proposal created based on customer with join leads
    if (!empty($staffIdArr)) {
        $CI->db->select(db_prefix() . 'proposals.id,' . db_prefix() . 'proposals.invoice_id,' . db_prefix() . 'proposals.estimate_id');
        $CI->db->from(db_prefix() . 'proposals');
        $CI->db->join(db_prefix() . 'clients', 'clients.userid = ' . db_prefix() . 'proposals.rel_id');
        $CI->db->join(db_prefix() . 'leads', 'leads.id = ' . db_prefix() . 'clients.leadid');
        $CI->db->where(db_prefix() . 'proposals.rel_type', 'customer');
        $CI->db->where_in(db_prefix() . 'leads.assigned', $staffIdArr);
        $CI->db->where(db_prefix() . 'leads.isDeleted', 'false');
        $CI->db->where(db_prefix() . 'proposals.deleted_at IS NULL');
        $CI->db->where(db_prefix() . 'clients.deleted_at IS NULL');
        $CI->db->where(db_prefix() . 'leads.is_vendor', '0');
        $query = $CI->db->get();
        $resultData = $query->result_array();
        if (!empty($resultData)) {
            $proposalIdArr = array_values(array_filter(array_merge($proposalIdArr, array_column($resultData, 'id'))));
            $invoiceIdArr = array_values(array_filter(array_merge($invoiceIdArr, array_column($resultData, 'invoice_id'))));
            $estimateIdArr = array_values(array_filter(array_merge($estimateIdArr, array_column($resultData, 'estimate_id'))));
        }
    }

    //Advance payment based on proposals
    if (!empty($proposalIdArr)) {
        $CI->db->select('SUM(amount) as total, count(id) as total_transaction');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords');
        $CI->db->where('invoiceid', 0);
        $CI->db->where_in('proposal_id', $proposalIdArr);
        $CI->db->where('deleted_at IS NULL');
        $CI->db->where("date BETWEEN '$start_date' AND '$end_date'");
        $result = $CI->db->get()->row();
        if (!empty($result)) {
            $total_amount += $result->total;
            $total_transaction += $result->total_transaction;
        }
    }

    // Get estimate > invoice > payment received amount.
    if (!empty($estimateIdArr)) {
        $CI->db->select('SUM(invoicepaymentrecords.amount) as total, count(invoicepaymentrecords.id) as total_transaction');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords as invoicepaymentrecords');
        $CI->db->join(db_prefix() . 'invoices as invoices', 'invoicepaymentrecords.invoiceid = invoices.id');
        $CI->db->join(db_prefix() . 'estimates as estimates', 'invoices.id = estimates.invoiceid');
        $CI->db->where_in('estimates.id', $estimateIdArr);
        $CI->db->where("invoicepaymentrecords.date BETWEEN '$start_date' AND '$end_date'");
        $CI->db->where('invoicepaymentrecords.deleted_at IS NULL');
        $CI->db->where('invoices.deleted_at IS NULL');
        $CI->db->where('estimates.deleted_at IS NULL');
        $result = $CI->db->get()->row();
        if (!empty($result)) {
            $total_amount += $result->total;
            $total_transaction += $result->total_transaction;
        }
    }

    // Get invoice > payment received amount.
    if (!empty($invoiceIdArr)) {
        $CI->db->select('SUM(amount) as total, count(id) as total_transaction');
        $CI->db->from(db_prefix() . 'invoicepaymentrecords');
        $CI->db->where_in('invoiceid', $invoiceIdArr);
        $CI->db->where("date BETWEEN '$start_date' AND '$end_date'");
        $CI->db->where('deleted_at IS NULL');
        $result = $CI->db->get()->row();
        if (!empty($result)) {
            $total_amount += $result->total;
            $total_transaction += $result->total_transaction;
        }
    }

    return ["total_amount" => $total_amount, "total_transaction" => $total_transaction];
}

function getAllDatesOfMonth($year, $month)
{
    $startDate = new DateTime("$year-$month-01");

    $endDate = clone $startDate;
    $endDate->modify('last day of this month');

    $dates = [];

    while ($startDate <= $endDate) {
        $dates[] = $startDate->format('Y-m-d');
        $startDate->modify('+1 day');
    }

    return $dates;
}

function get_custom_email_data($id)
{
    if (empty($id)) {
        return null;
    }

    if (!class_exists('email_campaigns_emails_model', false)) {
        get_instance()->load->model('email_campaigns_emails_model');
    }

    return get_instance()->email_campaigns_emails_model->get($id);
}

function get_mail_service_data($id)
{
    if (empty($id)) {
        return null;
    }

    if (!class_exists('mailservices_model', false)) {
        get_instance()->load->model('mailservices_model');
    }

    return get_instance()->mailservices_model->get_single($id);
}

function campaign_update($data, $id)
{
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $CI->db->update(db_prefix() . 'emailcampaign', $data);
}

function campaign_email_count($data)
{
    $CI = &get_instance();
    $CI->load->model('clients_model');
    $CI->load->model('leads_model');
    $CI->load->model('email_campaign_mail_list_model');

    $leadAssignedWhere = "lost = 0 AND isDeleted = 'false' AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
    $customerAssignedWhere = "active = 1 AND deleted_at IS NULL AND (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9][A-Za-z0-9.-]*\.[A-Za-z]{2,63}$')";
    $staff_ids = (isset($data['staff_ids']) && !empty($data['staff_ids'])) ? array_filter($data['staff_ids']) : [];
    if (!empty($staff_ids)) {
        $staff_ids =  implode(",", $staff_ids);
        $leadAssignedWhere .= " AND assigned IN (" . $staff_ids . ")";
        $customerAssignedWhere .= " AND userid In (" . $staff_ids . ") ";
    } else if (!has_permission('email_campaigns', '', 'view')) {
        if (is_manager()) {
            $leadAssignedWhere .= " AND assigned IN (" . get_manager_assigned_staff_ids("", true) . ")";
        } else {
            $leadAssignedWhere .= " AND assigned = " . get_staff_user_id();
        }
        $customerAssignedWhere .= " AND userid In (" . implode(",", get_assigned_customers_ids_by_staff()) . ") ";
    }

    $countryIds = (isset($data['countries']) && !empty($data['countries'])) ? array_filter($data['countries']) : [];
    if (!empty($countryIds)) {
        $countryIds =  implode(",", $countryIds);
        $leadAssignedWhere .= " AND country IN (" . $countryIds . ")";
        $customerAssignedWhere .= 'AND userid IN (select userid from ' . db_prefix() . 'clients where country IN (' . $countryIds . '))';
    }

    if ($data['type'] == "all_customer") {
        return total_rows(db_prefix() . "contacts", $customerAssignedWhere);
    } else if ($data['type'] == "customer_group") {
        $clients = $CI->clients_model->get_contacts('', $customerAssignedWhere . ' AND userid IN (select customer_id from ' . db_prefix() . 'customer_groups where groupid =' . $data['group_id'] . ')');
        return count($clients);
    } else if ($data['type'] == "all_leads") {
        return total_rows(db_prefix() . "leads", $leadAssignedWhere);
    } else if ($data['type'] == "leads_source") {
        $leadAssignedWhere .= ' AND source = ' . $data['source_id'];
        return total_rows(db_prefix() . "leads", $leadAssignedWhere);
    } else if ($data['type'] == "leads_status") {
        $leadAssignedWhere .= ' AND status = ' . $data['status_id'];
        return total_rows(db_prefix() . "leads", $leadAssignedWhere);
    } else if ($data['type'] == "all_staff") {
        $where = 'active = 1 AND datedeleted IS NULL';
        return total_rows(db_prefix() . "staff", $where);
    } else if ($data['type'] == "all_list") {
        $allListCount = $CI->email_campaign_mail_list_model->get_list_items();
        return count($allListCount);
    } else if ($data['type'] == "specific_list") {
        $arr[] = $data['list_id'];
        $listCount = $CI->email_campaign_mail_list_model->get_list_items($arr);
        return count($listCount);
    }
}


function createPdfThumbnail($pdfPath, $thumbnailPath, $resolution = 150)
{
    $pdfPath = escapeshellarg($pdfPath);
    $thumbnailPath = escapeshellarg($thumbnailPath);
    $isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);
    $gsCommand = $isLocalhost ? 'gswin64c' : 'gs';
    $command = "$gsCommand -sDEVICE=jpeg -dFirstPage=1 -dLastPage=1 -dJPEGQ=95 -r{$resolution}x{$resolution} -o $thumbnailPath $pdfPath";
    exec($command, $output, $returnVar);
    return $returnVar === 0;
}

function copyFolder($source, $destination)
{
    if (!is_dir($source)) {
        return;
    }

    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
        $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

        if (is_dir($sourcePath)) {
            copyFolder($sourcePath, $destinationPath);
        } else {
            copy($sourcePath, $destinationPath);
        }
    }

    closedir($dir);
}

function deleteFolder($folder)
{
    if (is_dir($folder)) {
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $path = $folder . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                deleteFolder($path);
            } else {
                unlink($path);
            }
        }
        rmdir($folder);
    }
}

function phonenumberSplit($input)
{
    $cleanedInput = preg_replace('/[\s-]+/', '', $input);
    $phoneNumberArr = array_values(array_filter(preg_split('/[\/,]+/', $cleanedInput)));
    return $phoneNumberArr;
}

function tutorialLinkButtonRender($slug, $position = "left", $customCss = "")
{
    $CI = &get_instance();
    $CI->load->database();
    $CI->db->select(db_prefix() . 'tutorial_links.*, ' . db_prefix() . 'tutorial_videos.link');
    $CI->db->from(db_prefix() . 'tutorial_links');
    $CI->db->join(db_prefix() . 'tutorial_videos', '' . db_prefix() . 'tutorial_links.video_id = ' . db_prefix() . 'tutorial_videos.id');
    $CI->db->where(db_prefix() . 'tutorial_links.active', '1');
    $CI->db->where(db_prefix() . 'tutorial_links.slug', $slug);
    $query = $CI->db->get();
    $html = "";
    if ($query->num_rows() > 0) {
        $data = $query->row_array();
        $html = '
        <style>
            .tutorial-btn-' . $data['id'] . '{
                    margin-top:2px;
                    background-color: ' . $data['button_color'] . ';
                    border-color: ' . $data['button_color'] . ';
                    color: ' . $data['button_text_color'] . ';
                    ' . $customCss . '
            }

            .tutorial-btn-' . $data['id'] . ':hover,
            .tutorial-btn-' . $data['id'] . ':active
            {
                    background-color: ' . $data['button_hover_color'] . ';
                    border-color: ' . $data['button_hover_color'] . ';
                    color: ' . $data['button_hover_text_color'] . ';
            }
            @media (max-width: 768px) {
                .tutorial-btn-' . $data['id'] . ' {
                    margin-top: 5px;
                    margin-bottom: 5px;
                    width: 100%;
                }
            }
        </style>
        <a href="' . $data['link'] . '" target="_blank" class="btn btn-info pull-' . $position . ' tutorial-btn-' . $data['id'] . '">' . $data['button_text'] . ' <i class="fa fa-external-link" aria-hidden="true"></i></a>';
    }
    return $html;
}


function get_attachments_by_type($type, $id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('rel_type', $type);
    $CI->db->where('rel_id', $id);
    return $CI->db->get(db_prefix() . 'files')->result_array();
}


function get_customer_linked_files($id)
{
    $mergedArray = [];
    $data = get_all_customer_attachments($id);
    foreach ($data as $key => $items) {
        if (!empty($items)) {
            $mergedArray = array_merge($mergedArray, $items);
        }
    }
    return array_values(attachement_unique_filter($mergedArray));
}

function attachement_unique_filter($data)
{
    $uniqueArray = [];
    $seenKeys = [];
    foreach ($data as $item) {
        if (!in_array($item['attachment_key'], $seenKeys)) {
            $seenKeys[] = $item['attachment_key'];
            $uniqueArray[] = $item;
        }
    }
    return array_values($uniqueArray);
}

function countDaysInclusive($startDate, $endDate)
{
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days + 1;
}


function expenseTripIdFormat($id)
{
    return sprintf("EXP-TRIP-%05d", $id);
}

function expenseAdvancePaymentIdFormat($id)
{
    return sprintf("EXP-ADV-%05d", $id);
}

function expenseReportIdFormat($id)
{
    return sprintf("EXP-REP-%05d", $id);
}

function expenseIdFormat($id)
{
    return sprintf("EXP-%05d", $id);
}

function expenseReimbursementIdFormat($id)
{
    return sprintf("EXP-REIMB-%05d", $id);
}

function getExpenseCategory($id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix() . 'expenses_categories');
    return $query->row_array();
}

function getExpenseMerchant($id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix() . 'expense_merchant');
    return $query->row_array();
}

function getExpenseLocation($id)
{
    $CI = &get_instance();
    $CI->db->select('CONCAT(city, ", ", country) AS location');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix() . 'cities');
    $result = $query->row_array();
    return isset($result['location']) ? $result['location'] : '';
}

function get_payment_mode_name($id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix() . 'payment_modes');
    $paymentMode = $query->row_array();
    return (isset($paymentMode['name'])) ?  $paymentMode['name'] : "-";
}

function save_dynamic_amount_fields($rel_type, $rel_id, $records)
{
    $CI = &get_instance();
    $CI->load->database();

    if (empty($rel_type) || empty($rel_id)) {
        return false;
    }

    if (empty($records)) {
        $CI->db->where('rel_type', $rel_type);
        $CI->db->where('rel_id', $rel_id);
        $CI->db->delete(db_prefix() . 'extra_amount');
        return true;
    }

    $submitted_ids = [];
    foreach ($records as $row) {
        $id = !empty($row['id']) ? (int)$row['id'] : null;
        $label = isset($row['label']) ? $row['label'] : null;
        $amount = isset($row['amount']) ? $row['amount'] : null;

        if ($id) {
            $submitted_ids[] = $id;
            $CI->db->where('id', $id);
            $CI->db->update(db_prefix() . 'extra_amount', [
                'label' => $label,
                'amount' => $amount
            ]);
        } else {
            $CI->db->insert(db_prefix() . 'extra_amount', [
                'rel_type' => $rel_type,
                'rel_id'   => $rel_id,
                'label'    => $label,
                'amount'   => $amount
            ]);
            $submitted_ids[] = $CI->db->insert_id();
        }
    }

    $CI->db->where('rel_type', $rel_type);
    $CI->db->where('rel_id', $rel_id);

    if (!empty($submitted_ids)) {
        $CI->db->where_not_in('id', $submitted_ids);
    }

    $CI->db->delete(db_prefix() . 'extra_amount');
    return true;
}

function get_dynamic_amount_fields($rel_type, $rel_id)
{
    $CI = &get_instance();
    $CI->load->database();

    if (empty($rel_type) || empty($rel_id)) {
        return [];
    }

    $CI->db->where('rel_type', $rel_type);
    $CI->db->where('rel_id', $rel_id);
    $CI->db->order_by('id', 'ASC');
    $query = $CI->db->get(db_prefix() . 'extra_amount');

    return $query->result_array();
}

function save_tax_by_relation($tax_rate, $tax_name, $rel_id, $rel_type)
{
    $CI = &get_instance();
    $CI->load->database();

    $table = db_prefix() . 'item_tax';

    $CI->db->where([
        'rel_id'   => $rel_id,
        'rel_type' => $rel_type
    ]);
    $query = $CI->db->get($table);
    $data = [
        'itemid'   => 0,
        'taxrate'  => $tax_rate,
        'taxname'  => $tax_name,
        'rel_id'   => $rel_id,
        'rel_type' => $rel_type
    ];

    if ($query->num_rows() > 0) {
        $CI->db->where([
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type
        ]);
        return $CI->db->update($table, $data);
    } else {
        return $CI->db->insert($table, $data);
    }
}

function get_tax_by_relation($rel_id, $rel_type)
{
    $CI = &get_instance();
    $CI->load->database();
    $table = db_prefix() . 'item_tax';
    $CI->db->where([
        'rel_id'   => $rel_id,
        'rel_type' => $rel_type
    ]);
    $query = $CI->db->get($table);

    if ($query->num_rows() > 0) {
        return $query->row();
    }
    return [];
}

function replace_dynamic_prefix($input)
{
    if (empty($input)) {
        return $input;
    }

    $current_date = new DateTime();
    $current_month = (int)$current_date->format('n');
    $current_year = (int)$current_date->format('Y');

    if ($current_month >= 4) {
        $financial_year_full = $current_year . ($current_year + 1);
        $financial_year = $current_year . substr(($current_year + 1), -2);
        $financial_year_short = substr($current_year, -2) . substr(($current_year + 1), -2);
    } else {
        $financial_year_full = ($current_year - 1) . $current_year;
        $financial_year = ($current_year - 1) . substr($current_year, -2);
        $financial_year_short = substr(($current_year - 1), -2) . substr($current_year, -2);
    }

    $next_year_date = clone $current_date;
    $next_year_date->modify('+1 year');

    $prefix_variables = [
        'current_full_year' => $current_date->format('Y'),
        'current_year_short' => $current_date->format('y'),
        'next_full_year' => $next_year_date->format('Y'),
        'next_year_short' => $next_year_date->format('y'),
        'current_month' => $current_date->format('m'),
        'current_month_name' => strtoupper($current_date->format('F')),
        'current_month_short' => strtoupper($current_date->format('M')),
        'current_date' => $current_date->format('d'),
        'financial_year_full' => $financial_year_full,
        'financial_year' => $financial_year,
        'financial_year_short' => $financial_year_short
    ];

    foreach ($prefix_variables as $key => $value) {
        $input = str_replace('{' . $key . '}', $value, $input);
    }
    return $input;
}

// function get_next_number($type, $prefix = '')
// {
//     $CI = &get_instance();
//     $prefix = trim($prefix);
//     $highest_number = 0;
//     switch ($type) {
//         case 'proposal':
//             $CI->db->select('MAX(proposal_number) as max_number');
//             $CI->db->where('proposal_number_prefix', $prefix);
//             $result = $CI->db->get(db_prefix() . 'proposals')->row();
//             break;

//         case 'invoice':
//             $CI->db->select('MAX(number) as max_number');
//             $CI->db->where('prefix', $prefix);
//             $result = $CI->db->get(db_prefix() . 'invoices')->row();
//             break;

//         case 'purchase':
//             $CI->db->select('MAX(purchase_number) as max_number');
//             $CI->db->where('purchase_number_prefix', $prefix);
//             $result = $CI->db->get(db_prefix() . 'purchase')->row();
//             break;

//         case 'contract':
//             $CI->db->select('MAX(number) as max_number');
//             $CI->db->where('prefix', $prefix);
//             $result = $CI->db->get(db_prefix() . 'contracts')->row();
//             break;

//         default:
//             return 1;
//     }

//     if ($result && $result->max_number) {
//         $highest_number = (int)$result->max_number;
//     }

//     return $highest_number + 1;
// }

function get_next_number($type, $prefix = '')
{
    $CI = &get_instance();
    $prefix = trim($prefix);
    $highest_number = 0;

    switch ($type) {

        case 'proposal':
            $CI->db->select('MAX(proposal_number) as max_number');
            $CI->db->where('proposal_number_prefix', $prefix);
            $result = $CI->db->get(db_prefix() . 'proposals')->row();
        break;

        case 'invoice':
            $CI->db->select('MAX(number) as max_number');
            $CI->db->where('prefix', $prefix);
            $result = $CI->db->get(db_prefix() . 'invoices')->row();
        break;

        case 'purchase':
            $CI->db->select('MAX(purchase_number) as max_number');
            $CI->db->where('purchase_number_prefix', $prefix);
            $result = $CI->db->get(db_prefix() . 'purchase')->row();
        break;

        case 'contract':
            $CI->db->select('MAX(number) as max_number');
            $CI->db->where('prefix', $prefix);
            $result = $CI->db->get(db_prefix() . 'contracts')->row();
        break;

        default:
            return '001';
    }

    if ($result && $result->max_number) {
        $highest_number = (int)$result->max_number;
    }

    $next_number = $highest_number + 1;

    // convert to 001,002,003 format
    return sprintf('%03d', $next_number);
}

function get_contact_book_category_name($id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix(). 'contact_book_category');
    $category = $query->row_array();
    return (isset($category['name']))?  $category['name'] : "";
}

function get_contact_book_full_name($id)
{
    $CI = &get_instance();
    $CI->db->select('*');
    $CI->db->where('id', $id);
    $query = $CI->db->get(db_prefix(). 'contact_book');
    $user = $query->row_array();
    return (isset($user['firstname']))?  $user['firstname'].' '.$user['lastname'] : "";
}