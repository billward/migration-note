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

**CSS** — override `.migration-note` (and `.migration-note a`) in your theme's stylesheet. The plugin's stylesheet is only enqueued on pages that actually use the shortcode.

**HTML filter** — hook `migration_note_html` to rewrite the rendered output:

```php
add_filter('migration_note_html', function ($html, $atts) {
    // return your version of $html
    return $html;
}, 10, 2);
```

**Translation** — strings are loaded under the `migration-note` text domain from the `languages/` directory.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
