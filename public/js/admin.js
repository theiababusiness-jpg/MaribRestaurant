/*
 * admin.js
 *
 * This script file is for the administrator panel. You can implement
 * dynamic features such as form validation, sortable tables, or
 * asynchronous updates. At present it only logs a message when the
 * admin panel is loaded.
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('admin.js loaded');
});
function toggleMobileMenu(){
  const menu = document.getElementById('mobileMenu');
  if(!menu) return;
  menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
}
