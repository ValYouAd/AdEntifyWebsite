<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 22/07/2013
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Command;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskWorkerCommand extends ContainerAwareCommand
{
    #region const
    const PARALLEL_TASKS = 5;
    #endregion

    #region fields

    protected $em;
    protected $uploadService;

    #endregion

    protected function configure ()
    {
        $this->setName('adentify:task:check')
            ->setDescription('Check if a task waiting to be done');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        // Get current tasks in progress or with error
        $currentTasks = $this->em->createQuery('SELECT COUNT(task.id) FROM AdEntify\CoreBundle\Entity\Task task
            WHERE (task.status = :status1 OR task.status = :status2) AND task.attempt <= 3')
            ->setParameters(array(
                'status1' => Task::STATUS_WAITING,
                'status2' => Task::STATUS_ERROR
            ))
            ->getSingleScalarResult();

        if ($currentTasks < self::PARALLEL_TASKS) {
            $tasks = $this->em->createQuery('SELECT task FROM AdEntify\CoreBundle\Entity\Task task
                WHERE (task.status = :status1 OR task.status = :status2) AND task.attempt <= 3')
                ->setParameters(array(
                    'status1' => Task::STATUS_WAITING,
                    'status2' => Task::STATUS_ERROR
                ))->setMaxResults(self::PARALLEL_TASKS - $currentTasks)
                ->getResult();

            if (count($tasks) > 0) {
                foreach ($tasks as $task) {
                    try {
                        // Update task status
                        $task->setStatus(Task::STATUS_INPROGRESS);
                        $this->em->merge($task);
                        $this->em->flush();
                        $output->writeln('Task status updated');

                        // Do job
                        $response = array();
                        switch ($task->getType()) {
                            case Task::TYPE_UPLOAD:
                                $error = false;
                                try {
                                    $response = $this->uploadService->uploadPhotos($task->getUser(), json_decode($task->getMessage()), $task);
                                } catch (\Exception $ex) {
                                    $error = $ex->getMessage();
                                }

                                if (array_key_exists('error', $response)) {
                                    $task->setErrorMessage($response['error']);
                                    $task->setAttempt($task->getAttempt() + 1);
                                    $task->setStatus(Task::STATUS_ERROR);
                                    $this->em->merge($task);
                                } else if ($error) {
                                    $task->setErrorMessage($error);
                                    $task->setAttempt($task->getAttempt() + 1);
                                    $task->setStatus(Task::STATUS_ERROR);
                                    $this->em->merge($task);
                                } else {
                                    $task->setErrorMessage(null);
                                }
                                break;
                        }

                        // Check if notification is required
                        if (!$task->getErrorMessage()) {
                            if ($task->getType() == Task::TYPE_UPLOAD) {
                                // UPLOAD PHOTO Action & notification
                                $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_UPLOAD,
                                    $task->getUser(), null, $response['photos'], Action::VISIBILITY_PUBLIC, null, null,
                                    $task->getNotifyCompleted(), 'photosUploaded', null, $task->getUser());
                            }
                            // Task completed, remove it
                            $this->em->remove($task);
                        } else {
                            $output->writeln('<error>'.$task->getErrorMessage().'</error>');
                        }
                    } catch(\Exception $ex) {
                        $task->setStatus(Task::STATUS_ERROR)->setAttempt($task->getAttempt() + 1)->setErrorMessage($ex->getMessage());
                        $this->em->merge($task);
                    }
                    $this->em->flush();
                }
            } else
                $output->writeln('No task waiting to be done.');
        } else {
            $output->writeln('There are already too many tasks in parallel');
        }
    }

    /**
     * Setup fields
     */
    private function setup()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->uploadService = $this->getContainer()->get('ad_entify_core.upload');
    }
}