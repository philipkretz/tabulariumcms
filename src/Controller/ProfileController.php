<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Order;
use App\Entity\UserProfile;
use App\Entity\UserMedia;
use App\Entity\Friend;
use App\Repository\AddressRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Repository\UserMediaRepository;
use App\Repository\FriendRepository;
use App\Repository\UserBlockRepository;
use App\Repository\SiteSettingsRepository;
use App\Service\ImageProcessor;
use Doctrine\ORM\EntityManagerInterface;

class ProfileController extends AbstractController
{
    /**
     * Sanitize HTML content to remove scripts and event handlers
     */
    private function sanitizeHtml(string $html): string
    {
        // Remove script tags
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/si', '', $html);

        // Remove on* event handlers
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/si', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*\'[^\']*\'/si', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/si', '', $html);

        // Remove javascript: URLs
        $html = preg_replace('/javascript\s*:/si', 'blocked:', $html);

        // Remove data: URLs in src/href (potential XSS vectors)
        $html = preg_replace('/(src|href)\s*=\s*["\']?\s*data\s*:/si', '$1="blocked:', $html);

        // Remove vbscript: URLs
        $html = preg_replace('/vbscript\s*:/si', 'blocked:', $html);

        // Remove iframe, object, embed tags (potential XSS vectors)
        $html = preg_replace('/<(iframe|object|embed|applet|meta|link|base|form|input|button|select|textarea)\b[^>]*>/si', '', $html);
        $html = preg_replace('/<\/(iframe|object|embed|applet|meta|link|base|form|input|button|select|textarea)>/si', '', $html);

        return $html;
    }

    // PUBLIC ROUTES (no authentication required)

    #[Route('/profile/{slug}', name: 'app_public_profile', priority: -1)]
    public function publicProfile(
        string $slug,
        UserRepository $userRepo,
        UserMediaRepository $mediaRepo,
        FriendRepository $friendRepo,
        UserBlockRepository $blockRepo,
        SiteSettingsRepository $settingsRepo,
        EntityManagerInterface $em
    ): Response {
        // Check if public profiles are enabled
        $settings = $settingsRepo->getSettings();
        if (!$settings->isUserProfilesEnabled() || !$settings->isPublicProfilesEnabled()) {
            throw $this->createNotFoundException('Public profiles are disabled');
        }

        // Find user by slug or username
        $targetUser = $userRepo->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->where('p.profileSlug = :slug')
            ->orWhere('u.username = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$targetUser || !$targetUser->getProfile()) {
            throw $this->createNotFoundException('Profile not found');
        }

        $profile = $targetUser->getProfile();

        // Check if profile is approved
        if (!$profile->isProfileApproved() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createNotFoundException('Profile is pending approval');
        }

        $currentUser = $this->getUser();

        // Check blocking
        if ($currentUser && $blockRepo->isBlocked($currentUser, $targetUser)) {
            throw $this->createAccessDeniedException('You cannot view this profile');
        }

        // Check privacy settings
        $isFriend = false;
        $canViewFullProfile = true;

        if ($currentUser) {
            $isFriend = $friendRepo->areFriends($currentUser, $targetUser);
        }

        if (!$profile->isPublic() && !$isFriend && $currentUser !== $targetUser) {
            $canViewFullProfile = false;
        }

        // Increment profile views (only if not viewing own profile)
        if ($currentUser !== $targetUser) {
            $profile->incrementProfileViews();
            $em->flush();
        }

        // Get recent media (6 items)
        $recentMedia = $mediaRepo->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.isPublic = :isPublic OR :isFriend = true OR :isOwner = true')
            ->setParameter('user', $targetUser)
            ->setParameter('isPublic', true)
            ->setParameter('isFriend', $isFriend)
            ->setParameter('isOwner', $currentUser === $targetUser)
            ->orderBy('m.uploadedAt', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        // Get friends list (6 recent)
        $friends = $friendRepo->findFriends($targetUser);
        $recentFriends = array_slice($friends, 0, 6);

        // Log activity
        // TODO: Add activity log entry for profile view

        return $this->render('profile/public_profile.html.twig', [
            'profile' => $profile,
            'targetUser' => $targetUser,
            'canViewFullProfile' => $canViewFullProfile,
            'isFriend' => $isFriend,
            'isOwnProfile' => $currentUser === $targetUser,
            'recentMedia' => $recentMedia,
            'friends' => $recentFriends,
            'friendCount' => count($friends),
        ]);
    }

    #[Route('/profiles', name: 'app_profile_directory', priority: 1)]
    #[Route('/{_locale}/profiles', name: 'app_profile_directory_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function directory(
        Request $request,
        UserRepository $userRepo,
        SiteSettingsRepository $settingsRepo
    ): Response {
        // Check if public profiles are enabled
        $settings = $settingsRepo->getSettings();
        if (!$settings->isUserProfilesEnabled() || !$settings->isPublicProfilesEnabled()) {
            throw $this->createNotFoundException('Public profiles are disabled');
        }

        $search = $request->query->get('search', '');
        $location = $request->query->get('location', '');
        $interest = $request->query->get('interest', '');
        $role = $request->query->get('role', '');
        $hasPhoto = $request->query->get('has_photo', false);
        $sortBy = $request->query->get('sort', 'newest'); // newest, popular, active, alphabetical

        $qb = $userRepo->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->where('p.isPublic = :isPublic')
            ->andWhere('p.profileApproved = :approved')
            ->setParameter('isPublic', true)
            ->setParameter('approved', true);

        // Search filter
        if ($search) {
            $qb->andWhere('u.username LIKE :search OR p.firstName LIKE :search OR p.lastName LIKE :search OR p.tagline LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Location filter
        if ($location) {
            $qb->andWhere('p.location LIKE :location')
                ->setParameter('location', '%' . $location . '%');
        }

        // Interest filter (JSON field search)
        if ($interest) {
            $qb->andWhere('JSON_SEARCH(p.interests, \'one\', :interest) IS NOT NULL')
                ->setParameter('interest', '%' . $interest . '%');
        }

        // Role filter
        if ($role === 'seller') {
            $qb->andWhere('u.roles LIKE :sellerRole')
                ->setParameter('sellerRole', '%ROLE_SELLER%');
        } elseif ($role === 'buyer') {
            $qb->andWhere('u.roles NOT LIKE :sellerRole')
                ->setParameter('sellerRole', '%ROLE_SELLER%');
        }

        // Has photo filter
        if ($hasPhoto) {
            $qb->andWhere('p.avatar IS NOT NULL');
        }

        // Sorting
        switch ($sortBy) {
            case 'popular':
                $qb->orderBy('p.profileViews', 'DESC');
                break;
            case 'active':
                $qb->orderBy('p.lastActive', 'DESC');
                break;
            case 'alphabetical':
                $qb->orderBy('p.firstName', 'ASC')
                   ->addOrderBy('p.lastName', 'ASC');
                break;
            case 'newest':
            default:
                $qb->orderBy('u.createdAt', 'DESC');
                break;
        }

        $users = $qb->getQuery()->getResult();

        return $this->render('profile/directory.html.twig', [
            'users' => $users,
            'search' => $search,
            'location' => $location,
            'sortBy' => $sortBy,
        ]);
    }

    #[Route('/profile/{slug}/friends', name: 'app_profile_friends', priority: 1)]
    #[Route('/{_locale}/profile/{slug}/friends', name: 'app_profile_friends_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function profileFriends(
        string $slug,
        UserRepository $userRepo,
        FriendRepository $friendRepo,
        UserBlockRepository $blockRepo,
        SiteSettingsRepository $settingsRepo
    ): Response {
        // Check if public profiles are enabled
        $settings = $settingsRepo->getSettings();
        if (!$settings->isUserProfilesEnabled() || !$settings->isPublicProfilesEnabled()) {
            throw $this->createNotFoundException('Public profiles are disabled');
        }

        // Find user by slug or username
        $targetUser = $userRepo->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->where('p.profileSlug = :slug')
            ->orWhere('u.username = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$targetUser || !$targetUser->getProfile()) {
            throw $this->createNotFoundException('Profile not found');
        }

        $profile = $targetUser->getProfile();
        $currentUser = $this->getUser();

        // Check blocking
        if ($currentUser && $blockRepo->isBlocked($currentUser, $targetUser)) {
            throw $this->createAccessDeniedException('You cannot view this profile');
        }

        // Check privacy settings
        $isFriend = false;
        $canViewFriends = true;

        if ($currentUser) {
            $isFriend = $friendRepo->areFriends($currentUser, $targetUser);
        }

        if (!$profile->isPublic() && !$isFriend && $currentUser !== $targetUser) {
            $canViewFriends = false;
        }

        // Get all friends
        $friends = $canViewFriends ? $friendRepo->findFriends($targetUser) : [];

        return $this->render('profile/friends.html.twig', [
            'profile' => $profile,
            'targetUser' => $targetUser,
            'friends' => $friends,
            'canViewFriends' => $canViewFriends,
            'isFriend' => $isFriend,
            'isOwnProfile' => $currentUser === $targetUser,
        ]);
    }

    #[Route('/profile/{slug}/media', name: 'app_profile_media', priority: 1)]
    #[Route('/{_locale}/profile/{slug}/media', name: 'app_profile_media_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function profileMedia(
        string $slug,
        UserRepository $userRepo,
        UserMediaRepository $mediaRepo,
        FriendRepository $friendRepo,
        UserBlockRepository $blockRepo,
        SiteSettingsRepository $settingsRepo
    ): Response {
        // Check if public profiles are enabled
        $settings = $settingsRepo->getSettings();
        if (!$settings->isUserProfilesEnabled() || !$settings->isPublicProfilesEnabled()) {
            throw $this->createNotFoundException('Public profiles are disabled');
        }

        // Find user by slug or username
        $targetUser = $userRepo->createQueryBuilder('u')
            ->leftJoin('u.profile', 'p')
            ->where('p.profileSlug = :slug')
            ->orWhere('u.username = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$targetUser || !$targetUser->getProfile()) {
            throw $this->createNotFoundException('Profile not found');
        }

        $profile = $targetUser->getProfile();
        $currentUser = $this->getUser();

        // Check blocking
        if ($currentUser && $blockRepo->isBlocked($currentUser, $targetUser)) {
            throw $this->createAccessDeniedException('You cannot view this profile');
        }

        // Check privacy settings
        $isFriend = false;
        if ($currentUser) {
            $isFriend = $friendRepo->areFriends($currentUser, $targetUser);
        }

        // Get all media (respecting privacy)
        $media = $mediaRepo->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.isPublic = :isPublic OR :isFriend = true OR :isOwner = true')
            ->setParameter('user', $targetUser)
            ->setParameter('isPublic', true)
            ->setParameter('isFriend', $isFriend)
            ->setParameter('isOwner', $currentUser === $targetUser)
            ->orderBy('m.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('profile/media.html.twig', [
            'profile' => $profile,
            'targetUser' => $targetUser,
            'media' => $media,
            'isFriend' => $isFriend,
            'isOwnProfile' => $currentUser === $targetUser,
        ]);
    }

    // AUTHENTICATED ROUTES (require ROLE_USER)

    #[Route('/my-profile', name: 'app_profile', priority: 1)]
    #[Route('/{_locale}/my-profile', name: 'app_profile_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    #[IsGranted('ROLE_USER')]
    public function index(OrderRepository $orderRepository, AddressRepository $addressRepository): Response
    {
        $user = $this->getUser();

        // Fetch recent orders (last 3)
        $recentOrders = $orderRepository->findBy(
            ['customer' => $user],
            ['createdAt' => 'DESC'],
            3
        );

        // Calculate stats
        $totalOrders = $orderRepository->count(['customer' => $user]);
        $addressCount = $addressRepository->count(['user' => $user]);

        // Calculate account age in days
        $accountAge = $user->getCreatedAt()->diff(new \DateTime())->days;

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'totalOrders' => $totalOrders,
            'addressCount' => $addressCount,
            'accountAge' => $accountAge,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', priority: 1)]
    #[Route('/{_locale}/profile/edit', name: 'app_profile_edit_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            // Update user fields
            $firstName = $request->request->get('firstName');
            if ($firstName !== null) {
                $user->setFirstName($firstName);
            }

            $lastName = $request->request->get('lastName');
            if ($lastName !== null) {
                $user->setLastName($lastName);
            }

            $locale = $request->request->get('locale');
            if ($locale !== null) {
                $user->setLocale($locale);
            }

            if ($request->request->get('current_password')) {
                // Verify current password before changing email
                // This would need password validation logic
                $email = $request->request->get('email');
                if ($email !== null) {
                    $user->setEmail($email);
                }
            }

            // Get or create user profile
            $profile = $user->getProfile();
            if (!$profile) {
                $profile = new UserProfile();
                $profile->setUser($user);
                $entityManager->persist($profile);
            }

            // Update profile fields
            $tagline = $request->request->get('tagline');
            if ($tagline !== null) {
                $profile->setTagline($tagline);
            }

            $bio = $request->request->get('bio');
            if ($bio !== null) {
                // Sanitize HTML - remove scripts and event handlers
                $bio = $this->sanitizeHtml($bio);
                $profile->setBio($bio);
            }

            $location = $request->request->get('location');
            if ($location !== null) {
                $profile->setLocation($location);
            }

            $website = $request->request->get('website');
            if ($website !== null) {
                $profile->setWebsite($website);
            }

            // Handle interests (comma-separated string to array)
            $interestsString = $request->request->get('interests');
            if ($interestsString !== null) {
                // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callback 'trim'
                $interests = array_map('trim', explode(',', $interestsString));
                $interests = array_filter($interests); // Remove empty values
                $profile->setInterests($interests);
            }

            // Handle "Looking For" field
            $lookingFor = $request->request->get('lookingFor');
            if ($lookingFor !== null) {
                $profile->setLookingFor($this->sanitizeHtml($lookingFor));
            }

            // Handle "What I Offer" field
            $offering = $request->request->get('offering');
            if ($offering !== null) {
                $profile->setOffering($this->sanitizeHtml($offering));
            }

            // Handle custom CSS (sanitize - no JavaScript allowed)
            $customCss = $request->request->get('customCss');
            if ($customCss !== null) {
                // Basic CSS sanitization - remove any url() with javascript:
                $customCss = preg_replace('/url\s*\(\s*["\']?\s*javascript:/i', 'url(blocked:', $customCss);
                // Remove expression() which is IE-specific JS execution
                $customCss = preg_replace('/expression\s*\(/i', 'blocked(', $customCss);
                // Remove behavior: property (IE-specific)
                $customCss = preg_replace('/behavior\s*:/i', 'blocked:', $customCss);
                $profile->setCustomTemplate($customCss);
            }

            // Handle social media links
            $socialData = $request->request->all('social');
            if ($socialData) {
                $socialLinks = [];

                // Main social platforms
                $platforms = ['facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'snapchat', 'pinterest'];
                foreach ($platforms as $platform) {
                    if (!empty($socialData[$platform])) {
                        $socialLinks[$platform] = $socialData[$platform];
                    }
                }

                // Custom links
                if (!empty($socialData['custom_label']) && !empty($socialData['custom_url'])) {
                    $customLinks = [];
                    $labels = $socialData['custom_label'];
                    $urls = $socialData['custom_url'];

                    foreach ($labels as $index => $label) {
                        if (!empty($label) && !empty($urls[$index])) {
                            $customLinks[] = [
                                'label' => $label,
                                'url' => $urls[$index]
                            ];
                        }
                    }

                    if (!empty($customLinks)) {
                        $socialLinks['custom'] = $customLinks;
                    }
                }

                $profile->setSocialLinks($socialLinks);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/addresses', name: 'app_profile_addresses', priority: 1)]
    #[Route('/{_locale}/profile/addresses', name: 'app_profile_addresses_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function addresses(AddressRepository $addressRepository): Response
    {
        $addresses = $addressRepository->findByUser($this->getUser());
        
        return $this->render('profile/addresses.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/addresses/new', name: 'app_profile_address_new', priority: 1)]
    #[Route('/{_locale}/profile/addresses/new', name: 'app_profile_address_new_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function newAddress(Request $request, AddressRepository $addressRepository): Response
    {
        $address = new Address();
        
        if ($request->isMethod('POST')) {
            $address->setType($request->request->get('type'));
            $address->setFirstName($request->request->get('firstName'));
            $address->setLastName($request->request->get('lastName'));
            $address->setCompany($request->request->get('company'));
            $address->setAddressLine1($request->request->get('addressLine1'));
            $address->setAddressLine2($request->request->get('addressLine2'));
            $address->setCity($request->request->get('city'));
            $address->setPostalCode($request->request->get('postalCode'));
            $address->setCountry($request->request->get('country'));
            $address->setState($request->request->get('state'));
            $address->setPhone($request->request->get('phone'));
            $address->setUser($this->getUser());
            $address->setDefault($request->request->get('is_default', false));
            
            if ($address->isDefault()) {
                $addressRepository->setDefault($this->getUser(), $address, $address->getType());
            }
            
            $addressRepository->save($address, true);
            
            $this->addFlash('success', 'Address added successfully.');
            
            return $this->redirectToRoute('app_profile_addresses');
        }
        
        return $this->render('profile/address_form.html.twig', [
            'address' => $address,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/addresses/{id}/edit', name: 'app_profile_address_edit', priority: 1)]
    #[Route('/{_locale}/profile/addresses/{id}/edit', name: 'app_profile_address_edit_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function editAddress(Address $address, Request $request, AddressRepository $addressRepository): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        if ($request->isMethod('POST')) {
            $address->setType($request->request->get('type'));
            $address->setFirstName($request->request->get('firstName'));
            $address->setLastName($request->request->get('lastName'));
            $address->setCompany($request->request->get('company'));
            $address->setAddressLine1($request->request->get('addressLine1'));
            $address->setAddressLine2($request->request->get('addressLine2'));
            $address->setCity($request->request->get('city'));
            $address->setPostalCode($request->request->get('postalCode'));
            $address->setCountry($request->request->get('country'));
            $address->setState($request->request->get('state'));
            $address->setPhone($request->request->get('phone'));
            $address->setDefault($request->request->get('is_default', false));
            
            if ($address->isDefault()) {
                $addressRepository->setDefault($this->getUser(), $address, $address->getType());
            }
            
            $addressRepository->save($address, true);
            
            $this->addFlash('success', 'Address updated successfully.');
            
            return $this->redirectToRoute('app_profile_addresses');
        }
        
        return $this->render('profile/address_form.html.twig', [
            'address' => $address,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/addresses/{id}/delete', name: 'app_profile_address_delete', priority: 1)]
    #[Route('/{_locale}/profile/addresses/{id}/delete', name: 'app_profile_address_delete_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function deleteAddress(Address $address, AddressRepository $addressRepository): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        $addressRepository->remove($address, true);
        
        $this->addFlash('success', 'Address deleted successfully.');
        
        return $this->redirectToRoute('app_profile_addresses');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/settings', name: 'app_profile_settings', priority: 1)]
    #[Route('/{_locale}/profile/settings', name: 'app_profile_settings_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function settings(): Response
    {
        return $this->render('profile/settings.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/media-gallery', name: 'app_profile_media_gallery', priority: 1)]
    #[Route('/{_locale}/profile/media-gallery', name: 'app_profile_media_gallery_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function mediaGallery(
        UserMediaRepository $mediaRepo,
        SiteSettingsRepository $settingsRepo
    ): Response {
        $user = $this->getUser();
        $settings = $settingsRepo->getSettings();

        // Get user's media
        $media = $mediaRepo->findBy(
            ['user' => $user],
            ['uploadedAt' => 'DESC']
        );

        return $this->render('profile/media_gallery.html.twig', [
            'user' => $user,
            'media' => $media,
            'maxMediaPerUser' => $settings->getMaxMediaPerUser(),
            'maxMediaSizeKb' => $settings->getMaxMediaSizeKb(),
            'currentMediaCount' => count($media),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/media-gallery/upload', name: 'app_profile_media_gallery_upload', methods: ['POST'], priority: 1)]
    public function mediaGalleryUpload(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageProcessor $imageProcessor,
        SiteSettingsRepository $settingsRepo,
        UserMediaRepository $mediaRepo
    ): Response {
        try {
            $user = $this->getUser();
            $settings = $settingsRepo->getSettings();

            // Check if user media is enabled
            if (!$settings->isUserMediaEnabled()) {
                return $this->json(['success' => false, 'error' => 'Media uploads are disabled'], 403);
            }

            // Check media count limit
            $currentCount = $mediaRepo->count(['user' => $user]);
            if ($currentCount >= $settings->getMaxMediaPerUser()) {
                return $this->json([
                    'success' => false,
                    'error' => 'You have reached the maximum number of media files (' . $settings->getMaxMediaPerUser() . ')'
                ], 400);
            }

            $uploadedFile = $request->files->get('media');
            if (!$uploadedFile) {
                return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            }

            // Validate file size (from site settings, in KB)
            $maxSizeBytes = $settings->getMaxMediaSizeKb() * 1024;
            if ($uploadedFile->getSize() > $maxSizeBytes) {
                return $this->json([
                    'success' => false,
                    'error' => 'File size exceeds maximum allowed (' . round($settings->getMaxMediaSizeKb() / 1024, 1) . 'MB)'
                ], 400);
            }

            // Validate file type
            $mimeType = $uploadedFile->getMimeType();
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm'];
            if (!in_array($mimeType, $allowedTypes)) {
                return $this->json(['success' => false, 'error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP, MP4, WebM'], 400);
            }

            // Create user-specific media directory
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/user-media/' . $user->getId();
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
            if (!is_dir($uploadDir)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
                mkdir($uploadDir, 0755, true);
            }

            $isVideo = str_starts_with($mimeType, 'video/');

            if (!$isVideo) {
                // Resize images to max 1920x1920
                $resizedImagePath = $imageProcessor->resizeImage($uploadedFile, 1920, 1920, 85);
                $filename = $imageProcessor->generateSafeFilename('media', $uploadedFile->guessExtension());
                $destination = $uploadDir . '/' . $filename;

                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Paths are server-controlled and safely generated
                if (!rename($resizedImagePath, $destination)) {
                    return $this->json(['success' => false, 'error' => 'Failed to save media'], 500);
                }
            } else {
                // For videos, just move the file
                $filename = $imageProcessor->generateSafeFilename('video', $uploadedFile->guessExtension());
                $uploadedFile->move($uploadDir, $filename);
            }

            // Create UserMedia entity
            $media = new UserMedia();
            $media->setUser($user);
            $media->setFilename($filename);
            $media->setOriginalFilename($uploadedFile->getClientOriginalName());
            $media->setMimeType($mimeType);
            $media->setFileSize($uploadedFile->getSize());
            $media->setType($isVideo ? 'video' : 'image');
            $media->setIsPublic(true);
            $media->setUploadedAt(new \DateTimeImmutable());

            $entityManager->persist($media);
            $entityManager->flush();

            $mediaUrl = '/uploads/user-media/' . $user->getId() . '/' . $filename;

            return $this->json([
                'success' => true,
                'url' => $mediaUrl,
                'id' => $media->getId(),
                'type' => $media->getType(),
                'message' => 'Media uploaded successfully'
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to upload media: ' . $e->getMessage()], 500);
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/media-gallery/delete/{id}', name: 'app_profile_media_gallery_delete', methods: ['POST', 'DELETE'], priority: 1)]
    public function mediaGalleryDelete(
        int $id,
        EntityManagerInterface $entityManager,
        UserMediaRepository $mediaRepo
    ): Response {
        try {
            $user = $this->getUser();
            $media = $mediaRepo->find($id);

            if (!$media || $media->getUser()->getId() !== $user->getId()) {
                return $this->json(['success' => false, 'error' => 'Media not found'], 404);
            }

            // Delete file from filesystem
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/user-media/' . $user->getId() . '/' . $media->getFilename();
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
            if (file_exists($filePath)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                unlink($filePath);
            }

            // Delete from database
            $entityManager->remove($media);
            $entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Media deleted successfully']);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to delete media'], 500);
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/preview', name: 'app_profile_preview', priority: 1)]
    #[Route('/{_locale}/profile/preview', name: 'app_profile_preview_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function preview(
        UserMediaRepository $mediaRepo,
        FriendRepository $friendRepo
    ): Response {
        $user = $this->getUser();
        $profile = $user->getProfile();

        // Get media
        $media = $mediaRepo->findBy(
            ['user' => $user, 'isPublic' => true],
            ['uploadedAt' => 'DESC'],
            6
        );

        // Get friends
        $friends = $friendRepo->findFriends($user);
        $recentFriends = array_slice($friends, 0, 6);

        return $this->render('profile/preview.html.twig', [
            'profile' => $profile,
            'targetUser' => $user,
            'recentMedia' => $media,
            'friends' => $recentFriends,
            'friendCount' => count($friends),
            'isPreview' => true,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/orders', name: 'app_profile_orders', priority: 1)]
    #[Route('/{_locale}/profile/orders', name: 'app_profile_orders_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function orders(Request $request, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        $queryBuilder = $orderRepository->createQueryBuilder('o')
            ->where('o.customer = :user')
            ->setParameter('user', $user)
            ->orderBy('o.createdAt', 'DESC');

        if ($status) {
            $queryBuilder->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($search) {
            $queryBuilder->andWhere('o.orderNumber LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $orders = $queryBuilder->getQuery()->getResult();

        return $this->render('profile/orders.html.twig', [
            'orders' => $orders,
            'currentStatus' => $status,
            'searchQuery' => $search,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/orders/{id}', name: 'app_profile_order_detail', priority: 1)]
    #[Route('/{_locale}/profile/orders/{id}', name: 'app_profile_order_detail_locale', priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function orderDetail(Order $order): Response
    {
        if ($order->getCustomer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this order.');
        }

        return $this->render('profile/order_detail.html.twig', [
            'order' => $order,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/addresses/{id}/set-default', name: 'app_profile_address_set_default', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/profile/addresses/{id}/set-default', name: 'app_profile_address_set_default_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function setDefaultAddress(Address $address, AddressRepository $addressRepository): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $addressRepository->setDefault($this->getUser(), $address, $address->getType());
        $addressRepository->save($address, true);

        $this->addFlash('success', 'Default address updated successfully.');

        return $this->redirectToRoute('app_profile_addresses');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/change-password', name: 'app_profile_change_password', methods: ['POST'], priority: 1)]
    #[Route('/{_locale}/profile/change-password', name: 'app_profile_change_password_locale', methods: ['POST'], priority: 1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        // Verify current password
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Current password is incorrect.');
            return $this->redirectToRoute('app_profile_settings');
        }

        // Verify new passwords match
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'New passwords do not match.');
            return $this->redirectToRoute('app_profile_settings');
        }

        // Verify password length
        if (strlen($newPassword) < 8) {
            $this->addFlash('error', 'New password must be at least 8 characters long.');
            return $this->redirectToRoute('app_profile_settings');
        }

        // Hash and update password
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $entityManager->flush();

        $this->addFlash('success', 'Password changed successfully.');

        return $this->redirectToRoute('app_profile_settings');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/upload-avatar', name: 'app_profile_upload_avatar', methods: ['POST'], priority: 1)]
    public function uploadAvatar(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageProcessor $imageProcessor
    ): Response {
        try {
            $uploadedFile = $request->files->get('avatar');

            if (!$uploadedFile) {
                return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            }

            // Validate file (max 5MB)
            $imageProcessor->validateImageFile($uploadedFile, 5 * 1024 * 1024);

            $user = $this->getUser();

            // Create user-specific directory
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $user->getId();
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
            if (!is_dir($uploadDir)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
                mkdir($uploadDir, 0755, true);
            }

            // Delete old avatar if exists
            if ($user->getAvatar()) {
                $oldAvatarPath = $uploadDir . '/' . $user->getAvatar();
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                if (file_exists($oldAvatarPath)) {
                    // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                    unlink($oldAvatarPath);
                }
            }

            // Resize image to 400x400px
            $resizedImagePath = $imageProcessor->resizeImage($uploadedFile, 400, 400, 85);

            // Generate safe filename
            $filename = $imageProcessor->generateSafeFilename('avatar', $uploadedFile->guessExtension());

            // Move resized image to upload directory
            $destination = $uploadDir . '/' . $filename;
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Paths are server-controlled and safely generated
            if (!rename($resizedImagePath, $destination)) {
                return $this->json(['success' => false, 'error' => 'Failed to save image'], 500);
            }

            // Update user avatar field
            $user->setAvatar($filename);

            // Optionally sync with UserProfile.avatar
            $profile = $user->getProfile();
            if (!$profile) {
                $profile = new UserProfile();
                $profile->setUser($user);
                $entityManager->persist($profile);
            }
            $profile->setAvatar($filename);

            $entityManager->flush();

            $imageUrl = '/uploads/profiles/' . $user->getId() . '/' . $filename;

            return $this->json([
                'success' => true,
                'url' => $imageUrl,
                'message' => 'Avatar uploaded successfully'
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to upload avatar: ' . $e->getMessage()], 500);
        }
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profile/upload-cover-photo', name: 'app_profile_upload_cover_photo', methods: ['POST'], priority: 1)]
    public function uploadCoverPhoto(
        Request $request,
        EntityManagerInterface $entityManager,
        ImageProcessor $imageProcessor
    ): Response {
        try {
            $uploadedFile = $request->files->get('cover_photo');

            if (!$uploadedFile) {
                return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            }

            // Validate file (max 10MB)
            $imageProcessor->validateImageFile($uploadedFile, 10 * 1024 * 1024);

            $user = $this->getUser();

            // Create user-specific directory
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $user->getId();
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
            if (!is_dir($uploadDir)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
                mkdir($uploadDir, 0755, true);
            }

            // Get or create profile
            $profile = $user->getProfile();
            if (!$profile) {
                $profile = new UserProfile();
                $profile->setUser($user);
                $entityManager->persist($profile);
            }

            // Delete old cover photo if exists
            if ($profile->getCoverPhoto()) {
                $oldCoverPath = $uploadDir . '/' . $profile->getCoverPhoto();
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                if (file_exists($oldCoverPath)) {
                    // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                    unlink($oldCoverPath);
                }
            }

            // Resize image to 1200x400px (banner dimensions)
            $resizedImagePath = $imageProcessor->resizeImage($uploadedFile, 1200, 400, 85);

            // Generate safe filename
            $filename = $imageProcessor->generateSafeFilename('cover', $uploadedFile->guessExtension());

            // Move resized image to upload directory
            $destination = $uploadDir . '/' . $filename;
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Paths are server-controlled and safely generated
            if (!rename($resizedImagePath, $destination)) {
                return $this->json(['success' => false, 'error' => 'Failed to save image'], 500);
            }

            // Update profile cover photo field
            $profile->setCoverPhoto($filename);

            $entityManager->flush();

            $imageUrl = '/uploads/profiles/' . $user->getId() . '/' . $filename;

            return $this->json([
                'success' => true,
                'url' => $imageUrl,
                'message' => 'Cover photo uploaded successfully'
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to upload cover photo: ' . $e->getMessage()], 500);
        }
    }
}