<?php
/**
 * NovaeZMailingBundle Bundle.
 *
 * @package   Novactive\Bundle\eZMailingBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/eZMailingBundle/blob/master/LICENSE MIT Licence
 */
declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Menu;

use Doctrine\ORM\EntityManager;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Novactive\Bundle\eZMailingBundle\Entity\Campaign;
use Novactive\Bundle\eZMailingBundle\Entity\Mailing;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Builder.
 */
class Builder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return ItemInterface
     */
    public function createAdminMenu(RequestStack $requestStack): ItemInterface
    {
        $request      = $requestStack->getMasterRequest();
        $route        = null !== $request ? $request->attributes->get('_route') : null;
        $mailingRoute = 'novaezmailing_mailinglist';
        $userRoute    = 'novaezmailing_user';

        $menu  = $this->factory->createItem('root');
        $child = $menu->addChild(
            'mailinglists',
            ['route' => "{$mailingRoute}_index", 'label' => 'Mailing Lists']
        );

        if (substr($route, 0, \strlen($mailingRoute)) === $mailingRoute) {
            $child->setCurrent(true);
        }

        $child = $menu->addChild('users', ['route' => "{$userRoute}_index", 'label' => 'Users']);
        if (substr($route, 0, \strlen($userRoute)) === $userRoute) {
            $child->setCurrent(true);
        }

        return $menu;
    }

    /**
     * @param RequestStack $requestStack
     *
     * @return ItemInterface
     */
    public function createCampaignMenu(RequestStack $requestStack, EntityManager $entityManager): ItemInterface
    {
        $request = $requestStack->getMasterRequest();
        $route   = null !== $request ? $request->attributes->get('_route') : null;
        $menu    = $this->factory->createItem('root');
        $repo    = $entityManager->getRepository(Campaign::class);

        $campaigns = $repo->findAll();

        $mailingStatuses = Mailing::STATUSES;
        foreach ($campaigns as $campaign) {
            $child = $menu->addChild("camp_{$campaign->getId()}", ['label' => $campaign->getName()]);
            $child->addChild(
                "camp_{$campaign->getId()}_subsciptions",
                [
                    'route'           => 'novaezmailing_campaign_subscriptions',
                    'routeParameters' => ['campaign' => $campaign->getId()],
                    'label'           => 'Subscriptions',
                    'attributes'      => [
                        'class' => 'leaf subscriptions',
                    ],
                ]
            );

            foreach ($mailingStatuses as $statusId => $statusKey) {
                $child->addChild(
                    "mailing_status_{$statusId}",
                    [
                        'route'           => 'novaezmailing_campaign_mailings',
                        'routeParameters' => [
                            'campaign' => $campaign->getId(),
                            'status'   => $statusId,
                        ],
                        'label'           => $statusKey,
                        'attributes'      => [
                            'class' => "leaf {$statusKey}",
                        ],
                    ]
                );
            }
        }

        return $menu;
    }
}
