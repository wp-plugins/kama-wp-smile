<div class='wrap'>
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Настройки Kama WP Smiles</h2>
	
	<?php echo $this->get_all_smile_html(); ?>
	
	<form method="post" action="">
		<br style='clear:both'/>
		
		<div class='smiles_wrapper'>
			<h3>Выбранные смайлики (можно сортировать перетаскиванием):</h3>
			<div class='select_smiles'><?php $this->get_dir_smiles_img(); ?></div>
		</div>

		<div>
			<?php 
				$smiles = '';
				foreach( (array) @ $this->opt['used_sm'] as $smile )
					$smiles .= "$smile,";
				$smiles = rtrim($smiles, ',');
			?>
			<input type="hidden" name='used_sm' class='used_sm' value="<?php echo $smiles ?>"></textarea><br />
		</div>
		
		<br style='clear:both'/>
		
		<p><label><input type='text' name='textarea_id' size='15' value='<?php echo @ $this->opt['textarea_id']?>' /> <b>ID поля комментирования</b> (атирубт id HTML тега textarea).</label><br>
		Оставьте поле пустым, чтобы плагин не пытался автоматически встроить смайлики и используйте php код: <code>&lt;?php echo kama_sm_get_smiles_code( $textarea_id ) ?&gt;</code> в форме комментирвоания. Этот код выводит список смайликов (выглядит как под заголовком этой страницы).</p>
		
		
		<p><label><input type='text' name='spec_tags' size='15' value='<?php echo implode(',', (array) @ $this->opt['spec_tags'])?>' /> <b>HTML теги исключения</b>.</label> 
		<br>Укажите название HTML тегов (через запятую), содержание которых не нужно обрабатывать. Например: <code>code,pre</code>.</p>		
		
		<p>
			Специальные обозначения смайликов:<br>
			<?php 
			echo '<textarea name="hard_sm" style="width:200px; height:150px;">';
			foreach( (array) @ $this->opt['hard_sm'] as $k => $v ) echo $k .' >>> '. $v ."\n";
			echo '</textarea>';
			?>
			<br> 
			Укажите обозначение, которое будет использоваться в тексте и название смайлика на которы обозначение должно быть заменено. Название смотрите выше, при наведении на смайлик.
		</p>
		
		<p>
			<label>Дополнительные CSS стили:<br>
			<textarea name='additional_css' style="width:100%; height:70px;"><?php echo @ $this->opt['additional_css']?></textarea>
			<br><small>Допишите здесь имеющиеся стили, чтобы настроить вывод под себя. Эти стили будут добавлены после дефолтных.</small>
			</label>
			
			<label>
			<br><br>Дефолтные стили:<br>
			<textarea readonly style="width:100%; height:50px;"><?php echo @ $this->main_css() ?></textarea>
			</label>
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
	Если вы загружаете свой комплект смайликов, то лучше используйте идентичные названия и не забудьте поделиться этим комплектом <a href='http://wp-kama.ru/contacts'>со мной</a>. Я включу его в плагин <img src="<?php echo KWS_PLUGIN_URL ?>smiles/bb.gif" /></p>
	<h3>Удаление плагина</h3>
	<p>При удалении, плагин удаляет все следы прибывания на сайте. Конструкции смайликов вида <code>*smile*</code> в постах и комментариях также будут удалены. При удалении, на всякий случай, сделайте бэкап базы данных.</p>
	
</div>

<?php wp_enqueue_script('jquery-ui-sortable'); ?>
<script type='text/javascript'>
	// jQuery
	jQuery(document).ready(function($){
		// выбор смайликов
		var 
		$allSm   = $('.select_smiles'),
		$used_sm = $('input[name="used_sm"]'),
		$elUsed  = $('<div class="used-smiles">');
		
		$allSm.before( $elUsed ).before('<h3>Невыбранные:<h3>');
		
		$allSm.find('> *').click(function(){
			if( $(this).hasClass('checked') ){
				$(this).prependTo( $allSm ).removeClass('checked');
			} else {
				$(this).appendTo( $elUsed ).addClass('checked');
			}

			
			collectToInput();
		});
		
		// собираем по порядку при первой загрузке
		var array = $used_sm.val().replace(/\r/, '').split(/,/);
		$.each( array, function(){
			this.replace(/^\s+/,'').replace(/\s+$/,'');
			if( this != '' ){
				$allSm.find('#'+ this).appendTo( $elUsed );
			}
		});
				
		// обновляет input name="used_sm"
		var collectToInput = function(){
			var newSmIds = [];
			$elUsed.find('> *').each(function(){
				newSmIds.push( $(this).attr('id') );
			});
			$used_sm.val( newSmIds.join(',') );			
		};
		
		// сортировка смайликов
		$('.used-smiles').sortable({
			stop: function( event, ui ) { collectToInput(); }
		});
				
	});	
</script>
