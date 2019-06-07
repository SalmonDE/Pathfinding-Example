<?php
declare(strict_types = 1);

namespace SalmonDE\PathfindingExample;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use salmonde\pathfinding\Pathfinder;
use salmonde\pathfinding\astar\selector\NeighbourSelectorXZ;

class Main extends PluginBase {

	private $pos1 = null;
	private $pos2 = null;

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $params): bool{
		if(!isset($params[0])){
			return false;
		}

		if($params[0] === '1'){
			$this->pos1 = $sender->floor();
			$sender->sendMessage('[A*] Pos 1 set');
		}elseif($params[0] === '2'){
			$this->pos2 = $sender->floor();
			$sender->sendMessage('[A*] Pos 2 set');
		}elseif($params[0] === 'calculate'){
			if($this->pos1 === null || $this->pos2 === null){
				return false;
			}

			$sender->getLevel()->setBlock($this->pos1, Block::get(Block::AIR));
			$sender->getLevel()->setBlock($this->pos2, Block::get(Block::AIR));

			$pathfinder = new Pathfinder($sender->getLevel(), $this->pos1, $this->pos2);

			if(isset($params[1])){
				if(strtolower($params[1]) === '2d'){
					$pathfinder->getAlgorithm()->setNeighbourSelector(new NeighbourSelectorXZ());
				}
			}

			$sender->sendMessage('[A*] Calculating ...');
			$pathfinder->findPath();
			$sender->sendMessage('[A*] Done.');

			$result = $pathfinder->getPathResult();
			if($result === null){
				$sender->sendMessage('[A*] Null.');
				return true;
			}else{
				foreach($result as $block){
					$sender->getLevel()->setBlock($block, Block::get(Block::GOLD_BLOCK));
				}
			}

			$sender->getLevel()->setBlock($this->pos1, Block::get(Block::DIAMOND_BLOCK));
			$sender->getLevel()->setBlock($this->pos2, Block::get(Block::EMERALD_BLOCK));
		}elseif($params[0] === 'repeat'){
			if($this->pos1 === null || $this->pos2 === null){
				return false;
			}

			$pathfinder = new Pathfinder($sender->getLevel(), $this->pos1, $this->pos2);

			if(isset($params[1])){
				if(strtolower($params[1]) === '2d'){
					$pathfinder->getAlgorithm()->setNeighbourSelector(new NeighbourSelectorXZ());
				}
			}

			$this->getScheduler()->scheduleRepeatingTask(new PathVisualizerTask($pathfinder), 40);
			$sender->sendMessage('Visualizing ...');
		}elseif($params[0] === 'clear'){
			$this->getScheduler()->cancelAllTasks();
			$sender->sendMessage('[A*] Cleared.');
		}else{
			return false;
		}

		return true;
	}
}
