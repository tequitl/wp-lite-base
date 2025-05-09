<?php
/**
 * WordPress Email Handler Script
 *
 * Handles email sending through WordPress's wp_mail() function with proper security measures.
 *
 * @package WordPress
 */

if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
    $protocol = $_SERVER['SERVER_PROTOCOL'];
    if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3' ), true ) ) {
        $protocol = 'HTTP/1.0';
    }
    header( 'Allow: POST' );
    header( "$protocol 405 Method Not Allowed" );
    header( 'Content-Type: text/plain' );
    exit;
}

/** Sets up the WordPress Environment. */
require __DIR__ . '/wp-load.php';

nocache_headers();

// Verify nonce for security
if ( ! isset( $_POST['email_nonce'] ) || ! wp_verify_nonce( $_POST['email_nonce'], 'send_email_action' ) ) {
    wp_die( 'Invalid nonce specified', 'Email Error', array( 'response' => 403 ) );
}

// Get and sanitize email parameters
$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
$message = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
$headers = array('Content-Type: text/html; charset=UTF-8');

// Validate required fields
if ( empty( $to ) || empty( $subject ) || empty( $message ) ) {
    wp_die( 'Please fill in all required fields', 'Email Error', array( 'response' => 400 ) );
}

// Validate email address
if ( ! is_email( $to ) ) {
    wp_die( 'Invalid email address', 'Email Error', array( 'response' => 400 ) );
}

// Send email using WordPress mail function
$sent = wp_mail( $to, $subject, $message, $headers );

// Handle the response
if ( $sent ) {
    $response = array(
        'success' => true,
        'message' => 'Email sent successfully'
    );
} else {
    $response = array(
        'success' => false,
        'message' => 'Failed to send email'
    );
}

// Return JSON response
header( 'Content-Type: application/json' );
echo json_encode( $response );
exit;