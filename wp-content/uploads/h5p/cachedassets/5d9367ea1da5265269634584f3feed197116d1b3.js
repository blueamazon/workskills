var H5P = H5P || {};

/**
 * Constructor.
 *
 * @param {object} params Options for this library.
 */
H5P.Text = function (params) {
  this.text = params.text === undefined ? '<em>New text</em>' : params.text;
};

/**
 * Wipe out the content of the wrapper and put our HTML in it.
 *
 * @param {jQuery} $wrapper
 */
H5P.Text.prototype.attach = function ($wrapper) {
  $wrapper.addClass('h5p-text').html(this.text);
};
;
var H5P = H5P || {};
/**
 * Transition contains helper function relevant for transitioning
 */
H5P.Transition = (function ($) {

  /**
   * @class
   * @namespace H5P
   */
  Transition = {};

  /**
   * @private
   */
  Transition.transitionEndEventNames = {
    'WebkitTransition': 'webkitTransitionEnd',
    'transition':       'transitionend',
    'MozTransition':    'transitionend',
    'OTransition':      'oTransitionEnd',
    'msTransition':     'MSTransitionEnd'
  };

  /**
   * @private
   */
  Transition.cache = [];

  /**
   * Get the vendor property name for an event
   *
   * @function H5P.Transition.getVendorPropertyName
   * @static
   * @private
   * @param  {string} prop Generic property name
   * @return {string}      Vendor specific property name
   */
  Transition.getVendorPropertyName = function (prop) {

    if (Transition.cache[prop] !== undefined) {
      return Transition.cache[prop];
    }

    var div = document.createElement('div');

    // Handle unprefixed versions (FF16+, for example)
    if (prop in div.style) {
      Transition.cache[prop] = prop;
    }
    else {
      var prefixes = ['Moz', 'Webkit', 'O', 'ms'];
      var prop_ = prop.charAt(0).toUpperCase() + prop.substr(1);

      if (prop in div.style) {
        Transition.cache[prop] = prop;
      }
      else {
        for (var i = 0; i < prefixes.length; ++i) {
          var vendorProp = prefixes[i] + prop_;
          if (vendorProp in div.style) {
            Transition.cache[prop] = vendorProp;
            break;
          }
        }
      }
    }

    return Transition.cache[prop];
  };

  /**
   * Get the name of the transition end event
   *
   * @static
   * @private
   * @return {string}  description
   */
  Transition.getTransitionEndEventName = function () {
    return Transition.transitionEndEventNames[Transition.getVendorPropertyName('transition')] || undefined;
  };

  /**
   * Helper function for listening on transition end events
   *
   * @function H5P.Transition.onTransitionEnd
   * @static
   * @param  {domElement} $element The element which is transitioned
   * @param  {function} callback The callback to be invoked when transition is finished
   * @param  {number} timeout  Timeout in milliseconds. Fallback if transition event is never fired
   */
  Transition.onTransitionEnd = function ($element, callback, timeout) {
    // Fallback on 1 second if transition event is not supported/triggered
    timeout = timeout || 1000;
    Transition.transitionEndEventName = Transition.transitionEndEventName || Transition.getTransitionEndEventName();
    var callbackCalled = false;

    var doCallback = function () {
      if (callbackCalled) {
        return;
      }
      $element.off(Transition.transitionEndEventName, callback);
      callbackCalled = true;
      clearTimeout(timer);
      callback();
    };

    var timer = setTimeout(function () {
      doCallback();
    }, timeout);

    $element.on(Transition.transitionEndEventName, function () {
      doCallback();
    });
  };

  /**
   * Wait for a transition - when finished, invokes next in line
   *
   * @private
   *
   * @param {Object[]}    transitions             Array of transitions
   * @param {H5P.jQuery}  transitions[].$element  Dom element transition is performed on
   * @param {number=}     transitions[].timeout   Timeout fallback if transition end never is triggered
   * @param {bool=}       transitions[].break     If true, sequence breaks after this transition
   * @param {number}      index                   The index for current transition
   */
  var runSequence = function (transitions, index) {
    if (index >= transitions.length) {
      return;
    }

    var transition = transitions[index];
    H5P.Transition.onTransitionEnd(transition.$element, function () {
      if (transition.end) {
        transition.end();
      }
      if (transition.break !== true) {
        runSequence(transitions, index+1);
      }
    }, transition.timeout || undefined);
  };

  /**
   * Run a sequence of transitions
   *
   * @function H5P.Transition.sequence
   * @static
   * @param {Object[]}    transitions             Array of transitions
   * @param {H5P.jQuery}  transitions[].$element  Dom element transition is performed on
   * @param {number=}     transitions[].timeout   Timeout fallback if transition end never is triggered
   * @param {bool=}       transitions[].break     If true, sequence breaks after this transition
   */
  Transition.sequence = function (transitions) {
    runSequence(transitions, 0);
  };

  return Transition;
})(H5P.jQuery);
;
/**
 * Defines the H5P.ImageHotspots class
 */
H5P.ImageHotspots = (function ($, EventDispatcher) {

  /**
   * Default font size
   *
   * @constant
   * @type {number}
   * @default
   */
  var DEFAULT_FONT_SIZE = 24;

  /**
   * Creates a new Image hotspots instance
   *
   * @class
   * @augments H5P.EventDispatcher
   * @namespace H5P
   * @param {Object} options
   * @param {number} id
   */
  function ImageHotspots(options, id) {
    EventDispatcher.call(this);

    // Extend defaults with provided options
    this.options = $.extend(true, {}, {
      image: null,
      hotspots: [],
      hotspotNumberLabel: 'Hotspot #num',
      closeButtonLabel: 'Close',
      iconType: 'icon',
      icon: 'plus'
    }, options);
    // Keep provided id.
    this.id = id;
    this.isSmallDevice = false;
  }
  // Extends the event dispatcher
  ImageHotspots.prototype = Object.create(EventDispatcher.prototype);
  ImageHotspots.prototype.constructor = ImageHotspots;

  /**
   * Attach function called by H5P framework to insert H5P content into
   * page
   *
   * @public
   * @param {H5P.jQuery} $container
   */
  ImageHotspots.prototype.attach = function ($container) {
    var self = this;
    self.$container = $container;

    if (this.options.image === null || this.options.image === undefined) {
      $container.append('<div class="background-image-missing">Missing required background image</div>');
      return;
    }

    // Need to know since ios uses :hover when clicking on an element
    if (/(iPad|iPhone|iPod)/g.test( navigator.userAgent ) === false) {
      $container.addClass('not-an-ios-device');
    }

    $container.addClass('h5p-image-hotspots');

    this.$hotspotContainer = $('<div/>', {
      'class': 'h5p-image-hotspots-container'
    });

    if (this.options.image && this.options.image.path) {
      this.$image = $('<img/>', {
        'class': 'h5p-image-hotspots-background',
        src: H5P.getPath(this.options.image.path, this.id)
      }).appendTo(this.$hotspotContainer);

      // Set alt text of image
      if (this.options.backgroundImageAltText) {
        this.$image.attr('alt', this.options.backgroundImageAltText);
      }
      else {
        // Ignore image if no alternative text for assistive technologies
        this.$image.attr('aria-hidden', true);
      }
    }

    var isSmallDevice = function () {
      return self.isSmallDevice;
    };

    // Add hotspots
    var numHotspots = this.options.hotspots.length;
    this.hotspots = [];

    this.options.hotspots.sort(function (a, b) {
      // Sanity checks, move data to the back if invalid
      var firstIsValid = a.position && a.position.x && a.position.y;
      var secondIsValid = b.position && b.position.x && b.position.y;
      if (!firstIsValid) {
        return 1;
      }

      if (!secondIsValid) {
        return -1;
      }

      // Order top-to-bottom, left-to-right
      if (a.position.y !== b.position.y) {
        return a.position.y < b.position.y ? -1 : 1;
      }
      else {
        // a and b y position is equal, sort on x
        return a.position.x < b.position.x ? -1 : 1;
      }
    });

    for (var i=0; i<numHotspots; i++) {
      try {
        var hotspot = new ImageHotspots.Hotspot(this.options.hotspots[i], this.options, this.id, isSmallDevice, self);
        hotspot.appendTo(this.$hotspotContainer);
        var hotspotTitle = this.options.hotspots[i].header ? this.options.hotspots[i].header
          : this.options.hotspotNumberLabel.replace('#num', (i + 1).toString());
        hotspot.setTitle(hotspotTitle);
        this.hotspots.push(hotspot);
      }
      catch (e) {
        H5P.error(e);
      }
    }
    this.$hotspotContainer.appendTo($container);

    self.resize();
    this.on('resize', self.resize, self);

    this.on('enterFullScreen', function () {
      // Resize image when entering fullscreen.
      setTimeout(function () {
        self.trigger('resize');

        // Trap focus
        self.toggleTrapFocus(true);
      });
    });

    this.on('exitFullScreen', function () {
      // Do not rely on that isFullscreen has been updated
      self.trigger('resize', {forceImageHeight: true});
      self.toggleTrapFocus(false);
    });
  };

  /**
   * Toggle trap focus between hotspots
   *
   * @param {boolean} enable True to enable, otherwise will be released
   */
  ImageHotspots.prototype.toggleTrapFocus = function (enable) {
    if (this.hotspots.length < 1) {
      return;
    }
    if (enable) {
      // focus first hotspot
      this.hotspots[0].focus();

      // Trap focus
      if (this.hotspots.length > 1) {
        this.hotspots[this.hotspots.length - 1].setTrapFocusTo(this.hotspots[0]);
        this.hotspots[0].setTrapFocusTo(this.hotspots[this.hotspots.length - 1], true);
      }
    }
    else {
      // Untrap focus
      this.hotspots[this.hotspots.length - 1].releaseTrapFocus();
      this.hotspots[0].releaseTrapFocus();
    }
  };

  /**
   * Handle resizing
   * @private
   * @param {Event} [e]
   * @param {boolean} [e.forceImageHeight]
   * @param {boolean} [e.decreaseSize]
   */
  ImageHotspots.prototype.resize = function (e) {
    if (this.options.image === null) {
      return;
    }

    var self = this;
    var containerWidth = self.$container.width();
    var containerHeight = self.$container.height();
    var width = containerWidth;
    var height = Math.floor((width/self.options.image.width) * self.options.image.height);
    var forceImageHeight = e && e.data && e.data.forceImageHeight;

    // Check if decreasing iframe size
    var decreaseSize = e && e.data && e.data.decreaseSize;
    if (!decreaseSize) {
      self.$container.css('width', '');
    }

    // If fullscreen, we have both a max width and max height.
    if (!forceImageHeight && H5P.isFullscreen && height > containerHeight) {
      height = containerHeight;
      width = Math.floor((height/self.options.image.height) * self.options.image.width);
    }

    // Check if we need to apply semi full screen fix.
    if (self.$container.is('.h5p-semi-fullscreen')) {

      // Reset semi fullscreen width
      self.$container.css('width', '');

      // Decrease iframe size
      if (!decreaseSize) {
        self.$hotspotContainer.css('width', '10px');
        self.$image.css('width', '10px');

        // Trigger changes
        setTimeout(function () {
          self.trigger('resize', {decreaseSize: true});
        }, 200);
      }

      // Set width equal to iframe parent width, since iframe content has not been updated yet.
      var $iframe = $(window.frameElement);
      if ($iframe) {
        var $iframeParent = $iframe.parent();
        width = $iframeParent.width();
        self.$container.css('width', width + 'px');
      }
    }

    self.$image.css({
      width: width + 'px',
      height: height + 'px'
    });

    if (self.initialWidth === undefined) {
      self.initialWidth = self.$container.width();
    }

    self.fontSize = (DEFAULT_FONT_SIZE * (width/self.initialWidth));

    self.$hotspotContainer.css({
      width: width + 'px',
      height: height + 'px',
      fontSize: self.fontSize + 'px'
    });

    self.isSmallDevice = (containerWidth / parseFloat($("body").css("font-size")) < 40);
  };

  return ImageHotspots;
})(H5P.jQuery, H5P.EventDispatcher);
;
/**
 * Defines the ImageHotspots.Hotspot class
 */
(function ($, ImageHotspots) {

  /**
   * Creates a new Hotspot
   *
   * @class
   * @namespace H5P.ImageHotspots
   * @param  {Object} config
   * @param  {Object} options
   * @param  {number} id
   * @param  {boolean} isSmallDeviceCB
   * @param  {H5P.ImageHotspots} parent
   */
  ImageHotspots.Hotspot = function (config, options, id, isSmallDeviceCB, parent) {
    var self = this;
    this.config = config;
    this.visible = false;
    this.id = id;
    this.isSmallDeviceCB = isSmallDeviceCB;
    this.options = options;

    // A utility variable to check if a Predefined icon or an uploaded image should be used.
    var iconImageExists = (options.iconImage !== undefined && options.iconType === 'image');

    if (this.config.content === undefined  || this.config.content.length === 0) {
      throw new Error('Missing content configuration for hotspot. Please fix in editor.');
    }

    // Check if there is an iconImage that should be used instead of fontawesome icons to determine the html element.
    this.$element = $(iconImageExists ? '<img/>' : '<button/>', {
      'class': 'h5p-image-hotspot ' + (!iconImageExists ? 'h5p-image-hotspot-' + options.icon : ''),
      'role': 'button',
      'tabindex': 0,
      'aria-haspopup': true,
      src: iconImageExists ? H5P.getPath(options.iconImage.path, this.id) : undefined,
      click: function () {
        // prevents duplicates while loading
        if (self.loadingPopup) {
          return false;
        }

        if (self.visible) {
          self.hidePopup();
        }
        else {
          self.showPopup(true);
        }
        return false;
      },
      keydown: function (e) {
        if (e.which === 32 || e.which === 13) {
          // Prevent duplicates while loading
          if (self.loadingPopup) {
            return false;
          }

          if (self.visible) {
            self.hidePopup();
          }
          else {
            self.showPopup(true);
          }
          e.stopPropagation();
          return false;
        }
      }
    });
    if (this.config.position.legacyPositioning) {
      this.$element.css({
        top: this.config.position.y + '%',
        left: this.config.position.x + '%',
        color: options.color
      });
    }
    else {
      this.$element.css({
        top: 'calc(' + this.config.position.y + '% - 0.6em)',
        left: 'calc(' + this.config.position.x + '% - 0.6em)',
        color: options.color
      });
    }

    parent.on('resize', function () {
      if (self.popup) {

        self.actionInstances.forEach(function (actionInstance) {
          if (actionInstance.trigger !== undefined) {

            // The reason for this timeout is fullscreen on chrome on android
            setTimeout(function () {
              actionInstance.trigger('resize');
            }, 1);
          }
        });
      }
    });
  };

  /**
   * Append the hotspot to a container
   * @public
   * @param {H5P.jQuery} $container
   */
  ImageHotspots.Hotspot.prototype.appendTo = function ($container) {
    this.$container = $container;
    this.$element.appendTo($container);
  };

  /**
   * Display the popup
   * @param {boolean} [focusPopup] Focuses popup for keyboard accessibility
   */
  ImageHotspots.Hotspot.prototype.showPopup = function (focusPopup) {
    var self = this;

    // Create popup content:
    var $popupBody = $('<div/>', {'class': 'h5p-image-hotspot-popup-body'});
    self.loadingPopup = true;

    this.actionInstances = [];
    var waitForLoaded = [];
    this.config.content.forEach(function (action) {
      var $popupFraction = $('<div>', {
        'class': 'h5p-image-hotspot-popup-body-fraction',
        appendTo: $popupBody
      });

      var actionInstance = H5P.newRunnable(action, self.id);
      self.actionInstances.push(actionInstance);
      if (actionInstance.libraryInfo.machineName === 'H5P.Image' || actionInstance.libraryInfo.machineName === 'H5P.Video') {
        waitForLoaded.push(actionInstance);
      }
      actionInstance.attach($popupFraction);
    });

    var readyToPopup = function () {
      // Disable all hotspots
      self.toggleHotspotsTabindex(true);
      self.visible = true;
      self.popup.show(focusPopup);
      self.$element.addClass('active');
      self.actionInstances.forEach(function (actionInstance) {
        actionInstance.trigger('resize');
      });
    };

    // Popup style
    var popupClass = 'h5p-video';
    if (!waitForLoaded.length) {
      popupClass = 'h5p-text';
    }
    else if (self.actionInstances.length === 1 && self.actionInstances[0].libraryInfo.machineName === 'H5P.Image') {
      popupClass = 'h5p-image';
    }

    // Create Image hot-spots popup
    self.popup = new ImageHotspots.Popup(
      self.$container, $popupBody,
      self.config.position.x,
      self.config.position.y,
      self.$element.outerWidth(),
      self.config.header,
      popupClass,
      self.config.alwaysFullscreen || self.isSmallDeviceCB(),
      self.options
    );

    // Release
    self.popup.on('closed', function (e) {
      self.hidePopup();

      // Refocus hotspot
      if (e.data && e.data.refocus) {
        self.focus();
      }
    });

    // Finished loading popup
    self.popup.on('finishedLoading', function () {
      self.loadingPopup = false;
    });

    if (waitForLoaded.length) {
      var loaded = 0;

      // Wait for libraries to load before showing popup
      waitForLoaded.forEach(function (unloaded) {

        // Signal that library has finished loading
        var fire = function () {
          clearTimeout(timeout);
          unloaded.off('loaded', fire);
          loaded += 1;

          if (loaded >= waitForLoaded.length) {
            setTimeout(function () {
              readyToPopup();
            }, 100);
          }
        };

        // Add timer fallback if loaded event is not triggered
        var timeout = setTimeout(fire, 1000);
        unloaded.on('loaded', fire, {unloaded: unloaded, timeout: timeout});
        unloaded.trigger('resize');
      });

    }
    else {
      setTimeout(function () {
        readyToPopup();
      }, 100);
    }

    // We don't get click events on body for iOS-devices
    $('body').children().on('click.h5p-image-hotspot-popup', function (event) {
      var $target = $(event.target);
      if (self.visible && !$target.hasClass('h5p-enable-fullscreen') && !$target.hasClass('h5p-disable-fullscreen')) {
        self.hidePopup();
      }
    });
  };

  /**
   * Toggle whether hotspots has tabindex
   * @param {boolean} [disable] Disable tabindex if true
   */
  ImageHotspots.Hotspot.prototype.toggleHotspotsTabindex = function (disable) {
    this.$container.find('.h5p-image-hotspot')
      .attr('tabindex', disable ? '-1' : '0')
      .attr('aria-hidden', disable ? true : '');
  };

  /**
   * Hide popup
   * @public
   */
  ImageHotspots.Hotspot.prototype.hidePopup = function () {
    if (this.popup) {
      // We don't get click events on body for iOS-devices
      $('body').children().off('click.h5p-image-hotspot-popup');

      this.popup.hide();
      this.$element.removeClass('active');
      this.visible = false;
      this.popup = undefined;
      this.toggleHotspotsTabindex();
    }
  };

  /**
   * Focus hotspot
   */
  ImageHotspots.Hotspot.prototype.focus = function () {
    this.$element.focus();
  };

  /**
   * Set up trapping of focus
   *
   * @param {ImageHotspots.Hotspot} hotspot Hotspot that focus should be trapped to
   * @param {boolean} [trapReverseTab] Traps when tabbing backwards
   */
  ImageHotspots.Hotspot.prototype.setTrapFocusTo = function (hotspot, trapReverseTab) {
    this.$element.on('keydown.trapfocus', function (e) {
      var keyCombination = e.which === 9 && (trapReverseTab ? e.shiftKey : !e.shiftKey);
      if (keyCombination) {
        hotspot.focus();
        e.stopPropagation();
        return false;
      }
    });
  };

  /**
   * Release trap focus from hotspot
   */
  ImageHotspots.Hotspot.prototype.releaseTrapFocus = function () {
    this.$element.off('keydown.trapfocus');
  };

  /**
   * Set title of hotspot element
   * @param {string} title Title to set for hotspot element
   */
  ImageHotspots.Hotspot.prototype.setTitle = function (title) {
    this.$element.attr('title', title);
    this.$element.attr('aria-label', title);
  };

})(H5P.jQuery, H5P.ImageHotspots);
;
/**
 * Defines the ImageHotspots.Popup class
 */
(function ($, ImageHotspots, EventDispatcher) {

  /**
   * Creates new Popup instance
   *
   * @class
   * @namespace H5P.ImageHotspots
   * @param {H5P.jQuery} $container
   * @param {H5P.jQuery} $content
   * @param {number} x
   * @param {number} y
   * @param {number} hotspotWidth
   * @param {string} header
   * @param {string} className
   * @param {boolean} fullscreen
   * @param {Object} options
   *
   */
  ImageHotspots.Popup = function ($container, $content, x, y, hotspotWidth, header, className, fullscreen, options) {
    EventDispatcher.call(this);

    var self = this;
    this.$container = $container;
    var width = this.$container.width();

    var pointerWidthInPercent = 4;
    hotspotWidth = (hotspotWidth/width)*100;

    var popupLeft = 0;
    var popupWidth = 0;
    var toTheLeft = false;

    if (fullscreen) {
      popupWidth = 100;
      className += ' fullscreen-popup';
    }
    else {
      toTheLeft = (x > 45);
      popupLeft = (toTheLeft ? 0 : (x + hotspotWidth + pointerWidthInPercent));
      popupWidth = (toTheLeft ?  x - pointerWidthInPercent : 100 - popupLeft);
    }

    this.$popupBackground = $('<div/>', {'class': 'h5p-image-hotspots-overlay'});
    this.$popup = $('<div/>', {
      'class': 'h5p-image-hotspot-popup ' + className,
      'role': 'dialog'
    }).css({
      left: (toTheLeft ? '' : '-') + '100%',
      width: popupWidth + '%'
    }).click(function (event) {
      // If clicking on popup, stop propagating:
      event.stopPropagation();
    }).appendTo(this.$popupBackground);

    this.$popupContent = $('<div/>', {'class': 'h5p-image-hotspot-popup-content'});
    if (header) {
      this.$popupHeader = $('<div/>', {
        'class': 'h5p-image-hotspot-popup-header',
        html: header,
        'tabindex': '-1'
      });
      this.$popupContent.append(this.$popupHeader);
      this.$popup.addClass('h5p-image-hotspot-has-header');
    }
    $content.appendTo(this.$popupContent);
    this.$popupContent.appendTo(this.$popup);

    // Add close button
    this.$closeButton = $('<button>', {
      'class': 'h5p-image-hotspot-close-popup-button',
      'aria-label': options.closeButtonLabel,
      'title': options.closeButtonLabel
    }).click(function () {
      self.trigger('closed');
    }).keydown(function (e) {
      if (e.which === 32 || e.which === 13) {
        self.trigger('closed', {refocus: true});
        return false;
      }
    }).appendTo(this.$popup);

    // Need to add pointer to parent container, since this should be partly covered
    // by the popup
    if (fullscreen) {
      this.$closeButton.addClass('h5p-image-hotspot-close-fullscreen');

      if (!H5P.isFullscreen) {
        var $fullscreenButton = $('.h5p-enable-fullscreen');
        this.$closeButton.css({
          width: $fullscreenButton.outerWidth() + 'px',
          top: $fullscreenButton.outerHeight() + 'px'
        });
      }

      H5P.Transition.onTransitionEnd(self.$popup, function () {
        self.$closeButton.css({
          right: '0'
        });
      }, 300);
    }
    else {
      this.$pointer = $('<div/>', {
        'class': 'h5p-image-hotspot-popup-pointer to-the-' + (toTheLeft ? 'left' : 'right'),
      }).css({
        top: y + 0.5 + '%'
      }).appendTo(this.$popup);
    }

    this.$popupBackground.appendTo(this.$container);

    /**
     * Show popup
     * @param {boolean} [focusContainer] Will focus container for keyboard accessibility
     */
    self.show = function (focusContainer) {
      // Fix height
      var contentHeight = self.$popupContent.height();
      var parentHeight = self.$popup.height();

      if (!fullscreen) {
        if (contentHeight < parentHeight) {
          // don't need all height:
          self.$popup.css({
            maxHeight: 'auto',
            height: 'auto'
          });

          // find new top:
          var yInPixels = (y / 100) * parentHeight;
          var top = ((y / 100) * parentHeight) - (contentHeight / 2);

          // Make sure popup close button is not conflicting with full screen button
          if (top < 30) {
            top = 30;
          }
          else if (top + contentHeight > parentHeight) {
            top = parentHeight - contentHeight;
          }

          // From pixels to percent:
          var pointerTop = yInPixels - top;
          top = (top / parentHeight) * 100 ;
          self.$popup.css({
            top: top + '%'
          });

          // Need to move pointer:
          self.$pointer.css({
            top: ((pointerTop / contentHeight) * 100) - (parentHeight / contentHeight * 0.5) + '%'
          });
        }
        else {
          // Need all height:
          self.$popupContent.css({
            height: '100%',
            overflow: 'auto'
          });
        }
      }

      self.$popup.css({
        left: popupLeft + '%'
      });
      self.$popupBackground.addClass('visible');

      H5P.Transition.onTransitionEnd(self.$popup, function () {
        if (focusContainer) {
          if (self.$popupHeader) {
            self.$popupHeader.focus();
          }
          else {
            self.$closeButton.focus();
          }
        }
        self.trigger('finishedLoading');
      }, 300);
    };

    self.hide = function () {
      self.$popupBackground.remove();
    };
  };

  // Extends the event dispatcher
  ImageHotspots.Popup.prototype = Object.create(EventDispatcher.prototype);
  ImageHotspots.Popup.prototype.constructor = ImageHotspots.Popup;

})(H5P.jQuery, H5P.ImageHotspots, H5P.EventDispatcher);
;
