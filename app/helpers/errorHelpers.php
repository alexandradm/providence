<?php
/** ---------------------------------------------------------------------
 * app/helpers/errorHelpers.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2015 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 * 
 * @package CollectiveAccess
 * @subpackage utils
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 * 
 * ----------------------------------------------------------------------
 */

 /**
   *
   */
    
	# --------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caDisplayException(Exception $e) {
		if(class_exists('AppController')) { AppController::getInstance()->removeAllPlugins(); }
		
		$pn_errno = 0;
		$ps_errstr = $e->getMessage();
		$ps_errfile = __FILE__;
		$pn_errline = __LINE__;
		$pa_errcontext = $e->getTrace();
		$pa_errcontext_args = caExtractStackTraceArguments($pa_errcontext);
		$pa_request_params = caExtractRequestParams();
		
		require_once((defined("__CA_THEME_DIR__") ? __CA_THEME_DIR__ : __DIR__.'/../../themes/default').'/views/system/fatal_error_html.php');
		exit;
	}
	# --------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caDisplayFatalError($pn_errno, $ps_errstr, $ps_errfile, $pn_errline, $pa_symboltable) {
	
		$pa_errcontext = debug_backtrace(); 
		array_shift($pa_errcontext); // remove entry for error handler
		$pa_errcontext_args = caExtractStackTraceArguments($pa_errcontext);
		$pa_request_params = caExtractRequestParams();
		
		switch($pn_errno) {
			case E_WARNING:
			case E_NOTICE:
			case E_STRICT:
			case E_DEPRECATED:
				//print "ARGH: $ps_errstr<br>";
				break;
			default:
				if(class_exists('AppController')) { AppController::getInstance()->removeAllPlugins(); }
				require_once((defined("__CA_THEME_DIR__") ? __CA_THEME_DIR__ : __DIR__."/../../themes/default")."/views/system/fatal_error_html.php");
				exit;
		}
	}
	# --------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caExtractStackTraceArguments($pa_errcontext) {
		if(!is_array($pa_errcontext)) { return array(); }
		$pa_args = array();
		foreach($pa_errcontext as $vn_i => $va_trace) {
			if(!is_array($va_trace)) { return array(); }
			if(!isset($va_trace['args']) || !is_array($va_trace['args'])) { return array(); }
			$pa_args[$vn_i] = array();
			foreach($va_trace['args'] as $vn_j => $vm_arg) {
				if (is_object($vm_arg)) { 
					$pa_args[$vn_i][] = 'Object '.get_class($vm_arg);
				} elseif(is_array($vm_arg)) {
					$pa_args[$vn_i][] = 'Array('.sizeof($vm_arg).')';
				} elseif(is_resource($vm_arg)) {
					$pa_args[$vn_i][] = 'Resource';
				} elseif(is_bool($vm_arg)) {
					$pa_args[$vn_i][] = $vm_arg ? "true" : "false";
				} elseif(is_string($vm_arg)) {
					$pa_args[$vn_i][] = "'".(string)$vm_arg."'";
				} else {
					$pa_args[$vn_i][] = (string)$vm_arg;
				}
			}
		}
		return $pa_args;
	}
	# --------------------------------------------------------------------------------------------
	/**
	 *
	 */
	function caExtractRequestParams() {
		if(!include_once(pathinfo(__FILE__, PATHINFO_DIRNAME).'/../../vendor/autoload.php')) { return array(); }
		
		if(!is_array($_REQUEST)) { return array(); }
		
		$o_purifier = new HTMLPurifier();
		$pa_params = array();
		foreach($_REQUEST as $vs_k => $vm_val) {
			if($vs_k == 'password') { continue; } // don't dump plain text passwords on screen
			$pa_params[$o_purifier->purify($vs_k)] = $o_purifier->purify($vm_val);
		}

		return $pa_params;
	}
	# --------------------------------------------------------------------------------------------
		
