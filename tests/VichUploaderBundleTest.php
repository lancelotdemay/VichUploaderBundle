<?php

namespace Vich\UploaderBundle\Tests;

use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\FlysystemStorage;
use Vich\UploaderBundle\Tests\Kernel\FilesystemAppKernel;
use Vich\UploaderBundle\Tests\Kernel\FlysystemOfficialAppKernel;
use Vich\UploaderBundle\Tests\Kernel\FlysystemOneUpAppKernel;
use Vich\UploaderBundle\Tests\Kernel\SimpleAppKernel;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
final class VichUploaderBundleTest extends TestCase
{
    public function testSimpleKernel(): void
    {
        $kernel = new SimpleAppKernel('test', true);
        $kernel->boot();

        $this->assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());
        $this->assertInstanceOf(UploadHandler::class, $kernel->getContainer()->get('vich_uploader.upload_handler'));
    }

    public function testFilesystemKernel(): void
    {
        $kernel = new FilesystemAppKernel('test', true);
        $kernel->boot();

        $this->assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());
        $this->assertInstanceOf(UploadHandler::class, $kernel->getContainer()->get('vich_uploader.upload_handler'));
    }

    public function testFlysystemOfficialKernel(): void
    {
        $kernel = new FlysystemOfficialAppKernel('test', true);
        $kernel->boot();

        $this->assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());

        // Test the upload
        /** @var FilesystemInterface $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        $this->assertFalse($filesystem->has('filename.txt'));

        /** @var FlysystemStorage $storage */
        $storage = $kernel->getContainer()->get('test.vich_uploader.storage');
        $this->assertInstanceOf(FlysystemStorage::class, $storage);

        $object = new DummyEntity();

        $mapping = $this->getPropertyMappingMock();

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($object)
            ->willReturn($this->createUploadedFile());

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('uploads.storage');

        $mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($object)
            ->willReturn('filename.txt');

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($object)
            ->willReturn('');

        $storage->upload($object, $mapping);

        /** @var FilesystemInterface $filesystem */
        $filesystem = $kernel->getContainer()->get('test.uploads.storage');
        $this->assertTrue($filesystem->has('filename.txt'));
    }

    public function testFlysystemOneUpKernel(): void
    {
        $kernel = new FlysystemOneUpAppKernel('test', true);
        $kernel->boot();

        $this->assertArrayHasKey('VichUploaderBundle', $kernel->getBundles());

        // Test the upload
        /** @var FilesystemInterface $filesystem */
        $filesystem = $kernel->getContainer()->get('oneup_flysystem.product_image_fs_filesystem');
        $this->assertFalse($filesystem->has('filename.txt'));

        /** @var FlysystemStorage $storage */
        $storage = $kernel->getContainer()->get('test.vich_uploader.storage');
        $this->assertInstanceOf(FlysystemStorage::class, $storage);

        $object = new DummyEntity();

        $mapping = $this->getPropertyMappingMock();

        $mapping
            ->expects($this->once())
            ->method('getFile')
            ->with($object)
            ->willReturn($this->createUploadedFile());

        $mapping
            ->expects($this->once())
            ->method('getUploadDestination')
            ->willReturn('product_image_fs');

        $mapping
            ->expects($this->once())
            ->method('getUploadName')
            ->with($object)
            ->willReturn('filename.txt');

        $mapping
            ->expects($this->once())
            ->method('getUploadDir')
            ->with($object)
            ->willReturn('');

        $storage->upload($object, $mapping);

        /** @var FilesystemInterface $filesystem */
        $filesystem = $kernel->getContainer()->get('oneup_flysystem.product_image_fs_filesystem');
        $this->assertTrue($filesystem->has('filename.txt'));
    }

    private function createUploadedFile(): UploadedFile
    {
        return new UploadedFile(
            __DIR__.'/Fixtures/App/app/Resources/images/symfony_black_03.png',
            'symfony_black_03.png',
            null,
            null,
            true
        );
    }
}
