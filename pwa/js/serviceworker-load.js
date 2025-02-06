"use strict";

(function (Drupal, drupalSettings, navigator, window) {
  'use strict';

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      let scope = drupalSettings.path.baseUrl;
      if (typeof drupalSettings.pwa.scope !== "undefined" && drupalSettings.pwa.scope !== null) {
        scope = drupalSettings.pwa.scope;
      }
      navigator.serviceWorker.register(drupalSettings.pwa.installPath, {
        scope: scope
      }).then(function (registration) {
        console.log("Service Worker registered! Scope: ".concat(registration.scope));
      }).catch(function (err) {
        console.log("Service Worker registration failed: ".concat(err));
      });
    });
  }
})(Drupal, drupalSettings, navigator, window);
