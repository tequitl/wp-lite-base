<?php 
namespace VCYC\Controllers;
/**
 * Load Connections Model
 */
use VCYC\Models\Connections as Conn_Model;

/**
 * Load (via Use) all the necessary Core classes
 */
use WP_REST_Controller, WP_REST_Server, WP_REST_Request, WP_REST_Response, WP_Error;

class Rest_Api extends WP_REST_Controller {

    public function __construct() {
        // Hook into the 'rest_api_init' action to register routes
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    // Function to register routes
    public function register_routes() {
        $route_base = 'vcyc/v1';
        //Add new connection
        register_rest_route($route_base, '/add_new_github_conn/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'add_new_github_conn'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
        //Delete connection
        register_rest_route($route_base, '/delete_github_conn/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'delete_github_conn'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        register_rest_route($route_base, '/activate_connection/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'activate_connection'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
        register_rest_route($route_base, '/deactivate_connection/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'deactivate_connection'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
         //  Get Meta Box VC params
         register_rest_route($route_base, '/get_vcyc_params_meta_box/', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vcyc_params_meta_box'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Get custom CSS
        register_rest_route($route_base, '/get_vcyc_params_additional_css/', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vcyc_params_additional_css'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Get custom CSS
        register_rest_route($route_base, '/get_vcyc_params_options_pages/', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_vcyc_params_options_pages'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Add new route to set is_active post meta to YES
        register_rest_route($route_base, '/post_activate_deactivate/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'post_activate_deactivate'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
        // Add new route to set is_active post meta to YES
        register_rest_route($route_base, '/additional_css_activate_deactivate/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'additional_css_activate_deactivate'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Add new route to activate/deactivate options pages
        register_rest_route($route_base, '/options_pages_activate_deactivate/', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'options_pages_activate_deactivate'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

    }
    public function add_new_github_conn(WP_REST_Request $request){
        $params = $request->get_params();  // Get the data from the request
        $new_conn =(array)$params['conn'];
        Conn_Model::add_new_connection($new_conn);
        $conn_id = $new_conn['id'];
        Conn_Model::activate_connection($conn_id);
        $data = [
            'message' => 'New Connection Added and Activated',
            'status' => 'success',
            'params' => $params
        ];
        return new WP_REST_Response($data, 200);
    }
    //Delete connection
    public function delete_github_conn(WP_REST_Request $request){
        $params = $request->get_params();  // Get the data from the request
        $conn_id = $params['conn_id'];
        $data = [
            'message' => 'Connection Deleted',
            'status' => 'success',
            'conn_id' => $params['conn_id']
        ];
        Conn_Model::delete_connection($conn_id);
        return new WP_REST_Response($data, 200);
    }
    //Activate connection
    public function activate_connection(WP_REST_Request $request){
        $params = $request->get_params();  // Get the data from the request
        $conn_id = $params['conn_id'];
        $data = [
            'message' => 'Connection Activated',
            'status' => 'success',
            'conn_id' => $params['conn_id']
        ];
        Conn_Model::activate_connection($conn_id);
        return new WP_REST_Response($data, 200);
    }
    //Deactivate connection
    public function deactivate_connection(WP_REST_Request $request){
        $params = $request->get_params();  // Get the data from the request
        $conn_id = $params['conn_id'];
        $data = [
            'message' => 'Connection Deactivated',
            'status' => 'success',
            'conn_id' => $params['conn_id']
        ];
        Conn_Model::deactivate_connection($conn_id);
        return new WP_REST_Response($data, 200);
    }
   
    // Get VC meta box callback function
    public function get_vcyc_params_meta_box(WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');  // Get the data from the request
        // Check if post_id is provided and valid
        if (!$post_id) {
            return new WP_REST_Response(['message' => 'No post ID provided'], 400);
        }
        $commit_path=get_post_meta($post_id, 'vcyc_commit_path', true);
        $post_type = get_post_type($post_id);
        if(empty($commit_path)){
            $post_date = get_post_field('post_date', $post_id);
            $post_date = gmdate('Y/m/d', strtotime($post_date));
            $commit_path=$post_type."s/".$post_date."/".$post_id.".html";
            update_post_meta($post_id, 'vcyc_commit_path', $commit_path);
        }
        $is_active = get_post_meta($post_id, 'vcyc_active', true);
        
        if(empty($is_active)) $is_active = "NO";
        $data = [
            'commits_path' => $commit_path,
            'is_active' => $is_active,
            'post_type' => $post_type
        ];
        return new WP_REST_Response($data, 200);
    }

     // Get custom CSS callback function
     public function get_vcyc_params_additional_css(WP_REST_Request $request) {
        $custom_css_post = wp_get_custom_css_post(); // Get the custom CSS post object
        if ($custom_css_post) {
            $custom_css_id = $custom_css_post->ID . "-" . $custom_css_post->post_name; // Create custom CSS ID
            $commits_path = "customizer/additional-css/".$custom_css_id.".css";
            $is_active = get_option('vcyc_active_additional_css');
            if(empty($is_active)) $is_active = "NO";
            $data = [
                'commits_path' => $commits_path,
                'is_active' => $is_active
            ];
            return new WP_REST_Response($data, 200);
        } else {
            return new WP_REST_Response(['message' => 'Custom CSS post not found'], 404);
        }
    }

    // Permissions check callback (optional)
    public function permissions_check() {
        if (!is_user_logged_in() || !current_user_can("manage_options")) return new WP_Error('rest_forbidden', __( 'You are not currently logged in.', 'version-control-your-content' ), array( 'status' => 401 ) );
		return true;
    }
    
    // Callback function to set is_active post meta to YES
    public function post_activate_deactivate(WP_REST_Request $request) {
        $post_id = $request->get_param('post_id');  // Get the post ID from the request
        $active = $request->get_param('active');  // Get the active value from the request
        if (!$post_id) {
            return new WP_REST_Response(['message' => 'No post ID provided'], 400);
        }
        update_post_meta($post_id, 'vcyc_active', $active);  // Update the post meta
        return new WP_REST_Response(['message' => 'Post updated', 'status' => 'success'], 200);
    }

    // Callback function to set is_active post meta to YES
    public function additional_css_activate_deactivate(WP_REST_Request $request) {
        $active = $request->get_param('active');  // Get the active value from the request
        update_option('vcyc_active_additional_css', $active);  // Update the post meta
        return new WP_REST_Response(['message' => 'Option updated', 'status' => 'success'], 200);
    }

    public function get_vcyc_params_options_pages(WP_REST_Request $request) {
        $option_page = $request->get_param('option_page');  // Get the data from the request
        // Check if option_page is provided and valid
        if (!$option_page) {
            return new WP_REST_Response(['message' => 'No option page provided'], 400);
        }
        $commit_path = get_option('vcyc_commit_path_' . $option_page, '');
        if (empty($commit_path)) {
            $commit_path = "options/" . $option_page . ".json";
            update_option('vcyc_commit_path_' . $option_page, $commit_path);
        }
        $is_active = get_option('vcyc_active_' . $option_page, 'NO');
        $data = [
            'commits_path' => $commit_path,
            'is_active' => $is_active
        ];
        return new WP_REST_Response($data, 200);
    }

    public function options_pages_activate_deactivate(WP_REST_Request $request) {
        $option_page = $request->get_param('option_page');  // Get the option page from the request
        $active = $request->get_param('active');  // Get the active value from the request
        if (!$option_page) {
            return new WP_REST_Response(['message' => 'No option page provided'], 400);
        }
        update_option('vcyc_active_' . $option_page, $active);  // Update the option
        return new WP_REST_Response(['message' => 'Option updated', 'status' => 'success'], 200);
    }
}// End of class