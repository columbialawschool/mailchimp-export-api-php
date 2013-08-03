<?php

// Include (or load) the library
include_once('MailChimpExportAPI.class.php');

// Alias the library class as MailChimpExport for convenience
use ColumbiaLawSchool\Integration\Email\MailChimp\API\Export as MailChimpExport;

// Construct the object
//
// @todo Be sure to pass a valid API key for the MailChimp account
//   to be accessed.
$MCE = new MailChimpExport('YOUR_API_KEY');

// Export list membership details (by passing a valid, unique
// MailChimp List ID) into an associative array, keyed by
// email address, of objects
//
// @link http://kb.mailchimp.com/article/how-can-i-find-my-list-id
//   Instructions for determining and retrieving a MailChimp List ID
$list = $MCE->getList('LIST_ID');

// @todo Interate through the returned $list array and execute the desired
//   logic on each element.

// Export subscriber activity (opens and clicks) for a campaign (by passing a
// valid unique MailChimp Campaign ID) into an associative array, keyed by
// email address, of objects
//
// @link http://mailchimp.com/contact/campaign-id
//   Instructions on how to determine and retrieve a MailChimp Campaign ID
$activity = $MCE->getCampaignSubscriberActivity('CAMPAIGN_ID');

// @todo Interate through the returned $activity array and execute the desired
//   logic on each element.