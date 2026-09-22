# Project Showcase Studio v2.3.0

A native WordPress Project Manager + flexible Custom Field engine + Elementor Single Layout system + modern Project Showcase + WooCommerce relation layer.

## v1.3.0 focus

- Project-specific fields in addition to reusable global fields.
- Different field types/options per Project.
- Project card field selection with per-project ordering.
- New modern Card presets and motion modes.
- New Elementor widgets: Project Field, Project Hero, Project Stats, Project CTA, Project Breadcrumbs, Project Share.
- Dynamic support for project-only custom fields.
- WooCommerce remains optional and separate from Project data.
- No JetEngine, ACF or Meta Box dependency.

## User workflow

1. **Projects → Add New** creates a Project as data inside WordPress Admin.
2. Global reusable fields can be filled, or Project-only fields can be created for this specific Project.
3. **Card display fields** lets the editor choose and order any core or custom field that should appear on Showcase cards.
4. **Projects → Single Layouts** defines reusable Elementor layouts and conditions.
5. Single Project layouts render the current Project's data without turning the Project itself into an Elementor page.

## Compatibility

- WordPress 6.3+
- PHP 7.4+
- Elementor / Elementor Pro when installed
- WooCommerce when installed

## Important

This package is a development/test build. Real runtime testing should be performed on a staging WordPress installation with the target Elementor/WooCommerce versions.


## v1.4.0
- Elementor design documents are stored as `elementor_library` templates; Project Layout records remain a separate manager/data layer.
- Single Layout Edit links use Elementor's Document API and preview the selected Project context.
- Elementor widget/category registration is attached directly to the official manager hooks.
- Added Project Services, Materials, Location and Inquiry widgets.
- Added richer Product card transition controls and additional Showcase motion presets.

## v1.4.1 – Elementor editor & widget loading fix
- Single Layout manager records now edit the linked `elementor_library` document rather than the manager CPT.
- Added Elementor Document API URL with a safe library-document fallback URL.
- Elementor widgets register directly on the official widget manager hook with per-widget error isolation and diagnostics.
- Cleaned and fixed Project Services, Materials, Location and Inquiry widgets.
- Product image transitions now honor their configured duration.

## v1.4.1 – Widget & Layout editor repair + UI stage
- Single Layout editing now targets the linked Elementor Library document, with a safe document-ID fallback.
- Project Manager `pss_layout` remains separate from Elementor's editable design document.
- Elementor widget registration is isolated per widget so one faulty widget cannot blank the whole widget panel.
- Added Project Specifications, Project Tags and Project Features widgets with lightweight motion and reduced-motion support.
- Fixed Services, Materials, Location and Inquiry widget render logic.
