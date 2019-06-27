<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\LikePost;
use App\Entity\Post;
use App\Entity\Question;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


/**
 * @Route("/{_locale}/async")
 */
class AjaxController extends Controller
{

    /**
     * @Route("/check_user/{email}")
     * @param Request $request
     * @return JsonResponse
     */
    public function isRegistered(Request $request)
    {
        if($request->isXmlHttpRequest()){

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));


            $em = $this->getDoctrine()->getManager();
            $user =  $em->getRepository('App:User')->findOneByEmail($request->get('email'));

            $id = is_object($user) ? $user->getId() : 0;

            return new JsonResponse(array(
                'response' => 'success',
                'id' => $serializer->serialize($id, 'json')
            ),200);

        }
    }


    /**
     * @Route("/p/like/",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     },
     *      name="count_like",methods={"GET"})
     */
    public function loeadLike(Request $request)
    {
        if($request->isXmlHttpRequest())
        {


            $em = $this->getDoctrine()->getManager();
            $post=$em->getRepository(Post::class)->findAll();
            $result=[];
            foreach ($post as $p){
                $total = $em->getRepository(LikePost::class)->findAllPostLike($p);
                $total = count($total);
                $likepost=0;
                if($this->getUser()){
                    $like = $em->getRepository(LikePost::class)->findByLikePostByUser($p,$this->getUser());
                    if(is_object($like)){
                        $likepost = $like->getLikep() ;
                    }
                }

                $result[]=[
                    'id'=>$p->getId(),
                    'total'=>$total,
                    'like'=>$likepost
                ];

            }

            return $this->json(['likes' => $result]);


        }

        return false;
    }



    /**
     * @Route("/p/{slug}/heart",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     },
     *      name="toggle_heart",methods={"GET"})
     */
    public function toggleArticleHeart(Request $request,$slug)
    {
        // TODO - actually heart/unheart the article!
        if($request->isXmlHttpRequest()){
            if($user=$this->getuser()){
                $em = $this->getDoctrine()->getManager();
                $post=$em->getRepository(Post::class)->findOneBySlug($slug);

                $like = $em->getRepository(LikePost::class)->findOneBy(
                    [
                        'post'=>$post,
                        'user'=>$user,
                    ]);



                if(is_object($like)){
                    $yes = $like->getLikep() ? false : true;
                    $like->setLikep($yes);
                }else{
                    $like = new LikePost();
                    $like->setPost($post);
                    $like->setUser($user);
                    $like->setLikep(true);
                }

                $em->persist($like);
                $em->flush();

                $total = $em->getRepository(LikePost::class)->findAllPostLike($post);
                $total = count($total);

                return $this->json(['hearts' => $total]);


            }

            return $this->json(['hearts' => rand(5, 100)]);

        }
    }



    /**
     * @Route("/p/comments/",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss|json",
     *     },
     *      name="load_post_comment",methods={"GET"})
     */
    public function loadPostcomment(Request $request)
    {
        // TODO - actually heart/unheart the article!
        if($request->isXmlHttpRequest()){
                $em = $this->getDoctrine()->getManager();
                $list=$em->getRepository(Post::class)->findByCountComment();
                return $this->json(['list' => $list]);
        }
        return false;
    }



    /**
     * @Route("/search.{_format}",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *          "_format": "json",
     *     },
     *      name="search")
     */
    public function search(Request $request)
    {
       // if($request->isXmlHttpRequest()){

            $encoder = new JsonEncoder();
            $normalizer = new ObjectNormalizer();

            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getId();
            });

            $serializer = new Serializer(array($normalizer), array($encoder));


            $em = $this->getDoctrine()->getManager();
            $post =  $em->getRepository('App:Post')->findAll();
            $questions =  $em->getRepository('App:Question')->findAll();





       // }
        return $this->json([
            'response' => 'success',
            'post' => $serializer->serialize($post, 'json'),
            //'questions' => $serializer->serialize($questions, 'json'),
        ]);
    }




    /**
     *@Route("/active/{type}-{id}/{action}/",
     *name="admin_active",methods={"POST"})
     */
    public function activeOption(Request $request,$type,$id,$action){


        if($request->isXmlHttpRequest()){
            $em = $this->getDoctrine()->getManager();


            $class = $action == 1 ? 'yes' : 'no';
            $text = $action == 1 ? 'Si' : 'No';

            switch ($type){
                case 'user':
                    $us = $em->getRepository(User::class)->findOneBy(array('id' => $id));
                    $us->setActive($action);
                    $em->flush();
                     return $this->json([
                        'success' => true,
                        'class'=>$class,
                        'text'=>$text,
                        'action'=>(int)$us->getActive(),
                    ]);
                    break;
                case 'post':
                    $post = $em->getRepository(Post::class)->findOneBy(array('id' => $id));
                    $post->setActive($action);
                    $em->flush();
                    return $this->json([
                        'success' => true,
                        'class'=>$class,
                        'text'=>$text,
                        'action'=>(int)$post->getActive(),
                    ]);
                    break;
                case 'new':
                    $post = $em->getRepository(Post::class)->findOneBy(array('id' => $id));
                    $post->setNew($action);
                    $em->flush();
                    return $this->json([
                        'success' => true,
                        'class'=>$class,
                        'text'=>$text,
                        'action'=>(int)$post->getNew(),
                    ]);
                    break;
            }

            return $this->json(['success' => false]);
        }

        return $this->json(['success' => false]);

    }





    /**
     *@Route("/deluser/",
     *name="delete_admin_user",methods={"POST"})
     */
    public function deleteUser(Request $request){


        if($request->isXmlHttpRequest()){
            $em = $this->getDoctrine()->getManager();
            $us = $em->getRepository(User::class)->findOneBy(array('id' => $request->get('id')));

            $em->remove($us);
            $em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false]);

    }


    /**
     *@Route("/delquestion/",
     *name="delete_admin_question",methods={"POST"})
     */
    public function deleteQuestion(Request $request){


        if($request->isXmlHttpRequest()){
            $em = $this->getDoctrine()->getManager();
            $us = $em->getRepository(Question::class)->findOneBy(array('id' => $request->get('id')));

            $em->remove($us);
            $em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['success' => false]);

    }


    /**
     * @Route("/load/info/",
     *      name="load_info",methods={"GET"})
     */
    public function loadAdminInfo(Request $request)
    {
        // TODO - actually heart/unheart the article!
        if($request->isXmlHttpRequest()){
            $em = $this->getDoctrine()->getManager();
            $users=$em->getRepository(User::class)->findAll();
            $posts=$em->getRepository(Post::class)->findAll();
            $questions=$em->getRepository(Question::class)->findAll();
            $comments=$em->getRepository(Comments::class)->findAll();
            return $this->json([
                'user' => count($users),
                'post' => count($posts),
                'question'=>count($questions),
                'comments'=>count($comments)
            ]);
        }
        return false;
    }
}
