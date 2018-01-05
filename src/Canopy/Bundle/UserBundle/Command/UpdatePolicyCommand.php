<?php

namespace Canopy\Bundle\UserBundle\Command;

use Canopy\Bundle\UserBundle\Entity\Organisation;
use Canopy\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePolicyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('canopy:policy:update')
            ->setDescription('Validate ignored TC for x days and update the user\'s status if there is new policies.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $services = $this->getContainer()->get('api.catalog.service')->getServicesForNotification();
        $itemsInfo = empty($services) ? [] : $this->getContainer()->get('api.ecommerce.cart')->getItemsInfoByServiceUuids($services);

        $notificationAlreadySent = [];

        foreach ($itemsInfo as $itemInfo) {
            $customerApprovers = $em->getRepository('CanopyUserBundle:User')->getOrganisationCustomerApproversByUserUuid($itemInfo['userUuid']);

            foreach ($customerApprovers as $user) {
                $key = $user->getUuid() . $itemInfo['productUuid'];

                if (in_array($key, $notificationAlreadySent)) {
                    continue;
                }

                $this->getContainer()->get('canopy.branded_mailer')->sendBrandedMail(
                    'product_policy_notification',
                    'canopy',
                    $user->getEmail(),
                    ['fullname' => $user->getFullname(), 'title' => $itemInfo['title']]
                );
                $output->writeln(
                    sprintf('T&C update notification for %s: email sent to %s', $itemInfo['title'], $user->getEmail())
                );

                $notificationAlreadySent[] = $key;
            }
        }

        $latestPolicy = $this->getContainer()->get('api.catalog.policy')->getLatestGeneralPolicy();

        $policyValidity = ($latestPolicy['valid_from']) ? new \DateTime($latestPolicy['valid_from']) : null;
        $today = new \DateTime();

        $todayDt = $today->format('Y-m-d');
        $policyValidityDt = ($policyValidity !== null) ? $policyValidity->format('Y-m-d') : null;

        if ($todayDt !== $policyValidityDt) {
            $output->writeln('No new policy today.');
        } else {

            $em->createQueryBuilder()
                ->update('CanopyUserBundle:User', 'u')
                ->set('u.answeredLatestPolicy', ':latest')
                ->set('u.policyChoice', ':choice')
                ->set('u.policyUuid', ':policyUuid')
                ->where('u.policyUuid != :policyUuid')
                ->setParameter('policyUuid', $latestPolicy['uuid'])
                ->setParameter('latest', false)
                ->setParameter('choice', User::POLICY_ACCEPTED)
                ->getQuery()
                ->execute();

            $em->createQueryBuilder()
                ->update('CanopyUserBundle:Organisation', 'o')
                ->set('o.answeredLatestPolicy', ':latest')
                ->set('o.policyChoice', ':choice')
                ->set('o.policyUuid', ':policyUuid')
                ->where('o.policyUuid != :policyUuid')
                ->setParameter('policyUuid', $latestPolicy['uuid'])
                ->setParameter('latest', false)
                ->setParameter('choice', Organisation::POLICY_ACCEPTED)
                ->getQuery()
                ->execute();

            $output->writeln('New policy today, users and organisations are updated.');

            // Code to send an email to all approver users from all organisation
            $approversEmail = $em->createQueryBuilder()
                ->select('u.email')
                ->from('CanopyUserBundle:User', 'u')
                ->join('u.organisation', 'o')
                ->where('u.roles LIKE :roles')
                ->andWhere('o.policyUuid = :policyUuid')
                ->setParameter('policyUuid', $latestPolicy['uuid'])
                ->andWhere('u.enabled = :enabled')
                ->andWhere('u.verified = :verified')
                ->andWhere('o.answeredLatestPolicy = :latest')
                ->orderBy('u.email', 'ASC')
                ->setParameter('roles', '%ROLE_CUSTOMER_APPROVER%')
                ->setParameter('verified', true)
                ->setParameter('enabled', true)
                ->setParameter('latest', false)
                ->getQuery()->getResult();

            $approversEmail = array_map(
                function ($sub) {
                    return $sub['email'];
                },
                $approversEmail
            );

            if (!empty($approversEmail)) {
                $this->getContainer()->get('canopy.branded_mailer')->sendBrandedMail(
                    'general_policy_applied',
                    'canopy',
                    $approversEmail,
                    []
                );
                $output->writeln('Email sent to '.implode(';', $approversEmail));
            } else {
                $output->writeln('Email not sent. No approvers emails available.');
            }
        }

        // Validate ignored TC for "tcAutoAcceptedDays" days
        $policyValidFrom = new \DateTime($latestPolicy['valid_from']);

        if ($policyValidFrom->diff(new \DateTime())->format('%a') >= $this->getContainer()->getParameter('policy_auto_accepted_days')) {
            $qb = $em->createQueryBuilder();

            $usersWhoDidntAnswerOrIgnorePolicyExpr = $qb->expr()->orX(
                $qb->expr()->isNull('u.policyUuid'),
                $qb->expr()->neq('u.policyUuid', ':uuid'),
                $qb->expr()->neq('u.answeredLatestPolicy', ':latest'),
                $qb->expr()->eq('u.policyChoice', User::POLICY_IGNORED)
            );

            $qb->update('CanopyUserBundle:User', 'u')
                ->set('u.answeredLatestPolicy', ':latest')
                ->set('u.policyUuid', ':uuid')
                ->set('u.policyChoice', ':choice')
                ->where($usersWhoDidntAnswerOrIgnorePolicyExpr)
                ->setParameter('latest', true)
                ->setParameter('uuid', $latestPolicy['uuid'])
                ->setParameter('choice', User::POLICY_ACCEPTED)
                ->getQuery()
                ->execute();

            $qb = $em->createQueryBuilder();
            $organisationWhoDidntAnswerOrIgnorePolicyExpr = $qb->expr()->orX(
                $qb->expr()->isNull('o.policyUuid'),
                $qb->expr()->neq('o.policyUuid', ':uuid'),
                $qb->expr()->neq('o.answeredLatestPolicy', ':latest'),
                $qb->expr()->eq('o.policyChoice', User::POLICY_IGNORED)
            );

            $qb->update('CanopyUserBundle:Organisation', 'o')
                ->set('o.answeredLatestPolicy', ':latest')
                ->set('o.policyUuid', ':uuid')
                ->set('o.policyChoice', ':choice')
                ->where($organisationWhoDidntAnswerOrIgnorePolicyExpr)
                ->setParameter('latest', true)
                ->setParameter('uuid', $latestPolicy['uuid'])
                ->setParameter('choice', Organisation::POLICY_ACCEPTED)
                ->getQuery()
                ->execute();

            $output->writeln('Ignored policy are now accepted, users and organisations are updated.');
        }
    }
}
