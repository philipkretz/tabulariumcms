<?php

namespace App\Controller;

use App\Entity\Friend;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Repository\UserBlockRepository;
use App\Repository\SiteSettingsRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FriendController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private FriendRepository $friendRepo,
        private UserRepository $userRepo,
        private UserBlockRepository $blockRepo,
        private SiteSettingsRepository $settingsRepo,
        private NotificationRepository $notificationRepo
    ) {}

    #[Route('/friends/requests', name: 'app_friends_requests', methods: ['GET'], priority: 1)]
    #[Route('/{_locale}/friends/requests', name: 'app_friends_requests_locale', methods: ['GET'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function requests(): Response
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            $this->addFlash('error', 'Friend system is currently disabled');
            return $this->redirectToRoute('app_profile');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Get pending friend requests
        $pendingRequests = $this->friendRepo->findPendingRequests($currentUser);

        return $this->render('friends/requests.html.twig', [
            'pendingRequests' => $pendingRequests
        ]);
    }

    #[Route('/friends/add/{userId}', name: 'app_friends_add', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/friends/add/{userId}', name: 'app_friends_add_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function add(int $userId): JsonResponse
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            return $this->json(['error' => 'Friend system is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $targetUser = $this->userRepo->find($userId);
        if (!$targetUser) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Can't send friend request to yourself
        if ($targetUser->getId() === $currentUser->getId()) {
            return $this->json(['error' => 'Cannot send friend request to yourself'], 400);
        }

        // Check if users are blocking each other
        if ($this->blockRepo->isBlocked($currentUser, $targetUser) ||
            $this->blockRepo->isBlocked($targetUser, $currentUser)) {
            return $this->json(['error' => 'Cannot send friend request to this user'], 403);
        }

        // Check if target user allows friend requests
        $targetProfile = $targetUser->getProfile();
        if ($targetProfile && !$targetProfile->isAllowFriendRequests()) {
            return $this->json(['error' => 'This user has disabled friend requests'], 403);
        }

        // Check if already friends
        if ($this->friendRepo->areFriends($currentUser, $targetUser)) {
            return $this->json(['error' => 'Already friends with this user'], 400);
        }

        // Check if friend request already exists
        $existingRequest = $this->friendRepo->createQueryBuilder('f')
            ->where('(f.user = :user AND f.friend = :friend) OR (f.user = :friend AND f.friend = :user)')
            ->andWhere('f.status = :pending')
            ->setParameter('user', $currentUser)
            ->setParameter('friend', $targetUser)
            ->setParameter('pending', Friend::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingRequest) {
            return $this->json(['error' => 'Friend request already pending'], 400);
        }

        // Create friend request
        $friendRequest = new Friend();
        $friendRequest->setUser($currentUser);
        $friendRequest->setFriend($targetUser);
        $friendRequest->setStatus(Friend::STATUS_PENDING);

        $this->em->persist($friendRequest);

        // Create notification for target user
        $notification = new Notification();
        $notification->setUser($targetUser);
        $notification->setType(Notification::TYPE_FRIEND_REQUEST);
        $notification->setRelatedUser($currentUser);
        $notification->setRelatedEntityId($friendRequest->getId());
        $notification->setMessage($currentUser->getUsername() . ' sent you a friend request');

        $this->em->persist($notification);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Friend request sent'
        ]);
    }

    #[Route('/friends/accept/{requestId}', name: 'app_friends_accept', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/friends/accept/{requestId}', name: 'app_friends_accept_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function accept(int $requestId): JsonResponse
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            return $this->json(['error' => 'Friend system is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $friendRequest = $this->friendRepo->find($requestId);
        if (!$friendRequest) {
            return $this->json(['error' => 'Friend request not found'], 404);
        }

        // Only the recipient can accept the request
        if ($friendRequest->getFriend()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Check if already accepted
        if ($friendRequest->getStatus() === Friend::STATUS_ACCEPTED) {
            return $this->json(['error' => 'Friend request already accepted'], 400);
        }

        // Accept the friend request
        $friendRequest->setStatus(Friend::STATUS_ACCEPTED);
        $friendRequest->setAcceptedAt(new \DateTimeImmutable());

        // Create notification for requester
        $notification = new Notification();
        $notification->setUser($friendRequest->getUser());
        $notification->setType(Notification::TYPE_FRIEND_ACCEPTED);
        $notification->setRelatedUser($currentUser);
        $notification->setRelatedEntityId($friendRequest->getId());
        $notification->setMessage($currentUser->getUsername() . ' accepted your friend request');

        $this->em->persist($notification);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Friend request accepted'
        ]);
    }

    #[Route('/friends/reject/{requestId}', name: 'app_friends_reject', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/friends/reject/{requestId}', name: 'app_friends_reject_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function reject(int $requestId): JsonResponse
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            return $this->json(['error' => 'Friend system is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $friendRequest = $this->friendRepo->find($requestId);
        if (!$friendRequest) {
            return $this->json(['error' => 'Friend request not found'], 404);
        }

        // Only the recipient can reject the request
        if ($friendRequest->getFriend()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Mark as rejected (or just delete it)
        $friendRequest->setStatus(Friend::STATUS_REJECTED);
        $this->em->flush();

        // Optionally, delete rejected requests after some time
        // For now, we just mark as rejected

        return $this->json([
            'success' => true,
            'message' => 'Friend request rejected'
        ]);
    }

    #[Route('/friends/remove/{friendshipId}', name: 'app_friends_remove', methods: ['DELETE', 'POST'], priority: 1)]
    #[Route('/{_locale}/friends/remove/{friendshipId}', name: 'app_friends_remove_locale', methods: ['DELETE', 'POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function remove(int $friendshipId): JsonResponse
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            return $this->json(['error' => 'Friend system is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $friendship = $this->friendRepo->find($friendshipId);
        if (!$friendship) {
            return $this->json(['error' => 'Friendship not found'], 404);
        }

        // Only involved users can remove the friendship
        if ($friendship->getUser()->getId() !== $currentUser->getId() &&
            $friendship->getFriend()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Remove the friendship
        $this->em->remove($friendship);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Friend removed'
        ]);
    }

    #[Route('/friends/cancel/{requestId}', name: 'app_friends_cancel', methods: ['DELETE', 'POST'], priority: 1)]
    #[Route('/{_locale}/friends/cancel/{requestId}', name: 'app_friends_cancel_locale', methods: ['DELETE', 'POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function cancel(int $requestId): JsonResponse
    {
        // Check if friend system is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isFriendSystemEnabled()) {
            return $this->json(['error' => 'Friend system is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $friendRequest = $this->friendRepo->find($requestId);
        if (!$friendRequest) {
            return $this->json(['error' => 'Friend request not found'], 404);
        }

        // Only the requester can cancel their own request
        if ($friendRequest->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // Only pending requests can be cancelled
        if ($friendRequest->getStatus() !== Friend::STATUS_PENDING) {
            return $this->json(['error' => 'Can only cancel pending requests'], 400);
        }

        // Remove the friend request
        $this->em->remove($friendRequest);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Friend request cancelled'
        ]);
    }
}
