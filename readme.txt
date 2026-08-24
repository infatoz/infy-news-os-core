=== Infy News OS Core ===
Contributors: infatoz
Donate link: https://infatoz.com
Tags: news, seo, schema, google-news, editorial
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.28
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

News publisher engine for Google Search, News, Discover, and Top Stories.

== Description ==

Infy News OS Core powers the Infy News OS theme: NewsArticle schema, news sitemaps, editorial workflow, live blogs, ads, and newsletter.

Re-uploading this ZIP over an existing install replaces the same folder (`infy-news-os-core`) and keeps settings, posts, and subscribers. Do not rename the folder inside the ZIP.

Production sites receive plugin updates from GitHub ([infatoz/infy-news-os-core](https://github.com/infatoz/infy-news-os-core)). After this version is installed once, use Dashboard → Updates and optionally Enable auto-updates. Push a Version bump to `main`, or publish a GitHub Release whose tag matches the plugin Version (for example `1.6.23`).

== Installation ==

1. Plugins → Add Plugin → Upload Plugin → choose `infy-news-os-core-1.6.28.zip`.
2. If WordPress reports the plugin is already installed, choose **Replace current with uploaded**.
3. Activate Infy News OS Core (it stays active after a replace if it was already active).
4. Install and activate the Infy News OS theme.
5. The setup wizard opens after activation. Install AMP and Web Stories, then pick a site look.

== Changelog ==

= 1.6.28 =
* GitHub updates use the plugin Version header on `main` (a Release tag alone is not enough).
* Dashboard → Updates and the plugin-row “Check GitHub” link clear the updater cache so new versions show immediately.

= 1.6.26 =
* Safe to activate next to a GitHub ZIP leftover folder (`infy-news-os-core-main`) without a fatal.
* GitHub / tag ZIPs rename to `infy-news-os-core` on first upload, not only on replace.

= 1.6.25 =
* Admin dashboard with stack health, stats, and grouped navigation.
* Homepage builder: per-block spacing, background, device visibility, author/offset, CTA and video blocks, duplicate.
* Customizer split into Global, Header, Footer, and Blog panels (Astra-style) with container, type scale, breadcrumbs, and scroll to top.

= 1.6.24 =
* Setup wizard after activation: required plugins, site look, and news essentials.
* Plugin row and Infy News OS menu shortcuts for the wizard, required plugins, and Site look.

= 1.6.23 =
* WordPress updates are fetched from the public GitHub repo instead of infatoz.com.

= 1.6.22 =
* Five Customizer site looks: Editorial, Broadsheet, Magazine, Digital, and News app (hybrid mobile-app chrome).

= 1.6.21 =
* Separate Customizer and Editorial checkboxes for sticky header on desktop and mobile.

= 1.6.20 =
* Mobile menu builder: custom WordPress menu plus search, social, subscribe, widgets, and HTML blocks.

= 1.6.19 =
* Customizer Fonts section: choose local news-portal sans and serif faces (no Google Fonts CDN).

= 1.6.18 =
* Customizer masthead control for logo, site title, and description combinations (caption under the logo is off by default).

= 1.6.17 =
* Article sidebar builder: add, edit, reorder, or remove blocks (ad, trending, widgets, related, newsletter, and more).

= 1.6.16 =
* Customizer Light theme and Dark theme palettes (primary, secondary, background, surface, text, muted, borders, masthead, breaking).

= 1.6.15 =
* Sticky masthead on/off in Customizer → Infy News OS → Colors & subscribe (same setting as Editorial).

= 1.6.14 =
* Article experience copy matches text-size + header dark mode; print is a share-row action.

= 1.6.13 =
* Article related Load more (initial count, batch size, maximum) in Homepage settings and Customizer → Infy News OS → Article.

= 1.6.12 =
* Archive feed labels include relative times (“3 hours ago”) for the news-app listing layout.

= 1.6.11 =
* Archive pagination type (numbered pages, Load more, or infinite scroll) in Editorial settings and Customizer → Infy News OS → Archives.

= 1.6.10 =
* Customizer → Infy News OS → Article can enable or disable the floating share bar (same setting as Editorial).

= 1.6.9 =
* Customizer → Infy News OS → Mobile menu sets drawer background, item, hover, and label colors.

= 1.6.8 =
* Appearance → Customize → Infy News OS can set masthead date, time, formats, and custom text.

= 1.6.7 =
* Customizer and settings drop unused homepage-layout controls (hero rows, section IDs, Web Stories layout). Those live in Homepage builder. Colors, ticker, and site-wide chrome switches stay.

= 1.6.6 =
* Homepage ticker shows latest stories by default (live blogs keep a LIVE chip). Admins can switch to breaking-only, set count, section, label, speed, and homepage-only.

= 1.6.5 =
* Live blogs appear in section archives, trending, related, Google News RSS (skipping noindex), and AMP live coverage polls with amp-live-list. Homepage module switches in Infy News OS settings now actually hide builder blocks.

= 1.6.4 =
* Live blogs mix into Latest and the breaking ticker, with a pulsing LIVE chip on cards and ticker items.

= 1.6.3 =
* Live blogs show the update feed first while coverage is open, poll for new posts, and let each update use the device share sheet.

= 1.6.2 =
* Force official AMP plugin into Transitional mode with /amp/ path URLs so canonical pages stay the default theme.

= 1.6.1 =
* AMP-safe theme pairing, official Web Stories in Search sitemaps, and a fuller demo import (articles, live blogs, Web Stories, media, homepage modules).

= 1.6.0 =
* Editorial magazine theme pairing, archive SEO, and safe ZIP replace upgrades (settings preserved).

= 1.5.1 =
* Archive SEO titles, robots, and CollectionPage schema.

= 1.0.0 =
* Initial release.
