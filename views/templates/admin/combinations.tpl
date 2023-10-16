<form id="module_form" class="defaultForm form-horizontal" method="post" enctype="multipart/form-data" novalidate="">
	<div class="panel" id="fieldset_0">												
		<div class="panel-heading">
			<i class="icon-info-sign"></i>
		</div>								
        {foreach from=$images item=image}
            <div class="form-wrapper">											
                <div class="form-group">						
			        <div class="col-lg-4">
                            <img src="{$image.link}" />
                    </div>
                    <div class="col-lg-8">
                            {foreach from=$product_attributes item=pa}
                                <div class="row">
                                    <div class="col-xs-12">
                                        <input type="checkbox" value="{$pa.id_attribute}" name="images[{$image.id_image}][]"> {$pa.name}
                                    </div>
                                </div>
                            {/foreach}
                    </div>                    
                </div>																	
	        </div><!-- /.form-wrapper -->
        {/foreach}					
    <input type="hidden" name="step" value="assign" />
    <input type="hidden" name="id_product" value="{$id_product}" />
    <input type="hidden" name="id_attribute_group" value="{$id_attribute_group}" />
    <div class="panel-footer">
        <button type="submit" value="1" id="module_form_submit_btn" name="submitConfig" class="btn btn-default pull-right">
            <i class="process-icon-save"></i> Next
        </button>
	</div>
    </div>
</form>
