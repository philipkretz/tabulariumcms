<?php

namespace App\DataFixtures;

use App\Entity\SiteSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SmtpSettingsFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $smtpSettings = [
            [
                "key" => "smtp_host",
                "name" => "SMTP Host",
                "value" => "localhost",
                "type" => "string",
                "category" => "email",
                "description" => "SMTP server hostname (e.g., smtp.gmail.com, mail.example.com)"
            ],
            [
                "key" => "smtp_port",
                "name" => "SMTP Port",
                "value" => "587",
                "type" => "integer",
                "category" => "email",
                "description" => "SMTP server port (usually 587 for TLS or 465 for SSL)"
            ],
            [
                "key" => "smtp_username",
                "name" => "SMTP Username",
                "value" => "",
                "type" => "string",
                "category" => "email",
                "description" => "SMTP authentication username (usually your email address)"
            ],
            [
                "key" => "smtp_password",
                "name" => "SMTP Password",
                "value" => "",
                "type" => "string",
                "category" => "email",
                "description" => "SMTP authentication password or app-specific password"
            ],
            [
                "key" => "smtp_encryption",
                "name" => "SMTP Encryption",
                "value" => "tls",
                "type" => "string",
                "category" => "email",
                "description" => "Encryption method: tls, ssl, or leave empty for none"
            ],
            [
                "key" => "smtp_from_email",
                "name" => "From Email Address",
                "value" => "noreply@example.com",
                "type" => "string",
                "category" => "email",
                "description" => "Default sender email address for outgoing emails"
            ],
            [
                "key" => "smtp_from_name",
                "name" => "From Name",
                "value" => "TabulariumCMS",
                "type" => "string",
                "category" => "email",
                "description" => "Default sender name for outgoing emails"
            ],
        ];

        foreach ($smtpSettings as $settingData) {
            // Check if setting already exists
            $existingSetting = $manager->getRepository(SiteSettings::class)
                ->findOneBy(["settingKey" => $settingData["key"]]);

            if (!$existingSetting) {
                $setting = new SiteSettings();
                $setting->setSettingKey($settingData["key"]);
                $setting->setSettingName($settingData["name"]);
                $setting->setSettingValue($settingData["value"]);
                $setting->setSettingType($settingData["type"]);
                $setting->setCategory($settingData["category"]);
                $setting->setDescription($settingData["description"]);
                $setting->setLocale("global");
                $setting->setPublic(false);

                $manager->persist($setting);
            }
        }

        $manager->flush();
    }
}
