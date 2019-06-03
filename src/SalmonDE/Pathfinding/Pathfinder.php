<?php
declare(strict_types = 1);

namespace SalmonDE\Pathfinding;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
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

            $sender->level->setBlock(new Vector3($this->pos1->x, $this->pos1->y, $this->pos1->z), Block::get(Block::AIR));
            $sender->level->setBlock(new Vector3($this->pos2->x, $this->pos2->y, $this->pos2->z), Block::get(Block::AIR));

            $pathfinder = new AStar($sender->level, $this->pos1, $this->pos2);
            $sender->sendMessage('[A*] Calculating ...');
            $node = $pathfinder->findPath();
            $sender->sendMessage('[A*] Done.');

            if($node === \null){
                $sender->sendMessage('[A*] Null.');
            }else{
                $v = new Vector3();
                do{
                    if(($node = $node->getPredecessor()) instanceof Node){
                        $v->setComponents($node->x, $node->y, $node->z);
                        $sender->level->setBlock($v, Block::get(Block::GOLD_BLOCK));
                    }else{
                        break;
                    }
                }while(\true);
            }

            $sender->level->setBlock(new Vector3($this->pos1->x, $this->pos1->y, $this->pos1->z), Block::get(Block::DIAMOND_BLOCK));
            $sender->level->setBlock(new Vector3($this->pos2->x, $this->pos2->y, $this->pos2->z), Block::get(Block::EMERALD_BLOCK));
        }else{
            return \false;
        }

        return \true;
    }
}
