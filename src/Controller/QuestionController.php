<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Form\CommentsType;
use App\Repository\QuestionRepository;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class QuestionController extends Controller
{
    /**
     * @Route("/{_locale}/q/", name="questions")
     */
    public function index()
    {
        return $this->render('question/index.html.twig', [
            'controller_name' => 'QuestionController',
        ]);
    }

    /**
     * @Route("/{_locale}/questions/list",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="question_list")
     */
    public function questionList(Request $request)
    {
        $list = $this->getDoctrine()->getRepository(Question::class)->findAllQuestions();
        $posts = $this->getDoctrine()->getRepository(Post::class)->findPostHome();
        return $this->render('front/questions/_question_user_list.html.twig', [
            'questions' => $list,
            'posts'=>$posts,
        ]);

    }


    /**
     * @Route("/{_locale}/q/mylist",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="question_user_list")
     */
    public function questionUserList(Request $request)
    {
        $list = $this->getDoctrine()->getRepository(Question::class)->findAllQuestionsByUser($this->getUser());


        $posts = $this->getDoctrine()->getRepository(Post::class)->findPostHome();
        return $this->render('front/questions/_question_user_list.html.twig', [
            'questions' => $list,
            'posts'=>$posts,
            'edit'=>true
        ]);

    }


    /**
     * @Route(
     *     "/{_locale}/q/{time}/{slug}",
     *     requirements={
     *         "_locale": "en|es|fr|it|pt",
     *         "_format": "html|rss",
     *     }
     *     ,name="show-question"
     * )
     */
    public function singleQuestion(Request $request, $slug,$time)
    {
        $em = $this->getDoctrine()->getManager();
        $slug = str_replace('.html','',$slug);
        $single_question=$em->getRepository(Question::class)->findOneBySlug($slug,$time);


        //get all post comments
        $comments=$em->getRepository(Question::class)->findComments($single_question);


        $form = $this->createForm(CommentsType::class, new \App\Entity\Comments(),[
            'action'=>$this->generateUrl('show-question',[
                'slug'=>$slug,
                'time'=>$time
            ]),
        ]);


        $form->handleRequest($request);
        if($form->isSubmitted()){
            $comment=$form->getData();
            $comment->setDateAdd(new \DateTime());
            $comment->setQuestion($single_question);
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

        return $this->render('front/questions/_single_question.html.twig', [
            'single_question'=>$single_question,
            'create_comment'=>$form->createView(),
            'comments'=>$comments,
        ]);
    }


    /**
     * @Route("/{_locale}/question/create",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="create_question")
     */
    public function createQuestion(Request $request)
    {
        $form = $this->createForm(QuestionType::class, new Question(),
            [
                'action'=>$this->generateUrl('create_question'),
            ]);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $question=$form->getData();
            $question->setUser($this->getuser());
            $question->setDateCreated(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('question_user_list');
        }

        return $this->render('front/questions/_create_question.html.twig', [
            'form'=>$form->createView(),
        ]);

    }


    /**
     * @Route("/{_locale}/question/edit/{id}",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="edit_question")
     */

    public function editQuestion(Request $request,$id)
    {

        $em = $this->getDoctrine()->getManager();
        $question = $em->getRepository(Question::class)->findOneBy([
            'id'=>$id,
            'user'=>$this->getUser()
        ]);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $question=$form->getData();
            $question->setUser($this->getuser());
            $question->setDateCreated(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('index');
        }

        return $this->render('front/questions/_create_question.html.twig', [
            'form'=>$form->createView(),
        ]);
    }
}
