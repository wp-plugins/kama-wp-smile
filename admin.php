<style type="text/css">
	.sm_list{ position:relative !important; top:16px !important; }
	.wrap{ max-width:1000px; margin-left:20px; }
	
	.smiles_wrapper{ width:700px; }
	.smiles_wrapper b{ display:block; width:50px; height:50px; float:left; text-align:center; margin:5px; cursor:pointer; }
	.smiles_wrapper b img{ padding-top:10px;}
	.smiles_wrapper b.checked{ background:#eee; outline:1px dashed #BCBCBC; }
	
	.kama_sm_options { padding:20px 10px 0; }
	.kama_sm_options table td { padding:3px 5px; text-align:left; }
	.kama_sm_options table td span { line-height:16px; }
	
	.used_smiles{  }
	.select_smiles{  }
	.used_smiles:after, .select_smiles:after{ display:block; content:''; clear:both; }
	
	.kws-smiley{ margin-top: 20% !important; }
</style>

<div class='wrap'>
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Настройки Kama WP Smiles</h2>
	<?php echo $this->get_all_smile_html(); ?>
	
	<form method="post" action="">
		<br style='clear:both'/>
		
		<div class='smiles_wrapper'>
			<p>Выберите смайлики:</p>
			<div class='select_smiles'><?php $this->get_dir_smiles_img(); ?></div>
		</div>

		<div>
			<?php 
				$smiles = '';
				foreach( $this->opt['used_sm'] as $smile )
					$smiles .= "$smile,";
				$smiles = rtrim($smiles, ',');
			?>
			<input type="hidden" name='used_sm' class='used_sm' value="<?php echo $smiles ?>"></textarea><br />
		</div>
		
		<br style='clear:both'/>
		
		<p><label><input type='text' name='textarea_id' size='15' value='<?php echo $this->opt['textarea_id']?>' /> <b>ID поля комментирования</b> (атирубт id HTML тега textarea).</label><br>
		Оставьте поле пустым, чтобы плагин не пытался автоматически встроить смайлики и используйте php код: <code>&lt;?php echo kama_sm_get_smiles_code( $textarea_id ) ?&gt;</code> в форме комментирвоания. Этот код выводит список смайликов (выглядит как под заголовком этой страницы).</p>
		
		
		<p><label><input type='text' name='spec_tags' size='15' value='<?php echo implode(',', $this->opt['spec_tags'])?>' /> <b>HTML теги исключения</b>.</label> 
		<br>Укажите название HTML тегов (через запятую), содержание которых не нужно обрабатывать. Например: <code>code,pre</code>.</p>		
		
		<p>
			Специальные обозначения смайликов:<br>
			<textarea name='hard_sm' style="width:200px; height:150px;"><?php 
				foreach( $this->opt['hard_sm'] as $k => $v ){
					echo $k .' >>> '. $v ."\n";
				} 
			?></textarea>
			<br> 
			Укажите обозначение, которое будет использоваться в тексте и название смайлика на которы обозначение должно быть заменено. Название смотрите выше, при наведении на смайлик.
		</p>
		
		<p>
			CSS стили:<br>
			<textarea name='use_css' style="width:100%; height:150px;"><?php echo $this->opt['use_css']?></textarea>
			<br> 
			Можно перенести стили в файл стилей, чтобы они не хранились в настройках.
		</p>
		
		<br>	
		<input type='submit' name='kama_sm_submit' class='button-primary' value='Сохранить изменения' title='Сохранить изменения'  /> <input type='submit' name='kama_sm_reset' class='button' value='Сбросить настройки на начальные' onclick='return confirm("Уверенны?")' />
	</form>
	
	<br style='clear:both'/>
	
	<form method="post" action="">
		<p></p>
	</form>
	<div style='text-align:right;'><em><small>Права на смайлики принадлежат Манцурову Ивану (<a href='http://www.kolobok.us/content_plugins/gallery/gallery.php?smiles.8'>Авторские смайлы стиля Колобок</a>, можете собрать свою коллекцию).</small></em></div>
	<br><br>
	<hr>
	<br><br>
	<h3>Другой комплект смайликов</h3>
	<b>Чтобы заменить смайлики на свои</b>, нужно в папку плагина <code>smiles</code> "залить" свои картинки. Важно: файлы должны быть в gif формате.
	<br><br>
	Название файлов будут использоваться в тексте, как теги для замены. Например, если вы залили файл kissed.gif в текст будет вставляться тег <code>*kissed*</code>. В названии допускаются: нижний регистр латинских букв (a-z), цифры (0-9), тире (-) и подчеркивание (_).
	<br><br>
	Если вы загружаете свой комплект смайликов, то лучше используйте идентичные названия и не забудте поделиться этим комплектом <a href='http://wp-kama.ru/contacts'>со мной</a>. Я включу его в плагин <img src="<?php echo $this->plugin_url ?>smiles/bb.gif" /></p>
	<h3>Удалине плагина</h3>
	<p>При удалении, плагин удаляет все следы прибывания на сайте. Конструкции смайликов вида <code>*smile*</code> в постах и комментариях также будут удалены. При удалении, на всякий случай, сделайте бэкап базы данных.</p>
	
</div>


<script type='text/javascript'>
	// jQuery
	jQuery(document).ready(function($){
		// выбор смайликов
		var 
		$el = $('.select_smiles'),
		$elUsed = $('<div class="used_smiles"></div>'),
		$used_sm = $('.used_sm');
		$el.before( $elUsed );
		
		$el.children().each(function(){
			$(this).click(function(){
				if( $(this).hasClass('checked') ){
					$(this).prependTo( $el ).removeClass('checked');
				} else {
					$(this).appendTo( $elUsed ).addClass('checked');
				}
				// обновляем поле
				var used_sm_val = '';
				$elUsed.find('b').each(function(){
					used_sm_val += $(this).attr('id') + ",";
				});
				$used_sm.val( used_sm_val.replace(/,$/,'') );
			});
		});
		
		// собираем по порядку
		var array = $used_sm.val().replace(/\r/, '').split(/,/);
		$.each( array, function(){
			var th = this;
			th.replace(/^\s+/,'').replace(/\s+$/,'');
			if( th != '' ){
				$el.find('#'+ th).appendTo( $elUsed );
			}
		});
		// Конец Выбор смайликов
				
	});	
</script>
