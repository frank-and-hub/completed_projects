/*=========

Template Name: Morbizz - SEO & Digital Marketing HTML Template

=========*/

/*=========
----- JS INDEX -----
1.Whole Script Strict Mode Syntax
2.Portfolio Tabbing JS
=========*/

$(document).ready(function($) {

	// Whole Script Strict Mode Syntax
	"use strict";

	// Portfolio Tabbing JS Start
    $(function() {
    var filterList = {
        init: function() {
            // MixItUp plugin
            // http://mixitup.io
            $('#portfoliolist').mixItUp({
                selectors: {
                    target: '.portfolio-two',
                    filter: '.filter'
                },
                load: {
                    filter: '.all, .digital-pr, .monitoring, .ppc, .seo, .smm'
                }
            });
        }
    };
    // Run the show!
    filterList.init();
    });
    // Portfolio Tabbing JS End
   
});