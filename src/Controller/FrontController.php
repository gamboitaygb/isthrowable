<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Question;
use App\Utils\Loginrss;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Register;
use App\Form\RegisterType;
use Facebook\Facebook;
use Abraham\TwitterOAuth\TwitterOAuth;

class FrontController extends Controller
{
    /**
     * @Route("/", name="init")
     */
    public function init(Request $request)
    {
        return $this->redirectToRoute('index',[],301);
    }
    /**
     * @Route("/{_locale}/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="index")
     */


    public function index()
    {


        $posts = $this->getDoctrine()->getRepository(Post::class)->findPostHome();
        $last_questions = $this->getDoctrine()->getRepository(Question::class)->findQuestions();

        $data=[
            'posts'=>$posts,
            'questions'=>$last_questions
        ];


        if(!$this->getUser()){
            $registro= new Register();

            $form = $this->createForm(RegisterType::class, $registro,array(
                'empty_data'=>'user_register',
                'validation_groups' => array('default', 'empty_data'),
            ));

           $data= array_merge($data,[
               'form'=>$form->createView(),
               'google_login'=>$this->google()->createAuthUrl(),
               'facebook_login'=>$this->furlLogin(),
               'instagram_login'=>$this->instaUrlLoging(),
               'printerest_login'=>$this->rss()->UrlPrinterestLogin(),
               'twitter_login'=>$this->twitterUrlLogin($this->twitter()),
               ]);
        }


        return $this->render('front/index.html.twig',$data );
    }

    public function rss()
    {
        $ress = new Loginrss();
        return $ress;
    }

    private function google()
    {
        $client = new \Google_Client();
        $client->setApplicationName('isThrowable');
        $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/../client_secret_299301554663-sb6elfgfou4seiuuqg9fbqcn1ntcr4p5.apps.googleusercontent.com.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(['profile','email']);
        return $client;
    }


    private function facebook()
    {

        $fb = new Facebook([
            'app_id' => '903228346536960', // Replace {app-id} with your app id
            'app_secret' => 'dedc78e383a215b5aabf6c1450559da5',
            'default_graph_version' => 'v3.1',
        ]);

        return $fb;
    }
    private function fHelper($fb)
    {
        $helper = $fb->getRedirectLoginHelper();
        return $helper;
    }

    private function furlLogin()
    {
        $fb = $this->facebook();
        $helper = $this->fHelper($fb);

        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('https://isthrowable.com/es/fb-callback/', $permissions);

        return $loginUrl;
    }

    private function instagram()
    {
        $insta = new \League\OAuth2\Client\Provider\Instagram([
            'clientId'          => 'c813e5d198e540c7b71f70bb00c7ac3c',
            'clientSecret'      => 'aa3534cbf5694b0fb1ee8601a22ed7eb',
            'redirectUri'       => 'https://isthrowable.com/es/instagram-callback/',
        ]);

        return $insta;
    }

    public function instaUrlLoging()
    {
        $insta = $this->instagram();

        $authUrl = $insta->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $insta->getState();

        return $authUrl;
    }


    private function twitter($oauth_token=null,$oauth_secret=null){
        $twitteroauth = new TwitterOAuth(
            'FUgEg4laxzJubIXWiXiSj8auE',
            'HAqB3CMXd6AfLJSUw0V0bn9mNNZsQXDdxlV0JtjKffWxwBq2Nm',
            $oauth_token,
            $oauth_secret);
        return $twitteroauth;
    }

    private function twitterUrlLogin($twitteroauth)
    {
        // request token of application
        $request_token = $twitteroauth->oauth(
            'oauth/request_token', [
                'oauth_callback' => 'https://isthrowable.com/es/tw-callback/'
            ]
        );

        // throw exception if something gone wrong
        if($twitteroauth->getLastHttpCode() != 200) {
            throw new \Exception('There was a problem performing this request');
        }

        // save token of application to session
        $_SESSION['oauth_token'] = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

        // generate the URL to make request to authorize our application
        $url = $twitteroauth->url(
            'oauth/authorize', [
                'oauth_token' => $request_token['oauth_token']
            ]
        );

        return $url;
    }


    /**
     * @Route("/{_locale}/contact/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="contact")
     */
    public function contactAction(Request $request,\Swift_Mailer $mailer){
        $defaultData = array('message' => '');
        $form = $this->createFormBuilder($defaultData)

            ->add('asunto', TextType::class,array(
                'attr'=> array('class' => 'form-control','placeholder'=>'Asunto'),
                'label'=>'Asunto *',
            ))

            ->add('message',TextareaType::class,array(
                'attr'=> array('class' => 'form-control','placeholder'=>'Contenido'),
                'label'=>'Cuéntanos tu historia',
            ))
            ->add('name', TextType::class,array(
                'attr'=> array('class' => 'form-control','placeholder'=>'Nombre Completo'),
                'label'=>'Nombre Completo *',
            ))
            ->add('email', EmailType::class,array(
                'attr'=> array('class' => 'form-control','placeholder'=>'Correco Electrónico'),
                'label'=>'Correo Electrónico *',
            ))

            ->add('Enviar',SubmitType::class,array(
                'attr'=> array('class' => 'btn btn-outline-info is-btn-default')))

            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $_POST['g-recaptcha-response']!='') {
            // data es un array con claves 'name', 'email', y 'message'
            $data = $form->getData();


            $message = (new \Swift_Message('Cambio de contraseña'))
                ->setSubject('Nuevo mensaje de '.$data['name'])
                ->setFrom($data['email'])
                ->setTo('info@isthrowable.com')
                ->setBody($data['message'], 'text/html'
                )
            ;
            $mailer->send($message);


            return $this->render('statics/contact.html.twig',[
                'form'=>$form->createView(),
                'sent'=>'Mensaje enviado correctamente, pronto nos pondremos en contacto',
            ]);

        }

        return $this->render('statics/contact.html.twig',[
            'form'=>$form->createView(),
        ]);


    }


}
