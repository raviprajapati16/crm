<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_map extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('client_map_model');
        $this->load->model('invoice_map_model');
        // Ensure user has access to clients module
        if (!has_permission('customers', '', 'view') && !have_assigned_customers()) {
            access_denied('customers');
        }
    }

    // =========================================================================
    // MAIN VIEW
    // =========================================================================
    public function index()
    {
        $data['title'] = _l('clients') . ' - Map View';

        $this->load->model('clients_model');
        $data['groups'] = $this->clients_model->get_groups();

        // Pass countries that actually have clients
        $this->db->select('country_id, short_name');
        $this->db->from(db_prefix() . 'countries');
        $this->db->where('country_id IN (SELECT country FROM ' . db_prefix() . 'clients WHERE deleted_at IS NULL)');
        $data['countries'] = $this->db->get()->result_array();
        $data['geojson_version'] = $this->invoice_map_model->get_geojson_version_token();

        $this->load->view('admin/client_map/index', $data);
    }

    // =========================================================================
    // AJAX: MAP DATA (Aggregated Counts)
    // =========================================================================
    public function map_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $level   = $this->input->post('level') ?: 'world';
        $country = $this->input->post('country');
        $state   = $this->_canonicalize_state_name($this->input->post('state'));
        $filters = $this->_build_filters();

        $data       = [];
        $breadcrumb = [];

        if ($level === 'world') {
            $data = $this->client_map_model->get_world_data($filters);
            $breadcrumb[] = ['label' => 'World', 'level' => 'world'];

        } elseif ($level === 'country') {
            $data = $this->client_map_model->get_country_data($country, $filters);
            $breadcrumb[] = ['label' => 'World', 'level' => 'world'];
            $breadcrumb[] = ['label' => $country, 'level' => 'country', 'iso2' => $country];

        } elseif ($level === 'state') {
            $data = $this->client_map_model->get_state_data($country, $state, $filters);
            $breadcrumb[] = ['label' => 'World', 'level' => 'world'];
            $breadcrumb[] = ['label' => $country, 'level' => 'country', 'iso2' => $country];
            $breadcrumb[] = ['label' => $state,   'level' => 'state',   'iso2' => $country, 'state' => $state];
        }

        echo json_encode([
            'success'    => true,
            'level'      => $level,
            'data'       => $data,
            'breadcrumb' => $breadcrumb
        ]);
        die();
    }

    // =========================================================================
    // AJAX: CITY DETAILS (Modal List)
    // =========================================================================
    public function city_clients()
    {
        log_message('debug', 'city_clients called');
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $country = $this->input->post('country');
        $state   = $this->_canonicalize_state_name($this->input->post('state'));
        $city    = $this->input->post('city');
        $page    = (int) $this->input->post('page');
        $filters = $this->_build_filters();

        if (!$country) {
            echo json_encode(['success' => false, 'message' => 'Country is required.']);
            die();
        }

        $city  = $city ? trim($city) : '';
        $state = $state ? trim($state) : '';

        $result = $this->client_map_model->get_city_clients($country, $state, $city, $filters, $page);

        // Format for frontend
        foreach ($result['clients'] as &$cl) {
            $cl['client_url']     = admin_url('clients/client/' . $cl['userid']);
            $cl['date_formatted'] = _d(explode(' ', $cl['datecreated'])[0]);
            $cl['active_label']   = $cl['active'] == 1 ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';
        }

        echo json_encode([
            'success'     => true,
            'total_count' => $result['count'],
            'clients'     => $result['clients'],
            'page'        => $page,
            'has_more'    => count($result['clients']) === 100,
        ]);
        die();
    }

    // =========================================================================
    // EXPORT: CSV Download
    // =========================================================================
    public function export_csv()
    {
        $level   = $this->input->post('level') ?: 'world';
        $iso2    = $this->input->post('country');
        $state   = $this->input->post('state');
        $city    = $this->input->post('city');
        $filters = $this->_build_filters();

        $clients = $this->client_map_model->get_export_clients($level, $iso2, $state, $city, $filters);

        $company_name     = get_option('invoice_company_name') ?: get_option('companyname');
        $scope            = $this->_export_scope_label($level, $iso2, $state, $city);
        $filters_text     = $this->_export_filters_summary($filters);
        $totals_by_active = $this->_export_totals_by_active($clients);

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=clients_map_export_' . date('Ymd_His') . '.xls');

        $cols = 9;
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8">';
        echo '<style>table{border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;} .text{mso-number-format:"\\@";}</style>';
        echo '</head><body>';

        // Header
        echo '<table border="0" cellpadding="4"><tr><td colspan="' . $cols . '"><b>' . htmlspecialchars($company_name) . '</b></td></tr>';
        echo '<tr><td colspan="' . $cols . '"><font color="#28B8DA"><b>CUSTOMER MAP REPORT</b></font></td></tr>';
        echo '<tr><td colspan="' . $cols . '">Scope: ' . htmlspecialchars($scope) . '</td></tr>';
        if ($filters_text !== '') {
            echo '<tr><td colspan="' . $cols . '">' . htmlspecialchars($filters_text) . '</td></tr>';
        }
        echo '<tr><td colspan="' . $cols . '">Generated on ' . date('d M Y h:i A') . ' by ' . htmlspecialchars(get_staff_full_name(get_staff_user_id())) . '</td></tr>';
        echo '</table><br>';

        // Summary
        echo '<table border="1" cellpadding="5"><tr><td><b>Total Customers</b></td><td>' . count($clients) . '</td></tr>';

        if (!empty($totals_by_active[1])) {
            echo '<tr><td>Active:</td>';
            echo '<td><font color="' . $this->_export_active_color(1) . '"><b>' . $totals_by_active[1] . '</b></font></td></tr>';
        }
        if (!empty($totals_by_active[0])) {
            echo '<tr><td>Inactive:</td>';
            echo '<td><font color="' . $this->_export_active_color(0) . '"><b>' . $totals_by_active[0] . '</b></font></td></tr>';
        }
        echo '</table><br>';

        // Data
        $headers = ['#', 'Company', 'Phone', 'Active', 'Country', 'State', 'City', 'Groups', 'Date Created'];
        echo '<table border="1" cellpadding="5"><tr>';
        foreach ($headers as $h) {
            echo '<th bgcolor="#E8E8E8"><b>' . $h . '</b></th>';
        }
        echo '</tr>';

        if (empty($clients)) {
            echo '<tr><td colspan="' . $cols . '" align="center">No customers found.</td></tr>';
        } else {
            $counter = 1;
            foreach ($clients as $cl) {
                $active_label = $cl['active'] == 1 ? 'Active' : 'Inactive';
                $active_color = $this->_export_active_color($cl['active']);
                $date_created = !empty($cl['datecreated']) ? _d(explode(' ', $cl['datecreated'])[0]) : '';

                echo '<tr>';
                echo '<td align="center">' . $counter . '</td>';
                echo '<td>' . htmlspecialchars($cl['company'] ?? '') . '</td>';
                echo '<td class="text">' . htmlspecialchars($cl['phonenumber'] ?? '') . '</td>';
                echo '<td align="center"><font color="' . $active_color . '"><b>' . htmlspecialchars($active_label) . '</b></font></td>';
                echo '<td>' . htmlspecialchars($cl['country'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($cl['state'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($cl['city'] ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($cl['groups'] ?? '') . '</td>';
                echo '<td align="center">' . htmlspecialchars($date_created) . '</td>';
                echo '</tr>';
                $counter++;
            }
        }

        echo '</table></body></html>';
        exit;
    }

    // =========================================================================
    // EXPORT: PDF Download
    // =========================================================================
    public function export_pdf()
    {
        $level   = $this->input->post('level') ?: 'world';
        $iso2    = $this->input->post('country');
        $state   = $this->input->post('state');
        $city    = $this->input->post('city');
        $filters = $this->_build_filters();

        $clients = $this->client_map_model->get_export_clients($level, $iso2, $state, $city, $filters);

        $company_name     = get_option('invoice_company_name') ?: get_option('companyname');
        $scope            = $this->_export_scope_label($level, $iso2, $state, $city);
        $filters_text     = $this->_export_filters_summary($filters);
        $generated_on     = date('d M Y h:i A');
        $generated_by     = get_staff_full_name(get_staff_user_id());
        $totals_by_active = $this->_export_totals_by_active($clients);

        $th_style    = 'background-color:#f8f9fa; color:#2d2d2d; font-weight:bold; text-align:center; border:1px solid #dcdcdc;';
        $label_style = 'font-weight:bold; background-color:#f4f5f7;';
        $col_count   = 9;

        $html = $this->_export_pdf_logo_html();

        $html .= '<h2 style="text-align:center; font-weight:bold; color:#323a45; margin:4px 0;">' . htmlspecialchars($company_name) . '</h2>';
        $html .= '<h3 style="text-align:center; font-weight:bold; color:#28b8da; letter-spacing:1px; margin:4px 0;">CUSTOMER MAP REPORT</h3>';
        $html .= '<p style="text-align:center; font-size:12px; color:#555; margin:6px 0;">Scope: ' . htmlspecialchars($scope) . '</p>';
        if ($filters_text !== '') {
            $html .= '<p style="text-align:center; font-size:10px; color:#555; margin:4px 0;">' . htmlspecialchars($filters_text) . '</p>';
        }
        $html .= '<p style="text-align:right; font-size:9px; color:#777; margin:8px 0;">Generated on ' . htmlspecialchars($generated_on) . ' by ' . htmlspecialchars($generated_by) . '</p>';

        $html .= '<table width="40%" border="1" cellpadding="6" cellspacing="0">';
        $html .= '<tr><td colspan="2" style="font-weight:bold; background-color:#f7f9fa;">Summary</td></tr>';

        if (!empty($totals_by_active[1])) {
            $html .= '<tr><td width="60%" style="padding-left:10px;">Active:</td>';
            $html .= '<td width="40%" style="color:' . $this->_export_active_color(1) . '; font-weight:bold;">' . $totals_by_active[1] . '</td></tr>';
        }
        if (!empty($totals_by_active[0])) {
            $html .= '<tr><td width="60%" style="padding-left:10px;">Inactive:</td>';
            $html .= '<td width="40%" style="color:' . $this->_export_active_color(0) . '; font-weight:bold;">' . $totals_by_active[0] . '</td></tr>';
        }
        $html .= '<tr><td width="60%" style="' . $label_style . '">Total Customers:</td>';
        $html .= '<td width="40%">' . count($clients) . '</td></tr>';

        $html .= '</table>';
        $html .= $this->_export_pdf_spacer(12);
        $html .= '<hr style="border:none; border-top:2px solid #28b8da;">';
        $html .= $this->_export_pdf_spacer(10);

        $headers = ['#', 'Company', 'Phone', 'Active', 'Country', 'State', 'City', 'Groups', 'Date Created'];
        $widths  = ['5%', '18%', '12%', '8%', '10%', '10%', '10%', '17%', '10%'];

        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr>';
        foreach ($headers as $index => $h) {
            $html .= '<th width="' . $widths[$index] . '" style="' . $th_style . '">' . $h . '</th>';
        }
        $html .= '</tr>';

        if (empty($clients)) {
            $html .= '<tr><td colspan="' . $col_count . '" style="text-align:center; padding:16px; color:#777; font-style:italic; background-color:#fafafa;">No customers found for the selected scope and filters.</td></tr>';
        } else {
            $counter = 1;
            foreach ($clients as $cl) {
                $active_label = $cl['active'] == 1 ? 'Active' : 'Inactive';
                $active_color = $this->_export_active_color($cl['active']);
                $date_created = !empty($cl['datecreated']) ? _d(explode(' ', $cl['datecreated'])[0]) : '';
                $row_bg       = ($counter % 2 === 0) ? '#fafafa' : '#ffffff';

                $html .= '<tr style="background-color:' . $row_bg . ';">';
                $html .= '<td style="text-align:center;">' . $counter . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['company'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['phonenumber'] ?? '') . '</td>';
                $html .= '<td style="text-align:center; color:' . $active_color . '; font-weight:bold;">' . htmlspecialchars($active_label) . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['country'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['state'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['city'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($cl['groups'] ?? '') . '</td>';
                $html .= '<td style="text-align:center;">' . htmlspecialchars($date_created) . '</td>';
                $html .= '</tr>';

                $counter++;
            }
        }

        $html .= '</table>';

        try {
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($company_name);
            $pdf->SetTitle('Customer Map Report');
            $pdf->SetHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('customers_map_export_' . date('Ymd_His') . '.pdf', 'D');
        } catch (\Throwable $e) {
            log_message('error', 'Client Map PDF export failed: ' . $e->getMessage());
            show_error('Could not generate PDF: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================
    private function _canonicalize_state_name($state)
    {
        if ($state === null || $state === '') {
            return null;
        }

        $state = html_entity_decode((string) $state, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $state = strip_tags($state);
        $state = preg_replace('/\s*&\s*/', ' and ', $state);
        $state = preg_replace('/_+/', ' ', $state);
        $state = preg_replace('/\s+/', ' ', trim($state));
        $state = $this->_fold_diacritics($state);

        return $state !== '' ? substr($state, 0, 100) : null;
    }

    private function _fold_diacritics($text)
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        if (class_exists('Normalizer')) {
            $text = Normalizer::normalize($text, Normalizer::FORM_D);
            $text = preg_replace('/\p{M}/u', '', $text);
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        return $text;
    }

    private function _build_filters()
    {
        return [
            'exclude_inactive' => $this->input->post('exclude_inactive'),
            'groups'           => $this->input->post('groups'),
        ];
    }

    /**
     * TCPDF cannot load local paths via <img src="..."> — embed as base64 with @ prefix.
     */
    /**
     * TCPDF ignores CSS margin on tables — use an explicit height spacer row.
     */
    private function _export_pdf_spacer($height_px = 12)
    {
        $height_px = max(1, (int) $height_px);

        return '<table width="100%" border="0" cellpadding="0" cellspacing="0">'
            . '<tr><td height="' . $height_px . '" style="font-size:1px; line-height:' . $height_px . 'px;">&nbsp;</td></tr>'
            . '</table>';
    }

    private function _export_pdf_logo_html()
    {
        $width = (int) (get_option('pdf_logo_width') ?: 120);
        $path  = $this->_export_pdf_logo_path();

        if ($path === '' || !is_readable($path)) {
            return '';
        }

        $filesize = @filesize($path);
        if ($filesize === false || $filesize > 2 * 1024 * 1024) {
            return '';
        }

        $imageData = base64_encode(file_get_contents($path));
        if ($imageData === '') {
            return '';
        }

        return '<div style="text-align:center; margin-bottom:8px;">'
            . '<img src="@' . $imageData . '" width="' . $width . '" />'
            . '</div>';
    }

    private function _export_pdf_logo_path()
    {
        $company_dir = get_upload_path_by_type('company');

        $logo = get_option('company_logo');
        if ($logo !== '' && file_exists($company_dir . $logo)) {
            return $company_dir . $logo;
        }

        $custom = get_option('custom_pdf_logo_image_url');
        if ($custom !== '') {
            if (file_exists($custom)) {
                return $custom;
            }
            $relative = FCPATH . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $custom), DIRECTORY_SEPARATOR);
            if (file_exists($relative)) {
                return $relative;
            }
        }

        $logo_dark = get_option('company_logo_dark');
        if ($logo_dark !== '' && file_exists($company_dir . $logo_dark)) {
            return $company_dir . $logo_dark;
        }

        return '';
    }

    private function _export_scope_label($level, $iso2, $state, $city)
    {
        $breadcrumb = $this->_build_breadcrumb($level, $iso2, $state);
        $scope      = implode(' › ', array_column($breadcrumb, 'label'));

        if (!empty($city)) {
            $scope .= ' › ' . $city;
        }

        return $scope;
    }

    private function _export_filters_summary($filters)
    {
        $parts = [];

        if (!empty($filters['exclude_inactive']) && $filters['exclude_inactive'] == '1') {
            $parts[] = 'Exclude Inactive: Yes';
        }

        if (!empty($filters['groups'])) {
            $group_ids = is_array($filters['groups']) ? $filters['groups'] : [$filters['groups']];
            $group_ids = array_filter(array_map('intval', $group_ids));
            if ($group_ids) {
                $this->load->model('clients_model');
                $all_groups = $this->clients_model->get_groups();
                $names      = [];
                foreach ($all_groups as $group) {
                    if (in_array((int) $group['id'], $group_ids, true)) {
                        $names[] = $group['name'];
                    }
                }
                if ($names) {
                    $parts[] = 'Groups: ' . implode(', ', $names);
                }
            }
        }

        return implode(' | ', $parts);
    }

    private function _export_totals_by_active($clients)
    {
        $totals = [0 => 0, 1 => 0];

        foreach ($clients as $cl) {
            $key = (int) $cl['active'] === 1 ? 1 : 0;
            $totals[$key]++;
        }

        return $totals;
    }

    private function _export_active_color($active)
    {
        return (int) $active === 1 ? '#84C529' : '#FC2D42';
    }

    private function _build_breadcrumb($level, $iso2 = null, $state = null)
    {
        $bc = [['label' => 'World', 'level' => 'world', 'iso2' => null, 'state' => null]];

        if ($iso2) {
            $this->db->select('long_name')->where('iso2', $iso2);
            $row  = $this->db->get(db_prefix() . 'countries')->row();
            $bc[] = [
                'label' => $row ? $row->long_name : $iso2,
                'level' => 'country',
                'iso2'  => $iso2,
                'state' => null,
            ];
        }

        if ($state) {
            $bc[] = ['label' => $state, 'level' => 'state', 'iso2' => $iso2, 'state' => $state];
        }

        return $bc;
    }

    public function geojson()
    {
        $level = $this->input->get('level') ?: 'world';
        $iso2  = $this->input->get('iso2');
        $state = $this->input->get('state');
        $stateIso = $this->input->get('state_iso');

        // Sanitise
        $level = in_array($level, ['world', 'country', 'state']) ? $level : 'world';
        if ($iso2)  $iso2  = strtoupper(preg_replace('/[^a-zA-Z]/', '', $iso2));
        if ($stateIso) {
            $stateIso = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '', $stateIso));
            $stateIso = substr($stateIso, 0, 16);
        }
        if ($state) {
            $state = html_entity_decode($state, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $state = strip_tags($state);
            $state = preg_replace('/\s*&\s*/', ' and ', $state);
            $state = preg_replace('/\s+/', ' ', trim($state));
            $state = substr($state, 0, 100);
        }

        $geojson = $this->invoice_map_model->get_geojson($level, $iso2, $state, $stateIso ?: null);

        if ($geojson === false) {
            $this->output->set_status_header(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'GeoJSON not available for this region']);
            die();
        }

        $version = $this->invoice_map_model->get_geojson_version_token();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=86400, must-revalidate');
        header('ETag: "' . $version . '"');
        header('Vary: Accept-Encoding');
        echo $geojson;
        die();
    }

    // =========================================================================
    // AJAX: server-side geocode (client profile map pin)
    // =========================================================================
    public function geocode()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $query = trim((string) $this->input->get('q'));
        if ($query === '') {
            echo json_encode(['success' => false]);
            die();
        }

        $this->load->library('app_geocoder');
        $coords = $this->app_geocoder->get_coordinate($query);

        echo json_encode([
            'success' => (bool) $coords,
            'lat'     => $coords['latitude'] ?? null,
            'lon'     => $coords['longitude'] ?? null,
        ]);
        die();
    }
}
