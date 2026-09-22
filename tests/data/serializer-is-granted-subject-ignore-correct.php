<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\SerializerIgnoreCorrect;

use Coala\RestControlBundle\Service\Serializer\AppSerializer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Validator\Doctrine\EntityExists;

class Employer {}

class IgnoredSubjectIdCommand
{
    #[Ignore]
    public int $employerId;
    public string $firstName;
}

class IgnoredPromotedCommand
{
    public function __construct(
        #[Ignore]
        public Employer $employer,
    ) {}
}

class LegacyAnnotationIgnoreCommand
{
    #[\Symfony\Component\Serializer\Annotation\Ignore]
    public int $employerId;
}

class UnrelatedCommand
{
    public string $firstName;
    public int $foundationId;
}

class PrivateNoSetterCommand
{
    private ?Employer $employer = null;
}

class Foundation {}

class ConstructorWiredIgnoredCommand
{
    public function __construct(
        #[Ignore]
        public int $baseId,
        public string $label,
    ) {}
}

class EntityExistsOtherEntityCommand
{
    #[EntityExists(entityClass: Foundation::class)]
    public int $foundationId;
}

abstract class BaseController
{
    public function __construct(
        protected readonly AppSerializer $serializer,
        protected readonly object $otherService,
    ) {}

    abstract protected function handle(object $message): object;
}

class IgnoredSubjectId extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new IgnoredSubjectIdCommand()));
    }
}

class IgnoredPromoted extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new IgnoredPromotedCommand($employer)));
    }
}

class LegacyAnnotationIgnore extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserialize($content, LegacyAnnotationIgnoreCommand::class));
    }
}

class NoSubjectLinkedProperty extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new UnrelatedCommand()));
    }
}

class PrivateWithoutSetter extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new PrivateNoSetterCommand()));
    }
}

class IsGrantedWithoutSubject extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new UnrelatedCommand()));
    }
}

class NoSerializerCall extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE', subject: 'employer')]
    public function __invoke(Employer $employer): object
    {
        $command = new IgnoredSubjectIdCommand();

        return $this->handle($command);
    }
}

class OtherServiceSameMethodName extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->otherService->deserializeIntoExistingObject($content, new UnrelatedCommand()));
    }
}

class ConstructorWiredIgnored extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer, string $label): object
    {
        $command = new ConstructorWiredIgnoredCommand($employer->getId(), $label);

        return $this->handle($this->serializer->deserializeIntoExistingObject($content, $command));
    }
}

class EntityExistsForUnrelatedEntity extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserialize($content, EntityExistsOtherEntityCommand::class));
    }
}
