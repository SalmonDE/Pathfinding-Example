<?php
declare(strict_types = 1);

namespace SalmonDE\PathfindingExample;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use salmonde\pathfinding\Pathfinder;

class Main extends PluginBase {

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

			$sender->getLevel()->setBlock(new Vector3($this->pos1->x, $this->pos1->y, $this->pos1->z), Block::get(Block::AIR));
			$sender->getLevel()->setBlock(new Vector3($this->pos2->x, $this->pos2->y, $this->pos2->z), Block::get(Block::AIR));

			$pathfinder = new Pathfinder($sender->getLevel(), $this->pos1, $this->pos2);
			$sender->sendMessage('[A*] Calculating ...');
			$pathfinder->findPath();
			$sender->sendMessage('[A*] Done.');

			$result = $pathfinder->getPathResult();
			if($result === \null){
				$sender->sendMessage('[A*] Null.');
				return true;
			}else{
				foreach($result as $block){
					$sender->getLevel()->setBlock(new Vector3($block->x, $block->y, $block->z), Block::get(Block::GOLD_BLOCK));
				}
			}

			$sender->getLevel()->setBlock(new Vector3($this->pos1->x, $this->pos1->y, $this->pos1->z), Block::get(Block::DIAMOND_BLOCK));
			$sender->getLevel()->setBlock(new Vector3($this->pos2->x, $this->pos2->y, $this->pos2->z), Block::get(Block::EMERALD_BLOCK));
		}else{
			return \false;
		}

		return \true;
	}
}
