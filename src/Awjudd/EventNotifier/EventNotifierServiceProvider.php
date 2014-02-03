<?php namespace Awjudd\EventNotifier;

use App;
use Config;

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
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('awjudd/event-notifier');

        dd($this->is_enabled());
		// Figure out if we should set up the object
		if($this->is_enabled())
		{
			// We are enabled, so start wiring up the events
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
		if($enabled || $section == 'mail' )
		{
			// Is the email notification engine enabled?
			if(Config::get('event-notifier::notification.mail.enabled'))
			{
				$subStatus |= true;
			}
		}

		// Check about the SMS notification
		if($enabled || $section == 'sms' )
		{
			// Is the SMS notification engine enabled?
			if(Config::get('event-notifier::notification.sms.enabled'))
			{
				$subStatus |= true;
			}
		}

		// Return whatever we derived
		return $enabled & $subStatus;
	}

}