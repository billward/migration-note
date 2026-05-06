<?php
/**
 * Migration Note
 *
 * @package           MigrationNote
 * @author            Bill Ward
 * @copyright         2026 Bill Ward
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Migration Note
 * Description:       Adds a [migration_note source="..." url="..." date="..." note="..."] shortcode that renders a discreet attribution footer for posts migrated from another site.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Bill Ward
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       migration-note
 */

defined('ABSPATH') || exit;

define('MIGRATION_NOTE_VERSION', '1.0.0');

function migration_note_register_assets() {
    // Register only; the shortcode enqueues lazily so the stylesheet doesn't load on pages without the shortcode.
    wp_register_style(
        'migration-note',
        plugin_dir_url(__FILE__) . 'migration-note.css',
        array(),
        MIGRATION_NOTE_VERSION
    );
}
add_action('wp_enqueue_scripts', 'migration_note_register_assets');

function migration_note_render($atts) {
    $atts = shortcode_atts(array(
        'source' => '',
        'url'    => '',
        'date'   => '',
        'note'   => '',
    ), $atts, 'migration_note');

    $source = trim($atts['source']);
    if ($source === '') {
        return '';
    }

    wp_enqueue_style('migration-note');

    $source_safe = esc_html($source);
    $url         = esc_url(trim($atts['url']));
    $date_safe   = esc_html(trim($atts['date']));
    $note_safe   = wp_kses_post(trim($atts['note']));

    if ($url !== '') {
        $source_safe = '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $source_safe . '</a>';
    }

    if ($date_safe !== '') {
        $body = sprintf(
            /* translators: 1: source site name (optionally a link), 2: original publication date */
            __('Migrated from %1$s on %2$s', 'migration-note'),
            $source_safe,
            $date_safe
        );
    } else {
        $body = sprintf(
            /* translators: %s: source site name (optionally a link) */
            __('Migrated from %s', 'migration-note'),
            $source_safe
        );
    }

    if ($note_safe !== '') {
        // Concatenated with no separator so the author supplies any leading punctuation in the note value itself.
        $body .= $note_safe;
    }

    $html = '<p class="migration-note"><em>' . $body . '</em></p>';

    return apply_filters('migration_note_html', $html, $atts);
}
add_shortcode('migration_note', 'migration_note_render');
