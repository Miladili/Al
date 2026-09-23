=== Project Showcase Studio ===
Tags: projects, portfolio, elementor, woocommerce, interior design, architecture
Requires at least: 6.3
Requires PHP: 7.4
Stable tag: 3.1.0

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

== 3.1.0 ==
* Project Field, Info, Stats and related widgets resolve library/core keys (`year`, `core_year`, leftover `global:year`) against actual project values.
* Empty widgets show an editor placeholder instead of a blank canvas. They stay silent on the live site until data exists.
* Project Fields library “Add Field” now includes placeholder, default, unit, options and conditional visibility (matching the saved PHP form).
* Gallery masonry/lift layouts and field-library cards were connected to real CSS. Elementor Single Layout editor flow is unchanged.

== Important ==
Projects are data entities. They are not edited as Elementor pages. Elementor is used for reusable Single Project Layouts and Project widgets.

The plugin does not replace site-wide header/footer settings, Elementor global settings, or WooCommerce templates.

== 2.8.0 ==
* There is no mandatory project schema. Field Library stays definitions-only; each Project adds only the fields it needs.
* Add Field is the primary project-editor action. After insert, only the value is edited (reorder, duplicate, collapse, remove).
* Starter Single Layouts use ivory / stone / sand / taupe instead of black as the default visual direction.
* Plugin admin heroes and schema cards are scoped to PSS screens only. Elementor widgets and the Single Layouts hub are unchanged.

== 2.7.0 ==
* Add/Edit Project is a value-only CMS form. Field key/type/options live in Projects → Project Fields.
* Extra project fields are added through “Add field to this project”, then only the value is shown.
* Field types expanded: WYSIWYG, Color, File, Video, Map/Location, plus nested Group inside Repeater/Group.
* Before/After never fatals on missing project, empty fields, deleted attachments or invalid URLs. Manual mode does not require project data.
* Taxonomy selects, Media Library pickers for before/after/floor plan, and scoped admin CSS only.

== 2.6.0 ==
* Showcase card compositions now use different DOM (split, fullscreen overlay, floating, dossier, caption-side, hover-reveal) instead of one article layout with extra classes.
* Title, metadata, index and CTA each have independent placement; the same fields are never printed both on the image and under the card.
* Project Slider is a real engine: slides-per-view, tablet/mobile CSS variables, full-width, autoplay, loop, arrows, dots, progress, swipe, keyboard, peek, fade/scale.
* Added Project Process and Sticky Scroll Story widgets. Existing widgets stay registered.
* Starter layouts Modern — Editorial and Premium — Cinematic include story, process, marquee, awards, team and slider sections using native Elementor section settings.
* Hero, Before/After and Gallery keep Dynamic Project Data and Manual sources.

== 2.5.0 ==
* One Single Layouts hub under Projects (All / Add New / Default / Conditions / Settings).
* Extra optional core fields: contractor, status, budget, completion, photographer, and more.
* Distinct Mosaic and Stacked showcase systems plus Dossier / Atelier / Courtyard cards.
* Horizontal Scroll snap and scrub controls; existing pinned motion remains.
* Field library search on the Project Fields screen.

== 2.4.0 ==
* Single Layout Edit with Elementor opens the linked library document without rewriting the preview onto a Project page.
* Project widgets keep registering even if one widget fails.
* Before/After, Gallery, Image, Video and others accept Project data or manual media.
* Added Reveal, Timeline, Awards, Team, Testimonials, Story, Marquee and Slider widgets.

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
