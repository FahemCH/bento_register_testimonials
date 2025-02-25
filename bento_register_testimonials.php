<?php
/*
Plugin Name: My Coachin Testimonials
Description: Un plugin pour gérer et afficher les témoignages pour My Coach-in.
Version: 1.0
Author: Bento Digital
Author URI: https://bentodigital.dz/
License: GPL2
*/

// Enregistrement du type de contenu personnalisé pour les témoignages
function bento_register_testimonials() {
    $labels = array(
        'name'               => 'Témoignages',
        'singular_name'      => 'Témoignage',
        'menu_name'          => 'Témoignages',
        'add_new'            => 'Ajouter nouveau',
        'add_new_item'       => 'Ajouter un nouveau témoignage',
        'edit_item'          => 'Modifier le témoignage',
        'new_item'           => 'Nouveau témoignage',
        'view_item'          => 'Voir le témoignage',
        'all_items'          => 'Tous les témoignages',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'menu_icon'          => 'dashicons-testimonial',
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
    );
    register_post_type('testimonial', $args);
}
add_action('init', 'bento_register_testimonials');

// Shortcode pour afficher les témoignages
function bento_testimonials_shortcode() {
    $args = array(
        'post_type'      => 'testimonial',
        'posts_per_page' => -1
    );
    $testimonials = new WP_Query($args);
    
    // Inline CSS to enforce equal heights on testimonial cards
    $output = '
    <style>
    .owl-carousel.testimonials-container {
        gap: 24px;
    }
    .owl-carousel.testimonials-container .testimonial-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 348px;
        height: 317px;
        box-sizing: border-box;
    }
    .owl-carousel.testimonials-container .testimonial-card .text {
        flex: 1;
        overflow: hidden;
    }
    .owl-carousel.testimonials-container .testimonial-card img.logo {
        max-width: 48px;
        max-height: 48px;
        border-radius: 48px;
    }
    </style>
    ';
    
    // Add owl-carousel class to the container
    $output .= '<div class="owl-carousel testimonials-container">';

    while ($testimonials->have_posts()) {
        $testimonials->the_post();
        $logo = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'wp-content/themes/themename/assets/img/placeholder.webp';
        $secondary = get_post_meta(get_the_ID(), 'secondary_text', true);
        $primary = get_post_meta(get_the_ID(), 'primary_text', true);
        $body = get_the_content();

        // Each testimonial card gets an extra "item" class for Owl Carousel
        $output .= '<div class="item testimonial-card">';
        $output .= '<img class="logo" src="' . esc_url($logo) . '" alt="Logo">';
        $output .= '<div class="text">';
        $output .= '<p class="secondary">' . esc_html($secondary) . '</p>';
        $output .= '<p class="primary">' . esc_html($primary) . '</p>';
        $output .= '<p class="body">' . esc_html($body) . '</p>';
        $output .= '</div></div>';
    }

    wp_reset_postdata();
    $output .= '</div>';
    
    // Inline script to initialize Owl Carousel with autoplay and dots (bullets) only
    $output .= '
    <script>
    jQuery(document).ready(function($) {
        $(".owl-carousel.testimonials-container").owlCarousel({
            items: 3,
            loop: true,
            margin: 10,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            nav: false,
            dots: true
        });
    });
    </script>
    ';

    return $output;
}
add_shortcode("bento_testimonials", "bento_testimonials_shortcode");




// Ajouter une meta box pour les champs personnalisés
function bento_testimonial_meta_box() {
    add_meta_box('testimonial_meta', 'Détails du témoignage', 'bento_testimonial_meta_callback', 'testimonial', 'normal', 'high');
}
add_action('add_meta_boxes', 'bento_testimonial_meta_box');

function bento_testimonial_meta_callback($post) {
    $secondary_text = get_post_meta($post->ID, 'secondary_text', true);
    $primary_text = get_post_meta($post->ID, 'primary_text', true);
    echo '<label>Texte secondaire :</label><br />';
    echo '<input type="text" name="secondary_text" value="' . esc_attr($secondary_text) . '" class="widefat" />';
    echo '<br /><br /><label>Texte principal :</label><br />';
    echo '<input type="text" name="primary_text" value="' . esc_attr($primary_text) . '" class="widefat" />';
}

function bento_save_testimonial_meta($post_id) {
    if (array_key_exists('secondary_text', $_POST)) {
        update_post_meta($post_id, 'secondary_text', sanitize_text_field($_POST['secondary_text']));
    }
    if (array_key_exists('primary_text', $_POST)) {
        update_post_meta($post_id, 'primary_text', sanitize_text_field($_POST['primary_text']));
    }
}
add_action('save_post', 'bento_save_testimonial_meta');
