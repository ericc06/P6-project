/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// var $ = require('jquery');
const $ = require('jquery');
// JS is equivalent to the normal "bootstrap" package
// no need to set this to a variable, just require it
require('bootstrap');

/*
console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

$(document).ready(function() {
    $('.btn').on('click', function() {
      var $this = $(this);
      var loadingText = '<i class="fa fa-spinner fa-spin"></i> Loading...';
      if ($(this).html() !== loadingText) {
        $this.data('Load more', $(this).html());
        $this.html(loadingText);
      }
      setTimeout(function() {
        $this.html($this.data('Load more'));
      }, 2000);
    });
  })
  */
