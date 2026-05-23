<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
	<div class="col-md-12">
		<?php $company_logo = get_option('company_logo'); ?>
		<?php $company_logo_dark = get_option('company_logo_dark'); ?>
		<?php $company_logo_white = get_option('company_logo_white'); ?>
		<?php $company_brochure = get_option('company_brochure'); ?>
		<?php if ($company_logo != '') { ?>
			<div class="row">
				<div class="col-md-9">
					<h5>Company Logo</h5>
				</div>
				<div class="col-md-9">
					<img src="<?php echo base_url('uploads/company/' . $company_logo); ?>" class="img img-responsive">
				</div>
				<?php if (has_permission('settings', '', 'delete')) { ?>
					<div class="col-md-3 text-right">
						<a href="<?php echo admin_url('settings/remove_company_logo'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_logo" class="control-label"><?php echo _l('settings_general_company_logo'); ?></label>
				<input type="file" name="company_logo" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>" accept=".jpeg,.jpg,.png">
			</div>
		<?php } ?>
		<hr />
		<?php if ($company_logo_dark != '') { ?>
			<div class="row">
				<div class="col-md-9">
					<h5>Company Dark Logo</h5>
				</div>
				<div class="col-md-9">
					<a><img src="<?php echo base_url('uploads/company/' . $company_logo_dark); ?>" class="img img-responsive">
				</div>
				<?php if (has_permission('settings', '', 'delete')) { ?>
					<div class="col-md-3 text-right">
						<a href="<?php echo admin_url('settings/remove_company_logo/dark'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_logo_dark" class="control-label"><?php echo _l('company_logo_dark'); ?></label>
				<input type="file" name="company_logo_dark" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>" accept=".jpeg,.jpg,.png">
			</div>
		<?php } ?>
		<hr />
		<?php if ($company_logo_white != '') { ?>
			<div class="row">
				<div class="col-md-9">
					<h5>Company White Logo</h5>
				</div>
				<div class="col-md-9" style="background-color: black; padding : 10px;">
					<img src="<?php echo base_url('uploads/company/' . $company_logo_white); ?>" class="img img-responsive">
				</div>
				<?php if (has_permission('settings', '', 'delete')) { ?>
					<div class="col-md-3 text-right">
						<a href="<?php echo admin_url('settings/remove_company_logo/white'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_logo_white" class="control-label">Company Logo White</label>
				<input type="file" name="company_logo_white" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>" accept=".jpeg,.jpg,.png">
			</div>
		<?php } ?>
		<?php $favicon = get_option('favicon'); ?>
		<?php if ($favicon != '') { ?>
			<div class="form-group favicon">
				<div class="row">
					<div class="col-md-9">
						<h5>Company Favicon</h5>
					</div>
					<div class="col-md-9">
						<img src="<?php echo base_url('uploads/company/' . $favicon); ?>" class="img img-responsive">
					</div>
					<?php if (has_permission('settings', '', 'delete')) { ?>
						<div class="col-md-3 text-right">
							<a href="<?php echo admin_url('settings/remove_favicon'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
						</div>
					<?php } ?>
				</div>
				<div class="clearfix"></div>
			</div>
		<?php } else { ?>
			<div class="form-group favicon_upload">
				<label for="favicon" class="control-label"><?php echo _l('settings_general_favicon'); ?></label>
				<input type="file" name="favicon" class="form-control" accept=".jpeg,.jpg,.png">
			</div>
		<?php } ?>
		<hr />
		<?php if ($company_brochure != '') {
			$protected_path = protected_file_url_by_path(get_upload_path_by_type('company') . $company_brochure);
			$company_brochure_url = site_url('download/file_download?path=' . $protected_path);
		?>
			<div class="row">
				<div class="col-md-9">
					<h5>Company Brochure</h5>
				</div>
				<div class="col-md-9" style="padding : 10px;">
					<a href="<?= $company_brochure_url; ?>" target="_blank"><img width="250px" src="<?php echo base_url('assets/images/brochure-download.png'); ?>" class="img img-responsive"></a>
				</div>
				<?php if (has_permission('settings', '', 'delete')) { ?>
					<div class="col-md-3 text-right">
						<a href="<?php echo admin_url('settings/remove_company_brochure'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_brochure" class="control-label">Company Brochure</label>
				<input type="file" name="company_brochure" class="form-control" value="" data-toggle="tooltip" title="Company Brochure Upload" accept=".pdf">
			</div>
		<?php } ?>
		<hr />
		<?php $attrs = (get_option('companyname') != '' ? array() : array('autofocus' => true)); ?>
		<?php echo render_input('settings[companyname]', 'settings_general_company_name', get_option('companyname'), 'text', $attrs); ?>
		<hr />
		<?php echo render_input('settings[brandname]', 'Brand Name', get_option('brandname'), 'text', $attrs); ?>
		<hr />
		<?php echo render_input('settings[main_domain]', 'settings_general_company_main_domain', get_option('main_domain')); ?>
		<hr />
		<?php render_yes_no_option('rtl_support_admin', 'settings_rtl_support_admin'); ?>
		<hr />
		<?php render_yes_no_option('rtl_support_client', 'settings_rtl_support_client'); ?>
		<hr />
		<?php echo render_input('settings[allowed_files]', 'settings_allowed_upload_file_types', get_option('allowed_files')); ?>
		<hr />
		<?php echo render_input('settings[auto_logout_minutes]', '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="Blank or 0 will be consider as Auto Logout OFF" data-original-title="" title=""></i> Auto Logout (In Minutes)', get_option('auto_logout_minutes'), 'number') ?>
	</div>
</div>