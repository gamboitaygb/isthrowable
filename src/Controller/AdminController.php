<?php

namespace App\Controller;

use App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\User;
use App\Entity\Profile;
use App\Form\ProfileType;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_login")
     */
    public function index()
    {



        if($this->getUser()){
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Sorry this page is not for you!');
            return $this->redirectToRoute('index');
        }

        $authUtils = $this->get('security.authentication_utils');
        return $this->render('admin/index.html.twig', array(
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ));
    }



    /**
     * @Route("/logout", name="admin_logout")
     */
    public function logout()
    {

    }

    /**
     * @Route("/home/",name="admin_home")
     */
    public function homepage(Request $request)
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('admin_login');
        }

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Sorry this page is not for you!');

        return $this->render('admin/home.html.twig', array(
            'name' => $this->getUser()->getPerson()->getName(),
        ));
    }

    /**
     * @Route("/post/create",name="admin_create_post")
     */
    public function createPost(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Acces deny!');
        $form = $this->createForm(PostType::class, new \App\Entity\Post(),[
            'action'=>$this->generateUrl('admin_create_post'),
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted()){
            $post=$form->getData();
            $post->setDateCreated(new \DateTime());
            $post->setDateUpd(new \DateTime());
            $post->setAuthor($this->getUser()->getPerson());
            $post->setViews(0);
            $post->setLike(0);

            /*if($post->getPhotoPath()==null){
                $post->setPicture('anonymous.png');
            }*/
            $post->subirFoto();

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('list_post');
        }
        return $this->render('admin/post/_create_post.html.twig', [
            'form'=>$form->createView(),
        ]);
    }


    /**
     * @Route("/post/list", name="list_post" )
     */
    public  function postList()
    {
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('App:Post')->findAll();

        return $this->render('admin/post/_list.html.twig',[
            'list'=>$list,
        ]);

    }

    /**
     * @Route("/comments/list", name="list_comments" )
     */
    public function commentsList()
    {
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('App:Comments')->findAll();

        return $this->render('admin/post/_comments.html.twig',[
            'list'=>$list,
        ]);

    }


    /**
     * @Route("/user/list/",name="user_list")
     */
    public function userList()
    {
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('App:User')->findBy(
            [],
            ['id'=>'desc']
        );

        return $this->render('admin/users/_user_list.html.twig',[
            'list'=>$list,
        ]);
    }

    /**
     *@Route("/user/edit-profile/{id}",
     *name="edit_user")
     */
    public function editUser(Request $request,$id,UserPasswordEncoderInterface $encoder)
    {


        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(array('id' => $id));

        $user_profile = new Profile($user, $user->getPerson());

            $form = $this->createForm(ProfileType::class, $user_profile);


            $form->handleRequest($request);
            if ($form->isSubmitted()) {

                $user = $form->getData()->getUser();
                $person = $form->getData()->getPerson();


                if(!in_array($user->getRoles(),$user->getRoles())){
                    $user->setRoles($request->get('rol_user'));
                }


                if (null !== $user->getPasswordClear()) {

                    $encoded = $encoder->encodePassword($user,$user->getPasswordClear());
                    $user->setPassword($encoded);
                    $user->setPasswordClear('');
                }



                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('user_list',[],302);

            }
            return $this->render('admin/users/_edit_user.html.twig', [
                'form' => $form->createView(),
            ]);


    }

    /**
     *@Route("/user/delete/{id}",
     *name="delete_user")
     */
    public function deleteUser(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(array('id' => $id));
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('user_list');

    }

    /**
     *@Route("/question/list/",
     *name="admin_question_list")
     */
    public function questionList()
    {
        $em = $this->getDoctrine()->getManager();
        $list = $em->getRepository('App:Question')->findAll();

        return $this->render('admin/question/_list_question.html.twig',[
            'list'=>$list,
        ]);
    }



    /*public function user(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));


            $response = new JsonResponse(array(
                'user' => $serializer->serialize($this->getUser(), 'json')
            ),200);
            return $response;

        }
    }*/

}
