/*
 * front.js
 *
 * This script file contains client‑side logic for the user‑facing part
 * of the restaurant website. You can add interactive behaviours here,
 * such as updating the cart, handling language toggles, or loading
 * recommended products via AJAX. For now it simply logs a message to
 * confirm that the file is loaded.
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('front.js loaded');
});
/*
  Promo Slider (3 cards)
  - يتقلب تلقائيا كل 4 ثواني
  - أزرار السابق/التالي
  - نقاط (dots)
*/

// Slider auto-play
const slides = document.querySelectorAll('#heroSlider .slide');
let currentSlide = 0;

// function showNextSlide() {
//     slides[currentSlide].classList.remove('active');
//     currentSlide = (currentSlide + 1) % slides.length;
//     slides[currentSlide].classList.add('active');
// }

// // كل 4 ثواني
// setInterval(showNextSlide, 4000);
