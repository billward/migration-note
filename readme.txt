=== Migration Note ===
Contributors: billward
Tags: migration, attribution, shortcode, content, footer
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A discreet "Migrated from..." attribution footer for posts that were imported from another site.

== Description ==

Migration Note provides a single shortcode, `[migration_note]`, for marking a post as having been migrated from another site. It is designed for bloggers who consolidate content from old sites into a new WordPress install and want to credit the original source without cluttering the post body.

The output is a small italic line styled like a footer, with an optional link to the original source, an optional original-publish date, and an optional freeform note for editorial context (e.g. "lightly edited for clarity").

= Usage =

`[migration_note source="My Old Blog" url="https://oldblog.example.com/post" date="January 5, 2018" note="; lightly edited for clarity"]`

All attributes are optional except `source`. If `source` is omitted, the shortcode renders nothing.

* **source** — name of the original site. Rendered as plain text, or as a link if `url` is also provided.
* **url** — URL of the original post. When set, the source name becomes a link with `target="_blank" rel="noopener noreferrer"`.
* **date** — original publication date, rendered after the source.
* **note** — freeform editorial text appended directly to the rendered footer with no separator. Accepts the same inline HTML allowed in post content (emphasis, links, entities). Begin the value with whatever punctuation or whitespace you want between the date and the note, e.g. `note="; ..."`, `note=", ..."`, or `note="&mdash;..."`.

= Features =

* Single shortcode, no extra fields to fill in.
* Customizable rendering via **Settings → Migration Note** — change the wording, add or drop "on", reformat dates, translate the footer to your language, all without code or translation files.
* Stylesheet only loads on pages where the shortcode is actually used.
* Output is a single class-scoped block (`.migration-note`) so it's easy to restyle in your theme.
* `migration_note_html` filter lets advanced users override the rendered HTML without forking.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/migration-note`, or install through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen.
3. Add `[migration_note ...]` to any post or page where you want an attribution footer.

== Frequently Asked Questions ==

= Can I change the appearance? =

Yes — override the `.migration-note` CSS class in your theme's stylesheet. For example, to left-align the note:

`.migration-note { text-align: left; }`

= Does the link open in a new tab? =

Yes. The link uses `target="_blank"` together with `rel="noopener noreferrer"` for safety.

= Can I translate or change the "Migrated from" text? =

Yes — go to **Settings → Migration Note** and edit the **Template** field. The template is a single line of text with placeholders (`%source%`, `%link%`, `%date%`, `%note%`); rewrite it in any language or any wording you like. There's no need for translation files.

= How do I change the date format? =

**Settings → Migration Note** → **Date format**. The default is `%Y-%m-%d` with an " on " prefix; change to e.g. `%B %-d, %Y` for "January 5, 2018" or anything else (see the help text below the field for codes). Leave blank to render the date attribute exactly as supplied with no formatting or prefix.

= How do I customize the rendered HTML? =

Hook the `migration_note_html` filter:

`add_filter('migration_note_html', function ($html, $atts) { /* ... */ return $html; }, 10, 2);`

== Changelog ==

= 1.1.0 =
* New: **Settings → Migration Note** page with editable Template and Date format fields. Replaces the previous translation-file approach — admins customize wording directly. Per-site settings on multisite.
* New: `%link%` template placeholder (auto-wraps the source name in `<a>` when `url` is supplied, falls back to plain text when not).
* Defaults preserve v1.0.0 output for posts with ISO-formatted (`YYYY-MM-DD`) date attributes.
* Removed: redundant `load_plugin_textdomain()` call (translations on WordPress.org-hosted plugins auto-load since WP 4.6).

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.1.0 =
Adds a Settings → Migration Note page so you can edit the rendered text directly. Defaults preserve v1.0.0 output; no action required after upgrading.

= 1.0.0 =
Initial release.
