<?php
/**
 * Adds a new notification type using actions and filters.
 * Demo for how to approach add-on notification types.
 */
class Alchemyst_Forms_Insert_Post_Notification {
    const NOTIFICATION_NAME = 'insert-post';

    // The entire framework for developing additional notification types is done via filters and actions.
    public static function init() {
        // Register the notificaiton type.
        add_filter('alchemyst_forms:notification-types', array(__CLASS__, 'add_notification_type'), 10, 1);

        // Add actions for saving and updating the notifications.
        add_action('alchemyst_forms:save-notification-type:' . self::NOTIFICATION_NAME, array(__CLASS__, 'save_notification'), 10, 2);
        add_action('alchemyst_forms:update-notification-type:' . self::NOTIFICATION_NAME, array(__CLASS__, 'update_notification'), 10, 2);

        // Add action for actually processing the notification.
        add_action('alchemyst_forms:do-notification-type:' . self::NOTIFICATION_NAME, array(__CLASS__, 'do_notification'), 10, 3);

        // Build the notification object when being retrieved from the database.
        add_filter('alchemyst_forms:build-notification-object:' . self::NOTIFICATION_NAME, array(__CLASS__, 'build_notification_object'));

        // Add a validator class that contains admin-side validations for this notification type.
        add_filter('alchemyst_forms:form-notification-validator-classes', array(__CLASS__, 'add_validator_class'));
    }

    /**
     * Registers the notification type with Alchemyst Forms
     *
     * Used with filter - alchemyst_forms:notification-types
     * @param $notification_types - Current array of notification types
     */
    public static function add_notification_type($notification_types) {
        $dir = __DIR__ . '/admin-views/';
        $notification_types[self::NOTIFICATION_NAME] = array(
            'name' => 'Insert Post',
            'dashicon' => 'admin-post',
            'template-copy' => $dir . 'insert-post-copy-template.php',
            'template-main' => $dir . 'insert-post-template.php',
        );
        return $notification_types;
    }

    /**
     * Save the notification to the database.
     * Note: All notifications MUST save to the database.
     * Generally you will want to simply provide your additional fields as meta input in the build_post_arr() function below.
     *
     * Used by action - alchemyst_forms:save-notification-type:
     * @param $notification - Notification object for this notification.
     * @param $form_id - Form ID for the parent form containing this notification.
     */
    public static function save_notification($notification, $form_id) {
        $post_arr = self::build_post_arr($notification, $form_id);
        $post_id = wp_insert_post($post_arr);
        return $post_id;
    }

    /**
     * Update an existing notification to the database.
     * Note: All notifications MUST save to the database.
     * Generally you will want to simply provide your additional fields as meta input in the build_post_arr() function below.
     *
     * Used by action - alchemyst_forms:update-notification-type:
     * @param $notification - Notification object for this notification.
     * @param $form_id - Form ID for the parent form containing this notification.
     */
    public static function update_notification($notification, $form_id) {
        $post_arr = self::build_post_arr($notification, $form_id);
        $post_arr['ID'] = $notification['id'];
        $post_id = wp_update_post($post_arr);
        return $post_id;
    }

    /**
     * Helper function for save_notification() and update_notification(). Takes both parameters from those functions.
     * Customize your meta_input here to save any additional fields.
     */
    public static function build_post_arr($notification, $form_id) {
        return array(
            'post_type' => AF_NOTIFICATIONS_POSTTYPE,
            'post_status' => 'publish',
            'post_title' => 'Email Notification for Form: ' . $form_id,
            'meta_input' => array(
                '_alchemyst_forms-form-id'                   => $form_id,
                '_alchemyst_forms-notification-type'         => $notification['type'],
                '_alchemyst_forms-notification-post-type'    => $notification['post_type'],
                '_alchemyst_forms-notification-post-status'  => $notification['post_status'],
                '_alchemyst_forms-notification-post-title'   => $notification['post_title'],
                '_alchemyst_forms-notification-post-content' => $notification['post_content'],
            )
        );
    }

    /**
     * Perform all notification actions here!
     *
     * You may wish to do form interpolation here, the submitted form data is available in the $request parameter.
     *
     * @param $notification - Notification object for this notification.
     * @param $request - Submitted form data ($_POST)
     * @param $dom - A processed object containing the DOM structure of the form being submitted.
     */
    public static function do_notification($notification, $request, $dom) {
        // Set up an array of variables to interpolate with form fields.
        $to_interpolate = array(
            'post_type'     => $notification->post_type,
            'post_status'   => $notification->post_status,
            'post_title'    => $notification->post_title,
            'post_content'  => $notification->post_content
        );

        // Perform the interpolation. For example, if the notification had set up 'post_title' to inherit a value from the form
        // via a shorttag (such as [field-name]), this interpolator would replace that short-tag with the submitter's input.
        $interpolated_vars = Alchemyst_Forms_Interpolator::interpolate_vars($to_interpolate, $notification->form_id, $request, $dom);

        // Extract the interpolated vars into the current scope, just keeps the code cleaner below.
        extract($interpolated_vars);

        // Here we take the interpolated results, and insert them into Wordpress as a new post.
        $post_arr = array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'post_title' => $post_title,
            'post_content' => $post_content,
            'meta_input' => array(
                '_alchemyst_forms-request' => $request,
                '_alchemyst_forms-form-id' => $notification->form_id,
                '_alchemyst_forms-notification-id' => $notification->ID
            )
        );
        $post_id = wp_insert_post($post_arr);
    }

    /**
     * Used when the notification is retrieved from the database to be displayed later.
     * $obj is the result of a get_post() call.
     *
     * @see https://developer.wordpress.org/reference/functions/get_post/
     */
    public static function build_notification_object($obj) {
        $obj->post_type    = get_post_meta($obj->ID, '_alchemyst_forms-notification-post-type', true);
        $obj->post_status  = get_post_meta($obj->ID, '_alchemyst_forms-notification-post-status', true);
        $obj->post_title   = get_post_meta($obj->ID, '_alchemyst_forms-notification-post-title', true);
        $obj->post_content = get_post_meta($obj->ID, '_alchemyst_forms-notification-post-content', true);

        return $obj;
    }

    /**
     * Register the validator class as an array pair where key = the notification type, and value = the name of the
     * class housing all the validation methods to be used.
     *
     * All validation methods within this class will be called when the form is updated on the Wordpress 'save_post' action.
     *
     * This validation class name should extend Alchemyst_Forms_Validator_Methods_Type to inherit the utility methods
     * provided by that class, and used in the demo below.
     */
    public static function add_validator_class($validator_classes) {
        $validator_classes[self::NOTIFICATION_NAME] = 'Alchemyst_Forms_Insert_Post_Notification_Validation';
        return $validator_classes;
    }
}

// Very important! Fire the init method after all Alchemyst Forms classes are loaded.
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Insert_Post_Notification', 'init'));

/**
 * Define methods for validating notifications of this post type.
 *
 * The following method should typically be used:
 * Alchemyst_Forms_Validator::build_response((bool) $success, (string) $message, (int) $error_level)
 *
 * The following error levels are defined:
 * self::LEVEL_ERROR = 3
 * self::LEVEL_WARNING = 2
 * self::LEVEL_INFO = 1
 * self::LEVEL_SUCCESS = 0
 */
class Alchemyst_Forms_Insert_Post_Notification_Validation extends Alchemyst_Forms_Validator_Methods_Type {

    // Ensure that the submitted post type is a valid post type.
    public static function not_a_valid_post_type($post_id, $notification) {
        $post_types = get_post_types();
        if (!in_array($notification->post_type, $post_types) && !preg_match('/\[(.*)\]/', $notification->post_status)) {
            return Alchemyst_Forms_Validator::build_response(false, 'Create Post notification (ID: ' . $notification->ID . ') appears to have an invalid post type.', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    // Ensure that the submitted post status is a valid post status.
    public static function not_a_valid_post_status($post_id, $notification) {
        $post_stati = get_post_stati();
        if (!in_array($notification->post_status, $post_stati) && !preg_match('/\[(.*)\]/', $notification->post_status)) {
            return Alchemyst_Forms_Validator::build_response(false, 'Create Post notification (ID: ' . $notification->ID . ') appears to have an invalid post status.', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    // Post titles are strongly recommended.
    public static function no_post_title($post_id, $notification) {
        if (!$notification->post_title) {
            return Alchemyst_Forms_Validator::build_response(false, 'Create Post notification (ID: ' . $notification->ID . ') does not contain a post title, it is strongly suggested you include one.', self::LEVEL_WARNING);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }
}
