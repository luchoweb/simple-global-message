<?php

class LRCP_Plugin_Test extends WP_UnitTestCase {

  private $plugin;

  public function setUp(): void {
    parent::setUp();

    if ( ! class_exists( 'LRCP_Plugin' ) ) {
      require dirname(__DIR__) . '/simple-custom-plugin.php';
    }

    $this->plugin = new LRCP_Plugin();
  }

  public function tearDown(): void {
    delete_option( 'lrcp_options' );
    parent::tearDown();
  }

  public function test_sanitize_strips_tags_and_trims() {
    $raw = [
      'message' => " <script>alert('x')</script>  Hello <b>World</b> ",
      'bg_color' => '#112233',
      'text_color' => '#abcdef',
    ];

    $sanitized = $this->plugin->sanitize( $raw );

    $this->assertArrayHasKey( 'message', $sanitized );
    $this->assertSame( "alert('x')  Hello World", $sanitized['message'] );

    $this->assertArrayHasKey( 'bg_color', $sanitized );
    $this->assertArrayHasKey( 'text_color', $sanitized );
    $this->assertSame( '#112233', $sanitized['bg_color'] );
    $this->assertSame( '#abcdef', $sanitized['text_color'] );
  }

  public function test_sanitize_rejects_invalid_colors() {
    $raw = [
      'message' => 'Hi',
      'bg_color' => 'rgb(10,10,10)',
      'text_color' => '#GGHHHH', 
    ];

    $sanitized = $this->plugin->sanitize( $raw );

    $this->assertTrue( array_key_exists( 'bg_color', $sanitized ) );
    $this->assertTrue( array_key_exists( 'text_color', $sanitized ) );

    $this->assertTrue( $sanitized['bg_color'] === '' || $sanitized['bg_color'] === null );
    $this->assertTrue( $sanitized['text_color'] === '' || $sanitized['text_color'] === null );
  }

  public function test_render_message_prints_when_non_empty() {
    update_option( 'lrcp_options', [
      'message' => 'Hello Front',
      'bg_color' => '',
      'text_color' => '',
    ] );

    ob_start();
    $this->plugin->render_message();
    $output = ob_get_clean();

    $this->assertNotEmpty( $output, 'Expected markup when message is not empty.' );
    $this->assertStringContainsString( '<p', $output );
    $this->assertStringContainsString( 'Hello Front', $output );
  }

  public function test_render_message_prints_nothing_when_empty() {
    update_option( 'lrcp_options', [
      'message' => '   ',
      'bg_color' => '#000000',
      'text_color' => '#ffffff',
    ] );

    ob_start();
    $this->plugin->render_message();
    $output = ob_get_clean();

    $this->assertSame( '', $output, 'No markup expected when message is empty or whitespace.' );
  }

  public function test_render_message_applies_colors_when_set() {
    update_option( 'lrcp_options', [
      'message'    => 'Colored!',
      'bg_color'   => '#123456',
      'text_color' => '#fedcba',
    ] );

    ob_start();
    $this->plugin->render_message();
    $output = ob_get_clean();

    $this->assertStringContainsString( 'Colored!', $output );
    $this->assertStringContainsString( '#123456', $output );
    $this->assertStringContainsString( '#fedcba', $output );
  }

  public function test_defaults_do_not_notice_when_option_missing() {
    ob_start();
    $this->plugin->render_message();
    $output = ob_get_clean();

    $this->assertIsString( $output );
  }
}
