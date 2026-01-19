<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class NotificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationRepository $notificationRepo
    ) {}

    #[Route('/notifications', name: 'app_notifications', methods: ['GET'], priority: 1)]
    #[Route('/{_locale}/notifications', name: 'app_notifications_locale', methods: ['GET'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function index(): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // Get all notifications (paginated in production)
        $notifications = $this->notificationRepo->findByUser($currentUser, 50);
        $unreadCount = $this->notificationRepo->countUnread($currentUser);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    #[Route('/notifications/{id}/mark-read', name: 'app_notifications_mark_read', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/notifications/{id}/mark-read', name: 'app_notifications_mark_read_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function markRead(int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $notification = $this->notificationRepo->find($id);
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        // Only the owner can mark as read
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/notifications/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/notifications/mark-all-read', name: 'app_notifications_mark_all_read_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function markAllRead(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $count = $this->notificationRepo->markAllAsRead($currentUser);

        return $this->json([
            'success' => true,
            'count' => $count
        ]);
    }

    #[Route('/notifications/{id}/delete', name: 'app_notifications_delete', methods: ['DELETE', 'POST'], priority: 1)]
    #[Route('/{_locale}/notifications/{id}/delete', name: 'app_notifications_delete_locale', methods: ['DELETE', 'POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function delete(int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $notification = $this->notificationRepo->find($id);
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], 404);
        }

        // Only the owner can delete
        if ($notification->getUser()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $this->em->remove($notification);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
