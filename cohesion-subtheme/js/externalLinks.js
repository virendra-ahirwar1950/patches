if (!String.prototype.startsWith) {
  Object.defineProperty(String.prototype, 'startsWith', {
    value: function(search, rawPos) {
      var pos = rawPos > 0 ? rawPos|0 : 0;
      return this.substring(pos, pos + search.length) === search;
    }
  });
}

(function ($) {
  Drupal.behaviors.externalLinks = {
    attach: function (context, settings) {
      $('a', context).once('dx8-subtheme-external-link').filter(function () {
        var overridden = false;
        if (this.dataset.targetself) {
          overridden = this.dataset.targetself;
        }

        return this.hostname != window.location.hostname && this.href != '#' && !this.href.startsWith('mailto:') && !overridden;
      }).attr('target', '_blank').addRel('noopener');
    }
  };

  $.fn.addRel = function(val) {
    return this.each(function() {
      var $this = $(this);
      var current = $this.attr('rel') ? $this.attr('rel').split(' ') : [];
      current.push(val);

      $this.attr('rel', current.filter(function(value, index, self) {
        return self.indexOf(value) === index;
      }).join(' '));
    });
  };
})(jQuery);