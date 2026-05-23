<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
	.select-container {
		display: inline-block;
	}

	.select-container select {
		width: 11.5em;
	}

	.header-filter-section {
		display: flex;
		gap: 10px;
	}

	.select-container {
		display: inline-block;
	}

	.select-container select {
		width: 11em;
	}


	.header-filter-section {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		justify-content: space-between;
	}

	.select-container {
		flex: 1 1 auto;
		/* Allows elements to shrink and grow as needed */
		min-width: 150px;
		/* Sets a minimum width for each dropdown */
	}

	.dataTables_filter {
		display: flex;
		align-items: center;
		justify-content: flex-end;
	}

	.dt-info-text {
		margin-right: 10px;
		white-space: nowrap;
		font-size: 15px;
	}


	/* Add these styles to your stylesheet */
	.stats-header {
		display: flex;
		align-items: center;
		margin-bottom: 20px;
	}

	.mtop20 {
		margin-top: 20px;
	}

	.mtop30 {
		margin-top: 30px;
	}

	.mright10 {
		margin-right: 10px;
	}

	.stat-box {
		border-radius: 4px;
		padding: 20px;
		position: relative;
		margin-bottom: 20px;
		min-height: 120px;
		display: flex;
		align-items: center;
		color: #fff;
	}

	.stat-icon {
		padding-right: 15px;
		border-right: 1px solid rgba(255, 255, 255, 0.2);
		margin-right: 15px;
	}

	.stat-content {
		flex-grow: 1;
	}

	.stat-number {
		font-size: 24px;
		font-weight: 600;
		margin: 0;
		color: #fff;
	}

	.stat-text {
		margin: 5px 0;
		opacity: 0.9;
		font-size: 16px;
		color: #fff;
	}

	.stat-percentage {
		position: absolute;
		top: 6px;
		right: 6px;
		font-size: 14px;
		color: #fff;
		font-weight: 500;
	}

	.bg-primary {
		background-color: #337ab7;
	}

	.bg-success {
		background-color: #5cb85c;
	}

	.bg-info {
		background-color: #5bc0de;
	}

	.bg-warning {
		background-color: #f0ad4e;
	}

	.bg-danger {
		background-color: #d9534f;
	}

	/* Bootstrap 3.4.0 specific overrides */
	.panel-default {
		border-color: #ddd;
	}

	.panel-default>.panel-heading {
		background-color: #f5f5f5;
		border-color: #ddd;
	}

	.btn-group>.btn-sm {
		padding: 5px 10px;
		font-size: 12px;
		line-height: 1.5;
		border-radius: 3px;
	}
</style>
<div id="wrapper">
	<div class="content">
		<div class="row mtop30">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="stats-header">
							<h4 class="no-margin">All Campaigns Analytics</h4>
						</div>
						<hr class="hr-panel-heading" />
						<!-- Main Stats Cards -->
						<div class="row">
							<div class="col-md-2">
								<div class="stat-box bg-primary">
									<div class="stat-icon">
										<i class="fa fa-th-list fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['Total'] ?></h3>
										<p class="stat-text">Total Campaigns</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-warning">
									<div class="stat-icon">
										<i class="fa fa-hourglass-start fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['In Queue'] +  $campaign_stats['Scheduled']  ?></h3>
										<p class="stat-text">In Queue / Scheduled</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-success">
									<div class="stat-icon">
										<i class="fa fa-paper-plane fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['In Progress'] ?></h3>
										<p class="stat-text">In Progress</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-danger">
									<div class="stat-icon">
										<i class="fa fa-times-circle fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['Stopped'] ?></h3>
										<p class="stat-text">Stopped</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-success">
									<div class="stat-icon">
										<i class="fa fa-check-circle fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['Completed'] ?></h3>
										<p class="stat-text">Completed</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-danger">
									<div class="stat-icon">
										<i class="fa fa-pause fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $campaign_stats['Paused'] ?></h3>
										<p class="stat-text">Paused</p>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-2">
								<div class="stat-box bg-primary">
									<div class="stat-icon">
										<i class="fa fa-envelope fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['total_emails'] ?></h3>
										<p class="stat-text">Total Emails</p>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-warning">
									<div class="stat-icon">
										<i class="fa fa-hourglass-start fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['queue_count'] ?></h3>
										<p class="stat-text">In Queue</p>
										<div class="stat-percentage">
											<span><?= $stats['queue_percentage'] ?>%</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-success">
									<div class="stat-icon">
										<i class="fa fa-paper-plane fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['sent_count'] ?></h3>
										<p class="stat-text">Emails Sent</p>
										<div class="stat-percentage">
											<span><?= $stats['sent_percentage'] ?>%</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-danger">
									<div class="stat-icon">
										<i class="fa fa-times-circle fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['failed_count'] ?></h3>
										<p class="stat-text">Failed To Send</p>
										<div class="stat-percentage">
											<span><?= $stats['failed_percentage'] ?>%</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-success">
									<div class="stat-icon">
										<i class="fa fa-envelope-open fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['opened_count'] ?></h3>
										<p class="stat-text">Opened Emails</p>
										<div class="stat-percentage">
											<span><?= $stats['opened_percentage'] ?>%</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-2">
								<div class="stat-box bg-danger">
									<div class="stat-icon">
										<i class="fa fa-envelope fa-2x"></i>
									</div>
									<div class="stat-content">
										<h3 class="stat-number"><?= $stats['not_opened_count'] ?></h3>
										<p class="stat-text">Not Opened</p>
										<div class="stat-percentage">
											<span><?= $stats['not_opened_percentage'] ?>%</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<div class="_buttons">
							<a href="<?= admin_url('email_campaigns/create') ?>" class="btn btn-info pull-left display-block">Create New Campaign</a>
						</div>
						<div class="pull-right d-flex header-filter-section select-container">
							<select id="status-filter" class="form-control">
								<option value="" selected>Select Status</option>
								<option value="Completed">Completed</option>
								<option value="Paused">Paused</option>
								<option value="In Progress">In Progress</option>
								<option value="Scheduled">Scheduled</option>
								<option value="Error">Error</option>
								<option value="Stopped">Stopped</option>
							</select>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<p class="text-warning mtop5">
						<h4 class="no-margin"><?php echo _l('email_campaigns'); ?></h4>
						</p>
						<div class="clearfix"></div>
						<?php render_datatable(array(
							_l('Sr. No.'),
							_l('Campaign Title'),
							_l('Scheduled Date'),
							_l('Status'),
							_l('Days'),
							_l('Created By'),
							_l('Created At'),
							_l('Action'),
						), 'email-campaigns'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	var table;
	$(function() {
		var fnServerParams = {
			"status": '#status-filter',
		}
		table = initDataTable('.table-email-campaigns', window.location.href, [0], [0], fnServerParams,[0, 'desc']);
		$('#status-filter').on('change', function() {
			if (table) {
				table.draw();
			}
		});
	});

	function campaignStatusUpdate(id, status) {
		$.ajax({
			url: "<?php echo admin_url('email_campaigns/status_update') ?>",
			method: "POST",
			data: {
				id: id,
				status: status,
			},
			dataType: 'json'
		}).done(function(result) {
			if (result.success) {
				alert_float('success', result.message);
				if (table) {
					table.draw();
				}
			} else {
				alert_float('danger', result.message);
			}
		});
	}
</script>
</body>

</html>