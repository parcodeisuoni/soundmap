<div id="slide_3">
	<div class="third-step step">
		<div class="column-left">
			<div class="text-content">
				<h2><?php echo __("Information about the recording",'soundmap'); ?></h2>
				<p><?php echo __("Help us filling this form about the information of your recording.",'soundmap'); ?></p>
				<p><?php echo __("Please, fill as much fields as you can, on the different languages of the sound map. Don't worry if you don't know how to do it in any language, we will check the texts and translate them.",'soundmap'); ?></p>
				<p><?php echo __("Fill also the fields of the date you did the recording, the name of the author and also try to categorize the recording. You can select more than one category.",'soundmap'); ?></p>
			</div>
		</div>
		<div class="column-right">
			<h2 class="step-caption"><?php echo __("Step 3: Information about the recording"); ?></h2>
			<form id="data-form" class="cmxform" method="get" >
				
			<?php

				oneLanguageFields();
		
			
			?>
			
			<input type="hidden" id="posLat" name="posLat" value=""></input>
			<input type="hidden" id="posLong" name="posLong" value=""></input>
			<input type="hidden" id="fileURL" name="fileURL" value=""></input>
			<input type="hidden" id="attachID" name="attachID" value=""></input>
			<input type="hidden" id="duracion" name="duracion" value=""></input>
			<input type="hidden" id="fileName" name="fileName" value=""></input> 

			</form>			
			
		</div>
	</div>
</div>


<?php


function commonFields(){
	?>
	<div id="common-fields">
		<p><label for="date"><?php echo __("Date (dd/mm/yyyy)",'soundmap')?></label><input class="field" type="text" id="date" name="date"></input></p>
		<p><label for="author"><?php echo __("Author",'soundmap')?></label><input type="text" class="field" id="author" name="author" value=""></input></p>
		<p><label for="categoria"><?php echo __("Categories",'soundmap')?></label>
								<select id="categoria" name="categoria[]" value="" multiple="multiple" class="field">
									                <?php
                								$categories=  get_categories();

                								foreach ($categories as $cat) {

                    							$option = '<option value="'.$cat->term_id.'">';
                    							$option .= $cat->cat_name;
                    							$option .= '</option>';
                    							echo $option;

								                }
                							?>
								</select></p>
	</div>
	<?php
}

function oneLanguageFields(){
	
	
		?><div id="fields">
		    <ul>
		        <li><a href="#tabs-it">Italiano</a></li>
		        <li><a href="#tabs-en">English</a></li>
		        <li><a href="#tabs-eu">Euskara</a></li>
		        <li><a href="#tabs-es">Espa&ntilde;ol</a></li>
		    </ul>

			<div id="tabs-it" class="tab">
				<p><label for="title_it" ><?php echo __("Title",'soundmap'); ?> - Italiano</label><input class="field" type="text" id="title_it" name="title_it" value=""></input></p>
				<p><label for="descripcion_it" ><?php echo __("Description",'soundmap'); ?> - Italiano</label><textarea class="field text-field" rows="5" type="text" id="descripcion_it" name="descripcion_it"></textarea></p>
			</div>
			<div id="tabs-en" class="tab">
				<p><label for="title_en" ><?php echo __("Title",'soundmap'); ?> - English</label><input class="field" type="text" id="title_en" name="title_en" value=""></input></p>
				<p><label for="descripcion_en" ><?php echo __("Description",'soundmap'); ?> - English</label><textarea class="field text-field" rows="5" type="text" id="descripcion_en" name="descripcion_en"></textarea></p>
			</div>
			<div id="tabs-eu" class="tab">
				<p><label for="title_eu" ><?php echo __("Title",'soundmap'); ?> - Euskara</label><input class="field" type="text" id="title_eu" name="title_eu" value=""></input></p>
				<p><label for="descripcion_eu" ><?php echo __("Description",'soundmap'); ?> - Euskara</label><textarea class="field text-field" rows="5" type="text" id="descripcion_eu" name="descripcion_eu"></textarea></p>
			</div>
			<div id="tabs-es" class="tab">
				<p><label for="title_es" ><?php echo __("Title",'soundmap'); ?> - Espa&ntilde;ol</label><input class="field" type="text" id="title_es" name="title_es" value=""></input></p>
				<p><label for="descripcion_es" ><?php echo __("Description",'soundmap'); ?> - Espa&ntilde;ol</label><textarea class="field text-field" rows="5" type="text" id="descripcion_es" name="descripcion_es"></textarea></p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery("document").ready(function(){
				jQuery("#fields").tabs();
			});	
		</script>
		
		<?php
		commonFields();
}

?>