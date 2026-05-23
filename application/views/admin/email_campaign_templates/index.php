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
								<a href="javascript:;" onclick="openTemplateModel()" class="btn btn-info pull-left display-block">Create New Template</a>
							</div>
						<?php  } ?>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<p class="text-warning mtop5">
						<h4 class="no-margin"><?php echo _l('email_campaigns_templates'); ?></h4>
						</p>
						<div class="clearfix"></div>
						<?php render_datatable(array(
							_l('Sr. No.'),
							_l('Title'),
							_l('subject'),
							_l('Created By'),
							_l('Created At'),
						), 'email-campaign-templates'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="template_modal" tabindex="-1" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<?php echo form_open_multipart(admin_url('email_campaign_templates/template/' . $template->id), array('id' => 'templateForm')); ?>
		<input type="hidden" id="id" name="id" value="" form="templateForm" />
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<?= all_type_input_render([
							"label" => "title",
							"id" => "title",
							"name" => "title",
							"type" => "text",
							"is_required" => true,
							"selected_value" => (isset($template->title)) ? $template->title : '',
							"form" => 'templateForm'
						], 'col-md-12', true);
						?>

						<?= all_type_input_render([
							"label" => "Subject",
							"id" => "subject",
							"name" => "subject",
							"type" => "text",
							"is_required" => true,
							"selected_value" => (isset($template->subject)) ? $template->subject : '',
							"form" => 'templateForm'
						], 'col-md-12', true);
						?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info" form="templateForm"><?php echo _l('submit'); ?></button>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<div class="modal fade" id="template_preview_modal" tabindex="-1" role="dialog" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body html-section">

			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function() {
		initDataTable('.table-email-campaign-templates', window.location.href, [0], [0]);

		appValidateForm($('#templateForm'), {
			title: 'required',
			subject: 'required',
		});
	});

	function openTemplateModel(id = "") {
		var parent = $('#template_modal');
		if (id == "") {
			parent.find('#id').val("");
			parent.find('#fieldtitle').val("");
			parent.find('#fieldsubject').val("");
			parent.find('.modal-title').html("Add Template");
			parent.modal('show');
		} else {
			$.ajax({
				url: "<?php echo admin_url('email_campaign_templates/get_template'); ?>",
				method: "POST",
				data: {
					id: id
				},
				dataType: 'json'
			}).done(function(result) {
				if (result.success) {
					parent.find('#id').val(result.data.id);
					parent.find('#fieldsubject').val(result.data.subject);
					parent.find('#fieldtitle').val(result.data.title);
					parent.find('.modal-title').html("Edit Template");
					parent.modal('show');
				}
			});
		}

	}

	function openPreviewModal(id = "") {
		var parent = $('#template_preview_modal');
		var htmlSection = parent.find('.html-section');
		parent.find('.modal-title').html("Template Preview");
		$.ajax({
			url: "<?php echo admin_url('email_campaign_templates/template_preview'); ?>",
			method: "POST",
			data: {
				id: id
			},
			dataType: 'json'
		}).done(function(result) {
			if (result.success && result.template_url) {
				var iframe = $('<iframe>', {
					width: '100%',
					height: '500px',
					frameborder: '0',
					allowfullscreen: true,
					src: result.template_url
				});
				htmlSection.empty().append(iframe);
				$('#template_preview_modal').modal('show');
			}
		});
	}
</script>
</body>

</html>