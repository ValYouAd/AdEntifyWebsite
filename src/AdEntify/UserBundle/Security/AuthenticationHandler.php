<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 13/12/2013
 * Time: 16:48
 */

namespace AdEntify\UserBundle\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationHandler
    implements AuthenticationSuccessHandlerInterface,
    AuthenticationFailureHandlerInterface
{
    protected $router;
    protected $security;
    protected $userManager;
    protected $service_container;

    public function __construct(RouterInterface $router, SecurityContext $security, $userManager, $service_container)
    {
        $this->router = $router;
        $this->security = $security;
        $this->userManager = $userManager;
        $this->service_container = $service_container;

    }
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        if ($request->isXmlHttpRequest()) {
            $result = array('success' => true);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else {
            // Create a flash message with the authentication error message
            $url = $this->router->generate('fos_user_security_login');

            return new RedirectResponse($url);
        }

        return new RedirectResponse($this->router->generate('loggedInHome'));
    }
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {

        if ($request->isXmlHttpRequest()) {
            $result = array('success' => false, 'message' => $exception->getMessage());
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        return new Response();
    }
}