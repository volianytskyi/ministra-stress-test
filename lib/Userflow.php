<?php

use ApiV3Exceptions\StbEmuException;

class Userflow
{
  private $emu;
  private $lastPing;
  public $nextChannelsNumber;
  public $watchingBoundStart;
  public $watchingBoundEnd;
  public $actionSleepTimeout;
  public $pingSleepTimeout;
  public $magcoreTheme;
  public $maxChannelsToZapNumber;

  public function __construct(StbEmu $emu)
  {
    $this->emu = $emu;
    $this->nextChannelsNumber = 10;
    $this->watchingBoundStart = 60 * 10; //10 minutes
    $this->watchingBoundEnd = 60 * 60 * 2; //2 hours
    $this->actionSleepTimeout = 15; //seconds
    $this->pingSleepTimeout = 120; //seconds
    $this->magcoreTheme = 'magcore-theme-ministra';
    $this->maxChannelsToZapNumber = 30;
  }

  public function run()
  {
    $this->load();
    $this->zapping();
  }

  private function load()
  {
    $this->emu->auth();

    $this->emu->ping();
    $this->lastPing = time();

    $this->emu->getModules();

    $this->emu->getSettings();

    $this->emu->getTvChannels(500);

    $this->emu->getTheme($this->magcoreTheme);

    $this->emu->getTvGenres();
  }

  private function zapping()
  {
    $channels = array_column($this->emu->getTvChannels(500)['data'], 'id');
    if(count($channels) < $this->maxChannelsToZapNumber)
    {
      $this->maxChannelsToZapNumber = count($channels);
    }

    $channelsToZapp = mt_rand(1, $this->maxChannelsToZapNumber);

    echo "User [$this->emu->login] is zapping $channelsToZapp channels...\n";

    //random channel to watch
    $channelIndex = array_rand($channels);
    $channelToWatch = $channels[$channelIndex];

    $nextChannels = array_slice($channels, $channelIndex, $this->nextChannelsNumber);

    $from = time();
    // to = from + 2 hours 30 minutes
    $to = $from + 60 * 60 * 2 + 60 * 30;
    for($i = 0; $i < $channelsToZapp; $i++)
    {
      $channelLink = $this->emu->getChannelLink($channels[$i])['data']['url'];
      $this->emu->playChannel($channelLink);
      $this->emu->getChannelEpg($channels[$i], $from, $to);
      sleep(mt_rand(3, 7));
    }
    $this->watching($channelToWatch, $nextChannels);
  }

  private function watching(&$channelToWatch, &$nextChannels)
  {
    $start = time();

    $end = mt_rand($start + $this->watchingBoundStart, $start + $this->watchingBoundEnd);

    echo "User [$this->emu->login] has started to watch the channel [$channelToWatch]\n";
    echo "User [$this->emu->login] will watch the channel [$channelToWatch] for " . floor(($end - $start)/60) . " minutes\n";
    $from = time();
    $to = $from + 60 * 60 * 2 + 60 * 30;
    $channelLink = $this->emu->getChannelLink($channelToWatch)['data']['url'];
    $this->emu->playChannel($channelLink);
    $this->emu->getChannelEpg($channelToWatch, $from, $to);

    while(true)
    {
      
      try
      {
        foreach(['added', 'deleted', 'modified'] as $action)
        {
          sleep($this->actionSleepTimeout);
          $this->emu->updateChannelsList($action, $lastPing);
          $this->lastPing = time();
        }
        $this->emu->ping();
        $this->emu->getNextEpg($nextChannels);
        $this->lastPing = time();

        if(time() > $end)
        {
          $this->zapping();
        }
      }
      catch (ApiV3Exceptions\StbEmuException $e)
      {
        if(strpos($e->getMessage(), 'Authorization required') !== false)
        {
          $emu->auth(true);
          continue;
        }
      }

      sleep($this->pingSleepTimeout);
    }
  }
}



 ?>
