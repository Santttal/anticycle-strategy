<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class AbstractFunctionalTest extends BaseWebTestCase
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;
    /**
     * @var array
     */
    private $fixturesEntityManagers = [];
    /**
     * @var array
     */
    protected $fixtureInstances = [];
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->em = $this->loadEntityManager(EntityManagerInterface::class);
        $this->referenceRepository = $this->loadFixtures($this->em, $this->getFixtures())->getReferenceRepository();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->em->rollback();
        unset($this->em, $this->referenceRepository, $this->fixtureInstances);

        parent::tearDown();
    }

    /**
     * Get array of fixtures FQCNs.
     *
     * @return string[]
     */
    abstract protected function getFixtures(): array;

    /**
     * @throws \InvalidArgumentException
     */
    protected function loadEntityManager(string $serviceName): EntityManagerInterface
    {
        $em = self::$container->get($serviceName);
        if (!$em instanceof EntityManagerInterface) {
            $eMessage = sprintf('%s is not instance of %s', $serviceName, EntityManagerInterface::class);
            throw new \InvalidArgumentException($eMessage);
        }

        if (!$em->getConnection()->isTransactionActive()) {
            $em->beginTransaction();
        }

        return $em;
    }

    protected function getReference(string $name)
    {
        return $this->referenceRepository->getReference($name);
    }

    public function loadFixtures(EntityManagerInterface $defaultEm, array $fixtures): ORMExecutor
    {
        $loader = new Loader();

        foreach ($fixtures as $fixtureEntityManager => $fixture) {
            $this->loadFixtureClass($fixture, $fixtureEntityManager, $loader);
        }

        $executor = new ORMExecutor($defaultEm);

        foreach ($loader->getFixtures() as $fixture) {
            $executor->load($this->em, $fixture);
        }

        return $executor;
    }

    /**
     * @param mixed $managerName
     */
    private function loadFixtureClass(string $fixtureName, $managerName, Loader $loader): void
    {
        $fixtureInstance = new $fixtureName();
        $this->fixturesEntityManagers[$fixtureName] = $managerName;
        $this->fixtureInstances[$fixtureName] = $fixtureInstance;

        if (!$loader->hasFixture($fixtureInstance)) {
            $loader->addFixture($fixtureInstance);
        }

        if ($fixtureInstance instanceof DependentFixtureInterface) {
            foreach ($fixtureInstance->getDependencies() as $fixtureEntityManager => $dependency) {
                $this->loadFixtureClass($dependency, $fixtureEntityManager, $loader);
            }
        }
    }
}
