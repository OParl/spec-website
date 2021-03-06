<?php

class RepositoryTest extends TestCase
{
    protected $remoteURI = 'tests/assets/test.git';

    protected $localName = 'test';

    /**
     * @var \App\Services\HubSync\Repository
     */
    protected $repo;

    public function setUp(): void
    {
        parent::setUp();

        $fs = $this->app->make(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $this->repo = new \App\Services\HubSync\Repository($fs, $this->localName, $this->remoteURI);
    }

    public function testGetRemoteURI()
    {
        $this->assertEquals($this->remoteURI, $this->repo->getRemoteURI());
    }

    public function testGetLocalName()
    {
        $this->assertEquals($this->localName, $this->repo->getLocalName());
    }

    public function testGetStoragePath()
    {
        $this->assertEquals('hub_sync/'.$this->localName, $this->repo->getPath());
    }

    public function testGetAbsolutePath()
    {
        $this->assertStringStartsWith('/', $this->repo->getAbsolutePath());
        $this->assertStringEndsWith('storage/app/hub_sync/'.$this->localName, $this->repo->getAbsolutePath());
    }

    public function testNoRepoBeforeUpdate()
    {
        $this->assertFileNotExists($this->repo->getAbsolutePath());
    }

    /**
     * @depends testNoRepoBeforeUpdate
     */
    public function testFirstUpdateClones()
    {
        $this->repo->update();

        $this->assertFileExists($this->repo->getAbsolutePath());
    }

    /**
     * @depends testFirstUpdateClones
     */
    public function testSecondUpdateRebases()
    {
        $this->repo->update();

        // This test just has to pass without exceptions
        $this->addToAssertionCount(1);
    }

    /**
     * @depends testSecondUpdateRebases
     */
    public function testRemoveRemoves()
    {
        $this->repo->remove();

        $this->assertFileNotExists($this->repo->getAbsolutePath());
    }

    public function testCleanRemovesUntracked()
    {
        // ensure unclean state
        @file_put_contents($this->repo->getAbsolutePath().'/removable_testfile', 'content');

        $this->repo->clean();

        $this->assertFileNotExists($this->repo->getAbsolutePath().'/removable_testfile');
    }
}
