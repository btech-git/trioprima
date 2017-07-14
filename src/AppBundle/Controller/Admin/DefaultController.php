<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Common\UserPassword;
use AppBundle\Entity\Admin\Staff;
use AppBundle\Form\Common\UserPasswordType;
use AppBundle\Form\Admin\StaffProfileType;

/**
 * @Route("/admin/default")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/login", name="admin_default_login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/default/login.html.twig', array(
            'lastUsername' => $lastUsername,
            'error' => $error,
        ));
    }
    
    /**
     * @Route("/login_check", name="admin_default_login_check")
     */
    public function loginCheckAction()
    {
    }
    
    /**
     * @Route("/logout", name="admin_default_logout")
     */
    public function logoutAction()
    {
    }
    
    /**
     * @Route("/profile_show/{id}", name="admin_default_profile_show")
     * @Method("GET")
     * @Security("has_role('ROLE_USER') and user.isEqualTo(staff)")
     */
    public function profileShowAction(Staff $staff)
    {
        return $this->render('admin/default/profile_show.html.twig', array(
            'user' => $staff,
        ));
    }
    
    /**
     * @Route("/profile_edit/{id}", name="admin_default_profile_edit")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER') and user.isEqualTo(staff)")
     */
    public function profileEditAction(Request $request, Staff $staff)
    {
        $form = $this->createForm(StaffProfileType::class, $staff);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Staff::class);
            $repository->update($staff);

            return $this->redirectToRoute('admin_default_profile_show', array('id' => $staff->getId()));
        }

        return $this->render('admin/default/profile_edit.html.twig', array(
            'user' => $staff,
            'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/password_change/{id}", name="admin_default_password_change")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_USER') and user.isEqualTo(staff)")
     */
    public function passwordChangeAction(Request $request, Staff $staff)
    {
        $userPassword = new UserPassword;
        $form = $this->createForm(UserPasswordType::class, $userPassword);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($staff, $userPassword->getNewPassword());
            $staff->setPassword($password);
            
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository(Staff::class);
            $repository->update($staff);

            return $this->redirectToRoute('admin_default_profile_show', array('id' => $staff->getId()));
        }

        return $this->render('admin/default/password_change.html.twig', array(
            'user' => $staff,
            'form' => $form->createView(),
        ));
    }
}
