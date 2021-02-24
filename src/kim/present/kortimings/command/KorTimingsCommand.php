<?php

/**
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 *
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpDocSignatureInspection
 * @noinspection SpellCheckingInspection
 */

declare(strict_types=1);

namespace kim\present\kortimings\command;

use kim\present\kortimings\utils\RomajaConverter;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\defaults\TimingsCommand;
use pocketmine\lang\TranslationContainer;
use pocketmine\player\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\scheduler\BulkCurlTaskOperation;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\InternetException;

use function http_build_query;
use function implode;
use function is_array;
use function json_decode;
use function strtolower;

final class KorTimingsCommand extends TimingsCommand{
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
        if(!$this->testPermission($sender))
            return true;

        //If not "paste" mode, pass it to be processed by PMMP
        if(strtolower($args[0] ?? "") !== "paste")
            return parent::execute($sender, $commandLabel, $args);

        if(!TimingsHandler::isEnabled()){
            $sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsDisabled"));

            return true;
        }

        $data = [
            "browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
            "data" => RomajaConverter::convert(implode(PHP_EOL, TimingsHandler::printTimings()))
        ];

        $host = Server::getInstance()->getConfigGroup()->getProperty("timings.host", "timings.pmmp.io");
        $sender->getServer()->getAsyncPool()->submitTask(new class($sender, $host, $agent, $data) extends BulkCurlTask{
            private const TLS_KEY_SENDER = "sender";
            private string $host;

            /** @param string[] $data */
            public function __construct(CommandSender $sender, string $host, string $agent, array $data){
                parent::__construct([
                    new BulkCurlTaskOperation(
                        "https://$host?upload=true",
                        10,
                        [],
                        [
                            CURLOPT_HTTPHEADER => [
                                "User-Agent: $agent",
                                "Content-Type: application/x-www-form-urlencoded"
                            ],
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => http_build_query($data),
                            CURLOPT_AUTOREFERER => false,
                            CURLOPT_FOLLOWLOCATION => false
                        ]
                    )
                ]);
                $this->host = $host;
                $this->storeLocal(self::TLS_KEY_SENDER, $sender);
            }

            public function onCompletion() : void{
                /** @var CommandSender $sender */
                $sender = $this->fetchLocal(self::TLS_KEY_SENDER);
                if($sender instanceof Player && !$sender->isOnline())
                    return;

                $result = $this->getResult()[0];
                if($result instanceof InternetException){
                    $sender->getServer()->getLogger()->logException($result);
                    return;
                }
                $response = json_decode($result->getBody(), true);
                if(is_array($response) && isset($response["id"])){
                    Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.timingsRead",
                        ["https://{$this->host}/?id=" . $response["id"]]));
                }else{
                    Command::broadcastCommandMessage($sender, new TranslationContainer("pocketmine.command.timings.pasteError"));
                }
            }
        });

        return true;
    }
}