/**
 * RTL Sidebar Fix for AdminLTE 3
 * Handles proper sidebar behavior for Arabic language
 */

(function($) {
    'use strict';
    
    // Check if we're in RTL mode
    var isRTL = $('html').attr('dir') === 'rtl' || $('html').attr('lang') === 'ar' || $('body').hasClass('rtl-layout');
    
    if (!isRTL) {
        return; // Exit if not RTL
    }
    
    // Configuration
    var SIDEBAR_WIDTH = 250;
    var SIDEBAR_MINI_WIDTH = 73.5; // 4.6rem
    var CONTENT_SPACING = 15; // Space between sidebar and content
    var ANIMATION_SPEED = 300;
    
    // RTL Layout Manager
    var RTLManager = {
        init: function() {
            this.setupLayout();
            this.bindEvents();
            this.fixAdminLTE();
        },
        
        setupLayout: function() {
            // Add RTL classes
            $('html').attr('dir', 'rtl').attr('lang', 'ar');
            $('body').addClass('rtl-layout text-sm');
            
            // Initial layout fix
            this.updateLayout();
        },
        
        updateLayout: function() {
            var $body = $('body');
            var $sidebar = $('.main-sidebar');
            var $content = $('.content-wrapper');
            var $navbar = $('.main-header.navbar');
            var $footer = $('.main-footer');
            
            var marginRight = (SIDEBAR_WIDTH + CONTENT_SPACING) + 'px';
            
            // Check sidebar state
            if ($body.hasClass('sidebar-collapse')) {
                if ($body.hasClass('sidebar-mini')) {
                    marginRight = CONTENT_SPACING + 'px';
                } else {
                    marginRight = CONTENT_SPACING + 'px';
                }
            } else if ($body.hasClass('sidebar-mini') && !$body.hasClass('sidebar-mini-expand')) {
                marginRight = (SIDEBAR_MINI_WIDTH + CONTENT_SPACING) + 'px';
            }
            
            // Apply styles
            $sidebar.css({
                'position': 'fixed',
                'right': '0',
                'left': 'auto',
                'top': '0',
                'bottom': '0',
                'z-index': '1034',
                'width': SIDEBAR_WIDTH + 'px',
                'height': '100vh',
                'overflow-y': 'auto',
                'transform': 'none',
                'box-shadow': '-3px 0 6px rgba(0,0,0,.1)'
            });
            
            // Update content margins with spacing
            $content.add($footer).css({
                'margin-right': marginRight,
                'margin-left': '0',
                'padding-right': '15px',
                'transition': 'margin-right ' + ANIMATION_SPEED + 'ms ease-in-out'
            });
            
            // Update navbar with spacing
            $navbar.css({
                'margin-right': marginRight,
                'margin-left': '0',
                'transition': 'margin-right ' + ANIMATION_SPEED + 'ms ease-in-out'
            });
            
            // Fix navigation arrows
            this.fixNavigationArrows();
        },
        
        fixNavigationArrows: function() {
            $('.nav-sidebar .nav-link').each(function() {
                var $arrow = $(this).find('.right.fas.fa-angle-left');
                if ($arrow.length) {
                    $arrow.css({
                        'transform': 'rotate(180deg)',
                        'position': 'absolute',
                        'left': '1rem',
                        'right': 'auto',
                        'transition': 'transform ' + ANIMATION_SPEED + 'ms'
                    });
                    
                    if ($(this).parent().hasClass('menu-open')) {
                        $arrow.css('transform', 'rotate(90deg)');
                    }
                }
            });
        },
        
        bindEvents: function() {
            var self = this;
            
            // Handle pushmenu button click
            $(document).off('click.rtl', '[data-widget="pushmenu"]');
            $(document).on('click.rtl', '[data-widget="pushmenu"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $body = $('body');
                
                // Toggle sidebar state
                if ($(window).width() > 992) {
                    // Desktop mode
                    if ($body.hasClass('sidebar-mini')) {
                        $body.toggleClass('sidebar-mini-expand');
                    } else {
                        $body.toggleClass('sidebar-collapse');
                    }
                } else {
                    // Mobile mode
                    $body.toggleClass('sidebar-open');
                    
                    if ($body.hasClass('sidebar-open')) {
                        self.createOverlay();
                    } else {
                        self.removeOverlay();
                    }
                }
                
                // Update layout after state change
                setTimeout(function() {
                    self.updateLayout();
                }, 10);
                
                return false;
            });
            
            // Handle window resize
            $(window).on('resize.rtl', function() {
                self.updateLayout();
                
                if ($(window).width() > 992) {
                    $('body').removeClass('sidebar-open');
                    self.removeOverlay();
                }
            });
            
            // Handle tree menu clicks
            $(document).on('click', '.nav-sidebar .has-treeview > a', function(e) {
                var $arrow = $(this).find('.right.fas.fa-angle-left');
                if ($arrow.length) {
                    var isOpen = $(this).parent().hasClass('menu-open');
                    $arrow.css('transform', isOpen ? 'rotate(180deg)' : 'rotate(90deg)');
                }
            });
        },
        
        createOverlay: function() {
            if ($('.sidebar-overlay').length === 0) {
                var $overlay = $('<div class="sidebar-overlay"></div>');
                $overlay.css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'right': '0',
                    'bottom': '0',
                    'background-color': 'rgba(0, 0, 0, 0.1)',
                    'z-index': '1033',
                    'display': 'block'
                });
                
                $overlay.on('click', function() {
                    $('body').removeClass('sidebar-open');
                    RTLManager.removeOverlay();
                    RTLManager.updateLayout();
                });
                
                $('body').append($overlay);
            }
        },
        
        removeOverlay: function() {
            $('.sidebar-overlay').remove();
        },
        
        fixAdminLTE: function() {
            // Override AdminLTE's PushMenu plugin if it exists
            if ($.fn.PushMenu) {
                var originalPushMenu = $.fn.PushMenu;
                $.fn.PushMenu = function(option) {
                    return this.each(function() {
                        // Call original but prevent its layout changes
                        var result = originalPushMenu.call($(this), option);
                        
                        // Apply our RTL fixes immediately after
                        setTimeout(function() {
                            RTLManager.updateLayout();
                        }, 10);
                        
                        return result;
                    });
                };
            }
            
            // Fix dropdown menus
            $('.dropdown-menu').each(function() {
                if ($(this).hasClass('dropdown-menu-right')) {
                    $(this).removeClass('dropdown-menu-right').addClass('dropdown-menu-left');
                }
            });
            
            // Fix form controls
            $('input, select, textarea').css({
                'direction': 'rtl',
                'text-align': 'right'
            });
            
            // Fix cards
            $('.card-tools').css('float', 'left');
            
            // Fix user panel
            $('.user-panel .image').css({
                'float': 'right',
                'padding-right': '0.8rem',
                'padding-left': '0'
            });
            
            $('.user-panel .info').css({
                'padding-right': '55px',
                'padding-left': '5px',
                'text-align': 'right'
            });
            
            // Fix badges
            $('.badge.right').css({
                'right': 'auto',
                'left': '10px'
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        RTLManager.init();
        
        // Reinitialize after AdminLTE loads
        setTimeout(function() {
            RTLManager.updateLayout();
            RTLManager.fixNavigationArrows();
        }, 500);
    });
    
    // Export for global access
    window.RTLManager = RTLManager;
    
})(jQuery);
