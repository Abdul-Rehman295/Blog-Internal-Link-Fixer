# Blog Internal Link Fixer

**Version:** 1.0.0

## Overview

Blog Internal Link Fixer is a WordPress admin plugin that finds and safely corrects incorrect internal blog links.

For example, if the actual blog URL is:

```text
https://example.com/blog/blog-name/
```

but an internal link points to:

```text
https://example.com/blog-name/
```

the plugin detects the matching blog post and allows you to change the link to the correct URL.

The plugin does **not** automatically change links during scanning. You review the results, select the links you want to change, and then click **Change Selected Links**.

---

## Main Features

* Automatically detects the current WordPress website domain.
* Detects the site's blog URL prefix, normally `/blog/`.
* Scans published WordPress blog posts.
* Finds internal links whose slug matches a published blog post.
* Shows the source blog, existing URL, matched blog, and correct URL.
* Supports selecting individual links.
* Supports **Select All**.
* Does not modify anything during scanning.
* Changes only selected links.
* Ignores external and third-party URLs.
* Ignores `mailto:`, `tel:`, `javascript:`, `data:` and anchor links.
* Does not perform a blind database-wide search and replace.
* Re-validates source and target posts before replacement.

---

# WPBakery Compatibility

The plugin is designed to work with **WPBakery-created blog posts**.

WPBakery stores its content inside WordPress `post_content` using shortcode markup.

Example:

```text
[vc_row]
[vc_column]
[vc_column_text]
<p>
Read our <a href="/water-damage-guide/">water damage guide</a>.
</p>
[/vc_column_text]
[/vc_column]
[/vc_row]
```

The plugin targets the specific HTML anchor `href`.

### Before

```html
<a href="/water-damage-guide/">
```

### After

```html
<a href="/blog/water-damage-guide/">
```

The surrounding WPBakery shortcode structure is preserved.

The plugin does **not** intentionally:

* Run WPBakery shortcodes
* Rebuild WPBakery content
* Convert WPBakery content
* Modify WPBakery shortcode attributes
* Rewrite the entire post as newly generated HTML

This is intended to reduce the risk of damaging WPBakery content.

---

# Gutenberg / Block Editor Compatibility

The plugin also supports normal Gutenberg / Block Editor content.

Example:

```html
<!-- wp:paragraph -->
<p>
Read our <a href="/water-damage-guide/">water damage guide</a>.
</p>
<!-- /wp:paragraph -->
```

The plugin targets the link URL while preserving the surrounding Gutenberg block structure.

---

# Classic Editor Compatibility

The plugin supports Classic Editor posts and normal HTML stored in `post_content`.

Example:

```html
<p>
Read our <a href="/water-damage-guide/">water damage guide</a>.
</p>
```

Only the matching `href` is changed.

---

# Editor Compatibility

| Editor / Content Type          | Supported |
| ------------------------------ | --------- |
| WPBakery                       | Yes       |
| Gutenberg / Block Editor       | Yes       |
| Classic Editor                 | Yes       |
| Normal HTML                    | Yes       |
| Mixed shortcode + HTML content | Yes       |

The plugin works with the WordPress `post_content` rather than depending on one specific visual editor.

---

# Main Page Protection

The plugin does **not** blindly replace URLs throughout your website.

For example, normal pages such as:

```text
/about-us/
/contact/
/services/
/privacy-policy/
```

are not changed simply because they are internal pages.

The plugin first checks whether the URL's slug matches a **published WordPress blog post**.

For example:

```text
/about-us/
```

will not be changed unless there is also a published blog post with the slug:

```text
about-us
```

---

# Third-Party URL Protection

External URLs are ignored.

For example:

```text
https://google.com/
https://facebook.com/
https://example-other-site.com/blog-name/
```

These links are not modified.

The plugin only considers links belonging to the current WordPress website.

---

# URL Types Ignored

The plugin ignores:

```text
#section
mailto:someone@example.com
tel:+123456789
javascript:...
data:...
```

It also ignores external domains.

---

# URL Matching

The plugin can handle absolute URLs:

```text
https://example.com/blog-name/
```

Relative URLs:

```text
/blog-name/
```

URLs with query strings:

```text
/blog-name/?source=test
```

URLs with fragments:

```text
/blog-name/#section
```

The matching process focuses on the URL path and blog post slug.

---

# Example

### Actual published blog

```text
https://example.com/blog/mold-removal-guide/
```

### Incorrect internal link

```text
https://example.com/mold-removal-guide/
```

The plugin recognizes:

```text
mold-removal-guide
```

as the slug of a published blog post.

It then displays:

```text
Source Blog:
Water Damage Guide

Existing Link:
/mold-removal-guide/

Matched Blog:
Mold Removal Guide

Correct URL:
/blog/mold-removal-guide/
```

You can select the result and click:

```text
Change Selected Links
```

The link becomes:

```text
https://example.com/blog/mold-removal-guide/
```

---

# Replacement Method

The plugin uses a **targeted replacement method**.

It does not intentionally rewrite the entire WordPress post into a newly generated HTML document when making a replacement.

Instead, it identifies the specific HTML anchor:

```html
<a href="...">
```

and changes the matching `href`.

This is intended to reduce the risk of changing:

* WPBakery shortcodes
* Gutenberg block comments
* Existing HTML structure
* Unrelated content
* Other links
* Other shortcode data

---

# How the Plugin Works

The process is:

```text
1. Scan published blog posts
        ↓
2. Get published blog post slugs
        ↓
3. Inspect internal links in each post
        ↓
4. Ignore external/third-party URLs
        ↓
5. Ignore unsupported URL types
        ↓
6. Compare the URL slug with published blog slugs
        ↓
7. Ignore links already using the correct blog URL
        ↓
8. Display possible corrections
        ↓
9. Administrator selects links
        ↓
10. Click "Change Selected Links"
        ↓
11. Plugin validates the source/target posts again
        ↓
12. Only selected links are changed
```

---

# Installation

1. Log in to WordPress Admin.
2. Go to:

```text
Plugins → Add New Plugin
```

3. Click:

```text
Upload Plugin
```

4. Upload:

```text
blog-internal-link-fixer-wpbakery-safe.zip
```

5. Activate the plugin.

6. Go to:

```text
Tools → Blog Link Fixer
```

7. Click:

```text
Scan Blog Posts
```

---

# How to Use

## Step 1 — Open the Plugin

Go to:

```text
WordPress Admin
→ Tools
→ Blog Link Fixer
```

## Step 2 — Scan

Click:

```text
Scan Blog Posts
```

The plugin scans published WordPress blog posts.

## Step 3 — Review

Review the detected results.

Each result shows:

* Source Blog
* Existing URL
* Matched Blog
* Correct URL

## Step 4 — Select

Select the links you want to change.

You can select:

* One link
* Multiple links
* All links

## Step 5 — Change

Click:

```text
Change Selected Links
```

The plugin updates only the selected links.

---

# Recommended Local / Staging Test

Before using the plugin on a live website, test it on a local or staging WordPress installation.

Create several published blog posts:

```text
/blog/post-one/
/blog/post-two/
/blog/post-three/
```

Then create another blog post containing:

```text
/post-one/
/post-two/
```

Also add links that should **not** be changed:

```text
/about-us/
/contact/
https://google.com/
```

Run the scanner.

The expected behavior is:

```text
/post-one/       → Detect
/post-two/       → Detect

/about-us/       → Ignore
/contact/        → Ignore
google.com       → Ignore
```

---

# Recommended Editor Tests

For a complete compatibility test, create:

### 1. WPBakery Post

Create a WPBakery blog post containing:

```html
<a href="/post-one/">Post One</a>
```

### 2. Gutenberg Post

Create a Gutenberg post containing:

```html
<a href="/post-two/">Post Two</a>
```

### 3. Classic Editor Post

Create a Classic Editor post containing:

```html
<a href="/post-three/">Post Three</a>
```

Run the scanner.

The plugin should identify the incorrect blog links and propose:

```text
/blog/post-one/
/blog/post-two/
/blog/post-three/
```

After replacement, verify the posts both:

* Inside the WordPress editor
* On the front end

---

# Important WPBakery Test

For WPBakery, it is recommended to inspect the post after replacement.

Before:

```text
[vc_row]
[vc_column]
[vc_column_text]
<p>Read our <a href="/post-one/">Post One</a></p>
[/vc_column_text]
[/vc_column]
[/vc_row]
```

After:

```text
[vc_row]
[vc_column]
[vc_column_text]
<p>Read our <a href="/blog/post-one/">Post One</a></p>
[/vc_column_text]
[/vc_column]
[/vc_row]
```

The WPBakery shortcode structure should remain intact.

---

# Current Scope

The current version scans:

```text
Post Type: post
Status: publish
```

This means the plugin is primarily designed for standard WordPress blog posts.

Custom Post Types are **not automatically scanned** by the current version.

---

# Requirements

Recommended:

* WordPress 5.8 or newer
* PHP 7.4 or newer
* PHP DOM extension enabled
* Administrator-level WordPress permissions

The user running the plugin needs the:

```text
manage_options
```

capability.

---

# Large Websites

If the website contains hundreds or thousands of blog posts, the scan may require higher PHP/server limits.

Potential limits include:

* PHP memory limit
* PHP execution time
* WordPress/server request timeout

For very large websites, background/batched scanning can be added in a future version.

---

# Troubleshooting

## No results are found

Check:

1. The blog posts are published.
2. The links are inside the stored post content.
3. The links belong to the same domain.
4. The final slug matches a published blog post.
5. The current URL differs from the canonical blog permalink.
6. The website uses `/blog/` or another detected blog prefix.

---

## WPBakery Link Is Not Detected

Make sure the link is an actual HTML anchor:

```html
<a href="/post-one/">Post One</a>
```

The plugin looks for anchor `href` links inside the stored post content.

---

## Scan Fails

Check:

* PHP error logs
* WordPress debug logs
* PHP memory limit
* PHP execution time
* PHP DOM extension

---

# Database Safety

The plugin updates post content through WordPress's normal post-update mechanism.

Before running bulk replacements on a production website, it is strongly recommended to create a complete database backup.

For production websites, testing on staging first is recommended.

---

# What the Plugin Does NOT Do

The plugin does not:

* Change external/third-party URLs.
* Automatically rewrite every internal URL.
* Change normal pages simply because they are internal.
* Change images.
* Change CSS URLs.
* Change JavaScript URLs.
* Change email links.
* Change telephone links.
* Change anchor links.
* Run WPBakery shortcodes.
* Convert WPBakery content into another editor format.
* Automatically modify links during scanning.
* Scan custom post types in the current version.

---

# Safety Summary

The plugin follows this basic rule:

```text
Same website?
        ↓
Yes
        ↓
Is the URL's slug a published blog post?
        ↓
Yes
        ↓
Is the URL already correct?
        ↓
No
        ↓
Show it for review
        ↓
User selects it
        ↓
Change only that link
```

This is intentionally different from a global search-and-replace operation.

---

# Production Recommendation

Before using the plugin on a production website:

1. Create a database backup.
2. Test the plugin on local/staging.
3. Test WPBakery content.
4. Test Gutenberg content.
5. Test Classic Editor content.
6. Review the scan results.
7. Start with a small number of selected links.
8. Verify the changed posts.
9. Then process the remaining links.

---

# License

GPL-2.0-or-later
