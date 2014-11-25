<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 13/10/2014
 * Time: 15:07
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\OAuth\Client;
use AdEntify\CoreBundle\Form\ClientType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Brand;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class ClientsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Client")
 */
class ClientsController extends FosRestController {

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Client",
     *  input="AdEntify\CoreBundle\Form\ClientType",
     *  output="AdEntify\CoreBundle\Entity\OAuth\Client",
     *  section="Client"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
	$formClient = new Client();
	$form = $this->getForm($formClient);
	$form->bind($request);
	if ($form->isValid()) {
	    $client = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:OAuth\Client')->findOneBy(array('name' => $formClient->getName()));
	    if (!$client) {
		$clientManager = $this->get('fos_oauth_server.client_manager.default');
		$client = $clientManager->createClient();
		$client->setName($formClient->getName());
		$client->setDisplayName($formClient->getDisplayName());
		$client->setRedirectUris($formClient->getRedirectUris());
		$client->setAllowedGrantTypes(array('token', 'authorization_code', 'password', 'http://grants.api.adentify.com/facebook_access_token', 'http://grants.api.adentify.com/twitter_access_token'));
		$clientManager->updateClient($client);
	    }

	    return array(
		'id' => $client->getPublicId(),
		'secret' => $client->getSecret()
	    );
	} else {
	    return $form;
	}
    }

    /**
     * Get form for Client
     *
     * @param null $client
     * @return mixed
     */
    protected function getForm($client = null)
    {
	return $this->createForm(new ClientType(), $client);
    }
}