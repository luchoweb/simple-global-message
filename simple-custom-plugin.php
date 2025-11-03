<?php
/**
 * Plugin Name: Simple Custom Plugin
 * Description: This is a simple custom plugin
 * Author: Luis Rodriguez
 * Version: 1.0.0
 * License: GPL+2
 */

class LRCP_Plugin {
  private $option_key = 'lrcp_options';
  private $plugin_version = '1.0.0';

  public function __construct() {
    // Add hooks when the plugin starts
    add_action('admin_menu', [$this, 'add_settings_page']); // create settings page in admin area
    add_action('admin_init', [$this, 'register_settings']);  // register plugin settings
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']); // load CSS for frontend
    add_action('wp_body_open', [$this, 'render_message']); // show message in site body
  }

  public function enqueue_assets() {
    // This function loads a CSS file only on the frontend (not admin)
    if ( !is_admin() ) {
      wp_enqueue_style( 'lrcp-message', plugin_dir_path( __FILE__ ) . 'assets/styles.css', [], $this->plugin_version );
    }
  }

  public function add_settings_page() {
    // Add a new page inside WordPress "Settings" menu
    add_options_page( 'Message Settings', 'Message Settings', 'manage_options', 'lrcp_settings', [$this, 'settings_page'] );
  }

  public function settings_page() {
    // This function displays the plugin settings form
    ?>
    <div class="wrap">
      <form action="options.php" method="post">
        <?php
          // WordPress functions to show settings fields and save button
          settings_fields( 'lrcp_group' );
          do_settings_sections( 'lrcp_settings' );
          submit_button( 'Save changes' );
        ?>
      </form>
    </div>
    <?php
  }

  public function register_settings() {
    // This function registers all plugin settings fields
    register_setting( 'lrcp_group', $this->option_key, [$this, 'sanitize'] );

    // Add section title and description
    add_settings_section(
      'lrcp_section',
      'Message Settings',
      function () {
        echo '<p>Fill the message field to show the message on the site.</p>';
      },
      'lrcp_settings'
    );

    // Add message field
    add_settings_field(
      'message',
      'Message',
      function () {
        $options = get_option($this->option_key);
        printf(
          '<input type="text" name="%1$s[message]" value="%2$s" placeholder="%3$s" />',
          esc_attr( $this->option_key ),
          esc_attr( $options['message'] ),
          esc_html__( 'Short message here', 'simple-custom-plugin' )
        );
      },
      'lrcp_settings',
      'lrcp_section',
    );

    // Add background color field
    add_settings_field(
      'bg_color',
      'Background Color',
      function () {
        $options = get_option($this->option_key);
        printf(
          '<input type="color" name="%1$s[bg_color]" value="%2$s" />',
          esc_attr( $this->option_key ),
          esc_attr( $options['bg_color'] )
        );
      },
      'lrcp_settings',
      'lrcp_section',
    );

    // Add text color field
    add_settings_field(
      'text_color',
      'Text Color',
      function () {
        $options = get_option($this->option_key);
        printf(
          '<input type="color" name="%1$s[text_color]" value="%2$s" />',
          esc_attr( $this->option_key ),
          esc_attr( $options['text_color'] )
        );
      },
      'lrcp_settings',
      'lrcp_section',
    );
  }

  public function sanitize( $input ) {
    // Clean the user input before saving it
    $out = [];

    // Sanitize message text
    $raw_message = isset( $input['message'] ) ? (string) $input['message'] : '';
    $no_tags = strip_tags( $raw_message ); // remove HTML tags
    $no_breaks = preg_replace( '/[\r\n\t]+/', ' ', $no_tags ); // remove line breaks
    $out['message'] = trim( $no_breaks );

    // Sanitize color values
    $raw_bg = isset( $input['bg_color'] ) ? (string) $input['bg_color'] : '';
    $raw_text = isset( $input['text_color'] ) ? (string) $input['text_color'] : '';

    $bg = sanitize_hex_color( $raw_bg );
    $text = sanitize_hex_color( $raw_text );

    $out['bg_color'] = $bg ?: '';
    $out['text_color'] = $text ?: '';

    return $out;
  }

  public function render_message() {
    // This function prints the message in the frontend
    $defaults = [
      'message' => '',
      'bg_color' => '#ffffff',
      'text_color' => '#000000',
    ];

    $options = get_option( $this->option_key, $defaults );

    $message = trim( (string) $options['message'] );
    if ( $message === '' ) {
      // If message is empty, do nothing
      return;
    }

    // Show the message with selected styles
    printf(
      '<p class="lrcp-message" style="%1$s">%2$s</p>',
      esc_attr( 'background-color:' . $options['bg_color'] .';color:' . $options['text_color'] ),
      esc_attr( $options['message'] ),
    );
  }
}

// This runs when the plugin is activated for the first time
register_activation_hook( __FILE__, function () {
  $defaults = [
    'message' => '',
    'bg_color' => '#ffffff',
    'text_color' => '#000000',
  ];

  // Save default settings in the database
  add_option( 'lrcp_options', $defaults );
});

// Create a new instance of the plugin class
new LRCP_Plugin();
