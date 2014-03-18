<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\VenueType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Venue;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class VenuesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Venue")
 */
class VenuesController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of all venues",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Venue')->findAll();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Venue",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View()
     *
     * @return Venue
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Venue')->find($id);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Venue",
     *  input="AdEntify\CoreBundle\Form\VenueType",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $venueRequest = $request->request->get('venue');

            // Check if existing foursquare id exist
            if ($request->request->has('venue')) {
                $venue = $em->getRepository('AdEntifyCoreBundle:Venue')->findOneBy(array(
                    'foursquareId' => $venueRequest['foursquareId']
                ));

                // Update venue products
                if ($venue && is_array($venueRequest) && array_key_exists('products', $venueRequest) && count($venueRequest['products']) > 0) {
                    $this->addProductsToVenue($venue, $venueRequest, $em);
                    $em->merge($venue);
                    $em->flush();
                }

                if ($venue) {
                    return $venue;
                }
            }
            $venue = new Venue();
            $form = $this->getForm($venue);
            $form->bind($request);
            if ($form->isValid()) {
                // Add venue products
                $this->addProductsToVenue($venue, $venueRequest, $em);
                if ($venue->getLink()) {
                    $shortUrl = $em->getRepository('AdEntifyCoreBundle:ShortUrl')->createShortUrl($venue->getLink());
                    if ($shortUrl)
                        $venue->setShortUrl($shortUrl)->setLink($this->generateUrl('redirect_url', array(
                            'id' => $shortUrl->getBase62Id()
                        )));
                }
                $em->persist($venue);
                $em->flush();

                return $venue;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $query
     * @param int $limit (max : 50)
     * @param null $ll latitude & longitude of current position
     * @param int $radius (in meters)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a venue with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @QueryParam(name="limit", default="30")
     * @QueryParam(name="ll", default="", description="Latitude and longitude of the user's location. Example : 44.3,37.2. If it's not specified, globally search.")
     * @QueryParam(name="radius", default="10000", description="Limit results to venues within this many meters of the specified location. Maximum 100,000 meters")
     * @View()
     */
    public function getSearchAction($query, $limit, $ll, $radius)
    {
        $url = 'https://api.foursquare.com/v2/venues/search';
        // If latitude/longitude given, search locally
        if (!empty($ll)) {
            $url .= '?intent=browse&radius=' . $radius . '&ll=' . $ll;
        } else {
            $url .= '?intent=global';
        }
        $url .= '&limit=' . $limit . '&query=' . urlencode($query) . '&client_id=' . $this->container->getParameter('foursquare.client_id')
            . '&client_secret=' . $this->container->getParameter('foursquare.client_secret') . '&v='.date('Ymd');

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response !== false) {
            $venues = array();
            $response = json_decode($response);
            if ($response->response->venues && count($response->response->venues) > 0) {
                foreach($response->response->venues as $val) {
                    $venue = new Venue();
                    $venue->setName($val->name)
                        ->setFoursquareId($val->id)
                        ->setLink(isset($val->url) ? $val->url : '')
                        ->setLat($val->location->lat)
                        ->setLng($val->location->lng);
                    if (isset($val->canonicalUrl))
                        $venue->setFoursquareShortLink($val->canonicalUrl);
                    if (isset($val->location->address))
                        $venue->setAddress($val->location->address);
                    if (isset($val->location->city))
                        $venue->setCity($val->location->city);
                    if (isset($val->location->postalCode))
                        $venue->setPostalCode($val->location->postalCode);
                    if (isset($val->location->state))
                        $venue->setState($val->location->state);
                    if (isset($val->location->cc))
                        $venue->setCc($val->location->cc);
                    if (isset($val->location->country))
                        $venue->setCountry($val->location->country);
                    $venues[] = $venue;
                }
            }
            return $venues;
        } else {
            throw new \Exception('Cannot search venues.');
        }
    }

    /**
     * Get form for Venue
     *
     * @param null $venue
     * @return mixed
     */
    protected function getForm($venue = null)
    {
        return $this->createForm(new VenueType(), $venue);
    }

    protected function addProductsToVenue($venue, $venueRequest, $em) {
        if ($venue && is_array($venueRequest) && array_key_exists('products', $venueRequest) && count($venueRequest['products']) > 0) {
            foreach ($venueRequest['products'] as $productId) {
                $found = count($venue->getProducts()) > 0;
                foreach ($venue->getProducts() as $product) {
                    if ($product->getId() == $productId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $product = $em->getRepository('AdEntifyCoreBundle:Product')->find($productId);
                    if ($product)
                        $venue->addProduct($product);
                }
            }
        }
    }
}