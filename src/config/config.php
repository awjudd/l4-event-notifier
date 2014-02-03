<?php
/* 
* @Author: Andrew Judd
* @Date:   2014-02-02 19:25:26
* @Last Modified by:   Andrew Judd
* @Last Modified time: 2014-02-02 23:06:46
*/
return array (

    /**
     * Is the monitoring (and notification) of events enabled?
     * 
     * @var array
     */
    'enabled' => array (

        /**
         * What environments is this enabled in?  The environment name
         * here should match that from App::environment()
         * 
         * @var array
         */
        'environments' => array (
            'production'
        ),

        /**
         * Should we force the notification to be enabled?
         * 
         * @var boolean
         */
        'force' => true,
    ),

    /**
     * An array of all of the events that should be monitored
     * 
     * @var array
     */
    'events' => array (

        /**
         * An array of any events that should be listened for.
         * 
         * @var array
         */
        'listeners' => array (

        ),

        /**
         * An array of any special cases.
         * 
         * Handled Options:
         *  - 'app.error' => App::error()
         * 
         * @var array
         */
        'special' => array (
            'app.error',
        ),
    ),

    /**
     * All of the notification based settings
     * 
     * @var array
     */
    'notification' => array (

        /**
         * Settings for any email notifications that are sent (and if enabled)
         * 
         * @var array
         */
        'mail' => array (

            /**
             * The name in the resource file for the email's body template.
             * 
             * @var string
             */
            'body' => 'eventnotifier::messages.mail.body',

            /**
             * Whether or not email notifications are enabled
             * 
             * @var boolean
             */
            'enabled' => true,

            /**
             * The name in the resource file for the email's subject.
             * 
             * @var string
             */
            'subject' => 'eventnotifier::messages.mail.subject',

            /**
             * An array of email addresses to send the notification to
             * 
             * @var array
             */
            'to' => array (
            ),
        ),

        'sms' => array (

            /**
             * The name in the resource file for the email's body template.
             * 
             * @var string
             */
            'body' => 'eventnotifier::messages.sms.body',

            /**
             * Whether or not SMS notifications are enabled
             * 
             * @var boolean
             */
            'enabled' => true,

            /**
             * An array of phone numbers to send a text to.
             * 
             * @var array
             */
            'to' => array (
            ),
        ),
    ),
);