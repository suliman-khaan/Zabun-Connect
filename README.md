# Zabun Connect

Zabun Connect is a WordPress/Elementor integration plugin designed to sync listings from the Zabun CRM API (`gateway-cmsapi.v2.zabun.be`) locally for maximum performance, and expose them as widgets and shortcodes.

## Features

- **Database Cache**: Syncs listings into a custom database table (`wp_zabun_listings`) to prevent API performance bottlenecks on the frontend.
- **WP-Cron Scheduler**: Keeps data updated automatically.
- **Elementor Widgets**: Property Grid and Property Detail widgets with design controls.
- **Shortcodes**: Standard `[zabun_grid]` and `[zabun_detail]` shortcodes for use anywhere.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/zabun-connect` directory.
2. Run `composer install` in the plugin directory to install the autoloader.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Go to **Settings -> Zabun Connect** to configure your API key.
