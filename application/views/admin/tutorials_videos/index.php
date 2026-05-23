<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<?php if (has_permission('tutorials_videos', '', 'create')) { ?>
							<div class="_buttons">
								<a href="javascript:;" onclick="openTutorialModal()"
									class="btn btn-info pull-left display-block">Add New Tutorial</a>
							</div>
						<?php } ?>
						<div class="clearfix"></div><br>
						<?php render_datatable(array(
							_l('Sr. No.'),
							_l('Title'),
							_l('Description'),
							_l('Action'),
						), 'tutorials-videos'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="tutorial_modal" tabindex="-1" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<?php echo form_open_multipart(admin_url('tutorials_videos/save'), array("id" => "tutorialForm")); ?>
		<input type="hidden" id="tutorial_id" name="id" form="tutorialForm" value="" />
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add New Videos</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="title">Title</label>
							<input type="text" name="title" id="title" class="form-control" form="tutorialForm"
								required />
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label for="description">Description</label>
							<textarea name="description" id="description" class="form-control" form="tutorialForm"
								rows="4"></textarea>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<label for="link">Tutorial Link</label>
							<input type="url" name="link" id="link" class="form-control" form="tutorialForm" required />
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
				<button type="submit" class="btn btn-info" form="tutorialForm"><?php echo _l('submit'); ?></button>
			</div>
		</div>
		<?php echo form_close(); ?>
	</div>
</div>
<?php init_tail(); ?>
<script>
	var table;
	$(function () {
// Default sort Title A to Z
table = initDataTable('.table-tutorials-videos', window.location.href, [],[],[], [1, 'asc']);


		$('#tutorialForm').appFormValidator({
			rules: {
				title: 'required',
				link: 'required',
			},
			errorPlacement: function (error, element) {
				var formGroup = $(element).closest('.form-group');
				formGroup.append(error);
			},
			submitHandler: function (form) {
				var $submitBtn = $(this).find('button[type="submit"]');
				$submitBtn.prop('disabled', true);
				var originalText = $submitBtn.html();
				$submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
				form.submit();
			}
		});
	});

	function openTutorialModal(id = "") {
		var parent = $('#tutorial_modal');
		if (id == "") {
			parent.find('#title').val("");
			parent.find('#description').val("");
			parent.find('#link').val("");
			parent.find('#tutorial_id').val("");
			parent.find('.modal-title').html("Add New Tutorial");
			parent.modal('show');
		} else {
			parent.find('.file-preview-section').empty();
			$.ajax({
				url: "<?php echo admin_url('tutorials_videos/get_data'); ?>",
				method: "POST",
				data: {
					id: id
				},
				dataType: 'json'
			}).done(function (result) {
				if (result.success) {
					parent.find('#title').val(result.data.title);
					parent.find('#description').val(result.data.description);
					parent.find('#link').val(result.data.link);
					parent.find('#tutorial_id').val(result.data.id);
					parent.find('.modal-title').html("Edit Tutorial");
					parent.modal('show');
				}
			});
		}
	}
	$(document).on('click', '.copybtn', function () {
		var textToCopy = $(this).attr('data-url');

		if (navigator.clipboard) {
			navigator.clipboard.writeText(textToCopy).then(function () {
				alert_float('success', "Tutorial Public URL successfully copied.");
			}).catch(function (err) {
				console.error('Clipboard copy failed', err);
				fallbackCopy(textToCopy);
			});
		} else {
			fallbackCopy(textToCopy);
		}

		function fallbackCopy(text) {
			var tempElement = $("<textarea>");
			$("body").append(tempElement);
			tempElement.val(text).select();
			document.execCommand("copy");
			tempElement.remove();
			alert_float('success', "Tutorial Public URL successfully copied.");
		}
	});
</script>
</body>

</html>