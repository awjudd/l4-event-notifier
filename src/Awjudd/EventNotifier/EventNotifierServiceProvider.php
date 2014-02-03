<?php namespace Awjudd\EventNotifier;

use App;
use Config;

use Event;
use Exception;

use Sms;

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
		foreach(Config::get('event-notifier::events.listeners', array()) as $event)
		{
			Event::listen($event, function() use($event) {
				
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
				
			});
		}
	}

}