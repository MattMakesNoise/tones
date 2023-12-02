<?php

namespace Mattsadev\Tones;

class Tones_Post_Type {
    public static function init() {
        add_action( 'init', [ self::class, 'create_tones_post_type' ] );
        add_action( 'init', [ self::class, 'register_tones_meta' ] );
        add_action( 'add_meta_boxes', [ self::class, 'tones_meta_boxes' ] );
        add_action( 'save_post_tones', [ self::class, 'save_tones_meta_box' ] );
    }

    /**
     * Creates tones custom post type.
     *
     * @return void
     * @since 1.0.0
     */
    public static function create_tones_post_type(): void {
        register_post_type('tones',
            array(
                'labels' => array(
                    'name' => __('Tones'),
                    'singular_name' => __('Tone'),
                ),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array('slug' => 'tones'),
                'menu_icon' => 'dashicons-format-audio'
            )
        );
    }

    /**
     * Registers tones custom fields.
     *
     * @return void
     * @since 1.0.0
     */
    public static function register_tones_meta(): void {
        // Register tone frequency field
        register_post_meta('tones', 'tone_freq', array(
            'type' => 'integer',
            'single' => true,
            'show_in_rest' => true,
        ));

        // Register tone file field
        register_post_meta('tones', 'tone_file', array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
        ));
    }

    /**
     * Adds custom meta boxes to tones custom post type.
     *
     * @return void
     * @since 0.1.0
     */
    public static function tones_meta_boxes(): void {
        add_meta_box(
            'tone-freq',
            'Tone Frequency',
            [ self::class, 'tone_freq_meta_box' ],
            'tones',
            'side',
            'low'
        );

        add_meta_box(
            'tone-file',
            'Tone File',
            [ self::class, 'tone_file_meta_box' ],
            'tones',
            'side',
            'low'
        );
    }

    /**
     * Outputs tone frequency meta box.
     *
     * @return void
     * @since 0.1.0
     */
    public static function tone_freq_meta_box(): void {
        global $post;
        $freq = get_post_meta($post->ID, 'tone_freq', true);
        ?>
        <label for="tone_freq">Tone Freq</label>
        <input type="number" name="tone_freq" id="tone_freq" value="<?php echo $freq; ?>" step="1">
        <?php
    }

    /**
     * Outputs tone file meta box.
     *
     * @return void
     * @since 0.1.0
     */
    public static function tone_file_meta_box(): void {
        global $post;
        $file = get_post_meta($post->ID, 'tone_file', true);
        ?>
        <label for="tone_file">Tone File</label>
        <input type="text" name="tone_file" id="tone_file" value="<?php echo $file; ?>">
        <?php
    }

    /**
     * Saves tone freq and tone file meta boxes.
     *
     * @since 1.0.0
     *
     * @param int $post_id The ID of the post being saved.
     *
     * @return void
     */
    public static function save_tones_meta_box( int $post_id ): void {
        if ( array_key_exists( 'tone_freq', $_POST ) ) {
            update_post_meta(
                $post_id,
                'tone_freq',
                $_POST[ 'tone_freq' ]
            );
        }

        if ( array_key_exists( 'tone_file', $_POST ) ) {
            update_post_meta(
                $post_id,
                'tone_file',
                $_POST[ 'tone_file' ]
            );
        }
    }
}