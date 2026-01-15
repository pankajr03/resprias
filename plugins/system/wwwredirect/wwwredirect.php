<?php
/* ======================================================
 # www Redirect for Joomla! - v1.2.8 (free version)
 # -------------------------------------------------------
 # For Joomla! CMS (v4.x)
 # Author: Web357 (Yiannis Christodoulou)
 # Copyright: (©) 2014-2024 Web357. All rights reserved.
 # License: GNU/GPLv3, https://www.gnu.org/licenses/gpl-3.0.html
 # Website: https://www.web357.com
 # Demo: 
 # Support: support@web357.com
 # Last modified: Monday 27 October 2025, 04:02:06 PM
 ========================================================= */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;

class plgSystemWWWRedirect extends CMSPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		
		// Set up logging category
		if (!defined('WWWREDIRECT_LOG_CATEGORY'))
		{
			define('WWWREDIRECT_LOG_CATEGORY', 'plg_system_wwwredirect');
		}
	}

	public function onAfterInitialise()
	{
		// Get the application object.
		$app = Factory::getApplication();

		// Get Params from plugin
		$plugin 			= PluginHelper::getPlugin( 'system', 'wwwredirect' );
		$params 			= new Registry($plugin->params); 
		$redirection_type 	= $params->get('redirection_type', 'with_www');
		$force_https 		= $params->get('force_https', 'with_https');
		$apply_admin 		= $params->get('apply_admin', 0, 'int');
		$exclusion_patterns = $params->get('exclusion_patterns', '', 'string');
		$enable_logging     = $params->get('enable_logging', 0, 'int');
		$preferred_domain   = $params->get('preferred_domain', '', 'string');
		
		// Set up logging if enabled
		if ($enable_logging)
		{
			$log_options = array(
				'text_file' => $params->get('log_file', 'wwwredirect.log', 'string'),
				'text_file_path' => Factory::getConfig()->get('log_path'),
				'text_entry_format' => '{DATETIME} {PRIORITY} {MESSAGE}'
			);
			
			Log::addLogger($log_options, Log::INFO, array(WWWREDIRECT_LOG_CATEGORY));
		}

		// Do not enable the plugin at Joomla! backend unless explicitly enabled
		if ($app->isClient('administrator') && !$apply_admin)
		{
			return;
		}

		// Do not enable the plugin at Localhost
		$exclude_list = array('127.0.0.1', '::1', 'localhost', 'yourdomain.com', 'www.yourdomain.com');
		if (in_array($_SERVER['SERVER_NAME'], $exclude_list)) 
		{
			return;
		}

		// get current request URI
		$current_request_uri = $_SERVER['REQUEST_URI'];
		$current_method = $_SERVER['REQUEST_METHOD'];
		$current_ip = $_SERVER['REMOTE_ADDR'];

		// Check if the current URL matches any exclusion pattern
		if (!empty($exclusion_patterns))
		{
			// Convert the patterns from multiline textbox to array
			$patterns = explode("\n", str_replace("\r", "", $exclusion_patterns));
			
			foreach ($patterns as $pattern)
			{
				$pattern = trim($pattern);
				if (empty($pattern))
				{
					continue;
				}

				// Check if the pattern is a regex (starts and ends with /)
				if (substr($pattern, 0, 1) === '/' && substr($pattern, -1) === '/')
				{
					// It's a regex pattern
					if (@preg_match($pattern, $current_request_uri))
					{
						// Log skipped redirect if logging is enabled
						if ($enable_logging)
						{
							$this->logSkippedRedirect($current_request_uri, $pattern, 'regex', $current_method, $current_ip);
						}
						return; // Skip redirect for this URL
					}
				}
				else
				{
					// It's a simple string match
					if (strpos($current_request_uri, $pattern) !== false)
					{
						// Log skipped redirect if logging is enabled
						if ($enable_logging)
						{
							$this->logSkippedRedirect($current_request_uri, $pattern, 'string', $current_method, $current_ip);
						}
						return; // Skip redirect for this URL
					}
				}
			}
		}

		// get URL
		$current_domain = $_SERVER['HTTP_HOST'];

		// Multi-domain redirection check (highest priority)
		if (!empty($preferred_domain))
		{
			$preferred_domain = trim($preferred_domain);
			
			// Remove protocol from preferred domain if accidentally included
			$preferred_domain = preg_replace('/^https?:\/\//', '', $preferred_domain);
			
			// Check if current domain is different from preferred domain
			if ($current_domain !== $preferred_domain)
			{
				// Determine protocol for destination URL
				$destination_protocol = $force_https === 'with_https' ? 'https' : 
										($force_https === 'non_https' ? 'http' : 
										(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'));
				
				$destination_url = $destination_protocol . '://' . $preferred_domain . $_SERVER['REQUEST_URI'];
				$current_url_for_log = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $current_domain . $_SERVER['REQUEST_URI'];
				
				// Log multi-domain redirect if logging is enabled
				if ($enable_logging)
				{
					$this->logRedirect($current_url_for_log, $destination_url, $current_method, $current_ip);
				}
				
				$this->redirectToTheFinalDestination($destination_url);
				return; // Exit early after redirect
			}
		}

		// get the http(s) var
		if ($force_https === 'with_https')
		{
			$http_s = "https";
		}
		elseif ($force_https === 'non_https')
		{
			$http_s = "http";
		}
		else
		{
			$http_s = isset($_SERVER['HTTPS']) ? "https" : "http";	
		}

		$current_url = $http_s . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		// Build the destination URL
		if ($redirection_type == 'with_www')
		{
			if (strpos($current_url, '//www.') === false) 
			{
				$destination_url = str_replace('//', '//www.', $current_url);
			}
		}
		else if ($redirection_type == 'non_www') 
		{
			if (strpos($current_url, '//www.') !== false) 
			{
				$destination_url = str_replace('//www.', '//', $current_url);
			}
		}

		// Build the final destination URL
		if (!empty($destination_url))
		{
			$final_destination_url = $destination_url;
		}
		else
		{
			$final_destination_url = $current_url;
		}

		// If the final destination URL is not the same with the current, then redirect to the final destination with www or non-www.
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if ($actual_link !== $final_destination_url) 
		{
			// Log actual redirect if logging is enabled
			if ($enable_logging)
			{
				$this->logRedirect($actual_link, $final_destination_url, $current_method, $current_ip);
			}
      		$this->redirectToTheFinalDestination($final_destination_url);
    	}
	}

	// Redirect URL
	public static function redirectToTheFinalDestination($final_destination_url)
	{
		if (!empty($final_destination_url))
		{
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: ' . $final_destination_url);
			exit();
		}
	}
	
	// Log skipped redirects using JLog
	private function logSkippedRedirect($url, $pattern, $match_type, $method, $ip)
	{
		try
		{
			$log_message = sprintf(
				"SKIPPED - IP: %s - Method: %s - URL: %s - Matched pattern: %s (%s)",
				$ip,
				$method,
				$url,
				$pattern,
				$match_type
			);
			
			Log::add($log_message, Log::INFO, WWWREDIRECT_LOG_CATEGORY);
		}
		catch (Exception $e)
		{
			// Silent failure - we don't want to break the site if logging fails
		}
	}
	
	// Log actual redirects using JLog
	private function logRedirect($from_url, $to_url, $method, $ip)
	{
		try
		{
			$log_message = sprintf(
				"REDIRECT - IP: %s - Method: %s - From: %s - To: %s",
				$ip,
				$method,
				$from_url,
				$to_url
			);
			
			Log::add($log_message, Log::INFO, WWWREDIRECT_LOG_CATEGORY);
		}
		catch (Exception $e)
		{
			// Silent failure - we don't want to break the site if logging fails
		}
	}
}