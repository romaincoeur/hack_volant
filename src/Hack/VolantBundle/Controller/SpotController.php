<?php

namespace Hack\VolantBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Hack\VolantBundle\Entity\Spot;
use Hack\VolantBundle\Form\SpotType;

/**
 * Spot controller.
 *
 */
class SpotController extends Controller
{

    /**
     * Lists all Spot entities.
     *
     */
    public function indexAction()
    {
        $units = 'si';
        $lang = 'fr';
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HackVolantBundle:Spot')->findAll();

        $forecastIO = $this->get('hack_volant.forecastio');

        foreach ($entities as $entity)
        {
            $currentConditions = $forecastIO->getCurrentConditions($entity->getLatitude(), $entity->getLongitude(), $units, $lang);
            $entity->setWindSpeed($currentConditions->getWindSpeed());
        }

        return $this->render('HackVolantBundle:Spot:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Spot entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Spot();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->updateCoordinates();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('spot_show', array('id' => $entity->getId())));
        }

        return $this->render('HackVolantBundle:Spot:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Spot entity.
     *
     * @param Spot $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Spot $entity)
    {
        $form = $this->createForm(new SpotType(), $entity, array(
            'action' => $this->generateUrl('spot_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Spot entity.
     *
     */
    public function newAction()
    {
        $entity = new Spot();
        $form   = $this->createCreateForm($entity);

        return $this->render('HackVolantBundle:Spot:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Spot entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HackVolantBundle:Spot')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spot entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('HackVolantBundle:Spot:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Spot entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HackVolantBundle:Spot')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spot entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('HackVolantBundle:Spot:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Spot entity.
    *
    * @param Spot $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Spot $entity)
    {
        $form = $this->createForm(new SpotType(), $entity, array(
            'action' => $this->generateUrl('spot_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Spot entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HackVolantBundle:Spot')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spot entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('spot_edit', array('id' => $id)));
        }

        return $this->render('HackVolantBundle:Spot:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Spot entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HackVolantBundle:Spot')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Spot entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('spot'));
    }

    /**
     * Creates a form to delete a Spot entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('spot_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
