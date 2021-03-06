<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\UserFilterType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * User controller.
 *
 * @Route("/user")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class UserController extends BaseController {
  public function init(Request $request) {
    parent::init($request);
    $this->breadcrumbs->addItem('user.labels.plural', $this->generateUrl('user'));
  }

  /**
   * Lists all User entities.
   *
   * @Route("/", name="user")
   * @Method("GET")
   * @Template()
   */
  public function indexAction(Request $request) {
    // initialize a query builder
    $filterBuilder = $this->get('doctrine.orm.entity_manager')
      ->getRepository('AppBundle:User')
      ->createQueryBuilder('u');

    $form = $this->get('form.factory')->create(new UserFilterType(), NULL, array(
      'action' => $this->generateUrl('user'),
      'method' => 'GET',
    ));

    if ($request->query->has($form->getName())) {
      $this->breadcrumbs->addItem('Søg', $this->generateUrl('user'));

      // manually bind values from the request
      $form->submit($request->query->get($form->getName()));

      // build the query from the given form object
      $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($form, $filterBuilder);
    }

    $query = $filterBuilder->getQuery();

    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
      $query,
      $request->query->get('page', 1),
      20
    );

    return $this->render('AppBundle:User:index.html.twig', array('pagination' => $pagination, 'form' => $form->createView()));
  }

  /**
   * Creates a new User entity.
   *
   * @Route("/", name="user_create")
   * @Method("POST")
   * @Template("AppBundle:User:new.html.twig")
   */
  public function createAction(Request $request) {
    $userManager = $this->get('fos_user.user_manager');

    $user = $userManager->createUser();
    $user->setEnabled(TRUE);

    $form = $this->createCreateForm($user);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $userManager->updateUser($user);

      $this->flash->success('user.confirmation.created');

      return $this->redirect($this->generateUrl('user'));
    }

    $this->reportErrors($form);

    return array(
      'entity' => $user,
      'edit_form' => $form->createView(),
    );
  }

  /**
   * Creates a form to create a User entity.
   *
   * @param User $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createCreateForm(User $entity) {
    $form = $this->createForm(new UserType(), $entity, array(
      'action' => $this->generateUrl('user_create'),
      'method' => 'POST',
    ));

    $this->addCreate($form, $this->generateUrl('user'));

    return $form;
  }

  /**
   * Displays a form to create a new User entity.
   *
   * @Route("/new", name="user_new")
   * @Method("GET")
   * @Template()
   */
  public function newAction() {
    $this->breadcrumbs->addItem('user.actions.create', $this->generateUrl('user_new'));

    $entity = new User();
    $form = $this->createCreateForm($entity);

    return array(
      'entity' => $entity,
      'edit_form' => $form->createView(),
    );
  }

  /**
   * Displays a form to edit an existing User entity.
   *
   * @Route("/{id}/edit", name="user_edit")
   * @Method("GET")
   * @Template()
   */
  public function editAction($id) {
    $this->breadcrumbs->addItem('user.actions.edit', $this->generateUrl('user_edit', array('id' => $id)));
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('AppBundle:User')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find User entity.');
    }

    $editForm = $this->createEditForm($entity);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
    );
  }

  /**
   * Creates a form to edit a User entity.
   *
   * @param User $entity The entity
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createEditForm(User $entity) {
    $form = $this->createForm(new UserType(), $entity, array(
      'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
      'method' => 'PUT',
    ));

    $this->addUpdate($form, $this->generateUrl('user'));

    return $form;
  }

  /**
   * Edits an existing User entity.
   *
   * @Route("/{id}", name="user_update")
   * @Method("PUT")
   * @Template("AppBundle:User:edit.html.twig")
   */
  public function updateAction(Request $request, $id) {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('AppBundle:User')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find User entity.');
    }

    $editForm = $this->createEditForm($entity);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $userManager = $this->get('fos_user.user_manager');
      $userManager->updatePassword($entity);
      $em->flush();

      $this->flash->success('user.confirmation.updated');

      return $this->redirect($this->generateUrl('user'));
    }

    $this->reportErrors($editForm);

    return array(
      'entity' => $entity,
      'edit_form' => $editForm->createView(),
    );
  }

  private function reportErrors($form) {
    foreach ($form->getErrors() as $error) {
      $this->flash->error($error->getMessage());
    }
  }

}
