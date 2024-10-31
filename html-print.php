<?php 

/***
 * Plug-in: Print Flyers Lite
 * File: html-print.php
 * Purpose: Generate the html page for printing
 * 
 ****/

if ( ! defined( 'ABSPATH' ) ) die ('failed security check - file accessed directly'); // Exit if accessed directly
if ( !isset ($_GET['task'] )) die ('failed security check - no task set'); // no task in URL so quit
if ( $_GET['task'] <> 'printflyerslite' )  die ('failed security check - unknown task'); // wrong task in URL so quit


$sampleheader = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-header.jpg';
$samplefooter = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-footer.jpg';
$sampleprintericon = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-printer-icon.png';
//Here are the default variables to the Plugin if there aren't any settings found in the database
	$def = array(		'featured_image' => 1, 
						'gallery' => 1, 
						'product_description' => 1,
						'price'=> 1, 
						'product_attributes'=> 1, 
						'short_description' => 1, 
						'img_position' => 'right',
						'img_width' => '50%',
						'img_marginleft' => '20px',
						'img_marginright' => '0px',
						'img_margintop' => '0px',
						'img_marginbottom' => '20px',
						'show_border' => '1',
						'gallery_img_width' =>'15%',
						'gallery_border' => '1',
						'font_family' => 'Arial',
						'font_size' => '14px',
						'button_legend' => 'Print',
						'button_marginleft' => '0px',
						'button_marginright' => '0px',
						'button_margintop' => '0px',
						'button_marginbottom' => '0px',
						'sku' => '',
						'printer_icon' => $sampleprintericon
						);



//gets the options set in Admin page
$ops = get_option('printflyerslite_ops'); 

//the array_merge checks if there are any missing keys (settings) from $ops, our custom options
$ops = array_merge($def, $ops);

// We need to consider the nonce. The pid was encoded in the nonce.
check_admin_referer($_REQUEST['pid']); // Nonce is not right so quit

// Find out what product we have to deal with
$product = wc_get_product($_REQUEST['pid']);

//The pid may not have been a valid product so check and quit if not.
if (($product == null) OR ($product == false)) die ('failed security check - not a product');

// do we have a variation?
$isVariation = ( isset( $_REQUEST['variation_id'] ) && $_REQUEST['variation_id'] > 0 );
if ( $isVariation ) {  // Yes, so deal with it

    $var_id = $_REQUEST[ 'variation_id' ];
    $wp_variations = $product->get_available_variations( );
    foreach ( $wp_variations as $key1=>$value1 ) {
        if ( $var_id == $value1 [ 'variation_id' ] ) {
            $variation_array = $value1;
            }
        }
    }


//now we are going to output the HTML to the print page

?>

<!DOCTYPE html>

<html>

<head>

<title>Print <?php print $product->get_title(); ?></title>


<style>

body, #container {
	font-family: '<?php print $ops['font_family']; ?>';
	font-size: <?php print $ops['font_size']?>;
}



#thumbnails img {

<?php

//this gets the width to the Gallery.

	if(isset($ops['gallery_img_width']) && $ops['gallery_img_width'] != "")
		echo "width: " . $ops['gallery_img_width'] . "; ";

?>
	height: "auto";

}

</style>

<link rel="stylesheet" href="<?php print plugins_url( 'css/printflyers-lite.css', __FILE__ ) ?>" />

<script src="<?php print home_url('/wp-includes/js/jquery/jquery.js'); ?>"></script>

<script>


jQuery(function($)

{
		window.print(); //standard Javascript print function

});

</script>

</head>

<body>

<div id="container" style="display:block;">

    <div id="printflyers-lite-main">

	    <h1 id="printflyers-lite-title"><?php print $product->get_title(); ?></h1>

	    <div id="printflyers-lite-images">

		    <?php if( $ops['featured_image'] == 1 ) include("inc/featured-image.php");
            ?>

		<?php if($ops['price'] == 1 ): /** Gets the price of the product **/ ?>
			<div id="printflyers-lite-price">
				<h3>
				<?php if ( $isVariation ) {
				    echo $variation_array['price_html'];
                    }
				else {
				    echo $product->get_price_html(); 
				    }
				?>
				</h3>
			</div><!-- end id="printflyers-lite-price" -->

		<?php endif; ?>


		<?php if($ops['sku'] == 1 ): /** Gets the sku of the product **/ ?>
			<div id="printflyers-lite-sku">
				<?php 
    			 if ( $isVariation ) {
                    _e('SKU:', 'woocommerce');
				    echo $variation_array['sku'];
                    }
				else {
				    _e('SKU:', 'woocommerce');
				    echo $product->get_sku(); 
			        }
				?>
			</div>

		<?php endif; ?>

		<?php if($ops['short_description'] == 1 ): /** gets the product's short description **/ ?>
			<div id="printflyers-lite-short-description">
            	<?php
			    	$content = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
                	echo wpautop( $content );
            	?>
			</div><!-- end id="printflyers-lite-short-description" -->
			<P>
		<?php endif; ?>

		<?php if($ops['product_description'] == 1 ): /** gets the product's full description **/ ?>
			<div id="printflyers-lite-description">
				<?php
					$heading = apply_filters( 'woocommerce_product_description_heading', __( 'Description', 'woocommerce' ) ); 
				?>

				<h2><?php echo $heading; ?></h2>

				<?php
			    	$content = apply_filters( 'woocommerce_description', $product->get_description() );
					echo wpautop( $content );
            	?>
			</div><!-- end id="printflyers-lite-description" -->
            <P>
		<?php endif; ?>

		<?php if($ops['product_attributes'] == 1 ): ?>

			<?php $attributes = $product->get_attributes(); ?>

			<?php if(count($attributes)): ?>

				<div id="additional-info">
					<?php
						$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional information', 'woocommerce' ) );
					?>

					<?php if ( $heading ): ?>
						<h2><?php echo $heading; ?></h2>
					<?php endif; ?>

					<p><?php wc_display_product_attributes($product); ?></p>

				</div><!-- end id="additional-info" -->

			<?php endif; ?>

		<?php endif; ?>

		<?php if($ops['gallery'] == 1 ): /** checks if the gallery image option is set **/ ?>
			
			<div id="thumbnails">
				
			<?php include("inc/gallery-images.php"); ?>

			</div><!-- end id="thumbnails" -->

		<?php endif; ?>

	</div><!-- end id="printflyers-lite-images" -->

    </div><!-- end id="printflyers-lite-main" -->

</div><!-- end id="container" -->

</body>

</html>