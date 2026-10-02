# Blog Internal Link Fixer 1.1.0 — WPBakery Safe

WPBakery elements are WordPress shortcodes stored in post content. This version scans raw `post_content` for actual HTML anchor `href` values and changes only the selected exact `href`.

It does NOT:
- run WPBakery shortcodes
- use `do_shortcode()`
- parse and reserialize the entire post with DOMDocument
- rewrite shortcode markup
- change WPBakery attributes
- touch external URLs

Workflow:
1. Tools → Blog Link Fixer
2. Scan Blog Posts
3. Review matches
4. Select individual matches or Select All
5. Change Selected Links

A match requires the link to be same-domain and its final URL slug to match a published WordPress `post`. The target post's current canonical permalink becomes the replacement.

Recommended: test on a local/staging clone and make a database backup before bulk changes.
