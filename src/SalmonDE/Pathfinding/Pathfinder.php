<?php
declare(strict_types = 1);

namespace SalmonDE\Pathfinding;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class Pathfinder extends PluginBase {

    private $pos1 = \null;
    private $pos2 = \null;

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $params): bool{
        if(!isset($params[0])){
            return \false;
        }

        if($params[0] === '1'){
            $this->pos1 = $sender->floor();
            $sender->sendMessage('[A*] Pos 1 set');
        }elseif($params[0] === '2'){
            $this->pos2 = $sender->floor();
            $sender->sendMessage('[A*] Pos 2 set');
        }elseif($params[0] === 'calculate'){
            if($this->pos1 === \null || $this->pos2 === \null){
                return \false;
            }

            $sender->level->setBlockIdAt($this->pos1->x, $this->pos1->y, $this->pos1->z, 0);
            $sender->level->setBlockIdAt($this->pos2->x, $this->pos2->y, $this->pos2->z, 0);

            $pathfinder = new AStar($sender->level, $this->pos1, $this->pos2);
            $sender->sendMessage('[A*] Calculating ...');
            $node = $pathfinder->findPath();
            $sender->sendMessage('[A*] Done.');

            if($node === \null){
                $sender->sendMessage('[A*] Null.');
            }else{
                do{
                    if(($node = $node->getPredecessor()) instanceof Node){
                        $sender->level->setBlockIdAt($node->x, $node->y, $node->z, 41);
                    }else{
                        break;
                    }
                }while(\true);
            }

            $sender->level->setBlockIdAt($this->pos1->x, $this->pos1->y, $this->pos1->z, 57);
            $sender->level->setBlockIdAt($this->pos2->x, $this->pos2->y, $this->pos2->z, 133);
        }else{
            return \false;
        }

        return \true;
    }
}
