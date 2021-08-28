
  /*
    *
    *   Javascript Functions
    *   ------------------------------------------------
    *   WP Mobile Menu PRO
    *   Copyright WP Mobile Menu 2017 - http://www.wpmobilemenu.com
    *
    *
    *
    */

    
 "use strict";
  var searchTerm = '';

 (function ($) {

 jQuery( document ).ready( function(){
    var editorSettings = null;

    $( '#mobmenu_hide_elements' ).after( '<a href="#" class="mobmenu-find-element"><span class="dashicons-before dashicons-search">Find element</a>' );
    $('body').append('<iframe class="mobmenu-preview-iframe" scrolling="no" id="mobmenu-preview-iframe" width="380" height="650" >');
    setTimeout(function(){ 
        const urlParams = new URLSearchParams( window.location.search );
        var subMenu     = urlParams.get( 'tab' );

        if ( subMenu == null ) {
          subMenu = 'general-options';
        }
        $( '.nav-tab-wrapper .nav-tab' ).removeClass( 'active' );
        $( '[data-link-id=' + subMenu + ']' ).parent().parent().addClass( 'active' );
        $( '[data-link-id=' + subMenu + ']' ).click();

    }, 100);
    

    // Initilialize the CodeMirror on the custom CSS option.
    if ( $('#mobmenu_custom_css').length > 0 ) {
        editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};

        editorSettings.codemirror = _.extend(
        {},
        editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css'

            }
        );
        wp.codeEditor.initialize($('#mobmenu_custom_css'), editorSettings);
    }

    // Initilialize the CodeMirror on the custom JS option.
    if ( $('#mobmenu_custom_js').length > 0 ) {
        
   
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'javascript',
                lint: false
            }
        );
        wp.codeEditor.initialize($('#mobmenu_custom_js'), editorSettings);
    }
    
    //Hide deprecated field.
    $( '#mobmenu_header_font_size' ).parent().parent().hide();
    $( '#mobmenu_enabled_logo' ).parent().parent().hide();

    var icon_key;

    $( '.mobmenu-icon-holder' ).each( function() {

        if ( $( this ).parent().find('input').length) {
            icon_key = $( this ).parent().find('input').val();
            $( this ).html( '<span class="mobmenu-item mob-icon-' + icon_key + '" data-icon-key="' + icon_key + '"></span>');
        }
    });

         
        $( document ).on( 'click', '.mm-search-settings-results li' , function ( e ) {

        e.preventDefault();
        var dataTarget = jQuery( this ).find('a').attr( 'data-target-id' );
        jQuery('[data-link-id=' + dataTarget ).parent().click();
        jQuery('[data-link-id=' + dataTarget ).click();
        $( '.mm-search-settings-results' ).css( 'opacity', '0');
        $( '.mm-search-settings-results' ).html( '' );
        $( '#mm_search_settings').val('');
      });

        $( document ).on( "click", ".mobmenu-close-overlay" , function () {
        
            $( ".mobmenu-icons-overlay" ).fadeOut();
            $( ".mobmenu-icons-content" ).fadeOut();
            $( "#mobmenu_search_icons" ).attr( "value", "" );
            $( ".mobmenu-icons-content .mobmenu-item" ).removeClass( "mobmenu-hide-icons" );
            $( ".mobmenu-icons-remove-selected" ).hide();

            return false;
    
        });

        // Export settings.
        $( document ).on( 'click', '.export-mobile-menu-settings' , function () {
            location.href += '&mobmenu-action=download-settings';
            return false;
        });

        // Import settings.
        $( document ).on( 'click', '.import-mobile-menu-settings' , function () {
            location.href += '&mobmenu-action=import-settings';
            return false;
        });

        // Import Demos.
        $( document ).on( 'click', '.mobile-menu-import-demo' , function () {
            var demo = $( this ).attr( 'data-demo-id' );
            location.href += '&mobmenu-action=import-settings&demo=' + demo;
            return false;
        });

        $( document ).on( 'click', '.mobmenu-icons-remove-selected' , function () {

             $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'save_menu_item_icon',
                    menu_item_id: $( '.mobmenu-icons-content' ).attr( 'data-menu-item-id' ),
                    menu_item_icon: ""
                    },
               
                success: function( response ) {

                    $( '.mobmenu-item-selected' ).removeClass( 'mobmenu-item-selected' );
                    $( '.mobmenu-icons-remove-selected' ).hide();

                }
            });
        
            return false;
    
        });
        
        $( document ).on( 'click', ".toplevel_page_mobile-menu-options #mobmenu-modal-body .mobmenu-item" , function() {
            
            var icon_key = $( this ).attr( "data-icon-key" );
            $( ".mobmenu-icon-holder.selected-option" ).html( '<span class="mobmenu-item mob-icon-' + icon_key + '" data-icon-key="' + icon_key + '"></span>');
            $( ".mobmenu-close-overlay" ).trigger( "click" );
            $( ".mobmenu-icon-holder.selected-option" ).parent().find('input').val( icon_key );
            $( ".mobmenu-icon-holder.selected-option" ).removeClass( 'selected-option' );

        });

        $( document ).on( "click", ".nav-menus-php #mobmenu-modal-body .mobmenu-item" , function() {

            
            var icon_key = $( this ).attr( "data-icon-key" );
            
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "save_menu_item_icon",
                    menu_item_id: $( ".mobmenu-icons-content" ).attr( "data-menu-item-id" ),
                    menu_item_icon: icon_key
                    },
               
                success: function( response ) {
                                               
                    $( "#mobmenu-modal-body" ).append( response );   
                                                                 
                }
            });

            $( '#mobmenu-modal-body .mobmenu-item' ).removeClass( 'mobmenu-item-selected' );
            $( this ).addClass( 'mobmenu-item-selected' );
            $( '.mobmenu-icons-remove-selected' ).show();
                        
        });

      
        $( document ).on( 'mouseleave', '.mm-mobile-header-type img',  function() {
            $(this).removeClass( 'active');
        });

        $( document ).on( 'mouseenter', '.mm-mobile-header-type img',  function() {
            $(this).addClass( 'active');
        });

        $( document ).on( 'click', '.mm-mobile-header-type img',  function() {

            if ( $( this ).hasClass('hamburger-menu') ) {
                $( '#mobmenu_enabled_naked_header' ).next().click();
            } else {
                $( '#mobmenu_enabled_naked_header' ).next().next().click();
            }
        });

        $( document ).on( 'input', '#mobmenu_search_icons',  function() {

            var foundResults = false;

            if ( $( this ).val().length > 1 ) {

                var str = $( this ).val();
                str = str.toLowerCase().replace(
                    /\b[a-z]/g, function( letter ) {
                        return letter.toLowerCase();
                    } 
                );

                var txAux = str; 
                
                $( '#mobmenu-modal-body .mobmenu-item' ).each(
                    function() {

                        if ( $( this ).attr( 'data-icon-key' ).indexOf( txAux ) < 0 ) {
                            $( this ).addClass( "mobmenu-hide-icons" );
                        } else {
                            $( this ).removeClass( "mobmenu-hide-icons" );
                            foundResults = true;
                    
                        }

                    }
                );
            } else {
                $( '#mobmenu-modal-body .mobmenu-item' ).removeClass( 'mobmenu-hide-icons' );
            }

            if ( $( this ).val() === '' || !foundResults ) {
                $( '#mobmenu-modal-body .mobmenu-item' ).removeClass( 'mobmenu-hide-icons' );
                
            }

            if ( $( this ).val() !== '' &&  $( this ).val().length >= 3  && !foundResults ) {
                $( '#mobmenu-modal-body .mobmenu-item' ).addClass( 'mobmenu-hide-icons' );
            }

        });  

    $( document ).on( 'click', '.mobmenu-icon-picker' , function( e ) {
          
          e.preventDefault();
        
          var full_content = '';
          var selected_icon = '';
          var menu_id = 0;
          var id = 0;

          $( this ).prev().addClass( 'selected-option' );

          if (  $( '.mobmenu-icons-overlay' ).length ) {
                full_content = 'no';
          } else {
                full_content = 'yes';
          }

          $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: "get_icons_html",
                        menu_item_id: 0,
                        menu_id: 0,
                        full_content: full_content
                        },
               
                    success: function( response ) {
                        if ( full_content == 'yes' ) {
                                                    
                            $( 'body' ).append( response );   
                            selected_icon = $( '.mobmenu-icons-holder' ).attr( 'data-selected-icon' );
                                                
                        } else {

                            $( '.mobmenu-icons-overlay' ).fadeIn();
                            $( '.mobmenu-icons-content' ).fadeIn();
                            $( '#mobmenu-modal-body .mobmenu-item' ).removeClass( 'mobmenu-item-selected' );
                            selected_icon = $( response ).attr( 'data-selected-icon' );

                        }

                        if ( selected_icon != '' && selected_icon != undefined ) {
                            $( ".mob-icon-" + selected_icon ).addClass( "mobmenu-item-selected" );
                            $( ".mobmenu-icons-remove-selected" ).show();
                            //$( ".mobmenu-icon-picker" ).before( $( ".mob-icon-" + selected_icon ).html() );
                        }                       
                        
                    }

                });
    });

    $( '.mm-panel-search-bar #mm_search_settings' ).on( 'keyup', function( e ) {
        e.preventDefault();
        searchTerm = $( this ).val();
        var previousTerm = '';
        var termsList = [];
        termsList = [['Header Options','url'],['Left Menu Options','left-menu-options'],['Right Menu Options','value6'], ['Color Options', 'url']];
        
        // General Options Tab - Main Options
        termsList.push(['Mobile Menu Visibility (Width trigger)', 'general-visibility-options'],['Enable only in Mobile devices', 'general-visibility-options'],['Enable Testing Mode', 'general-visibility-options'],['Enable Left Menu', 'left-menu-options'],['Enable Right Menu', 'right-menu-options'],['Enable Footer Menu', 'footer-options']);

        // General Options Tab - Hide Original Theme menu
        termsList.push(['Hide Elements', 'general-hide-elements'],['Hide Elements by default', 'general-hide-elements']);

        // General Options Tab - Miscellaneous Options
        termsList.push(['Menu Display Type', 'general-misc-options'],['Enable Over effects', 'general-misc-options'],['Sliding Submenus', 'general-misc-options'],['Automatically Close Submenus', 'general-misc-options'],['Menu items border size', 'general-misc-options'],['Close icon', 'general-misc-options'],['Close icon font size', 'general-misc-options'],['Submenu Open icon', 'general-misc-options'],['Submenu Close icon', 'general-misc-options'],['Submenu icon font size', 'general-misc-options']);

        // General Options Tab - Advanced Options
        termsList.push(['Sticky HTML Elements', 'general-advanced-options'],['Custom CSS', 'general-advanced-options'],['Custom JS', 'general-advanced-options'],['Disable Mobile Menu on specific custom post types', 'general-advanced-options'],['Disable Mobile Menu on seleted pages', 'general-advanced-options']);

        // General Options Tab - Import and Export Options
        termsList.push(['Export Settings', 'general-import-export'],['Import Settings', 'general-import-export']);

        // Header Tab - Logo
        termsList.push(['Site Logo', 'logo-options'],['Upload Logo', 'logo-options'],['Logo Height', 'logo-options'],['Retina Logo', 'logo-options'],['Disable Logo URL', 'logo-options'],['Alternative Logo URL', 'logo-options'],['Logo/Text Top Margin', 'logo-options']);

        // Header Tab - Header Main Options
        termsList.push(['Header Elements Position', 'header'],['Sticky Header', 'header-options'],['Naked Header', 'header-options'],['Disable Logo/Text', 'header-options'],['Auto-hide Header when scrolling down.', 'header-options']);

        // Header Tab - Header
        termsList.push(['Header Shadow', 'header-options'],['Header Height', 'header-options'],['Header Text', 'header-options'],['Use page title text', 'header-options'],['Header Logo/Text Alignment', 'header-options'],['Header Logo/Text Left Margin', 'header-options'],['Header Logo/text Spacing', 'header-options'],['Header Logo/text Right Margin', 'header-options']);

        // Header Tab - Header Banner
        termsList.push(['Enable Header Banner', 'header#header-banner-options'],['Header Banner Position', 'header#header-banner-options'],['Header Banner Content', 'header#header-banner-options'],['Header Banner Height', 'header#header-banner-options'],['Disable Logo URL', 'header#header-banner-options'],['Header Banner Alignment', 'header#header-banner-options'],['Header Banner Padding', 'header#header-banner-options']);

        // Header Tab - Header Search
        termsList.push(['Enable Header Search', 'header#header-search-options'],['Header Elements Order', 'header#header-search-options'],['Live Search (Ajax)', 'header#header-search-options'],['Search Results Alignment', 'header#header-search-options'],['Search Icon Image', 'header#header-search-options'],['Search Icon Top Margin', 'header#header-search-options'],['Search Icon Font Size', 'header#header-search-options'],['Use text instead Icon', 'header#header-search-options'],['Placeholder Text', 'header#header-search-options']);

        // Footer Tab - Main options
        termsList.push(['Footer Menu', 'footer'],['Auto-hide Footer when scrolling up', 'footer'],['Footer style', 'footer'],['Footer padding', 'footer'],['Icon font size', 'footer'],['Footer Text Font/Size', 'footer']);

        // Left Menu Tab - Main options
        termsList.push(['Left Menu Content', 'left-menu-options'],['Left Menu', 'left-menu-options'],['Parent Link open submenu', 'left-menu-options'],['Only visible for logged users', 'left-menu-options']);

        // Left Menu Tab - Left Menu Icon
        termsList.push(['Text After Icon', 'left-menu-icon'],['Icon Action', 'left-menu-icon'],['Icon Link URL', 'left-menu-icon'],['Icon Link URL target', 'left-menu-icon'],['Icon Type', 'left-menu-icon'],['Icon Animation Type', 'left-menu-icon'],['Icon Font', 'left-menu-icon'],['Icon Font Size', 'left-menu-icon'],['Icon Image', 'left-menu-icon'],['Icon Top Margin', 'left-menu-icon'],['Icon Left Margin', 'left-menu-icon'],['Menu item icons position', 'left-menu-icon'],['Menu Item icons Horizontal Padding', 'left-menu-icon']);

        // Left Menu Tab - Left Panel options
        termsList.push(['Left Panel Background Image', 'left-panel-options'],['Left Panel Background Image Opacity', 'left-panel-options'],['Left Panel Background Image Size', 'left-panel-options'],['Left Panel Background Gradient CSS', 'left-panel-options'],['Left Menu Panel Widht Units', 'left-panel-options'],['Left Menu Panel Width (Pixels)', 'left-panel-options'],['Left Menu Panel Width (Percentage)', 'left-panel-options'],['Left Menu content padding', 'left-panel-options'],['Left Menu Copyright content', 'left-panel-options']);

        // Right Menu Tab - Main options
        termsList.push(['Right Menu Content', 'right-menu-options'],['Right Menu', 'right-menu-options'],['Parent Link open submenu', 'right-menu-options'],['Only visible for logged users', 'right-menu-options']);

        // Right Menu Tab - Right Menu Icon
        termsList.push(['Text After Icon', 'right-menu-options'],['Icon Action', 'right-menu-options'],['Icon Link URL', 'right-menu-options'],['Icon Link URL target', 'right-menu-options'],['Icon Type', 'right-menu-icon'],['Icon Animation Type', 'right-menu-icon'],['Icon Font', 'right-menu-icon'],['Icon Font Size', 'right-menu-icon'],['Icon Image', 'right-menu-icon'],['Icon Top Margin', 'right-menu-icon'],['Icon Left Margin', 'right-menu-icon'],['Menu item icons position', 'right-menu-icon'],['Menu Item icons Horizontal Padding', 'right-menu-icon']);

        // Right Menu Tab - Right Panel options
        termsList.push(['Right Panel Background Image', 'right-panel-options'],['Right Panel Background Image Opacity', 'right-panel-options'],['Right Panel Background Image Size', 'right-panel-options'],['Right Panel Background Gradient CSS', 'right-panel-options'],['Right Menu Panel Widht Units', 'right-panel-options'],['Right Menu Panel Width (Pixels)', 'right-panel-options'],['Right Menu Panel Width (Percentage)', 'right-panel-options'],['Right Menu content padding', 'right-panel-options'],['Right Menu Copyright content', 'right-panel-options']);

        // WooCommerce Tab - Main options
        termsList.push(['Enable WooCommerce Menu', 'woocommerce'],['Open cart after adding a product', 'woocommerce'],['Enable Account links in Mobile Cart Panel', 'woocommerce'],['Header Search only in products', 'woocommerce'],['Cart Total in Footer', 'woocommerce']);

        // WooCommerce Tab - Product filter
        termsList.push(['Enable Mobile Product Filter', 'woocommerce#product-filter'],['Filter icon font', 'woocommerce#product-filter'],['Filter icon font size', 'woocommerce#product-filter'],['Shop Filter Top Margin', 'woocommerce#product-filter'],['Shop Filter Location', 'woocommerce#product-filter']);

        // WooCommerce Tab - Cart Icon
        termsList.push(['Icon Type', 'woocommerce#cart-icon'],['Icon font', 'woocommerce#cart-icon'],['Icon font size', 'woocommerce#cart-icon'],['Icon Image', 'woocommerce#cart-icon'],['Cart Icon Top Margin', 'woocommerce#cart-icon']);

        // WooCommerce Tab - Cart translations
        termsList.push(['Cart Header Text', 'woocommerce#cart-translations'],['Cart No Items Text', 'woocommerce#cart-translations'],['Cart Link to the Shop Page Text', 'woocommerce#cart-translations'],['Filter Icon Text', 'woocommerce#cart-translations']);

        // WooCommerce Tab - Cart Panel
        termsList.push(['Cart Panel Background Image', 'woocommerce#cart-panel'],['Cart Panel Background Image Opacity', 'woocommerce#cart-panel'],['Cart Panel Background Gradient CSS', 'woocommerce#cart-panel'],['Cart Panel Width Units', 'woocommerce#cart-panel'],['Cart Menu Panel Width (Pixels)', 'woocommerce#cart-panel'],['Cart Menu Panel Width (Percentage)', 'woocommerce#cart-panel'],['Cart Menu Content Padding', 'woocommerce#cart-panel']);

        // Fonts Tab - Main options
        termsList.push(['WooCommerce Menu Font', 'fonts'],['Header Menu Font', 'fonts'],['Header Banner Font', 'fonts'],['Footer Text Font', 'fonts'],['Text After Icon Font', 'fonts'],['Left Menu Font', 'fonts'],['Copyright Font', 'fonts'],['Right Menu Font', 'fonts']);

        if ( searchTerm == previousTerm ) return;
          previousTerm = searchTerm;

          if ( searchTerm && searchTerm.length > 2 ) {
            var $searchResult = '';
            
            var found = termsList.find(function(element) {
                if ( 0 <= element[0].toLowerCase().indexOf(searchTerm.toLowerCase()) ) {
                    var linkURL = window.location.origin + window.location.pathname + '?page=mobile-menu-options&tab=' + element[1];

                    $searchResult += '<li><a href="' + linkURL + '" data-target-id="' + element[1] + ' ">' + element[0] + '</a></li>';
                }
              });

              if ( $searchResult.length > 0 ) {
                $searchResult = '<ul>' + $searchResult + '</ul>';
              }

            $( '.mm-search-settings-results' ).html( $searchResult );
            $( '.mm-search-settings-results' ).css( 'opacity', '1' );
          }
          else {
            $( '.mm-search-settings-results' ).html( '' );
            $( '.mm-search-settings-results' ).css( 'opacity', '0');
          }
      });

    $( document ).on( 'click', '.mm-scan-alerts a' , function( e ) {
        e.preventDefault();
        $( '[data-link-id=general-alerts]' ).click();
    });

    $( document ).on( 'click', '.mobmenu-item-settings' , function( e ) {
             
             e.preventDefault();

             var menu_item = $( this ).parent().parent().parent().parent();
             var menu_title = $(this).parent().parent().find('.menu-item-title').text();
             var menu_id = $( "#menu" ).val();
             var selected_icon = '';
             var full_content = '';
             var id = parseInt( menu_item.attr( 'id' ).match(/[0-9]+/)[0], 10);
             
             if (  $( ".mobmenu-icons-overlay" ).length ) {
                full_content = 'no';                                    
             } else {
                full_content = 'yes';
             }

             $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: "get_icons_html",
                        menu_item_id: id,
                        menu_id: menu_id,
                        menu_title: menu_title,
                        full_content: full_content
                        },
               
                    success: function( response ) {
                        if ( full_content == 'yes' ) {
                                                    
                            $( "body" ).append( response );   
                            selected_icon = $( ".mobmenu-icons-holder" ).attr( "data-selected-icon" );
                                                
                        } else {

                            $( ".mobmenu-icons-overlay" ).fadeIn();
                            $( ".mobmenu-icons-content" ).fadeIn();
                            $( "#mobmenu-modal-body .mobmenu-item" ).removeClass( "mobmenu-item-selected" );        
                            selected_icon = $( response ).attr( "data-selected-icon" );
                            $( "#mobmenu-modal-header h2").html( $( response ).attr( "data-title" ) );

                        }

                        if ( selected_icon != '' && selected_icon != undefined ) {
                            $( ".mob-icon-" + selected_icon ).addClass( "mobmenu-item-selected" );
                            $( ".mobmenu-icons-remove-selected" ).show();
                        }

                        $( "#mobmenu-modal-body" ).scrollTop( $( ".mobmenu-item-selected" ).offset() - 250 );
                        $( ".mobmenu-icons-content" ).attr( "data-menu-id", menu_id );
                        $( ".mobmenu-icons-content" ).attr( "data-menu-item-id" , id );
                    }

                });

                $( "#mobmenu_search_icons" ).focus();
    });

    $( document ).on( 'click', '.nav-tab-wrapper ul li', function(e) {

        e.preventDefault();
        var dataLinkId = $(this).attr( 'data-link-id' );

        $( '.nav-tab-wrapper .nav-tab li' ).removeClass( 'active' );
        $(this).addClass( 'active' );
        $( '.titan-framework-panel-wrap .form-table tr' ).hide();
        $( '.' + dataLinkId ).show();
        const url = new URL(window.location);
        url.searchParams.set('tab', dataLinkId);
        window.history.pushState({}, '', url);

        return false;
    });

    $( document ).on( 'click', '.titan-framework-panel-wrap .nav-tab-wrapper .nav-tab', function(e) {
        e.preventDefault();
        $( '.nav-tab-wrapper .nav-tab.active ul' ).hide();
        $( '.nav-tab-wrapper .nav-tab' ).removeClass( 'active' );
        $(this).find( 'ul' ).first().show();
        $(this).addClass( 'active' );
        $( '.nav-tab-wrapper .nav-tab li' ).removeClass( 'active' );
        $(this).find( 'ul li' ).first().addClass( 'active' );
        $( '.titan-framework-panel-wrap .form-table tr' ).hide();
        $( '.' + $(this).attr( 'data-tab-id' ) ).show();
        
    });
    
    $( "#menu-to-edit li.menu-item" ).each( function() {

        var menu_item = $(this);
        var menu_id = $( "input#menu" ).val();
        var title = menu_item.find( ".menu-item-title" ).text();
        var id = parseInt(menu_item.attr( "id" ).match(/[0-9]+/)[0], 10);
        var selected_icon = '';
        var full_content = '';

        $( ".item-title", menu_item ).append( $( "<i class='mobmenu-item-settings mob-icon-mobile-2'><span>Set Icon</span></i>" ) );

    });

    $( document ).on( 'click', '.wp-mobile-menu-notice .notice-dismiss' , function( e ) {
        
        $.ajax({
                  type: 'POST',
                  url: ajaxurl,

                  data: {
                      action: 'dismiss_wp_mobile_notice',
                      security: $( this ).parent().attr( 'data-ajax-nonce' )
                      }
              });
    });

    $( document ).on( 'click', ' .mobmenu-find-element' , function( e ) {

        e.preventDefault();
        var href    = window.location.href;
        var index   = href.indexOf('/wp-admin');
        var homeUrl = href.substring(0, index);
        $( '#mobmenu-preview-iframe' ).attr( 'src', homeUrl + '/?mobmenu-action=find-element' );
        $( '#mobmenu-preview-iframe' ).show();
    });

});

}(jQuery));
// In Parent
function receivePickedElement (el) {
    var hideElements = jQuery( '#mobmenu_hide_elements').val().trim();
    if ( hideElements == '' ) { 
        jQuery( '#mobmenu_hide_elements').val( el );
    } else {
        jQuery( '#mobmenu_hide_elements').val( hideElements + ' , ' + el );
    }
  }