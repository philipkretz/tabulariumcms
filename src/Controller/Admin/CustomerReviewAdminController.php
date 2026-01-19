<?php

namespace App\Controller\Admin;

use App\Repository\CustomerReviewRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class CustomerReviewAdminController extends CRUDController
{
    public function deleteAllAction(Request $request, CustomerReviewRepository $reviewRepository): RedirectResponse
    {
        $this->admin->checkAccess('delete');

        if ($request->isMethod('POST')) {
            $count = $reviewRepository->deleteAll();

            $this->addFlash(
                'sonata_flash_success',
                sprintf('Successfully deleted %d customer reviews.', $count)
            );
        }

        return $this->redirectToList();
    }

    public function batchActionDeleteAll(ProxyQueryInterface $query, CustomerReviewRepository $reviewRepository): RedirectResponse
    {
        $this->admin->checkAccess('delete');

        $count = $reviewRepository->deleteAll();

        $this->addFlash(
            'sonata_flash_success',
            sprintf('Successfully deleted all %d customer reviews.', $count)
        );

        return $this->redirectToList();
    }
}
