<?php

define('EVEAPI_ROOT', 'http://api.eve-online.com');
define('ACCOUNTS_FILE', 'accounts.ini');

if (!is_file(ACCOUNTS_FILE))
{
	printf('Missing %s', ACCOUNTS_FILE);
	exit(1);
}

$accounts = parse_ini_file(ACCOUNTS_FILE, true);

$args = $_SERVER['argv'];

$script = array_shift($args);
$nick = array_shift($args);
$channel = array_shift($args);
$sender = array_shift($args);
$cmd = array_shift($args);
$account = strtolower(array_shift($args));

define('IS_FORCED_COMMAND', !empty($cmd));

if (!empty($account) && isset($accounts[$account]))
{
	$accounts = array($account => $accounts[$account]);
}

function for_each_account($fn, $url)
{
	global $accounts;

	foreach ($accounts as $account)
	{
		if (empty($account['enabled']))
		{
			continue;
		}

		$reader = new XMLReader();

		$reader->open(EVEAPI_ROOT .
			str_replace(
				array(
					'@apiKey',
					'@userId',
					'@characterId'
				),
				array(
					$account['apiKey'],
					$account['userId'],
					$account['characterId']
				),
				$url));

		call_user_func($fn, $account, $reader);

		$reader->close();
	}
}

function format_isk($amount)
{
	static $steps = array(
		'T' => 1000000000000,
		'B' => 1000000000,
		'M' => 1000000,
		'K' => 1000
	);

	foreach ($steps as $suffix => $step)
	{
		if ($amount / $step > 1)
		{
			return sprintf('%s%s',
				number_format($amount / $step, 2, '.', ','),
				$suffix);
		}
	}

	return sprintf('%s ISK', number_format($amount, 2, '.', ','));
}

function format_balance($amount)
{
	static $steps = array(
		'Trillion' => 1000000000000,
		'Billion' => 1000000000,
		'Million' => 1000000,
		'Thousand' => 1000
	);

	foreach ($steps as $suffix => $step)
	{
		if ($amount / $step > 1)
		{
			return sprintf('%s %s ISK',
				number_format($amount / $step, 2, '.', ','),
				$suffix);
		}
	}

	return sprintf('%s ISK', number_format($amount, 2, '.', ','));
}
