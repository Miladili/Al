=== Project Showcase Studio ===
Tags: projects, portfolio, elementor, woocommerce, interior design, architecture
Requires at least: 6.3
Requires PHP: 7.4
Stable tag: 2.3.0

Manage Projects as independent data, define reusable Custom Fields, build reusable Single Project Layouts with Elementor, and display modern project cards.

== Core workflow ==
1. Projects → Project Fields: define reusable project fields.
2. Projects → Add New: create project data without opening Elementor.
3. Projects → Single Layouts: create or duplicate reusable Single Project layouts.
4. Edit the layout with Elementor and use Project widgets + normal Elementor widgets.
5. Select a preview project for the Elementor editor.
6. Assign layout conditions or override a layout per project.
7. Use Project Showcase to display cards that link automatically to each Project Single URL.
8. Optionally connect WooCommerce products to Projects.

== Requirements ==
* WordPress 6.3+
* PHP 7.4+
* Elementor is recommended for the visual layout builder and widgets.
* Elementor Pro is required by Elementor for its native active Dynamic Tags UI; the plugin also provides project widgets and a Project Custom Field widget.
* WooCommerce is optional and only required for Project Products integration.

== Important ==
Projects are data entities. They are not edited as Elementor pages. Elementor is used for reusable Single Project Layouts and Project widgets.

The plugin does not replace site-wide header/footer settings, Elementor global settings, or WooCommerce templates.

== 2.3.0 ==
* Showcase cards never repeat the same fields on the image and under the card.
* Added Project info placement control and skipped `%token%` placeholders.
* Added Elementor responsive card controls and extra hover styles.
* Rebuilt Modern — Editorial and Premium — Cinematic starter layouts as designed landing pages.
* Added Project Horizontal Scroll widget (pinned panels, reduced-motion, mobile stack/swipe).
* Single Layout Edit with Elementor still opens the Elementor Library document only.

== 1.2.0 ==
* Added ALL/ANY Single Layout condition logic with specificity scoring.
* Added Custom Field based layout conditions.
* Added persistent Elementor preview Project context for Single Layouts.
* Added broader Project Dynamic Tags including image, description and meta.
* Added conditional visibility rules to reusable Project Fields.
* Single Project rendering now temporarily uses the current Project as the WordPress global post so compatible Elementor/dynamic widgets see the correct context.

== 1.5.0 ==
* Hardened Elementor integration to boot only after Elementor initialization.
* Removed the unsafe DOMContentLoaded re-fire from the Elementor asset.
* Moved Project preview URL handling to Elementor's WP preview URL filter.
* Removed global preview-context mutation of the WordPress `$post`.
* Prevented Elementor library synchronization from running on every normal WordPress request.
* Kept normal theme/header/footer architecture untouched outside Project Single rendering.


1.7.0 Stability/UI update: safer Elementor lifecycle, fallback widget category visibility, no automatic Elementor library sync during editor boot, and redesigned Modern/Premium Single Project starter layouts.
