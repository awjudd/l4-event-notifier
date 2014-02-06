<?php namespace Awjudd\EventNotifier;

use App;
use Config;
use Event;
use Exception;
use Mail;
use Sms;
use Str;
use Lang;

use Illuminate\Support\ServiceProvider;

class EventNotifierServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Whether or not email notifications are enabled.
	 * 
	 * @var boolean
	 */
	private $email_enabled = false;

	/**
	 * Whether or not SMS notifications are enabled.
	 * 
	 * @var boolean
	 */
	private $sms_enabled = false;


	private $storage = null;

	/**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('awjudd/event-notifier');

		// Figure out if we should set up the object
		if($this->is_enabled())
		{
			// Configure Twilio if needed
			if($this->sms_enabled)
			{
				// Map our config values to the config values needed for the Twilio plugin
				Config::set('laratwilio::laratwilio.accountSid', Config::get('event-notifier::notification.sms.config.sid'));
				Config::set('laratwilio::laratwilio.authToken', Config::get('event-notifier::notification.sms.config.token'));
				Config::set('laratwilio::laratwilio.fromNumber', Config::get('event-notifier::notification.sms.config.from'));
			}

			// Wire up the regular events
			$this->wire_event_listeners();

			// Wire up the special case events
			$this->wire_special_listeners();

			$this->storage = storage_path(Config::get('event-notifier::storage'));

			// Setup the storage directory
			if(!file_exists($this->storage))
			{
				// It didn't exist, so make the folder
				mkdir($this->storage, 0777);
			}
		}
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

	/**
	 * Returns or not the entire plugin is enabled.
	 * 
	 * @param string $section If null, then it'll look through every step, otherwise the specific piece.
	 * 
	 * @return boolean
	 */
	private function is_enabled($section = null)
	{
		// Assume we are enabled
		$enabled = true;

		// Looking through to make sure at least 1 notification engine is enabled
		$subStatus = false;

		// Are we globally enabled?
		if($section === null)
		{
			// Check if we globally enabled it
			if(!Config::get('event-notifier::enabled.force'))
			{
				// It wasn't globally enabled, so check if we are in the correct environment
				$enabled = in_array(App::environment(), Config::get('event-notifier::enabled.environments', array()));
			}
		}

		// Check about the mail notification
		if($enabled && ( $section == 'mail' || $section == null ) )
		{
			// Is the email notification engine enabled?
			if(Config::get('event-notifier::notification.mail.enabled'))
			{
				$subStatus |= true;
				$this->email_enabled = (true & $enabled) == 1;
			}
		}

		// Check about the SMS notification
		if($enabled && ($section == 'sms' || $section == null))
		{
			// Is the SMS notification engine enabled?
			if(Config::get('event-notifier::notification.sms.enabled'))
			{
				$subStatus |= true;
				$this->sms_enabled = (true & $enabled) == 1;
			}
		}

		// Return whatever we derived
		return ($enabled & $subStatus) == 1;
	}

	/**
	 * Wires up all of the necessary event listeners
	 * 
	 * @return void
	 */
	private function wire_event_listeners()
	{
		// Cycle through all of the events that are set to listen for
		foreach(Config::get('event-notifier::events.listeners', array()) as $event)
		{
			// Add in a listener
			Event::listen($event, function() use($event) {
				$this->send_sms($event);
			});
		}
	}

	/**
	 * Wires up all of the special-case listeners
	 * 
	 * @return void
	 */
	private function wire_special_listeners()
	{
		// Grab the list of special events
		$special = Config::get('event-notifier::events.special', array());

		// Check if the application error exists
		if(in_array('app.error', $special))
		{
			// It did, so wire it through
			App::error(function(Exception $ex) {
				$this->send_email('Application Error', $ex->getMessage());
				$this->send_sms('Application Error - ' . $ex->getMessage());
			});
		}
	}

	/**
	 * Sends a detailed email to the recipient.
	 * 
	 * @param string $event The event that occurred
	 * @param string $additional Any additional information that should be provided
	 * @return void
	 */
	private function send_email($event, $additional)
	{
		// Check if emails are enabled
		if(!$this->email_enabled)
		{
			// It isn't enabled, so just return
			return;
		}

		$title = Lang::get( Config::get('event-notifier::notification.mail.subject'),
				array(
					'event' => Str::limit($event, 50),
					'site' => Config::get('event-notifier::site.name'),
				)
			);
 
 		$data = array(
			'additional' => $additional,
			'event' => $event,
			'site' => Config::get('event-notifier::site.name'),
		);
						
		// Send an email
		Mail::queue(array( 'text' => Config::get('event-notifier::notification.mail.body') ), $data, function($message) use($title) {
			// Who will receive the file?
			$message->to(Config::get('event-notifier::notification.mail.to', array()));

			// Define the subject
			$message->subject($title);
		});
	}

	/**
	 * Sends a SMS message to all of the desired recipients.
	 * 
	 * @param string $event The name of the event that was captured
	 * @return void
	 */
	private function send_sms($event)
	{
		// Check if SMSes are enabled
		if(!$this->sms_enabled)
		{
			// It isn't enabled, so just return
			return;
		}

		// Loop thorugh all of the phone numbers
		foreach(Config::get('event-notifier::notification.sms.to', array()) as $number)
		{
			// Send out an email
			Sms::send(
				array(
					'to' => $number,
					'text' => Lang::get(
						Config::get('event-notifier::notification.sms.body'), 
						array(
							'event' => Str::limit($event, 50),
							'site' => Config::get('event-notifier::site.name'),
						)
					),
				)
			);
		}
	}

}