<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\MessageRepository;
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
class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageRepository $messageRepo,
        private UserRepository $userRepo,
        private UserBlockRepository $blockRepo,
        private SiteSettingsRepository $settingsRepo,
        private NotificationRepository $notificationRepo
    ) {}

    #[Route('/messages', name: 'app_messages_inbox', methods: ['GET'], priority: 1)]
    #[Route('/{_locale}/messages', name: 'app_messages_inbox_locale', methods: ['GET'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function inbox(): Response
    {
        // Check if messaging is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isMessagingEnabled()) {
            $this->addFlash('error', 'Messaging is currently disabled');
            return $this->redirectToRoute('app_profile');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Get all conversations (grouped by conversation partner)
        $messages = $this->messageRepo->findUserInbox($currentUser);

        // Group messages by conversation partner
        $conversations = [];
        $conversationPartners = [];

        foreach ($messages as $message) {
            $partner = $message->getSender()->getId() === $currentUser->getId()
                ? $message->getReceiver()
                : $message->getSender();

            $partnerId = $partner->getId();

            if (!isset($conversationPartners[$partnerId])) {
                $conversationPartners[$partnerId] = $partner;
                $conversations[$partnerId] = [
                    'partner' => $partner,
                    'lastMessage' => $message,
                    'unreadCount' => 0
                ];
            }

            // Count unread messages from this partner
            if (!$message->isRead() && $message->getReceiver()->getId() === $currentUser->getId()) {
                $conversations[$partnerId]['unreadCount']++;
            }
        }

        // Sort conversations by last message time
        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for sorting
        uasort($conversations, fn($a, $b) =>
            $b['lastMessage']->getSentAt() <=> $a['lastMessage']->getSentAt()
        );

        return $this->render('messages/inbox.html.twig', [
            'conversations' => $conversations,
            'unreadCount' => $this->messageRepo->countUnread($currentUser)
        ]);
    }

    #[Route('/messages/conversation/{userId}', name: 'app_messages_conversation', methods: ['GET'], priority: 1)]
    #[Route('/{_locale}/messages/conversation/{userId}', name: 'app_messages_conversation_locale', methods: ['GET'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function conversation(int $userId): Response
    {
        // Check if messaging is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isMessagingEnabled()) {
            $this->addFlash('error', 'Messaging is currently disabled');
            return $this->redirectToRoute('app_profile');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $otherUser = $this->userRepo->find($userId);
        if (!$otherUser) {
            throw $this->createNotFoundException('User not found');
        }

        // Check if users are blocking each other
        if ($this->blockRepo->isBlocked($currentUser, $otherUser) ||
            $this->blockRepo->isBlocked($otherUser, $currentUser)) {
            $this->addFlash('error', 'Cannot message this user');
            return $this->redirectToRoute('app_messages_inbox');
        }

        // Check if other user allows messages
        $otherProfile = $otherUser->getProfile();
        if ($otherProfile && !$otherProfile->isAllowMessages()) {
            $this->addFlash('error', 'This user has disabled messages');
            return $this->redirectToRoute('app_messages_inbox');
        }

        // Get conversation messages
        $messages = $this->messageRepo->findConversation($currentUser, $otherUser);

        // Mark received messages as read
        foreach ($messages as $message) {
            if ($message->getReceiver()->getId() === $currentUser->getId() && !$message->isRead()) {
                $message->setIsRead(true);
                $message->setReadAt(new \DateTimeImmutable());
            }
        }
        $this->em->flush();

        // Reverse order for display (oldest first)
        $messages = array_reverse($messages);

        return $this->render('messages/conversation.html.twig', [
            'messages' => $messages,
            'otherUser' => $otherUser,
            'currentUser' => $currentUser
        ]);
    }

    #[Route('/messages/send', name: 'app_messages_send', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/messages/send', name: 'app_messages_send_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function send(Request $request): JsonResponse
    {
        // Check if messaging is enabled
        $settings = $this->settingsRepo->getSettings();
        if (!$settings->isMessagingEnabled()) {
            return $this->json(['error' => 'Messaging is currently disabled'], 403);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $receiverId = $request->request->get('receiver_id');
        $content = $request->request->get('content');

        if (!$receiverId || !$content || trim($content) === '') {
            return $this->json(['error' => 'Invalid message data'], 400);
        }

        $receiver = $this->userRepo->find($receiverId);
        if (!$receiver) {
            return $this->json(['error' => 'Receiver not found'], 404);
        }

        // Check if users are blocking each other
        if ($this->blockRepo->isBlocked($currentUser, $receiver) ||
            $this->blockRepo->isBlocked($receiver, $currentUser)) {
            return $this->json(['error' => 'Cannot message this user'], 403);
        }

        // Check if receiver allows messages
        $receiverProfile = $receiver->getProfile();
        if ($receiverProfile && !$receiverProfile->isAllowMessages()) {
            return $this->json(['error' => 'This user has disabled messages'], 403);
        }

        // Create message
        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent(htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8'));

        $this->em->persist($message);

        // Create notification for receiver
        $notification = new Notification();
        $notification->setUser($receiver);
        $notification->setType(Notification::TYPE_MESSAGE_RECEIVED);
        $notification->setRelatedUser($currentUser);
        $notification->setRelatedEntityId($message->getId());
        $notification->setMessage($currentUser->getUsername() . ' sent you a message');

        $this->em->persist($notification);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sentAt' => $message->getSentAt()->format('Y-m-d H:i:s'),
                'sender' => [
                    'id' => $currentUser->getId(),
                    'username' => $currentUser->getUsername()
                ]
            ]
        ]);
    }

    #[Route('/messages/{id}/mark-read', name: 'app_messages_mark_read', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/messages/{id}/mark-read', name: 'app_messages_mark_read_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function markRead(int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $message = $this->messageRepo->find($id);
        if (!$message) {
            return $this->json(['error' => 'Message not found'], 404);
        }

        // Only the receiver can mark message as read
        if ($message->getReceiver()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $message->setIsRead(true);
        $message->setReadAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/messages/{id}/delete', name: 'app_messages_delete', methods: ['DELETE', 'POST'], priority: 1)]
    #[Route('/{_locale}/messages/{id}/delete', name: 'app_messages_delete_locale', methods: ['DELETE', 'POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function delete(int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $message = $this->messageRepo->find($id);
        if (!$message) {
            return $this->json(['error' => 'Message not found'], 404);
        }

        // Mark as deleted for the current user
        if ($message->getSender()->getId() === $currentUser->getId()) {
            $message->setSenderDeleted(true);
        } elseif ($message->getReceiver()->getId() === $currentUser->getId()) {
            $message->setReceiverDeleted(true);
        } else {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        // If both deleted, remove from database
        if ($message->isSenderDeleted() && $message->isReceiverDeleted()) {
            $this->em->remove($message);
        }

        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
