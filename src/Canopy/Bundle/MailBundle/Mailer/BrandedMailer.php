<?php

namespace Canopy\Bundle\MailBundle\Mailer;

use Symfony\Component\DependencyInjection\ContainerInterface;

class BrandedMailer
{
    /**
     * The container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Branding infos.
     *
     * @var array
     */
    private $brands;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $brands
     */
    public function __construct(ContainerInterface $container, array $brands)
    {
        $this->container = $container;
        $this->brands = $brands;
    }

    protected function getMailer()
    {
        return $this->container->get('canopy.mailer');
    }

    /**
     * Return brand details to make specific mail.
     *
     * @param $brand
     *
     * @return array
     */
    protected function getBrandData($brand)
    {
        if (!isset($this->brands[$brand])) {
            throw new \LogicException(sprintf('Unknown brand "%s"', $brand));
        }

        return $this->brands[$brand];
    }

    /**
     * Prepare a branded mail.
     *
     * @param $type
     * @param $brand
     * @param $recipient
     * @param array $params
     *
     * @return int
     */
    public function sendBrandedMail($type, $brand, $recipient, array $params)
    {
        $brandData = $this->getBrandData($brand);
        $params = array_merge($params, ['brand' => $brandData]);

        return $this->getMailer()->sendEmail(
            'CanopyMailBundle:Mail:'.$type.'.html.twig',
            $params,
            [$brandData['email']['sender']['email'] => $brandData['email']['sender']['name']],
            $recipient,
            [],
            $brandData['email']['bcc']
        );
    }
}
