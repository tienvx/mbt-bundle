<?php

namespace Tienvx\Bundle\MbtBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Form\TaskType;
use Tienvx\Bundle\MbtBundle\Repository\TaskRepository;
use Tienvx\Bundle\MbtBundle\Service\ModelContainer;

class TaskController extends Controller
{
    /**
     * Show tasks list
     *
     * @param integer $page The current page passed via URL
     *
     * @return Response
     */
    public function indexAction($page = 1): Response
    {
        /** @var TaskRepository $repo */
        $repo = $this->get('doctrine')->getRepository('MbtBundle:Task');
        $paginator = $repo->getAllTasks($page);
        $limit = 5;
        /** @var \ArrayIterator $tasks */
        $tasks = $paginator->getIterator();
        $total = $tasks->count();
        $maxPages = ceil($total / $limit);

        return $this->render('@TienvxMbt/task/index.html.twig', ['tasks' => $tasks, 'maxPages' => $maxPages, 'thisPage' => $page]);
    }

    public function createAction(Request $request)
    {
        /** @var $modelContainer ModelContainer */
        $modelContainer = $this->container->get('tienvx_mbt.model_container');
        $models = $modelContainer->getModelChoices();
        $algorithms = ['Random' => 'random(100,100)'];

        // create a task and give it some dummy data for this example
        $task = new Task();
        $task->setTitle('');
        $task->setModel(reset($models));
        $task->setAlgorithm('random(100,100)');

        $form = $this->createForm(new TaskType(), $task, ['models' => $models, 'algorithms' => $algorithms]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirect($this->generateUrl('tienvx_mbt_task_show', ['id' => $task->getId()]));
        }

        return $this->render('@TienvxMbt/task/create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updateAction(Request $request, Task $task)
    {
        /** @var $modelContainer ModelContainer */
        $modelContainer = $this->container->get('tienvx_mbt.model_container');
        $models = $modelContainer->getModelChoices();
        $algorithms = ['Random' => 'random(100,100)'];

        $form = $this->createForm(new TaskType(), $task, ['models' => $models, 'algorithms' => $algorithms]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirect($this->generateUrl('tienvx_mbt_task_show', ['id' => $task->getId()]));
        }

        return $this->render('@TienvxMbt/task/update.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function showAction(Task $task)
    {
        return $this->render('@TienvxMbt/task/show.html.twig', [
            'task' => $task,
        ]);
    }

    public function deleteAction(Request $request, Task $task)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tienvx_mbt_task_delete', ['id' => $task->getId()]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete',
                                            'attr' => array('class' => 'btn btn-danger btn-lg')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();
            return $this->redirectToRoute('tienvx_mbt_task_index');
        }

        return $this->render('@TienvxMbt/task/delete.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
