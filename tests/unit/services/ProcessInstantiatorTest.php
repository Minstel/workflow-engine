<?php

use Jasny\EventDispatcher\EventDispatcher;

/**
 * @covers ProcessInstantiator
 */
class ProcessInstantiatorTest extends \Codeception\Test\Unit
{
    protected function createScenario(): Scenario
    {
        $scenario = new Scenario();
        $scenario->title = 'Do the test';

        $scenario->actors['manager'] = new JsonSchema([
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'title' => 'Manager',
            'description' => 'The manager of the organization',
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'email' => ['type' => 'string', 'format' => 'email'],
            ]
        ]);
        $scenario->actors['client'] = new JsonSchema([
            'title' => 'Client', 
            'type' => 'object',
            'signkeys' => [
                '57FWtEbXoMKXj71FT84hcvCxN5z1CztbZ8UYJ2J49Gcn'
            ],
            'identity' => '6uk7288s-afe4-7398-8dbh-7914ffd930pl'
        ]);

        $scenario->states[':initial'] = new State();
        $scenario->states['step1'] = new State();
        $scenario->states['step2'] = new State();

        $scenario->assets['doc'] = new JsonSchema([
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'title' => 'Document',
            'description' => 'The final document',
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'content' => ['type' => 'string'],
            ],
        ]);
        $scenario->assets['book'] = new JsonSchema(['title' => 'Book', 'type' => 'object']);
        $scenario->assets['attachments'] = new JsonSchema([
            'title' => 'Attachments',
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'files' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'url' => ['type' => 'string', 'format' => 'uri'],
                        ],
                    ],
                ],
            ],
        ]);

        $scenario->definitions['d1'] = Asset::fromData(['I' => 'one', 'II' => 'two']);
        $scenario->definitions['d2'] = Asset::fromData(['A' => 'alpha', 'B' => 'Beta']);

        return $scenario;
    }

    public function testInstantiate(): void
    {
        $scenario = $this->createScenario();

        $dispatcher = $this->createMock(EventDispatcher::class);
        $process = new Process();
        $process->setDispatcher($dispatcher);

        $processGateway = $this->createMock(ProcessGateway::class);
        $processGateway->expects($this->once())->method('create')->willReturn($process);

        $currentState = $this->createMock(CurrentState::class);
        $stateInstatiator = $this->createMock(StateInstantiator::class);
        $stateInstatiator->expects($this->once())->method('instantiate')
            ->with($scenario->states[':initial'])->willReturn($currentState);

        $dispatcher->expects($this->once())->method('trigger')
            ->with('instantiate', $this->identicalTo($process));

        $identity = $this->createMock(Identity::class);
        $identityGateway = $this->createMock(IdentityGateway::class);
        $identityGateway->expects($this->once())->method('fetch')
            ->with('6uk7288s-afe4-7398-8dbh-7914ffd930pl')->willReturn($identity);

        $instantiator = new ProcessInstantiator($processGateway, $stateInstatiator, $identityGateway);

        $process = $instantiator->instantiate($scenario);

        $this->assertAttributeSame($scenario, 'scenario', $process);
        $this->assertAttributeEquals('Do the test', 'title', $process);

        $this->assertActors($process->actors, $identity);
        $this->assertAssets($process->assets);

        // Definitions should be a clone from scenario
        $this->assertAttributeEquals($scenario->definitions, 'definitions', $process);
        $this->assertNotSame($scenario->definitions->getArrayCopy(), $process->definitions->getArrayCopy());

        $this->assertAttributeSame($currentState, 'current', $process);
    }

    /**
     * Test 'instantiate' method, if identity is not found
     *
     * @expectedException Exception
     * @expectedExceptionMessage Identity with id 6uk7288s-afe4-7398-8dbh-7914ffd930pl not found
     */
    public function testInstantiateIdentityNotFound()
    {
        $scenario = $this->createScenario();

        $dispatcher = $this->createMock(EventDispatcher::class);
        $process = new Process();
        $process->setDispatcher($dispatcher);

        $processGateway = $this->createMock(ProcessGateway::class);
        $processGateway->expects($this->once())->method('create')->willReturn($process);

        $stateInstatiator = $this->createMock(StateInstantiator::class);

        $dispatcher->expects($this->never())->method('trigger');

        $identityGateway = $this->createMock(IdentityGateway::class);
        $identityGateway->expects($this->once())->method('fetch')
            ->with('6uk7288s-afe4-7398-8dbh-7914ffd930pl')->willReturn(null);

        $instantiator = new ProcessInstantiator($processGateway, $stateInstatiator, $identityGateway);

        $instantiator->instantiate($scenario);
    }

    /**
     * @param AssocEntitySet&iterable<Actor> $actors
     */
    protected function assertActors($actors, Identity $identity): void
    {
        $this->assertInstanceOf(AssocEntitySet::class, $actors);
        $this->assertEquals(Actor::class, $actors->getEntityClass());
        $this->assertCount(2, $actors);

        $this->assertArrayHasKey('manager', $actors->getArrayCopy());
        $this->assertInstanceOf(Actor::class, $actors['manager']);
        $this->assertAttributeEquals('Manager', 'title', $actors['manager']);
        $this->assertAttributeEquals('The manager of the organization', 'description', $actors['manager']);
        $this->assertAttributeSame(null, 'name', $actors['manager']);
        $this->assertAttributeSame(null, 'email', $actors['manager']);
        $this->assertAttributeSame(null, 'identity', $actors['manager']);

        $this->assertArrayHasKey('client', $actors->getArrayCopy());
        $this->assertInstanceOf(Actor::class, $actors['client']);
        $this->assertAttributeEquals('Client', 'title', $actors['client']);
        $this->assertAttributeEquals(['57FWtEbXoMKXj71FT84hcvCxN5z1CztbZ8UYJ2J49Gcn'], 'signkeys', $actors['client']);
        $this->assertAttributeEquals($identity, 'identity', $actors['client']);
    }

    /**
     * @param AssetSet&iterable<Asset> $assets
     */
    protected function assertAssets($assets): void
    {
        $this->assertInstanceOf(AssocEntitySet::class, $assets);
        $this->assertEquals(Asset::class, $assets->getEntityClass());
        $this->assertCount(3, $assets);

        $this->assertArrayHasKey('doc', $assets->getArrayCopy());
        $this->assertInstanceOf(Asset::class, $assets['doc']);
        $this->assertAttributeEquals('Document', 'title', $assets['doc']);
        $this->assertAttributeEquals('The final document', 'description', $assets['doc']);
        $this->assertAttributeSame(null, 'content', $assets['doc']);

        $this->assertArrayHasKey('book', $assets->getArrayCopy());
        $this->assertInstanceOf(Asset::class, $assets['book']);
        $this->assertAttributeEquals('Book', 'title', $assets['book']);

        $this->assertArrayHasKey('attachments', $assets->getArrayCopy());
        $this->assertInstanceOf(Asset::class, $assets['attachments']);
        $this->assertAttributeEquals('Attachments', 'title', $assets['attachments']);
        $this->assertAttributeSame([], 'files', $assets['attachments']);
    }
}
