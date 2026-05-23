<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>
<style>
    .huge {
        font-size: 40px;
        font-weight: bold;
    }

    .summary-card {
        height: 150px;
    }

    .summary-card .panel-body {
        height: 100%;
        align-items: center;
    }

    .panel-info {
        border-color: #5bc0de;
    }

    .panel-success {
        border-color: #5cb85c;
    }

    .panel-warning {
        border-color: #f0ad4e;
    }

    .panel-info>.panel-body {
        background-color: #f8f9fa;
    }

    .panel-success>.panel-body {
        background-color: #f8f9fa;
    }

    .panel-warning>.panel-body {
        background-color: #f8f9fa;
    }

    .text-info {
        color: #5bc0de !important;
    }

    .text-success {
        color: #5cb85c !important;
    }

    .text-warning {
        color: #f0ad4e !important;
    }

    .text-muted {
        color: #777 !important;
        font-size: 14px;
    }

    #stock_update_modal .input-group-btn .btn {
        border-radius: 0;
        border-color: #ccc;
        min-width: 80px;
    }

    #stock_update_modal .btn-group-justified .btn {
        min-width: 120px;
        font-weight: 500;
    }

    #stock_update_modal #new_stock_quantity {
        font-size: 16px;
        font-weight: 500;
        text-align: left;
    }

    #stock_update_modal .form-control-static {
        font-size: 16px;
        color: #2c3e50;
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #e3e6f0;
    }

    #stock_update_modal .alert-info {
        border-left: 4px solid #17a2b8;
    }

    /* Active operation button styles */
    #stock_update_modal .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    #stock_update_modal .btn-default {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Stock Management</h4>
                                <hr>
                            </div>
                            <div class="col-md-3">
                                <label for="year_filter">Select Year:</label>
                                <select id="year_filter" class="form-control">
                                    <option value="">All Years</option>
                                    <?php
                                    $current_year = date('Y');
                                    for ($year = $current_year; $year >= 2025; $year--) {
                                        $selected = ($year == $current_year) ? 'selected' : '';
                                        echo "<option value='{$year}' {$selected}>{$year}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4><?php echo _l('Summary'); ?></h4>
                                <br>
                            </div>
                        </div>
                        <div class="row" id="summary-cards">
                            <!-- Total Purchase Card -->
                            <div class="col-md-4 col-sm-6">
                                <div class="panel panel-info summary-card">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-shopping-cart fa-3x text-info"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="huge" id="total-purchase">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="text-muted">Total Purchase</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Sales Card -->
                            <div class="col-md-4 col-sm-6">
                                <div class="panel panel-success summary-card">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-line-chart fa-3x text-success"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="huge" id="total-sales">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="text-muted">Total Sales</div>
                                                <div class="text-success" id="sales-percentage">
                                                    <span>0%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Stock Card -->
                            <div class="col-md-4 col-sm-6">
                                <div class="panel panel-warning summary-card">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-xs-3">
                                                <i class="fa fa-cubes fa-3x text-warning"></i>
                                            </div>
                                            <div class="col-xs-9 text-right">
                                                <div class="huge" id="available-stock">
                                                    <i class="fa fa-spinner fa-spin"></i>
                                                </div>
                                                <div class="text-muted">Available Stock</div>
                                                <div class="text-warning" id="stock-percentage">
                                                    <span>0%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Product Stock</h4>
                                <br>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                render_datatable(array(
                                    '#',
                                    "Product Name",
                                    "Main Group",
                                    "Sub Group",
                                    "Total Stock",
                                    "Available Stock",
                                    "Total Sales",
                                ), 'product-stcok');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="stock_update_modal" tabindex="-1" role="dialog" aria-labelledby="stockUpdateModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="stock_update_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="stockUpdateModalLabel">
                        <i class="fa fa-edit"></i> Update Stock Quantity
                    </h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="stock_item_id" name="item_id">
                    <input type="hidden" id="stock_quantity" name="current_quantity">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Available Stock:</label>
                                <p class="form-control-static">
                                    <strong id="current_stock_display">0</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Select Operation:</label>
                                <div class="btn-group btn-group-justified" role="group">
                                    <div class="btn-group" role="group">
                                        <button type="button" id="stock_plus_btn" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" id="stock_minus_btn" class="btn btn-default">
                                            <i class="fa fa-minus"></i> Subtract
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="new_stock_quantity">Quantity:</label>
                                <input type="number"
                                    id="new_stock_quantity"
                                    name="operation_value"
                                    class="form-control text-center"
                                    value="0"
                                    min="0"
                                    placeholder="Enter quantity"
                                    required>
                                <small class="text-muted">Enter the quantity to add or subtract</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Note:</strong>
                                <span id="operation-preview">
                                    Select Add or Subtract operation and enter a value to update stock.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var table;
    $(document).ready(function() {
        loadStockData();

        var serverParams = {};
        serverParams['year'] = $('#year_filter');
        table = initDataTable('.table-product-stcok', admin_url + 'product_stock/table', ['undefined'], [0, 1, 2, 3, 4, 5], serverParams, []);

        $('#year_filter').change(function() {
            loadStockData();
            table.draw();
        });
    });

    function loadStockData() {
        var year = $('#year_filter').val();

        $('#total-purchase').html('<i class="fa fa-spinner fa-spin"></i>');
        $('#total-sales').html('<i class="fa fa-spinner fa-spin"></i>');
        $('#available-stock').html('<i class="fa fa-spinner fa-spin"></i>');
        $('#sales-percentage span').text('0%');
        $('#stock-percentage span').text('0%');

        $.ajax({
            url: '<?php echo admin_url("product_stock/get_stock_data"); ?>',
            type: 'POST',
            data: {
                year: year
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data.summary;

                    $('#total-purchase').text(data.total_purchase_qty);
                    $('#total-sales').text(data.total_sales_qty);
                    $('#available-stock').text(data.available_stock_qty);

                    $('#sales-percentage span').text(data.sales_percentage + '%');
                    $('#stock-percentage span').text(data.stock_percentage + '%');
                } else {
                    alert('Error loading data: ' + response.message);
                }
            },
            error: function() {
                alert_float('danger', 'Error : Something went wrong');
                $('#total-purchase').text('0');
                $('#total-sales').text('0');
                $('#available-stock').text('0');
                $('#sales-percentage span').text('0%');
                $('#stock-percentage span').text('0%');
            }
        });
    }

    function stock_edit(item_id) {
        var currentStock = parseInt($('[data-avl-stock]').filter(function() {
            return $(this).closest('tr').find('[onclick*="stock_edit(' + item_id + ')"]').length > 0;
        }).attr('data-avl-stock')) || 0;

        $('#stock_item_id').val(item_id);
        $('#current_stock_display').text(currentStock);
        $('#stock_quantity').val(currentStock);
        $('#new_stock_quantity').val(0);

        $('#stock_plus_btn').addClass('btn-primary').removeClass('btn-default');
        $('#stock_minus_btn').addClass('btn-default').removeClass('btn-primary');
        $('#stock_plus_btn').data('operation', 'plus');
        $('#stock_minus_btn').data('operation', 'minus');

        $('#stock_update_form').data('operation', 'plus');

        $('#stock_update_modal').modal('show');
    }

    $(document).ready(function() {

        $(document).on('click', '#stock_plus_btn', function() {
            $('#stock_plus_btn').addClass('btn-primary').removeClass('btn-default');
            $('#stock_minus_btn').addClass('btn-default').removeClass('btn-primary');
            $('#stock_update_form').data('operation', 'plus');
        });

        $(document).on('click', '#stock_minus_btn', function() {
            $('#stock_minus_btn').addClass('btn-primary').removeClass('btn-default');
            $('#stock_plus_btn').addClass('btn-default').removeClass('btn-primary');
            $('#stock_update_form').data('operation', 'minus');
        });

        $(document).on('submit', '#stock_update_form', function(e) {
            e.preventDefault();

            var itemId = $('#stock_item_id').val();
            var operationValue = $('#new_stock_quantity').val();
            var operation = $(this).data('operation');

            if (operationValue == null || operationValue == "") {
                alert_float('danger', 'Stock quantity cannot be empty');
                return;
            }

            if (parseInt(operationValue) <= 0) {
                alert_float('danger', 'Stock quantity cannot be zero or negative');
                return;
            }

            var submitBtn = $(this).find('button[type="submit"]');
            var originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: admin_url + 'product_stock/update_stock',
                type: 'POST',
                data: {
                    item_id: itemId,
                    operation: operation,
                    operation_value: operationValue
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message || 'Stock updated successfully');
                        $('#stock_update_modal').modal('hide');
                        table.draw();
                        loadStockData();
                    } else {
                        alert_float('danger', response.message || 'Error updating stock');
                    }
                },
                error: function() {
                    alert_float('danger', 'Error: Something went wrong');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
</script>
</body>

</html>