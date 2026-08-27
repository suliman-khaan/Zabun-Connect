# Zabun Connect
Seamless WordPress & Elementor Integration for Zabun Real Estate CRM (`gateway-cmsapi.v2.zabun.be`).

---

## Highlights

- **Native Standalone**: Pure zero-dependency plugin. No Composer install or command-line setup needed. Just install and activate.
- **Smart Database Cache**: Cached storage inside `wp_zabun_listings` using WordPress `dbDelta` ensures ultra-fast page speed without blocking the UI with live external API calls.
- **Elementor Widgets**: Native widgets for Property Grid, Property Details, Property Search Bar, and Featured Listings with full style and typography controls.
- **Shortcode Compatibility**: Standard shortcodes (`[zabun_grid]`, `[zabun_detail]`, `[zabun_search]`, `[zabun_featured]`) compatible with classic editor, block themes, and Gutenberg.
- **WP-Cron Automated Background Sync**: Scheduled background sync ensures property data, prices, and media stay up-to-date automatically.

---

## Installation & Setup

1. **Download**: Place the `zabun-connect` plugin directory inside `/wp-content/plugins/` (or upload as a `.zip` via **Plugins $\rightarrow$ Add New $\rightarrow$ Upload Plugin** in your WordPress Admin).
2. **Activate**: Activate the plugin through the **Plugins** menu.
3. **Configure**: Go to **Settings $\rightarrow$ Zabun Connect** in your WordPress dashboard.
4. **Enter Credentials**: Fill in your Zabun API details:
   - **API Key** (`api_key`)
   - **Client ID** (`client_id`)
   - **Server ID** (`server_id`)
   - **Company ID** (`X-CLIENT-ID`)
5. **Test & Sync**: Click **Test Connection** to verify your gateway access, then click **Sync Now** to download your property listings.

---

## Author

- **Author**: Suliman Khan
- **Website**: [https://sulimankhan.pro](https://sulimankhan.pro)
