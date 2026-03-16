# WordPress Photography Theme — Design Spec

**Date:** 2026-03-16
**Reference site:** https://www.jamespopsys.com/
**Project root:** `D:/projects/aonghusflynn/`

---

## Overview

A custom WordPress theme for a photography portfolio with WooCommerce print sales. The theme presents named photo projects (series/albums), each with its own page. The homepage is a full-screen slideshow of project covers. Individual project pages show a square photo grid with a lightbox viewer. The shop is a separate WooCommerce section, unconnected to individual projects.

---

## Goals

- Allow the photographer to add named photo projects via wp-admin with no coding
- Present projects in a cinematic, minimal dark aesthetic similar to jamespopsys.com
- Sell prints through a standard WooCommerce shop (separate from projects)
- Fast, lightweight — no page builders, no unnecessary plugins

---

## Non-Goals

- No e-commerce integration on project pages (shop is separate)
- No blog section
- No Google Fonts (use system font stack for performance)
- No full-site editing / Gutenberg block theme

---

## Tech Stack

| Layer | Choice | Reason |
|---|---|---|
| Base theme | Underscores (_s) | Minimal boilerplate, no visual opinions, industry standard |
| Local dev | LocalWP | Easiest WordPress setup on Windows, no config required |
| Gallery fields | ACF Free | Drag-and-drop gallery field, no code required to manage photos |
| Shop | WooCommerce | Standard WordPress e-commerce |
| Lightbox | GLightbox (CDN) | Lightweight, accessible, keyboard + touch support |

---

## Architecture

### Custom Post Type: `photo_project`

Registered in `inc/post-types.php`.

**Fields:**

| Field | Type | Notes |
|---|---|---|
| Title | WordPress built-in | Project name, e.g. "Iceland 2024" |
| Cover Image | Featured Image | Displayed full-screen on homepage slideshow |
| Description | WordPress editor | Optional short text shown below photo grid |
| Photos | ACF Gallery | All project images, drag to reorder |
| Menu Order | WordPress built-in | Controls order in homepage slideshow |

**URL structure:**
- Single project: `/projects/{slug}/` e.g. `/projects/iceland-2024/`

### Pages

| URL | Template | Description |
|---|---|---|
| `/` | `front-page.php` | Homepage — full-screen project slideshow |
| `/projects/{slug}/` | `single-photo_project.php` | Project page — square grid + lightbox |
| `/shop/` | WooCommerce default | Shop archive (styled to match theme) |
| `/about/` | `page.php` | Generic page |
| `/contact/` | `page.php` | Generic page |

---

## Theme File Structure

```
your-theme/
├── style.css                        # Theme header + base styles
├── functions.php                    # Theme setup, CPT registration, script enqueue
├── index.php                        # Fallback template
├── front-page.php                   # Homepage slideshow
├── single-photo_project.php         # Individual project page
├── archive-photo_project.php        # Redirects to homepage (not used directly)
├── page.php                         # Generic pages (About, Contact)
├── header.php                       # Global nav
├── footer.php                       # Minimal footer
├── woocommerce/
│   └── archive-product.php          # Shop page template override
├── assets/
│   ├── css/
│   │   ├── main.css                 # Global styles, CSS custom properties
│   │   ├── slideshow.css            # Homepage full-screen slideshow
│   │   ├── project.css              # Project grid + lightbox styles
│   │   └── shop.css                 # WooCommerce dark theme overrides
│   └── js/
│       ├── slideshow.js             # Scroll/swipe behaviour for homepage
│       └── lightbox.js              # GLightbox initialisation
└── inc/
    ├── post-types.php               # photo_project CPT registration
    ├── acf-fields.php               # ACF field group definitions (PHP registration)
    └── woocommerce.php              # WooCommerce hooks and setup
```

---

## Homepage Template (`front-page.php`)

**Behaviour:**
- Queries all published `photo_project` posts ordered by menu order
- Each project renders as a full-viewport section (100vw × 100vh)
- Cover image (featured image) fills the slide with `object-fit: cover`
- Project title overlaid bottom-left, white, minimal sans-serif typography
- Mouse wheel, trackpad scroll, and touch swipe navigate between slides using CSS scroll-snap
- Clicking a slide navigates to that project's single page
- Dot navigation indicator (one dot per project) fixed to right edge

**Implementation notes:**
- Use CSS `scroll-snap-type: y mandatory` on the container for smooth native scroll behaviour
- Each slide is a `<section>` with `scroll-snap-align: start`
- `slideshow.js` handles dot indicator highlighting on scroll

---

## Project Page Template (`single-photo_project.php`)

**Layout:**
- Minimal header: project title + photo count (e.g. "Iceland 2024 — 24 photographs")
- Back link (← Projects) top-left
- Responsive square grid:
  - 4 columns on desktop (≥1024px)
  - 3 columns on tablet (≥768px)
  - 2 columns on mobile
- Photos sourced from ACF gallery field, rendered as `<img>` with `aspect-ratio: 1 / 1` and `object-fit: cover`
- GLightbox initialised on all grid images — clicking opens full-screen viewer
- Lightbox includes left/right arrows, keyboard navigation, swipe on mobile
- Optional project description rendered below the grid if populated

---

## Navigation (`header.php`)

- Fixed top bar, full width
- Transparent background over homepage slideshow, solid `#0d0d0d` on all inner pages
- Left: photographer name / logo (links to homepage)
- Right: Projects · Shop · About · Contact
- Mobile: hamburger menu collapsing to full-screen overlay

---

## Visual Design

| Property | Value |
|---|---|
| Background | `#0d0d0d` |
| Primary text | `#f0f0f0` |
| Accent / hover | `#ffffff` |
| Muted text | `#888888` |
| Font | `-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif` |
| Nav font size | `13px`, letter-spacing `0.08em`, uppercase |
| Body font size | `16px` |
| Transitions | `opacity 0.3s ease`, `transform 0.3s ease` |

---

## WooCommerce Integration

- WooCommerce installed as a plugin, runs standard shop functionality
- `woocommerce/` directory in theme overrides shop templates for dark styling
- `assets/css/shop.css` overrides WooCommerce default styles to match dark aesthetic
- `inc/woocommerce.php` declares WooCommerce support and removes default WooCommerce styles (`add_theme_support('woocommerce')`, `add_filter('woocommerce_enqueue_styles', '__return_empty_array')`)
- Shop is completely independent of photo projects — no cross-linking

---

## Admin Experience

**Adding a new project:**
1. wp-admin → Projects → Add New
2. Enter project title
3. Set Featured Image (cover for homepage slideshow)
4. Upload photos into the Gallery field — drag to reorder
5. Optionally write a short description
6. Set Menu Order to control slideshow position
7. Publish — project is immediately live

**No shortcodes, no page builders required.**

---

## Dev Environment Setup

1. Download and install **LocalWP** (https://localwp.com/)
2. Create a new site (e.g. `photography.local`)
3. Clone / copy theme into `wp-content/themes/your-theme/`
4. Activate theme in wp-admin → Appearance → Themes
5. Install plugins: **Advanced Custom Fields** (free), **WooCommerce**
6. Import ACF field group via `inc/acf-fields.php` (auto-registers on theme activation)

---

## Out of Scope

- Print-on-demand service integration
- Client proofing / password-protected galleries
- Image watermarking
- Multi-language support
- REST API / headless setup
