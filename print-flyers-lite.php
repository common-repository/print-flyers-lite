<?php
/**
 * Plugin Name: Print Flyers Lite
 * Author URI: https://togethernet.co.uk
 * Plugin URI: https://print-flyers.com
 * Description: WooCommerce extension to create printer-friendly product pages with tailored headers and footers.
 * Version: 1.2
 * Author: Togethernet
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: WooCommerce, print, product, pdf
 * Requires at least: 3.5
 * Tested up to: 5.3.2
 * WC requires at least: 3.0
 * WC tested up to: 3.8.1
 * *
 * Text Domain printflyers
 */


// Prevent direct file access
if( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

defined('SB_DS') or define('SB_DS', DIRECTORY_SEPARATOR); //this checks for the separator on the hosting environment. So "/" for linux "\" for windows
define('SC_PRINTFLYERSLITE_PLUGIN_DIR', dirname(__FILE__)); //the directory to the plugin on the web server
define('SC_PRINTFLYERSLITE_PLUGIN_URL', WP_PLUGIN_URL . '/' . basename(SC_PRINTFLYERSLITE_PLUGIN_DIR)); //the url directory of the plugin on the browser




function printflyers_lite_validate_options( $input ) {
	// Sanitize textarea input (strip html tags, and escape characters)
	$input['button_legend'] = wp_filter_nohtml_kses($input['button_legend']);
	$input['printer_icon'] = wp_filter_nohtml_kses($input['printer_icon']);
	return $input;
}

function printflyers_lite_init() { /*** tell WordPress about the new set of options ***/
	register_setting( 'printflyers_lite_plugin_options', 'printflyerslite_ops', 'printflyers_lite_validate_options' );
}
add_action('admin_init', 'printflyers_lite_init' );

class SC_PRINTFLYERSLITE { //the main plugin class, main logic is here for admin page

	public static $textdomain = 'printflyers';
	
	private $settings_page_handle = 'printflyers_lite_options_handle';

 	public function __construct () {

		// if this is not the admin page, or the user is not an admin, go to the redirect handler

		if(!is_admin()) add_action('template_redirect', array($this, 'action_template_redirect'));
		add_action('init', array($this,'printflyers_lite_localize') );  //makes sure that the localization is run at the beginning
		add_action('admin_menu', array($this, 'add_menu_entry')); //adds the menu on the admin page for the application
		add_action('admin_print_scripts', array($this, 'printflyers_lite_addscripts')); //loads plugins scripts	
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab_printflyers', __CLASS__ . '::printflyers_lite_settings' );
	
		$option_name = 'printflyerslite_ops' ; // Initialise the database variables

		if ( get_option( $option_name ) !== false ) {
    	// The option already exists, so we just take a copy.
			$options = get_option('printflyerslite_ops'); //gets the variables stored in the plugin options
		} else {
    	// The options haven't been added yet. We'll add them.
    		$deprecated = null;
    		$autoload = 'no';
			//default setting for the plugin, normally used after first install as a default
			$sampleheader = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-header.jpg';
			$samplefooter = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-footer.jpg';
			$sampleprintericon = SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/default-printer-icon.png';
			$options = array(
						'featured_image' => 1, 
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
						'button_position' => '4', 
						'button_legend' => 'Print',
						'button_marginleft' => '0px',
						'button_marginright' => '0px',
						'button_margintop' => '0px',
						'button_marginbottom' => '0px',
						'sku' => '',
						'printer_icon' => $sampleprintericon
					    );
    		add_option( $option_name, $options, $deprecated, $autoload );
		}
		
		add_action('woocommerce_single_product_summary', array($this, 'printflyers_lite_button'), 10);

 	} // end construct
	

	
	/**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab_printflyers'] = __( 'Print Flyers Lite', 'woocommerce-settings-tab-printflyers' );
        return $settings_tabs;
    }

    public function printflyers_lite_addscripts() {
	    // add the scripts to the admin page
	    // the javascript files to open up the media manager
	    wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_script( 'printflyers-script', plugins_url( '/js/admin.js', __FILE__ ) );
    }

    public function printflyers_lite_localize() {
    	// Localization
    	load_plugin_textdomain('printflyers', false, dirname(plugin_basename(__FILE__)). "/languages" );
    }
    

	public function add_menu_entry() {
		// add the page to WordPress and call the function that is responsible of displaying the plugin options
		add_submenu_page( 'options-general.php', 'Print Flyers Lite', 'Print Flyers Lite', 'manage_options', $this->settings_page_handle, array( $this, 'printflyers_lite_options' ) );
	}


	public function printflyers_lite_options() { // Admin page settings for upgrade info and link to settings

	    $tab = 'tab1';
        if ( isset( $_REQUEST[ 'tab' ] ) )
            switch ( $_REQUEST[ 'tab' ] ) {
		    case 'tab2':
			    $tab = 'tab2'; break;	
		    default:
			    $tab = 'tab1'; break;
        	}
		?>

		<div class="wrap">
		    <h2><img style="margin-right:15px; vertical-align: middle" src="<?php print (SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/icon.png') ?>"><?php _e('Print Flyers Lite Settings', 'printflyers'); ?></h2>
			<div id="icon-options-general" class="icon32"></div>
			<h2 class="nav-tab-wrapper">
				<a href="<?php echo admin_url( 'options-general.php?page=' . $this->settings_page_handle ) ?>" class="nav-tab <?php echo ( $tab == 'tab1' ) ? 'nav-tab-active' : '' ?>">
					<?php _e('Upgrade to Print Flyers', 'printflyers'); ?>
				<a href="<?php echo admin_url( 'options-general.php?page=' . $this->settings_page_handle  . '&tab=tab2') ?>" class="nav-tab <?php echo ( $tab == 'tab2' ) ? 'nav-tab-active' : '' ?>">
					<?php _e('Settings', 'printflyers'); ?>
				</a>

				</a>
			</h2>
			<div class="metabox-holder">
			<?php
				switch ( $tab ) {
				    case 'tab2':
					    $this->settings_page_tab2(); break;
				    default:
				        $this->settings_page_tab1(); break;
				}
			?>
			</div> <!-- .metabox-holder -->
		</div> <!-- .wrap -->
		<?php
	}
	

	private function settings_page_tab2() { // tab 2 is for the plugin options
	   ?>
			<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<div class="inside">
            			<h3><?php _e('Print Flyers Settings', 'printflyers'); ?></h3>
            			<P><?php _e('Settings are located under the Print Flyers Lite tab in your WooCommerce Settings.', 'printflyers'); ?>
    				</div> <!-- .inside -->
				</div> <!-- .postbox -->
			</div> <!-- #post-body-content -->
		</div> <!-- #post-body -->	
		<?php
	}
	
	
	private function settings_page_tab1() { // tab 1 is the update tab
		?>
		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<div class="inside">
            			<h3><?php _e('Upgrade', 'printflyers'); ?></h3>


<h3>Print Flyers is the big brother of Print Flyers Lite.</h3>

Print Flyers adds extra control of the content. With Print Flyers you have finer control over what gets printed.<p>
There are many improvements, and one of the best things about Print Flyers is the ability to add your own customised headers and footers, which turns your standard product information into professional flyers.<p>
A Print Flyers license also includes 12 months' developer support.<p>
Print Flyers is translation-ready and comes with English (US), English (UK) and French (FR) translations, with more to follow.<p>

Find out more at <a href="https://print-flyers.com/">print-flyers.com</a>

<h2>Click to get <a title="Buy Print Flyers for WooCommerce" href="https://print-flyers.com/">Print Flyers</a></h2>

<hr />

<img src="<?php print (SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/sneakers-flyer.jpg') ?>" width="100%">

&nbsp;

    				</div> <!-- .inside -->
				</div> <!-- .postbox -->
			</div> <!-- #post-body-content -->
		</div> <!-- #post-body -->	
		<?php
	}



	public static function printflyers_lite_settings() { // WooCommerce Admin page settings
		// define the KEYS to available image positions.
        $img_positions = array( 'left', 'none', 'right' );

        //the font for the text page, 
        $fonts = array('Arial', 'Calibri', 'Courier', 'Garamond', 'Georgia', 'Helvetica', 'Minion', 'Monospace', 'Palatino', 'Sans-serif', 'Serif', 'Times', 'Times New Roman', 'Verdana');
		?>
		</form>
        <style>
        .mytooltip { position: relative; display: inline-block; border-bottom: 2px dotted #A46497; }
        .mytooltip .mytooltiptext { visibility: hidden; width: 200px; background-color: #A46497; color: #fff;
                                    text-align: center; border-radius: 6px; padding: 5px; position: absolute;
                                    z-index: 1; top: 150%; left: 50%; margin-left: -60px; }
        .mytooltip .mytooltiptext::after {  content: ""; position: absolute; bottom: 100%; left: 50%;
                                            margin-left: -5px; border-width: 5px; border-style: solid;
                                            border-color: transparent transparent #A46497 transparent; }
        .mytooltip:hover .mytooltiptext { visibility: visible; }
		td { margin-right: 20px; }
		.woocommerce-save-button { display: none !important; }
        </style>
		<div class="wrap">
		    <form action="options.php" method="post">
		    <h2><img style="margin-right:15px; vertical-align: middle"
					 src="<?php print (SC_PRINTFLYERSLITE_PLUGIN_URL . '/assets/icon.png') ?>"><?php _e('Print Flyers Lite Settings', 'printflyers'); ?>
			</h2>
			<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<div class="inside">
	
			                <h3><?php _e('Do more with Print Flyers', 'printflyers'); ?></h3>

				            <p><?php _e('For documentation and how to upgrade to the pro version, visit <a href="https://print-flyers.com">print-flyers.com</a>', 'printflyers'); ?></p>
	
    					</div> <!-- .inside -->
					</div> <!-- .postbox -->
					<div class="postbox">
						<div class="inside">

			                <?php
				                settings_fields('printflyers_lite_plugin_options');
				                $options = get_option('printflyerslite_ops');
				                //gets the variables stored in the plugin options
				                //$options should be set assuming user has visited the admin pages once
			                ?>		
			                <h3><?php _e('Print button', 'printflyers'); ?></h3>
				            <table>
								
					            <tr><td class="mytooltip"><?php _e('Label for the button', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Leave blank for no label (use icon)', 'printflyers'); ?></span></td><td>						
					                <input id="button_legend" type="text" size="20" name="printflyerslite_ops[button_legend]" value="<?php print sanitize_text_field($options['button_legend']) ?>" /></td></tr>             					
                				<tr><td class="mytooltip"><?php _e('Left margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[button_marginleft]" value="<?php print $options['button_marginleft'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Right margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[button_marginright]" value="<?php print $options['button_marginright'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Top margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[button_margintop]" value="<?php print $options['button_margintop'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Bottom margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[button_marginbottom]" value="<?php print $options['button_marginbottom'] ?>" /></td></tr>		
				            </table>
				            <p><label class="mytooltip"><?php _e('Printer icon', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Upload your own icon. A suitable size is about 20px x 20px', 'printflyers'); ?></span></label></p>
				            <div>
                                <input type="text" name="printflyerslite_ops[printer_icon]" id="image_url" class="regular-text" value="<?php echo esc_url_raw($options['printer_icon'])?>" >
                                <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Select Icon" >
                                <p><?php _e ('Current selection: ', 'printflyers'); ?><img src="<?php echo esc_url($options['printer_icon'])?>"></p>
                            </div>
				            <?php submit_button(); ?>		
    					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				    <div class="postbox">
					    <div class="inside">

				            <h3><?php _e('General display options', 'printflyers'); ?></h3>

				            <table>

					            <tr><td><?php _e('Show short description?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[short_description]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
						        <?php if (isset($options['short_description']) && $options['short_description'] == 0)
							        _e ('Selected=', 'printflyers'); ?> ?>
						        <?php _e('No', 'printflyers'); ?></option></select></td></tr>
								
					            <tr><td><?php _e('Show description?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[product_description]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
							    <?php if (isset($options['product_description']) && $options['product_description'] == 0)
    							    _e ('Selected=', 'printflyers'); ?> ?>
							    <?php _e('No', 'printflyers'); ?></option></select></td></tr>
								
					            <tr><td><?php _e('Show price?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[price]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
							    <?php if (isset($options['price']) && $options['price'] == 0)
							        _e ('Selected=', 'printflyers'); ?> ?>
							    <?php _e('No', 'printflyers'); ?></option></select></td></tr>
														
					            <tr><td><?php _e('Show SKU?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[sku]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
							    <?php if (isset($options['sku']) && $options['sku'] == 0)
    							    _e ('Selected=', 'printflyers'); ?> ?>
							    <?php _e('No', 'printflyers'); ?></option></select></td></tr>
							
					            <tr><td><?php _e('Show attributes?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[product_attributes]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
							    <?php if (isset($options['product_attributes']) && $options['product_attributes'] == 0)
							        _e ('Selected=', 'printflyers'); ?> ?>
							    <?php _e('No', 'printflyers'); ?></option></select></td></tr>

				            </table>				

				            <?php submit_button(); ?>				
    					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				    <div class="postbox">
					    <div class="inside">
				            <!-- featured image settings -->
					        <h3><?php _e('Featured image', 'printflyers'); ?></h3>
					        <table>

					            <tr><td><?php _e('Show featured image?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[featured_image]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
						        <?php if (isset($options['featured_image']) && $options['featured_image'] == 0)
    						        _e ('Selected=', 'printflyers'); ?> ?>
						        <?php _e('No', 'printflyers'); ?></option></select>
					            </td></tr>
					            <tr>
					            <label><td class="mytooltip"><?php _e('Position', 'printflyers');?><span class="mytooltiptext"><?php _e('Text will wrap around image if you select Left or Right', 'printflyers'); ?></span></label>
					            </td>
					            <td>
					            <select name="printflyerslite_ops[img_position]">
					                <option value="">-- <?php _e('Position', 'printflyers'); ?> --</option>
					                <?php foreach($img_positions as $position): ?>
					                    <option value="<?php print $position; ?>" <?php print ($options['img_position'] == $position) ? 'selected="selected"' : ''; ?>>
					    	            <?php print $position; ?>
					                </option>
					                <?php endforeach; ?>
					            </select>
					            </td>
					            </tr>
		
				                <!-- featured image width and margin settings -->
					            <tr><td class="mytooltip"><?php _e('Width', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 50% or 20px. Height will scale accordingly', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[img_width]" value="<?php print $options['img_width'] ?>" /></td></tr>
					            <tr><td class="mytooltip"><?php _e('Left margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 3% or 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[img_marginleft]" value="<?php print $options['img_marginleft'] ?>" /></td></tr>
					            <tr><td class="mytooltip"><?php _e('Right margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 3% or 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[img_marginright]" value="<?php print $options['img_marginright'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Top margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 3% or 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[img_margintop]" value="<?php print $options['img_margintop'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Bottom margin', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 3% or 5px', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[img_marginbottom]" value="<?php print $options['img_marginbottom'] ?>" /></td></tr>		
					            <tr><td class="mytooltip"><?php _e('Show border?', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Adds a thin gray frame around image', 'printflyers'); ?></span></td><td><select name="printflyerslite_ops[show_border]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
					            <?php if (isset($options['show_border']) && $options['show_border'] == 0)
						            _e ('Selected=', 'printflyers'); ?> ?>
    				            <?php _e('No', 'printflyers'); ?></option></select>
    				            </td></tr>
				            </table>
		
				            <?php submit_button(); ?>		
    					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				    <div class="postbox">
					    <div class="inside">
				            <!-- Gallery Options for the plugin -->
				            <h3><?php _e('Gallery', 'printflyers'); ?></h3>
				            <table>

    				            <tr><td><?php _e('Show gallery?', 'printflyers'); ?></td><td><select name="printflyerslite_ops[gallery]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
    					        <?php if (isset($options['gallery']) && $options['gallery'] == 0)
    						        _e ('Selected=', 'printflyers'); ?> ?>
    					        <?php _e('No', 'printflyers'); ?></option></select></td></tr>
    								
    				            <tr><td class="mytooltip"><?php _e('Gallery Image Width', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Example: 15% or 100px. The height will scale in proportion', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[gallery_img_width]" value="<?php print $options['gallery_img_width'] ?>" /></td></tr>
    		
    				            <tr><td class="mytooltip"><?php _e('Show borders?', 'printflyers'); ?><span class="mytooltiptext"><?php _e('Adds a thin gray frame around each image', 'printflyers'); ?></span></td><td><select name="printflyerslite_ops[gallery_border]"><option value='1'><?php _e('Yes', 'printflyers'); ?></option><option value='0' 
    					        <?php if (isset($options['gallery_border']) && $options['gallery_border'] == 0)
    						        _e ('Selected=', 'printflyers'); ?> ?>
    					        <?php _e('No', 'printflyers'); ?></option></select></td></tr>
    				        </table>

    				        <?php submit_button(); ?>		
    					</div> <!-- .inside -->
					</div> <!-- .postbox -->
				    <div class="postbox">
					    <div class="inside">
    				        <!-- plugins font settings -->
    				        <h3><?php _e('Printer font', 'printflyers'); ?></h3>
		
    				        <table>

    					       <tr><td><span><?php _e('Font Size', 'printflyers'); ?></span></td><td><input type="text" name="printflyerslite_ops[font_size]" value="<?php print $options['font_size'] ?>" /></td></tr>

						        <tr><td><label><?php _e('Font Family', 'printflyers'); ?></label></td><td>						
    					        <select name="printflyerslite_ops[font_family]">		
    						        <option value="">-- <?php _e('font family', 'printflyers'); ?> --</option>		
    						        <?php foreach($fonts as $font): ?>		
    						            <option value="<?php print $font; ?>" <?php print ($options['font_family'] == $font) ? _e ('selected') : ''; ?>>		
    							        <?php print $font; ?>		
    						            </option>		
    						        <?php endforeach; ?>		
    					       </select>							
    					       </td></tr>
	
    				        </table>

    				        <?php submit_button(); ?>
    				    </div> <!-- .inside -->
				    </div> <!-- .postbox -->

			    </div> <!-- #post-body-content -->
			</div> <!-- #post-body -->	
		    </div> <!-- .metabox-holder -->
			</form>
		</div> <!-- .wrap -->
		<?php
	}


	public function printflyers_lite_button() {
		global $product; //gets the global class Product for WooCommerce so we can get the product's details

		$link = home_url('/index.php?task=printflyerslite&pid='.$product->get_id()); //sets the URL for the post page
		$nonced_url = wp_nonce_url($link, $product->get_id()); // adds a nonce to the URL

		$ops = get_option('printflyerslite_ops');

		//this produces the print link on the products page
		//Gets width, margin and position properties for the button

		$bml = ($ops['button_marginleft']) ?   $ops['button_marginleft'] : '0px';
		$bmr = ($ops['button_marginright']) ?  $ops['button_marginright'] : '0px';
		$bmt = ($ops['button_margintop']) ?    $ops['button_margintop'] : '0px';
		$bmb = ($ops['button_marginbottom']) ? $ops['button_marginbottom'] : '0px';

		?><a href="<?php print $nonced_url; ?>"	id="print_button_id" 
				                                target="_blank" 
				                                rel="nofollow" 
				                                style="display: block; margin-left: <?php print $bml; ?>; margin-right: <?php print $bmr; ?>; margin-top: <?php print $bmt; ?>; margin-bottom: <?php print $bmb; ?>;"
				                                ><img src="<?php print ($ops['printer_icon']) ?>"><?php print (sanitize_text_field($ops['button_legend'])) ?>
				                                </a>

        <script language="JavaScript">
    		jQuery( 'document' ).ready( function( $ ) { // add variation id onto print button link
        		if ( jQuery( "input[name='variation_id']" ) ) {
    		        jQuery( "input[name = 'variation_id']" ).change( function() {
        			    variationnn_id = jQuery( "input[name='variation_id']" ).val();
        			    cur_href = document.getElementById( "print_button_id" ).href; 
        			    cur_href2 = cur_href.split( '&variation_id' );
        			    cur_href = cur_href2[0];
        			    document.getElementById( "print_button_id" ).href=cur_href+"&variation_id="+variationnn_id;
        			    return false;
    		        });
    		    }
		    });
	    </script>	
		<?php
	}

	public function action_template_redirect() {
		if( isset($_REQUEST['task']) && $_REQUEST['task'] == 'printflyerslite' && isset($_REQUEST['pid']) && $_REQUEST['pid'] ) {
			$retrieved_nonce = $_REQUEST['_wpnonce'];
			if (!wp_verify_nonce($retrieved_nonce, $_REQUEST['pid'] ) )
					die( 'Failed security check (print-flyers-lite: expired or invalid nonce)' );
			require_once SC_PRINTFLYERSLITE_PLUGIN_DIR . SB_DS . 'html-print.php';
			die();
		}
	}
}

$sc_printflyers = new SC_PRINTFLYERSLITE(); //this simply calls the class meaning the construct method is run