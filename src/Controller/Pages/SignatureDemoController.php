<?php

namespace Wexample\SymfonySignatureDemo\Controller\Pages;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyMedia\Entity\MediaFolder;
use Wexample\SymfonyMedia\Service\MediaLibraryService;
use Wexample\SymfonyMedia\Service\MediaStorage;
use Wexample\SymfonySignature\Entity\Envelope;
use Wexample\SymfonySignature\Enum\EnvelopeSignerStatus;
use Wexample\SymfonySignature\Repository\EnvelopeRepository;
use Wexample\SymfonySignature\Routing\SignatureRoute;
use Wexample\SymfonySignature\Service\EnvelopeService;
use Wexample\SymfonySignature\Service\SignatureKeyService;
use Wexample\SymfonySignature\Service\SignerAccessService;
use Wexample\SymfonySignatureDemo\Traits\SymfonySignatureDemoBundleClassTrait;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;

/**
 * A sample envelope, sent by the signed-in demo account to itself, to the
 * admin account and to someone outside — whose signed link, sent by mail,
 * is shown here too, the demo having nobody outside to receive it.
 */
#[Route(path: '/signature-demo/', name: 'signature_demo_')]
final class SignatureDemoController extends AbstractPagesController
{
    use SymfonySignatureDemoBundleClassTrait;

    final public const string ROUTE_INDEX = VariableHelper::INDEX;

    /** The application's form token id, declared stateless already. */
    private const string CSRF_TOKEN_ID = 'submit';

    private const string OUTSIDE_EMAIL = 'client@example.org';

    private const string FOLDER = 'Signature demo';

    #[Route(path: '', name: self::ROUTE_INDEX)]
    public function index(EnvelopeRepository $repository, SignatureKeyService $keys, SignerAccessService $access): Response
    {
        $user = $this->getUser();
        $outsideLinks = [];

        if ($user) {
            foreach ($repository->findByOwner(...$keys->key($user)) as $envelope) {
                foreach ($envelope->getSigners() as $signer) {
                    if ($signer->isOutside() && $signer->getStatus() === EnvelopeSignerStatus::PENDING) {
                        $outsideLinks[] = ['title' => $envelope->getTitle(), 'url' => $access->getUrl($signer)];
                    }
                }
            }
        }

        return $this->renderPage(self::ROUTE_INDEX, [
            'csrf_token_id' => self::CSRF_TOKEN_ID,
            'outside_links' => $outsideLinks,
            'outside_email' => self::OUTSIDE_EMAIL,
        ]);
    }

    #[Route(path: 'sample', name: 'sample', methods: [Request::METHOD_POST])]
    public function sample(
        Request $request,
        EnvelopeService $envelopes,
        MediaStorage $storage,
        MediaLibraryService $library,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $user = $this->getUser();

        if (! $user || ! $this->isCsrfTokenValid(self::CSRF_TOKEN_ID, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $date = new DateTimeImmutable();
        $reference = 'DEMO-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $envelope = $envelopes->create(
            'Demo contract ' . $reference,
            $user,
            ['amount' => '1200.00 EUR', 'reference' => $reference],
        );

        $path = tempnam(sys_get_temp_dir(), 'signature-demo');
        file_put_contents($path, $this->renderView('@WexampleSymfonySignatureDemoBundle/pages/signature_demo/contract.txt.twig', ['date' => $date, 'reference' => $reference]));
        $envelopes->addDocument($envelope, $storage->import($path, 'contract-' . strtolower($reference) . '.txt', $this->getFolder($entityManager, $library)));

        // The signed-in account, the admin account, someone outside.
        $envelopes->addSigner($envelope, (string) $user->getUserIdentifier(), method_exists($user, 'getDisplayName') ? $user->getDisplayName() : $user->getUserIdentifier());

        if ($user->getUserIdentifier() !== DemoAccount::ADMIN->getEmail()) {
            $envelopes->addSigner($envelope, DemoAccount::ADMIN->getEmail(), 'Demo admin');
        }

        $envelopes->addSigner($envelope, self::OUTSIDE_EMAIL, 'Outside client');
        $envelopes->seal($envelope);

        return $this->redirectToRoute(SignatureRoute::ENVELOPE, ['id' => (string) $envelope->getId()]);
    }

    private function getFolder(EntityManagerInterface $entityManager, MediaLibraryService $library): MediaFolder
    {
        return $entityManager->getRepository(MediaFolder::class)->findOneBy(['name' => self::FOLDER, 'parent' => null])
            ?? $library->createFolder(self::FOLDER);
    }
}
