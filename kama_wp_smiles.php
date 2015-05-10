<?php
/*
Plugin Name: Kama WP Smiles
Version: 1.7.0
Description: Заменяет стандартные смайлики WP. Легко можно установить свои смайлы, также в настройках можно выбрать предпочитаемые смайлики.
Plugin URI: http://wp-kama.ru/?p=185
Author: Kama
Author URI: http://wp-kama.ru/
*/

/* удаляем стандартные фильты */
remove_action('init', 'smilies_init', 5);
remove_filter('comment_text', 'convert_smilies', 20);
remove_filter('the_content', 'convert_smilies');
remove_filter('the_excerpt', 'convert_smilies');

register_activation_hook( __FILE__, function(){ Kama_Wp_Smiles::instance()->activation(); } );

register_uninstall_hook(  __FILE__, array( 'Kama_Wp_Smiles', 'uninstall') );



Kama_Wp_Smiles::instance();


function kama_sm_get_smiles_code( $textarea_id ){
	$KWS = Kama_Wp_Smiles::instance();	
	
	return $KWS->get_all_smile_html( $textarea_id ) . $KWS->insert_smile_js();
}


class Kama_Wp_Smiles {
	const OPT_NAME = 'wp_sm_opt'; //название опций
	
	public $plugin_dir;
	public $plugin_url;
	public $opt; // опции
	
	private $sm_img; // шаблон замены
	
	protected static $instance;
	
	public static function instance(){
		is_null( self::$instance ) && self::$instance = new self;
		return self::$instance;
	}
	
	private function __construct(){
		$this->plugin_dir = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);
		
		$this->opt = get_option( self::OPT_NAME );
			
		$this->sm_img = '<img class="kws-smiley" src="'. $this->plugin_url .'smiles/%1$s.gif" alt="%2$s" />';
		
		
		// инициализация
		add_action( 'wp_head', array( &$this, 'styles') );
		
		if( ! $this->opt['not_insert'] )
			add_action( 'wp_footer', array( &$this, 'footer_scripts') );

		add_filter('comment_text', array( &$this, 'smiles') );
		add_filter('the_content', array( &$this, 'smiles') );
		add_filter('the_excerpt', array( &$this, 'smiles') );
		
		if( is_admin() ) $this->admin_init();
	}
		
	function def_options(){
		$this->opt['textarea_id'] = 'comment';
		$this->opt['spec_tags']   = array('pre','code');
		$this->opt['not_insert']  = 0;
		$this->opt['additional_css']     = '';
		
		//разделил для того, чтобы упростить поиск вхождений
		$this->opt['used_sm'] = array('smile','sad','laugh','rofl','blum','kiss','yes','no','good','bad','unknw','sorry','pardon','wacko','acute','boast','boredom','dash','search','crazy','yess','cool');		
		$this->opt['hard_sm'] = array( //смайлики который обозначаются спецсимволами (исключения)
			 '=)'  => 'smile'
			,':)'  => 'smile'
			,':-)' => 'smile'
			,'=('  => 'sad'
			,':('  => 'sad'
			,':-(' => 'sad'
			,'=D'  => 'laugh'
			,':D'  => 'laugh'
			,':-D' => 'laugh'
		);
		$this->opt['exist'] = $this->get_dir_smile_names();
		
		update_option( self::OPT_NAME, $this->opt ); // add_option учитывается автоматом
			
		return true;
	}

	// Функция фильтрации
	function smiles( $text ){
		$pattern = '';
		
		foreach( $this->opt['hard_sm'] as $k => $v ) $pattern .= preg_quote( $k )."|";
		
		$pattern = $pattern . '\*([a-z0-9-_]{0,20})\*'; // паттерн смайликов для замены
		
		// если есть теги, в которых не нужно проводить замену
		if( $pat = $this->opt['spec_tags'] ){
			$out = $pat1 = $pat2 = '';
			
			foreach( $pat as $tag ) $pat1 .= "(<$tag.*$tag>)|"; // (<code.*code>)|(<pre.*pre>)|(<blockquote.*blockquote>)
			
			$pat1 = trim( $pat1, '|');
			$pat2 .= '^<('. implode('|', $pat) .')'; 	// ^<(code|pre|blockquote)
			$textarr = preg_split("@$pat1@Usi", $text, -1, PREG_SPLIT_DELIM_CAPTURE );
			
			foreach( $textarr as $textel ){
				if( strlen( $textel ) > 0 && ! preg_match("@$pat2@i", $textel ) )
					$out .= preg_replace_callback("@$pattern@", array( $this, 'kama_smiles_replacement'), $textel );
				else
					$out .= $textel;
			}
		}
		else
			$out = preg_replace_callback("@$pattern@", array( $this, 'kama_smiles_replacement'), $text);

		return $out;
	}
	
	// Коллбэк функция для замены
	function kama_smiles_replacement( $match ){
		if( $ok = @ $this->opt['hard_sm'][ $match[0] ] )
			return sprintf( $this->sm_img, $ok, $ok );  
		
		elseif( in_array( $match[1], $this->opt['exist']) )
			return sprintf( $this->sm_img, $match[1], $match[0] );
		
		return $match[0]; // " {smile $match[0] not defined} ";
	}


	function footer_scripts(){
		if( ! is_singular() || $GLOBALS['post']->comment_status != 'open' )
			return; 

		$all_smile = addslashes( $this->get_all_smile_html( $this->opt['textarea_id'] ) );
		
		?>
		<!-- Kama WP Smiles -->
		<?php echo $this->insert_smile_js(); ?>
		<script type="text/javascript">			
			var tx = document.getElementById( '<?php echo $this->opt['textarea_id'] ?>' );
			if( tx ){
				var
				txNext = tx.nextSibling,
				txPar  = tx.parentNode,
				txWrapper = document.createElement('DIV');
				
				txWrapper.innerHTML = '<?php echo $all_smile ?>';
				txWrapper.setAttribute('class', 'kws-wrapper');
				txWrapper.appendChild(tx);
				txWrapper = txPar.insertBefore(txWrapper, txNext);			
			}
		</script>
		<!-- End Kama WP Smiles -->
		<?php
		
		return;
	}
	
	function get_all_smile_html( $textarea_id = '' ){
		$all_smiles = $this->all_smiles( $textarea_id );
		
		// прячем src чтобы не было загрузки картинок при загрузке страницы, только при наведении
		$all_smiles = str_replace( 'style', 'bg', $all_smiles );
		
		$out = '<div id="sm_list" class="sm_list" style="width:30px; height:30px; background:url('. $this->plugin_url .'smiles/smile.gif) center center no-repeat" 
			onmouseover="
			var el=this.childNodes[0];
			if( el.style.display == \'block\' )
				return;

			el.style.display=\'block\';
			
			for( var i=0; i < el.childNodes.length; i++ ){
				var l = el.childNodes[i];
				var bg = l.getAttribute(\'bg\');
				if( bg )
					l.setAttribute( \'style\', bg );
			}
			" 
			onmouseout="this.childNodes[0].style.display = \'none\'">
			<div id="sm_container" class="sm_container">'. $all_smiles .'</div>
		</div>';
		
		// нужно в одну строку, используется в js 
		$out = str_replace( array("\n","\t","\r"), '', $out );

		return $out;
	}
		
	function all_smiles( $textarea_id = false ){
		$gather_sm = array(); //собираем все в 1 массив
		
		// переварачиваем массив и избавляемся от дублей
		$hard_sm = array_flip( $this->opt['hard_sm'] );

		foreach( $this->opt['used_sm'] as $val ){
			$gather_sm[ $val ] = @ $hard_sm[ $val ];
			
			if( empty( $gather_sm[ $val ] ) ){
				$gather_sm[ $val ] = "*$val*";
			}
		}
		
		//преобразуем в картинки
		$out = '';
		foreach( $gather_sm as $name => $text ){
			$params = "'{$text}', " . ( $textarea_id ? "'$textarea_id'" : "'{$this->opt['textarea_id']}'");
			$out .= '<div class="smiles_button" onclick="ksm_insert('. $params .');" style="background-image:url('. $this->plugin_url .'smiles/'. $name .'.gif);" title="'. $text .'"></div>';
		}
			
		return $out;
	}

	// wp_head
	function styles(){
		if( ! is_singular() || $GLOBALS['post']->comment_status != 'open' )
			return; 
		
		echo '<style>'. $this->main_css() . @ $this->opt['additional_css'] .'</style>';
	}
	
	function main_css(){
		ob_start();
		?>
<style>
/* kama wp smiles */
.kws-wrapper{ position: relative; z-index:99; }
.sm_list{ z-index:9999; position:absolute; bottom:.3em; left:.3em; }
.sm_container{ display:none; position:absolute; top:0px; left:0px; width:410px; box-sizing: border-box; z-index:1001; background:#fff; padding:5px; border-radius:2px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.35); }
.sm_container:after{ content:''; display:table; clear:both; }
.sm_container .smiles_button{ cursor:pointer; width:50px; height: 35px; display: inline-block; float: left; background-position:center center; background-repeat:no-repeat; }
.sm_container .smiles_button:hover{ background-color: rgba(255, 223, 0,.1); }
.kws-smiley{ display: inline !important; border: none !important; box-shadow: none !important; margin: 0 .07em !important; vertical-align:-0.2em !important; background: none !important; padding: 0 !important;
}
</style>
		<?php
		
		return str_replace( array('<style>','</style>'), '', ob_get_clean() );
	}
	
	function insert_smile_js(){
		ob_start();
		?>
		<script type="text/javascript">
		function ksm_insert( aTag, txtr_id ){
			var tx = document.getElementById( txtr_id );				
			tx.focus();
			aTag = ' ' + aTag + ' ';
			if( typeof tx.selectionStart != 'undefined'){
				var start = tx.selectionStart;
				var end = tx.selectionEnd;		
				
				var insText = tx.value.substring(start, end);
				tx.value = tx.value.substr(0, start) +  aTag  + tx.value.substr(end);
				
				var pos = start + aTag.length;
				tx.selectionStart = pos;
				tx.selectionEnd = pos;
			}
			else if(typeof document.selection != 'undefined') {
				var range = document.selection.createRange();
				range.text = aTag;
			}
			
			document.getElementById('sm_container').style.display = 'none';
			
			if( typeof tinyMCE != 'undefined' )
				tinyMCE.execCommand("mceInsertContent", false, aTag );
		}
		</script>
		<?php
		return str_replace( array("\n","\t","\r"), '', ob_get_clean() );
	}
	
	// читаем файлы с каталога. вернет массив
	function get_dir_smile_names(){
		$out = array();
		foreach( glob( $this->plugin_dir . 'smiles/*.gif' ) as $fpath ){
			$fname = basename( $fpath );
			$out[] = preg_replace('@\..*?$@', '', $fname ); // удяляем расширение
		}

		return $out;
	}
	

	
	
	## Админ часть ---------------------------------------------------------------------------
	function admin_init(){	
		add_action( 'admin_menu',  array( $this, 'admin_menu') );
		
		// добавляем смайлии к формам
		add_action( 'the_editor', array( $this, 'admin_insert') );
		add_action( 'admin_print_footer_scripts', array( $this, 'admin_js'), 999 );
		
		add_action( 'admin_head', array( $this, 'admin_styles') );
	}
	
	function admin_styles(){ echo '<style>'. $this->main_css() .'</style>'; }
	
	function activation(){
		delete_option('use_smilies');
		
		if( ! get_option( self::OPT_NAME ) )
			$this->def_options();
	}
	
	function uninstall(){
		global $wpdb;
		
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
			
		// проверка пройдена успешно. Начиная от сюда удаляем опции и все остальное.
		delete_option( self::OPT_NAME );
		
		// Удаляем 
		foreach( self::$instance->get_dir_smile_names() as $val ){
			$val = addslashes( $val);
			if( $val ){
				$val = $wpdb->escape( $val );
				$wpdb->query( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, ' *$val* ', '') WHERE post_type = 'post'" );
				$wpdb->query( "UPDATE $wpdb->comments SET comment_content = REPLACE(comment_content, ' *$val* ', '')" );
			}
		}
	}	
	
	function admin_menu(){
		add_options_page('Настройки Kama WP Smiles', 'Kama WP Smiles', 'manage_options', __FILE__,  array( $this, 'admin_options_page'));
	}
	
	function admin_options_page(){
		if( ! current_user_can('manage_options') )
			return;
			
		if( isset( $_POST['kama_sm_submit'] ) ){
			$used_sm = $used_sm2 = array();
			
			$used_sm = explode(",", trim( $_POST['used_sm']) );
			
			foreach( $used_sm as $val ){
				$val = trim( $val);
				if( $val )
					$used_sm2[] = $val;
			}
			
			$spec_tags = $_POST['spec_tags'] ? explode(',', str_replace(' ', '', trim( $_POST['spec_tags'] ) ) ) : array();
			
			$hard_sm = trim( $_POST['hard_sm'] );
			$hard_sm = explode("\n", $hard_sm);
			foreach( $hard_sm as $val ){
				if( empty( $val) )
					continue;
					
				$temp = explode(' >>> ', trim( $val ) );
				$hard_sm['temp'][ trim( $temp[0] ) ] = trim( $temp[1] );
			}
			$hard_sm = $hard_sm['temp'];
		
			$this->opt['textarea_id'] = $_POST['textarea_id'];
			$this->opt['additional_css']     = stripslashes( $_POST['additional_css'] );
			$this->opt['spec_tags']   = $spec_tags;
			$this->opt['used_sm']     = $used_sm2;
			$this->opt['not_insert']  = isset( $_POST['not_insert'] ) ? 1 : 0;
			$this->opt['exist']       = $this->get_dir_smile_names();
			$this->opt['hard_sm']     = $hard_sm;

			update_option( self::OPT_NAME, $this->opt );
			delete_option('use_smilies'); // удаляем стандартную опцию отображения смайликов
		}
		elseif( isset( $_POST['kama_sm_reset']) )
			$this->def_options();
		
		
		include $this->plugin_dir .'admin.php';
	}
	
	
	
	// добавляем ко всем textarea созданым через the_editor
	function admin_insert( $html ){
		preg_match('~<textarea[^>]+id=[\'"]([^\'"]+)~i', $html, $match );
		$tx_id = $match[1];

		$html = str_replace('textarea>', 'textarea>'. $this->get_all_smile_html( $tx_id ), $html );

		return $html;
	}
	
	function admin_js(){
		?>
		<?php echo $this->insert_smile_js(); ?>
		<script type="text/javascript">
		//* Передвигаем блоки смайликов для визуального редактора и для HTML редактора
		jQuery(document).ready(function( $){
			// Передвигаем смайлы в HTML редактор
			// форм может быть несколько поэтому перебираем массив
			$('.sm_list').each(function(){
				var $quicktags = $(this).siblings('.quicktags-toolbar');
				if( $quicktags[0] ){
					$quicktags.append( $(this) );
					$(this).css({ position:'absolute', display:'inline-block', padding:'4px 0 0 25px', left:'auto', top:'auto', right:'auto', bottom:'auto', height:'23px' });
				}			
			});
			
			var $mce_editor = $('#insert-media-button');
			if( $mce_editor[0] ){
				$mce_editor.after( $($('.sm_list')[0]).css({ position:'relative', padding:'0', margin:'2px 0px 0px 30px', left:'none', top:'none', right:'none', bottom:'none' }) );
			}
			
		});
		//*/
		</script>
		<?php
	}
	
	// Выберите смайлики:
	function get_dir_smiles_img(){
		$hard_sm = array_flip( $this->opt['hard_sm'] );
		$gather_sm = array();

		foreach( $this->get_dir_smile_names() as $smile ){
			$sm_name = $sm_code = $smile;
			if( @ $hard_sm[ $smile ] ){
				$sm_code = $smile;
				$sm_name = $hard_sm[ $smile ];
			}
				
			echo '<b id="'. $sm_code .'" title="'. $sm_name .'" class="'. ( in_array( $sm_code, (array) @ $this->opt['used_sm'] ) ? 'checked':'' ) .'" >'. sprintf( $this->sm_img, $sm_code, $sm_name ) .'</b>';
		}
	}
	
}