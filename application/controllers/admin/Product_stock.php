<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Product_stock extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('currencies_model');
        $this->load->model('payments_model');
        $this->load->model('invoices_model');
    }

    public function index()
    {
        if (!has_permission('product_stock', '', 'view')) {
            access_denied('product_stock');
        }
        $data = [];
        $this->load->view('admin/product_stock/manage', $data);
    }

    public function table()
    {
        if (!has_permission('product_stock', '', 'view')) {
            ajax_access_denied('product_stock');
        }
        $this->app->get_table_data('product_stock');
    }

    public function get_stock_data()
    {
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        if (!has_permission('product_stock', '', 'view')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]);
            exit;
        }

        $year = $this->input->post('year');
        $yearCondition = '';
        if (!empty($year)) {
            $yearCondition = " AND YEAR(date) = " . $year;
        }


        $purchase_query = "SELECT SUM(total_purchase) as total_purchase_qty FROM " . db_prefix() . "items";
        $purchase_result = $this->db->query($purchase_query)->row();
        $total_purchase_qty = $purchase_result->total_purchase_qty;

        $sales_query = "
                    SELECT 
                        COALESCE(SUM(ti.qty), 0) as total_sales_qty
                    FROM " . db_prefix() . "itemable ti
                    INNER JOIN " . db_prefix() . "invoices i ON ti.rel_id = i.id
                    WHERE ti.rel_type = 'invoice' AND i.status != 5 AND i.status!= 6
                    AND i.deleted_at IS NULL
                    $yearCondition
                ";

        $sales_result = $this->db->query($sales_query)->row();
        $total_sales_qty = $sales_result->total_sales_qty;

        $available_stock_qty = $total_purchase_qty - $total_sales_qty;

        $sales_percentage = 0;
        $stock_percentage = 0;

        if ($total_purchase_qty > 0) {
            $sales_percentage = round(($total_sales_qty / $total_purchase_qty) * 100, 1);
            $stock_percentage = round(($available_stock_qty / $total_purchase_qty) * 100, 1);
        }

        $response = [
            'success' => true,
            'data' => [
                'summary' => [
                    'total_purchase_qty' => floatval($total_purchase_qty),
                    'total_sales_qty' => floatval($total_sales_qty),
                    'available_stock_qty' => floatval($available_stock_qty),
                    'sales_percentage' => $sales_percentage,
                    'stock_percentage' => $stock_percentage
                ],
                'filters' => [
                    'year' => $year,
                ]
            ]
        ];


        echo json_encode($response);
        exit;
    }

    public function update_stock()
    {
        if (!$this->input->is_ajax_request() || $this->input->server('REQUEST_METHOD') !== 'POST') {
            show_404();
        }

        $response = [
            'success' => false,
            'message' => 'Invalid request'
        ];

        try {
            $item_id = (int)$this->input->post('item_id');
            $operation = $this->input->post('operation'); // 'plus' or 'minus'
            $operation_value = (int)$this->input->post('operation_value');

            if (empty($item_id) || empty($operation) || $operation_value <= 0) {
                $response['message'] = 'Invalid input parameters';
                echo json_encode($response);
                return;
            }

            if (!has_permission('product_stock', '', 'edit')) {
                $response['message'] = 'You do not have permission to update stock';
                echo json_encode($response);
                return;
            }

            $item = $this->db->where('id', $item_id)->get(db_prefix() . 'items')->row();
            if (!$item) {
                $response['message'] = 'Item not found';
                echo json_encode($response);
                return;
            }

            $current_quantity = (int)$item->total_purchase;
            $new_quantity = $current_quantity;

            if ($operation == 'plus') {
                $new_quantity += $operation_value;
            } elseif ($operation === 'minus') {
                $new_quantity -= $operation_value;
                if ($new_quantity < 0) {
                    $response['message'] = 'Cannot reduce stock below zero';
                    echo json_encode($response);
                    return;
                }
            } else {
                $response['message'] = 'Invalid operation type';
                echo json_encode($response);
                return;
            }

            $quantity_difference = $new_quantity - $current_quantity;

            // Start transaction
            $this->db->trans_start();

            $update_result = $this->db->where('id', $item_id)
                ->update(db_prefix() . 'items', [
                    'total_purchase' => $new_quantity
                ]);

            if (!$update_result) {
                $this->db->trans_rollback();
                $response['message'] = 'Failed to update stock';
                echo json_encode($response);
                return;
            }

            // Log stock activity
            $this->log_stock_activity($item_id, $current_quantity, $new_quantity, $quantity_difference);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $response['message'] = 'Transaction failed';
                echo json_encode($response);
                return;
            }

            // Success Response
            $response = [
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => [
                    'item_id' => $item_id,
                    'old_quantity' => $current_quantity,
                    'new_quantity' => $new_quantity,
                    'difference' => $quantity_difference,
                    'operation' => $operation,
                    'operation_value' => $operation_value
                ]
            ];

            // Check Low Stock Alert
            $low_stock_limit = get_option('stock_low_alert_limit') ?: 0;
            if ($new_quantity <= $low_stock_limit) {
                $response['message'] .= ' (Warning: Stock is now below the low stock limit)';
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            log_message('error', 'Stock Update Error: ' . $e->getMessage());
        }

        echo json_encode($response);
    }


    private function log_stock_activity($item_id, $old_quantity, $new_quantity, $difference)
    {
        $this->db->select('description');
        $this->db->where('id', $item_id);
        $item = $this->db->get(db_prefix() . 'items')->row();

        $log_message = sprintf(
            'Stock updated for [Product ID : ' . $item_id . ', Description - %s] from %d to %d (difference: %+d)',
            $item ? $item->description : 'Unknown Item',
            $old_quantity,
            $new_quantity,
            $difference
        );

        log_activity($log_message);
    }
}
