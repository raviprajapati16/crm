<?php
class Invoice_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        if (!is_admin()) {
            access_denied('invoice_settings');
        }
        $this->load->view('admin/invoice_settings/manage');
    }


    public function save()
    {
        if (!is_admin()) {
            access_denied('invoice_settings');
        }

        // ── Raw POST (XSS-clean disabled so prefix variables aren't stripped) ──
        $data = $this->input->post(null, false);

        $success_count = 0;
        $error_count   = 0;

        // ── Extract branch array fields ───────────────────────────────────────
        $submitted_ids    = isset($data['branch_id'])      ? (array) $data['branch_id']      : [];
        $branch_names     = isset($data['branch_name'])    ? (array) $data['branch_name']    : [];
        $invoice_prefixes = isset($data['invoice_prefix']) ? (array) $data['invoice_prefix'] : [];
        $gst_numbers      = isset($data['gst_number'])     ? (array) $data['gst_number']     : [];

        // Remove arrays so the generic loop below doesn't try to store them
        unset($data['branch_id'], $data['branch_name'], $data['invoice_prefix'], $data['gst_number']);

        // Load existing branches from DB
        $existing_json = get_option('branch_rows');
        $existing_branches = $existing_json ? json_decode($existing_json, true) : [];
        if (!is_array($existing_branches)) {
            $existing_branches = [];
        }

        // Ensure all existing branches have a unique ID
        foreach ($existing_branches as &$eb) {
            if (empty($eb['id'])) {
                $eb['id'] = uniqid('br_');
            }
        }
        unset($eb);

        // Map existing branches by ID for lookup
        $existing_map = [];
        foreach ($existing_branches as $eb) {
            $existing_map[$eb['id']] = $eb;
        }

        $processed_branches = [];
        $submitted_processed_ids = [];

        // ── Build/update submitted rows ───────────────────────────────────────
        $total_submitted = max(count($branch_names), count($invoice_prefixes), count($gst_numbers));
        for ($i = 0; $i < $total_submitted; $i++) {
            $sub_id     = isset($submitted_ids[$i]) ? trim($submitted_ids[$i]) : '';
            $sub_name   = isset($branch_names[$i]) ? trim($branch_names[$i]) : '';
            $sub_prefix = isset($invoice_prefixes[$i]) ? trim($invoice_prefixes[$i]) : '';
            $sub_gst    = isset($gst_numbers[$i]) ? trim($gst_numbers[$i]) : '';

            if (empty($sub_name)) {
                continue; // Skip empty rows
            }

            if (!empty($sub_id) && isset($existing_map[$sub_id])) {
                $branch = $existing_map[$sub_id];
                $branch['branch_name']    = $sub_name;
                $branch['invoice_prefix'] = $sub_prefix;
                $branch['gst_number']     = $sub_gst;
                $branch['deleted']        = 0;
            } else {
                $sub_id = uniqid('br_');
                $branch = [
                    'id'              => $sub_id,
                    'branch_name'     => $sub_name,
                    'invoice_prefix'  => $sub_prefix,
                    'gst_number'      => $sub_gst,
                    'proposal_prefix' => 'PROP-',
                    'deleted'         => 0
                ];
            }

            $processed_branches[$sub_id] = $branch;
            $submitted_processed_ids[]   = $sub_id;
        }

        // ── Process deleted/removed branches (in DB but not in POST) ──────────
        $warnings = [];
        foreach ($existing_branches as $eb) {
            $eb_id = $eb['id'];
            if (!in_array($eb_id, $submitted_processed_ids)) {
                // If it was already deleted, just keep it
                if (!empty($eb['deleted'])) {
                    $processed_branches[$eb_id] = $eb;
                    continue;
                }

                // Check if in use in invoices or proposals
                $in_use = false;
                $gst = trim($eb['gst_number'] ?? '');
                $invoice_prefix = trim($eb['invoice_prefix'] ?? '');
                $proposal_prefix = trim($eb['proposal_prefix'] ?? '');

                // Check Invoices table
                if ($gst !== '') {
                    $this->db->where('gst_number', $gst);
                    if ($this->db->count_all_results(db_prefix() . 'invoices') > 0) {
                        $in_use = true;
                    }
                }
                if (!$in_use && $invoice_prefix !== '') {
                    $resolved_invoice_prefix = replace_dynamic_prefix($invoice_prefix);
                    $this->db->where('prefix', $resolved_invoice_prefix);
                    if ($this->db->count_all_results(db_prefix() . 'invoices') > 0) {
                        $in_use = true;
                    }
                }

                // Check Proposals table
                if (!$in_use && $gst !== '') {
                    $this->db->where('proposal_gst_number', $gst);
                    if ($this->db->count_all_results(db_prefix() . 'proposals') > 0) {
                        $in_use = true;
                    }
                }
                if (!$in_use && $proposal_prefix !== '') {
                    $resolved_proposal_prefix = replace_dynamic_prefix($proposal_prefix);
                    $this->db->where('proposal_number_prefix', $resolved_proposal_prefix);
                    if ($this->db->count_all_results(db_prefix() . 'proposals') > 0) {
                        $in_use = true;
                    }
                }

                if ($in_use) {
                    // Do not delete, restore it and warn the user
                    $eb['deleted'] = 0;
                    $processed_branches[$eb_id] = $eb;
                    $warnings[] = 'Branch "' . $eb['branch_name'] . '" is currently in use and cannot be deleted.';
                } else {
                    // Soft delete
                    $eb['deleted'] = 1;
                    $processed_branches[$eb_id] = $eb;
                }
            }
        }

        // Save branches list in DB
        $data['branch_rows'] = json_encode(array_values($processed_branches));

        // ── Generic save loop (handles all scalar options) ────────────────────
        foreach ($data as $name => $value) {
            if (is_array($value)) {
                continue; // safety net
            }

            $existing = $this->db->get_where(db_prefix() . 'options', ['name' => $name])->row();

            if ($existing) {
                $this->db->where('name', $name);
                if ($this->db->update(db_prefix() . 'options', ['value' => $value, 'autoload' => 1])) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            } else {
                if ($this->db->insert(db_prefix() . 'options', [
                    'name'     => $name,
                    'value'    => $value,
                    'autoload' => 1,
                ])) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }

        // Add warning alerts if any deletes were blocked
        if (!empty($warnings)) {
            set_alert('warning', implode('<br>', $warnings));
        }

        if ($error_count === 0) {
            set_alert('success', 'Invoice settings updated successfully.');
        } else {
            set_alert('danger', 'Some settings could not be saved. Please try again.');
        }

        redirect(admin_url('invoice_settings'));
    }
}
