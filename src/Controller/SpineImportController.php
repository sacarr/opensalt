<?php

namespace App\Controller;

use App\Command\CommandDispatcherTrait;
use App\Command\Import\ImportSpineCommand;
use App\Entity\User\User;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class SpineImportController extends AbstractController
{
    use CommandDispatcherTrait;

    /**
     * @Route("/salt/spine/import/skills", methods={"POST"}, name="spine_import_skills")
     * @Security("is_granted('create', 'lsdoc')")
     *
     * @return Response
     */
    public function importSpineSkills(Request $request, UserInterface $user): Response
    {
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException();
        }
        $file = $request->files->get('file');
        $path = $file->getRealPath();

        $command = new ImportSpineCommand($path, "skills", null, $user->getOrg());
        $this->sendCommand($command);

        return new Response('OK', Response::HTTP_OK);
    }

    /**
     * @Route("/salt/spine/import/standards", methods={"POST"}, name="spine_import_standards")
     * @Security("is_granted('create', 'lsdoc')")
     *
     * @return Response
     */
    public function importSpineStandards(Request $request, UserInterface $user): Response
    {
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException();
        }
        $file = $request->files->get('file');
        $path = $file->getRealPath();

        $command = new ImportSpineCommand($path, "standards", null, $user->getOrg());
        $this->sendCommand($command);

        return new Response('OK', Response::HTTP_OK);
    }

    /**
     * @Route("/salt/spine/import/spine", methods={"POST"}, name="spine_import_spine")
     * @Security("is_granted('create', 'lsdoc')")
     *
     * @return Response
     */
    public function importSpine(Request $request, UserInterface $user): Response
    {
        if (!($user instanceof User)) {
            throw $this->createAccessDeniedException();
        }
        $file = $request->files->get('file');
        $path = $file->getRealPath();

        $command = new ImportSpineCommand($path, "spine", null, $user->getOrg());
        $this->sendCommand($command);

        return new Response('OK', Response::HTTP_OK);
    }

}
