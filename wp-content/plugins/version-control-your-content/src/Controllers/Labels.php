<?php

namespace VCYC\Controllers;

class Labels{

// Array to hold label definitions
private static $labels = [
    'no_conn_error' => 'No GitHub connection is active. Your content won\'t be version controlled.',
    'verifying_pat' => 'Verifying your Access Token...',
    'pat_error' => 'Please enter a valid Access Token.',
    'pat_verify_error' => 'Please verify your Access Token.',
    'pat_invalid' => 'Invalid Access Token. Please enter a valid Access Token.',
    'pat_verify_success' => 'Access Token verified successfully.',
    'fetching_repos' => 'Access Token Verified. Fetching your private repositories list...',
    'fetching_branches' => 'Fetching branches of selected repository...',
    'conn_name_error' => 'Please enter at least 2 digit connection name.',
    'conn_name_success' => 'Connection name saved successfully.',
    'conn_save_error' => 'Failed to save connection.',
    'conn_save_success' => 'Connection saved successfully.',
    'conn_activate_success' => 'Connection activated successfully.',
    'connection_deleted' => 'Connection deleted successfully.',
    'connection_not_found' => 'GitHub account not found',
    'repos_fetched' => 'Repositories fetched successfully.',
    'branches_fetched' => 'Branches fetched successfully.',
];

/**
 * Get the label by name.
 *
 * @param string $label_key The key for the label.
 * @return string|null The label if found, null otherwise.
 */
public static function get($label_key) {
    // Apply WordPress filter to allow user-defined labels
    $label = apply_filters('vcyc_label_' . $label_key, self::$labels[$label_key] ?? null);
    return self::$labels[$label_key] ?? null;
}

/**
 * Print the label by name.
 *
 * @param string $label_key The key for the label.
 * @return void
 */
public static function print($label_key) {
    $label = self::get($label_key);
    echo esc_html($label);
}

/**
 * Get all labels.
 *
 * @return array An array of all labels.
 */
public static function get_all_labels() {
    return self::$labels;
}

}//end of Labels class