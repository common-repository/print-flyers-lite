/***
 * Plug-in: Print Flyers Lite
 * Version: 1.1
 * File: admin.js
 * Purpose: interface to the WP media library via javascript for uploading header, footer and printer icon images.
 * 
 ****/
jQuery(document).ready(function($){ // printer icon
    $('#upload-btn').click(function(e) {
        e.preventDefault();
            var image = wp.media({ 
                title: 'Upload Icon',
                multiple: false
                }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                    console.log(uploaded_image);
                    var image_url = uploaded_image.toJSON().url;
                        $('#image_url').val(image_url);
            });
        });
    });
 
 jQuery(document).ready(function($){ // header image
    $('#upload-btn2').click(function(e) {
        e.preventDefault();
            var image = wp.media({ 
                title: 'Upload Image',
                multiple: false
                }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                    console.log(uploaded_image);
                    var image_url = uploaded_image.toJSON().url;
                        $('#image_url2').val(image_url);
            });
        });
    });
            
 jQuery(document).ready(function($){ // footer image
    $('#upload-btn3').click(function(e) {
        e.preventDefault();
            var image = wp.media({ 
                title: 'Upload Image',
                multiple: false
                }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                    console.log(uploaded_image);
                    var image_url = uploaded_image.toJSON().url;
                        $('#image_url3').val(image_url);
            });
        });
    });
            

jQuery('document').ready(function($) {
    if(jQuery("input[name='variation_id']")) {
        jQuery("input[name='variation_id']" ).change(function() {
            variationnn_id=jQuery("input[name='variation_id']" ).val();
            //alert("variationn_id="+variationnn_id);
            cur_href=document.getElementById("print_button_id").href; 
            cur_href2=cur_href.split('&variation_id');
            cur_href=cur_href2[0];
            document.getElementById("print_button_id").href=cur_href+"&variation_id="+variationnn_id;
            return false;
            });
        }
    });
