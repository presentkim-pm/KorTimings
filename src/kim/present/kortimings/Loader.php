<?php
declare(strict_types=1);

namespace kim\present\kortimings;

use kim\present\kortimings\command\KorTimingsCommand;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{
    protected function onLoad() : void{
        $commandMap = $this->getServer()->getCommandMap();

        //Unregister "pocketmine:timings" command
        $pmTimings = $commandMap->getCommand("timings");
        if($pmTimings !== null){
            $commandMap->unregister($pmTimings);
        }

        //Register "kor:timings" command
        $commandMap->register("kor", new KorTimingsCommand("timings"));
    }
}