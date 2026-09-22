<?php /** @noinspection ALL */ declare(strict_types=1);

namespace App\Controller\SerializerIgnore;

use Coala\RestControlBundle\Service\Serializer\AppSerializer;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Validator\Doctrine\EntityExists;

class Employer {}
class Workflow {}

class CommandWithSubjectId
{
    public int $employerId;
    public string $firstName;
}

class CommandWithSubjectObject
{
    public function __construct(
        public Employer $employer,
    ) {}
}

class CommandWithTypedProperty
{
    public ?Employer $owner = null;
}

class CommandWithSetter
{
    private ?Employer $employer = null;

    public function setEmployer(Employer $employer): void
    {
        $this->employer = $employer;
    }
}

class CommandWithTwoSubjects
{
    #[Ignore]
    public int $workflowId;
    public int $employerId;
}

class CommandWiredByConstructor
{
    public function __construct(
        #[Ignore]
        public int $workflowId,
        public int $baseFunctionId,
        public int $relationId,
    ) {}
}

class CommandWithEntityExists
{
    #[EntityExists(entityClass: Workflow::class)]
    public int $baseWorkflowId;

    #[EntityExists(Employer::class)]
    public int $someOtherId;
}

abstract class BaseController
{
    public function __construct(
        protected readonly AppSerializer $serializer,
    ) {}

    abstract protected function handle(object $message): object;
}

class SubjectIdWithoutIgnore extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        $message = $this->serializer->deserializeIntoExistingObject($content, new CommandWithSubjectId()); // @error iwfWeb.serializerIsGrantedSubjectIgnore

        return $this->handle($message);
    }
}

class PromotedSubjectWithoutIgnore extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserializeIntoExistingObject($content, new CommandWithSubjectObject($employer))); // @error iwfWeb.serializerIsGrantedSubjectIgnore
    }
}

class TypedPropertyWithoutIgnore extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        $command = new CommandWithTypedProperty();
        $command->owner = $employer;
        $command = $this->serializer->deserializeIntoExistingObject($content, $command); // @error iwfWeb.serializerIsGrantedSubjectIgnore

        return $this->handle($command);
    }
}

class PrivateWithSetterWithoutIgnore extends BaseController
{
    #[IsGranted('EMPLOYEE_UPDATE', 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        return $this->handle($this->serializer->deserialize($content, CommandWithSetter::class)); // @error iwfWeb.serializerIsGrantedSubjectIgnore
    }
}

class ArraySubjectPartiallyIgnored extends BaseController
{
    #[IsGranted('DECLARATION_EDIT', subject: ['workflow', 'portalSubject' => 'employer'])]
    public function __invoke(string $content, Workflow $workflow, Employer $employer): object
    {
        $message = $this->serializer->deserializeIntoExistingObject($content, new CommandWithTwoSubjects()); // @error iwfWeb.serializerIsGrantedSubjectIgnore

        return $this->handle($message);
    }
}

class OverwriteAfterDeserializeStillFlagged extends BaseController
{
    #[IsGranted('EMPLOYEE_CREATE', subject: 'employer')]
    public function __invoke(string $content, Employer $employer): object
    {
        $message = $this->serializer->deserializeIntoExistingObject($content, new CommandWithSubjectId()); // @error iwfWeb.serializerIsGrantedSubjectIgnore
        $message->employerId = 1;

        return $this->handle($message);
    }
}

class ConstructorWiredDivergentNames extends BaseController
{
    #[IsGranted('DECLARATION_EDIT', subject: ['workflow', 'portalSubject' => 'employer'])]
    public function __invoke(string $content, Workflow $workflow, Employer $employer): object
    {
        $command = new CommandWiredByConstructor($workflow->getId(), $employer->getId(), relationId: $employer?->getId());
        $command = $this->serializer->deserializeIntoExistingObject($content, $command); // @error iwfWeb.serializerIsGrantedSubjectIgnore @error iwfWeb.serializerIsGrantedSubjectIgnore

        return $this->handle($command);
    }
}

class AssignmentWiredDivergentName extends BaseController
{
    #[IsGranted('DECLARATION_EDIT', subject: 'workflow')]
    public function __invoke(string $content, Workflow $workflow): object
    {
        $command = new CommandWithSubjectId();
        $command->firstName = (string) $workflow->getId();
        $command = $this->serializer->deserializeIntoExistingObject($content, $command); // @error iwfWeb.serializerIsGrantedSubjectIgnore

        return $this->handle($command);
    }
}

class EntityExistsDivergentNames extends BaseController
{
    #[IsGranted('DECLARATION_EDIT', subject: ['workflow', 'employer'])]
    public function __invoke(string $content, Workflow $workflow, Employer $employer): object
    {
        return $this->handle($this->serializer->deserialize($content, CommandWithEntityExists::class)); // @error iwfWeb.serializerIsGrantedSubjectIgnore @error iwfWeb.serializerIsGrantedSubjectIgnore
    }
}
