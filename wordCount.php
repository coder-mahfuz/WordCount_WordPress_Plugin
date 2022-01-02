<?php

/*

Plugin Name: Word Count
Description: A plugin that counts words, characters and read time.
Version: 1.0
Author: Mahfuzur Rahman
Author URI: https://github.com/coder-mahfuz

*/

class WordCountPlugin {

    function __construct(){
        add_action('admin_menu', array($this, 'count_words'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
    }



    function ifWrap($content){
        if (is_main_query() AND is_single() AND
        (
            get_option('wcp_wordcount', '1') OR
            get_option('wcp_charactercount', '1') OR
            get_option('wcp_readtime', '1')    
        )) {
            return $this-> createHtml($content);
        }
        return $content;
    }


function createHtml($content){
$html = '<h3>'. esc_html(get_option('wcp_headline', 'Post Statistics')) .'</h3><p>';


//get word count if word count or read time is set to yes
if (get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')) {
    $wordCount = str_word_count(strip_tags($content));
    $characterCount = strlen(strip_tags($content));
}

if (get_option('wcp_wordcount', '1')) {
    $html .= 'This post has ' . $wordCount . ' words.<br>';
}

if (get_option('wcp_charactercount', '1')) {
    $html .= 'This post has ' . $characterCount . ' characters.<br>';
}
if (get_option('wcp_readtime', '1')) {
    $html .= 'This post will take about ' . round($wordCount/245) . ' minute(s) to read.<br>';
}


$html .= '</p>';

if (get_option('wcp_location', '0') == '0') {
    return $html . $content;
}

return $content . $html;

}


function settings(){
    add_settings_section('wcp_first_section', null, null, 'word-count-settings');

    /* Display Location Field */
    add_settings_field('wcp_location', 'Display location', array($this, 'locationHtml'), 'word-count-settings', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_location', array($this, 'sanitize_callback' => array($this, 'sanitize_location'), 'default' => '0'));

    /* Headline Text Field */
    add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHtml'), 'word-count-settings', 'wcp_first_section');
    register_setting('wordcountplugin', 'wcp_headline', array($this, 'sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

    /* word count Field */
    add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHtml'), 'word-count-settings', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
    register_setting('wordcountplugin', 'wcp_wordcount', array($this, 'sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    /* Character count Field */
    add_settings_field('wcp_charactercount', 'Character Count', array($this, 'checkboxHtml'), 'word-count-settings', 'wcp_first_section', array('theName' => 'wcp_charactercount'));
    register_setting('wordcountplugin', 'wcp_charactercount', array($this, 'sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

    /* Readtime Field */
    add_settings_field('wcp_readtime', 'Read Time', array($this, 'checkboxHtml'), 'word-count-settings', 'wcp_first_section', array('theName' => 'wcp_readtime'));
    register_setting('wordcountplugin', 'wcp_readtime', array($this, 'sanitize_callback' => 'sanitize_text_field', 'default' => '1'));
}


function sanitize_location($input) {
    if ($input != '1' AND $input != '0') {
        add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either Beginning of post or End of post');

        return get_option('wcp_location');
    }

    return $input;
}


/* Reusable checkbox field */

function checkboxHtml($args){ ?>

<input type="checkbox" name="<?php echo $args['theName'] ?>" value="1"<?php checked(get_option($args['theName']), '1') ?>>

<?php }



/* Html for location field */
function locationHtml(){ ?>
<select name="wcp_location">
    <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
    <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of post</option>
</select>
<?php }


/* Html for Text field */
function headlineHtml(){ ?>

<input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">

<?php }

function count_words(){
    add_options_page('word count settings', 'word count', 'manage_options', 'word-count-settings', array($this, 'settingsHtml'));
}


function settingsHtml(){ ?>
<div class="wrap">
    <h1>Word Count Settings</h1>
    <form action='options.php' method= 'POST'>
<?php
settings_fields('wordcountplugin');
do_settings_sections('word-count-settings');
submit_button();
?>

    </form>
</div>
<?php }
}

$wordCountPlugin = new WordCountPlugin();