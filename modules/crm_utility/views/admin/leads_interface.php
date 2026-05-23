<?php $showScriptTag = false; ?>
<?php if($show == 'dropdown'): ?>
	<div class="col-md-2 leads-filter-column mtop10">
		<label for="custom_view" class="control-label">Products</label>
		<?php
		echo render_select('view_products',get_tags_unique(),array('name','name'),'','',array('data-width'=>'100%','data-none-selected-text'=>_l('tags')),array(),'no-mbot');
		?>
	</div>
<?php endif; ?>
<?php if($showScriptTag): #this is to avoid script tag repetation ?>
<script type="text/javascript">
<?php endif; ?>
	<?php if($show == 'js'): ?>
		/*$("table.table-leads").on('preXhr.dt', function ( e, settings, data ) {
	      data.view_products = $("#view_products").val();
	   	});*/
		/*var columns = $(".table-leads thead th");
		var tags_i = -1;
		$.each(columns,function(i,elem){
			if($(elem).attr('id') == 'th-tags'){
				tags_i = i;
			}
		});
		console.log(tags_i);*/
		$("#view_products").on('change',function(){
			var product = $(":selected",this).text(),
			regex = '\\b' + product + '\\b';
			$("table.table-leads").DataTable().search(product).draw();
		});
	<?php endif; ?>
<?php if($showScriptTag): ?>
</script>
<?php endif; ?>