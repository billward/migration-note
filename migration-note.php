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
 * Description:       Adds a [migration_note source="..." url="..." date="..." note="..."] shortcode that renders a discreet attribution footer for posts migrated from another site. Customize the rendering under Settings → Migration Note.
 * Version:           1.1.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Bill Ward
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       migration-note
 */

defined('ABSPATH') || exit;

define('MIGRATION_NOTE_VERSION', '1.1.0');

/**
 * Defaults are constructed to render byte-identical output to v1.0 on existing
 * posts whose date attribute is ISO-formatted (or empty / non-parseable).
 */
function migration_note_default_options() {
    return array(
        'template'    => 'Migrated from %link%%date%%note%',
        'date_format' => ' on %Y-%m-%d',
    );
}

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

/**
 * Convert a strftime-style format string to a PHP date()-compatible one.
 * Strftime tokens (`%Y`, `%-d`, etc.) translate to PHP date codes; every other
 * character is backslash-escaped so date_i18n() preserves it verbatim
 * (otherwise letters in literal phrases like " on " get interpreted as date codes).
 */
function migration_note_strftime_to_date($strftime_format) {
    static $map = array(
        '%Y' => 'Y', '%y' => 'y',
        '%m' => 'm', '%-m' => 'n',
        '%d' => 'd', '%-d' => 'j', '%e' => 'j',
        '%H' => 'H', '%-H' => 'G', '%I' => 'h', '%-I' => 'g',
        '%M' => 'i', '%S' => 's',
        '%p' => 'A', '%P' => 'a',
        '%B' => 'F', '%b' => 'M',
        '%A' => 'l', '%a' => 'D',
        '%j' => 'z',
        '%%' => '%',
    );

    $out = '';
    $i   = 0;
    $len = strlen($strftime_format);
    while ($i < $len) {
        if ($strftime_format[$i] === '%' && $i + 1 < $len) {
            $three = substr($strftime_format, $i, 3);
            if (isset($map[$three])) {
                $out .= $map[$three];
                $i   += 3;
                continue;
            }
            $two = substr($strftime_format, $i, 2);
            if (isset($map[$two])) {
                $out .= $map[$two];
                $i   += 2;
                continue;
            }
        }
        $out .= '\\' . $strftime_format[$i];
        $i++;
    }
    return $out;
}

/**
 * Render a date attribute using the configured strftime-style format.
 * Empty input → empty output (lets the entire date phrase, including any
 * leading literal like " on ", collapse out of the rendered template).
 * Unparseable input → verbatim attribute, exposed for filtering.
 */
function migration_note_format_date($date_attr, $strftime_format) {
    $date_attr = trim((string) $date_attr);
    if ($date_attr === '') {
        return '';
    }
    if ($strftime_format === '') {
        return esc_html($date_attr);
    }
    // %v = verbatim. Lets the user keep the source date string untouched while still benefiting
    // from the empty-collapse trick (e.g. format ' on %v' renders ' on Jan 5, 2018' when set, empty when not).
    if (strpos($strftime_format, '%v') !== false) {
        return esc_html(str_replace('%v', $date_attr, $strftime_format));
    }
    $timestamp = strtotime($date_attr);
    if ($timestamp === false) {
        // Unparseable input: keep the literal prefix from the format and append the verbatim date.
        $prefix = preg_match('/^([^%]*)/', $strftime_format, $m) ? $m[1] : '';
        $fallback = esc_html($prefix . $date_attr);
        return apply_filters('migration_note_unparseable_date', $fallback, $date_attr, $strftime_format);
    }
    return esc_html(date_i18n(migration_note_strftime_to_date($strftime_format), $timestamp));
}

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

    $url       = esc_url(trim($atts['url']));
    $source_h  = esc_html($source);
    $note_safe = wp_kses_post(trim($atts['note']));
    $link      = $url !== ''
        ? '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $source_h . '</a>'
        : $source_h;

    $opts        = wp_parse_args(get_option('migration_note_options', array()), migration_note_default_options());
    $date_phrase = migration_note_format_date($atts['date'], $opts['date_format']);

    $body = strtr($opts['template'], array(
        '%source%' => $source_h,
        '%url%'    => $url,
        '%link%'   => $link,
        '%date%'   => $date_phrase,
        '%note%'   => $note_safe,
    ));

    $html = '<p class="migration-note"><em>' . $body . '</em></p>';

    return apply_filters('migration_note_html', $html, $atts);
}
add_shortcode('migration_note', 'migration_note_render');

// ---------------------------------------------------------------------------
// Settings page (Settings → Migration Note)
// ---------------------------------------------------------------------------

function migration_note_register_settings() {
    register_setting(
        'migration_note',
        'migration_note_options',
        array(
            'type'              => 'array',
            'sanitize_callback' => 'migration_note_sanitize_options',
            'default'           => migration_note_default_options(),
        )
    );

    add_settings_section('migration_note_main', '', '__return_false', 'migration-note');

    add_settings_field(
        'template',
        __('Template', 'migration-note'),
        'migration_note_field_template',
        'migration-note',
        'migration_note_main'
    );

    add_settings_field(
        'date_format',
        __('Date format', 'migration-note'),
        'migration_note_field_date_format',
        'migration-note',
        'migration_note_main'
    );
}
add_action('admin_init', 'migration_note_register_settings');

function migration_note_sanitize_options($input) {
    $defaults = migration_note_default_options();
    $template = isset($input['template']) ? wp_kses_post(trim((string) $input['template'])) : '';
    $format   = isset($input['date_format']) ? wp_strip_all_tags((string) $input['date_format']) : '';

    return array(
        'template'    => $template !== '' ? $template : $defaults['template'],
        'date_format' => $format,
    );
}

function migration_note_field_template() {
    $opts = wp_parse_args(get_option('migration_note_options', array()), migration_note_default_options());
    printf(
        '<textarea id="migration_note_template" name="migration_note_options[template]" rows="3" cols="70" class="large-text code">%s</textarea>',
        esc_textarea($opts['template'])
    );
    echo '<p class="description">' . esc_html__('Placeholders:', 'migration-note') . '</p>';
    echo '<ul class="description" style="margin-left:1.5em;list-style:disc">';
    echo '<li><code>%source%</code> &mdash; ' . esc_html__('plain source name', 'migration-note') . '</li>';
    echo '<li><code>%url%</code> &mdash; ' . esc_html__('raw URL (or empty)', 'migration-note') . '</li>';
    echo '<li><code>%link%</code> &mdash; ' . esc_html__('source name as a link when URL is set, otherwise plain text', 'migration-note') . '</li>';
    echo '<li><code>%date%</code> &mdash; ' . esc_html__('formatted date phrase from the Date format setting; empty when the post supplies no date', 'migration-note') . '</li>';
    echo '<li><code>%note%</code> &mdash; ' . esc_html__('freeform editorial note', 'migration-note') . '</li>';
    echo '</ul>';
}

function migration_note_field_date_format() {
    $opts = wp_parse_args(get_option('migration_note_options', array()), migration_note_default_options());
    printf(
        '<input type="text" id="migration_note_date_format" name="migration_note_options[date_format]" value="%s" class="regular-text code" />',
        esc_attr($opts['date_format'])
    );
    /* translators: %date% is the literal name of the template placeholder and should not be translated. */
    echo '<p class="description">' . esc_html__('Used when %date% is rendered. Literal text (e.g. " on ") is preserved exactly. Common codes:', 'migration-note') . '</p>';
    echo '<ul class="description" style="margin-left:1.5em;list-style:disc">';
    echo '<li><code>%Y</code> ' . esc_html__('4-digit year', 'migration-note') . ' &middot; <code>%y</code> ' . esc_html__('2-digit year', 'migration-note') . '</li>';
    echo '<li><code>%m</code> ' . esc_html__('zero-padded month', 'migration-note') . ' &middot; <code>%-m</code> ' . esc_html__('non-padded', 'migration-note') . ' &middot; <code>%B</code> ' . esc_html__('full month name', 'migration-note') . ' &middot; <code>%b</code> ' . esc_html__('abbreviated', 'migration-note') . '</li>';
    echo '<li><code>%d</code> ' . esc_html__('zero-padded day', 'migration-note') . ' &middot; <code>%-d</code> ' . esc_html__('non-padded', 'migration-note') . '</li>';
    echo '<li><code>%H</code>, <code>%M</code>, <code>%S</code> &mdash; ' . esc_html__('hour, minute, second', 'migration-note') . '</li>';
    echo '<li><code>%v</code> &mdash; ' . esc_html__('verbatim source date string (skips parsing; useful for non-standard dates while keeping a prefix)', 'migration-note') . '</li>';
    echo '</ul>';
    echo '<p class="description">' . esc_html__('Leave blank to render the date attribute verbatim with no prefix.', 'migration-note') . '</p>';
}

function migration_note_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Migration Note', 'migration-note'); ?></h1>
        <p><?php esc_html_e('Customize how the [migration_note] shortcode renders. The defaults reproduce the original "Migrated from … on YYYY-MM-DD" output.', 'migration-note'); ?></p>
        <form method="post" action="options.php">
            <?php
            settings_fields('migration_note');
            do_settings_sections('migration-note');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function migration_note_admin_menu() {
    add_options_page(
        __('Migration Note', 'migration-note'),
        __('Migration Note', 'migration-note'),
        'manage_options',
        'migration-note',
        'migration_note_settings_page'
    );
}
add_action('admin_menu', 'migration_note_admin_menu');
