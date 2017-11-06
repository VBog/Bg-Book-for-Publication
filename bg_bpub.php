<?php
/* 
    Plugin Name: Bg Book Publisher 
    Description: The plugin helps you to publish big book with a detailed structure of chapters and sections and forms table of contents of the book.
    Version: 1.0.1
    Author: VBog
    Author URI: https://bogaiskov.ru 
	License:     GPL2
	Text Domain: bg_bpub
	Domain Path: /languages
*/

/*  Copyright 2017  Vadim Bogaiskov  (email: vadim.bogaiskov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*****************************************************************************************
	Блок загрузки плагина
	
******************************************************************************************/

// Запрет прямого запуска скрипта
if ( !defined('ABSPATH') ) {
	die( 'Sorry, you are not allowed to access this page directly.' ); 
}

define('BG_BPUB_VERSION', '1.0.1');

// Устанавливаем крючки
if ( defined('ABSPATH') && defined('WPINC') ) {
// Регистрируем крючок для обработки контента при его сохранении
	add_action( 'save_post', 'bg_bpub_save');

// Регистрируем крючок на удаление плагина
	if (function_exists('register_uninstall_hook')) {
		register_uninstall_hook(__FILE__, 'bg_bpub_deinstall');
	}

// Регистрируем крючок для загрузки интернационализации 
	add_action( 'plugins_loaded', 'bg_bpub_load_textdomain' );
	
	if ( is_admin() ) {
	// Регистрируем крючок для добавления JS скрипта в админке 
		add_action( 'admin_enqueue_scripts' , 'bg_bpub_admin_enqueue_scripts' ); 
	} else {
	// Регистрируем крючок для добавления таблицы стилей для плагина
		add_action( 'wp_enqueue_scripts' , 'bg_bpub_frontend_styles' );
	// Регистрируем фильтр для добавления имени автора книги в заголовок записи
		add_filter( 'the_title', 'add_author_to_page_title', 100, 2 );
	}

// Регистрируем шорт-код book_author
	add_shortcode( 'book_author', 'bg_bpub_book_author_shortcode' );
}

// Загрузка интернационализации
function bg_bpub_load_textdomain() {
  load_plugin_textdomain( 'bg_bpub', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

// JS скрипт 
function bg_bpub_admin_enqueue_scripts () {
	wp_enqueue_script( 'bg_bpub_proc', plugins_url( 'js/bg_bpub_admin.js', __FILE__ ), false, BG_BPUB_VERSION, true );
	wp_localize_script( 'bg_bpub_proc', 'bg_bpub', 
		array( 
			'nonce' => wp_create_nonce('bg-bpub-nonce') 
		) 
	);
}
	 
// Tаблица стилей для плагина
function bg_bpub_frontend_styles () {
	wp_enqueue_style( "bg_bpub_styles", plugins_url( "/css/style.css", plugin_basename(__FILE__) ), array() , BG_BPUB_VERSION  );
}

// Добавляем имя автора книги в заголовок записи
function add_author_to_page_title( $title, $id ) {
	global $post, $author_place;
	if ($author_place == 'none') return $title;
	$book_author = bg_bpub_book_author($id);
	if (!$book_author) return $title;
	// убедимся что мы редактируем заголовок нужного типа поста
	if ( is_singular( array ('post', 'page') ) && in_the_loop() ) {
		if ($author_place == 'after') $title = $title.'<br>'.$book_author;
		else if ($author_place == 'before') $title = $book_author.'<br>'.$title;
	}
	return $title;
}
// Имя автора книги
function bg_bpub_book_author($post_id) {
	
	$book_author = get_post_meta($post_id, 'book_author',true);
	return ((!$book_author)? "" : '<span class=bg_bpub_book_author>'.$book_author.'</span>');
}

// [book_author]
function bg_bpub_book_author_shortcode ( $atts, $content = null ) {
	$post = get_post();
	 return bg_bpub_book_author($post->ID);
}
// Выполняется при удалении плагина
function bg_bpub_deinstall() {
	// Удаляем опции
	delete_option('bg_bpub_options');
	
	// Удаляем мета-поля в постах
	$args = array(
		'numberposts' => -1,
		'post_type' => array('post','page'),
		'post_status' => 'any'
	);
	$allposts = get_posts($args);
	foreach( $allposts as $postinfo) {
		delete_post_meta( $postinfo->ID, 'the_book');
		delete_post_meta( $postinfo->ID, 'nextpage_level');
		delete_post_meta( $postinfo->ID, 'toc_level');
		delete_post_meta( $postinfo->ID, 'book_author');
	}
}


include_once ("inc/options.php");
/**************************************************************************
  Настраиваемые параметры плагина
***************************************************************************/
$options = get_option('bg_bpub_options');
// Пост является книгой по умолчанию
$is_book = isset ($options['default'])?$options['default']:""; 
// Уровень заголовков, по которым производить разбиение по страницам
$nextpage_level = $options['nextpage_level'];
// Максимальный уровень, до которого включать заголовки в оглавление
$toc_level = $options['toc_level'];
// Содержание на каждой странице
$toc_place = isset ($options['toc_place'])?$options['toc_place']:""; 
// Месторасположение имени автора в заголовке
$author_place = $options['author_place'];


/**************************************************************************
  Функция обработки текста при сохранении поста
***************************************************************************/
function bg_bpub_save( $id ) {
	global $is_book, $nextpage_level, $toc_level;

	$post = get_post($id);
	if( isset($post) && ($post->post_type == 'post' || $post->post_type == 'page') ) { 	// убедимся что мы редактируем нужный тип поста
		if (get_current_screen()->id == 'post' || get_current_screen()->id == 'page') {	// убедимся что мы на нужной странице админки
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return;					// пропустим если это автосохранение
			if ( ! current_user_can('edit_post', $id ) ) return;						// убедимся что пользователь может редактировать запись
		
			// Уровень заголовков, по которым производить разбиение по страницам
			if (get_post_meta($post->ID, 'nextpage_level',true))
				$nextpage_level = get_post_meta($post->ID, 'nextpage_level',true);
			// Максимальный уровень, до которого включать заголовки в оглавление
			if (get_post_meta($post->ID, 'toc_level',true))
				$toc_level = get_post_meta($post->ID, 'toc_level',true);

			$content = $post->post_content;
			// Удаляем ранее внесенные изменения
			$content = bg_bpub_clear ($content);
			// Добавляем разрывы страниц и оглавление
			if (get_post_meta($id, 'the_book',true)) $content = bg_bpub_proc ($content);

			// Удаляем хук, чтобы не было зацикливания
			remove_action( 'save_post', 'bg_bpub_save' );

			// обновляем запись. В это время срабатывает событие save_post
			wp_update_post( array( 'ID' => $id, 'post_content' => $content ) );

			// Ставим хук обратно
			add_action( 'save_post', 'bg_bpub_save' );
		}
	}
}

/**************************************************************************
  Служебные глобальные переменные
***************************************************************************/
// Текущий порядковый номер заголовка
$headers = array(
	"h1" => 0,
	"h2" => 0,
	"h3" => 0,
	"h4" => 0,
	"h5" => 0,
	"h6" => 0);
// Порядковый номер страницы
$pagenum = 1;
// Оглавление
$table_of_contents = "";

/**************************************************************************
	Функция разбора текста и формирования ссылок и оглавления
 **************************************************************************/
function bg_bpub_proc ($content) {
	global $toc_place, $table_of_contents;
	
	// Ищем все заголовки
	$content = preg_replace_callback ('/<(h[1-6])(.*?)>(.*?)<\/\1>/is',
		function ($match) {
			global $headers, $pagenum, $table_of_contents, $nextpage_level, $toc_level;
			
			$level = (int) $match[1][1];			// Уровень заголовка от 1 до 6
			
			$headers[$match[1]]++;					// Увеличиваем текущий номер заголовка этого уровня
			for ($l=$level; $l<=6; $l++) {			// и сбрасываем нумерацию заголовков нижнего уровня
				$headers['h'.($l+1)] = 0;
			}

			// Определяем место разбиения на страницы
			if ($level <= $nextpage_level && $headers[$match[1]] > 1) {
				$nextpage = "<!--nextpage-->";
				$pagenum++;
			}
			else $nextpage = "";

				// Формируем имя якоря
			$name = 'ch';
			for ($l=0; $l<$level; $l++) $name.='_'.$headers['h'.($l+1)];
			
			$anchor = "";
			if ($level <= $toc_level) {
				// Создаем оглавление
				$table_of_contents .= '<a class="bg_bpub_toc_'.$match[1].'" href="../'.$pagenum.'/#'.$name.'">'.strip_tags($match[3]).'</a><br>';
				// Создаем якорь
				$anchor = '<a name="'.$name.'"></a>';
			}	
			// Возвращаем заголовок с добавленными тегом новой страницы (в начале) и якорем (в конце)
			return $nextpage.'<'.$match[1].$match[2].'>'.$anchor.$match[3].'</'.$match[1].'>';
		} ,$content);
		
	if ($table_of_contents) {
		/* translators: Summary in spoiler on a page */
		$table_of_contents = '<div class="bg_bpub_toc"><details><summary><b>'.__('Table of contents', 'bg_bpub').'</b></summary><br>'.$table_of_contents.'</details></div>';

		// Оглавление на каждой странице, кроме первой
		if ($toc_place) {
			if (function_exists('bg_forreaders_proc'))
				$content = preg_replace ('/<!--nextpage-->/is', '<!--nextpage-->'.'[noread]'.$table_of_contents.'[/noread]', $content);	
			else
				$content = preg_replace ('/<!--nextpage-->/is', '<!--nextpage-->'.$table_of_contents, $content);	
		}
		
		// Оглавление на первой странице
		$content = 	preg_replace ('/href="\.\.\//is', 'href="', $table_of_contents).$content;
	}
	return $content;
}

/**************************************************************************
	Функция очистки текста от внесенных изменений
 **************************************************************************/
function bg_bpub_clear ($content) {
	
	// Удаляем оглавление
	$content = preg_replace ('/<div class="bg_bpub_toc">(.*?)<\/div>/is', "", $content);	
	$content = preg_replace ('/\[noread\]\s*\[\/noread\]/is', "", $content);	

	// Удаляем разбиение на страницы
	$content = preg_replace ('/<\!--nextpage-->/is', "", $content);	
	
	// Удаляем якори
	$content = preg_replace ('/<a name="ch_(.*?)"><\/a>/is', "", $content);	
	
	return $content;
}

/*****************************************************************************************
	Добавляем блок в боковую колонку на страницах редактирования страниц
	
******************************************************************************************/
add_action('admin_init', 'bg_bpub_extra_fields', 1);
// Создание блока
function bg_bpub_extra_fields() {
	/* translators: Meta box title */
    add_meta_box( 'bg_bpub_extra_fields', __('Book Publisher', 'bg_bpub'), 'bg_bpub_extra_fields_box_func', array('post', 'page'), 'side', 'low'  );
}
// Добавление полей
function bg_bpub_extra_fields_box_func( $post ){
	global $is_book, $nextpage_level, $toc_level;
	
	wp_nonce_field( basename( __FILE__ ), 'bg_bpub_extra_fields_nonce' );
	// Дополнительное поле поста
	add_post_meta($post->ID, 'the_book', $is_book, true );
	add_post_meta($post->ID, 'nextpage_level', $nextpage_level, true );
	add_post_meta($post->ID, 'toc_level', $toc_level, true );
	add_post_meta($post->ID, 'book_author', "", true );
	
	$html = '<label><input type="checkbox" name="bg_bpub_the_book" id="bg_bpub_the_book"';
	$html .= (get_post_meta($post->ID, 'the_book',true)) ? ' checked="checked"' : '';
	/* translators: Сheckbox label (in Metabox)*/
	$html .= ' /> '.__('this post is book', 'bg_bpub').'</label><br>';

	/* translators: Label for input field  (in Metabox) */
	$html .= '<label>'.__('Header level for page break tags', 'bg_bpub').'<br>';
	$html .= '<input type="number" name="bg_bpub_nextpage_level" id="bg_bpub_nextpage_level" min="1" max="6"';
	$html .= ' value="'.get_post_meta($post->ID, 'nextpage_level',true).'" /></label><br>';

	/* translators: Label for input field  (in Metabox) */
	$html .= '<label>'.__('Header level for table of contents', 'bg_bpub').'<br>';
	$html .= '<input type="number" name="bg_bpub_toc_level" id="bg_bpub_toc_level" min="1" max="6"';
	$html .= ' value="'.get_post_meta($post->ID, 'toc_level',true).'" /></label><br>';

	/* translators: Label for input field  (in Metabox) */
	$html .= '<label>'.__('Book author', 'bg_bpub').'<br>';
	$html .= '<input type="text" name="bg_bpub_book_author" id="bg_bpub_book_author" size="35"';
	$html .= ' value="'.get_post_meta($post->ID, 'book_author',true).'" /></label><br>';

	echo $html;
}
// Сохранение значений произвольных полей при сохранении поста
add_action('save_post', 'bg_bpub_extra_fields_update', 0);
function bg_bpub_extra_fields_update( $post_id ){

	// проверяем, пришёл ли запрос со страницы с метабоксом
	if ( !isset( $_POST['bg_bpub_extra_fields_nonce'] )
	|| !wp_verify_nonce( $_POST['bg_bpub_extra_fields_nonce'], basename( __FILE__ ) ) ) return $post_id;
	// проверяем, является ли запрос автосохранением
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
	// проверяем, права пользователя, может ли он редактировать записи
	if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;
	if (isset( $_POST['bg_bpub_the_book']) && $_POST['bg_bpub_the_book'] == 'on') {
		update_post_meta($post_id, 'the_book', $_POST['bg_bpub_the_book']);
		
		$nextpage_level = (int) sanitize_text_field($_POST['bg_bpub_nextpage_level']);
		if ($nextpage_level >0 && $nextpage_level <7)
			update_post_meta($post_id, 'nextpage_level', $nextpage_level);
		
		$toc_level = (int) sanitize_text_field($_POST['bg_bpub_toc_level']);
		if ($toc_level >0 && $toc_level <7)
			update_post_meta($post_id, 'toc_level', $toc_level);
		
		$book_author = sanitize_text_field($_POST['bg_bpub_book_author']); 
		$book_author = esc_html($book_author);
		update_post_meta($post_id, 'book_author', $book_author);
	} else {
		update_post_meta($post_id, 'the_book', '');
	}
	return $post_id;		
}

