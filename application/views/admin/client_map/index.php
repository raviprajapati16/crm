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
                            Customer Map View
                        </h4>
                    </div>
                    <div class="panel-body">

                        <!-- ── Filters ─────────────────────────────────── -->
                        <div class="row mbot10" id="cm-map-filters">
                            
                            <div class="col-md-3">
                                <label class="control-label">Customer Status</label>
                                <div class="checkbox checkbox-primary" style="margin-top: 5px;">
                                    <input type="checkbox" id="cm-exclude-inactive" name="exclude_inactive" value="1">
                                    <label for="cm-exclude-inactive">Exclude Inactive Customers</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="control-label">Customer Groups</label>
                                <select id="cm-groups" class="selectpicker form-control" multiple
                                        data-none-selected-text="All Groups" data-width="100%">
                                    <?php foreach($groups as $group){ ?>
                                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mtop20">
                                <button id="cm-apply-filters" class="btn btn-primary btn-sm">
                                    <i class="fa fa-filter"></i> Apply Filters
                                </button>
                                <button id="cm-reset-filters" class="btn btn-default btn-sm mls5">
                                    <i class="fa fa-refresh"></i> Reset
                                </button>
                                <button id="cm-export-csv" class="btn btn-success btn-sm mls5">
                                    <i class="fa fa-download"></i> Excel
                                </button>
                                <button id="cm-export-pdf" class="btn btn-danger btn-sm mls5">
                                    <i class="fa fa-download"></i> PDF
                                </button>
                            </div>
                        </div>

                        <!-- ── Breadcrumb & Back ───────────────────────── -->
                        <div class="row mbot5" id="cm-breadcrumb-row" style="display:none;">
                            <div class="col-md-12">
                                <button class="btn btn-xs btn-default" id="cm-back-btn" onclick="ClientMap.goBack()">
                                    <i class="fa fa-arrow-left"></i> Back
                                </button>
                                &nbsp;
                                <ol class="breadcrumb inline-block" id="cm-breadcrumb" style="background:none;padding:0;margin:0;display:inline-flex;"></ol>
                            </div>
                        </div>

                        <!-- ── Map Container ──────────────────────────── -->
                        <div class="row">
                            <div class="col-md-12">
                                <div id="cm-map-wrapper" style="position:relative;">

                                    <!-- Loading overlay -->
                                    <div id="cm-loader" style="
                                        display:none; position:absolute; top:0; left:0;
                                        width:100%; height:100%; z-index:9999;
                                        background:rgba(255,255,255,0.82);
                                        align-items:center; justify-content:center;
                                        flex-direction:column; border-radius:4px;">
                                        <div class="dt-loader"><span></span></div>
                                        <p id="cm-loader-text" class="text-muted mtop10" style="font-size:13px;">Loading map…</p>
                                    </div>

                                    <!-- ECharts canvas -->
                                    <div id="cm-chart"
                                         style="width:100%; height:72vh; min-height:480px;">
                                    </div>

                                    <!-- No-data overlay -->
                                    <div id="cm-no-data" style="display:none; text-align:center; padding:80px 0;">
                                        <i class="fa fa-map-o fa-3x text-muted"></i>
                                        <p class="text-muted mtop10">No customer data found for this region.</p>
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
<div class="modal fade" id="cm-city-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <button type="button" id="cm-export-city-pdf" class="btn btn-danger btn-xs pull-right" style="margin-right: 15px; margin-top: 2px;">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
                <button type="button" id="cm-export-city-csv" class="btn btn-success btn-xs pull-right" style="margin-right: 5px; margin-top: 2px;">
                    <i class="fa fa-file-excel-o"></i> Export CSV
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-building-o"></i>
                    <span id="cm-city-modal-title">City Customers</span>
                </h4>
            </div>
            <div class="modal-body">
                <!-- Summary bar -->
                <div class="row mbot10">
                    <div class="col-md-12 text-center">
                        <div class="well well-sm" style="display: inline-block; padding: 10px 30px;">
                            <strong id="cm-city-count" style="font-size: 18px;">—</strong>
                            <div class="text-muted" style="font-size:12px;">Total Customers</div>
                        </div>
                    </div>
                </div>

                <!-- Client table -->
                <div id="cm-city-loader" style="text-align:center;padding:30px;">
                    <div class="dt-loader"><span></span></div>
                </div>
                <div id="cm-city-table-wrap" style="display:none; overflow-x: auto;">
                    <table class="table table-striped table-hover" id="cm-city-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company</th>
                                <th>Primary Phone</th>
                                <th>Groups</th>
                                <th>Date Created</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cm-city-tbody"></tbody>
                    </table>
                    <div id="cm-city-load-more" style="text-align:center;display:none; margin-top: 15px;">
                        <button class="btn btn-default btn-sm" onclick="ClientMap.loadMoreCityClients()">
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

<?php $this->load->view('admin/client_map/includes/map_js'); ?>

</body>
</html>
