# Changelog

## 3.2.0
- Before/After no longer prints two images side by side. After sits full-bleed, Before is clipped with `clip-path`, and pointer-drag updates `--pss-ba-pos` (including Elementor preview).
- Frontend JS for comparison, lightbox, filters and slider is delegated / hooked to `elementor/frontend/init` so widgets work after canvas re-render.
- Landing widgets (Timeline, Process, Team, Testimonials, Awards, Story, Showcase, Slider) show defaults or editor placeholders.
- Existing widget IDs stay registered. Library-document Single Layout flow is unchanged.

## 3.1.0
- Widget field keys now resolve `global:` / `core:` / plain keys to stored project values (library fields, local values, and core meta).
- Project Info reads taxonomy + core meta + per-project field values, so Year/Area/Designer no longer look empty after schema-free editing.
- Empty widgets print an Elementor-editor placeholder (`.pss-el-empty`); frontend stays blank until there is data.
- Field Library JS “Add Field” matches the PHP form: placeholder, default, unit, required, visibility, radio/checkbox options.
- Gallery masonry is a real column layout; hover-lift is wired. Plugin-only admin field cards show type/key chips.
- Elementor library-document Single Layout flow is unchanged.

## 3.0.2
- Plugin boot, activation, layout Elementor URLs and widget controls/render are isolated so one PHP error cannot white-screen WordPress or empty the Elementor panel.
- Single Layouts now create a real `elementor_library` document directly (no WP Page create/delete detour).
- Frontend widget JS is no longer enqueued on every Elementor frontend request.

## 3.0.1
- Activation parse error: leftover merge fragment after `class Fields` in `includes/class-fields.php` (fatal on plugin include). Removed.
- Field library save again stores placeholder, default value, and unit.

## 3.0.0
- Deepened thin Elementor widgets (Title, Description, Info, Field, Gallery, Inquiry, Breadcrumbs, Navigation, Custom Fields, Related, Tags, Location, Share, CTA, Image, Video) with connected Style controls: typography, color, hover, spacing, alignment, max-width.
- Field types added: Email, Phone, Radio, Checkbox, Time, Relationship, Icon, plus placeholder / default / unit on field definitions.
- Project media tab uses Media Library pickers for Before, After and Floor Plan (no raw ID-only UI).
- Showcase query supports Latest / Featured / Related. Featured is a project checkbox, not a hardcoded industry schema.
- Archive template uses an optional layout (Elementor library document) or a fallback project grid. No custom Elementor document type.
- Dynamic tags: Project Location and Project Date. Project Documents widget lists file fields or manual downloads.
- Frontend JS no longer loads on every widget; only Showcase, Slider, Gallery, Before/After, Scroll and Sticky request it.
- Elementor library-document Single Layout flow is unchanged.

## 2.8.0
- No mandatory universal project schema. Field Library defines reusable fields; each Project chooses its own attributes via Add Field.
- Project editor starts empty (plus core title / featured image / gallery / taxonomies). Added fields are value-only cards with reorder, duplicate, collapse and remove.
- Existing saved values are recovered without dumping the whole library onto every record.
- Starter Modern and Premium layouts use ivory/stone/sand/taupe section colors instead of black.
- Plugin-only admin chrome (heroes, schema cards) stays under `.pss-admin-screen`. Elementor registration and the single Single Layouts menu are unchanged.

## 2.7.0
- Project editor no longer mixes field definitions with values. Reusable fields are defined once; Add/Edit Project shows a JetEngine-like value form.
- Unique fields are added via “Add field to this project” and then edited as values only.
- Extra field types: WYSIWYG, Color, File, Video, Map. Nested Group is allowed inside structured fields.
- Before/After render is fully defensive (empty project, missing media, invalid URLs, empty repeater, editor preview).
- Classification taxonomies and transformation images use proper selects / Media Library controls.

## 2.6.0
- Card presets now switch composition markup (split, fullscreen, float, dossier, caption-side, hover-reveal, perspective) instead of recoloring one DOM tree.
- Title / meta / index / CTA placements are independent. Overlay content is not repeated below the image.
- Project Slider writes `--pss-slides` and `--pss-gap` through Elementor responsive `selectors`, then a JS engine actually slides, loops, autoplays, peeks, and handles swipe/keyboard.
- Added Project Process and Sticky Scroll Story. No existing widget was removed.
- Starter Single Layouts rebuilt with extra landing-page sections (story, process, marquee, awards, team, slider) while remaining classic Elementor sections/columns.
- Library-document Elementor flow and the single Single Layouts hub are unchanged.

## 2.5.0
- Single Layouts is one Projects submenu: All Layouts, Add New Layout, Default Layout, Conditions, and Settings live as hub tabs. Duplicate “Add Single Layout” / Settings menu items are gone.
- Optional core records expanded (contractor, status, budget, completion, photographer, consultant, engineer) and can appear on cards when selected.
- Project Fields library has a search box; the Project editor toolbar already filters the field library.
- Showcase gained Mosaic and Stacked stories layouts plus Dossier / Atelier / Courtyard card systems. Card meta still never prints both on the image and below.
- Horizontal Scroll gained optional snap and scrub smoothness without replacing the existing pin/track motion.
- Admin CSS for the hub stays under `.pss-admin-screen`. Elementor library-document editor flow is unchanged.

## 2.4.0
- Single Layout → Edit with Elementor now opens the linked Elementor Library document and no longer rewrites the editor preview iframe onto a Project URL (that mismatch blanked the canvas).
- Asking Elementor to open a layout manager record redirects to the library document editor; Project posts still open as data, not Elementor pages. Pages/Posts are untouched.
- Editor/preview project context is resolved from `editor_post_id` / library meta instead of swapping the preview document.
- Leftover Elementor meta is stripped from layout manager records so Elementor does not treat them as canvases.
- Widgets gained Dynamic Project Data vs Manual content sources (Before/After, Gallery, Image, Video, CTA, Field, Stats, Story, Team).
- Added Image Reveal, Timeline, Awards, Team, Testimonials, Story, Marquee and Slider widgets. Existing Project widgets remain registered.
- Showcase cards gained more distinct presets (asymmetric, full-image, interactive) without duplicating overlay + below meta.

## 2.3.0
- Showcase cards no longer print the same project fields on the image and under the card. Placement is Auto / Inside / Below / Hidden.
- Placeholder tokens such as `%name%` are skipped on cards.
- Card styles (modern, luxury, editorial, architectural, minimal) and hover motion were expanded, with Elementor responsive column/gap/ratio/radius controls.
- Single Layout → Edit with Elementor still opens the linked Elementor Library document, never a Project post or the layout manager record.
- Starter layouts Modern — Editorial and Premium — Cinematic were rebuilt as designed two-column landing pages (including floor plan, tags, share, and the new scroll widget).
- New Elementor widget: Project Horizontal Scroll (vertical scroll drives pinned horizontal panels, reduced-motion and mobile stack/swipe).

## 2.2.0
- Fixed Single Layouts so they open a real Elementor Library document via the Document API.
- Stopped Project posts and layout manager records from opening in Elementor (this caused blank Add Project screens).
- Registered Project widgets on official Elementor widget/category hooks, independently of boot order.
- Fixed Project Share controls (a missing section could empty the entire Elementor widget panel).
- Image dynamic tags now use Elementor's Data_Tag API.
- Starter layouts now use classic sections/columns with unique IDs so they open even without Flexbox containers.
- Admin CSS/JS stay scoped to Project screens and no longer run during the Elementor canvas.
- Showcase gained a true Editorial layout; frontend assets load through widget dependencies.
- WooCommerce product selector on Projects restored (`render_project_products`).
- One-time starter upgrades no longer run during Elementor editor/AJAX requests.

## 2.1.0
- Reworked Single Layouts to use a normal Elementor Library design document linked to a separate Project Layout manager record.
- Removed the custom Elementor Document Type architecture that could interfere with Elementor editor loading.
- Simplified widget categories and registration to Elementor's standard addon lifecycle.
- Removed global Elementor editor asset enqueueing; Project widget CSS is loaded through widget dependencies.
- Scoped plugin admin CSS to PSS screens only.
- Preserved all existing Project widgets and data functionality.
