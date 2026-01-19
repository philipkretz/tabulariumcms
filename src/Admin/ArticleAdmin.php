<?php

namespace App\Admin;

use App\Entity\Article;
use App\Form\Type\GrapeJsEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper, ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, CheckboxType, IntegerType, TextType, TextareaType, MoneyType};
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ArticleAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $article = $this->getSubject();
        
        $form
            ->with("Product Information", ["class" => "col-md-8"])
                ->add("name", TextType::class)
                ->add("slug", TextType::class, ["help" => "URL-friendly identifier"])
                ->add("type", ChoiceType::class, [
                    "choices" => [
                        "Physical Product" => Article::TYPE_PHYSICAL,
                        "Bundle (Multiple Products)" => Article::TYPE_BUNDLE,
                        "Virtual/Download" => Article::TYPE_VIRTUAL,
                        "Room" => Article::TYPE_ROOM,
                        "Timeslot" => Article::TYPE_TIMESLOT,
                        "Ticket" => Article::TYPE_TICKET,
                    ],
                    "help" => "Physical: shipped items | Bundle: multiple products | Virtual: downloadable | Room: bookable space | Timeslot: time reservation | Ticket: event entry"
                ])
                ->add("sku", TextType::class, ["required" => false, "help" => "Stock Keeping Unit"])
                ->add("shortDescription", TextareaType::class, ["required" => false, "attr" => ["rows" => 3]])
                ->add("description", GrapeJsEditorType::class, [
                    "label" => "Full Description",
                    "editor_height" => "500px",
                    "required" => false
                ])
            ->end()
            ->with("Pricing & Stock", ["class" => "col-md-4"])
                ->add("netPrice", MoneyType::class, ["currency" => "EUR", "help" => "Price without tax"])
                ->add("taxRate", MoneyType::class, ["currency" => false, "help" => "Tax percentage (default 21%)"])
                ->add("stock", IntegerType::class, ["help" => "Available quantity"])
                ->add("ignoreStock", CheckboxType::class, ["required" => false, "help" => "Don't track stock for this article"])
                ->add("size", TextType::class, ["required" => false, "help" => "Product size (e.g., 'M', 'XL', '10x20cm')"])
                ->add("weight", TextType::class, ["required" => false, "help" => "Product weight in kg (e.g., '2.5')"])
            ->end()
            ->with("Logistics & Shipping", ["class" => "col-md-6"])
                ->add("isDangerousGoods", CheckboxType::class, ["required" => false, "help" => "Mark as dangerous goods (requires special handling)"])
                ->add("isOversizePackage", CheckboxType::class, ["required" => false, "help" => "Mark as oversize package (special shipping required)"])
                ->add("requiresSpecialDelivery", CheckboxType::class, ["required" => false, "help" => "Requires special delivery service"])
                ->add("packageAmount", IntegerType::class, ["help" => "Number of packages needed for this product"])
            ->end()
            ->with("Media", ["class" => "col-md-6"])
                ->add("mainImage", ModelType::class, ["class" => "App\Entity\Media", "property" => "filename", "required" => false, "help" => "Primary product image"])
                ->add("images", ModelType::class, ["class" => "App\Entity\Media", "property" => "filename", "required" => false, "multiple" => true, "help" => "Additional images"])
                ->add("videos", ModelType::class, ["class" => "App\Entity\Media", "property" => "filename", "required" => false, "multiple" => true, "help" => "Product videos"])
            ->end()
            ->with("Categories & Language", ["class" => "col-md-6"])
                ->add("category", ModelType::class, ["class" => "App\Entity\Category", "property" => "name", "required" => false])
                ->add("categoryPage", ModelType::class, ["class" => "App\Entity\Page", "property" => "title", "required" => false, "help" => "Category display page"])
                ->add("language", ModelType::class, ["class" => "App\Entity\Language", "property" => "name", "required" => false])
            ->end();

        if ($article && $article->getType() === Article::TYPE_VIRTUAL) {
            $form->with("Virtual Product", ["class" => "col-md-12"])
                ->add("downloadFile", ModelType::class, ["class" => "App\Entity\Media", "property" => "filename", "required" => false, "help" => "File sent to buyer via email"])
            ->end();
        }

        if ($article && $article->getType() === Article::TYPE_BUNDLE) {
            $form->with("Bundle Items", ["class" => "col-md-12"])
                ->add("bundleItems", ModelType::class, ["class" => "App\Entity\Article", "property" => "name", "required" => false, "multiple" => true, "help" => "Products included in this bundle"])
            ->end();
        }

        if (!$article || $article->getType() === Article::TYPE_PHYSICAL) {
            $form->with("Shipping", ["class" => "col-md-12"])
                ->add("shippingMethods", ModelType::class, ["class" => "App\Entity\ShippingMethod", "property" => "name", "required" => false, "multiple" => true])
            ->end();
        }

        if ($article && $article->getType() === Article::TYPE_ROOM) {
            $form->with("Room Details", ["class" => "col-md-12"])
                ->add("size", TextType::class, ["required" => false, "help" => "Room size/capacity (e.g., '50 sqm', '10 people')"])
            ->end();
        }

        if ($article && $article->getType() === Article::TYPE_TIMESLOT) {
            $form->with("Timeslot Details", ["class" => "col-md-12"])
                // Additional timeslot fields can be added here
            ->end();
        }

        if ($article && $article->getType() === Article::TYPE_TICKET) {
            $form->with("Ticket Details", ["class" => "col-md-12"])
                ->add("stock", IntegerType::class, ["help" => "Available tickets"])
            ->end();
        }

        $form
            ->with("Product Variants", ["class" => "col-md-12"])
                ->add("variants", CollectionType::class, [
                    "by_reference" => false,
                    "required" => false,
                    "label" => "Variants (Different sizes, amounts, colors)",
                    "btn_add" => "Add Variant",
                    "help" => "Define product variants like different sizes (S, M, L), amounts (500ml, 1L), or colors."
                ], [
                    "edit" => "inline",
                    "inline" => "table",
                ])
            ->end()
            ->with("Translations", ["class" => "col-md-12"])
                ->add("translations", CollectionType::class, [
                    "by_reference" => false,
                    "required" => false,
                    "label" => "Translations (Name, Description, SEO in multiple languages)",
                    "btn_add" => "Add Translation",
                    "help" => "Add translations for different languages. Each language should have unique content."
                ], [
                    "edit" => "inline",
                    "inline" => "table",
                ])
            ->end()
            ->with("Settings", ["class" => "col-md-12"])
                ->add("isActive", CheckboxType::class, ["required" => false])
                ->add("isFeatured", CheckboxType::class, ["required" => false, "help" => "Show in featured products"])
                ->add("allowComments", CheckboxType::class, ["required" => false, "help" => "Allow customers to leave comments and reviews on this product"])
                ->add("seller", ModelType::class, ["class" => "App\Entity\User", "property" => "email", "required" => false, "help" => "Multi-vendor seller"])
            ->end()
            ->with("Purchase Options", ["class" => "col-md-12"])
                ->add("isRequestOnly", CheckboxType::class, [
                    "required" => false,
                    "label" => "Request Only (Disable Direct Purchase)",
                    "help" => "Replace buy button with 'Request Product' button that sends inquiry email"
                ])
                ->add("requestEmail", TextType::class, [
                    "required" => false,
                    "label" => "Request Email Address",
                    "help" => "Email to receive product inquiries. Leave empty to use seller email or default site contact email.",
                    "attr" => ["placeholder" => "contact@example.com"]
                ])
            ->end()
            ->with("SEO", ["class" => "col-md-12"])
                ->add("metaTitle", TextType::class, ["required" => false])
                ->add("metaDescription", TextareaType::class, ["required" => false, "attr" => ["rows" => 3]])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier("name")
            ->add("type", "choice", ["choices" => [
                Article::TYPE_PHYSICAL => "Physical",
                Article::TYPE_BUNDLE => "Bundle",
                Article::TYPE_VIRTUAL => "Virtual",
                Article::TYPE_ROOM => "Room",
                Article::TYPE_TIMESLOT => "Timeslot",
                Article::TYPE_TICKET => "Ticket"
            ]])
            ->add("sku")
            ->add("grossPrice", "currency", ["currency" => "EUR"])
            ->add("stock")
            ->add("category")
            ->add("language")
            ->add("isActive", null, ["editable" => true])
            ->add("isFeatured", null, ["editable" => true])
            ->add(ListMapper::NAME_ACTIONS, null, [
                "actions" => ["show" => [], "edit" => [], "duplicate" => ["template" => "@App/admin/article/list__action_duplicate.html.twig"], "delete" => []]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add("name")
            ->add("type")
            ->add("sku")
            ->add("category")
            ->add("language")
            ->add("isActive")
            ->add("isFeatured")
            ->add("seller");
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add("id")
            ->add("name")
            ->add("slug")
            ->add("type")
            ->add("sku")
            ->add("shortDescription")
            ->add("description", "html")
            ->add("netPrice")
            ->add("taxRate")
            ->add("grossPrice")
            ->add("stock")
            ->add("mainImage")
            ->add("category")
            ->add("language")
            ->add("seller")
            ->add("isActive")
            ->add("isFeatured")
            ->add("createdAt")
            ->add("updatedAt");
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add("duplicate", $this->getRouterIdParameter()."/duplicate");
    }

    public function duplicateAction($id): RedirectResponse
    {
        $object = $this->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException(sprintf("Unable to find Article with id: %s", $id));
        }

        $em = $this->getModelManager()->getEntityManager($object);

        // Create a new article with copied data
        $duplicate = new Article();
        $duplicate->setName($object->getName() . " (Copy)");
        $duplicate->setSlug($object->getSlug() . "-copy-" . time());
        $duplicate->setShortDescription($object->getShortDescription());
        $duplicate->setDescription($object->getDescription());
        $duplicate->setNetPrice($object->getNetPrice());
        $duplicate->setTaxRate($object->getTaxRate());

        $duplicate->setStock(0); // Reset stock for duplicate
        $duplicate->setSku($object->getSku() ? $object->getSku() . "-COPY" : null);
        $duplicate->setType($object->getType());

        // Copy media
        $duplicate->setMainImage($object->getMainImage());
        foreach ($object->getImages() as $image) {
            $duplicate->addImage($image);
        }
        foreach ($object->getVideos() as $video) {
            $duplicate->addVideo($video);
        }

        // Copy dimensions and logistics
        $duplicate->setSize($object->getSize());
        $duplicate->setWeight($object->getWeight());
        $duplicate->setIsDangerousGoods($object->getIsDangerousGoods());
        $duplicate->setIsOversizePackage($object->getIsOversizePackage());
        $duplicate->setRequiresSpecialDelivery($object->getRequiresSpecialDelivery());
        $duplicate->setPackageAmount($object->getPackageAmount());

        // Copy SEO fields
        $duplicate->setMetaTitle($object->getMetaTitle());
        $duplicate->setMetaDescription($object->getMetaDescription());

        $duplicate->setIsActive(false); // Set to inactive by default
        $duplicate->setIsFeatured(false);
        $duplicate->setCategory($object->getCategory());
        $duplicate->setCategoryPage($object->getCategoryPage());
        $duplicate->setLanguage($object->getLanguage());
        $duplicate->setSeller($object->getSeller());
        $duplicate->setIsRequestOnly($object->isRequestOnly());
        $duplicate->setRequestEmail($object->getRequestEmail());

        // Copy virtual product download file
        if ($object->getDownloadFile()) {
            $duplicate->setDownloadFile($object->getDownloadFile());
        }

        // Copy bundle items
        foreach ($object->getBundleItems() as $bundleItem) {
            $duplicate->addBundleItem($bundleItem);
        }

        // Copy shipping methods
        foreach ($object->getShippingMethods() as $shippingMethod) {
            $duplicate->addShippingMethod($shippingMethod);
        }

        $em->persist($duplicate);

        // Copy variants
        foreach ($object->getVariants() as $variant) {
            $variantClone = new \App\Entity\ArticleVariant();
            $variantClone->setArticle($duplicate);
            $variantClone->setName($variant->getName());
            $variantClone->setValue($variant->getValue());
            $variantClone->setSku($variant->getSku() ? $variant->getSku() . "-COPY" : null);
            $variantClone->setNetPrice($variant->getNetPrice());
            $variantClone->setStock($variant->getStock());
            $variantClone->setIsActive($variant->isActive());
            $em->persist($variantClone);
        }

        // Copy translations
        foreach ($object->getTranslations() as $translation) {
            $translationClone = new \App\Entity\ArticleTranslation();
            $translationClone->setArticle($duplicate);
            $translationClone->setLanguage($translation->getLanguage());
            $translationClone->setName($translation->getName());
            $translationClone->setShortDescription($translation->getShortDescription());
            $translationClone->setDescription($translation->getDescription());
            $translationClone->setMetaTitle($translation->getMetaTitle());
            $translationClone->setMetaDescription($translation->getMetaDescription());
            $em->persist($translationClone);
        }

        $em->flush();

        $this->addFlash("sonata_flash_success", sprintf(
            "Article duplicated successfully with %d variants and %d translations!",
            count($object->getVariants()),
            count($object->getTranslations())
        ));

        return new RedirectResponse($this->generateUrl("list"));
    }

    public function toString(object $object): string
    {
        return $object instanceof Article ? $object->getName() ?? "Article" : "Article";
    }
}
