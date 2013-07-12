<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 08/04/2013
 * Time: 19:50
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Security\Logout;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * Listener for the logout action
 *
 * This handler will clear the application's Facebook cookie.
 */
class FacebookHandler implements LogoutHandlerInterface
{
    private $facebook;
    private $securityContext;

    public function __construct(\BaseFacebook $facebook, $securityContext)
    {
        $this->facebook = $facebook;
        $this->securityContext = $securityContext;
    }

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $user = $this->securityContext->getToken()->getUser();
        $response->headers->clearCookie('fbsr_'.$user->getFacebookId());
    }
}
