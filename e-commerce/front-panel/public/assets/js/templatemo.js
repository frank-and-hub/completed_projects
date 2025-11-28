'use strict';
document.addEventListener('DOMContentLoaded', function() {
    // Accordion
    var allPanels = document.querySelectorAll('.templatemo-accordion > li > ul');
    allPanels.forEach(function(panel) {
        panel.style.display = 'none'; // Hide all panels initially
    });

    var accordionLinks = document.querySelectorAll('.templatemo-accordion > li > a');
    accordionLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            console.log('Hello world!');
            var target = this.nextElementSibling;
            if (!target.classList.contains('active')) {
                allPanels.forEach(function(panel) {
                    panel.classList.remove('active');
                    panel.style.display = 'none';
                });
                target.classList.add('active');
                target.style.display = 'block';
            }
            event.preventDefault();
        });
    });
    // End accordion

    // Product detail
    var productLinks = document.querySelectorAll('.product-links-wap a');
    productLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            var thisSrc = this.querySelector('img').getAttribute('src');
            document.getElementById('product-detail').setAttribute('src', thisSrc);
            event.preventDefault();
        });
    });

    var btnMinus = document.getElementById('btn-minus');
    var btnPlus = document.getElementById('btn-plus');
    var varValue = document.getElementById('var-value');
    var productQuantity = document.getElementById('product-quanity');

    if (btnMinus) {
        btnMinus.addEventListener('click', function(event) {
            var val = parseInt(varValue.textContent);
            val = (val === 1) ? val : val - 1;
            varValue.textContent = val;
            productQuantity.value = val;
            event.preventDefault();
        });
    }

    if (btnPlus) {
        btnPlus.addEventListener('click', function(event) {
            var val = parseInt(varValue.textContent);
            val++;
            varValue.textContent = val;
            productQuantity.value = val;
            event.preventDefault();
        });
    }

    var sizeButtons = document.querySelectorAll('.btn-size');
    sizeButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            var thisVal = this.textContent;
            document.getElementById('product-size').value = thisVal;
            sizeButtons.forEach(function(btn) {
                btn.classList.remove('btn-secondary');
                btn.classList.add('btn-success');
            });
            this.classList.remove('btn-success');
            this.classList.add('btn-secondary');
            event.preventDefault();
        });
    });
    // End product detail
});
