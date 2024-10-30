<?php

/*
    Plugin Name: Header Footer Script Adder
    Plugin URI: http://www.webclasses.in
    Description: Plugin for adding scripts in header and footer
    Author: mahethekiller
    Version: 1.2.1
    Author URI: http://www.webclasses.in
    */

class Mahethekiller_hfsa
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'page_init'));
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Header Footer Script Adder',
			'Header Footer Script Adder',
			'manage_options',
			'header_footer_script_adder_mk',
			array($this, 'mahethekiller_hfsa_admin')
		);
	}

	/**
	 * Options page callback
	 */
	public function mahethekiller_hfsa_admin()
	{
		// Set class property
		$this->options = get_option('mahethekiller_hfsa_options');

		if (isset($_GET["page"]) && ($_GET["page"] == "header_footer_script_adder_mk")) {

			wp_enqueue_script('Codemirror', plugin_dir_url(__FILE__) . 'codemirror/codemirror.js');

			wp_enqueue_style('Codemirrorcss', plugin_dir_url(__FILE__) . "codemirror/codemirror.css");
			wp_enqueue_style('cm_blackboard', plugin_dir_url(__FILE__) . "/codemirror/blackboard.css");

			wp_enqueue_script('cm_xml', plugin_dir_url(__FILE__) . "/codemirror/xml.js");
			wp_enqueue_script('codemirrorjs', plugin_dir_url(__FILE__) . 'codemirror/javascript.js');
			wp_enqueue_script('cm_css', plugin_dir_url(__FILE__) . "/codemirror/css.js");
			wp_enqueue_script('cm_css', plugin_dir_url(__FILE__) . "/codemirror/php.js");
			wp_enqueue_script('cm_css', plugin_dir_url(__FILE__) . "/codemirror/clike.js");


			wp_enqueue_script('customjs', plugin_dir_url(__FILE__) . 'customjs/custom.js');
		}
?>
		<div class="wrap">
			<div style="width: 100%">
				<div style="width: 50%;float: left;">
					<form method="post" action="options.php">
						<?php
						// This prints out all hidden setting fields
						settings_fields('mahethekiller_hfsa_option_group');
						do_settings_sections('mahethekiller_hfsa_settings_admin');
						submit_button();
						?>
					</form>
				</div>

				<div style="width: 40%;float: right;">
					<div style="width: 40%;float: left;margin-top: 100px">
						<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="T7RMRXUM27ZM6">
							<table>
								<tr>
									<td><input type="hidden" name="on0" value="Amount">
										<h2>Contribute or Donate!</h2>
										<p>Want to help make this plugin even better? All donations are used to improve this plugin, so donate $10, $20 or $50 now!</p>
									</td>
								</tr>
								<tr>
									<td>
										<select name="os0">
											<option value="Donate">Donate $10.00 USD</option>
											<option value="Donate">Donate $20.00 USD</option>
											<option value="Donate">Donate $50.00 USD</option>
										</select> </td>
								</tr>
							</table>
							<input type="hidden" name="currency_code" value="USD">
							<input type="image" src="<?php echo plugin_dir_url(__FILE__); ?>images/paypal-donate.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
							<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
						</form>

					</div>
				</div>
			</div>
		</div>


	<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'mahethekiller_hfsa_option_group', // Option group
			'mahethekiller_hfsa_options', // Option name
			array($this, 'sanitize') // Sanitize
		);

		add_settings_section(
			'mahethekiller_hfsa_code', // ID
			'<h1>Header and footer code Adder</h1>', // Title
			array($this, 'print_section_info'), // Callback
			'mahethekiller_hfsa_settings_admin' // Page
		);

		add_settings_field(
			'header_code', // ID
			'Header Code', // Title 
			array($this, 'header_code_callback'), // Callback
			'mahethekiller_hfsa_settings_admin', // Page
			'mahethekiller_hfsa_code' // Section           
		);

		add_settings_field(
			'footer_code',
			'Footer Code',
			array($this, 'footer_code_callback'),
			'mahethekiller_hfsa_settings_admin',
			'mahethekiller_hfsa_code'
		);


		foreach (array('post', 'page') as $type) {
			add_meta_box('mahethekiller_hfsa_all_post_meta', 'Insert Script to &lt;head&gt;', array($this, 'mahethekiller_hfsa_meta_setup'), $type, 'normal', 'high');
		}

		add_action('save_post', array($this, 'mahethekiller_hfsa_post_meta_save'));
	}


	public function mahethekiller_hfsa_meta_setup()
	{
		global $post;

		// using an underscore, prevents the meta variable
		// from showing up in the custom fields section
		$meta = get_post_meta($post->ID, 'post_header_script', TRUE);

	?>



		<div>

			<p>
				<textarea name="post_header_script" rows="5" style="width:98%;"><?php if (!empty($meta)) echo htmlspecialchars_decode($meta); ?></textarea>
			</p>

			<p>Add some code to <code>&lt;head&gt;</code>.</p>
		</div>

	<?php

		// create a custom nonce for submit verification later
		echo '<input type="hidden" name="mahethekiller_hfsa_post_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
	}

	public function mahethekiller_hfsa_post_meta_save($post_id)
	{
		// authentication checks

		// make sure data came from our meta box
		if (
			!isset($_POST['mahethekiller_hfsa_post_meta_noncename'])
			|| !wp_verify_nonce($_POST['mahethekiller_hfsa_post_meta_noncename'], __FILE__)
		) return $post_id;

		// check user permissions
		if ($_POST['post_type'] == 'page') {
			if (!current_user_can('edit_page', $post_id)) return $post_id;
		} else {
			if (!current_user_can('edit_post', $post_id)) return $post_id;
		}


		$current_data = get_post_meta($post_id, 'post_header_script', TRUE);

		$new_data = esc_textarea(($_POST['post_header_script']));

		if ($current_data) {
			if (is_null($new_data)) {

				delete_post_meta($post_id, 'post_header_script');
			} else {
				update_post_meta($post_id, 'post_header_script', $new_data);
			}
		} elseif (!is_null($new_data)) {

			if (!add_post_meta($post_id, 'post_header_script', $new_data, TRUE)) {
				update_post_meta($post_id, 'post_header_script', $new_data);
			}
		}

		return $post_id;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize($input)
	{
		$new_input = array();
		if (isset($input['header_code']))
			$new_input['header_code'] = wp_json_encode($input['header_code']);

		if (isset($input['footer_code']))
			$new_input['footer_code'] = wp_json_encode($input['footer_code']);

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		print 'Enter your Header And Footer Code below:';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function header_code_callback()
	{
		// printf(
		// 	'<textarea id="header_code" style="width:500px;height:200px" name="mahethekiller_hfsa_options[header_code]" />%s</textarea>',
		// 	isset($this->options['header_code']) ? esc_attr(json_decode($this->options['header_code'])) : ''
		// );

	?>

		<textarea style="width:500px;height:200px" name="mahethekiller_hfsa_options[header_code]" id="header_code" class="form-control" rows="6" required="required">
		<?php echo isset($this->options['header_code']) ? trim(esc_attr(json_decode($this->options['header_code']))) : '' ?>
		</textarea>

	<?php

	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function footer_code_callback()
	{
		// printf(
		// 	'<textarea id="footer_code" style="width:500px;height:200px" name="mahethekiller_hfsa_options[footer_code]" />%s</textarea>',
		// 	isset($this->options['footer_code']) ? esc_attr(json_decode($this->options['footer_code'])) : ''
		// );

	?>

		<textarea style="width:500px;height:200px" name="mahethekiller_hfsa_options[footer_code]" id="footer_code" class="form-control" rows="6" required="required">
		<?php echo isset($this->options['footer_code']) ? trim(esc_attr(json_decode($this->options['footer_code']))) : '' ?>
		</textarea>

<?php



	}
}

if (is_admin())
	$Mahethekiller_hfsa = new Mahethekiller_hfsa();



// Add code to header and footer
// 
function mahethekiller_hfsa_add_footer_code()
{


	if (!is_admin() && !is_feed() && !is_robots() && !is_trackback()) {

		$mahethekiller_hfsa_options = get_option("mahethekiller_hfsa_options");

		if (!empty($mahethekiller_hfsa_options)) {
			$text = json_decode($mahethekiller_hfsa_options["footer_code"]);
			$text = convert_smilies($text);
			$text = do_shortcode($text);

			if ($text != '') {
				echo $text . "\n";
			}
		}
	}
}
add_action('wp_footer', 'mahethekiller_hfsa_add_footer_code');


function mahethekiller_hfsa_add_header_code()
{

	$mahethekiller_hfsa_options = get_option("mahethekiller_hfsa_options");
	if (!empty($mahethekiller_hfsa_options)) {
		echo json_decode($mahethekiller_hfsa_options["header_code"]) . "\n";
	}
}
add_action('wp_head', 'mahethekiller_hfsa_add_header_code');

function mahethekiller_hfsa_add_header_code_post()
{

	$mahethekiller_hfsa_post_meta = get_post_meta(get_the_ID(), 'post_header_script', TRUE);

	if ($mahethekiller_hfsa_post_meta != '') {
		echo htmlspecialchars_decode($mahethekiller_hfsa_post_meta) . "\n";
	}
}
add_action('wp_head', 'mahethekiller_hfsa_add_header_code_post');



// user install and deactivate hooks

function mahethekiller_hfsa_install()
{
	$current_user = wp_get_current_user();
	$email = $current_user->user_email;
	$s = 1;
	$name = $current_user->user_firstname . "-" . $current_user->user_lastname;
	$plugin = "Header-footer-script-adder";
	//file_get_contents("http://www.webclasses.in/myplugindata/plugin.php?email=$email&name=$name&s=$s&plugin=$plugin");
}
register_activation_hook(__FILE__, 'mahethekiller_hfsa_install');


function mahethekiller_hfsa_deactivation()
{
	$current_user = wp_get_current_user();
	$email = $current_user->user_email;
	$s = 0;
	$name = $current_user->user_firstname . "-" . $current_user->user_lastname;
	$plugin = "Header-footer-script-adder";
	//file_get_contents("http://www.webclasses.in/myplugindata/plugin.php?email=$email&name=$name&s=$s&plugin=$plugin");
}
register_deactivation_hook(__FILE__, 'mahethekiller_hfsa_deactivation');

?>