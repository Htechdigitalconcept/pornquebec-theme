# PornQuebec — Astra child theme

Child theme of [Astra](https://wordpress.org/themes/astra/) for pornquebec.ca (WordPress + WooCommerce + Elementor).

## Install (one click)
1. Make sure the **Astra** theme is installed (it is the parent).
2. WordPress admin > **Appearance > Themes > Add New > Upload Theme**, choose `pornquebec.zip`, **Install Now**, then **Activate**.
3. Open **Appearance > PornQuebec** for the setup page, status checks and template downloads.

On activation the theme copies its images into `wp-content/uploads/2026/11/`, which is where the Elementor templates expect them.

## What it includes
- `assets/css/pornquebec.css` — full design system: product and category cards, hover image swap, single product page, blog archive and single post, contact form, legal page helpers.
- Google Fonts (Poppins and Lobster Two) loaded from `functions.php`.
- Hover image swap for WooCommerce cards (first gallery image).
- Built-in contact form, shortcode `[pq_contact_form]`, no plugin needed. Emails go to Settings > General > Administration Email Address.
- `elementor-templates/` — Homepage, About, Privacy Policy, Terms and Conditions, Contact (import in Elementor > Templates > Import Templates).

## After activating
- If you pasted the CSS into Customize > Additional CSS before, **clear it**; the theme now loads it.
- Create pages and insert the templates: Home (set as static front page), About, Privacy Policy, Terms and Conditions, Contact (slug `contact`).
- Requires Elementor 3.16+ and WooCommerce.

## Notes
- The legal pages are general drafts, not legal advice. Have a Québec lawyer review them.
- Licence: GPL v2 or later.
