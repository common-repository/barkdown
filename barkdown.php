<?php

/*
Plugin Name: Barkdown
Plugin URI:  https://developer.wordpress.org/plugins/the-basics/
Description: Converts your default editor into a <strong>simple</strong> markdown editor. No frills.
Version:     1.2
Author:      Tyler Pope
Author URI:  https://tylerpope.net/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 
Domain Path: /languages
*/

if(!class_exists('MarkdownExtra_Parser') || !class_exists('Markdown_Parser')){
    require('markdown.php');
}

add_filter('quicktags_settings', 'barkdown_md_quicktags');
function barkdown_md_quicktags( $qtInit  ) {
    //Set to emtpy string, empty array or false won't work. It must be set to ","
    $qtInit['buttons'] = ',';
    return $qtInit;
}

function bd_markdown_add_quicktags() {
    if (wp_script_is('quicktags')){
?>
    <script type="text/javascript">
    QTags.addButton( 'eg_h1', 'h1', '#', ' ', 'h1', 'h1 tag', 1 );
    QTags.addButton( 'eg_h2', 'h2', '##', ' ', 'h2', 'h2 tag', 1 );
    QTags.addButton( 'eg_h3', 'h3', '###', ' ', 'h3', 'h3 tag', 1 );
    QTags.addButton( 'eg_strong', 'strong', '__', '__', 'strong', 'strong tag', 1 );
    QTags.addButton( 'eg_ital', 'italics', '_', '_', 'italics', 'italics tag', 1 );
    QTags.addButton( 'eg_code', 'code', '~~~\n', '\n~~~', 'code', 'code tag', 1 );
    </script>
<?php
    }
}

function bd_markdown_parse_markdown($content) {
	return Markdown($content);
}

function bd_markdown_revert_posts(){
    $post_types = get_post_types();
    $all_posts = get_posts(
        array(
            'post_type' => $post_types,
            'posts_per_page' => -1
        )
    );

    foreach($all_posts as $post){
        $content = Markdown($post->post_content);
        wp_update_post(
            array(
                'ID' => $post->ID,
                'post_content' => $content
            )
        );
    }
}

register_deactivation_hook( __file__, bd_markdown_revert_posts );

remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'bd_markdown_parse_markdown' );
add_filter( 'user_can_richedit' , '__return_false', 50 );
add_action( 'admin_print_footer_scripts', 'bd_markdown_add_quicktags' );