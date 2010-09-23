<?php

require 'core.php';

function read_transactions($account, XMLReader $reader)
{
	$timestampFile = $account['characterId'] . '.transactions.check';

	$lastChecked =
		is_file($timestampFile)
			? file_get_contents($timestampFile)
			: '';

	if (empty($lastChecked))
	{
		$lastChecked = date('Y-m-d 00:00:00');
	}

	$currentTime = '';

	$messages = array();

	while ($reader->read())
	{
		switch ($reader->name)
		{
			case 'currentTime':

				$currentTime = $reader->readString();

				$reader->next();

				break;

			case 'error':

				error_log($account['prefix'] . $reader->readString(), 0);

				$reader->next();

				$currentTime = '';

				break 2;

			case 'row':

				$ts = $reader->getAttribute('transactionDateTime');

				if ($ts <= $lastChecked)
				{
					break 2;
				}
				
				$for = $reader->getAttribute('transactionFor');
				
				if ($for === 'personal')
				{
					break;
				}
				
				$quantity = (int) $reader->getAttribute('quantity');

				$price = (float) $reader->getAttribute('price') * $quantity;

				if ($price < (float) $account['minPrice'])
				{
					break;
				}

				$messages[] = str_replace(
					array(
						'@quantity',
						'@typeName',
						'@price',
						'@stationName',
						'@transactionType',
						'@prefix'
					),
					array(
						$quantity,
						$reader->getAttribute('typeName'),
						format_isk($price),
						$reader->getAttribute('stationName'),
						strtoupper($reader->getAttribute('transactionType')),
						$account['prefix']
					),
					$account['transactionMessage']);

				break;
		}
	}

	if (!empty($currentTime))
	{
		file_put_contents($timestampFile, $currentTime);
	}

	foreach (array_reverse($messages) as $message)
	{
		echo $message . "\n";
	}
}

for_each_account('read_transactions', '/char/WalletTransactions.xml.aspx?apiKey=@apiKey&userID=@userId&characterID=@characterId');
