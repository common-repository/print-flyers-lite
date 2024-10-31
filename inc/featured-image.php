<?php

/***
 * Plug-in: Print Flyers Lite
 * File: featured-image.php
 * Purpose: Output the featured image
 *
 ****/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div id="printflyers-featured-image">

	<?php
					
		$w=$h=0;
		if( $isVariation ) {
    	// $src = $variation_array['image_src'];
    	$src = $variation_array['image']['full_src'];
		/*** we could have variations but no images for them so before calling getImageSize, check $src is not null ***/
		if ( !empty( $src ) ) {
    		list( $w, $h ) = getImageSize( $src );
			}
        }
		if ( $w > 0 && $h > 0) { } else {
			list( $src, $w, $h ) = wp_get_attachment_image_src( $product->get_image_id(), 'large' ); 
		}					//Gets width, margin and position properties for the featured image

        // Now $src should have our image, either featured image or selected variation
		$w = ($ops['img_width']) ? $ops['img_width'] : $w;

		$h = "auto";

		$ml = ($ops['img_marginleft']) ? $ops['img_marginleft'] : $def['img_marginleft'];
		$mr = ($ops['img_marginright']) ? $ops['img_marginright'] : $def['img_marginright'];
		$mt = ($ops['img_margintop']) ? $ops['img_margintop'] : $def['img_margintop'];
		$mb = ($ops['img_marginbottom']) ? $ops['img_marginbottom'] : $def['img_marginbottom'];
		$p = ($ops['img_position']) ? $ops['img_position'] : $def['img_position'];

		//outputs the featured image with its correct width, height settings as well as margin top, left, bottom and right

		$featureborder = ""; //create the var

		if($ops['show_border'] == 1)
			$featureborder = "showborder"; //if the option for border is on give it a class

		//echo the image with the inline css styles based on the properties from the options
					
		$imagestyle =   "width:" . $w . 
		                "; height:" . $h . 
		                "; margin-left:" . $ml . 
		                "; margin-right:" . $mr . 
		                "; margin-top:" . $mt . 
		                "; margin-bottom:" . $mb .
		                "; float:" . $p;
					    
	    $image_attributes[0] = $src;
					
	?>
    <img src="<?php echo $image_attributes[0]; ?>" class="<?php echo $featureborder; ?>" style="<?php echo $imagestyle ?>" />

</div>
