<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\FriendRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/social')]
#[IsGranted('ROLE_USER')]
class SocialApiController extends AbstractController
{
    public function __construct(
        private MessageRepository $messageRepo,
        private NotificationRepository $notificationRepo,
        private FriendRepository $friendRepo
    ) {}

    /**
     * Get unread message count for AJAX polling
     */
    #[Route('/messages/unread-count', name: 'api_messages_unread_count', methods: ['GET'])]
    public function messagesUnreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $count = $this->messageRepo->countUnread($user);

        return $this->json([
            'count' => $count
        ]);
    }

    /**
     * Get unread notifications for AJAX polling
     */
    #[Route('/notifications/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function notificationsUnread(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $notifications = $this->notificationRepo->findUnreadByUser($user);
        $count = count($notifications);

        // Return simplified notification data
        $data = array_map(function($notification) {
            return [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'message' => $notification->getMessage(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                'relatedUserId' => $notification->getRelatedUser()?->getId(),
                'relatedUsername' => $notification->getRelatedUser()?->getUsername()
            ];
        }, $notifications);

        return $this->json([
            'count' => $count,
            'notifications' => array_slice($data, 0, 5) // Return max 5 recent
        ]);
    }

    /**
     * Get pending friend requests count for AJAX polling
     */
    #[Route('/friends/pending-count', name: 'api_friends_pending_count', methods: ['GET'])]
    public function friendsPendingCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $pendingRequests = $this->friendRepo->findPendingRequests($user);
        $count = count($pendingRequests);

        return $this->json([
            'count' => $count
        ]);
    }

    /**
     * Get all counts in one request (more efficient for polling)
     */
    #[Route('/counts', name: 'api_social_counts', methods: ['GET'])]
    public function allCounts(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'messages' => $this->messageRepo->countUnread($user),
            'notifications' => $this->notificationRepo->countUnread($user),
            'friendRequests' => count($this->friendRepo->findPendingRequests($user))
        ]);
    }

    /**
     * Check for new messages in a specific conversation
     */
    #[Route('/messages/conversation/{userId}/new', name: 'api_messages_conversation_new', methods: ['GET'])]
    public function conversationNewMessages(int $userId): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Get messages from the conversation that are unread
        $messages = $this->messageRepo->createQueryBuilder('m')
            ->where('m.sender = :otherUser')
            ->andWhere('m.receiver = :currentUser')
            ->andWhere('m.isRead = false')
            ->andWhere('m.receiverDeleted = false')
            ->setParameter('otherUser', $userId)
            ->setParameter('currentUser', $currentUser)
            ->orderBy('m.sentAt', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function($message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'username' => $message->getSender()->getUsername()
                ]
            ];
        }, $messages);

        return $this->json([
            'count' => count($messages),
            'messages' => $data
        ]);
    }
}
