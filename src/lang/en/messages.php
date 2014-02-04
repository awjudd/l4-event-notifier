<?php
/* 
* @Author: Andrew Judd
* @Date:   2014-02-02 21:15:05
* @Last Modified by:   Andrew Judd
* @Last Modified time: 2014-02-04 07:03:06
*/
return array (

    /**
     * An array of all of the mail-specific contents.
     * 
     * @var array
     */
    'mail' => array (
        /**
         * The contents of the email to be sent.
         * 
         * @var string
         */
        'body' => 'An event that you are monitoring (:event) has occurred.

        A stack trace :stacktrace attached to this email.

        Additional Information:
        :additional

        Extended Information:
        :extended
        ',

        /**
         * The subject of the email to be sent.
         * 
         * @var string
         */
        'subject' => ':site - The event (:event) just occurred.',
    ),

    /**
     * An array of all of the SMS-specific contents.
     * 
     * @var array
     */
    'sms' => array (
        /**
         * The body of the SMS to be sent.
         * 
         * @var string
         */
        'body' => ':site - The event (:event) just occurred.',
    ),
);