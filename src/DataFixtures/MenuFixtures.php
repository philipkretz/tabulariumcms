<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
class MenuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Header Menu
        $headerMenu = new Menu();
        $headerMenu->setName('Main Navigation');
        $headerMenu->setIdentifier('main-menu');
        $headerMenu->setPosition('header');
        $headerMenu->setActive(true);
        $manager->persist($headerMenu);

        // Header Menu Items
        $homeItem = new MenuItem();
        $homeItem->setMenu($headerMenu);
        $homeItem->setTitle('Home');
        $homeItem->setUrl('/');
        $homeItem->setIcon('fas fa-home');
        $homeItem->setSortOrder(10);
        $homeItem->setActive(true);
        $manager->persist($homeItem);

        $aboutItem = new MenuItem();
        $aboutItem->setMenu($headerMenu);
        $aboutItem->setTitle('About');
        $aboutItem->setUrl('/about');
        $aboutItem->setIcon('fas fa-info-circle');
        $aboutItem->setSortOrder(20);
        $aboutItem->setActive(true);
        $manager->persist($aboutItem);

        // About submenu items
        $teamItem = new MenuItem();
        $teamItem->setMenu($headerMenu);
        $teamItem->setParent($aboutItem);
        $teamItem->setTitle('Our Team');
        $teamItem->setUrl('/about/team');
        $teamItem->setSortOrder(10);
        $teamItem->setActive(true);
        $manager->persist($teamItem);

        $historyItem = new MenuItem();
        $historyItem->setMenu($headerMenu);
        $historyItem->setParent($aboutItem);
        $historyItem->setTitle('History');
        $historyItem->setUrl('/about/history');
        $historyItem->setSortOrder(20);
        $historyItem->setActive(true);
        $manager->persist($historyItem);

        $servicesItem = new MenuItem();
        $servicesItem->setMenu($headerMenu);
        $servicesItem->setTitle('Services');
        $servicesItem->setUrl('/services');
        $servicesItem->setIcon('fas fa-briefcase');
        $servicesItem->setSortOrder(30);
        $servicesItem->setActive(true);
        $manager->persist($servicesItem);

        // Services submenu
        $webDevItem = new MenuItem();
        $webDevItem->setMenu($headerMenu);
        $webDevItem->setParent($servicesItem);
        $webDevItem->setTitle('Web Development');
        $webDevItem->setUrl('/services/web-development');
        $webDevItem->setSortOrder(10);
        $webDevItem->setActive(true);
        $manager->persist($webDevItem);

        $consultingItem = new MenuItem();
        $consultingItem->setMenu($headerMenu);
        $consultingItem->setParent($servicesItem);
        $consultingItem->setTitle('Consulting');
        $consultingItem->setUrl('/services/consulting');
        $consultingItem->setSortOrder(20);
        $consultingItem->setActive(true);
        $manager->persist($consultingItem);

        $hostingItem = new MenuItem();
        $hostingItem->setMenu($headerMenu);
        $hostingItem->setParent($servicesItem);
        $hostingItem->setTitle('Hosting');
        $hostingItem->setUrl('/services/hosting');
        $hostingItem->setSortOrder(30);
        $hostingItem->setActive(true);
        $manager->persist($hostingItem);

        $blogItem = new MenuItem();
        $blogItem->setMenu($headerMenu);
        $blogItem->setTitle('Blog');
        $blogItem->setUrl('/blog');
        $blogItem->setIcon('fas fa-blog');
        $blogItem->setSortOrder(40);
        $blogItem->setActive(true);
        $manager->persist($blogItem);

        $contactItem = new MenuItem();
        $contactItem->setMenu($headerMenu);
        $contactItem->setTitle('Contact');
        $contactItem->setUrl('/contact');
        $contactItem->setIcon('fas fa-envelope');
        $contactItem->setSortOrder(50);
        $contactItem->setActive(true);
        $manager->persist($contactItem);

        // Create Mobile Menu (simplified version)
        $mobileMenu = new Menu();
        $mobileMenu->setName('Mobile Navigation');
        $mobileMenu->setIdentifier('mobile-menu');
        $mobileMenu->setPosition('mobile');
        $mobileMenu->setActive(true);
        $manager->persist($mobileMenu);

        // Mobile Menu Items (top-level only for simplicity)
        $mobileHome = new MenuItem();
        $mobileHome->setMenu($mobileMenu);
        $mobileHome->setTitle('Home');
        $mobileHome->setUrl('/');
        $mobileHome->setIcon('fas fa-home');
        $mobileHome->setSortOrder(10);
        $mobileHome->setActive(true);
        $manager->persist($mobileHome);

        $mobileAbout = new MenuItem();
        $mobileAbout->setMenu($mobileMenu);
        $mobileAbout->setTitle('About');
        $mobileAbout->setUrl('/about');
        $mobileAbout->setIcon('fas fa-info-circle');
        $mobileAbout->setSortOrder(20);
        $mobileAbout->setActive(true);
        $manager->persist($mobileAbout);

        $mobileServices = new MenuItem();
        $mobileServices->setMenu($mobileMenu);
        $mobileServices->setTitle('Services');
        $mobileServices->setUrl('/services');
        $mobileServices->setIcon('fas fa-briefcase');
        $mobileServices->setSortOrder(30);
        $mobileServices->setActive(true);
        $manager->persist($mobileServices);

        $mobileBlog = new MenuItem();
        $mobileBlog->setMenu($mobileMenu);
        $mobileBlog->setTitle('Blog');
        $mobileBlog->setUrl('/blog');
        $mobileBlog->setIcon('fas fa-blog');
        $mobileBlog->setSortOrder(40);
        $mobileBlog->setActive(true);
        $manager->persist($mobileBlog);

        $mobileContact = new MenuItem();
        $mobileContact->setMenu($mobileMenu);
        $mobileContact->setTitle('Contact');
        $mobileContact->setUrl('/contact');
        $mobileContact->setIcon('fas fa-envelope');
        $mobileContact->setSortOrder(50);
        $mobileContact->setActive(true);
        $manager->persist($mobileContact);

        // Create Footer Menu
        $footerMenu = new Menu();
        $footerMenu->setName('Footer Links');
        $footerMenu->setIdentifier('footer-menu');
        $footerMenu->setPosition('footer');
        $footerMenu->setActive(true);
        $manager->persist($footerMenu);

        // Footer Menu - Quick Links Section
        $quickLinks = new MenuItem();
        $quickLinks->setMenu($footerMenu);
        $quickLinks->setTitle('Quick Links');
        $quickLinks->setUrl('#');
        $quickLinks->setSortOrder(10);
        $quickLinks->setActive(true);
        $manager->persist($quickLinks);

        $docsItem = new MenuItem();
        $docsItem->setMenu($footerMenu);
        $docsItem->setParent($quickLinks);
        $docsItem->setTitle('Documentation');
        $docsItem->setUrl('/docs');
        $docsItem->setSortOrder(10);
        $docsItem->setActive(true);
        $manager->persist($docsItem);

        $supportItem = new MenuItem();
        $supportItem->setMenu($footerMenu);
        $supportItem->setParent($quickLinks);
        $supportItem->setTitle('Support');
        $supportItem->setUrl('/support');
        $supportItem->setSortOrder(20);
        $supportItem->setActive(true);
        $manager->persist($supportItem);

        $termsItem = new MenuItem();
        $termsItem->setMenu($footerMenu);
        $termsItem->setParent($quickLinks);
        $termsItem->setTitle('Terms of Service');
        $termsItem->setUrl('/terms');
        $termsItem->setSortOrder(30);
        $termsItem->setActive(true);
        $manager->persist($termsItem);

        $privacyItem = new MenuItem();
        $privacyItem->setMenu($footerMenu);
        $privacyItem->setParent($quickLinks);
        $privacyItem->setTitle('Privacy Policy');
        $privacyItem->setUrl('/privacy');
        $privacyItem->setSortOrder(40);
        $privacyItem->setActive(true);
        $manager->persist($privacyItem);

        // Footer Menu - Services Section
        $servicesSection = new MenuItem();
        $servicesSection->setMenu($footerMenu);
        $servicesSection->setTitle('Services');
        $servicesSection->setUrl('#');
        $servicesSection->setSortOrder(20);
        $servicesSection->setActive(true);
        $manager->persist($servicesSection);

        $footerWebDev = new MenuItem();
        $footerWebDev->setMenu($footerMenu);
        $footerWebDev->setParent($servicesSection);
        $footerWebDev->setTitle('Web Development');
        $footerWebDev->setUrl('/services/web-development');
        $footerWebDev->setSortOrder(10);
        $footerWebDev->setActive(true);
        $manager->persist($footerWebDev);

        $footerConsulting = new MenuItem();
        $footerConsulting->setMenu($footerMenu);
        $footerConsulting->setParent($servicesSection);
        $footerConsulting->setTitle('Consulting');
        $footerConsulting->setUrl('/services/consulting');
        $footerConsulting->setSortOrder(20);
        $footerConsulting->setActive(true);
        $manager->persist($footerConsulting);

        $footerHosting = new MenuItem();
        $footerHosting->setMenu($footerMenu);
        $footerHosting->setParent($servicesSection);
        $footerHosting->setTitle('Hosting');
        $footerHosting->setUrl('/services/hosting');
        $footerHosting->setSortOrder(30);
        $footerHosting->setActive(true);
        $manager->persist($footerHosting);

        // Footer Menu - Company Section
        $companySection = new MenuItem();
        $companySection->setMenu($footerMenu);
        $companySection->setTitle('Company');
        $companySection->setUrl('#');
        $companySection->setSortOrder(30);
        $companySection->setActive(true);
        $manager->persist($companySection);

        $footerAbout = new MenuItem();
        $footerAbout->setMenu($footerMenu);
        $footerAbout->setParent($companySection);
        $footerAbout->setTitle('About Us');
        $footerAbout->setUrl('/about');
        $footerAbout->setSortOrder(10);
        $footerAbout->setActive(true);
        $manager->persist($footerAbout);

        $footerTeam = new MenuItem();
        $footerTeam->setMenu($footerMenu);
        $footerTeam->setParent($companySection);
        $footerTeam->setTitle('Team');
        $footerTeam->setUrl('/about/team');
        $footerTeam->setSortOrder(20);
        $footerTeam->setActive(true);
        $manager->persist($footerTeam);

        $footerCareers = new MenuItem();
        $footerCareers->setMenu($footerMenu);
        $footerCareers->setParent($companySection);
        $footerCareers->setTitle('Careers');
        $footerCareers->setUrl('/careers');
        $footerCareers->setSortOrder(30);
        $footerCareers->setActive(true);
        $manager->persist($footerCareers);

        $footerContact = new MenuItem();
        $footerContact->setMenu($footerMenu);
        $footerContact->setParent($companySection);
        $footerContact->setTitle('Contact');
        $footerContact->setUrl('/contact');
        $footerContact->setSortOrder(40);
        $footerContact->setActive(true);
        $manager->persist($footerContact);

        $manager->flush();
    }
}
