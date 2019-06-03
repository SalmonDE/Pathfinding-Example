<?php
declare(strict_types = 1);

namespace SalmonDE\Pathfinding;

use pocketmine\world\World;
use pocketmine\math\Vector3;

class AStar {

    private $world;

    private $startNode;
    private $targetNode;

    private $openListHeap;
    private $openList = [];
    private $closedList = [];

    protected const G_COST = 1;

    public function __construct(World $world, Vector3 $startPos, Vector3 $targetPos){
        $this->level = $world;
        $this->startNode = Node::fromVector3($startPos);
        $this->targetNode = Node::fromVector3($targetPos);
        $this->targetNode->setH(0);

        $this->openListHeap = new NodeHeap();
    }

    public function calculateEstimatedCost(Vector3 $pos): float{
        return \abs($pos->x - $this->targetNode->x) + ($pos->y - $this->targetNode->y) + \abs($pos->z - $this->targetNode->z);
    }

    public function getStartNode(): Node{
        return $this->startNode;
    }

    public function getTargetNode(): Node{
        return $this->targetNode;
    }

    public function findPath(): ?Node{
        $this->startNode->setG(0.0);
        $this->startNode->setH($this->calculateEstimatedCost($this->startNode));
        $this->openList[World::blockHash($this->startNode->x, $this->startNode->y, $this->startNode->z)] = $this->startNode;
        $this->openListHeap->insert($this->startNode);

        $operations = 0;
        while(\true){
            if($operations++ >= 100000 || $this->openListHeap->isEmpty()){
                return \null;
            }

            $currentNode = $this->openListHeap->extract();
            unset($this->openList[$hash = World::blockHash($currentNode->x, $currentNode->y, $currentNode->z)]);
            $this->closedList[$hash] = $currentNode;

            if($currentNode->equals($this->targetNode)){
                $this->targetNode->setPredecessor($currentNode);
                return $this->targetNode;
            }

            $block = $this->world->getBlockAt($currentNode->x, $currentNode->y, $currentNode->z);

            foreach($block->getAllSides() as $neighbour){
                if($neighbour->isSolid() || $neighbour->y < 0 || $neighbour->y > 255 || isset($this->closedList[$neighbourHash = World::blockHash($neighbour->x, $neighbour->y, $neighbour->z)])){
                    continue;
                }

                $neighbour = $this->openList[$neighbourHash] ?? Node::fromVector3($neighbour);

                if(($notInList = !isset($this->openList[$neighbourHash])) || $currentNode->getG() + self::G_COST < $neighbour->getG()){
                    $neighbour->setG($currentNode->getG() + self::G_COST);
                    $neighbour->setH($this->calculateEstimatedCost($neighbour));
                    $neighbour->setPredecessor($currentNode);

                    if($notInList){
                        $this->openList[$neighbourHash] = $neighbour;
                        $this->openListHeap->insert($neighbour);
                    }
                }
            }
        }
    }
}
