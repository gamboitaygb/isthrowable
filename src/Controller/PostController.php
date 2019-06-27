<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\CommentsType;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PostController extends Controller
{
    /**
     * @Route("/{_locale}/post/post-create",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="post-create")
     */
    public function createPost(Request $request)
    {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Acces deny!');
            $form = $this->createForm(PostType::class, new \App\Entity\Post(),[
                'action'=>$this->generateUrl('post-create'),
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
                return $this->redirectToRoute('index');
            }
            return $this->render('front/post/_create_post.html.twig', [
                'form'=>$form->createView(),
            ]);




    }


    /**
     * @Route(
     *     "/{_locale}/p/{slug}",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     }
     *     ,name="show-post"
     * )
     */
    public function singlePost(Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $slug = str_replace('.html','',$slug);
        $single_post=$em->getRepository(Post::class)->findOneBySlug($slug);

        //get all post comments
        $comments=$em->getRepository(Post::class)->findByComments($single_post);


        //$more_visited=$em->getRepository('App:Post')->findByVisited(5);

        $form = $this->createForm(CommentsType::class, new \App\Entity\Comments(),[
            'action'=>$this->generateUrl('show-post',[
                'slug'=>$slug
            ]),
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted()){
            $comment=$form->getData();
            $comment->setDateAdd(new \DateTime());
            $comment->setPost($single_post);
            $comment->setIpAddress($request->getClientIp());
            $comment->setUser($this->getUser()->getPerson());

            if($comment->getAnswere()){
                $comment->setAnswere(1);
            }


            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirect($request->getRequestUri());
        }


        if($single_post){
            $single_post->setViews($single_post->getViews()+1);
            $em->flush($single_post);
        }

        return $this->render('front/post/_single_post.html.twig', [
            'single_post'=>$single_post,
            'create_comment'=>$form->createView(),
            'comments'=>$comments,
        ]);
    }


    /**
     * @Route(
     *     "/{_locale}/post/list",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     }
     *     ,name="post-list"
     * )
     */
    public function postList(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $list=$em->getRepository(Post::class)->findBy(
            [
                'author'=>$this->getUser()->getPerson(),
            ],
            [
                'id' => 'DESC'
            ]);
        return $this->render('front/post/_list_admin_post.html.twig', [
            'posts'=>$list,
        ]);

    }


    /**
     * @Route(
     *     "/{_locale}/post/{id}/edit",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     }
     *     ,name="edit-post"
     * )
     */
    public function editPost(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post=$em->getRepository(Post::class)->findOneBy([
            'id'=>$id
        ]);



        $form = $this->createForm(PostType::class, $post,[
            'actions'=>'',
            'validation_groups' => array('default', 'actions'),
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted()){
            $post=$form->getData();
            $post->subirFoto();


            $em->persist($post);
            $em->flush();
        }


        return $this->render('front/post/_create_post.html.twig', [
            'form'=>$form->createView(),
        ]);
    }

}
