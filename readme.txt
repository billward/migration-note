=== Migration Note ===
Contributors: billward
Tags: migration, attribution, shortcode, content, footer
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.0.0
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

* Single, focused shortcode — no settings page, no bloat.
* Translatable (text domain `migration-note`).
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

= Can I translate the "Migrated from" text? =

Yes. The plugin loads translations from its `languages/` directory using the `migration-note` text domain.

= How do I customize the rendered HTML? =

Hook the `migration_note_html` filter:

`add_filter('migration_note_html', function ($html, $atts) { /* ... */ return $html; }, 10, 2);`

== Changelog ==

= 1.0.0 =
* Initial public release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
