<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
add_option('bg_bpub_options', array('default'=>'', 'nextpage_level'=>'2', 'toc_level'=>'3', 'toc_place'=>'', 'author_place'=>'after'));

add_action('admin_menu', 'bg_bpub_add_plugin_page');
function bg_bpub_add_plugin_page(){
	/* translators: Option page title */
	add_options_page( __('Book Publisher options', 'bg_bpub') , __('Book Publisher', 'bg_bpub'), 'manage_options', 'bg_bpub_slug', 'bg_bpub_options_page_output' );
}

function bg_bpub_options_page_output(){
	$val = get_option('bg_rating_options');
	
	?>
	<div class="wrap">
		<h2><?php echo get_admin_page_title() ?></h2>

		<form action="options.php" method="POST">
			<?php
				settings_fields( 'bg_bpub_option_group' );		// скрытые защитные поля
				do_settings_sections( 'bg_bpub_page' ); 		// секции с настройками (опциями) 'section_1'
				submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Регистрируем настройки.
 * Настройки будут храниться в массиве, а не одна настройка = одна опция.
 */
add_action('admin_init', 'bg_bpub_settings');
function bg_bpub_settings(){
	// параметры: $option_group, $option_name, $sanitize_callback
	register_setting( 'bg_bpub_option_group', 'bg_bpub_options', 'bg_bpub_sanitize_callback' );

	// параметры: $id, $title, $callback, $page
	/* translators: Section title */
	add_settings_section( 'section_1', __('Default options', 'bg_bpub'), '', 'bg_bpub_page' ); 

	// параметры: $id, $title, $callback, $page, $section, $args
	/* translators: Settings field #1 */
	add_settings_field('bg_bpub_default', __('A post is book or not by default?', 'bg_bpub'), 'fill_bg_bpub_default', 'bg_bpub_page', 'section_1' );
	/* translators: Settings field #2 */
	add_settings_field('bg_bpub_nextpage_level', __('Header level for page break tags', 'bg_bpub'), 'fill_bg_bpub_nextpage_level', 'bg_bpub_page', 'section_1' );
	/* translators: Settings field #3 */
	add_settings_field('bg_bpub_toc_level', __('Header level for table of contents', 'bg_bpub'), 'fill_bg_bpub_toc_level', 'bg_bpub_page', 'section_1' );
	/* translators: Settings field #4 */
	add_settings_field('bg_bpub_toc_place', __('Table of contents on each page', 'bg_bpub'), 'fill_bg_bpub_toc_place', 'bg_bpub_page', 'section_1' );
	/* translators: Settings field #5 */
	add_settings_field('bg_bpub_author_place', __('Place where show name of book author', 'bg_bpub'), 'fill_bg_bpub_author_place', 'bg_bpub_page', 'section_1' );
}

## Заполняем опцию 1
function fill_bg_bpub_default(){
	$val = get_option('bg_bpub_options');
	$val = isset ($val['default'])?$val['default']:""; 
	
	?>
	<label><input type="checkbox" name="bg_bpub_options[default]" value="1" <?php checked( 1, $val ); ?> /> <?php /* translators: Comment to settings field #1 */ _e('check if a post is book by default', 'bg_bpub'); ?></label>
	<?php
}
## Заполняем опцию 2
function fill_bg_bpub_nextpage_level(){
	$val = get_option('bg_bpub_options');
	$val = $val['nextpage_level']; 
	?>
	<input type="number" name="bg_bpub_options[nextpage_level]" value="<?php echo esc_attr( $val ) ?>" min=1 max=6 /><br>
	<?php
}

## Заполняем опцию 3
function fill_bg_bpub_toc_level(){
	$val = get_option('bg_bpub_options');
	$val = $val['toc_level']; 
	?>
	<input type="number" name="bg_bpub_options[toc_level]" value="<?php echo esc_attr( $val ) ?>" min=1 max=6 /><br>
	<?php
}

## Заполняем опцию 4
function fill_bg_bpub_toc_place(){
	$val = get_option('bg_bpub_options');
	$val = isset ($val['toc_place'])?$val['toc_place']:""; 
	?>
	<label><input type="checkbox" name="bg_bpub_options[toc_place]" value="1" <?php checked( 1, $val ); ?> /></label>
	<?php
}

## Заполняем опцию 5
function fill_bg_bpub_author_place(){
	$val = get_option('bg_bpub_options');
	$val = $val['author_place']; 
	?>
   <select name="bg_bpub_options[author_place]">
		<option value="before" <?php selected( 'before', $val ); ?> ><?php /* translators: Option value of Settings field #1 */ _e('Before title', 'bg_bpub'); ?></option>
		<option value="after" <?php selected( 'after', $val ); ?> ><?php /* translators: Option value of Settings field #1 */ _e('After title', 'bg_bpub'); ?></option>
		<option value="none" <?php selected( 'none', $val ); ?> ><?php /* translators: Option value of Settings field #1 */ _e('Don\'t show name of book author', 'bg_bpub'); ?></option>
   </select>
	<?php
}

## Очистка данных
function bg_bpub_sanitize_callback( $options ){ 
	// очищаем
	foreach( $options as $name => &$val ){
		if( $name == 'default' ) {
			$val = (int) sanitize_text_field( $val );
			if ($val != 1) $val = "";
		}

		if( $name == 'nextpage_level' ) {
			$val = (int) sanitize_text_field( $val );
			if ($val < 1) $val = 1;
			if ($val > 6) $val = 6;
		}

		if( $name == 'toc_level' ) {
			$val = (int) sanitize_text_field( $val );
			if ($val < 1) $val = 1;
			if ($val > 6) $val = 6;
		}

		if( $name == 'toc_place' ) {
			$val = (int) sanitize_text_field( $val );
			if ($val != 1) $val = "";
		}
		
		if( $name == 'author_place' ) {
			$val = sanitize_key( $val );
		}
	}

	return $options;
}
