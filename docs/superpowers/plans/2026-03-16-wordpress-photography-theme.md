# WordPress Photography Theme Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a custom WordPress theme (`aonghus-photography`) with a full-screen project slideshow homepage, per-project photo galleries with lightbox, and a separate WooCommerce print shop.

**Architecture:** Built on the Underscores (_s) starter theme, the theme registers a `photo_project` custom post type managed via ACF gallery fields. The homepage uses CSS scroll-snap for a full-screen per-project slideshow; project pages show a square thumbnail grid powered by GLightbox. WooCommerce runs as a standalone plugin with CSS-only dark theme overrides.

**Tech Stack:** PHP 8+, WordPress 6+, Underscores (_s) starter, ACF Free plugin, WooCommerce plugin, GLightbox (CDN), Jest (JS unit tests), LocalWP (local dev environment)

**Spec:** `docs/superpowers/specs/2026-03-16-wordpress-photography-theme-design.md`

---

## File Map

| File | Responsibility |
|---|---|
| `aonghus-photography/style.css` | Theme header declaration + CSS custom properties reset |
| `aonghus-photography/functions.php` | Bootstraps theme: requires inc/ files, registers nav menus, declares theme support, enqueues assets |
| `aonghus-photography/index.php` | WordPress fallback template |
| `aonghus-photography/front-page.php` | Homepage: queries photo_project posts, renders scroll-snap slideshow |
| `aonghus-photography/single-photo_project.php` | Project page: renders ACF gallery as square grid, initialises GLightbox |
| `aonghus-photography/archive-photo_project.php` | Redirects to homepage (CPT archive not used directly) |
| `aonghus-photography/page.php` | Generic WordPress pages (About, Contact) |
| `aonghus-photography/header.php` | Fixed nav bar with mobile hamburger; outputs `<body>` open tag |
| `aonghus-photography/footer.php` | Minimal footer; outputs `</body>` close and `wp_footer()` |
| `aonghus-photography/inc/post-types.php` | Registers `photo_project` CPT with rewrite slug `projects` |
| `aonghus-photography/inc/acf-fields.php` | Registers ACF gallery field group via `acf_add_local_field_group()` |
| `aonghus-photography/inc/woocommerce.php` | Declares WooCommerce support, removes default WC styles |
| `aonghus-photography/assets/css/main.css` | CSS custom properties, dark base styles, typography, utility classes |
| `aonghus-photography/assets/css/slideshow.css` | Homepage scroll-snap slideshow layout and dot navigation |
| `aonghus-photography/assets/css/project.css` | Project page square grid and lightbox trigger styles |
| `aonghus-photography/assets/css/shop.css` | WooCommerce dark theme overrides (all shop pages via CSS) |
| `aonghus-photography/assets/js/slideshow.js` | Updates dot indicator on scroll; no DOM mutation beyond class toggling |
| `aonghus-photography/assets/js/navigation.js` | Hamburger toggle — adds/removes `nav-open` class on `<body>` |
| `aonghus-photography/assets/js/lightbox.js` | Initialises GLightbox on `.project-grid a` elements |
| `aonghus-photography/woocommerce/archive-product.php` | Shop archive layout override (dark) |
| `aonghus-photography/woocommerce/single-product.php` | Single product layout override (dark) |
| `aonghus-photography/tests/js/navigation.test.js` | Jest unit tests for hamburger toggle logic |
| `aonghus-photography/tests/js/slideshow.test.js` | Jest unit tests for dot indicator update logic |
| `aonghus-photography/package.json` | Jest dev dependency + test script |

---

## Chunk 1: Foundation — Scaffolding, functions.php, Custom Post Type

### Task 1: Set up LocalWP and download Underscores

**Files:**
- Create: `aonghus-photography/` (theme directory inside LocalWP site)

- [ ] **Step 1: Install LocalWP**

  Download from https://localwp.com/ and install. Launch LocalWP, click **+ Create a new site**, name it `photography`, choose preferred environment (defaults are fine), set admin username/password. Note the site root path — the WordPress install lives at something like `~/Local Sites/photography/app/public/`.

- [ ] **Step 2: Download Underscores starter theme**

  Go to https://underscores.me/, enter theme name `aonghus-photography`, tick **_sassify!** OFF (we're using plain CSS), click **Generate**. Download the zip.

- [ ] **Step 3: Extract theme into LocalWP site**

  Extract the downloaded zip into `~/Local Sites/photography/app/public/wp-content/themes/`. The folder must be named `aonghus-photography`.

  Verify:
  ```
  ls ~/Local Sites/photography/app/public/wp-content/themes/aonghus-photography/
  # Expected: style.css  functions.php  index.php  header.php  footer.php  ...
  ```

- [ ] **Step 4: Activate theme in wp-admin**

  Open the LocalWP site (click **Open Site** → append `/wp-admin`), log in, go to **Appearance → Themes**, activate `aonghus-photography`.

  Verify: Site homepage loads without a white screen of death.

- [ ] **Step 5: Clone this repo into the theme directory**

  ```bash
  cd "~/Local Sites/photography/app/public/wp-content/themes/aonghus-photography"
  git init
  git remote add origin https://github.com/aonghusflynn/aonghusflynn-popsys.git
  git fetch origin feat/wordpress-photography-theme-spec
  git checkout feat/wordpress-photography-theme-spec
  ```

---

### Task 2: Configure style.css and functions.php

**Files:**
- Modify: `aonghus-photography/style.css`
- Modify: `aonghus-photography/functions.php`

- [ ] **Step 1: Replace style.css header and add custom properties**

  Replace the entire contents of `style.css` with:

  ```css
  /*
  Theme Name:   aonghus-photography
  Theme URI:    https://github.com/aonghusflynn/aonghusflynn-popsys
  Author:       Aonghus Flynn
  Description:  Minimal dark photography portfolio with WooCommerce print shop.
  Version:      1.0.0
  License:      GNU General Public License v2 or later
  Text Domain:  aonghus-photography
  */

  /* Custom properties — all colours and spacing defined here */
  :root {
    --colour-bg:      #0d0d0d;
    --colour-text:    #f0f0f0;
    --colour-muted:   #888888;
    --colour-accent:  #ffffff;
    --font-stack:     -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --nav-height:     56px;
    --transition:     0.3s ease;
  }
  ```

- [ ] **Step 2: Clean up functions.php — remove Underscores defaults we don't need**

  Open `functions.php`. Delete the widget setup function (`aonghus_photography_widgets_init`) and its `add_action` hook — we're not using widget areas.

  Keep: `aonghus_photography_setup()`, `aonghus_photography_content_width`, `aonghus_photography_scripts()`.

- [ ] **Step 3: Update aonghus_photography_setup() in functions.php**

  Replace the body of `aonghus_photography_setup()` with:

  ```php
  function aonghus_photography_setup() {
      load_theme_textdomain( 'aonghus-photography', get_template_directory() . '/languages' );
      add_theme_support( 'automatic-feed-links' );
      add_theme_support( 'title-tag' );
      add_theme_support( 'post-thumbnails' );
      add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );

      register_nav_menus( [
          'primary' => esc_html__( 'Primary Menu', 'aonghus-photography' ),
      ] );
  }
  add_action( 'after_setup_theme', 'aonghus_photography_setup' );
  ```

- [ ] **Step 4: Update aonghus_photography_scripts() to enqueue our assets**

  Replace the existing `aonghus_photography_scripts()` function with:

  ```php
  function aonghus_photography_scripts() {
      $v = wp_get_theme()->get( 'Version' );

      // main.css loads on every page
      wp_enqueue_style( 'aonghus-main', get_template_directory_uri() . '/assets/css/main.css', [], $v );

      // navigation.js loads on every page (mobile hamburger needed everywhere)
      wp_enqueue_script( 'aonghus-navigation', get_template_directory_uri() . '/assets/js/navigation.js', [], $v, true );

      // Homepage only
      if ( is_front_page() ) {
          wp_enqueue_style(  'aonghus-slideshow', get_template_directory_uri() . '/assets/css/slideshow.css', [ 'aonghus-main' ], $v );
          wp_enqueue_script( 'aonghus-slideshow', get_template_directory_uri() . '/assets/js/slideshow.js',  [], $v, true );
      }

      // Single project pages only
      if ( is_singular( 'photo_project' ) ) {
          wp_enqueue_style( 'aonghus-project', get_template_directory_uri() . '/assets/css/project.css', [ 'aonghus-main' ], $v );
          wp_enqueue_script( 'aonghus-lightbox', get_template_directory_uri() . '/assets/js/lightbox.js', [], $v, true );
      }

      // WooCommerce pages only
      if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
          wp_enqueue_style( 'aonghus-shop', get_template_directory_uri() . '/assets/css/shop.css', [ 'aonghus-main' ], $v );
      }
  }
  add_action( 'wp_enqueue_scripts', 'aonghus_photography_scripts' );
  ```

- [ ] **Step 5: Require inc/ files from functions.php**

  Add at the bottom of `functions.php`:

  ```php
  require get_template_directory() . '/inc/post-types.php';
  require get_template_directory() . '/inc/acf-fields.php';
  require get_template_directory() . '/inc/woocommerce.php';
  ```

- [ ] **Step 6: Create placeholder asset files so WordPress doesn't 404**

  ```bash
  mkdir -p aonghus-photography/assets/css aonghus-photography/assets/js aonghus-photography/inc
  touch aonghus-photography/assets/css/main.css
  touch aonghus-photography/assets/css/slideshow.css
  touch aonghus-photography/assets/css/project.css
  touch aonghus-photography/assets/css/shop.css
  touch aonghus-photography/assets/js/navigation.js
  touch aonghus-photography/assets/js/slideshow.js
  touch aonghus-photography/assets/js/lightbox.js
  touch aonghus-photography/inc/post-types.php
  touch aonghus-photography/inc/acf-fields.php
  touch aonghus-photography/inc/woocommerce.php
  ```

  Add `<?php` as the first line of each `.php` file:
  ```bash
  for f in aonghus-photography/inc/*.php; do echo '<?php' > "$f"; done
  ```

- [ ] **Step 7: Verify site still loads**

  Reload the site homepage. No PHP errors should appear. Check LocalWP → **Open site**.

- [ ] **Step 8: Commit**

  ```bash
  git add style.css functions.php inc/ assets/
  git commit -m "feat: theme setup — style.css header, functions.php, asset placeholders"
  ```

---

### Task 3: Register photo_project Custom Post Type

**Files:**
- Create: `aonghus-photography/inc/post-types.php`

- [ ] **Step 1: Write the CPT registration**

  Replace `inc/post-types.php` with:

  ```php
  <?php
  /**
   * Registers the photo_project custom post type.
   */
  function aonghus_register_photo_project_cpt() {
      $labels = [
          'name'               => 'Projects',
          'singular_name'      => 'Project',
          'add_new_item'       => 'Add New Project',
          'edit_item'          => 'Edit Project',
          'new_item'           => 'New Project',
          'view_item'          => 'View Project',
          'search_items'       => 'Search Projects',
          'not_found'          => 'No projects found.',
          'not_found_in_trash' => 'No projects found in trash.',
      ];

      $args = [
          'labels'              => $labels,
          'public'              => true,
          'has_archive'         => false,
          'rewrite'             => [ 'slug' => 'projects' ],
          'supports'            => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
          'menu_icon'           => 'dashicons-camera',
          'show_in_rest'        => false,
      ];

      register_post_type( 'photo_project', $args );
  }
  add_action( 'init', 'aonghus_register_photo_project_cpt' );
  ```

  Note: `has_archive => false` prevents WordPress generating an archive URL (we redirect it to the homepage). `page-attributes` support enables the Menu Order field for slideshow ordering.

- [ ] **Step 2: Flush rewrite rules**

  In wp-admin go to **Settings → Permalinks** and click **Save Changes** (no changes needed — just saving flushes the rules).

- [ ] **Step 3: Verify CPT appears in wp-admin**

  Check that **Projects** appears in the wp-admin left sidebar with a camera icon. Click **Add New** — confirm the title field, featured image box, and menu order field (under Page Attributes) are present.

- [ ] **Step 4: Commit**

  ```bash
  git add inc/post-types.php
  git commit -m "feat: register photo_project custom post type"
  ```

---

## Chunk 2: ACF Fields, Global Styles, Navigation

### Task 4: Register ACF Gallery Field Group

**Files:**
- Modify: `aonghus-photography/inc/acf-fields.php`

- [ ] **Step 1: Install ACF plugin**

  In wp-admin go to **Plugins → Add New**, search for **Advanced Custom Fields**, install and activate the free version by WP Engine.

- [ ] **Step 2: Write the ACF field group registration**

  Replace `inc/acf-fields.php` with:

  ```php
  <?php
  /**
   * Registers ACF field group for photo_project via PHP (no JSON import needed).
   * Requires: Advanced Custom Fields plugin (free).
   */
  function aonghus_register_acf_fields() {
      if ( ! function_exists( 'acf_add_local_field_group' ) ) {
          return; // ACF not active — fail silently.
      }

      acf_add_local_field_group( [
          'key'    => 'group_photo_project',
          'title'  => 'Project Fields',
          'fields' => [
              [
                  'key'           => 'field_project_gallery',
                  'label'         => 'Photos',
                  'name'          => 'project_gallery',
                  'type'          => 'gallery',
                  'instructions'  => 'Upload project photos. Drag to reorder.',
                  'return_format' => 'array',
                  'preview_size'  => 'medium',
                  'insert'        => 'append',
                  'library'       => 'all',
                  'min'           => 0,
                  'max'           => 0,
              ],
          ],
          'location' => [
              [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'photo_project' ] ],
          ],
          'menu_order'            => 0,
          'position'              => 'normal',
          'style'                 => 'default',
          'label_placement'       => 'top',
          'instruction_placement' => 'label',
      ] );
  }
  add_action( 'acf/init', 'aonghus_register_acf_fields' );
  ```

- [ ] **Step 3: Verify gallery field appears in wp-admin**

  Go to **Projects → Add New**. Below the content editor you should see a **Photos** gallery field with an **Add to gallery** button.

- [ ] **Step 4: Add a test project**

  Create a project titled **Test Project**, set a featured image, upload 3–4 photos to the gallery, set Menu Order to `1`, and publish. We'll use this throughout development.

- [ ] **Step 5: Commit**

  ```bash
  git add inc/acf-fields.php
  git commit -m "feat: register ACF gallery field group for photo_project"
  ```

---

### Task 5: Global styles (main.css)

**Files:**
- Modify: `aonghus-photography/assets/css/main.css`

- [ ] **Step 1: Write global dark base styles**

  Replace `assets/css/main.css` with:

  ```css
  /* ============================================================
     GLOBAL — base styles, typography, layout utilities
     Custom properties are declared in style.css
  ============================================================ */

  *, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  html {
    font-size: 16px;
    scroll-behavior: smooth;
  }

  body {
    background-color: var(--colour-bg);
    color: var(--colour-text);
    font-family: var(--font-stack);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
  }

  a {
    color: inherit;
    text-decoration: none;
    transition: opacity var(--transition);
  }

  a:hover {
    opacity: 0.7;
  }

  img {
    display: block;
    max-width: 100%;
    height: auto;
  }

  /* Skip link (accessibility) */
  .skip-link {
    position: absolute;
    left: -999px;
    top: auto;
    width: 1px;
    height: 1px;
    overflow: hidden;
  }
  .skip-link:focus {
    left: 0;
    top: 0;
    width: auto;
    height: auto;
    padding: 8px 16px;
    background: var(--colour-accent);
    color: var(--colour-bg);
    z-index: 9999;
  }

  /* Page wrapper — adds top padding equal to nav height on inner pages */
  .site-content {
    padding-top: var(--nav-height);
  }

  /* Homepage has full-bleed slides — no top padding */
  .home .site-content {
    padding-top: 0;
  }

  /* Generic page content */
  .page-content {
    max-width: 720px;
    margin: 0 auto;
    padding: 64px 24px;
    line-height: 1.7;
    color: var(--colour-text);
  }

  .page-content h1 {
    font-size: clamp(1.5rem, 4vw, 2.5rem);
    font-weight: 400;
    letter-spacing: 0.04em;
    margin-bottom: 32px;
    text-transform: uppercase;
  }

  .page-content p {
    color: var(--colour-muted);
    margin-bottom: 16px;
  }
  ```

- [ ] **Step 2: Verify styles load**

  Reload the site. Background should now be `#0d0d0d`. Open browser DevTools → Network tab, confirm `main.css` returns 200.

- [ ] **Step 3: Commit**

  ```bash
  git add assets/css/main.css
  git commit -m "feat: global dark base styles"
  ```

---

### Task 6: Header, footer, and mobile navigation

**Files:**
- Modify: `aonghus-photography/header.php`
- Modify: `aonghus-photography/footer.php`
- Modify: `aonghus-photography/assets/js/navigation.js`
- Create: `aonghus-photography/tests/js/navigation.test.js`
- Create: `aonghus-photography/package.json`

- [ ] **Step 1: Set up Jest**

  In the theme root, create `package.json`:

  ```json
  {
    "name": "aonghus-photography",
    "private": true,
    "scripts": {
      "test": "jest"
    },
    "devDependencies": {
      "jest": "^29.0.0",
      "jest-environment-jsdom": "^29.0.0"
    },
    "jest": {
      "testEnvironment": "jsdom",
      "testMatch": ["**/tests/js/**/*.test.js"]
    }
  }
  ```

  ```bash
  cd aonghus-photography && npm install
  ```

- [ ] **Step 2: Write the failing navigation test**

  Create `tests/js/navigation.test.js`:

  ```js
  /**
   * Tests for navigation.js hamburger toggle logic.
   * The module exports a single function: initNavigation(bodyEl)
   */
  const { initNavigation } = require('../../assets/js/navigation');

  describe('initNavigation', () => {
    let body, button;

    beforeEach(() => {
      document.body.innerHTML = `
        <body>
          <button class="nav-toggle" aria-expanded="false" aria-label="Open menu"></button>
        </body>
      `;
      body   = document.body;
      button = document.querySelector('.nav-toggle');
    });

    test('clicking toggle adds nav-open class to body', () => {
      initNavigation(body);
      button.click();
      expect(body.classList.contains('nav-open')).toBe(true);
    });

    test('clicking toggle again removes nav-open class', () => {
      initNavigation(body);
      button.click();
      button.click();
      expect(body.classList.contains('nav-open')).toBe(false);
    });

    test('clicking toggle sets aria-expanded to true', () => {
      initNavigation(body);
      button.click();
      expect(button.getAttribute('aria-expanded')).toBe('true');
    });

    test('clicking toggle again sets aria-expanded to false', () => {
      initNavigation(body);
      button.click();
      button.click();
      expect(button.getAttribute('aria-expanded')).toBe('false');
    });
  });
  ```

- [ ] **Step 3: Run test — verify it fails**

  ```bash
  npm test -- --testPathPattern=navigation
  ```

  Expected: FAIL — `Cannot find module '../../assets/js/navigation'`

- [ ] **Step 4: Write navigation.js**

  Replace `assets/js/navigation.js` with:

  ```js
  /**
   * navigation.js — Mobile hamburger toggle.
   * Adds/removes `nav-open` class on the body element.
   * Exported for unit testing; auto-initialises in browser.
   */

  'use strict';

  function initNavigation(bodyEl) {
    const toggle = bodyEl.querySelector('.nav-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
      const isOpen = bodyEl.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // Auto-init in browser environment
  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
      initNavigation(document.body);
    });
  }

  // Export for Jest
  if (typeof module !== 'undefined') {
    module.exports = { initNavigation };
  }
  ```

- [ ] **Step 5: Run test — verify it passes**

  ```bash
  npm test -- --testPathPattern=navigation
  ```

  Expected: PASS — 4 tests passing

- [ ] **Step 6: Write header.php**

  Replace `header.php` with:

  ```php
  <!DOCTYPE html>
  <html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <a class="skip-link screen-reader-text" href="#primary">
    <?php esc_html_e( 'Skip to content', 'aonghus-photography' ); ?>
  </a>

  <header class="site-header" id="masthead">
    <div class="site-header__inner">

      <a class="site-header__name" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <?php bloginfo( 'name' ); ?>
      </a>

      <nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'aonghus-photography' ); ?>">
        <?php
        wp_nav_menu( [
          'theme_location' => 'primary',
          'menu_class'     => 'site-nav__list',
          'container'      => false,
          'fallback_cb'    => false,
        ] );
        ?>
      </nav>

      <button class="nav-toggle"
              aria-expanded="false"
              aria-controls="site-nav"
              aria-label="<?php esc_attr_e( 'Open menu', 'aonghus-photography' ); ?>">
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
        <span class="nav-toggle__bar"></span>
      </button>

    </div><!-- .site-header__inner -->
  </header><!-- #masthead -->

  <div id="page" class="site">
    <main id="primary" class="site-content">
  ```

- [ ] **Step 7: Write footer.php**

  Replace `footer.php` with:

  ```php
    </main><!-- #primary -->
  </div><!-- #page -->

  <footer class="site-footer">
    <div class="site-footer__inner">
      <p class="site-footer__copy">
        &copy; <?php echo esc_html( date( 'Y' ) ); ?>
        <?php bloginfo( 'name' ); ?>
      </p>
    </div>
  </footer>

  <?php wp_footer(); ?>
  </body>
  </html>
  ```

- [ ] **Step 8: Add header + nav CSS to main.css**

  Append to `assets/css/main.css`:

  ```css
  /* ============================================================
     HEADER & NAVIGATION
  ============================================================ */

  .site-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--nav-height);
    z-index: 100;
    transition: background-color var(--transition);
  }

  /* Solid background on all non-homepage pages */
  body:not(.home) .site-header {
    background-color: var(--colour-bg);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  /* Transparent over slideshow; JS adds .scrolled class when user scrolls */
  .home .site-header {
    background-color: transparent;
  }

  .site-header__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    padding: 0 24px;
  }

  .site-header__name {
    font-size: 13px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 500;
    color: var(--colour-text);
  }

  /* Desktop nav */
  .site-nav__list {
    display: flex;
    gap: 32px;
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .site-nav__list a {
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--colour-text);
  }

  /* Hamburger button — hidden on desktop */
  .nav-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
  }

  .nav-toggle__bar {
    display: block;
    width: 22px;
    height: 1px;
    background: var(--colour-text);
    transition: transform var(--transition), opacity var(--transition);
  }

  /* Mobile */
  @media (max-width: 767px) {
    .site-nav {
      position: fixed;
      inset: 0;
      background: var(--colour-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity var(--transition);
      z-index: 99;
    }

    .nav-open .site-nav {
      opacity: 1;
      pointer-events: all;
    }

    .site-nav__list {
      flex-direction: column;
      align-items: center;
      gap: 32px;
      font-size: 20px;
    }

    .nav-toggle {
      display: flex;
      z-index: 101;
    }

    /* Animate bars to X when open */
    .nav-open .nav-toggle__bar:nth-child(1) {
      transform: translateY(6px) rotate(45deg);
    }
    .nav-open .nav-toggle__bar:nth-child(2) {
      opacity: 0;
    }
    .nav-open .nav-toggle__bar:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }
  }

  /* Footer */
  .site-footer {
    padding: 32px 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .site-footer__copy {
    font-size: 12px;
    color: var(--colour-muted);
    letter-spacing: 0.04em;
  }
  ```

- [ ] **Step 9: Set up the Primary navigation menu in wp-admin**

  Go to **Appearance → Menus**, create a menu called **Primary**, add pages/custom links for: Projects (`/projects/`), Shop (`/shop/`), About (`/about/`), Contact (`/contact/`). Assign it to the **Primary Menu** location. Save.

  Verify: Nav links appear in the site header on desktop. On mobile (resize browser to <768px), nav links should be hidden and a hamburger button visible; clicking it should toggle the full-screen overlay.

- [ ] **Step 10: Commit**

  ```bash
  git add header.php footer.php assets/css/main.css assets/js/navigation.js tests/js/navigation.test.js package.json package-lock.json
  git commit -m "feat: header, footer, mobile navigation with passing Jest tests"
  ```

---

## Chunk 3: Homepage Slideshow

### Task 7: Homepage front-page.php + slideshow CSS/JS

**Files:**
- Modify: `aonghus-photography/front-page.php`
- Modify: `aonghus-photography/assets/css/slideshow.css`
- Modify: `aonghus-photography/assets/js/slideshow.js`
- Create: `aonghus-photography/tests/js/slideshow.test.js`

- [ ] **Step 1: Write failing slideshow test**

  Create `tests/js/slideshow.test.js`:

  ```js
  /**
   * Tests for slideshow.js dot indicator logic.
   * Exports: initSlideshow(containerEl, dotsEl)
   */
  const { getActiveDotIndex } = require('../../assets/js/slideshow');

  describe('getActiveDotIndex', () => {
    test('returns 0 when scrollTop is 0', () => {
      expect(getActiveDotIndex(0, 800)).toBe(0);
    });

    test('returns 1 when scrolled past one full slide height', () => {
      expect(getActiveDotIndex(800, 800)).toBe(1);
    });

    test('returns 2 when scrolled past two slide heights', () => {
      expect(getActiveDotIndex(1700, 800)).toBe(2);
    });

    test('returns 0 for negative scroll (overscroll at top)', () => {
      expect(getActiveDotIndex(-10, 800)).toBe(0);
    });
  });
  ```

- [ ] **Step 2: Run test — verify it fails**

  ```bash
  npm test -- --testPathPattern=slideshow
  ```

  Expected: FAIL — `getActiveDotIndex is not a function`

- [ ] **Step 3: Write slideshow.js**

  Replace `assets/js/slideshow.js` with:

  ```js
  /**
   * slideshow.js — Homepage dot indicator.
   * Highlights the dot corresponding to the currently visible slide.
   * Pure functions exported for unit testing; DOM wiring in initSlideshow().
   */

  'use strict';

  /**
   * Returns the zero-based index of the active slide.
   * @param {number} scrollTop  - Current scrollTop of the slides container.
   * @param {number} slideHeight - Height of one slide (viewport height).
   * @returns {number}
   */
  function getActiveDotIndex(scrollTop, slideHeight) {
    if (scrollTop < 0) return 0;
    return Math.round(scrollTop / slideHeight);
  }

  /**
   * Wires up the scroll listener and dot updates.
   * @param {Element} containerEl - The scroll-snap container (.slideshow).
   * @param {NodeList} dots        - All `.slideshow__dot` elements.
   */
  function initSlideshow(containerEl, dots) {
    if (!containerEl || !dots.length) return;

    function update() {
      const idx = getActiveDotIndex(containerEl.scrollTop, containerEl.clientHeight);
      dots.forEach((dot, i) => {
        dot.classList.toggle('slideshow__dot--active', i === idx);
        dot.setAttribute('aria-current', i === idx ? 'true' : 'false');
      });
    }

    containerEl.addEventListener('scroll', update, { passive: true });
    update(); // Set initial state
  }

  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
      const container = document.querySelector('.slideshow');
      const dots      = document.querySelectorAll('.slideshow__dot');
      initSlideshow(container, dots);
    });
  }

  if (typeof module !== 'undefined') {
    module.exports = { getActiveDotIndex, initSlideshow };
  }
  ```

- [ ] **Step 4: Run test — verify it passes**

  ```bash
  npm test -- --testPathPattern=slideshow
  ```

  Expected: PASS — 4 tests passing

- [ ] **Step 5: Write front-page.php**

  Replace `front-page.php` with:

  ```php
  <?php
  /**
   * Homepage template — full-screen scroll-snap project slideshow.
   */

  get_header();

  $projects = new WP_Query( [
      'post_type'      => 'photo_project',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'post_status'    => 'publish',
  ] );
  ?>

  <?php if ( $projects->have_posts() ) : ?>

    <div class="slideshow" role="region" aria-label="<?php esc_attr_e( 'Photo projects', 'aonghus-photography' ); ?>">

      <?php while ( $projects->have_posts() ) : $projects->the_post(); ?>

        <section class="slideshow__slide"
                 role="group"
                 aria-label="<?php echo esc_attr( get_the_title() ); ?>">

          <a class="slideshow__link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View project: %s', 'aonghus-photography' ), get_the_title() ) ); ?>">

            <?php if ( has_post_thumbnail() ) : ?>
              <?php the_post_thumbnail( 'full', [ 'class' => 'slideshow__cover', 'loading' => 'eager', 'decoding' => 'async' ] ); ?>
            <?php endif; ?>

            <div class="slideshow__meta">
              <h2 class="slideshow__title"><?php the_title(); ?></h2>
            </div>

          </a>

        </section><!-- .slideshow__slide -->

      <?php endwhile; wp_reset_postdata(); ?>

    </div><!-- .slideshow -->

    <!-- Dot navigation -->
    <nav class="slideshow__dots" aria-label="<?php esc_attr_e( 'Project navigation', 'aonghus-photography' ); ?>">
      <?php
      $projects->rewind_posts();
      $i = 0;
      while ( $projects->have_posts() ) :
          $projects->the_post();
          $i++;
      ?>
        <button class="slideshow__dot <?php echo $i === 1 ? 'slideshow__dot--active' : ''; ?>"
                aria-current="<?php echo $i === 1 ? 'true' : 'false'; ?>"
                aria-label="<?php echo esc_attr( get_the_title() ); ?>">
        </button>
      <?php endwhile; wp_reset_postdata(); ?>
    </nav>

  <?php else : ?>

    <div class="slideshow slideshow--empty">
      <p class="slideshow__empty-msg">
        <?php esc_html_e( 'No projects yet — add your first project in wp-admin.', 'aonghus-photography' ); ?>
      </p>
    </div>

  <?php endif; ?>

  <?php get_footer(); ?>
  ```

- [ ] **Step 6: Write slideshow.css**

  Replace `assets/css/slideshow.css` with:

  ```css
  /* ============================================================
     HOMEPAGE SLIDESHOW — full-viewport scroll-snap
  ============================================================ */

  /* Remove default site-content padding for homepage */
  .home .site-content {
    padding-top: 0;
    height: 100vh;
    overflow: hidden;
  }

  .slideshow {
    height: 100vh;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    -webkit-overflow-scrolling: touch;
  }

  /* Hide scrollbar visually */
  .slideshow::-webkit-scrollbar { display: none; }
  .slideshow { scrollbar-width: none; }

  .slideshow__slide {
    position: relative;
    width: 100vw;
    height: 100vh;
    scroll-snap-align: start;
    overflow: hidden;
  }

  .slideshow__link {
    display: block;
    width: 100%;
    height: 100%;
    position: relative;
  }

  .slideshow__cover {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  /* Dark gradient overlay for text legibility */
  .slideshow__link::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
      to top,
      rgba(0, 0, 0, 0.55) 0%,
      rgba(0, 0, 0, 0) 50%
    );
    pointer-events: none;
  }

  .slideshow__meta {
    position: absolute;
    bottom: 40px;
    left: 32px;
    z-index: 2;
  }

  .slideshow__title {
    font-size: clamp(1.25rem, 3vw, 2rem);
    font-weight: 300;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--colour-accent);
  }

  /* Empty state */
  .slideshow--empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
  }

  .slideshow__empty-msg {
    color: var(--colour-muted);
    font-size: 14px;
    letter-spacing: 0.04em;
  }

  /* Dot navigation — fixed right edge */
  .slideshow__dots {
    position: fixed;
    right: 24px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 50;
  }

  .slideshow__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    border: none;
    cursor: pointer;
    padding: 0;
    transition: background var(--transition), transform var(--transition);
  }

  .slideshow__dot--active {
    background: var(--colour-accent);
    transform: scale(1.4);
  }
  ```

- [ ] **Step 7: Verify slideshow on site**

  Open the site homepage. You should see:
  - Test project cover image filling the full viewport
  - Project title overlaid bottom-left
  - One active dot on the right edge
  - If you add a second project in wp-admin, scrolling should snap to it and update the dot

- [ ] **Step 8: Commit**

  ```bash
  git add front-page.php assets/css/slideshow.css assets/js/slideshow.js tests/js/slideshow.test.js
  git commit -m "feat: homepage full-screen scroll-snap slideshow with passing Jest tests"
  ```

---

## Chunk 4: Project Page — Square Grid + Lightbox

### Task 8: Single project template, grid CSS, GLightbox

**Files:**
- Modify: `aonghus-photography/single-photo_project.php`
- Modify: `aonghus-photography/assets/css/project.css`
- Modify: `aonghus-photography/assets/js/lightbox.js`

- [ ] **Step 1: Write single-photo_project.php**

  Replace `single-photo_project.php` with:

  ```php
  <?php
  /**
   * Single photo_project template — square grid + GLightbox.
   */

  get_header();

  while ( have_posts() ) :
      the_post();

      $gallery = get_field( 'project_gallery' );
      $count   = is_array( $gallery ) ? count( $gallery ) : 0;
  ?>

  <article class="project" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <header class="project__header">
      <a class="project__back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        &larr; <?php esc_html_e( 'Projects', 'aonghus-photography' ); ?>
      </a>
      <div class="project__heading">
        <h1 class="project__title"><?php the_title(); ?></h1>
        <?php if ( $count > 0 ) : ?>
          <p class="project__count">
            <?php echo esc_html( sprintf(
              /* translators: %d: number of photos */
              _n( '%d photograph', '%d photographs', $count, 'aonghus-photography' ),
              $count
            ) ); ?>
          </p>
        <?php endif; ?>
      </div>
    </header>

    <?php if ( $gallery ) : ?>

      <div class="project__grid">
        <?php foreach ( $gallery as $image ) :
          $full   = esc_url( $image['url'] );
          $thumb  = esc_url( $image['sizes']['large'] ?? $image['url'] );
          $alt    = esc_attr( $image['alt'] ?: get_the_title() );
        ?>
          <a class="project__grid-item"
             href="<?php echo $full; ?>"
             data-gallery="project-<?php the_ID(); ?>"
             aria-label="<?php echo $alt; ?>">
            <img src="<?php echo $thumb; ?>"
                 alt="<?php echo $alt; ?>"
                 loading="lazy"
                 decoding="async">
          </a>
        <?php endforeach; ?>
      </div><!-- .project__grid -->

    <?php else : ?>

      <p class="project__no-photos">
        <?php esc_html_e( 'No photos added to this project yet.', 'aonghus-photography' ); ?>
      </p>

    <?php endif; ?>

    <?php if ( get_the_content() ) : ?>
      <div class="project__description">
        <?php the_content(); ?>
      </div>
    <?php endif; ?>

  </article><!-- .project -->

  <?php endwhile; ?>

  <?php get_footer(); ?>
  ```

- [ ] **Step 2: Write project.css**

  Replace `assets/css/project.css` with:

  ```css
  /* ============================================================
     PROJECT PAGE — square grid + lightbox
  ============================================================ */

  .project {
    padding-bottom: 80px;
  }

  .project__header {
    padding: 32px 24px 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .project__back {
    font-size: 12px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--colour-muted);
    transition: color var(--transition);
  }

  .project__back:hover {
    color: var(--colour-text);
    opacity: 1;
  }

  .project__heading {
    display: flex;
    align-items: baseline;
    gap: 16px;
    flex-wrap: wrap;
  }

  .project__title {
    font-size: clamp(1.25rem, 3vw, 2rem);
    font-weight: 300;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .project__count {
    font-size: 12px;
    color: var(--colour-muted);
    letter-spacing: 0.04em;
  }

  /* Square grid */
  .project__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2px;
    padding: 0 2px;
  }

  .project__grid-item {
    aspect-ratio: 1 / 1;
    overflow: hidden;
    display: block;
    cursor: zoom-in;
  }

  .project__grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.4s ease;
  }

  .project__grid-item:hover img {
    transform: scale(1.04);
  }

  /* Description below grid */
  .project__description {
    max-width: 640px;
    margin: 48px auto 0;
    padding: 0 24px;
    color: var(--colour-muted);
    line-height: 1.7;
    font-size: 15px;
  }

  .project__no-photos {
    text-align: center;
    padding: 64px 24px;
    color: var(--colour-muted);
    font-size: 14px;
  }

  /* Responsive breakpoints */
  @media (max-width: 1023px) {
    .project__grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 767px) {
    .project__grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
  ```

- [ ] **Step 3: Write lightbox.js**

  Replace `assets/js/lightbox.js` with:

  ```js
  /**
   * lightbox.js — Initialises GLightbox on project grid images.
   * GLightbox is loaded via CDN (declared in functions.php enqueue below).
   * No unit test needed: this is pure DOM wiring of a third-party library.
   */

  'use strict';

  if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof GLightbox === 'undefined') return;

      GLightbox({
        selector: '.project__grid-item',
        touchNavigation: true,
        loop: true,
        keyboardNavigation: true,
        closeOnOutsideClick: true,
        skin: 'clean',
      });
    });
  }
  ```

- [ ] **Step 4: Enqueue GLightbox CDN in functions.php**

  In `functions.php`, inside `aonghus_photography_scripts()`, add before the closing brace:

  ```php
  // GLightbox — only on single project pages
  if ( is_singular( 'photo_project' ) ) {
      wp_enqueue_style(
          'glightbox',
          'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
          [],
          '3.3.0'
      );
      wp_enqueue_script(
          'glightbox',
          'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
          [],
          '3.3.0',
          true
      );
  }
  ```

- [ ] **Step 5: Write archive-photo_project.php (redirect)**

  Replace `archive-photo_project.php` with:

  ```php
  <?php
  /**
   * Archive template for photo_project — redirects to homepage.
   * The project slideshow IS the homepage; no separate archive page is needed.
   */
  wp_redirect( home_url( '/' ), 301 );
  exit;
  ```

- [ ] **Step 6: Verify on site**

  Navigate to the Test Project you created in Task 4. Verify:
  - Project title and photo count appear in the header
  - Photos display in a 4-column square grid (desktop)
  - Hovering a photo shows a subtle zoom
  - Clicking a photo opens the GLightbox full-screen viewer
  - Left/right arrows and keyboard navigation work in the lightbox
  - Back arrow returns to homepage

  On mobile (resize to <768px): grid should be 2 columns.

- [ ] **Step 7: Commit**

  ```bash
  git add single-photo_project.php archive-photo_project.php assets/css/project.css assets/js/lightbox.js functions.php
  git commit -m "feat: project page with square grid and GLightbox lightbox"
  ```

---

## Chunk 5: WooCommerce, Generic Pages, Final Polish

### Task 9: WooCommerce dark theme integration

**Files:**
- Modify: `aonghus-photography/inc/woocommerce.php`
- Create: `aonghus-photography/woocommerce/archive-product.php`
- Create: `aonghus-photography/woocommerce/single-product.php`
- Modify: `aonghus-photography/assets/css/shop.css`

- [ ] **Step 1: Install WooCommerce plugin**

  In wp-admin go to **Plugins → Add New**, search **WooCommerce**, install and activate. Run the setup wizard (skip payment and shipping for now — this is just for styling).

- [ ] **Step 2: Write inc/woocommerce.php**

  Replace `inc/woocommerce.php` with:

  ```php
  <?php
  /**
   * WooCommerce integration.
   * Declares theme support, removes default WC styles (we supply our own via shop.css).
   */

  // Declare WooCommerce support so WC knows the theme is compatible.
  add_action( 'after_setup_theme', function () {
      add_theme_support( 'woocommerce' );
      add_theme_support( 'wc-product-gallery-zoom' );
      add_theme_support( 'wc-product-gallery-lightbox' );
      add_theme_support( 'wc-product-gallery-slider' );
  } );

  // Remove all default WooCommerce stylesheet enqueues.
  // Our shop.css handles all WC visual styling.
  add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

  // Remove WooCommerce breadcrumb (not needed with our minimal nav).
  add_filter( 'woocommerce_breadcrumb_defaults', function ( $defaults ) {
      $defaults['delimiter'] = ' &rsaquo; ';
      return $defaults;
  } );
  ```

- [ ] **Step 3: Create woocommerce/ directory and copy base templates**

  WooCommerce template overrides must match WooCommerce's own template structure. Copy the base templates from the WooCommerce plugin:

  ```bash
  mkdir -p aonghus-photography/woocommerce

  # Copy from WooCommerce plugin (path will vary — find your LocalWP plugins directory)
  cp "~/Local Sites/photography/app/public/wp-content/plugins/woocommerce/templates/archive-product.php" \
     aonghus-photography/woocommerce/archive-product.php

  cp "~/Local Sites/photography/app/public/wp-content/plugins/woocommerce/templates/single-product.php" \
     aonghus-photography/woocommerce/single-product.php
  ```

  Note: We copy the originals as a starting point, then trim what we don't need. WooCommerce checks that override templates exist before serving them.

- [ ] **Step 4: Verify woocommerce/archive-product.php is intact**

  Open `woocommerce/archive-product.php`. Confirm it starts with `<?php` and contains WooCommerce's default template markup. No changes are needed — WooCommerce automatically adds `.woocommerce` and `.woocommerce-page` body classes, which `shop.css` already targets. Leave the file as copied.

- [ ] **Step 5: Write shop.css**

  Replace `assets/css/shop.css` with:

  ```css
  /* ============================================================
     SHOP — WooCommerce dark theme overrides
     Applies to: archive-product, single-product, cart, checkout,
     account pages. No WC default stylesheets are loaded.
  ============================================================ */

  /* ---- Shared WooCommerce page wrapper ---- */
  .woocommerce,
  .woocommerce-page {
    padding: 80px 24px 64px;
    max-width: 1200px;
    margin: 0 auto;
  }

  /* ---- Page title ---- */
  .woocommerce h1.page-title,
  .woocommerce-page h1.page-title {
    font-size: clamp(1.25rem, 3vw, 2rem);
    font-weight: 300;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--colour-text);
    margin-bottom: 48px;
  }

  /* ---- Product grid ---- */
  .woocommerce ul.products {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    list-style: none;
    padding: 0;
    margin: 0;
  }

  @media (max-width: 767px) {
    .woocommerce ul.products {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .woocommerce ul.products li.product a img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
  }

  .woocommerce ul.products li.product a:hover img {
    transform: scale(1.03);
  }

  .woocommerce ul.products li.product .woocommerce-loop-product__title {
    font-size: 13px;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--colour-text);
    margin-top: 12px;
  }

  .woocommerce ul.products li.product .price {
    color: var(--colour-muted);
    font-size: 13px;
  }

  /* ---- Buttons ---- */
  .woocommerce a.button,
  .woocommerce button.button,
  .woocommerce input.button,
  .woocommerce #respond input#submit {
    background: var(--colour-text);
    color: var(--colour-bg);
    border: none;
    padding: 12px 28px;
    font-size: 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity var(--transition);
    border-radius: 0;
  }

  .woocommerce a.button:hover,
  .woocommerce button.button:hover {
    background: var(--colour-text);
    opacity: 0.8;
  }

  /* ---- Single product ---- */
  .woocommerce div.product div.images img {
    width: 100%;
    display: block;
  }

  .woocommerce div.product .product_title {
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    font-weight: 300;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--colour-text);
    margin-bottom: 12px;
  }

  .woocommerce div.product .price {
    color: var(--colour-muted);
    font-size: 16px;
    margin-bottom: 24px;
  }

  /* ---- Forms (cart, checkout, account) ---- */
  .woocommerce form .form-row input.input-text,
  .woocommerce form .form-row textarea {
    background: #1a1a1a;
    border: 1px solid #333;
    color: var(--colour-text);
    padding: 10px 14px;
    width: 100%;
    font-family: var(--font-stack);
    font-size: 14px;
  }

  .woocommerce form .form-row label {
    color: var(--colour-muted);
    font-size: 12px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 6px;
    display: block;
  }

  /* ---- Cart table ---- */
  .woocommerce table.shop_table {
    border-collapse: collapse;
    width: 100%;
    color: var(--colour-text);
  }

  .woocommerce table.shop_table th,
  .woocommerce table.shop_table td {
    padding: 16px 8px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    font-size: 13px;
    text-align: left;
  }

  .woocommerce table.shop_table th {
    color: var(--colour-muted);
    font-weight: 400;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  /* ---- Notices ---- */
  .woocommerce-message,
  .woocommerce-error,
  .woocommerce-info {
    background: #1a1a1a;
    border-top: 3px solid var(--colour-text);
    color: var(--colour-text);
    padding: 16px 20px;
    margin-bottom: 24px;
    font-size: 14px;
    list-style: none;
  }

  .woocommerce-error {
    border-top-color: #c0392b;
  }
  ```

- [ ] **Step 6: Verify WooCommerce shop**

  - Visit `/shop/` — products grid should appear with dark background, uppercase titles, square images.
  - Visit a product page — should match dark aesthetic.
  - Add to cart, visit `/cart/` and `/checkout/` — dark form fields and table.
  - Browser DevTools → Network: confirm no WooCommerce CSS files load (only our `shop.css`).

- [ ] **Step 7: Commit**

  ```bash
  git add inc/woocommerce.php woocommerce/ assets/css/shop.css
  git commit -m "feat: WooCommerce dark theme integration"
  ```

---

### Task 10: Generic page template + final polish

**Files:**
- Modify: `aonghus-photography/page.php`
- Modify: `aonghus-photography/index.php`

- [ ] **Step 1: Write page.php**

  Replace `page.php` with:

  ```php
  <?php
  /**
   * Generic page template — About, Contact, etc.
   */

  get_header();

  while ( have_posts() ) :
      the_post();
  ?>

  <article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
    <h1 class="entry-title"><?php the_title(); ?></h1>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </article>

  <?php endwhile; ?>

  <?php get_footer(); ?>
  ```

- [ ] **Step 2: Write index.php fallback**

  Replace `index.php` with:

  ```php
  <?php
  /**
   * Fallback template — WordPress requires this file to exist.
   * In practice, more specific templates (front-page.php, page.php, etc.)
   * will always be used first.
   */

  get_header();
  ?>
  <div class="page-content">
    <p><?php esc_html_e( 'Nothing found.', 'aonghus-photography' ); ?></p>
  </div>
  <?php get_footer(); ?>
  ```

- [ ] **Step 3: Create About and Contact pages in wp-admin**

  Go to **Pages → Add New**:
  - Create page titled **About** with any placeholder content. Publish.
  - Create page titled **Contact** with any placeholder content. Publish.

  Verify: Both pages load with dark background and correct typography from `main.css`.

- [ ] **Step 4: Run full Jest test suite**

  ```bash
  npm test
  ```

  Expected: All tests pass (navigation: 4, slideshow: 4 = 8 total).

- [ ] **Step 5: Final checklist**

  Manually verify each of the following:

  - [ ] Homepage: full-screen cover image, title overlay, dot indicator, scroll between projects
  - [ ] Homepage: clicking a project navigates to its page
  - [ ] Project page: title, photo count, 4-column grid (desktop), 2-column (mobile)
  - [ ] Project page: clicking photo opens GLightbox, arrows and keyboard work, swipe works on mobile
  - [ ] Project page: back arrow returns to homepage
  - [ ] Nav: all 4 links present on desktop
  - [ ] Nav mobile: hamburger toggles full-screen overlay
  - [ ] Shop: `/shop/` shows products in dark grid
  - [ ] Shop: single product, cart, checkout all dark-themed
  - [ ] About and Contact pages: load with dark style

- [ ] **Step 6: Final commit and push**

  ```bash
  git add page.php index.php
  git commit -m "feat: generic page template and index fallback — theme complete"
  git push origin feat/wordpress-photography-theme-spec
  ```

---

## Running All Tests

```bash
npm test
```

Expected output:
```
PASS tests/js/navigation.test.js
PASS tests/js/slideshow.test.js

Test Suites: 2 passed, 2 total
Tests:       8 passed, 8 total
```
