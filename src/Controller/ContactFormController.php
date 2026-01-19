<?php
namespace App\Controller;

use App\Entity\{ContactForm, ContactFormSubmission};
use App\Repository\{ContactFormRepository, SiteSettingsRepository};
use App\Security\CartSessionValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactFormController extends AbstractController
{
    #[Route("/contact/submit/{identifier}", name: "contact_form_submit", methods: ["POST"])]
    public function submit(
        string $identifier,
        Request $request,
        ContactFormRepository $formRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        SiteSettingsRepository $settingsRepository,
        CartSessionValidator $validator
    ): JsonResponse {
        // Validate origin
        if (!$validator->validateOrigin($request)) {
            return $this->json(["error" => "Invalid request origin"], 403);
        }

        // Rate limiting: max 5 contact form submissions per hour per IP
        $clientIp = $request->getClientIp();
        if (!$validator->checkRateLimit('contact_' . $clientIp, 5, 3600)) {
            return $this->json(["error" => "Too many submissions. Please try again later."], 429);
        }

        // Check honeypot field
        $honeypot = $request->request->get('website');
        if (!$validator->validateHoneypot($honeypot)) {
            error_log('Spam attempt detected in contact form from IP: ' . $clientIp);
            return $this->json(["error" => "Your submission was flagged as spam"], 400);
        }

        $form = $formRepository->findOneBy(["identifier" => $identifier, "isActive" => true]);

        if (!$form) {
            return $this->json(["error" => "Form not found"], 404);
        }

        $data = $request->request->all();
        $errors = [];

        // Sanitize all inputs
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $validator->sanitizeInput($value, true);

                // Check for SQL injection patterns
                if ($validator->hasSqlInjectionPatterns($data[$key])) {
                    error_log("SQL injection attempt in contact form field $key from IP: " . $clientIp);
                    return $this->json(["error" => "Invalid characters detected in your input"], 400);
                }

                // Check for spam
                if (strlen($data[$key]) > 20 && $validator->isSpam($data[$key])) {
                    error_log("Spam detected in contact form field $key from IP: " . $clientIp);
                    return $this->json(["error" => "Your submission appears to contain spam"], 400);
                }
            }
        }

        // Validate fields
        foreach ($form->getFields() as $field) {
            if ($field->isRequired() && empty($data[$field->getName()])) {
                $errors[$field->getName()] = "This field is required";
            }

            if ($field->getType() === "email" && !empty($data[$field->getName()])) {
                if (!filter_var($data[$field->getName()], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field->getName()] = "Invalid email address";
                }
            }
        }

        if (!empty($errors)) {
            return $this->json(["errors" => $errors], 400);
        }

        // Create submission
        $submission = new ContactFormSubmission();
        $submission->setForm($form);
        $submission->setData($data);
        $submission->setIpAddress($request->getClientIp());
        $submission->setUserAgent($request->headers->get("User-Agent"));

        $em->persist($submission);
        $em->flush();

        // Send email notification
        if ($form->isSendEmail()) {
            try {
                $adminEmail = $settingsRepository->findOneBy(["settingKey" => "admin_email"])?->getSettingValue() ?? "admin@example.com";
                $adminName = $settingsRepository->findOneBy(["settingKey" => "admin_name"])?->getSettingValue() ?? "Administrator";

                $emailBody = "New contact form submission:\n\n";
                foreach ($data as $key => $value) {
                    $emailBody .= ucfirst($key) . ": " . $value . "\n";
                }

                $email = (new Email())
                    ->from("noreply@" . $request->getHost())
                    ->to($adminEmail)
                    ->subject("New submission: " . $form->getName())
                    ->text($emailBody);

                $mailer->send($email);
            } catch (\Exception $e) {
                // Log error but don't fail the submission
            }
        }

        return $this->json([
            "success" => true,
            "message" => $form->getSuccessMessage()
        ]);
    }
}