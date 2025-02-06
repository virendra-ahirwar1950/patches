CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
------------
Progressive Web Apps are:

 * Reliable — Load instantly and never show an "Offline" screen to the visitor,
  even in uncertain network conditions.
 * Fast — Respond quickly to user interactions with silky smooth animations
  and no janky scrolling.
 * Engaging — Feel like a natural app on the device, with an immersive user
  experience.
  This new level of quality allows Progressive Web Apps to earn a place on the
   user’s home screen.

   Continue reading more about PWAs from Google or on MDN.

   In general a PWA depends on the following technologies to be available:

   Service Workers
   Web App Manifest
   HTTPS

 What does the PWA Drupal module do?
  The main benefit of this module is the use of Service Worker for caching and
 offline capabilities. Once the Service Worker is active, page loading is faster:

 * All JS and CSS files will always be served from cache while being refreshed
  in the background. Same thing as Stale While Revalidate in Varnish.
 * All pages are fetched from the network (as before) and a copy is kept in
  cache so it will be available when offline.
 * Images are cached unless the save-data header is detected in order to be
  mindful of bandwidth usage and cache size. A fallback image should appear for
  any uncached image.
 The module will also create a configurable manifest.json file to make the
 website installable on supporting mobile devices. Out of the box, the module
 fulfills enough PWA requirements that the "add to home screen" prompt is
 automatically triggered when a visitor returns often enough to your website.
  It provides a perfect PWA Lighthouse audit score by default as well.

REQUIREMENTS
------------

  Service workers are only available to "secure origins" (HTTPS sites, basically)
  http://localhost is also considered a secure origin see.
  https://www.chromium.org/blink/serviceworker/service-worker-faq

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.

CONFIGURATION
-------------

 Visit /admin/config/pwa/settings

 Add URLs you would like to to "cache on install." These will serve the page
 offline even if they have not been visited. Make sure the URL is not a 404.
 Make sure are these are relative URLs, tokens or regex are not supported.
 Because we cache these, you may need to flush your cache when changing this
 value.

 Sometimes offline caching of a page may cause unintended issues, in this cause
 you can add a regex to exclude any of these URLs.

By default, the manifest has the following properties:

 * name: from variable_get('site_name')
 * short_name: from variable_get('site_name')
 * description: blank
 * lang: default site language
 * dir: default site language direction
 * background_color: white
 * theme_color: white
 * start_url: /
 * orientation: portrait
 * display: standalone
 * icons: Drupal icons in 144, 192 and 512

 For PWA extras make sure to generate and upload your own launch screen icons.
 see https://www.drupal.org/project/pwa/issues/3066848#comment-13246853

 You can update your cache URLs programmatically, see
 https://www.drupal.org/project/pwa/issues/3032538

MAINTAINERS
-----------

Current maintainers:

 * AlexBorsody - (https://www.drupal.org/u/alexborsody)
 * rupl - (https://www.drupal.org/u/rupl)
 * nod - (https://www.drupal.org/u/nod_)
