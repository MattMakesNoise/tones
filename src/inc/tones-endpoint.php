<?php

namespace Mattsadev\Tones;

use WP_Error;
use WP_HTTP_Response;

class Tones_Endpoint {
    public static function init(): void {
        add_action( 'rest_api_init', [ self::class, 'register_tones_endpoint' ] );
    }

    /**
     * Registers tones endpoint.
     *
     * @return void
     * @since 1.0.0
     */
    public static function register_tones_endpoint(): void {
        register_rest_route( 'tones/v1', '/list', array(
            'methods' => 'GET',
            'callback' => [ self::class, 'get_tones_list' ],
        ) );
    }

    /**
     * Gets tones from database.
     *
     * @since 1.0.0
     * @return WP_HTTP_Response|\WP_REST_Response|WP_Error
     */
    public static function get_tones_list(): WP_HTTP_Response|\WP_REST_Response|WP_Error {
        $args = array(
            'post_type' => 'tones',
            'posts_per_page' => -1,
        );

        $query = new \WP_Query( $args );

        $tones = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $tones[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'frequency' => get_post_meta( get_the_ID(), 'tone_freq', true ),
                    'file' => get_post_meta( get_the_ID(), 'tone_file', true ),
                    'full_url' => Main::instance()->url . 'assets/mp3/' . sanitize_file_name( get_post_meta( get_the_ID(), 'tone_file', true ) ) . '_-6dBFS_5s.mp3',
                );
            }
        }

        return rest_ensure_response( $tones );
    }
}