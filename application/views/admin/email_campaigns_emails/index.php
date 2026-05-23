<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<?php if (has_permission('email_campaigns', '', 'create')) { ?>
							<div class="_buttons">
								<a href="javascript:;" onclick="openQuestionModal()" class="btn btn-info pull-left display-block">Create New Custom Email</a>
							</div>
						<?php } ?>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<p class="text-warning mtop5">
						<h4 class="no-margin">Custom Emails</h4>
						</p>
						<div class="clearfix"></div>
						<?php render_datatable(array(
							_l('Sr. No.'),
							_l('Email'),
							_l('Service'),
						), 'email-campaigns-emails'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="custom_email_model" tabindex="-1" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<?php echo form_open(admin_url('email_campaigns_emails/save'), array("name" => "customEmailForm", "id" => "customEmailForm")); ?>
		<input type="hidden" id="id" name="id" value="" form="customEmailForm" />
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div id="additional"></div>
						<?php echo render_input('email', 'Email', "", "text", array("form" => "customEmailForm")); ?>
						<?php echo render_input('password', 'Password', "", "text", array("form" => "customEmailForm")); ?>
						<div class="select-placeholder form-group">
							<label for="service_id">Mail Service</label>
							<select name="service_id" id="service_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-hide-disabled="true" form="customEmailForm" required>
								<option value="">Select Service</option>
								<?php
								foreach ($mail_services as $key => $item) {
								?>
									<option value="<?= $item['id'] ?>"><?= $item['service_name'] ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info" form="customEmailForm"><?php echo _l('submit'); ?></button>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function() {
		$(window).off('beforeunload');

		initDataTable('.table-email-campaigns-emails', window.location.href, [0], [0]);

		$('select').on('change', function() {
            $(this).closest('.form-group').find('p.text-danger').remove();
            $(this).closest('.form-group').removeClass('has-error');
        });

		appValidateForm($('#customEmailForm'), {
			email: 'required',
			password: 'required',
			service_id: 'required',
		});
	});

	function openQuestionModal(id = "") {
		var parent = $('#custom_email_model');
		if (id == "") {
			parent.find('#id').val("");
			parent.find('#email').val("");
			parent.find('#password').val("");
			parent.find('#service_id').val("");
			parent.find('#service_id').trigger('change');
			parent.find('#service_id').selectpicker('refresh');
			parent.find('.modal-title').html("Add New Custom Email");
			parent.modal('show');
		} else {
			$.ajax({
				url: "<?php echo admin_url('email_campaigns_emails/get_data'); ?>",
				method: "POST",
				data: {
					id: id
				},
				dataType: 'json'
			}).done(function(result) {
				if (result.success) {
					parent.find('#id').val(result.data.id);
					parent.find('#email').val(result.data.email);
					parent.find('#password').val(result.data.password);
					parent.find('#service_id').val(result.data.service_id);
					parent.find('#service_id').trigger('change');
					parent.find('#service_id').selectpicker('refresh');
					parent.find('.modal-title').html("Edit Custom Email");
					parent.modal('show');
				}
			});
		}

	}
</script>
</body>

</html>