<?php

/***
 * Plug-in: Print Flyers Lite
 * File: gallery-images.php
 * Purpose: Output the gallery images
 *
 ****/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// global $product;

$attachment_ids = $product->get_gallery_image_ids();

if ( $attachment_ids ) {
	$loop 		= 0;
	$columns 	= apply_filters( 'woocommerce_product_thumbnails_columns', 3 );
	?>
	<div class="thumbnails <?php echo 'columns-' . $columns; ?>"><?php
	
		$galleryborder = ""; //create the gallery border var
	
		if($ops['gallery_border'] == 1) $galleryborder = "class='galleryborder'"; //if the option for gallery border is on then give it a class

		$gw = ($ops['gallery_img_width']) ? $ops['gallery_img_width'] : $def['gallery_img_width']; // a width should have been set but if not then use the default
        $gh = "auto";
        
		?>
		<h2><?php _e('Images', 'woocommerce'); ?></h2>
		
		<?php foreach ( $attachment_ids as $attachment_id ) {

			$classes = array( 'zoom' );

			if ( $loop == 0 || $loop % $columns == 0 )
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				$classes[] = 'last';

			$image_link = wp_get_attachment_url( $attachment_id );

            $galleryimage = ("<img src='{$image_link}' style='width: {$gw}; height: {$gh};' {$galleryborder} />");

            echo $galleryimage;

			$loop++;
		}

	?></div>
	<?php
}
