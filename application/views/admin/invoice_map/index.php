<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <i class="fa fa-map" aria-hidden="true"></i>
                            <?php echo _l('invoice_map_view'); ?>
                        </h4>
                    </div>
                    <div class="panel-body">

                        <!-- ── Filters ─────────────────────────────────── -->
                        <div class="row mbot10" id="inv-map-filters">

                            <div class="col-md-2">
                                <label class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                                <div class="input-group date">
                                    <input type="text" id="im-date-from" class="form-control datepicker" placeholder="From date" autocomplete="off">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                                <div class="input-group date">
                                    <input type="text" id="im-date-to" class="form-control datepicker" placeholder="To date" autocomplete="off">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="control-label">Invoice Status</label>
                                <select id="im-status" class="selectpicker form-control" multiple
                                        data-none-selected-text="All Statuses" data-width="100%">
                                    <option value="1">Unpaid</option>
                                    <option value="2">Paid</option>
                                    <option value="3">Partially Paid</option>
                                    <option value="4">Overdue</option>
                                    <option value="6">Draft</option>
                                </select>
                            </div>

                            <?php if (count($invoices_branches_gst) > 0) { ?>
                            <div class="col-md-2">
                                <label class="control-label">Branch / GST</label>
                                <select id="im-gst-numbers" class="selectpicker form-control" multiple
                                        data-none-selected-text="All Branches" data-width="100%">
                                    <?php foreach ($invoices_branches_gst as $branch) { ?>
                                    <option value="<?php echo htmlspecialchars($branch['gst_number']); ?>">
                                        <?php echo htmlspecialchars($branch['branch_name'] . ' (' . $branch['gst_number'] . ')'); ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php } ?>

                            <div class="col-md-4 mtop20">
                                <button id="im-apply-filters" class="btn btn-primary btn-sm">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <button id="im-reset-filters" class="btn btn-default btn-sm mls5">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                                <button id="im-export-csv" class="btn btn-success btn-sm mls5">
                                    <i class="fa fa-download"></i> Excel
                                </button>
                                <button id="im-export-pdf" class="btn btn-danger btn-sm mls5">
                                    <i class="fa fa-download"></i> PDF
                                </button>
                            </div>
                        </div>

                        <!-- ── Breadcrumb & Back ───────────────────────── -->
                        <div class="row mbot5" id="im-breadcrumb-row" style="display:none;">
                            <div class="col-md-12">
                                <button class="btn btn-xs btn-default" id="im-back-btn" onclick="InvoiceMap.goBack()">
                                    <i class="fa fa-arrow-left"></i> Back
                                </button>
                                &nbsp;
                                <ol class="breadcrumb inline-block" id="im-breadcrumb" style="background:none;padding:0;margin:0;display:inline-flex;"></ol>
                            </div>
                        </div>

                        <!-- ── Map Container ──────────────────────────── -->
                        <div class="row">
                            <div class="col-md-12">
                                <div id="im-map-wrapper" style="position:relative;">

                                    <!-- Loading overlay -->
                                    <div id="im-loader" style="
                                        display:none; position:absolute; top:0; left:0;
                                        width:100%; height:100%; z-index:9999;
                                        background:rgba(255,255,255,0.82);
                                        align-items:center; justify-content:center;
                                        flex-direction:column; border-radius:4px;">
                                        <div class="dt-loader"><span></span></div>
                                        <p id="im-loader-text" class="text-muted mtop10" style="font-size:13px;">Loading map…</p>
                                    </div>

                                    <!-- ECharts canvas -->
                                    <div id="im-chart"
                                         style="width:100%; height:72vh; min-height:480px;">
                                    </div>

                                    <!-- No-data overlay -->
                                    <div id="im-no-data" style="display:none; text-align:center; padding:80px 0;">
                                        <i class="fa fa-map-o fa-3x text-muted"></i>
                                        <p class="text-muted mtop10">No invoice data found for this region.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /panel-body -->
                </div><!-- /panel_s -->
            </div>
        </div>
    </div>
</div>

<!-- ── City Detail Modal ───────────────────────────────────────────────────── -->
<div class="modal fade" id="im-city-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <button type="button" id="im-export-city-pdf" class="btn btn-danger btn-xs pull-right" style="margin-right: 10px; margin-top: 2px;">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
                <button type="button" id="im-export-city-csv" class="btn btn-success btn-xs pull-right" style="margin-right: 15px; margin-top: 2px;">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-building-o"></i>
                    <span id="im-city-modal-title">City Invoices</span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Summary bar -->
                <div class="row mbot10">
                    <div class="col-md-4">
                        <div class="well well-sm text-center" style="margin:0;">
                            <strong id="im-city-count">—</strong>
                            <div class="text-muted" style="font-size:11px;">Total Invoices</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well well-sm text-center" style="margin:0;">
                            <strong id="im-city-amount">—</strong>
                            <div class="text-muted" style="font-size:11px;">Total Amount</div>
                        </div>
                    </div>
                </div>

                <!-- Invoice table -->
                <div id="im-city-loader" style="text-align:center;padding:30px;">
                    <div class="dt-loader"><span></span></div>
                </div>
                <div id="im-city-table-wrap" style="display:none;">
                    <table class="table table-striped table-hover" id="im-city-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="im-city-tbody"></tbody>
                    </table>
                    <div id="im-city-load-more" style="text-align:center;display:none;">
                        <button class="btn btn-default btn-sm" onclick="InvoiceMap.loadMoreCityInvoices()">
                            Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<!-- ECharts (loaded from CDN) -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<?php $this->load->view('admin/invoice_map/includes/map_js'); ?>

</body>
</html>
