<?php
declare(strict_types = 1);

namespace SalmonDE\PathfindingExample;

use pocketmine\block\BlockLegacyIds as IDs;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use salmonde\pathfinding\Pathfinder;
use salmonde\pathfinding\astar\selector\NeighbourSelectorXZ;

class Main extends PluginBase {

	private $pos1 = null;
	private $pos2 = null;

	private $lastPathResult = null;
	private $lastPathWorld = null;

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $params): bool{
		if(!isset($params[0])){
			return false;
		}

		if($params[0] === '1'){
			$this->pos1 = $sender->getPosition()->floor();
			$sender->sendMessage('[A*] Pos 1 set');
		}elseif($params[0] === '2'){
			$this->pos2 = $sender->getPosition()->floor();
			$sender->sendMessage('[A*] Pos 2 set');
		}elseif($params[0] === 'calculate'){
			if($this->pos1 === null || $this->pos2 === null){
				return false;
			}

			$sender->getWorld()->setBlock($this->pos1, VanillaBlocks::AIR());
			$sender->getWorld()->setBlock($this->pos2, VanillaBlocks::AIR());

			$pathfinder = new Pathfinder($sender->getWorld(), $this->pos1, $this->pos2);
			$pathfinder->setMaxJumpHeight(1);

			if(isset($params[1])){
				if(strtolower($params[1]) === '2d'){
					$pathfinder->getAlgorithm()->setNeighbourSelector(new NeighbourSelectorXZ());
				}
			}

			if($this->lastPathResult !== null){
				$sender->sendMessage("[A*] Clearing last path ...");
				$this->clearLastPath();
			}

			$sender->sendMessage('[A*] Calculating ...');
			$pathfinder->findPath();
			$sender->sendMessage('[A*] Done.');

			$this->lastPathResult = $pathfinder->getPathResult();
			$this->lastPathWorld = $sender->getWorld();
			if($this->lastPathResult === null){
				$sender->sendMessage('[A*] Null.');
				return true;
			}else{
				foreach($this->lastPathResult as $blockPos){
					$sender->getWorld()->setBlock($blockPos, VanillaBlocks::GOLD());
				}
			}

			$sender->getWorld()->setBlock($this->pos1, VanillaBlocks::DIAMOND());
			$sender->getWorld()->setBlock($this->pos2, VanillaBlocks::EMERALD());
		}elseif($params[0] === 'repeat'){
			if($this->pos1 === null || $this->pos2 === null){
				return false;
			}

			$pathfinder = new Pathfinder($sender->getWorld(), $this->pos1, $this->pos2);

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

	public function clearLastPath(): void{
		if($this->lastPathResult === null){
			return;
		}

		foreach($this->lastPathResult as $blockPos){
			if($this->lastPathWorld->getBlock($blockPos)->getId() === Ids::GOLD_BLOCK){
				$this->lastPathWorld->setBlock($blockPos, VanillaBlocks::AIR());
			}
		}

		$this->lastPathResult = null;
		$this->lastPathWorld = null;

		if($this->lastPathWorld->getBlock($this->pos1)->getId() === Ids::DIAMOND_BLOCK){
			$this->lastPathWorld->setBlock($this->pos1, VanillaBlocks::AIR());
		}

		if($this->lastPathWorld->getBlock($this->pos2)->getId() === Ids::EMERALD_BLOCK){
			$this->lastPathWorld->setBlock($this->pos2, VanillaBlocks::AIR());
		}
	}

	public function onDisable(): void{
		if($this->lastPathResult !== null){
			$this->clearLastPath();
		}
	}
}
