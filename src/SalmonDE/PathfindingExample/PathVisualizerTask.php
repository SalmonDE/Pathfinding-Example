<?php
declare(strict_types = 1);

namespace SalmonDE\PathfindingExample;

use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\scheduler\Task;
use salmonde\pathfinding\Pathfinder;
use salmonde\pathfinding\PathResult;

class PathVisualizerTask extends Task {

	private $pathfinder;

	public function __construct(Pathfinder $pathfinder){
		$this->pathfinder = $pathfinder;
	}

	public function onRun(int $ct): void{
		$this->pathfinder->getAlgorithm()->resetPathResult();
		$this->pathfinder->findPath();
		$path = $this->pathfinder->getPathResult();

		if(!($path instanceof PathResult)){
			return;
		}

		$world = $this->pathfinder->getAlgorithm()->getWorld();
		$particle = new LavaDripParticle();
		foreach($path as $pos){
			$world->addParticle($pos->add(0.5, 0.5, 0.5), $particle);
		}

		$particle = new DustParticle(0, 0, 255);
		$world->addParticle($this->pathfinder->getAlgorithm()->getStartPos()->add(0.5, 0.5, 0.5), $particle);

		$particle = new DustParticle(0, 255, 0);
		$world->addParticle($this->pathfinder->getAlgorithm()->getTargetPos()->add(0.5, 0.5, 0.5), $particle);
	}
}
