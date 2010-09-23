<?php

require 'core.php';

function read_skillqueue($account, XMLReader $reader)
{
	$currentTime = 0;
	$endTime = 0;

	while ($reader->read())
	{
		switch ($reader->name)
		{
			case 'currentTime':

				$currentTime = strtotime($reader->readString());

				$reader->next();

				break;

			case 'error':

				error_log($account['prefix'] . $reader->readString(), 0);

				$reader->next();

				break 2;

			case 'row':

				$endTime = strtotime($reader->getAttribute('endTime'));

				break;
		}
	}

	if (empty($endTime))
	{
		echo $account['prefix'], "MAJOR FAIL! Skill queue empty!\n";
	}
	else
	{
		$steps = array(
			'd' => 3600 * 24,
			'h' => 3600,
			'min' => 60
		);

		$parts = array();

		$timeLeft = $endTime - $currentTime;

		if (IS_FORCED_COMMAND || $timeLeft < $steps['d'])
		{
			foreach ($steps as $type => $step)
			{
				$diff = floor($timeLeft / $step);

				if ($diff > 0)
				{
					$timeLeft -= $diff * $step;

					$parts[] = sprintf('%d%s', $diff, $type);
				}
			}

			echo $account['prefix'],
				sprintf("Skill queue ends in %s\n", implode(' ', $parts));
		}
	}
}

for_each_account('read_skillqueue', '/char/SkillQueue.xml.aspx?apiKey=@apiKey&userID=@userId&characterID=@characterId');