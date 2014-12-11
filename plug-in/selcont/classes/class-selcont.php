<?php


class NETMODE_Selcont {

    private $type = 'lecture_type';

    function __construct() {
        $this->register_lecture_type();
        $this->metaboxes();
    }

    public function register_lecture_type() {
        $labels = array(
            'name' => 'Lectures',
            'singular_name' => 'Lecture',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New',
            'edit_item' => 'Edit Item',
            'new_item' => 'Add New Item',
            'view_item' => 'View Item',
            'search_items' => 'Search Lectures',
            'not_found' => 'No Lectures Found',
            'not_found_in_trash' => 'No Lectures Found In Trash'
        );

        $args = array(
            'labels' => $labels,
            'query_var' => 'lectures',
            'rewrite' => array(
                'slug' => 'lectures/',
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            //'menu_position' => 25,
            'menu_icon' => admin_url() . 'images/media-button-image.gif',
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt'
            )
        );

        register_post_type($this->type, $args);
    }

    public function metaboxes() {

    }
}
