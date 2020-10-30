<?php

namespace App\Controller\User;

use App\Command\CommandDispatcherTrait;
use App\Command\User\DeleteFrameworkAclCommand;
use App\Entity\Framework\LsDoc;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Entity\User\User;
use App\Entity\User\UserDocAcl;
use App\Form\Type\AddAclUsernameType;
use App\Form\Type\AddAclUserType;
use App\Command\User\AddFrameworkUserAclCommand;
use App\Command\User\AddFrameworkUsernameAclCommand;
use App\Form\DTO\AddAclUserDTO;
use App\Form\DTO\AddAclUsernameDTO;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FrameworkAclController
 *
 * @Route("/cfdoc")
 */
class FrameworkAclController extends AbstractController
{
    use CommandDispatcherTrait;

    /**
     * @Route("/{id}/acl", methods={"GET", "POST"}, name="framework_acl_edit")
     * @Template()
     * @Security("is_granted('manage_editors', lsDoc)")
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, LsDoc $lsDoc)
    {
        $addAclUserDto = new AddAclUserDTO($lsDoc, UserDocAcl::DENY);
        $addOrgUserForm = $this->createForm(AddAclUserType::class, $addAclUserDto, [
            'lsDoc' => $lsDoc,
            'action' => $this->generateUrl('framework_acl_edit', ['id' => $lsDoc->getId()]),
            'method' => 'POST',
        ]);
        $addAclUsernameDto = new AddAclUsernameDTO($lsDoc, UserDocAcl::ALLOW);
        $addUsernameForm = $this->createForm(AddAclUsernameType::class, $addAclUsernameDto);

        $addOrgUserForm->handleRequest($request);
        if ($ret = $this->handleOrgUserAdd($lsDoc, $addOrgUserForm)) {
            return $ret;
        }

        $addUsernameForm->handleRequest($request);
        if ($ret = $this->handleUsernameAdd($lsDoc, $addUsernameForm)) {
            return $ret;
        }

        $acls = $lsDoc->getDocAcls();
        /** @var \ArrayIterator $iterator */
        $iterator = $acls->getIterator();
        $iterator->uasort(function (UserDocAcl $a, UserDocAcl $b) {
            return strcasecmp($a->getUser()->getUsername(), $b->getUser()->getUsername());
        });
        $acls = new ArrayCollection(iterator_to_array($iterator));

        $deleteForms = [];
        foreach ($acls as $acl) {
            /* @var UserDocAcl $acl */
            $aclUser = $acl->getUser();
            $deleteForms[$aclUser->getId()] = $this->createDeleteForm($lsDoc, $aclUser)->createView();
        }

        $orgUsers = [];
        if ('organization' === $lsDoc->getOwnedBy()) {
            $orgUsers = $lsDoc->getOrg()->getUsers();
        }

        return [
            'lsDoc' => $lsDoc,
            'aclCount' => $acls->count(),
            'acls' => $acls,
            'orgUsers' => $orgUsers,
            'addOrgUserForm' => $addOrgUserForm->createView(),
            'addUsernameForm' => $addUsernameForm->createView(),
            'deleteForms' => $deleteForms,
        ];
    }

    /**
     * @return RedirectResponse|null
     */
    private function handleOrgUserAdd(LsDoc $lsDoc, FormInterface $addOrgUserForm): ?Response
    {
        if ($addOrgUserForm->isSubmitted() && $addOrgUserForm->isValid()) {
            $dto = $addOrgUserForm->getData();
            $command = new AddFrameworkUserAclCommand($dto);

            try {
                $this->sendCommand($command);

                return $this->redirectToRoute('framework_acl_edit', ['id' => $lsDoc->getId()]);
            } catch (UniqueConstraintViolationException $e) {
                $error = new FormError('The username is already in your exception list.');
                $error->setOrigin($addOrgUserForm);
                $addOrgUserForm->addError($error);
            } catch (\InvalidArgumentException $e) {
                $error = new FormError($e->getMessage());
                $error->setOrigin($addOrgUserForm);
                $addOrgUserForm->addError($error);
            } catch (\Exception $e) {
                $error = new FormError('Unknown Error');
                $error->setOrigin($addOrgUserForm);
                $addOrgUserForm->addError($error);
            }
        }

        return null;
    }

    /**
     * @return RedirectResponse|null
     */
    private function handleUsernameAdd(LsDoc $lsDoc, FormInterface $addUsernameForm): ?Response
    {
        if ($addUsernameForm->isSubmitted() && $addUsernameForm->isValid()) {
            $dto = $addUsernameForm->getData();
            $command = new AddFrameworkUsernameAclCommand($dto);

            try {
                $this->sendCommand($command);

                return $this->redirectToRoute('framework_acl_edit', ['id' => $lsDoc->getId()]);
            } catch (UniqueConstraintViolationException $e) {
                $error = new FormError('The username is already in your exception list.');
                $error->setOrigin($addUsernameForm);
                $addUsernameForm->addError($error);
            } catch (\InvalidArgumentException $e) {
                $error = new FormError($e->getMessage());
                $error->setOrigin($addUsernameForm);
                $addUsernameForm->addError($error);
            } catch (\Exception $e) {
                //$error = new FormError($e->getMessage().' '.get_class($e));
                $error = new FormError('Unknown Error');
                $error->setOrigin($addUsernameForm);
                $addUsernameForm->addError($error);
            }
        }

        return null;
    }

    /**
     * @Route("/{id}/acl/{targetUser}", methods={"DELETE"}, name="framework_acl_remove")
     * @Security("is_granted('manage_editors', lsDoc)")
     *
     * @return RedirectResponse
     */
    public function removeAclAction(Request $request, LsDoc $lsDoc, User $targetUser): Response
    {
        $form = $this->createDeleteForm($lsDoc, $targetUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new DeleteFrameworkAclCommand($lsDoc, $targetUser);
            $this->sendCommand($command);
        }

        return $this->redirectToRoute('framework_acl_edit', ['id' => $lsDoc->getId()]);
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $targetUser The user entity
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(LsDoc $lsDoc, User $targetUser): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('framework_acl_remove', ['id' => $lsDoc->getId(), 'targetUser' => $targetUser->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
