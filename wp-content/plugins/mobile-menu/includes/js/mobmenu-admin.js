
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

            $( "#mobmenu-modal-body .mobmenu-item" ).removeClass( "mobmenu-item-selected" );
            $( this ).addClass( "mobmenu-item-selected" );
            $( ".mobmenu-icons-remove-selected" ).show();
                        
        });

     
        $( document ).on( "input", "#mobmenu_search_icons",  function() {

            var foundResults = false;

            if ( $( this ).val().length > 1 ) {

                var str = $( this ).val();
                str = str.toLowerCase().replace(
                    /\b[a-z]/g, function( letter ) {
                        return letter.toLowerCase();
                    } 
                );

                var txAux = str; 
                
                $( "#mobmenu-modal-body .mobmenu-item" ).each(
                    function() {

                        if ( $( this ).attr( "data-icon-key" ).indexOf( txAux ) < 0 ) {
                            $( this ).addClass( "mobmenu-hide-icons" );
                        } else {
                            $( this ).removeClass( "mobmenu-hide-icons" );
                            foundResults = true;
                    
                        }

                    }
                );
            } else {
                $( "#mobmenu-modal-body .mobmenu-item" ).removeClass( "mobmenu-hide-icons" );
            }

            if ( $( this ).val() === '' || !foundResults ) {
                $( "#mobmenu-modal-body .mobmenu-item" ).removeClass( "mobmenu-hide-icons" );
                
            }

            if ( $( this ).val() !== '' &&  $( this ).val().length >= 3  && !foundResults ) {
                $( "#mobmenu-modal-body .mobmenu-item" ).addClass( "mobmenu-hide-icons" );    
            }

        });  

    $( document ).on( "click", ".mobmenu-icon-picker" , function( e ) {
          
          e.preventDefault();
        
          var full_content = '';
          var selected_icon = '';
          var menu_id = 0;
          var id = 0;

          $( this ).prev().addClass( 'selected-option' );

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
                        menu_item_id: 0,
                        menu_id: 0,
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
        termsList = [['Header Options','url'],['Left Menu Options','url'],['Right Menu Options','value6'], ['Color Options', 'url']];
        
        // General Options Tab - Main Options
        termsList.push(['Mobile Menu Visibility (Width trigger)', 'general-options'],['Enable only in Mobile devices', 'general-options'],['Enable Testing Mode', 'general-options'],['Enable Left Menu', 'general-options'],['Enable Right Menu', 'general-options'],['Enable Footer Menu', 'general-options']);

        // General Options Tab - Hide Original Theme menu
        termsList.push(['Hide Elements', 'general-options#hide-original-theme-menu'],['Hide Elements by default', 'general-options#hide-original-theme-menu']);

        // General Options Tab - Miscelaneous Options
        termsList.push(['Menu Display Type', 'general-options#miscelaneous-options'],['Enable Over effects', 'general-options#miscelaneous-options'],['Sliding Submenus', 'general-options#miscelaneous-options'],['Automatically Close Submenus', 'general-options#miscelaneous-options'],['Menu items border size', 'general-options#miscelaneous-options'],['Close icon', 'general-options#miscelaneous-options'],['Close icon font size', 'general-options#miscelaneous-options'],['Submenu Open icon', 'general-options#miscelaneous-options'],['Submenu Close icon', 'general-options#miscelaneous-options'],['Submenu icon font size', 'general-options#miscelaneous-options']);

        // General Options Tab - Advanced Options
        termsList.push(['Sticky HTML Elements', 'general-options#advanced-options'],['Custom CSS', 'general-options#advanced-options'],['Custom JS', 'general-options#advanced-options'],['Disable Mobile Menu on specific custom post types', 'general-options#advanced-options'],['Disable Mobile Menu on seleted pages', 'general-options#advanced-options']);

        // General Options Tab - Import and Export Options
        termsList.push(['Export Settings', 'general-options#import-and-export'],['Import Settings', 'general-options#import-and-export']);

        // Header Tab - Logo
        termsList.push(['Site Logo', 'header#logo-options'],['Upload Logo', 'header#logo-options'],['Logo Height', 'header#logo-options'],['Retina Logo', 'header#logo-options'],['Disable Logo URL', 'header#logo-options'],['Alternative Logo URL', 'header#logo-options'],['Logo/Text Top Margin', 'header#logo-options']);

        // Header Tab - Header Main Options
        termsList.push(['Header Elements Position', 'header'],['Sticky Header', 'header'],['Naked Header', 'header'],['Disable Logo/Text', 'header'],['Auto-hide Header when scrolling down.', 'header']);

        // Header Tab - Header
        termsList.push(['Header Shadow', 'header#header-options'],['Header Height', 'header#header-options'],['Header Text', 'header#header-options'],['Use page title text', 'header#header-options'],['Header Logo/Text Alignment', 'header#header-options'],['Header Logo/Text Left Margin', 'header#header-options'],['Header Logo/text Spacing', 'header#header-options'],['Header Logo/text Right Margin', 'header#header-options']);

        // Header Tab - Header Banner
        termsList.push(['Enable Header Banner', 'header#header-banner-options'],['Header Banner Position', 'header#header-banner-options'],['Header Banner Content', 'header#header-banner-options'],['Header Banner Height', 'header#header-banner-options'],['Disable Logo URL', 'header#header-banner-options'],['Header Banner Alignment', 'header#header-banner-options'],['Header Banner Padding', 'header#header-banner-options']);

        // Header Tab - Header Search
        termsList.push(['Enable Header Search', 'header#header-search-options'],['Header Elements Order', 'header#header-search-options'],['Live Search (Ajax)', 'header#header-search-options'],['Search Results Alignment', 'header#header-search-options'],['Search Icon Image', 'header#header-search-options'],['Search Icon Top Margin', 'header#header-search-options'],['Search Icon Font Size', 'header#header-search-options'],['Use text instead Icon', 'header#header-search-options'],['Placeholder Text', 'header#header-search-options']);

        // Footer Tab - Main options
        termsList.push(['Footer Menu', 'footer'],['Auto-hide Footer when scrolling up', 'footer'],['Footer style', 'footer'],['Footer padding', 'footer'],['Icon font size', 'footer'],['Footer Text Font/Size', 'footer']);

        // Left Menu Tab - Main options
        termsList.push(['Left Menu Content', 'left-menu'],['Left Menu', 'left-menu'],['Parent Link open submenu', 'left-menu'],['Only visible for logged users', 'left-menu']);

        // Left Menu Tab - Left Menu Icon
        termsList.push(['Text After Icon', 'left-menu#menu-icon'],['Icon Action', 'left-menu#menu-icon'],['Icon Link URL', 'left-menu#menu-icon'],['Icon Link URL target', 'left-menu#menu-icon'],['Icon Type', 'left-menu#menu-icon'],['Icon Animation Type', 'left-menu#menu-icon'],['Icon Font', 'left-menu#menu-icon'],['Icon Font Size', 'left-menu#menu-icon'],['Icon Image', 'left-menu#menu-icon'],['Icon Top Margin', 'left-menu#menu-icon'],['Icon Left Margin', 'left-menu#menu-icon'],['Menu item icons position', 'left-menu#menu-icon'],['Menu Item icons Horizontal Padding', 'left-menu#menu-icon']);

        // Left Menu Tab - Left Panel options
        termsList.push(['Left Panel Background Image', 'left-menu#left-panel-options'],['Left Panel Background Image Opacity', 'left-menu#left-panel-options'],['Left Panel Background Image Size', 'left-menu#left-panel-options'],['Left Panel Background Gradient CSS', 'left-menu#left-panel-options'],['Left Menu Panel Widht Units', 'left-menu#left-panel-options'],['Left Menu Panel Width (Pixels)', 'left-menu#left-panel-options'],['Left Menu Panel Width (Percentage)', 'left-menu#left-panel-options'],['Left Menu content padding', 'left-menu#left-panel-options'],['Left Menu Copyright content', 'left-menu#left-panel-options']);

        // Right Menu Tab - Main options
        termsList.push(['Right Menu Content', 'right-menu'],['Right Menu', 'right-menu'],['Parent Link open submenu', 'right-menu'],['Only visible for logged users', 'right-menu']);

        // Right Menu Tab - Right Menu Icon
        termsList.push(['Text After Icon', 'right-menu#menu-icon'],['Icon Action', 'right-menu#menu-icon'],['Icon Link URL', 'right-menu#menu-icon'],['Icon Link URL target', 'right-menu#menu-icon'],['Icon Type', 'right-menu#menu-icon'],['Icon Animation Type', 'right-menu#menu-icon'],['Icon Font', 'right-menu#menu-icon'],['Icon Font Size', 'right-menu#menu-icon'],['Icon Image', 'right-menu#menu-icon'],['Icon Top Margin', 'right-menu#menu-icon'],['Icon Left Margin', 'right-menu#menu-icon'],['Menu item icons position', 'right-menu#menu-icon'],['Menu Item icons Horizontal Padding', 'right-menu#menu-icon']);

        // Right Menu Tab - Right Panel options
        termsList.push(['Right Panel Background Image', 'right-menu#right-panel-options'],['Right Panel Background Image Opacity', 'right-menu#right-panel-options'],['Right Panel Background Image Size', 'right-menu#right-panel-options'],['Right Panel Background Gradient CSS', 'right-menu#right-panel-options'],['Right Menu Panel Widht Units', 'right-menu#right-panel-options'],['Right Menu Panel Width (Pixels)', 'right-menu#right-panel-options'],['Right Menu Panel Width (Percentage)', 'right-menu#right-panel-options'],['Right Menu content padding', 'right-menu#right-panel-options'],['Right Menu Copyright content', 'right-menu#right-panel-options']);

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
                    $searchResult += '<li><a href="' + linkURL + '">' + element[0] + '</a></li>';
                }
              });

              if ( $searchResult.length > 0 ) {
                $searchResult = '<ul>' + $searchResult + '</ul>';
              }

            $( '.mm-search-settings-results' ).html( $searchResult );
          }
          else {
            $( '.mm-search-settings-results' ).html( '' );
          }
      });


    $( document ).on( "click", ".mobmenu-item-settings" , function( e ) {
             
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
        $('html, body').animate({
            scrollTop: parseInt($('#'+$(this).attr('data-link-id')).offset().top - 45)
        }, 1000);
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
});
    
}(jQuery));
