<?php
/**
 * Plugin Name: Simple Custom Plugin
 * Description: This is a simple custom plugin
 * Author: Luis Rodriguez
 * Version: 1.0.0
 */

class LRCP_Plugin {
  private $option_key = 'lrcp_options';
  private $plugin_version = '1.0.0';

  public function __construct() {
    add_action('admin_menu', [$this, 'add_settings_page']);
    add_action('admin_init', [$this, 'register_settings']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    add_action('wp_body_open', [$this, 'render_message']);
  }

  public function enqueue_assets() {
    if ( !is_admin() ) {
      wp_enqueue_style( 'lrcp-message', plugin_dir_path( __FILE__ ) . 'assets/styles.css', [], $this->plugin_version );
    }
  }

  public function add_settings_page() {
    add_options_page( 'Message Settings', 'Message Settings', 'manage_options', 'lrcp_settings', [$this, 'settings_page'] );
  }

  public function settings_page() {
    ?>
    <div class="wrap">
      <form action="options.php" method="post">
        <?php
          settings_fields( 'lrcp_group' );
          do_settings_sections( 'lrcp_settings' );
          submit_button( 'Save changes' );
        ?>
      </form>
    </div>
    <?php
  }

  public function register_settings() {
    register_setting(
      'lrcp_group',
      $this->option_key,
      [$this, 'sanitize']
    );

    add_settings_section(
      'lrcp_section',
      'Message Settings',
      function () {
        echo '<p>Fill the message field to show the message on the site.</p>';
      },
      'lrcp_settings'
    );

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
    $out = [];

    $raw_message = isset( $input['message'] ) ? (string) $input['message'] : '';
    $no_tags = strip_tags( $raw_message );
    $no_breaks = preg_replace( '/[\r\n\t]+/', ' ', $no_tags );
    $out['message'] = trim( $no_breaks );

    $raw_bg = isset( $input['bg_color'] ) ? (string) $input['bg_color'] : '';
    $raw_text = isset( $input['text_color'] ) ? (string) $input['text_color'] : '';

    $bg = sanitize_hex_color( $raw_bg );
    $text = sanitize_hex_color( $raw_text );

    $out['bg_color'] = $bg ?: '';
    $out['text_color'] = $text ?: '';

    return $out;
  }

  public function render_message() {
    $defaults = [
      'message' => '',
      'bg_color' => '#ffffff',
      'text_color' => '#000000',
    ];

    $options  = get_option( $this->option_key, $defaults );

    $message = trim( (string) $options['message'] );
    if ( $message === '' ) {
      return;
    }

    printf(
      '<p class="lrcp-message" style="%1$s">%2$s</p>',
      esc_attr( 'background-color:' . $options['bg_color'] .';color:' . $options['text_color'] ),
      esc_attr( $options['message'] ),
    );
  }

}

register_activation_hook( __FILE__, function () {
  $defaults = [
    'message'     => '',
    'bg_color'    => '#ffffff',
    'text_color'  => '#000000',
  ];
  add_option( 'lrcp_options', $defaults );
});

new LRCP_Plugin();
