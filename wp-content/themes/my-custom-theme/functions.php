<?php
    
if(! function_exists('my_theme_setup')){
    function my_theme_setup(){
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo');
        add_theme_support('woocommerce');
        add_theme_support('html-5',array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption'
        ));
        register_nav_menus(
            array(
                'primary' => __('Primary Menu','my-custom-theme'),
                'footer' => __('Footer Menu','my-custom-theme'),
            )
        );
    }
}
add_action('after_setup_theme','my_theme_setup');

if(!function_exists('my_theme_assests')){
    function my_theme_assests() {
     wp_enqueue_style(
        'main-style',
        get_stylesheet_uri()
    );
    wp_enqueue_style('theme-main',get_template_directory_uri().'/assets/css/main.css',array(), false,'(min-width: 481px) and (max-width: 767px)');
    wp_enqueue_script('main-js',get_template_directory_uri().'/assets/js/main.js',array(),'1.0',true);
    }
}
add_action('wp_enqueue_scripts','my_theme_assests');