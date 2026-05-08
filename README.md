# Migration Note

A small WordPress plugin that adds a discreet *"Migrated from..."* attribution footer to posts imported from another site.

## Usage

Activate the plugin, then drop a `[migration_note ...]` shortcode anywhere in your post content:

```
[migration_note source="My Old Blog"
                url="https://oldblog.example.com/post"
                date="January 5, 2018"
                note="; lightly edited for clarity"]
```

Renders an italic footer line below your post body, e.g.:

> *Migrated from My Old Blog on January 5, 2018; lightly edited for clarity*

If `source` is omitted, the shortcode renders nothing.

### Attributes

- **`source`** *(required)* — Name of the original site. Rendered as plain text, or as a link if `url` is set.
- **`url`** — URL of the original post. The source name becomes the link text (`target="_blank"`, `rel="noopener noreferrer"`).
- **`date`** — Original publication date. Free-form string, rendered after the source.
- **`note`** — Freeform editorial text appended after the date with **no separator added**. Begin the value with whatever punctuation you want, e.g. `note="; ..."`, `note=", ..."`, or `note="&mdash;..."`. Accepts the inline HTML allowed in WordPress post content.

## Installation

- **WordPress.org plugin directory** *(pending submission)* — Plugins → Add New → search "Migration Note" → Install.
- **Manual ZIP upload** — download a ZIP from the [Releases page](https://github.com/billward/migration-note/releases) and upload via Plugins → Add New → Upload Plugin.
- **From git** — `cd wp-content/plugins && git clone https://github.com/billward/migration-note.git`

Activate from the Plugins screen.

## Customization

**Wording / language / date format** — visit **Settings → Migration Note** in the WP admin. Two fields:

- **Template** (default: `Migrated from %link%%date%%note%`) — the line that gets rendered. Placeholders: `%source%`, `%url%`, `%link%` (auto-linked source), `%date%` (formatted date phrase), `%note%`.
- **Date format** (default: ` on %Y-%m-%d`) — how `%date%` is rendered when the post supplies a parseable date. strftime-style codes (`%Y`, `%m`, `%d`, `%B`, `%-d`, etc.); literal text is preserved as-is. Leave blank for verbatim. The entire phrase including any literal prefix collapses out when the post has no date.

Settings are per-site on multisite installs. Defaults reproduce the original "Migrated from … on YYYY-MM-DD" output exactly.

**CSS** — override `.migration-note` (and `.migration-note a`) in your theme's stylesheet. The plugin's stylesheet is only enqueued on pages that actually use the shortcode.

**HTML filter** — hook `migration_note_html` to rewrite the rendered output:

```php
add_filter('migration_note_html', function ($html, $atts) {
    // return your version of $html
    return $html;
}, 10, 2);
```

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
