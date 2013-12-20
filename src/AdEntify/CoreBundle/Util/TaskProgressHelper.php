<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 20/12/2013
 * Time: 12:03
 */

namespace AdEntify\CoreBundle\Util;


use AdEntify\CoreBundle\Entity\Task;

class TaskProgressHelper
{
    protected $totalSteps;
    protected $currentStep = 0;
    protected $task;
    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function start(Task $task, $steps)
    {
        $this->totalSteps = $steps;
        $this->task = $task;
    }

    public function advance($stepForward = 1)
    {
        $this->currentStep = $this->currentStep + $stepForward;
        $this->task->setProgress(round((100 * $this->currentStep) / $this->totalSteps));
        $this->em->merge($this->task);
        $this->em->flush();
    }
}