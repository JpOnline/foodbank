jQuery.noConflict();
jQuery(document).ready(function($){
                       
                       jQuery("img#background").attr("src", RwGet.pathto('images/editable_images/bg1.jpg'));
                       
                       if ((navigator.userAgent.indexOf('iPhone') != -1) || (navigator.userAgent.indexOf('iPod') != -1) || (navigator.userAgent.indexOf('iPad') != -1)) {
                       
                       jQuery('html').addClass('background1');
                       
                       }
                       
                       });