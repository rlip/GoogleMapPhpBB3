<?php
namespace rlip\intereststree\notification;

class inttree_proposal_accepted extends \phpbb\notification\type\base
{
    protected $titleKey = 'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_TITLE';
    protected $contentKey = 'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_ACCEPTED_TEXT';

    public static $notification_option = array(
        'lang'   => 'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_ACCEPTED_TEXT',
        'group'   => 'NOTIFICATION_GROUP_MISCELLANEOUS',
    );

    public function get_type()
    {
        return 'rlip.intereststree.notification.type.inttree_proposal_accepted';
    }

    public function is_available()
    {
        return true;
    }

    public function users_to_query(){
        return array();
    }

    /**
     * Get the id of the item
     *
     * @param array $my_notification_data The data from the post
     */
    public static function get_item_id($my_notification_data)
    {
        return time() % 8388606;
    }

    /**
     * Get the id of the parent
     *
     * @param array $my_notification_data The data from the topic
     */
    public static function get_item_parent_id($my_notification_data)
    {
        return 0;
    }

    /**
     * Find the users who want to receive notifications
     *
     * @param array $my_notification_data The data from the post
     * @param array $options Options for finding users for notification
     *
     * @return array
     */
    public function find_users_for_notification($my_notification_data, $options = array())
    {
        $options = array_merge(array(
            'ignore_users'      => array(),
        ), $options);

        $users = array((int) $my_notification_data['to_user_id']);

        return $this->check_user_notification_options($users, $options);
    }

    /**
     * Get the user's avatar
     */
    public function get_avatar()
    {
        return $this->user_loader->get_avatar($this->get_data('user_id'));
    }

    /**
     * Get the HTML formatted title of this notification
     *
     * @return string
     */
    public function get_title()
    {
        return $this->user->lang($this->titleKey).':';
    }

    /**
     * Get the url to this item
     *
     * @return string URL
     */
    public function get_url()
    {
        return append_sid($this->phpbb_root_path . 'app.php/intereststree/tree');
    }

    public function get_redirect_url()
    {
        return $this->get_url();
    }

    /**
     * Get email template
     *
     * @return string|bool
     */
    public function get_email_template()
    {
        return false;
    }

    /**
     * Get the HTML formatted reference of the notification
     *
     * @return string
     */
    public function get_reference()
    {
        return $this->user->lang($this->contentKey);
    }

    /**
     * Get email template variables
     *
     * @return array
     */
    public function get_email_template_variables()
    {
        return array();
    }

    /**
     * Function for preparing the data for insertion in an SQL query
     * (The service handles insertion)
     *
     * @param array $my_notification_data Data from insert_thanks
     * @param array $pre_create_data Data from pre_create_insert_array()
     *
     * @return array Array of data ready to be inserted into the database
     */
    public function create_insert_array($my_notification_data, $pre_create_data = array())
    {
        $this->set_data('user_id', $my_notification_data['user_id']);
        $this->set_data('to_user_id', $my_notification_data['to_user_id']);
        $this->set_data('username', $my_notification_data['username']);

        return parent::create_insert_array($my_notification_data, $pre_create_data);
    }
}