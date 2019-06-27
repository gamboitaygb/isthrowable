<?php

namespace App\Controller;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Person;
use App\Entity\Profile;
use App\Entity\User;
use App\Form\ProfileType;
use App\Form\UpdateDataType;
use App\Form\UpdatePersonType;
use App\Form\UpdateUserType;
use Facebook\Facebook;
use InstagramAPI\Instagram;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Register;
use App\Form\RegisterType;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File;

class SecurityController extends Controller
{

    private  $loginRss;




    public function __construct()
    {
        $this->loginRss = new Utils\Loginrss();
    }


    /**
     * @Route("/{_locale}/login",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="user_login")
     */
    public function login(Request $request,AuthenticationUtils $authenticationUtils)
    {

        if($this->getUser()){
            return $this->redirectToRoute('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('front/_singin.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'google_login'=>$this->google()->createAuthUrl(),
            'facebook_login'=>$this->furlLogin(),
            'instagram_login'=>$this->instaUrlLoging(),
            'printerest_login'=>$this->loginRss->UrlPrinterestLogin(),
            'twitter_login'=>$this->twitterUrlLogin($this->twitter()),
        ));

    }

    /**
     * @Route("/{_locale}/logout",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="user_logout")
     */
    public function logout()
    {

    }


    /**
     * @Route("/{_locale}/signup",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="create_account")
     */
    public function createAccount(Request $request,UserPasswordEncoderInterface $encoder,TranslatorInterface $translator,\Swift_Mailer $mailer){
        if($this->getUser()){
            return $this->redirectToRoute('index');
        }
        $registro= new Register();
        $translator->trans('sign_up');


        $form = $this->createForm(RegisterType::class, $registro,array(
            'register'=>'user_register',
            'validation_groups' => array('default','register'),
        ));



        $form->handleRequest($request);
        if($form->isSubmitted()){
            if($request->get('isvalid')==''){
                $em = $this->getDoctrine()->getManager();

                $user=$form->getData()->getUser();
                $person=$form->getData()->getPerson();

                $person->setIpClient($request->getClientIp());

                $encoded = $encoder->encodePassword($user,$user->getPassword());
                $user->setPassword($encoded);
                $user->setPerson($person);

                $protocol = strcmp($_SERVER['REQUEST_SCHEME'],'https') === 0 ? 'https://' : 'http://';

                $token = bin2hex(openssl_random_pseudo_bytes(10));
                $person->setToken($token);
                $person->setUrlValidate($protocol.$_SERVER['SERVER_NAME'].'/token/'.$token);

                $redis=new Utils\Redis();
                $robj=$redis->redis(4);
                $robj->hmset($token,['id'=>$user->getId()]);
                $robj->expire($token,86400);
                $robj->disconnect();

                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

                $this->sendEmailConfirmation($user,$mailer);
                return $this->render('statics/thanks.html.twig',array(
                    'email'=>$user->getEmail(),
                ));
            }

        }

        return $this->render('front/_create_account.html.twig',array(
            'form'=>$form->createView(),
            'google_login'=>$this->google()->createAuthUrl(),
            'facebook_login'=>$this->furlLogin(),
            'instagram_login'=>$this->instaUrlLoging(),
            'printerest_login'=>$this->loginRss->UrlPrinterestLogin(),
            'twitter_login'=>$this->twitterUrlLogin($this->twitter()),
        ));

    }
    /**
    *@Route("/{_locale}/edit-profile",
    *requirements={"_locale"="en|es|fr|it|pt"},
    *Name="edit-profile")
    */
    public function editProfile(Request $request)
    {
        $user = $this->getUser();
        if ($user) {
            $user_profile = new Profile($user, $user->getPerson());

            $form = $this->createForm(ProfileType::class, $user_profile);


            $form->handleRequest($request);
            if ($form->isSubmitted()) {
               $user = $form->getData()->getUser();
                $person = $form->getData()->getPerson();
                // $person->setDescription($form->getData()->getDescription());


                if (null !== $user->getPasswordClear()) {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    $encoded = $encoder->encodePassword($user->getPasswordClear(), null);
                    $user->setPassword($encoded);
                    $user->setPerson($person);
                }



                $em = $this->getDoctrine()->getManager();
                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

            }
            return $this->render('front/user/_profile.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }




        /**
     * @Route("/{_locale}/oauth2callback/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="google-oauth2callback")
     */
    public function googleOauth2Callback(Request $request,UserPasswordEncoderInterface $encoder)
    {

        $client = $this->google();
        $client->fetchAccessTokenWithAuthCode($request->get('code'));
        $user_info = new \Google_Service_Oauth2($client);
        if($user_info->userinfo_v2_me->get()->email){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'email'=>$user_info->userinfo_v2_me->get()->email,
            ]);

            if(!is_object($user)){
                $user = new User();
                $user->setRoles("ROLE_USER");
                $user->setActive(1);
                $user->setEmail($user_info->userinfo_v2_me->get()->email);
                $user->setExpired(1);
                $person = new Person();

                $person->setToken(uniqid());
                $person->setUrlValidate($_SERVER['SERVER_NAME'].'/token/'.$person->getToken());
                $person->setCreatedDate(new \DateTime());
                $person->setActivatedDate(new \DateTime());

                $person->setIpClient($request->getClientIp());
                $person->setName($user_info->userinfo_v2_me->get()->name);
                $person->setCountry('ES');

                $encoded = $encoder->encodePassword($user,'isThrowable');
                $user->setPassword($encoded);
                $user->setPerson($person);

                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

            }

            $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));
            return $this->redirectToRoute('index');


        }
        return false;

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



    /**
     * @Route("/{_locale}/fb-callback/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="facebook-oauth2callback")
     */
    public function facebookCallback(Request $request,UserPasswordEncoderInterface $encoder)
    {

        $fb = $this->facebook();
        $helper = $this->fHelper($fb);

        $accessToken = $helper->getAccessToken();

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in

        $response = $fb->get('/me?fields=id,name,email,link,picture', $accessToken->getValue());

        $user_info = $response->getGraphUser();

        if($user_info->getEmail()){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'email'=>$user_info->getEmail(),
            ]);

            if(!is_object($user)){
                $user = new User();
                $user->setRoles("ROLE_USER");
                $user->setActive(1);
                $user->setEmail($user_info->getEmail());
                $user->setExpired(1);
                $person = new Person();

                $person->setToken(uniqid());
                $person->setUrlValidate($_SERVER['SERVER_NAME'].'/token/'.$person->getToken());
                $person->setCreatedDate(new \DateTime());
                $person->setActivatedDate(new \DateTime());

                $person->setIpClient($request->getClientIp());
                $person->setName($user_info->getName());
                $person->setCountry('ES');

                $encoded = $encoder->encodePassword($user,'isThrowable');
                $user->setPassword($encoded);
                $user->setPerson($person);

                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

            }

            $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));
            return $this->redirectToRoute('index');


        }
        return false;

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
    /**
     * @Route("/{_locale}/instagram-callback/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="instagram-oauth2callback")
     */
    public function loginInstagram(Request $request,UserPasswordEncoderInterface $encoder)
    {

        $provider = $this->instagram();
        if (empty($request->get('state')) || ($request->get('state') !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            //exit('Invalid state');
            exit('Ha correido un error');

        } else {

            // Try to get an access token (using the authorization code grant)

            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->get('code'),
            ]);

            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the user's details
                $user_info = $provider->getResourceOwner($token);
                $user_info = $user_info->toArray();


                // Use these details to create a new profile
                if($user_info['id']){
                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository(User::class)->findOneBy([
                        'email'=>$user_info['username'].'@isthrowable.com',
                    ]);


                    if(!is_object($user)){
                        $user = new User();
                        $user->setRoles("ROLE_USER");
                        $user->setActive(1);
                        $user->setEmail($user_info['username'].'@isthrowable.com');
                        $user->setExpired(1);
                        $person = new Person();

                        $person->setToken(uniqid());
                        $person->setUrlValidate($_SERVER['SERVER_NAME'].'/token/'.$person->getToken());
                        $person->setCreatedDate(new \DateTime());
                        $person->setActivatedDate(new \DateTime());

                        $person->setIpClient($request->getClientIp());
                        $person->setName($user_info['full_name']);
                        $person->setCountry('ES');

                        $encoded = $encoder->encodePassword($user,'isThrowable');
                        $user->setPassword($encoded);
                        $user->setPerson($person);

                        $em->persist($person);
                        $em->flush();

                        $em->persist($user);
                        $em->flush();

                        $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
                        $this->container->get('security.token_storage')->setToken($token);
                        $this->container->get('session')->set('_security_main', serialize($token));
                        unset($_SESSION['oauth2state']);
                        return $this->redirectToRoute('instagram_welcome',[],301);

                    }

                    $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                    $this->container->get('session')->set('_security_main', serialize($token));
                    unset($_SESSION['oauth2state']);
                    return $this->redirectToRoute('index',[],301);


                }
                return false;

                //echo $token->getToken();
                exit();

            } catch (Exception $e) {

                // Failed to get user details
                exit('Oh estamos triste pero no ha podido ser posible conectarte...');
            }

            // Use this to interact with an API on the users behalf
            echo $token->getToken();

        }
    }


    public function instaUrlLoging()
    {
        $insta = $this->instagram();

        $authUrl = $insta->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $insta->getState();

        return $authUrl;
    }

    /**
     * @Route("/{_locale}/instagram-logged/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="instagram_welcome")
     */

    public function instawelcome()
    {
        if($this->getUser()){
         return $this->render('media/_instagram_welcome.html.twig',array(
            ));
        }

        return $this->redirectToRoute('index',[],301);
    }


    /**
     * @Route("/{_locale}/tw-callback/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="tw-callback")
     */
    public function twitterCallback(Request $request,UserPasswordEncoderInterface $encoder)
    {

        $oauth_verifier = filter_input(INPUT_GET, 'oauth_verifier');

        if (empty($oauth_verifier) ||
            empty($_SESSION['oauth_token']) ||
            empty($_SESSION['oauth_token_secret'])
        ) {
            // something's missing, go and login again
            exit("esta vacio");
        }

        $tw = $this->twitter($_SESSION['oauth_token'],
            $_SESSION['oauth_token_secret']);

        $token = $tw->oauth(
            'oauth/access_token', [
                'oauth_verifier' => $oauth_verifier
            ]
        );

        $tw = $this->twitter($token['oauth_token'],
            $token['oauth_token_secret']);

        $user_info = $tw->get('account/verify_credentials');

        if($user_info->id){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'email'=>$user_info->id_str.'@isthrowable.com',
            ]);


            if(!is_object($user)){
                $user = new User();
                $user->setRoles("ROLE_USER");
                $user->setActive(1);
                $user->setEmail($user_info->id_str.'@isthrowable.com');
                $user->setExpired(1);
                $person = new Person();

                $person->setToken(uniqid());
                $person->setUrlValidate($_SERVER['SERVER_NAME'].'/token/'.$person->getToken());
                $person->setCreatedDate(new \DateTime());
                $person->setActivatedDate(new \DateTime());

                $person->setIpClient($request->getClientIp());
                $person->setName($user_info->name);
                $person->setCountry(strtoupper($user_info->lang));

                $encoded = $encoder->encodePassword($user,'isThrowable');
                $user->setPassword($encoded);
                $user->setPerson($person);

                $em->persist($person);
                $em->flush();

                $em->persist($user);
                $em->flush();

                $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                unset($_SESSION['oauth_token']);
                unset($_SESSION['oauth_token_secret']);
                return $this->redirectToRoute('twitter_welcome',[],301);

            }

            $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));
            unset($_SESSION['oauth_token']);
            unset($_SESSION['oauth_token_secret']);
            return $this->redirectToRoute('index',[],301);


        }


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
     * @Route("/{_locale}/twitter-logged/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="twitter_welcome")
     */

    public function twitterWelcome()
    {
        if($this->getUser()){
            return $this->render('media/_twitter_welcome.html.twig',array(
            ));
        }

        return $this->redirectToRoute('index',[],301);

    }


        /**
     * @Route("/{_locale}/recovery-password",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="recovery-password")
     */
    function recoveryPassword(Request $request,\Swift_Mailer $mailer)
    {
        //1-Generar hash en redis y enviar por correo link con hash
        $token = bin2hex(openssl_random_pseudo_bytes(10));
        //guardar ese token en redis
        $redis=new Utils\Redis();
        $robj=$redis->redis();
        $robj->hmset($token,['email'=>$request->get('email-pwd')]);
        $robj->expire($token,86400);
        $robj->disconnect();

        $message = (new \Swift_Message('Cambio de contraseña'))
            ->setFrom('info@isthrowable.com')
            ->setTo($request->get('email-pwd'))
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/_change_password.html.twig',
                    //array('name' => $name)
                    ['key'=>$token]
                ),
                'text/html'
            )
        ;

        $mailer->send($message);



        return $this->redirectToRoute('user_login',[],301);

    }

    /**
     * @Route("/{_locale}/change-password/{key}",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="key-password")
     */
    function changePassword(Request $request,$key,UserPasswordEncoderInterface $encoder)
    {
        $redis=new Utils\Redis();
        $robj=$redis->redis();

        $data = $robj->hmget($key,['email']);
        $robj->disconnect();

        if(count($data)){

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'email'=>$data[0],
            ]);

            if(is_object($user)){

                $form = $this->createForm(UpdateUserType::class, $user);


                $form->handleRequest($request);
                if ($form->isSubmitted()) {

                    $user = $form->getData();

                    if (null !== $user->getPasswordClear()) {

                        $encoded = $encoder->encodePassword($user,$user->getPasswordClear());
                        $user->setPassword($encoded);
                        $user->setPasswordClear('');
                      }


                    $em = $this->getDoctrine()->getManager();

                    $em->persist($user);
                    $em->flush();
                    $robj->del([$key]);
                    return $this->render('front/user/_change_password.html.twig', [
                        'success' =>true,
                    ]);

                }
                return $this->render('front/user/_change_password.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            //redirigir a la pagina de error
            return $this->render('front/user/_change_password.html.twig', [
                'success' =>false,
            ]);
        }

    }



    function sendEmailConfirmation(User $user,$mailer)
    {
        $redis=new Utils\Redis(5);
        $robj=$redis->redis();
        $robj->hmset($user->getPerson()->getToken(),['email'=>$user->getEmail()]);
        //$robj->expire($token,86400);
        $robj->disconnect();

        $message = (new \Swift_Message('Gracias por registrarte!!'))
            ->setFrom('info@isthrowable.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    //array('name' => $name)
                    [
                        'user'=>$user,
                    ]
                ),
                'text/html'
            )
        ;

        $mailer->send($message);



        return $this->redirectToRoute('user_login',[],301);

    }

    /**
     * @Route("/token/{key}",
     *     name="activate_url")
     */
    public function activateUrl($key)
    {
        $redis=new Utils\Redis(5);
        $robj=$redis->redis();

        if($robj->exists($key)){
            $data = $robj->hmget($key,['email']);
            $robj->disconnect();

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->findOneBy([
                'email'=>$data[0],
            ]);

            if(is_object($user)){
                $user->setActive(1);
                $em->persist($user);
                $em->flush();
                $robj->del([$key]);
                return $this->render('front/user/_active_account.html.twig', [
                    'success' =>true,
                    'key'=>true
                ]);

            }

            return $this->render('front/user/_active_account.html.twig', [
                'success' =>false,
                'key'=>true
            ]);

        }
        $robj->disconnect();
        return $this->render('front/user/_active_account.html.twig', [
            'success' =>false,
            'key'=>false
        ]);



    }


    /**
     * @Route("/{_locale}/p-callback",
     *     name="printerest_callback")
     */
    public function printerestCallback(Request $request,UserPasswordEncoderInterface $encoder)
    {
        if($request->get('code')){

            $pinterest=$this->loginRss->printerestClient();


            $token = $pinterest->auth->getOAuthToken($request->get('code'));
            $pinterest->auth->setOAuthToken((string)$token->access_token);

            $me = $pinterest->users->me(array(
                'fields' => 'id,username,first_name,last_name,image[small,large],url'
            ));

            $user_info = $me->toArray();

            if($user_info['id']){
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository(User::class)->findOneBy([
                    'email'=>$user_info['id'].'@isthrowable.com',
                ]);


                if(!is_object($user)){
                    $user = new User();
                    $user->setRoles("ROLE_USER");
                    $user->setActive(1);
                    $user->setEmail($user_info['id'].'@isthrowable.com');
                    $user->setExpired(1);
                    $person = new Person();

                    $person->setToken(uniqid());
                    $person->setUrlValidate($_SERVER['SERVER_NAME'].'/token/'.$person->getToken());
                    $person->setCreatedDate(new \DateTime());
                    $person->setActivatedDate(new \DateTime());

                    $person->setIpClient($request->getClientIp());
                    $person->setName($user_info['first_name']);
                    $person->setCountry(strtoupper('es'));

                    $encoded = $encoder->encodePassword($user,'isThrowable');
                    $user->setPassword($encoded);
                    $user->setPerson($person);

                    $em->persist($person);
                    $em->flush();

                    $em->persist($user);
                    $em->flush();

                    $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                    $this->container->get('session')->set('_security_main', serialize($token));
                    return $this->redirectToRoute('printerest_welcome',[],301);

                }

                $token = new UsernamePasswordToken($user,$user->getPassword(),'frontend',$user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('index',[],301);


            }
        }
    }


    /**
     * @Route("/{_locale}/printerest-logged/",
     *     requirements={"_locale"="en|es|fr|it|pt"},
     *     name="printerest_welcome")
     */

    public function printerestWelcome()
    {
        if($this->getUser()){
            return $this->render('media/_printerest_welcome.html.twig',array(
            ));
        }

        return $this->redirectToRoute('index',[],301);

    }




}
