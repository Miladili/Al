# Changelog

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
