<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('app_admin_head', 'leads_app_admin_head_data');

function leads_app_admin_head_data()
{
?>
    <script>
        var leadUniqueValidationFields = <?php echo json_decode(json_encode(get_option('lead_unique_validation'))); ?>;
        var leadAttachmentsDropzone;

        function getLeadRowColorClass(data, staffUserId) {
            var lapselead = parseInt(data.lapselead, 10) || 0;
            var lastcontact = data.lastcontact;

            if (lapselead === 10) {
                return 'alert-danger';
            }
            if (lapselead === 9) {
                return 'alert-success';
            }
            if (lastcontact === null || lastcontact === '' || lastcontact === '0000-00-00 00:00:00') {
                return 'alert-my-info';
            }
            if (lapselead === 8) {
                return 'alert-warning';
            }
            if (lapselead === 7) {
                return 'alert-info';
            }
            if (lapselead === 6 || lapselead === 5 || lapselead === 4) {
                return 'alert-default-light';
            }
            if (parseInt(data.assigned, 10) === parseInt(staffUserId, 10)) {
                return 'alert-info';
            }
            return '';
        }
    </script>
<?php
}

/**
 * Check if the user is lead creator
 * @since  Version 1.0.4
 * @param  mixed  $leadid leadid
 * @param  mixed  $staff_id staff id (Optional)
 * @return boolean
 */

function is_lead_creator($lead_id, $staff_id = '')
{
    if (!is_numeric($staff_id)) {
        $staff_id = get_staff_user_id();
    }

    return total_rows(db_prefix() . 'leads', [
        'addedfrom' => $staff_id,
        'id'        => $lead_id,
    ]) > 0;
}

/**
 * Lead consent URL
 * @param  mixed $id lead id
 * @return string
 */
function lead_consent_url($id)
{
    return site_url('consent/l/' . get_lead_hash($id));
}

/**
 * Lead public form URL
 * @param  mixed $id lead id
 * @return string
 */
function leads_public_url($id)
{
    return site_url('forms/l/' . get_lead_hash($id));
}

/**
 * Get and generate lead hash if don't exists.
 * @param  mixed $id  lead id
 * @return string
 */
function get_lead_hash($id)
{
    $CI   = &get_instance();
    $hash = '';

    $CI->db->select('hash');
    $CI->db->where('id', $id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();
    if ($lead) {
        $hash = $lead->hash;
        if (empty($hash)) {
            $hash = app_generate_hash() . '-' . app_generate_hash();
            $CI->db->where('id', $id);
            $CI->db->update(db_prefix() . 'leads', ['hash' => $hash]);
        }
    }

    return $hash;
}

/**
 * Get leads summary
 * @return array
 */
function get_leads_summary($data = [])
{
    $CI = &get_instance();
    if (!class_exists('leads_model')) {
        $CI->load->model('leads_model');
    }
    $status_list = $CI->leads_model->get_status();

    $common_sql = ' AND is_vendor = "0" AND isDeleted = "false" ';

    $statuses = [];

    $statuses[] = [
        'total'  => true,
        'name'  => "Total Leads",
        'color' => '',
    ];

    $statuses = array_merge($statuses, $status_list);


    $totalStatuses   = count($statuses);



    //$has_permission_view   = has_permission('leads', '', 'view');
    $has_permission_view  = false;
    $sql = '';
    //$whereNoViewPermission = '(addedfrom = ' . get_staff_user_id() . ' OR assigned=' . get_staff_user_id() . ' OR is_public = 1)';
    if (isset($data) && !empty($data)) {
        $has_permission_view = true;
        if (isset($data['assignee']) && !empty($data['assignee'])) {
            $common_sql .= 'AND assigned IN ' . $data['assignee'];
        }
        $common_sql .= ' AND DATE(dateadded) >= "' . $data['startdate'] . '" AND DATE(dateadded) <= "' . $data['enddate'] . '"';
    } else {
        $has_permission_view   = (is_admin()) ? true : false;
        $whereNoViewPermission = 'assigned=' . get_staff_user_id();
    }

    $statuses[] = [
        'lost'  => true,
        'name'  => _l('lost_leads'),
        'color' => '',
    ];

    $statuses[] = [
        'junk'  => true,
        'name'  => _l('junk_leads'),
        'color' => '',
    ];

    foreach ($statuses as $status) {
        $sql .= ' SELECT COUNT(*) as total';
        $sql .= ' FROM ' . db_prefix() . 'leads';

        if (isset($status['lost'])) {
            $sql .= ' WHERE lost=1';
        } elseif (isset($status['junk'])) {
            $sql .= ' WHERE junk=1';
        } elseif (isset($status['total'])) {
            $sql .= ' WHERE lost = 0 AND junk = 0';
        } else {
            $sql .= ' WHERE status=' . $status['id'];
        }
        if (!$has_permission_view) {
            $sql .= ' AND ' . $whereNoViewPermission;
        }

        $sql .= $common_sql;


        $sql .= ' UNION ALL ';
        $sql = trim($sql);
    }
    // $sql = $sql . ' select count(tblleads.id) as total FROM tblleads Inner Join (SELECT Max(tblreminders.`date`) AS reminderdate, tblreminders.id,tblreminders.description,tblreminders.isnotified,tblreminders.rel_id, tblreminders.staff,tblreminders.rel_type,tblreminders.notify_by_email,tblreminders.creator FROM tblreminders GROUP BY tblreminders.rel_id) as reminder ON tblleads.id = reminder.rel_id  WHERE  if((lastcontact IS NULL AND CURDATE() > reminderdate) OR (lastcontact IS NOT NULL AND lastcontact <= reminderdate), 1, 0) = 1 '.$common_sql;
    $sql = $sql . ' select count(tblleads.id) as total FROM tblleads left Join (SELECT Max(tblreminders.`date`) AS reminderdate, tblreminders.id,tblreminders.description,tblreminders.isnotified,tblreminders.rel_id, tblreminders.staff,tblreminders.rel_type,tblreminders.notify_by_email,tblreminders.creator FROM tblreminders GROUP BY tblreminders.rel_id) as reminder ON tblleads.id = reminder.rel_id  WHERE lost = 0 AND junk = 0 AND reminderdate < NOW() AND is_vendor = "0" AND isDeleted = "false" ' . $common_sql;
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    $sql = $sql . ' UNION ALL select count(tblleads.id) as total FROM tblleads Left Join (SELECT Max(tblreminders.`date`) AS reminderdate, tblreminders.id,tblreminders.description,tblreminders.isnotified,tblreminders.rel_id, tblreminders.staff,tblreminders.rel_type,tblreminders.notify_by_email,tblreminders.creator FROM tblreminders GROUP BY tblreminders.rel_id) as reminder ON tblleads.id = reminder.rel_id WHERE date(reminderdate)  = date(NOW()) ' . $common_sql;
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    $sql = $sql . ' UNION ALL select count(tblleads.id) as total FROM tblleads Left Join (SELECT Max(tblreminders.`date`) AS reminderdate, tblreminders.id,tblreminders.description,tblreminders.isnotified,tblreminders.rel_id, tblreminders.staff,tblreminders.rel_type,tblreminders.notify_by_email,tblreminders.creator FROM tblreminders GROUP BY tblreminders.rel_id) as reminder ON tblleads.id = reminder.rel_id WHERE date(reminderdate)  = date(DATE_ADD(NOW(), INTERVAL 1 DAY)) ' . $common_sql;
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    $sql = $sql . ' UNION ALL select count(tblleads.id) as total FROM tblleads  Left Join tblreminders ON tblreminders.rel_id = tblleads.id where lastcontact is null and date is null ' . $common_sql;
    if (!$has_permission_view) {
        $sql .= ' AND ' . $whereNoViewPermission;
    }
    $result = [];

    $statuses[] = [
        'lapse'  => true,
        'name'  => 'Lapsed Lead',
        'color' => '#ff6f00',
    ];

    $statuses[] = [
        'today'  => true,
        'name'  => 'Today Lead',
        'color' => '#ff6f00',
    ];

    $statuses[] = [
        'tomorrow'  => true,
        'name'  => 'Tomorrow Lead',
        'color' => '#3c763d',
    ];

    $statuses[] = [
        'freshlead'  => true,
        'name'  => 'Fresh Lead',
        'color' => '#28B8DA',
    ];
    // Remove the last UNION ALL
    //$sql    = substr($sql, 0, -10);

    $query = $CI->db->query($sql);
    $result = $query->result();

    if (!$has_permission_view) {
        $CI->db->where($whereNoViewPermission);
    }

    $total_leads = $CI->db->count_all_results(db_prefix() . 'leads');

    foreach ($statuses as $key => $status) {
        if (isset($status['lost']) || isset($status['junk'])) {
            $statuses[$key]['percent'] = ($total_leads > 0 ? number_format(($result[$key]->total * 100) / $total_leads, 2) : 0);
        }

        $statuses[$key]['total'] = $result[$key]->total;
    }

    return $statuses;
}

/**
 * Render lead status select field with ability to create inline statuses with + sign
 * @param  array  $statuses         current statuses
 * @param  string  $selected        selected status
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @param  boolean $exclude_default whether to exclude default Client status
 * @return string
 */
function render_leads_status_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [], $exclude_default = false)
{
    foreach ($statuses as $key => $status) {
        if ($status['isdefault'] == 1) {
            if ($exclude_default == false) {
                if($status['id'] == 1){
                    $statuses[$key]['option_attributes'] = ['data-subtext' => _l('leads_converted_to_client')];
                }
                if($status['id'] == 75){
                    $statuses[$key]['option_attributes'] = ['data-subtext' => "Assigned to Customer"];
                }
            } else {
                unset($statuses[$key]);
            }
            break;
        }
    }

    if (is_admin() || get_option('staff_members_create_inline_lead_status') == '1') {
        return render_select_with_input_group($name, $statuses, ['id', 'name'], $lang_key, $selected, '<a href="#" onclick="new_lead_status_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', $select_attrs);
    }

    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

/**
 * Render lead source select field with ability to create inline source with + sign
 * @param  array   $sources         current sourcees
 * @param  string  $selected        selected source
 * @param  string  $lang_key        the label of the select
 * @param  string  $name            the name of the select
 * @param  array   $select_attrs    additional select attributes
 * @return string
 */
function render_leads_source_select($sources, $selected = '', $lang_key = '', $name = 'source', $select_attrs = [])
{
    if (is_admin() || get_option('staff_members_create_inline_lead_source') == '1') {
        echo render_select_with_input_group($name, $sources, ['id', 'name'], $lang_key, $selected, '<a href="#" onclick="new_lead_source_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', $select_attrs);
    } else {
        echo render_select($name, $sources, ['id', 'name'], $lang_key, $selected, $select_attrs);
    }
}

/**
 * Load lead language
 * Used in public GDPR form
 * @param  string $lead_id
 * @return string return loaded language
 */
function load_lead_language($lead_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $lead_id);
    $lead = $CI->db->get(db_prefix() . 'leads')->row();

    // Lead not found or default language already loaded
    if (!$lead || empty($lead->default_language)) {
        return false;
    }

    $language = $lead->default_language;

    if (!file_exists(APPPATH . 'language/' . $language)) {
        return false;
    }

    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];

    $CI->lang->load($language . '_lang', $language);
    if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
        $CI->lang->load('custom_lang', $language);
    }

    return true;
}

function updateOrCreateFilterdLeadRecord($lead_id)
{
    $CI =  &get_instance();
    // Check if record exists in tblfilteredleads
    $newLead = $CI->db->get_where('tblfilteredleads', array('id' => $lead_id))->row();

    if ($newLead) {
        // Record found in tblfilteredleads, update it based on tblleadsview
        $leadview = $CI->db->get_where('tblleadsview', array('id' => $lead_id))->row();
        if ($leadview) {
            $data = array(
                'name' => $leadview->name,
                'company' => $leadview->company,
                'email' => $leadview->email,
                'phonenumber' => $leadview->phonenumber,
                'city' => $leadview->city,
                'state' => $leadview->state,
                'country' => $leadview->country,
                'is_public' => $leadview->is_public,
                'tags' => $leadview->tags,
                'assigned_firstname' => $leadview->assigned_firstname,
                'short_name' => $leadview->short_name,
                'status_name' => $leadview->status_name,
                'source_name' => $leadview->source_name,
                'lastcontact' => $leadview->lastcontact,
                'dateadded' => $leadview->dateadded,
                'reminderdate' => $leadview->reminderdate,
                'lapselead' => $leadview->lapselead,
                'junk' => $leadview->junk,
                'lost' => $leadview->lost,
                'color' => $leadview->color,
                'status' => $leadview->status,
                'assigned' => $leadview->assigned,
                'assigned_lastname' => $leadview->assigned_lastname,
                'addedfrom' => $leadview->addedfrom,
                'is_converted' => $leadview->is_converted,
                'zip' => $leadview->zip,
                'isDeleted' => $leadview->isDeleted,
                'datedeleted' => $leadview->datedeleted,
                'deletedBy' => $leadview->deletedBy,
                'is_vendor' => $leadview->is_vendor,
                'gst_in' => $leadview->gst_in,
            );
            $CI->db->where('id', $lead_id);
            $CI->db->update('tblfilteredleads', $data);
        }
        log_activity('Lead Update Successful.');
    } else {
        $leadview = $CI->db->get_where('tblleadsview', array('id' => $lead_id))->row();
        if ($leadview) {
            $data = array(
                'id' => $leadview->id,
                'name' => $leadview->name,
                'company' => $leadview->company,
                'email' => $leadview->email,
                'phonenumber' => $leadview->phonenumber,
                'city' => $leadview->city,
                'state' => $leadview->state,
                'country' => $leadview->country,
                'is_public' => $leadview->is_public,
                'tags' => $leadview->tags,
                'assigned_firstname' => $leadview->assigned_firstname,
                'short_name' => $leadview->short_name,
                'status_name' => $leadview->status_name,
                'source_name' => $leadview->source_name,
                'lastcontact' => $leadview->lastcontact,
                'dateadded' => $leadview->dateadded,
                'reminderdate' => $leadview->reminderdate,
                'lapselead' => $leadview->lapselead,
                'junk' => $leadview->junk,
                'lost' => $leadview->lost,
                'color' => $leadview->color,
                'status' => $leadview->status,
                'assigned' => $leadview->assigned,
                'assigned_lastname' => $leadview->assigned_lastname,
                'addedfrom' => $leadview->addedfrom,
                'is_converted' => $leadview->is_converted,
                'zip' => $leadview->zip,
                'isDeleted' => $leadview->isDeleted,
                'datedeleted' => $leadview->datedeleted,
                'deletedBy' => $leadview->deletedBy,
                'is_vendor' => $leadview->is_vendor,
                'gst_in' => $leadview->gst_in,
            );
            $CI->db->insert('tblfilteredleads', $data);
        }
        log_activity('Lead Add Successful.');
    }
}

function refresh_tblfiltered_leads()
{
    $CI =  &get_instance();
    $CI->db->truncate('tblfilteredleads');
    log_message('info', 'table truncate Successful.');
    $CI->db->query("insert into tblfilteredleads SELECT * FROM tblleadsview;");
    log_message('info', 'table insert Successful.');
}

function customer_inquiry_form_render($fieldArray, $count, $requiredDisable, $formId)
{
    $requiredShow = $fieldArray['is_required'];
    if (!$requiredDisable) {
        $fieldArray['is_required'] = false;
    }
    $answers = [];
    if (isset($fieldArray['answer'])) {
        $answers = array_values(array_filter(explode(",", $fieldArray['answer'])));
    }

    $html = "";
    $type = $fieldArray['type'];
    $classNameCol = 'col-md-12';
    $type_options = array_values(array_filter(explode(",", $fieldArray['type_options'])));

    $html .= '<div class="' . $classNameCol . '">';
    $html .= '<div class="form-group" data-type="' . $type . '" data-name="' . $fieldArray['id'] . '" data-required="' . ($fieldArray['is_required'] ? true : false) . '">';
    $html .= '<div class="control-label"><div class="dragger todo-dragger"></div><span class="question_index">' . $count . ') </span>' . ucfirst($fieldArray['question']) .  ($requiredShow ? ' <span class="text-danger">* </span> ' : '') . '</div>';
    if ($type == 'link' || $type == 'input' || $type == 'number') {
        $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . (isset($fieldArray['placeholder']) ? ' placeholder="' . $fieldArray['placeholder'] . '"' : '') . ' type="' . $type . '" name="' . $fieldArray['id'] . '" id="field' . $fieldArray['id'] . '" class=" form-control' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" value="' . (isset($fieldArray['answer']) ? $fieldArray['answer'] : '') . '" form="' . $formId . '">';
    } elseif ($type == 'colorpicker') {
        $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . (isset($fieldArray['placeholder']) ? ' placeholder="' . $fieldArray['placeholder'] . '"' : '') . ' type="color" name="' . $fieldArray['id'] . '" id="field' . $fieldArray['id'] . '" class="' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" value="' . (isset($fieldArray['answer']) ? $fieldArray['answer'] : '') . '"' . ($type == 'file' ? ' accept="' . get_form_accepted_mimes() . '" filesize="' . file_upload_max_size() . '"' : '') . ' form="' . $formId . '">';
    } elseif ($type == 'textarea') {
        $html .= '<textarea' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' id="field' . $fieldArray['id'] . '" name="' . $fieldArray['id'] . '" rows="' . (isset($fieldArray['rows']) ? $fieldArray['rows'] : '4') . '" class="form-control ' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" placeholder="' . (isset($fieldArray['placeholder']) ? $fieldArray['placeholder'] : '') . '" form="' . $formId . '">' . (isset($fieldArray['answer']) ? $fieldArray['answer'] : '') . '</textarea>';
    } elseif ($type == 'date_picker') {
        $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' placeholder="' . (isset($fieldArray['placeholder']) ? $fieldArray['placeholder'] : '') . '" type="text" class="form-control ' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . 'render-input-disabled datepicker" name="' . $fieldArray['id'] . '" id="field' . $fieldArray['id'] . '" value="' . (isset($fieldArray['answer']) ? $fieldArray['answer'] : '') . '" form="' . $formId . '">';
    } elseif ($type == 'date_picker_time') {
        $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' placeholder="' . (isset($fieldArray['placeholder']) ? $fieldArray['placeholder'] : '') . '" type="text" class="form-control ' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . 'render-input-disabled datetimepicker" name="' . $fieldArray['id'] . '" id="field' . $fieldArray['id'] . '" value="' . (isset($fieldArray['answer']) ? $fieldArray['answer'] : '') . '" form="' . $formId . '">';
    } elseif ($type == 'select' || $type == 'multiselect') {
        $html .= '<select' . ($fieldArray['is_required'] ? ' required="true"' : '') . '' . (($type == 'multiselect') ? ' multiple="true"' : '') . ' class="selectpicker ' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" name="' . $fieldArray['id'] . ($type == 'multiselect' ? '[]' : '') . '" id="field' . $fieldArray['id'] . '"' . (isset($type_options) && count($type_options) > 10 ? 'data-live-search="true"' : '') . 'data-none-selected-text="' . (isset($fieldArray['placeholder']) ? $fieldArray['placeholder'] : 'Select Option') . '" data-width="100%" form="' . $formId . '">';
        $html .= '<option value="">Select option</option>';
        if (isset($type_options) && count($type_options) > 0) {
            foreach ($type_options as $option) {
                $html .= '<option value="' . $option . '" ' . (in_array($option, $answers) ? ' selected' : '') . '>' . ucfirst($option) . '</option>';
            }
        }
        $html .= '</select>';
    } elseif ($type == 'checkbox') {
        if (isset($type_options) && count($type_options) > 0) {
            $i = 0;
            $html .= '<div class="checkbox-section">';
            $type_options = array_chunk($type_options, 4);
            foreach ($type_options as $options_data) {
                $html .= '<div class="row">';
                foreach ($options_data as $checkbox) {
                    $html .= '<div class="col-md-3">';
                    $html .= '<label class="checkbox-inline for="chk_' . $fieldArray['id'] . '_' . $i . '">';
                    $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' class="' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" type="checkbox" id="chk_' . $fieldArray['id'] . '_' . $i . '" value="' . $checkbox . '" name="' . $fieldArray['id'] . '[]"' . (in_array($checkbox, $answers) ? ' checked' : '') . ' form="' . $formId . '">';
                    $html .= ucfirst($checkbox);
                    $html .= '</label>';
                    $html .= '</div>';
                    $i++;
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
    } elseif ($type == 'radio') {
        if (isset($type_options) && count($type_options) > 0) {
            $i = 0;
            $html .= '<div class="radio-section">';
            $type_options = array_chunk($type_options, 4);
            foreach ($type_options as $options_data) {
                $html .= '<div class="row">';
                foreach ($options_data as $radio) {
                    $html .= '<div class="col-md-3">';
                    $html .= '<label class="radio-inline" for="radio_' . $fieldArray['id'] . '_' . $i . '">';
                    $html .= '<input' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' class="' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" type="radio" id="radio_' . $fieldArray['id'] . '_' . $i . '" value="' . $radio . '" name="' . $fieldArray['id'] . '"' . (in_array($radio, $answers) ? ' checked' : '') . ' form="' . $formId . '">';
                    $html .= ucfirst($radio);
                    $html .= '</label>';
                    $html .= '</div>';
                    $i++;
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
    } elseif ($type == 'fileupload') {
        $html .= '<input ' . ($fieldArray['is_required'] ? ' required="true"' : '') . ' type="file" class="form-control ' . (isset($fieldArray['className']) ? $fieldArray['className'] : '') . '" id="fileupload_' . $fieldArray['id'] . '" name="' . $fieldArray['id'] . '" form="' . $formId . '" />';
        if (isset($fieldArray['answer']) && !empty($fieldArray['answer'])) {
            $file_url = site_url('download/file/lead_inquiry_form_files/' . $fieldArray['id']);
            $html .= "<span class = 'file-preview-section file-uploaded'>Preview : <a href='" . $file_url . "' class='preview-file' target='_blank'>" . $fieldArray['answer'] . "</a> <i class='fa fa-trash text-danger delete-inquiry-file' aria-hidden='true'></i></span>";
        }
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function generateUniqueString($length = 35)
{
    $timestamp = microtime(true);
    $timestampWithoutDot = str_replace('.', '', $timestamp);
    $randomBytes = random_bytes($length);
    $filteredString = preg_replace('/[^a-zA-Z0-9]/', '', bin2hex($randomBytes));
    $uniqueString = $timestampWithoutDot . $filteredString;
    $shuffledString = str_shuffle($uniqueString);
    return substr($shuffledString, 0, $length);
}

function inquiryFormMessageContent($customer_name, $product_name, $form_link)
{
    $CI = &get_instance();
    $CI->db->where('slug', 'lead-customer-inquiry-form-send');
    $query = $CI->db->get(db_prefix() . 'emailtemplates');
    $row = $query->row_array();
    $signatureCode = get_whatsapp_signature();
    $product_desc = "I wanted to reach out regarding some inquiries I have. ";
    if (!empty($product_name)) {
        $product_desc = "I am sharing an inquiry form to collect essential details for the " . $product_name . ". \n";
    }
    $message = $form_link;
    if ($row) {
        $message = $row['message'];
        $message = str_replace("{lead_name}", $customer_name, $message);
        $message = str_replace("{product_description}", $product_desc, $message);
        $message = str_replace("{inquiry_form_link}", $form_link, $message);
        $message = str_replace("{email_signature}", $signatureCode, $message);
    }
    $message = strip_tags($message);
    $message = str_replace('&nbsp;', ' ', $message);
    return $message;
}

function visitorFormWhatsappTemplate($slug, $customer_name, $form_link)
{
    $CI = &get_instance();
    $CI->db->where('slug', $slug);
    $query = $CI->db->get(db_prefix() . 'emailtemplates');
    $row = $query->row_array();
    $signatureCode = get_whatsapp_signature();
    $message = $form_link;
    if ($row) {
        $message = $row['message'];
        $message = str_replace("{lead_name}", $customer_name, $message);
        $message = str_replace("{visitor_form_link}", $form_link, $message);
        $message = str_replace("{email_signature}", $signatureCode, $message);
    }
    $message = strip_tags($message);
    $message = str_replace('&nbsp;', ' ', $message);
    return $message;
}

function generateWhatsappLink($phonenumber, $country_iso2, $message = "")
{
    $CI = &get_instance();
    if (empty($country_iso2)) {
        $country_iso2 = "IN";
    }
    $phonenumber = convert_phonenumer_by_country($phonenumber, $country_iso2);
    $link = "https://wa.me/{$phonenumber}";
    if (!empty($message)) {
        $encodedMessage = urlencode($message);
        $link .= "?text={$encodedMessage}";
    }
    return $link;
}

function leadLastContactAtUpdate($lead_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $lead_id);
    $CI->db->update(db_prefix() . 'leads', ["lastcontact " => date('Y-m-d H:i:s')]);
}

function inquiryFormApproveNotApproveMessageContent($customer_name, $type, $form_link, $not_approved_reason = "")
{
    $CI = &get_instance();
    if ($type == "approved") {
        $CI->db->where('slug', 'lead-customer-inquiry-form-approved');
    } else {
        $CI->db->where('slug', 'lead-customer-inquiry-form-not-approved');
    }
    $query = $CI->db->get(db_prefix() . 'emailtemplates');
    $row = $query->row_array();
    $signatureCode = get_whatsapp_signature();
    $message = $form_link;
    if ($row) {
        $message = $row['message'];
        $message = str_replace("{lead_name}", $customer_name, $message);
        $message = str_replace("{inquiry_form_not_approved_reason}", $not_approved_reason, $message);
        $message = str_replace("{inquiry_form_link}", $form_link, $message);
        $message = str_replace("{email_signature}", $signatureCode, $message);
    }
    $message = strip_tags($message);
    $message = str_replace('&nbsp;', ' ', $message);
    return $message;
}

function splitQuestionsIntoGroups($questionArr, $groupSize = 5)
{
    $result = [];
    $totalElements = count($questionArr);
    for ($i = 0; $i < $totalElements; $i += $groupSize) {
        $group = array_slice($questionArr, $i, $groupSize);
        $result[] = $group; // Add the group to the result
    }

    return $result;
}

function quotationFormMessageContent($customer_name, $form_link)
{
    $CI = &get_instance();
    $CI->db->where('slug', 'vendor-quotation-form-send');
    $query = $CI->db->get(db_prefix() . 'emailtemplates');
    $row = $query->row_array();
    $staffData = get_staff(get_staff_user_id());
    $signatureCode = get_whatsapp_signature();
    $message = $form_link;
    if ($row) {
        $message = $row['message'];
        $message = str_replace("{vendor_name}", $customer_name, $message);
        $message = str_replace("{quotation_form_link}", $form_link, $message);
        $message = str_replace("{email_signature}", $signatureCode, $message);
    }
    $message = strip_tags($message);
    $message = str_replace('&nbsp;', ' ', $message);
    return $message;
}

function vendorQuotationApproveNotApproveMessageContent($customer_name, $type, $form_link, $reject_note = "")
{
    $CI = &get_instance();
    if ($type == "approved") {
        $CI->db->where('slug', 'vendor-quotation-form-approved');
    } else {
        $CI->db->where('slug', 'vendor-quotation-form-not-approved');
    }
    $query = $CI->db->get(db_prefix() . 'emailtemplates');
    $row = $query->row_array();
    $staffData = get_staff(get_staff_user_id());
    $signatureCode = get_whatsapp_signature();
    $message = $form_link;
    if ($row) {
        $message = $row['message'];
        $message = str_replace("{vendor_name}", $customer_name, $message);
        $message = str_replace("{reject_note}", $reject_note, $message);
        $message = str_replace("{quotation_form_link}", $form_link, $message);
        $message = str_replace("{email_signature}", $signatureCode, $message);
    }
    $message = strip_tags($message);
    $message = str_replace('&nbsp;', ' ', $message);
    return $message;
}
function duplicateLeadData($email = "", $phonenumber = "", $rowResult = false)
{
    $CI = &get_instance();
    $result = [];

    if (!empty($email)) {
        $CI->db->select('*');
        $CI->db->where('email', $email);
        $leadquery = $CI->db->get(db_prefix() . 'leads');
        if ($leadquery->num_rows() > 0) {
            if ($rowResult) {
                $result = $leadquery->row();
            } else {
                $result = $leadquery->result();
            }
            return $result;
        }
    }

    if (!empty($phonenumber)) {
        $phoneFormatted = '+91' . $phonenumber;
        $phoneCleaned = preg_replace('/[^0-9]/', '', $phonenumber);
        $CI->db->select('*');
        $CI->db->where('phonenumber', $phonenumber);
        $CI->db->or_where('phonenumber', $phonenumber);
        $CI->db->or_where('phonenumber', $phoneFormatted);
        $CI->db->or_where('phonenumber', $phoneCleaned);
        $CI->db->or_like('phonenumber', $phoneFormatted, 'both');
        $CI->db->or_like('phonenumber', $phoneCleaned, 'both');
        $leadquery = $CI->db->get(db_prefix() . 'leads');
        if ($leadquery->num_rows() > 0) {
            if ($rowResult) {
                $result = $leadquery->row();
            } else {
                $result = $leadquery->result();
            }
            return $result;
        }
    }

    return $result;
}


function leadsEmailPreview($templateClass, $lead)
{
    $CI = &get_instance();
    if ($lead->is_vendor == "1") {
        $CI->load->library('merge_fields/vendors_merge_fields');
        $mergeableFields = $CI->vendors_merge_fields->format($lead);
    } else {
        $CI->load->library('merge_fields/leads_merge_fields');
        $mergeableFields = $CI->leads_merge_fields->format($lead);
    }
    $CI->load->library('merge_fields/staff_merge_fields');
    $mergeableFields = array_merge($mergeableFields, $CI->staff_merge_fields->format(get_staff_user_id()));
    $template = prepare_mail_preview_data($templateClass, $lead);
    $preview = parse_email_template_merge_fields($template['template'], $mergeableFields);
    return $preview;
}

function leadFormIdRender($prefix, $lead_id, $id)
{
    $formattedId = str_pad($id, 5, '0', STR_PAD_LEFT);
    return $prefix . '-' . $lead_id . '-' . $formattedId;
}

function officeVisitPurposeArr($isCustomer = "0", $id = null)
{
    $purposes = [
        1 => 'Business Meeting - Client',
        2 => 'Partnership / Collaboration Meeting',
        3 => 'Agreement discussion',
        4 => 'Customer',
    ];
    if ($id !== null && array_key_exists($id, $purposes)) {
        return $purposes[$id];
    }
    if ($isCustomer == "0") {
        unset($purposes[4]);
    }
    return $purposes;
}

function serviceRequestPurposeArr($id = null)
{
    $purposes = [
        1 => 'Maintenance / service request',
        2 => 'Equipment Maintenance',
        3 => 'Service request',
        4 => 'Technical Support',
        5 => 'Training / Workshop',
        6 => 'Training session',
        7 => 'Workshop attendance',
        8 => 'Seminar / Conference Participation',
        9 => 'Other',
    ];

    if ($id !== null && array_key_exists($id, $purposes)) {
        return $purposes[$id];
    }

    return $purposes;
}


function send_lead_template_email_from_webmail($templateClass, $lead)
{
    $CI = &get_instance();
    $CI->load->library('app_webmails');
    $uid = app_generate_hash();
    $preview_data = leadsEmailPreview($templateClass, $lead);
    if (!empty($preview_data)) {
        $sendData = array();
        $sendData['to'] = $lead->email;
        $sendData['subject'] = $preview_data->subject;
        $sendData['body'] = custom_inject_mail_add_tracking($preview_data->message, $uid);
        $mailSend = $CI->app_webmails->send_email($sendData);
        if ($mailSend) {
            custom_mail_add_tracking([
                "uid" => $uid,
                "email" => $lead->email,
                "subject" => $preview_data->subject,
                "message" => $preview_data->message,
                "rel_type" => 'lead',
                "rel_id" => $lead->id
            ]);
            return true;
        }
    }
    return false;
}

function checkLastContactAtUpdate($data, $id)
{
    if (isset($data['lastcontact'])) {
        unset($data['lastcontact']);
    }
    if (isset($data['csrf_token_name'])) {
        unset($data['csrf_token_name']);
    }
    if (isset($data['tags'])) {
        unset($data['tags']);
    }
    $CI = &get_instance();
    $leadsData = $CI->db->get_where(db_prefix() . 'leads', array('id' => $id))->row();
    if (!empty($leadsData)) {
        $isAssignedChanged = ($leadsData->assigned != $data['assigned']);
        $isCountryChanged = ($leadsData->country != $data['country']);
        $isStateChanged = ($leadsData->state != $data['state']);
        $isCityChanged = ($leadsData->city != $data['city']);

        unset($data['assigned']);
        unset($leadsData->assigned);

        unset($data['country']);
        unset($leadsData->country);

        unset($data['city']);
        unset($leadsData->city);

        unset($data['state']);
        unset($leadsData->state);

        $isOtherFieldsChanged = false;
        foreach ($data as $key => $value) {
            if (isset($leadsData->$key) && $leadsData->$key != $value) {
                $isOtherFieldsChanged = true;
                break;
            }
        }
        if (($isAssignedChanged || $isCountryChanged || $isStateChanged || $isCityChanged) && !$isOtherFieldsChanged) {
            return true;
        }
    }

    return false;
}

function get_plant_visitor_type($id)
{
    $CI = &get_instance();
    $CI->load->model('plant_visit_form_model');
    return $CI->plant_visit_form_model->get_plant_visitor_type_by_id($id);
}

function get_plant_visitform_check_free_visit($plantVisitData)
{
    $dayName = date('l', strtotime($plantVisitData['visit_date_time']));
    if ($plantVisitData['is_free_visit'] == "1" && $dayName == $plantVisitData['free_visit_day']) {
        return true;
    }
    return false;
}

function leads_permission_allow_to_manager($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $managerStaffIds = get_manager_assigned_staff_ids();
    if (!empty($lead_id)) {
        $lead_data = $CI->leads_model->get($lead_id);
        $managerRights = ($lead_data->is_vendor == "1") ? manager_employee_data_access_permission_check("vendors") : manager_employee_data_access_permission_check("leads");
        if ($managerRights) {
            if (in_array($lead_data->assigned, $managerStaffIds) || $lead_data->assigned == get_staff_user_id()) {
                return true;
            }
            if (in_array($lead_data->addedfrom, $managerStaffIds) || $lead_data->addedfrom == get_staff_user_id()) {
                return true;
            }
        }
    }
    return false;
}

function get_lead_full_name($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead_data = $CI->leads_model->get($lead_id);
    return $lead_data->name;
}

function get_source_name_by_id($source_id)
{
    $source_name = "";
    $CI = &get_instance();
    $CI->db->where('id', $source_id);
    $row = $CI->db->get(db_prefix() . 'leads_sources')->row();
    if ($row) {
        $source_name = $row->name;
    }
    return  $source_name;
}


function get_lead($lead_id)
{
    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead_data = $CI->leads_model->get($lead_id);
    return $lead_data;
}

/**
 * SQL expression for lead follow-up priority (uses CalculateLeadPriority DB function).
 *
 * @param string $lastcontactColumn
 * @param string $followupColumn  Reminder / follow-up date column
 * @param string $statusColumn
 * @return string
 */
function get_lead_priority_sql($lastcontactColumn = 'lastcontact', $followupColumn = 'reminderdate', $statusColumn = 'status')
{
    return 'CalculateLeadPriority(' . $lastcontactColumn . ',' . $followupColumn . ',' . $statusColumn . ') as lapselead';
}

/**
 * Bootstrap row class for a lead based on priority score and contact history.
 *
 * @param mixed      $lapselead
 * @param mixed      $lastcontact
 * @param int|null   $assigned
 * @param int|null   $staffId
 * @return string
 */
function get_lead_row_color_class($lapselead, $lastcontact = null, $assigned = null, $staffId = null)
{
    $priority = (int) $lapselead;

    if ($priority === 10) {
        return 'alert-danger';
    }

    if ($priority === 9) {
        return 'alert-success';
    }

    if ($lastcontact === null || $lastcontact === '' || $lastcontact === '0000-00-00 00:00:00') {
        return 'alert-my-info';
    }

    if ($priority === 8) {
        return 'alert-warning';
    }

    if ($priority === 7) {
        return 'alert-info';
    }

    if ($priority === 6) {
        return 'alert-default-light';
    }

    if ($priority === 5 || $priority === 4) {
        return 'alert-default-light';
    }

    if ($staffId !== null && (int) $assigned === (int) $staffId) {
        return 'alert-info';
    }

    return '';
}

/**
 * Legend items explaining lead row background colors.
 *
 * @return array<int, array{class: string, label: string}>
 */
function get_lead_color_legend_items()
{
    return [
        ['class' => 'alert-danger', 'label' => _l('lead_color_overdue')],
        ['class' => 'alert-success', 'label' => _l('lead_color_followup_today')],
        ['class' => 'alert-my-info', 'label' => _l('lead_color_never_contacted')],
        ['class' => 'alert-warning', 'label' => _l('lead_color_status_attention')],
        ['class' => 'alert-info', 'label' => _l('lead_color_followup_tomorrow')],
        ['class' => 'alert-info', 'label' => _l('lead_color_assigned_to_me')],
    ];
}

/**
 * Render lead row color legend (and optional status color key).
 *
 * @param array $statuses Lead statuses from leads_model->get_status()
 * @return void
 */
function render_lead_color_legend($statuses = [])
{
    $CI = &get_instance();
    $CI->load->view('admin/leads/includes/color_legend', [
        'legend_items' => get_lead_color_legend_items(),
        'statuses'     => $statuses,
    ]);
}
