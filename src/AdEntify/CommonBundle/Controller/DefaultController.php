<?php

namespace AdEntify\CommonBundle\Controller;

use AdEntify\CommonBundle\Models\Contact;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\CommonTools;
use AdEntify\CoreBundle\Util\FileTools;
use Doctrine\Tests\Common\Annotations\False;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\Query\ResultSetMapping;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="root_url")
     */
    public function indexNoLocaleAction()
    {
        return $this->redirect($this->generateUrl('home_logoff', array(
            '_locale' => $this->getCurrentLocale()
        )));
    }

    /**
     * @Route("{_locale}/", defaults={"_locale" = "en"}, requirements={"_locale" = "en|fr"}, name="home_logoff")
     * @Template
     */
    public function indexAction() {
        // Automatic redirect
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect($this->generateUrl('loggedInHome', array(
                '_locale' => $this->getCurrentLocale()
            )));
        }

        $em = $this->getDoctrine()->getManager();

        $tagsCount = $em->createQuery('SELECT COUNT(tag.id) FROM AdEntify\CoreBundle\Entity\Tag tag')->getSingleScalarResult();
        $users = $em->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user WHERE user.facebookId IS NOT NULL ORDER BY user.followersCount DESC')
            ->setMaxResults(6)->getResult();
        $brands = $em->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand WHERE brand.validated = 1 AND brand.smallLogoUrl IS NOT NULL ORDER BY brand.tagsCount DESC')
            ->setMaxResults(12)->getResult();
        $photos = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
            WHERE photo.visibilityScope = :visibilityScope AND photo.deletedAt IS NULL AND photo.status = :status AND photo.tagsCount > 0
            ORDER BY photo.likesCount DESC')
            ->setParameters(array(
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':status' => Photo::STATUS_READY,
            ))
            ->setMaxResults(9)->getResult();
        $hashtags = $em->createQuery('SELECT h FROM AdEntify\CoreBundle\Entity\Hashtag h ORDER BY h.usedCount DESC')->setMaxResults(36)->getResult();

        return array(
            'tagsCount' => str_split($tagsCount),
            'users' => $users,
            'brands' => $brands,
            'photos' => $photos,
            'hashtags' => $hashtags
        );
    }

    /**
     * @Route("/{_locale}/app/{slug}", defaults={"_locale" = "en"}, requirements={"_locale" = "en|fr","slug" = "(.+)"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")

     */
    public function appAllAction($slug)
    {
        $categories = $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category ORDER BY category.displayOrder")
            ->useQueryCache(false)
            ->useResultCache(true, null, 'categories'.$this->getRequest()->getLocale())
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Route("/{_locale}/app/", name="loggedInHome", defaults={"_locale" = "en"}, requirements={"_locale" = "en|fr"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")

     */
    public function appIndexAction()
    {
        $categories = $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category ORDER BY category.displayOrder")
            ->useQueryCache(false)
            ->useResultCache(true, null, 'categories'.$this->getRequest()->getLocale())
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Route("/app/")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     */
    public function appNoLocaleAction()
    {
        return $this->redirect($this->generateUrl('loggedInHome', array(
            '_locale' => $this->getCurrentLocale()
        )));
    }

    /**
     * @Route("/{_locale}/app/instagram/photos/", name="instagram_photos")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function instagramPhotosAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/app/flickr/sets/", name="flickr_sets")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function flickrSetsAction()
    {
        return array();
    }

    /**
     * @Route("/lang/{locale}", requirements={"locale" = "en|fr"}, name="change_lang")
     */
    public function langAction($locale)
    {
        $this->getRequest()->getSession()->set('_locale', $locale);
        $this->setUserLocale($locale);
        return $this->redirect($this->generateUrl('loggedInHome', array(
            '_locale' => $locale
        )));
    }

    /**
     * @Route("/r/{id}", name="redirect_url")
     */
    public function redirectAction($id)
    {
        $shortUrl = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:ShortUrl')
            ->findByBase62IdAndUpdateCounter($id);
        if ($shortUrl !== false) {
            return $this->redirect($shortUrl->getUrl(), 301);
        } else {
            throw new NotFoundHttpException('Redirect url not found');
        }
    }

    /**
     * @Route("/{_locale}/how-it-works", name="about")
     * @Template()
     */
    public function aboutAction()
    {
        $categories = $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category ORDER BY category.name")
            ->useQueryCache(false)
            ->useResultCache(true, null, 'categories'.$this->getRequest()->getLocale())
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Route("/{_locale}/who-we-are", name="press")
     * @Template()
     */
    public function whoWeAreAction()
    {
        $em = $this->getDoctrine()->getManager();
        $photos = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
            WHERE photo.visibilityScope = :visibilityScope AND photo.deletedAt IS NULL AND photo.status = :status AND photo.tagsCount > 0
            ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':status' => Photo::STATUS_READY,
            ))
            ->setMaxResults(9)->getResult();

        return array(
            'photos' => $photos
        );
    }

    /**
     * @Route("/{_locale}/jobs", name="jobs")
     * @Template()
     */
    public function jobsAction()
    {
        return array(
        );
    }

    /**
     * @Route("/{_locale}/contact", name="contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $contact = new Contact();

        $form = $this->createFormBuilder($contact)
            ->add('email', 'email', array(
                'label' => 'contact.email',
                'attr' => array(
                    'placeholder' => ''
                )
            ))
            ->add('name', 'text', array(
                'label' => 'contact.name',
                'attr' => array(
                    'placeholder' => ''
                )
            ))
            ->add('message', 'textarea', array(
                'label' => 'contact.message',
                'attr' => array(
                    'placeholder' => '',
                    'rows' => 6
                )
            ))
            ->add('send', 'submit', array(
                'attr' => array(
                    'class' => 'btn-around-corner btn-red-grey-border'
                ),
                'label' => 'contact.send'
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $message = \Swift_Message::newInstance()
                ->setFrom($this->container->getParameter('mailer_user'))
                ->setSubject('Contact')
                ->setTo($this->container->getParameter('contact_email'))
                ->setBody($this->renderView('AdEntifyCommonBundle:Default:contact-email.html.twig', array(
                    'contact' => $contact
                )))
            ;
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'contact.sent'
            );

            return $this->redirect($this->generateUrl('contact'));
        } else {
            $categories = $this->getDoctrine()->getManager()
                ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category")
                ->useQueryCache(false)
                ->useResultCache(true, null, 'categories'.$this->getRequest()->getLocale())
                ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
                ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
                ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
                ->getResult();
            return array(
                'categories' => $categories,
                'form' => $form->createView()
            );
        }
    }

    /**
     * @Route("/{_locale}/legal", name="legal")
     * @Template()
     */
    public function legalAction()
    {
        $em = $this->getDoctrine()->getManager();
        $parametersCGU = array(
            'locale' => $this->container->get('request')->getlocale()
        );

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Adentify\CoreBundle\Entity\Legal', 'Legal');
        $rsm->addScalarResult('terms_of_use', 'terms_of_use', 'string');
        $rsm->addScalarResult('privacy', 'privacy', 'string');
        $rsm->addScalarResult('legal_notices', 'legal_notices', 'string');

        $sql = 'SELECT terms_of_use, privacy, legal_notices FROM legal WHERE language = :locale';
        $cgu = $em->createNativeQuery($sql, $rsm)->setParameters($parametersCGU)->getResult();

        return array(
            'terms_of_use' => $cgu[0]['terms_of_use'],
            'privacy' => $cgu[0]['privacy'],
            'legal_notices' => $cgu[0]['legal_notices'],
        );
    }

    /**
     * Get current locale from user if logged and set, instead, get from request
     *
     * @return string
     */
    private function getCurrentLocale() {
        $locale = $this->getRequest()->getLocale();
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getLocale()) {
                $locale = $user->getLocale();
            }
        }

        return $locale;
    }

    /**
     * @Route("/post-test")
     * @Method({"POST"})
     */
    public function testAction()
    {
        $uploadedFile = $_FILES['file'];
        $user = $this->container->get('security.context')->getToken()->getUser();
        $path = FileTools::getUserPhotosPath($user);
        $filename = uniqid().$uploadedFile['name'];
        $file = $this->getRequest()->files->get('file');

        $url = $this->container->get('adentify_storage.file_manager')->upload($file, $path, $filename);
        if ($url) {
            $photo = new Photo();
            $thumb = new Thumb();
            $thumb->setOriginalPath($url);
            $thumb->configure($photo);
            $thumbs = $this->container->get('ad_entify_core.thumb')->generateUserPhotoThumb($thumb, $user, $filename);

            // Add original
            $originalImageSize = getimagesize($url);
            $thumbs['original'] = array(
                'filename' => $url,
                'width' => $originalImageSize[0],
                'height' => $originalImageSize[1],
            );

            $photo->fillThumbs($thumbs);
        } else {
            throw new HttpException(500, 'Can\'t upload photo.');
        }
    }

    /**
     * Set locale for the current user if logged
     *
     * @param $locale
     */
    private function setUserLocale($locale) {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $user->setLocale($locale);
            $this->getDoctrine()->getManager()->merge($user);
            $this->getDoctrine()->getManager()->flush();
        }
    }
}
