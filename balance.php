<?php

require 'core.php';

function read_balance($account, XMLReader $reader)
{
	while ($reader->read())
	{
		switch ($reader->name)
		{
			case 'currentTime':

				$reader->next();

				break;

			case 'error':

				error_log($account['prefix'] . $reader->readString(), 0);

				$reader->next();

				break 2;

			case 'row':

				echo $account['prefix'], format_balance($reader->getAttribute('balance')), "\n";

				break;
		}
	}
}

for_each_account('read_balance', '/char/AccountBalance.xml.aspx?apiKey=@apiKey&userID=@userId&characterID=@characterId');