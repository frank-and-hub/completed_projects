
/*=========
----- JS INDEX -----
1.Whole Script Strict Mode Syntax
2.Typed JS
=========*/

$(document).ready(function($) {

	// Whole Script Strict Mode Syntax
	"use strict";

	// Typed Js Start
	$(".typer").typed({
        strings: ["Lorem", "Lorem", "Lorem"],
        typeSpeed: 200,
        backSpeed: 30,
        backDelay: 800,
        cursorChar: "",
        contentType: 'html',
        loop: true
    });
	// Typed Js End
   
});